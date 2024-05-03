# Set timezone
timedatectl set-timezone $TIMEZONE

# Define non interactive package manager
export DEBIAN_FRONTEND=noninteractive

apt-get install -y wget unzip

#######################################
# Install sury.org repository for PHP #
#######################################
if [ ! -e /etc/apt/sources.list.d/php.list ]; then
	apt-get install -y apt-transport-https lsb-release ca-certificates
	wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
	echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list 
	apt-get update
fi

#############################
# Install and configure PHP #
#############################
if [ -z "${PHP_VERSION}" ]; then
	PHP_VERSION=8.3
fi
if [ ! -e /etc/php/${PHP_VERSION}/installed ]; then
	echo "Install PHP "${PHP_VERSION}
	apt-get install -y php${PHP_VERSION}-apcu php${PHP_VERSION}-cli php${PHP_VERSION}-curl php${PHP_VERSION}-dev php${PHP_VERSION}-dom php${PHP_VERSION}-intl php${PHP_VERSION}-mbstring php${PHP_VERSION}-xdebug php${PHP_VERSION}-zip
	sed -i "s|;date.timezone =|date.timezone = $TIMEZONE|g" /etc/php/${PHP_VERSION}/cli/php.ini
	cat <<EOF >> /etc/php/${PHP_VERSION}/mods-available/xdebug.ini
xdebug.start_with_request=yes
xdebug.mode=debug
xdebug.client_host = localhost
xdebug.client_port = 9003
xdebug.output_dir = "/tmp"
;xdebug.log=/tmp/xdebug.log
xdebug.idekey=VSCODE
EOF
	wget https://getcomposer.org/installer -qO /tmp/composer-setup.php
	EXPECTED_CHECKSUM=$(curl -s https://composer.github.io/installer.sig)
	ACTUAL_CHECKSUM=$(sha384sum /tmp/composer-setup.php -z | cut -d" " -f 1)
	if [ "$ACTUAL_CHECKSUM" == "$EXPECTED_CHECKSUM" ]; then
		php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer
		rm /tmp/composer-setup.php 
	else
		apt-get install -y composer
	fi
	touch /etc/php/${PHP_VERSION}/installed
fi
if [ "${PHP_MODULES}" != "" ]; then
	MODULES=""
	for mod in ${PHP_MODULES}; do
		MODULES="$MODULES php${PHP_VERSION}-$mod"
		apt-get -y install ${MODULES}
	done
fi
unset PHP_MODULES

#################
# Install OCI 8 #
#################
if [ -n "${PHP_WITH_OCI8}" -a "${PHP_WITH_OCI8}" = "true" ]; then
	if [ ! -e /etc/php/${PHP_VERSION}/installed_oci8 ]; then
		case $PHP_VERSION in
			"5.6")
				OCI_VERSION="2.0.12"
				;;
			"7.4")
				OCI_VERSION="2.2.0"
				;;
			"8.0")
				OCI_VERSION="3.0.1"
				;;
			"8.1")
				OCI_VERSION="3.2.1"
				;;
			"8.2"|"8.3")
				OCI_VERSION="3.3.0"
				;;
			*)
				OCI_VERSION=""
				;;
		esac
		wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip -qO /tmp/instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip
		wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip -qO /tmp/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip
		wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sqlplus-linux.x64-21.12.0.0.0dbru.zip -qO /tmp/instantclient-sqlplus-linux.x64-21.12.0.0.0dbru.zip
		unzip -o /tmp/instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip -d /usr/lib/
		unzip -o /tmp/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip -d /usr/lib/
		unzip -o /tmp/instantclient-sqlplus-linux.x64-21.12.0.0.0dbru.zip -d /usr/lib/

		INSTANT_CLIENT=/usr/lib/instantclient_21_12

		if [ ! -z $OCI_VERSION ]; then
			wget -q https://pecl.php.net/get/oci8-$OCI_VERSION.tgz -qO /tmp/oci8-$OCI_VERSION.tgz
		fi
		tar xf /tmp/oci8-$OCI_VERSION.tgz -C /tmp/
		saved_dir=$(pwd)
		cd /tmp/oci8-$OCI_VERSION
		phpize
		./configure --with-oci8=instantclient,$INSTANT_CLIENT
		make install
		cd $saved_dir

		cat <<EOF > /etc/php/${PHP_VERSION}/mods-available/oci8.ini
; configuration for php oci8 module
; priority=20
extension=oci8.so
EOF
		ln -s /etc/php/${PHP_VERSION}/mods-available/oci8.ini /etc/php/${PHP_VERSION}/cli/conf.d/20-oci8.ini

		cat <<EOF > /etc/ld.so.conf.d/oracle_instantclient.conf
$INSTANT_CLIENT
EOF
		ldconfig

		ln -s $INSTANT_CLIENT/sqlplus /usr/bin/sqlplus

		rm -f /tmp/instantclient-*.zip
		rm -Rf /tmp/oci8-$OCI_VERSION*

		apt-get install -y libaio1
		touch /etc/php/${PHP_VERSION}/installed_oci8
	fi
fi

####s####################################
# Install and configure MariaDB server #
########################################
if [ -n "${WITH_MYSQL}" -a "${WITH_MYSQL}" = "true" ]; then
	if [ ! -e /etc/mysql/installed ]; then
		apt-get -y install mariadb-server php${PHP_VERSION}-pdo-mysql
		sed -i 's/.*bind-address.*/bind-address = 0.0.0.0/' /etc/mysql/mariadb.conf.d/50-server.cnf
		systemctl restart mariadb
		touch /etc/mysql/installed
	fi
	mysql --user=root <<EOF
SET PASSWORD FOR 'root'@'localhost' = PASSWORD('vagrant');
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'vagrant';
FLUSH PRIVILEGES;
DELETE FROM mysql.user WHERE User='';
--DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
CREATE DATABASE $MYSQL_DB_DATABASE;
GRANT ALL ON $MYSQL_DB_DATABASE.* TO '$MYSQL_DB_USERNAME' IDENTIFIED BY '$MYSQL_DB_PASSWORD';
FLUSH PRIVILEGES;
EOF
fi

###########################################
# Install and configure PostgreSQL server #
###########################################
if [ -n "${WITH_POSTGRESQL}" -a "${WITH_POSTGRESQL}" = "true" ]; then
	if [ ! -e /etc/postgresql/installed ]; then
		apt-get -y install postgresql php${PHP_VERSION}-pdo-pgsql
		touch /etc/postgresql/installed
	fi
	sudo -u postgres psql <<EOF
CREATE DATABASE ${POSTGRESQL_DB_DATABASE};
CREATE USER ${POSTGRESQL_DB_USERNAME} WITH ENCRYPTED PASSWORD '${POSTGRESQL_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON DATABASE ${POSTGRESQL_DB_DATABASE} TO ${POSTGRESQL_DB_USERNAME};
EOF
fi


###########################################
# 	Install extras packages	          #
###########################################
if [ "${EXTRAS_PKG}" != "" ]; then
	apt-get -y install ${EXTRAS_PKG}
fi
unset EXTRAS_PKG
