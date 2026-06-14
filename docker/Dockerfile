FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    libpq-dev \
    unzip

RUN pecl install pcov \
    && docker-php-ext-enable pcov

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN echo 'alias c="composer"' >> /root/.bashrc

WORKDIR /app
CMD ["php", "-S", "0.0.0.0:8000"]
