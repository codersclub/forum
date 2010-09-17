<? 

// (c) Alexander Vaulin

define( 'ROOT_PATH', "./" );

// Enable module usage?
// (Vital for some mods and IPB enhancements)

define ( 'USE_MODULES', 1 );

//-----------------------------------------------
// NO USER EDITABLE SECTIONS BELOW
//-----------------------------------------------

error_reporting  (E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

   require "../../conf_global.php";

   require "../sources/functions.php";
   $std   = new FUNC;
   $print = new display();
   $sess  = new session();

   $to_require = "../sources/Drivers/".$INFO['sql_driver'].".php";
   require ($to_require);

// connect to database
   $DB = new db_driver;
   $DB->obj['sql_database']     = $INFO['sql_database'];
   $DB->obj['sql_user']         = $INFO['sql_user'];
   $DB->obj['sql_pass']         = $INFO['sql_pass'];
   $DB->obj['sql_host']         = $INFO['sql_host'];
   $DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];
   $DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;
   $DB->connect();

// Authenticate the user
// Authenticate the user
   $curuser = $sess->authorise();
   $userName = $curuser['name'];
   if( $userName != "admin" && $curuser != "avGolf2" )
   {
      echo "You have not rights to access this module";
      exit;
   }

   if( $nick != "" )
   {
      $query = "DELETE FROM `client_members` WHERE `nickname`='$nick'";
      $DB->query($query);

      echo "User <b>$nick</b> is deleted";
      echo "<hr>";
   }

echo <<< avStr
<SCRIPT>
window.location = "admin_userlist.php";
window.location.reload();
</SCRIPT>
avStr;

?>
