FROM debian:trixie

# Preform first update and install dependencies
RUN apt-get update && apt-get install -y \
    extrepo \
    git \
    vim \
    net-tools \
    iputils-ping \
    poppler-utils \
    && rm -rf /var/lib/apt/lists/*

# Enable external repos like sury and official mariadb
RUN extrepo enable sury \
    && extrepo enable mariadb-11.8

# Install MariaDB, Apache and PHP
RUN apt-get update && apt-get install -y \
    mariadb-server \
    mariadb-client \
    apache2 \
    libapache2-mod-php8.5 \
    php8.5 \
    php8.5-cli \
    php8.5-common \
    php8.5-curl \
    php8.5-gd \
    php8.5-intl \
    php8.5-mbstring \
    php8.5-mysql \
    php8.5-pgsql \
    php8.5-readline \
    php8.5-xml \
    php8.5-zip \
    php8.5-soap \
    php8.5-bcmath \
    php8.5-gettext \
    php8.5-mailparse \
    && rm -rf /var/lib/apt/lists/*

# Add the apache ssl support
RUN a2enmod rewrite \
    && a2enmod ssl \
    && a2enmod headers \
    && a2enmod expires \
    && a2enmod deflate \
    && mkdir -p /etc/apache2/ssl \
    && openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/apache-selfsigned.key \
    -out /etc/apache2/ssl/apache-selfsigned.crt \
    -subj "/C=ES/ST=Madrid/L=Madrid/O=SaltOS4/OU=Development/CN=localhost" \
    && a2ensite default-ssl.conf

# Configure apache
RUN echo "<Directory /var/www/html>\n\tAllowOverride All\n\tRequire all granted\n</Directory>" \
    > /etc/apache2/conf-enabled/saltos.conf

# Configure MariaDB
RUN sed -i 's/^bind-address.*/bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf && \
    echo "[mysqld]" >> /etc/mysql/mariadb.conf.d/99-custom.cnf && \
    echo "character-set-server = utf8mb4" >> /etc/mysql/mariadb.conf.d/99-custom.cnf && \
    echo "collation-server = utf8mb4_unicode_ci" >> /etc/mysql/mariadb.conf.d/99-custom.cnf && \
    echo "max_allowed_packet = 256M" >> /etc/mysql/mariadb.conf.d/99-custom.cnf

# Configure DB credentials
RUN service mariadb start \
    && echo "CREATE DATABASE saltos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" | mariadb \
    && echo "CREATE USER 'saltos'@'localhost' IDENTIFIED BY 'saltos';" | mariadb \
    && echo "GRANT ALL PRIVILEGES ON saltos.* TO 'saltos'@'localhost';" | mariadb \
    && service mariadb stop

# Configure PHP
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/' /etc/php/8.5/apache2/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 50M/' /etc/php/8.5/apache2/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/8.5/apache2/php.ini && \
    sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.5/apache2/php.ini

# Checkout saltos4 code
RUN git clone https://github.com/josepsanzcamp/SaltOS4.git /opt/SaltOS4

# Create the saltos4 instance
RUN cd /var/www \
    && mv html html.old \
    && mkdir html \
    && ln -s /opt/SaltOS4/code \
    && ln -s /opt/SaltOS4/scripts \
    && cd html \
    && bash ../scripts/make_instance.sh \
    && cd .. \
    && chown -R www-data:www-data html \
    && chmod -R 755 html

# SaltOS setup
RUN service mariadb start \
    && php /var/www/html/api/index.php setup \
    && user=admin php /var/www/html/api/index.php setup/certs \
    && user=admin php /var/www/html/api/index.php setup/company \
    && user=admin php /var/www/html/api/index.php setup/emails \
    && user=admin php /var/www/html/api/index.php setup/crm \
    && user=admin php /var/www/html/api/index.php setup/hr \
    && user=admin php /var/www/html/api/index.php setup/purchases \
    && user=admin php /var/www/html/api/index.php setup/sales \
    && service mariadb stop

CMD service mariadb start \
    && service apache2 start \
    && /bin/tail -f /dev/null
