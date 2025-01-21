#FROM 192.168.101.160:5000/php-oracle
FROM docker-registry.yes.com.sv/rlap_visor

WORKDIR /var/www/html
COPY . .
