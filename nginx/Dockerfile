FROM nginx

ADD ./default.conf /etc/nginx/conf.d/
ADD ./.htpasswd /etc/nginx/

RUN echo "daemon off;" >> /etc/nginx/nginx.conf

CMD service nginx start