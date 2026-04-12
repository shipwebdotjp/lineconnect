FROM wordpress:latest

ENV TZ Asia/Tokyo
WORKDIR /var/www/html

# Install necessary packages for building and testing, including git, subversion, and wp-cli
RUN apt-get update && \
    apt-get install -y \
    git \
    subversion \
    sudo \
    unzip \
    less \
    nano \
    # Clean up APT cache to reduce image size
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install sockets

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Add a user for running commands (matching the user in docker-compose.yml if 'xfs' is used)
# This assumes 'xfs' is intended to be a non-root user. For now, we'll keep it simple
# and just ensure 'www-data' user can run wp-cli commands.
# If 'xfs' user needs specific setup, that would be an additional step.

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer