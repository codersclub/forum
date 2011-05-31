#########################################
# RSS Bot Settings

$debug = 1;		# =1 for debug printing; =0 for quiet mode

#########################################
# MySQL parameters:

#$db_host     = 'localhost;mysql_socket=/tmp/maria.sock'; # Connect by socket
$db_host     = 'localhost';   # Connect by port
$db_port     = '3306';        # DB Port. Note: Comment this if you use SOCKET!!!
$db_user     = 'root';        # Database User Name
$db_password = '';            # Database Password
$db_name     = 'invision';    # Whatever database you uses

$search_sql_method = 'index'; # = conf_global.php::$INFO['search_sql_method']

$posting_type = 'FULL';       # News Type: FULL | SHORT
$user_id      = 51823;        # Posting User ID
$user_name    = 'RSS_Bot';    # Posting User Name
