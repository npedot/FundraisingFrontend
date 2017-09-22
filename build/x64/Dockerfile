FROM yukoff/php-32bit:7.1

RUN apt-get update && \
	# for intl
	apt-get install -y libicu-dev && \
	# for curl
	apt-get install -y libcurl4-openssl-dev && \
	# for xml
	apt-get install -y libxml2-dev && \
	docker-php-ext-configure intl --enable-intl && \
	docker-php-ext-install intl curl xml pdo_mysql mbstring
