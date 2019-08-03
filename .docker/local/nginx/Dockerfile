FROM nginx:alpine

RUN apk update && apk upgrade
RUN apk add bash git curl

RUN rm /etc/nginx/conf.d/*
COPY nginx.conf /etc/nginx/nginx.conf
COPY conf.d/* /etc/nginx/conf.d/

EXPOSE 80

VOLUME /var/www
VOLUME /var/run/php
