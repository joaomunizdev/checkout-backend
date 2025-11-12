FROM php:8.4-fpm

# Define the working directory.
WORKDIR /var/www

# Installs system dependencies.
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libgd-dev \
    libicu-dev

# Clear the cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installs the necessary PHP extensions for Laravel.
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Creates a user and group 'www'
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy the application files.
COPY . /var/www
COPY --chown=www:www . /var/www

# Switch to the 'www' user.
USER www

# Exposes port 9000 to PHP-FPM.
EXPOSE 9000

# Run application
CMD ["php-fpm"]