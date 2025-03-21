FROM alphatechsdev/warehouse-be-base:latest
LABEL authors="Li Chaoyi"

# copy current directory to /var/www/html
COPY . /var/www/laravel

# set working directory
WORKDIR /var/www/laravel

# mount .env file from secret
RUN --mount=type=secret,id=dot_env cat /run/secrets/dot_env > .env

# mount public/version file from secret
RUN --mount=type=secret,id=version cat /run/secrets/version > public/version

# run composer install
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-interaction --no-progress --no-suggest --prefer-dist

# run php artisan serve
CMD php artisan schedule:work & php artisan serve --host="0.0.0.0" --port="9000"
