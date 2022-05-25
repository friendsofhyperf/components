# See here for image contents: https://github.com/microsoft/vscode-dev-containers/tree/v0.195.0/containers/php/.devcontainer/base.Dockerfile
# [Choice] PHP version (use -bullseye variants on local arm64/Apple Silicon): 8, 8.0, 7, 7.4, 7.3, 8-bullseye, 8.0-bullseye, 7-bullseye, 7.4-bullseye, 7.3-bullseye, 8-buster, 8.0-buster, 7-buster, 7.4-buster, 7.3-buster
ARG VARIANT=7-bullseye
FROM mcr.microsoft.com/vscode/devcontainers/php:0-${VARIANT}

# [Choice] Node.js version: none, lts/*, 16, 14, 12, 10
ARG NODE_VERSION="none"
RUN if [ "${NODE_VERSION}" != "none" ]; then su vscode -c "umask 0002 && . /usr/local/share/nvm/nvm.sh && nvm install ${NODE_VERSION} 2>&1"; fi

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1

# Set up the environment.
ARG ID_ED25519_PRIV="none"
RUN if [ "${ID_ED25519_PRIV}" != "none" ]; then su vscode -c "mkdir -p ~/.ssh/ && echo -e \"${ID_ED25519_PRIV}\" > ~/.ssh/id_ed25519 && chmod 600 ~/.ssh/id_ed25519 && ssh-keyscan github.com >> ~/.ssh/known_hosts"; fi

# Install php-ext sockets & bcmath
RUN set -ex \
    && docker-php-ext-install sockets bcmath

# Install php-ext redis
RUN set -ex \
    && pecl install redis \
    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini

# Install php-ext swoole
ARG SWOOLE_VERSION="none"
RUN set -ex \
    && if [ "${SWOOLE_VERSION}" != "none"]; then pecl install http://pecl.php.net/get/swoole-${SWOOLE_VERSION}.tgz; else pecl install swoole; fi \
    && echo "extension=swoole.so" > /usr/local/etc/php/conf.d/swoole.ini \
    && echo "swoole.use_shortname = 'Off'" >> /usr/local/etc/php/conf.d/swoole.ini

# Configure PHP
RUN set -ex \
    && echo "memory_limit=1G" > /usr/local/etc/php/conf.d/default.ini \
    && echo "opcache.enable_cli = 'On'" >> /usr/local/etc/php/conf.d/opcache.ini