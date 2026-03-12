FROM php:8.1-fpm

# Define environment variables
ENV TIMEZONE=Europe/Berlin \
    BUILD_DIR=/var/build \
    DEPLOY_DIR=/var/www/html \
    DOCUMENT_ROOT=/var/www/html/public \
    PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d/:/usr/local/etc/php/conf.d/includes/

# Install packages
RUN apt-get update --quiet && apt-get install --quiet --assume-yes --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    libxml2-dev \
    zlib1g-dev \
    libbz2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    zip \
    g++ \
    vim \
    less

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        bz2 \
        calendar \
        gd \
        gettext \
        intl \
        mysqli \
        pdo_mysql \
        shmop \
        soap \
        sockets \
        sysvmsg \
        sysvsem \
        sysvshm \
        opcache \
        zip \
    && pecl install apcu \
    && docker-php-ext-enable apcu

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.idekey = PHPSTORM" >> /usr/local/etc/php/conf.d/ext-xdebug.ini \
    && echo "xdebug.mode = develop,debug" >> /usr/local/etc/php/conf.d/ext-xdebug.ini \
    && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/ext-xdebug.ini \
    && echo "xdebug.client_port = 9003" >> /usr/local/etc/php/conf.d/ext-xdebug.ini \
    && echo "xdebug.start_with_request = trigger" >> /usr/local/etc/php/conf.d/ext-xdebug.ini

# Cleanup
RUN apt-get remove -y '.*-dev' \
    && apt-get clean \
    && rm -rf /tmp/* /var/tmp/* \
    && rm -rf /var/lib/apt/lists/*

# Set time zone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone

RUN mkdir -p /var/log/www/ && chmod -R 777 /var/log/www/

RUN bash -ic 'history -s tail -f /var/log/www/error.log' \
    && bash -ic 'history -s tail -f /var/log/www/*.log' \
    && bash -ic 'history -a'

# Copy config and resources into image
COPY ./config/php/ ${PHP_INI_DIR}/
VOLUME ${DEPLOY_DIR}/

# Expose ports
EXPOSE 9000

CMD ["php-fpm"]
