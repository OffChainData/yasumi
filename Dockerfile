FROM inspectablocktech/php:v0.3
WORKDIR /var/www/app
CMD php -S 0.0.0.0:8000 server.php
