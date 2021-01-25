FROM ubuntu:20.10
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV DEBIAN_FRONTEND noninteractive

VOLUME ["/app"]
WORKDIR /app

RUN apt-get update \
&& apt-get install -y curl software-properties-common \
&& add-apt-repository ppa:ondrej/php \
&& apt-get update \
&& apt-get upgrade -y \
&& apt-get install -y \
    git \
    php7.3 \
    php7.3-cli \
    php7.3-curl \
    php7.3-json \
    php7.3-zip

ENTRYPOINT while true; do sleep 30; done
