#FROM 192.168.101.160:5000/php-oracle
FROM docker-registry.yes.com.sv/rlap_visor

# Instalar extensiones de PHP para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev

# Instalar nano
RUN apt-get install -y nano

# Instalar extensiones de PHP para PostgreSQL
RUN apt-get install php-pgsql -y

WORKDIR /var/www/html
COPY . .