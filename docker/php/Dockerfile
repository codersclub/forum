ARG BUILD_FPM_VERSION=7.4-fpm

# ==============================================================================
#   Common Layer
# ==============================================================================

FROM php:${BUILD_FPM_VERSION} AS base

WORKDIR "/home/sources/forum.sources.ru"

ARG DEBIAN_FRONTEND=noninteractive
ARG UID=1000

# ------------------------------------------------------------------------------
#   Install Dependencies
# ------------------------------------------------------------------------------

RUN apt-get update \
    && apt-get -y --no-install-recommends install \
      libzip-dev \
      libicu-dev \
      libgmp-dev \
      unzip \
      git \
      curl \
    # 1. Install Composer & Pie
    && curl -sS https://getcomposer.org/installer \
       | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -L --output /usr/bin/pie https://github.com/php/pie/releases/latest/download/pie.phar \
       && chmod +x /usr/bin/pie \
    # 2. Install extensions
    && docker-php-ext-install intl \
    && docker-php-ext-install sockets \
    && docker-php-ext-install gmp \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_mysql \
    # 3. Cleanup Dependencies
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
      && ln -sf /usr/share/zoneinfo/Etc/UTC /etc/localtime \
    # 4. Create User
    && useradd -u $UID -m sources

# ------------------------------------------------------------------------------
#   Common Configuration
# ------------------------------------------------------------------------------

COPY --chown=root:root ./docker/php/conf.d/php.ini "$PHP_INI_DIR/98-php.ini"

# ------------------------------------------------------------------------------
#   Common Entrypoint
# ------------------------------------------------------------------------------

COPY ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh



# ==============================================================================
#   Dev Layer
# ==============================================================================

FROM base AS dev

# ------------------------------------------------------------------------------
#   Dev Configuration
# ------------------------------------------------------------------------------

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY --chown=root:root ./docker/php/conf.d/php.dev.ini "$PHP_INI_DIR/99-php.dev.ini"

# ------------------------------------------------------------------------------
#   Dev Entrypoint
# ------------------------------------------------------------------------------

COPY ./docker/php/entrypoint.dev.sh /usr/local/bin/entrypoint.dev.sh
RUN chmod +x /usr/local/bin/entrypoint.dev.sh

# ------------------------------------------------------------------------------
#   Ready
# ------------------------------------------------------------------------------

USER sources

ENTRYPOINT ["/usr/local/bin/entrypoint.dev.sh"]


# ==============================================================================
#   Prod Layer
# ==============================================================================

FROM base AS prod

# ------------------------------------------------------------------------------
#   Prod Entrypoint
# ------------------------------------------------------------------------------

COPY ./docker/php/entrypoint.prod.sh /usr/local/bin/entrypoint.prod.sh
RUN chmod +x /usr/local/bin/entrypoint.prod.sh

# ------------------------------------------------------------------------------
#   Prod Files
# ------------------------------------------------------------------------------

COPY . /home/sources/forum.sources.ru

RUN APP_ENV=prod composer install --no-dev --no-interaction --optimize-autoloader \
 && chown $UID:1000 /home/sources/forum.sources.ru -R \
 && mkdir -p /home/sources/.composer \
 && chown $UID:1000 /home/sources/.composer -R

# ------------------------------------------------------------------------------
#   Ready
# ------------------------------------------------------------------------------

USER sources

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.prod.sh"]
