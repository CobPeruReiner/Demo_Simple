# PHP + Apache (Debian)
# FROM php:8.2-apache

# Paquetes + extensiones PHP necesarias
# RUN apt-get update && apt-get install -y \
# libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip curl \
# && docker-php-ext-configure gd --with-freetype --with-jpeg \
# && docker-php-ext-install -j"$(nproc)" gd zip pdo_mysql mysqli \
# && rm -rf /var/lib/apt/lists/*

# Mod_rewrite para .htaccess
# RUN a2enmod rewrite

# Quitar warning de FQDN
# RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Ruta de trabajo
# WORKDIR /var/www/html

# Copiar código
# COPY . /var/www/html

# Sesiones: carpeta y permisos
# RUN mkdir -p /var/lib/php/sessions \
# && chown -R www-data:www-data /var/lib/php/sessions \
# && echo "session.save_path=/var/lib/php/sessions" > /usr/local/etc/php/conf.d/99-sessions.ini

# Permisos del proyecto
# RUN chown -R www-data:www-data /var/www/html \
# && find /var/www/html -type d -exec chmod 755 {} \; \
# && find /var/www/html -type f -exec chmod 644 {} \;

# Healthcheck (usa curl)
# HEALTHCHECK --interval=30s --timeout=5s --retries=5 CMD curl -f http://localhost/ || exit 1

# PHP + Apache (Debian)
FROM php:8.2-apache

# # Paquetes + extensiones PHP necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
  libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip curl ca-certificates \
  #   # Certbot y módulo SSL de Apache
  certbot python3-certbot-apache openssl \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd zip pdo_mysql mysqli \
  && a2enmod rewrite headers ssl \
  && rm -rf /var/lib/apt/lists/*

# Opcional: seguridad básica de Apache
RUN { \
  echo "ServerName geocampo.online"; \
  echo "ServerTokens Prod"; \
  echo "ServerSignature Off"; \
  } >> /etc/apache2/apache2.conf

# # Permitir .htaccess
RUN bash -lc 'set -e; \
  CONF="/etc/apache2/conf-available/allow-override.conf"; \
  echo "<Directory /var/www/html/>" > ${CONF}; \
  echo "    AllowOverride All" >> ${CONF}; \
  echo "    Require all granted" >> ${CONF}; \
  echo "</Directory>" >> ${CONF}; \
  a2enconf allow-override \
  '

WORKDIR /var/www/html
COPY . /var/www/html

# # Ajustes PHP útiles
RUN mkdir -p /var/lib/php/sessions \
  && chown -R www-data:www-data /var/lib/php/sessions /var/www/html \
  && { \
  echo "session.save_path=/var/lib/php/sessions"; \
  echo "date.timezone=America/Lima"; \
  echo "upload_max_filesize=64M"; \
  echo "post_max_size=64M"; \
  echo "memory_limit=256M"; \
  echo "max_execution_time=60"; \
  } > /usr/local/etc/php/conf.d/99-custom.ini

HEALTHCHECK --interval=30s --timeout=5s --retries=5 CMD curl -fsS http://localhost/ || exit 1