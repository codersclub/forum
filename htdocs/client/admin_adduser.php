<? 

// (c) Alexander Vaulin

   require "../../conf_global.php";

define( 'ROOT_PATH', "./" );

// Enable module usage?
// (Vital for some mods and IPB enhancements)

define ( 'USE_MODULES', 1 );

//-----------------------------------------------
// NO USER EDITABLE SECTIONS BELOW
//-----------------------------------------------

error_reporting  (E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

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
   $DB->obj['sql_charset']      = $INFO['sql_charset'];
   $DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];
   $DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;
   $DB->connect();

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
      $query = "INSERT INTO `client_members` 
         (`nickname`, `email`, `date`, `paid`, `comment`)
         VALUES ('$nick', '$email', '$date', '$paid', '$comment')";
      $DB->query($query);

      if( mysql_affected_rows() > 0 )
         echo "User <b>$nick</b> is added";
      else
         echo "It was an error. Probably <b>$nick</b> is already added";
      echo "<hr>";
   }
   else
   {
      $arrDate = getdate( time() );
      $s2 = "-" . $arrDate["mon"];
      $s3 = "-" . $arrDate["mday"];
      $date = "" . $arrDate["year"] . $s2 . $s3;
   }

echo <<< avStr
Добавляем пользователя:
<FORM ACTION="admin_adduser.php" METHOD="post">
<table>
<tr><td>nick:</td><td><INPUT TYPE="text" NAME="nick" SIZE="40" MAXLENGTH="30"></td></tr>
<tr><td>e-mail:</td><td><INPUT TYPE="text" NAME="email" SIZE="40" MAXLENGTH="50"></td></tr>
<tr><td>date:</td><td><INPUT TYPE="text" NAME="date" VALUE=$date SIZE="40" MAXLENGTH="10"> for example: 2003-7-25</td></tr>
<tr><td>оплачено:</td><td><INPUT TYPE="text" NAME="paid" VALUE="20" SIZE="40" MAXLENGTH="10"> гривен</td></tr>
<tr><td>comment:</td><td><INPUT TYPE="text" NAME="comment" SIZE="40" MAXLENGTH="100"> любые комментарии</td></tr>
</table>
<br><INPUT NAME=SUBMIT TYPE="submit" VALUE="Add user">
</form>
avStr;
?>
