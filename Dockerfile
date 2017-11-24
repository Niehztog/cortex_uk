FROM php:7.1-apache

# Define environment variables
ENV TIMEZONE=Europe/Berlin \
    BUILD_DIR=/var/build \
    DEPLOY_DIR=/var/www/html \
    DOCUMENT_ROOT=/var/www/html/htdocs \
    PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d/:/usr/local/etc/php/conf.d/includes/ \
    APACHE_LOG_DIR=/var/log/www \
    DEBIAN_FRONTEND=noninteractive

# Expose ports
EXPOSE 80
EXPOSE 3306

# Install packages
RUN apt-get update --quiet && apt-get install --quiet --assume-yes --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng12-dev \
    libicu-dev \
    libxml2-dev \
    zlib1g-dev \
    libicu-dev \
    libbz2-dev \
    libcurl3-dev \
    libssl-dev \
    librabbitmq-dev \
    librabbitmq1 \
    geoip-bin \
    geoip-database \
    libgeoip-dev \
    zip \
    g++ \
    vim \
    less

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
        bcmath \
        bz2 \
        calendar \
        gd \
        gettext \
        intl \
        mcrypt \
        mysqli \
        pdo_mysql \
        phar \
        shmop \
        soap \
        sockets \
        sysvmsg \
        sysvsem \
        sysvshm \
        opcache \
        zip \
    && pecl install \
        amqp \
        apcu \
        geoip-1.1.1 \
        redis \        #memcache-3.0.8 \    && docker-php-ext-enable memcache \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install xdebug-2.5.0 \
    && docker-php-ext-enable xdebug

# Install memcache extension
RUN set -x \
    && apt-get update && apt-get install -y --no-install-recommends unzip \
    && cd /tmp \
    && curl -sSL -o php7.zip https://github.com/websupport-sk/pecl-memcache/archive/php7.zip \
    && unzip php7 \
    && cd pecl-memcache-php7 \
    && /usr/local/bin/phpize \
    && ./configure --with-php-config=/usr/local/bin/php-config \
    && make \
    && make install \
    && echo "extension=memcache.so" > /usr/local/etc/php/conf.d/ext-memcache.ini \
    && rm -rf /tmp/pecl-memcache-php7 php7.zip

# Install MariaDB
RUN apt-get install --quiet --assume-yes --no-install-recommends software-properties-common \
    && apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xcbcb082a1bb943db \
    && add-apt-repository 'deb [arch=amd64,i386,ppc64el] http://ftp.hosteurope.de/mirror/mariadb.org/repo/10.2/debian jessie main' \
    && bash -c 'debconf-set-selections <<< "mariadb-server-10.2 mysql-server/root_password password test"' \
    && bash -c 'debconf-set-selections <<< "mariadb-server-10.2 mysql-server/root_password_again password test"' \
    && apt-get install --quiet --assume-yes --no-install-recommends mariadb-server \
    && /etc/init.d/mysql start \
    && mysql -uroot -ptest -e "SET PASSWORD = PASSWORD('test');" \
    && mysql -uroot -ptest -e "CREATE DATABASE cortex"

# CMD ["/usr/bin/mysqld_safe"]

# Cleanup
RUN apt-get remove -y '.*-dev' \
    && apt-get clean \
    && rm -rf /tmp/* /var/tmp/* \
    && rm -rf /var/lib/apt/lists/*

# Set time zone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone

# Copy config and resources into image
COPY ./config/php/ ${PHP_INI_DIR}/
COPY htdocs ${DEPLOY_DIR}/

RUN mkdir -p /var/log/www/ && chmod -R 777 /var/log/www/
#    && touch /var/log/www/php_error.log \
#    && touch /var/log/www/access.log

