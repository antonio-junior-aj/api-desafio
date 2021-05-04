FROM alpine:3.11

RUN apk --update add --no-cache \
        ttf-dejavu ttf-droid ttf-freefont ttf-liberation ttf-ubuntu-font-family \
        git \
        nginx \
        curl \
        supervisor \
        php7 \
        php7-ctype \
        php7-curl \
        php7-dom \
        php7-fpm \
        php7-iconv \
        php7-json \
        php7-mbstring \
        php7-mcrypt \
        php7-opcache \
        php7-openssl \
        php7-pdo \
        php7-pdo_mysql \        
        php7-phar \
        php7-session \
        php7-tokenizer \
        php7-fileinfo \
        php7-xml \
        php7-intl \
        php7-xmlwriter \        
RUN rm -Rf /var/cache/apk/*


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY nginx.conf /etc/nginx/nginx.conf

COPY supervisord.conf /etc/supervisord.conf


RUN mkdir -p /app


WORKDIR /app

RUN chmod -R 755 /app

EXPOSE 80 443

COPY run.bash /tmp
RUN chmod +x /tmp/run.bash

CMD ["supervisord", "-c", "/etc/supervisord.conf"]