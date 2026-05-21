FROM php:8.2-apache

# Salin berkas skrip IPTV Anda ke dalam server apache
COPY kocak.php /var/www/html/index.php

# Aktifkan ekstensi cURL wajib untuk mengambil token Vidio
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install curl

# Atur agar server berjalan di port otomatis yang diminta oleh Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 80
