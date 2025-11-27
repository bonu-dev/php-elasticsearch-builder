FROM php:8.4-zts-trixie AS workspace

ENV PHP_INSTALL_EXTS="zip curl sockets"

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions ${PHP_INSTALL_EXTS} \
    && rm /usr/local/bin/install-php-extensions

COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

ENTRYPOINT ["php"]