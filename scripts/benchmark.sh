#!/bin/bash

# Performance test of the SaltOS API with different clients and PHP versions
#
# Usage (from the code/ directory of the instance):
#   ../scripts/benchmark.sh [N]
#
# Environment:
#   N         iterations of each scenario (default: 100, plus 1 warmup)
#   VERSIONS  PHP versions to test (default: the version of php)
#   CLIENTS   clients to test (default: "cli fcgi curl")
#   PORT      port of the private nginx used by curl (default: 28473)
#   ENCODING  Accept-Encoding of the measures, for example zstd (default: none), the
#             warmup is not compressed to check the responses
#
# Clients:
#   cli   user=admin phpX.Y api/index.php <rest>
#   fcgi  saltos <rest> against a private php-fpmX.Y, the client is compiled from
#         scripts/saltos_client.c into the temp dir
#   curl  curl http://127.0.0.1:PORT/api/?/<rest> against a private nginx that
#         uses the private php-fpmX.Y
#
# The private php-fpm uses the php.ini of /etc/php/X.Y/fpm and the private nginx
# follows the scripts/server.nginx.conf recipe, both run as the current user with
# their sockets, pids and logs in a temp dir, and only when a client needs them
#
# Scenarios:
#   about  about/about
#   view   the requests of a cold load of #/app/customers/view/1 (the view, the
#          list in the background, its filters and its data), without auth/check
#          because the cli client has no token
#
# Output: min / avg / max in ms of each request and of the whole scenario, measured
# in the client, and for fcgi and curl also the app time, measured by php-fpm in
# its access log (%{micro}d), without the costs of the client and the connection

set -e

N=${N:-${1:-100}}
VERSIONS=${VERSIONS:-$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')}
CLIENTS=${CLIENTS:-cli fcgi curl}
PORT=${PORT:-28473}
ENCODING=${ENCODING:-}
CURL_URL=http://127.0.0.1:$PORT/api/

if [ ! -f api/index.php ]; then
    echo "Run this script from the code/ directory"
    exit 1
fi

has_client() {
    [[ " $CLIENTS " == *" $1 "* ]]
}

for v in $VERSIONS; do
    if has_client cli && [ ! -x "/usr/bin/php$v" ]; then
        echo "/usr/bin/php$v not found"
        exit 1
    fi
    if (has_client fcgi || has_client curl) && [ ! -x "/usr/sbin/php-fpm$v" ]; then
        echo "/usr/sbin/php-fpm$v not found"
        exit 1
    fi
done
if has_client fcgi && ! command -v gcc >/dev/null; then
    echo "gcc not found, it is needed to compile the fcgi client"
    exit 1
fi
if has_client curl; then
    if [ ! -x /usr/sbin/nginx ]; then
        echo "/usr/sbin/nginx not found"
        exit 1
    fi
    if (exec 3<>"/dev/tcp/127.0.0.1/$PORT") 2>/dev/null; then
        echo "The port $PORT is in use, set another one with PORT"
        exit 1
    fi
fi

DATA='{"page":"0","order":"","search":""}'
FILTER='{"name":"last","val":"{\"page\":\"0\",\"order\":\"\",\"search\":\"\"}"}'

# Each request is "rest|json", the json is optional
declare -A SCENARIOS
SCENARIOS[about]='about/about'
SCENARIOS[view]="app/customers/view/1
app/customers/view/cache
app/customers/main
app/customers
app/customers/list/cache
app/customers/list/filter
app/customers/list/filter|$FILTER
app/customers/list/data|$DATA"

TEMP=$(mktemp -d)
SALTOS=$TEMP/saltos
export SALTOS_TOKEN=$TEMP/token

# Stops the private servers and invalidates the tokens of fcgi and curl
FPM_PID=
NGINX_PID=
CURL_TOKEN=
cleanup() {
    if [ -n "$CURL_TOKEN" ]; then
        curl_logout || true
    fi
    if [ -f "$SALTOS_TOKEN" ]; then
        "$SALTOS" logout >/dev/null || true
    fi
    if [ -n "$NGINX_PID" ]; then
        kill "$NGINX_PID" || true
    fi
    if [ -n "$FPM_PID" ]; then
        kill "$FPM_PID" || true
    fi
}
trap 'cleanup; rm -rf "$TEMP"' EXIT

# The fcgi client is compiled from the source to use always its current version
if has_client fcgi; then
    gcc -O2 -o "$SALTOS" "$(dirname "${BASH_SOURCE[0]}")/saltos_client.c"
fi

# Waits until the file exists (or not exists with !), max 5 seconds
wait_file() {
    local test=$1 file=$2
    for ((i = 0; i < 50; i++)); do
        if [ "$test" = "!" ]; then
            [ ! -e "$file" ] && return 0
        else
            [ -e "$file" ] && return 0
        fi
        sleep 0.1
    done
    return 1
}

# Starts a private php-fpm of the version, with the same pool settings as the default
# pool of Debian
fpm_start() {
    local v=$1
    cat >"$TEMP/fpm.conf" <<EOF
[global]
pid = $TEMP/fpm.pid
error_log = $TEMP/fpm.log

[www]
listen = $TEMP/fpm.sock
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
access.log = $TEMP/fpm.access.log
access.format = "%{micro}d"
EOF
    FPM_REQS=0
    "/usr/sbin/php-fpm$v" -y "$TEMP/fpm.conf" 2>>"$TEMP/fpm.log"
    if ! wait_file - "$TEMP/fpm.sock" || ! wait_file - "$TEMP/fpm.pid"; then
        echo "php-fpm$v did not start"
        cat "$TEMP/fpm.log"
        exit 1
    fi
    FPM_PID=$(cat "$TEMP/fpm.pid")
    export SALTOS_SOCKET=$TEMP/fpm.sock
}

fpm_stop() {
    kill "$FPM_PID"
    FPM_PID=
    wait_file ! "$TEMP/fpm.sock"
}

# Starts a private nginx that follows the scripts/server.nginx.conf recipe, the
# fastcgi.conf link is needed because the snippet includes it with a relative path
nginx_start() {
    mkdir -p "$TEMP/nginx"
    ln -sf /etc/nginx/fastcgi.conf "$TEMP/nginx/fastcgi.conf"
    cat >"$TEMP/nginx/nginx.conf" <<EOF
worker_processes auto;
pid $TEMP/nginx/nginx.pid;
error_log $TEMP/nginx/error.log;

events {
    worker_connections 768;
}

http {
    sendfile on;
    access_log $TEMP/nginx/access.log;
    client_body_temp_path $TEMP/nginx/body;
    fastcgi_temp_path $TEMP/nginx/fastcgi;
    proxy_temp_path $TEMP/nginx/proxy;
    uwsgi_temp_path $TEMP/nginx/uwsgi;
    scgi_temp_path $TEMP/nginx/scgi;

    server {
        listen 127.0.0.1:$PORT;
        root $PWD/web;
        index index.html index.php;
        server_tokens off;
        client_max_body_size 50M;
        gzip on;
        gzip_types text/css application/javascript application/json image/svg+xml;
        include /etc/nginx/mime.types;
        types {
            application/javascript mjs;
            application/json map;
        }
        location ~ \.php\$ {
            include /etc/nginx/snippets/fastcgi-php.conf;
            fastcgi_pass unix:$TEMP/fpm.sock;
            fastcgi_read_timeout 300s;
        }
    }
}
EOF
    /usr/sbin/nginx -p "$TEMP/nginx" -c "$TEMP/nginx/nginx.conf" -e "$TEMP/nginx/error.log"
    if ! wait_file - "$TEMP/nginx/nginx.pid"; then
        echo "nginx did not start"
        cat "$TEMP/nginx/error.log"
        exit 1
    fi
    NGINX_PID=$(cat "$TEMP/nginx/nginx.pid")
}

nginx_stop() {
    kill "$NGINX_PID"
    NGINX_PID=
    wait_file ! "$TEMP/nginx/nginx.pid"
}

# Login and logout of the curl client, the token is bound to the user agent
curl_login() {
    local out
    out=$(curl -s -A saltos-client -H "Content-Type: application/json" \
        --data '{"user":"admin","pass":"admin"}' "$CURL_URL?/auth/login")
    CURL_TOKEN=$(echo "$out" | sed -n 's/.*"token":"\([^"]*\)".*/\1/p')
    if [ -z "$CURL_TOKEN" ]; then
        echo "Error in curl auth/login: ${out:0:200}"
        exit 1
    fi
}

curl_logout() {
    curl -s -A saltos-client -H "Authorization: Bearer $CURL_TOKEN" "$CURL_URL?/auth/logout" >/dev/null
    CURL_TOKEN=
}

# Sends the request with the client, the encoding is the Accept-Encoding header
request() {
    local client=$1 rest=$2 json=$3 encoding=$4
    case $client in
        cli)
            if [ -n "$json" ]; then
                HTTP_ACCEPT_ENCODING=$encoding user=admin "php$VERSION" api/index.php "$rest" <<<"$json"
            else
                HTTP_ACCEPT_ENCODING=$encoding user=admin "php$VERSION" api/index.php "$rest" </dev/null
            fi
            ;;
        fcgi)
            if [ -n "$json" ]; then
                SALTOS_ENCODING=$encoding "$SALTOS" "$rest" "$json" </dev/null
            else
                SALTOS_ENCODING=$encoding "$SALTOS" "$rest" </dev/null
            fi
            ;;
        curl)
            local args=(-s -A saltos-client -H "Authorization: Bearer $CURL_TOKEN")
            if [ -n "$encoding" ]; then
                args+=(-H "Accept-Encoding: $encoding")
            fi
            if [ -n "$json" ]; then
                curl "${args[@]}" -H "Content-Type: application/json" --data "$json" "$CURL_URL?/$rest"
            else
                curl "${args[@]}" "$CURL_URL?/$rest"
            fi
            ;;
    esac
}

stats() {
    awk '{ s += $1; if (NR == 1 || $1 < min) min = $1; if ($1 > max) max = $1 }
        END { printf " %8.2f %8.2f %8.2f", min, s / NR, max }'
}

# Counts the requests sent to the private php-fpm, to know which lines of its access
# log belong to the measures, and waits until all of them are written
FPM_REQS=0
fpm_count() {
    FPM_REQS=$((FPM_REQS + $1))
    for ((k = 0; k < 50; k++)); do
        if [ "$(wc -l <"$TEMP/fpm.access.log")" -ge "$FPM_REQS" ]; then
            return 0
        fi
        sleep 0.1
    done
    echo "The access log of php-fpm has less lines than requests"
    exit 1
}

# Runs all scenarios with the client and prints the results with the label
bench() {
    local client=$1 label=$2
    for scenario in about view; do
        mapfile -t items <<<"${SCENARIOS[$scenario]}"
        # Warmup and check of the responses
        for item in "${items[@]}"; do
            rest=${item%%|*}
            json=${item#"$rest"}
            json=${json#|}
            out=$(request "$client" "$rest" "$json" "")
            if [ -z "$out" ] || [[ "$out" == *'"error":'* ]] || [[ "$out" == *'"status":"ko"'* ]]; then
                echo "Error in $label $rest: ${out:0:200}"
                exit 1
            fi
        done
        if [ "$client" != cli ]; then
            fpm_count ${#items[@]}
        fi
        # Measures, the values are stored in hundredths of ms
        rm -f "$TEMP"/times.* "$TEMP"/app.*
        start=$FPM_REQS
        for ((i = 0; i < N; i++)); do
            total=0
            for j in "${!items[@]}"; do
                item=${items[$j]}
                rest=${item%%|*}
                json=${item#"$rest"}
                json=${json#|}
                t0=$EPOCHREALTIME
                request "$client" "$rest" "$json" "$ENCODING" >/dev/null
                t1=$EPOCHREALTIME
                ms=$(((${t1/./} - ${t0/./}) / 10))
                echo "$ms" >>"$TEMP/times.$j"
                total=$((total + ms))
            done
            echo "$total" >>"$TEMP/times.total"
        done
        # The app times are the lines of the measures in the php-fpm access log, in the
        # same order as the requests, the values are in microseconds
        if [ "$client" != cli ]; then
            fpm_count $((N * ${#items[@]}))
            tail -n +$((start + 1)) "$TEMP/fpm.access.log" | head -n $((N * ${#items[@]})) |
                awk -v n=${#items[@]} -v dir="$TEMP" '{
                    j = (NR - 1) % n
                    print $1 / 1000 > (dir "/app." j)
                    t += $1
                    if (j == n - 1) {
                        print t / 1000 > (dir "/app.total")
                        t = 0
                    }
                }'
        fi
        # Results
        echo
        printf "%-12s %-40s %8s %8s %8s" "$label" "$scenario" min avg max
        if [ "$client" != cli ]; then
            printf " %8s %8s %8s" "app min" "app avg" "app max"
        fi
        echo
        for j in "${!items[@]}"; do
            item=${items[$j]}
            name=${item%%|*}
            [[ "$item" == *"|"* ]] && name="$name (post)"
            printf "%-12s %-40s" "" "$name"
            awk '{ print $1 / 100 }' "$TEMP/times.$j" | stats
            if [ "$client" != cli ]; then
                stats <"$TEMP/app.$j"
            fi
            echo
        done
        if [ ${#items[@]} -gt 1 ]; then
            printf "%-12s %-40s" "" "total"
            awk '{ print $1 / 100 }' "$TEMP/times.total" | stats
            if [ "$client" != cli ]; then
                stats <"$TEMP/app.total"
            fi
            echo
        fi
    done
}

echo "N=$N, VERSIONS=$VERSIONS, CLIENTS=$CLIENTS, ENCODING=$ENCODING"
for VERSION in $VERSIONS; do
    if has_client cli; then
        bench cli "cli $VERSION"
    fi
    if has_client fcgi || has_client curl; then
        fpm_start "$VERSION"
    fi
    if has_client fcgi; then
        "$SALTOS" login admin admin >/dev/null
        fpm_count 1
        bench fcgi "fcgi $VERSION"
        "$SALTOS" logout >/dev/null
        fpm_count 1
        rm -f "$SALTOS_TOKEN"
    fi
    if has_client curl; then
        nginx_start
        curl_login
        fpm_count 1
        bench curl "curl $VERSION"
        curl_logout
        fpm_count 1
        nginx_stop
    fi
    if [ -n "$FPM_PID" ]; then
        fpm_stop
    fi
done
