# Use a pre-built PHP-FPM with Nginx image
FROM php:latest

# Install Nginx
RUN apt-get update && \
    apt-get install -y nginx

# Remove the default Nginx configuration
RUN rm /etc/nginx/sites-available/default

# Copy your PHP files to the container
COPY . /var/www/html/

# Expose the port for Nginx
EXPOSE 80

# Start Nginx and PHP-FPM when the container runs
CMD service nginx start && php-fpm