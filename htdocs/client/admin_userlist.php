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

   if( $nickname != "" && $nick != "" )
   {
      $query = "UPDATE client_members SET
		nickname='$nick',
		email='$email',
		date='$date',
		paid='$paid',
		comment='$comment'
	WHERE nickname='$nickname'";
      $DB->query($query);
   }

   echo "here is the user list:<hr><table BORDER=1>";
   echo "<tr><td>nick</td><td>email</td><td>date</td><td>paid</td><td>comment</td><td>&nbsp;</td><td>&nbsp;</td></tr>";

   $allPaid = 0;
   $allUser = 0;

   $query = "SELECT * FROM `client_members`";
   $DB->query($query);
   while( $row = $DB->fetch_row() )
   {
      $nick = $row[nickname];

      echo "<tr>
<td>$nick</td>
<td>$row[email]</td>
<td>$row[date]</td>
<td>$row[paid]</td>
<td>$row[comment]</td>
<td><A HREF=\"admin_edituser.php?nick=$nick\">Edit user</A></td>
<td><A HREF=\"admin_deluser.php?nick=$nick\">Delete user</A></td>
	</tr>";

      $allPaid += $paid;
      $allUser ++;
   }

   echo "</table>";
   echo "<hr><b>Total users:</b> ". $allUser . "<br><b>Total paid:</b> " . $allPaid;
?>
