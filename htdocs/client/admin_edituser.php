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

   if( $nick == "" )
   {
      echo "Edit whom?";
      exit;
   }

   $query = "SELECT * FROM `client_members` WHERE `nickname`='$nick'";
   $DB->query($query);
   if( $DB->get_num_rows() == 0 )
   {
      echo "<b>$nick</b> is not a member";
      exit;
   }

   $row = $DB->fetch_row();

echo <<< avStr
Редактируем пользователя:
<FORM ACTION="admin_userlist.php" METHOD="post">
<INPUT TYPE="hidden" NAME="nickname" SIZE="40" MAXLENGTH="30" VALUE="$row[nickname]">
<table>
<tr><td>nick:</td>    <td><INPUT TYPE="text" NAME="nick"    SIZE="40" MAXLENGTH="30"  VALUE="$row[nickname]"></td></tr>
<tr><td>e-mail:</td>  <td><INPUT TYPE="text" NAME="email"   SIZE="40" MAXLENGTH="50"  VALUE="$row[email]"></td></tr>
<tr><td>date:</td>    <td><INPUT TYPE="text" NAME="date"    SIZE="40" MAXLENGTH="10"  VALUE="$row[date]"> for example: 2003-7-25</td></tr>
<tr><td>оплачено:</td><td><INPUT TYPE="text" NAME="paid"    SIZE="40" MAXLENGTH="10"  VALUE="$row[paid]"></td></tr>
<tr><td>comment:</td> <td><INPUT TYPE="text" NAME="comment" SIZE="40" MAXLENGTH="100" VALUE="$row[comment]"></td></tr>
</table>
<br><INPUT NAME=SUBMIT TYPE="submit" VALUE="Update user">
</form>
avStr;
?>
