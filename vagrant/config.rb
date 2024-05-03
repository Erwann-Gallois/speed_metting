# config.rb

# Define your variables
$MEMORY_SIZE = "2024"
$CPU_COUNT = 4

$TIMEZONE = "Europe/Paris"

$HTTP_PORT_GUEST   = nil                        # If nil, default forwarded port to 8080
$HTTP_PORT_HOST    = nil                        # If nil, default forwarded port to 8080

$PHP_VERSION    = 8.3                           # 5.6, 7.4, 8.0, 8.1, 8.2 or 8.3 accepted values
$PHP_MODULES    = ""                            # space separated module (ex: "ldap session")
$PHP_WITH_OCI8  = false                         # Install OCI8 module or not (accepted values true or false)

$WITH_MYSQL         = true
$MYSQL_PORT_HOST    = nil                       # If nil, default forwarded port to 3306
$MYSQL_DB_DATABASE  = "speedmeetings"
$MYSQL_DB_USERNAME  = "root"
$MYSQL_DB_PASSWORD  = "admin"

$WITH_POSTGRESQL            = false
$POSTGRESQL_PORT_HOST       = nil               # If nil, default forwarded port to 5432
$POSTGRESQL_DB_DATABASE     = "db_test"
$POSTGRESQL_DB_USERNAME     = "db_username"
$POSTGRESQL_DB_PASSWORD     = "db_password"

$EXTRAS_PKG   = ""				# Supplementary distrib packages needed
