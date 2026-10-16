# Container for preview hosting on Render (render.com). Not needed for Hostinger.
# PHP 8.4 + Apache, serving only the public/ folder.
FROM php:8.4-apache

RUN a2enmod rewrite headers expires deflate

# Production PHP settings: errors logged, never shown; PHP version hidden
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && printf 'expose_php = Off\npost_max_size = 2M\n' > "$PHP_INI_DIR/conf.d/site.ini"

# Apache: document root is public/, .htaccess files allowed, server version hidden
COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf
RUN printf 'ServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-enabled/zz-security.conf

WORKDIR /var/www/site

# The site itself (.env, node_modules and source material are excluded by .dockerignore).
# Bootstrap is already vendored in public/assets/vendor, so no npm build is needed.
COPY . .
RUN mkdir -p app/storage/messages app/storage/ratelimit app/storage/logs \
 && chown -R www-data:www-data app/storage

COPY docker/start.sh /usr/local/bin/start-site
RUN chmod +x /usr/local/bin/start-site

CMD ["start-site"]
