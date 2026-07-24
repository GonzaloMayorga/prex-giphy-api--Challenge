FROM php:8.3-fpm-bookworm

ARG LOCAL_UID=1000
ARG LOCAL_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        default-mysql-client \
        git \
        unzip \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        xml \
        xmlwriter \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker/php/local.ini /usr/local/etc/php/conf.d/99-application.ini

RUN groupadd --gid "${LOCAL_GID}" application \
    && useradd \
        --uid "${LOCAL_UID}" \
        --gid "${LOCAL_GID}" \
        --create-home \
        --shell /bin/bash \
        application

WORKDIR /var/www/html

USER application

CMD ["php-fpm"]
