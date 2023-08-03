# Use a PHP base image
FROM php:latest

# Set the working directory inside the container
WORKDIR /var/www/html/

# Copy your PHP files to the container
COPY . /var/www/html/

# Expose the port your API will run on (change it to the actual port your API listens on)
EXPOSE 80

# Start the PHP server when the container runs
CMD ["php", "-S", "127.0.0.1:80"]