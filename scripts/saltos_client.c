/*
 * Calls the SaltOS API through the php-fpm socket, avoiding the PHP CLI startup
 *
 * Usage (from the code/ directory of the instance):
 *   saltos login [user]    asks the password and stores the token
 *   saltos logout          invalidates and removes the stored token
 *   saltos <rest> [json]   GET request, or POST request if json is given
 *
 * Environment:
 *   SALTOS_SOCKET   php-fpm socket (default: /run/php/php8.5-fpm.sock)
 *   SALTOS_SCRIPT   api entry point (default: $PWD/web/api/index.php)
 *   SALTOS_TOKEN    token file (default: ~/.saltos_client_token)
 *   token           token to use instead of the stored one
 *
 * Build: cc -O2 -o saltos saltos_client.c
 *        gcc -O2 -o ~/bin/saltos ../scripts/saltos_client.c
 */

#include <errno.h>
#include <fcntl.h>
#include <stdarg.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/un.h>
#include <termios.h>
#include <unistd.h>

#define DEFAULT_SOCKET "/run/php/php8.5-fpm.sock"
#define DEFAULT_SCRIPT "web/api/index.php" /* relative to the current directory */
#define DEFAULT_TOKEN_FILE ".saltos_client_token" /* relative to $HOME */

/* Fixed values, the token is bound to the remote address and the user agent */
#define REMOTE_ADDR "127.0.0.1"
#define USER_AGENT "saltos-client"

#define FCGI_BEGIN_REQUEST 1
#define FCGI_END_REQUEST 3
#define FCGI_PARAMS 4
#define FCGI_STDIN 5
#define FCGI_STDOUT 6
#define FCGI_STDERR 7
#define FCGI_RESPONDER 1

typedef struct {
    char *data;
    size_t len;
    size_t size;
} buffer;

static void die(const char *fmt, ...)
{
    va_list ap;
    va_start(ap, fmt);
    vfprintf(stderr, fmt, ap);
    va_end(ap);
    fputc('\n', stderr);
    exit(1);
}

static void buf_add(buffer *b, const void *data, size_t len)
{
    if (b->len + len > b->size) {
        b->size = (b->len + len) * 2;
        b->data = realloc(b->data, b->size);
        if (!b->data) {
            die("Out of memory");
        }
    }
    memcpy(b->data + b->len, data, len);
    b->len += len;
}

static void buf_record(buffer *b, int type, const char *data, size_t len)
{
    unsigned char header[8] = {1, type, 0, 1, len >> 8, len & 0xff, 0, 0};
    buf_add(b, header, 8);
    buf_add(b, data, len);
}

static void buf_param_len(buffer *b, size_t len)
{
    if (len < 128) {
        unsigned char c = len;
        buf_add(b, &c, 1);
    } else {
        unsigned char c[4] = {(len >> 24) | 0x80, len >> 16, len >> 8, len};
        buf_add(b, c, 4);
    }
}

static void buf_param(buffer *b, const char *name, const char *value)
{
    if (!value) {
        return;
    }
    buf_param_len(b, strlen(name));
    buf_param_len(b, strlen(value));
    buf_add(b, name, strlen(name));
    buf_add(b, value, strlen(value));
}

static void read_all(int fd, void *data, size_t len)
{
    while (len) {
        ssize_t n = read(fd, data, len);
        if (n < 0 && errno == EINTR) {
            continue;
        }
        if (n <= 0) {
            die("Connection closed by php-fpm");
        }
        data = (char *)data + n;
        len -= n;
    }
}

/* Returns the body of the response, without the HTTP headers */
static char *request(const char *socket_path, const char *script, const char *rest,
                     const char *json, const char *token)
{
    buffer out = {0};
    buffer params = {0};
    char auth[256];
    char length[32];

    buf_param(&params, "SCRIPT_FILENAME", script);
    buf_param(&params, "QUERY_STRING", rest);
    buf_param(&params, "REMOTE_ADDR", REMOTE_ADDR);
    buf_param(&params, "HTTP_USER_AGENT", USER_AGENT);
    if (token && *token) {
        snprintf(auth, sizeof(auth), "Bearer %s", token);
        buf_param(&params, "HTTP_AUTHORIZATION", auth);
    }
    if (json && *json) {
        snprintf(length, sizeof(length), "%zu", strlen(json));
        buf_param(&params, "REQUEST_METHOD", "POST");
        buf_param(&params, "CONTENT_TYPE", "application/json");
        buf_param(&params, "CONTENT_LENGTH", length);
    } else {
        buf_param(&params, "REQUEST_METHOD", "GET");
    }

    unsigned char begin[8] = {0, FCGI_RESPONDER, 0, 0, 0, 0, 0, 0};
    buf_record(&out, FCGI_BEGIN_REQUEST, (char *)begin, 8);
    buf_record(&out, FCGI_PARAMS, params.data, params.len);
    buf_record(&out, FCGI_PARAMS, NULL, 0);
    size_t json_len = json ? strlen(json) : 0;
    for (size_t pos = 0; pos < json_len; pos += 65535) {
        size_t len = json_len - pos < 65535 ? json_len - pos : 65535;
        buf_record(&out, FCGI_STDIN, json + pos, len);
    }
    buf_record(&out, FCGI_STDIN, NULL, 0);
    free(params.data);

    int fd = socket(AF_UNIX, SOCK_STREAM, 0);
    struct sockaddr_un addr = {.sun_family = AF_UNIX};
    strncpy(addr.sun_path, socket_path, sizeof(addr.sun_path) - 1);
    if (fd < 0 || connect(fd, (struct sockaddr *)&addr, sizeof(addr)) < 0) {
        die("Could not connect to %s: %s", socket_path, strerror(errno));
    }
    for (size_t pos = 0; pos < out.len;) {
        ssize_t n = write(fd, out.data + pos, out.len - pos);
        if (n < 0 && errno == EINTR) {
            continue;
        }
        if (n <= 0) {
            die("Could not write to %s: %s", socket_path, strerror(errno));
        }
        pos += n;
    }
    free(out.data);

    buffer resp = {0};
    char content[65535 + 255];
    for (;;) {
        unsigned char header[8];
        read_all(fd, header, 8);
        size_t len = (header[4] << 8) | header[5];
        read_all(fd, content, len + header[6]);
        if (header[1] == FCGI_STDOUT) {
            buf_add(&resp, content, len);
        } else if (header[1] == FCGI_STDERR) {
            fwrite(content, 1, len, stderr);
        } else if (header[1] == FCGI_END_REQUEST) {
            break;
        }
    }
    close(fd);
    buf_add(&resp, "", 1);

    /* Skip the headers until the first empty line */
    char *body = strstr(resp.data, "\r\n\r\n");
    body = body ? body + 4 : resp.data;
    memmove(resp.data, body, strlen(body) + 1);
    return resp.data;
}

static void print_body(const char *body)
{
    size_t len = strlen(body);
    fwrite(body, 1, len, stdout);
    if (!len || body[len - 1] != '\n') {
        fputc('\n', stdout);
    }
}

static char *read_line(const char *prompt, int hidden)
{
    static char line[1024];
    struct termios old, new;
    int tty = hidden && isatty(STDIN_FILENO) && !tcgetattr(STDIN_FILENO, &old);
    fputs(prompt, stderr);
    if (tty) {
        new = old;
        new.c_lflag &= ~ECHO;
        tcsetattr(STDIN_FILENO, TCSAFLUSH, &new);
    }
    if (!fgets(line, sizeof(line), stdin)) {
        line[0] = 0;
    }
    if (tty) {
        tcsetattr(STDIN_FILENO, TCSAFLUSH, &old);
    }
    if (hidden) {
        fputc('\n', stderr);
    }
    line[strcspn(line, "\r\n")] = 0;
    return strdup(line);
}

static void json_string(buffer *b, const char *str)
{
    buf_add(b, "\"", 1);
    for (; *str; str++) {
        char esc[8];
        if (*str == '"' || *str == '\\') {
            snprintf(esc, sizeof(esc), "\\%c", *str);
        } else if ((unsigned char)*str < 0x20) {
            snprintf(esc, sizeof(esc), "\\u%04x", *str);
        } else {
            snprintf(esc, sizeof(esc), "%c", *str);
        }
        buf_add(b, esc, strlen(esc));
    }
    buf_add(b, "\"", 1);
}

int main(int argc, char **argv)
{
    const char *socket_path = getenv("SALTOS_SOCKET");
    if (!socket_path) {
        socket_path = DEFAULT_SOCKET;
    }

    char script[4096];
    if (getenv("SALTOS_SCRIPT")) {
        snprintf(script, sizeof(script), "%s", getenv("SALTOS_SCRIPT"));
    } else {
        char cwd[4000];
        if (!getcwd(cwd, sizeof(cwd))) {
            die("Could not get the current directory");
        }
        snprintf(script, sizeof(script), "%s/%s", cwd, DEFAULT_SCRIPT);
    }
    if (access(script, F_OK)) {
        die("%s not found", script);
    }

    char token_file[4096];
    if (getenv("SALTOS_TOKEN")) {
        snprintf(token_file, sizeof(token_file), "%s", getenv("SALTOS_TOKEN"));
    } else {
        snprintf(token_file, sizeof(token_file), "%s/%s", getenv("HOME"), DEFAULT_TOKEN_FILE);
    }

    char token[256] = "";
    if (getenv("token")) {
        snprintf(token, sizeof(token), "%s", getenv("token"));
    } else {
        FILE *f = fopen(token_file, "r");
        if (f) {
            if (!fgets(token, sizeof(token), f)) {
                token[0] = 0;
            }
            token[strcspn(token, "\r\n")] = 0;
            fclose(f);
        }
    }

    if (argc < 2) {
        const char *name = strrchr(argv[0], '/') ? strrchr(argv[0], '/') + 1 : argv[0];
        fprintf(stderr,
                "Usage (from the code/ directory of the instance):\n"
                "  %s login [user]    asks the password and stores the token\n"
                "  %s logout          invalidates and removes the stored token\n"
                "  %s <rest> [json]   GET request, or POST request if json is given\n",
                name, name, name);
        return 1;
    }

    if (!strcmp(argv[1], "login")) {
        char *user = argc > 2 ? strdup(argv[2]) : read_line("User: ", 0);
        char *pass = read_line("Pass: ", 1);
        buffer json = {0};
        buf_add(&json, "{\"user\":", 8);
        json_string(&json, user);
        buf_add(&json, ",\"pass\":", 8);
        json_string(&json, pass);
        buf_add(&json, "}", 2);
        char *body = request(socket_path, script, "/auth/login", json.data, NULL);
        char *start = strstr(body, "\"token\":\"");
        char *end = start ? strchr(start + 9, '"') : NULL;
        if (!end) {
            fputs(body, stderr);
            fputc('\n', stderr);
            return 1;
        }
        int fd = open(token_file, O_WRONLY | O_CREAT | O_TRUNC, 0600);
        if (fd < 0) {
            die("Could not write %s: %s", token_file, strerror(errno));
        }
        dprintf(fd, "%.*s\n", (int)(end - start - 9), start + 9);
        close(fd);
        print_body(body);
        return 0;
    }

    if (!strcmp(argv[1], "logout")) {
        print_body(request(socket_path, script, "/auth/logout", NULL, token));
        unlink(token_file);
        return 0;
    }

    print_body(request(socket_path, script, argv[1], argc > 2 ? argv[2] : NULL, token));
    return 0;
}
