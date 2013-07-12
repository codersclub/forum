<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Admin wrapper script
|   > Script written by Matt Mecham
|   > Date started: 1st March 2002
|
+--------------------------------------------------------------------------
*/

/*-----------------------------------------------
  USER CONFIGURABLE ELEMENTS
 ------------------------------------------------*/

// Are we running this on a lycos / tripod server?
// If so, change the following to a 1.

$is_on_tripod = 0;

// Root path

define( 'ROOT_PATH', './' );

// Check IP address to see if they match?
// this may cause problems for users on proxies
// where the IP address changes during a session

$check_ip = 1;

// Use GZIP content encoding for fast page generation
// in the admin center?

$use_gzip = 1;

// Enable module usage?
// (Vital for some mods and IPB enhancements)

define ( 'USE_MODULES', 1 );
if(!defined("IN_ACP")) define("IN_ACP",1);

/*-----------------------------------------------
  NO USER EDITABLE SECTIONS BELOW
 ------------------------------------------------*/

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

if ( $is_on_tripod != 1 )
{
	if (function_exists('ini_get'))
	{
		$safe_switch = @ini_get("safe_mode") ? 1 : 0;
	}
	else
	{
		$safe_switch = 1;
	}
}
else
{
	$safe_switch = 1;
}

define( 'SAFE_MODE_ON', $safe_switch );

if (function_exists("set_time_limit") == 1 and SAFE_MODE_ON == 0)
{
  @set_time_limit(0);
}

require_once ROOT_PATH . "autoload.php";
require ROOT_PATH . "sources/functions.php";
require ROOT_PATH . "sources/display.php";

/*-----------------------------------------------
  Import $INFO
 ------------------------------------------------*/


require ROOT_PATH."../conf_global.php";

$INFO['mm_groups'] = array(
	$INFO['admin_group'],
	$INFO['supermoderator_group'],
	$INFO['moderator_group'],
	$INFO['comoderator_group']
);

$Debug = Debug::instance();
$Debug->startTimer();

Ibf::registerApplication(new AdminApplication());

$ibforums = Ibf::app();
$std     = &$ibforums->functions;

$ibforums->init();


/*-----------------------------------------------
  Make sure our data is reset on each invocation
 ------------------------------------------------*/
$MEMBER          = array();
$SESSION_ID      = "";
$SKIN            = "";

// Put an end to insane thoughs before they begin
$MEMBER_NAME     = "";
$MEMBER_PASSWORD = "";
$MEMBER_EMAIL    = "";
$UserName        = "";
$PassWord        = "";


/*-----------------------------------------------
  Load up our classes (compiled into one package)
 ------------------------------------------------*/

$IN = $std->parse_incoming();

/*-----------------------------------------------
  Steralize our FORM and GET input
 ------------------------------------------------*/

$IN['AD_SESS'] = $_POST['adsess'] ? $_POST['adsess'] : $_GET['adsess'];

$IN['AD_SESS'] = $std->clean_value( $IN['AD_SESS'] );

/*-----------------------------------------------
  Import $PAGES and $CATS
 ------------------------------------------------*/

require ROOT_PATH."sources/Admin/admin_pages.php";


/*-----------------------------------------------
  Import Skinable elements
 ------------------------------------------------*/
require ROOT_PATH."sources/Admin/admin_skin.php";

$SKIN = new admin_skin();

$skin_universal = &$SKIN; //To keep functions.php happy

/*-----------------------------------------------
  Import Admin Functions
 ------------------------------------------------*/

require ROOT_PATH."sources/Admin/admin_functions.php";

$ADMIN = new admin_functions();

/*-----------------------------------------------
  Load up our database library
 ------------------------------------------------*/

// Song * check access

$ips_file = "../ip_table.php";

if ( file_exists( $ips_file ) )
{
	$log = 0;

	$FH = fopen( $ips_file, 'r' );
	$ips = fread( $FH, filesize($ips_file) );
	fclose($FH);

	if ( $ips and $IN['IP_ADDRESS'] )
	{
		$ips = str_replace( "\r", "", $ips);
		$ips = explode( "\n", $ips );

		foreach ($ips as $ip)
		{
			$ip = preg_replace( "/\n/", '' ,$ip );
			$ip = preg_replace( "/\#(.+?)$/", '' ,$ip );
			$ip = preg_replace( "/\s*$/", '' ,$ip );

			if ( $ip ) {
				// New IP algorithm by archimed7592
				$ip = preg_quote($ip, "/");
				$ip = preg_replace( "/\\\\\\*/", '[0-9]{1,3}' , $ip );
				if ( preg_match( '/^'.$ip.'/', $IN['IP_ADDRESS'] ) )
        			{
        				$log = 1;
        				break;
        			}
			}
		}
	}

	if ( !$log )
	{
		$env = "";
		foreach( $IN as $name => $line ) $env .= $name."=".$line."<br>";

		$stmt = $ibforums->db->query("INSERT INTO ibf_admin_foreign_visits (dt,ip_address,content) VALUES (CURRENT_TIMESTAMP,'".$IN['IP_ADDRESS']."','".mysql_escape_string($env)."')");

		fatal_error("You do not have access to the administrative CP");
	}
}

// Song * check access

//------------------------------------------------
// Fix up the "show" ID's for the menu tree...
//
// show=1,4,5 holds the current ID's, clicking on a
// collapse link creates out=4 - "4" is then removed
// from the show link.
//
// Good eh?
//------------------------------------------------


//------------------------------------------------
// Sort out settings cookie
//------------------------------------------------

$INFO['menu']    = 0;
$INFO['tx']      = 80;
$INFO['ty']      = 40;
$INFO['preview'] = "";

if ( $cookie = $std->my_getcookie('acpprefs') )
{
	list( $INFO['menu'], $INFO['tx'], $INFO['ty'], $INFO['preview'] ) = explode( ",", $cookie );
}

//------------------------------------------------
// ME 'N U!
//------------------------------------------------

if ( ! isset($IN['show']) )
{
	$IN['show'] = $std->my_getcookie('acpmenu');
}

if ($IN['show'] == 'none')
{
	$IN['show'] = "";
}
else if ($IN['show'] == 'all')
{
	$IN['show']     = "";

	foreach($CATS as $cid => $name)
	{
		$IN['show'] .= $cid.',';
	}
}
else
{
	$IN['show'] = preg_replace( "/(?:^|,)".$IN['out']."(?:,|$)/", ",", $IN['show'] );
	$IN['show'] = preg_replace( "/,,/" , "" , $IN['show'] );
	$IN['show'] = preg_replace( "/,$/" , "" , $IN['show'] );
	$IN['show'] = preg_replace( "/^,/" , "" , $IN['show'] );
}


//------------------------------------------------
// Admin.php Rules:
//
// No adsess number?
// -----------------
//
// Then we log into the admin CP
//
// Got adsess number?
// ------------------
//
// Then we check the cookie "ad_login" for a session key.
//
// If this session key matches the one stored in the admin_sessions
// table, then we check the data against the data stored in the
// profiles table.
//
// The session key and ad_sess keys are generated each time we log in.
//
// If we don't have a valid adsess in the URL, then we ask for a log in.
//
//------------------------------------------------

$session_validated = 0;
$this_session      = array();

$validate_login = 0;

if ($IN['login'] != 'yes') {

	if ( (!$IN['adsess']) or (empty($IN['adsess'])) or (!isset($IN['adsess'])) or ($IN['adsess'] == "") )
	{
		//----------------------------------
		// No URL adsess found, lets log in.
		//----------------------------------

		do_login("No administration session found");
	}
	else
	{
		//----------------------------------
		// We have a URL adsess, lets verify...
		//----------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_admin_sessions WHERE ID='".$IN['adsess']."'");
		$row = $stmt->fetch();

		if ($row['ID'] == "")
		{
			//----------------------------------
			// Fail-safe, no DB record found, lets log in..
			//----------------------------------

			do_login("Could not retrieve session record");
		}
		else if ($row['MEMBER_ID'] == "")
		{

			//----------------------------------
			// No member ID is stored, log in!
			//----------------------------------

			do_login("Could not retrieve a valid member id");

		}
		else
		{
			//----------------------------------
			// Key is good, check the member details
			//----------------------------------

			$stmt = $ibforums->db->query("SELECT * FROM ibf_members WHERE id='".$row['MEMBER_ID']."'");
			$MEMBER = $stmt->fetch();

			if ($MEMBER['id'] == "")
			{

				//----------------------------------
				// Ut-oh, no such member, log in!
				//----------------------------------

				do_login("Member ID invalid");

			}
			else
			{
				//----------------------------------
				// Member found, check passy
				//----------------------------------

				if ($row['SESSION_KEY'] != $MEMBER['password'])
				{
					//----------------------------------
					// Passys don't match..
					//----------------------------------

					do_login("Session member password mismatch");

				}
				else
				{
					//----------------------------------
					// Do we have admin access?
					//----------------------------------

					$stmt = $ibforums->db->query("SELECT * FROM ibf_groups WHERE g_id='".$MEMBER['mgroup']."'");

					$GROUP = $stmt->fetch();

					if ($GROUP['g_access_cp'] != 1)
					{
						do_login("You do not have access to the administrative CP");
					}
					else
					{
						$session_validated = 1;
						$this_session      = $row;
					}
				}
			}
		}
	}
}
else
{
	//----------------------------------
	// We must have submitted the form
	// time to check some details.
	//----------------------------------

	if ( empty($IN['username']) )
	{
		do_login("You must enter a username before proceeding");
	}

	if ( empty($IN['password']) )
	{
		do_login("You must enter a password before proceeding");
	}

	//----------------------------------
	// Attempt to get the details from the
	// DB
	//----------------------------------

	$stmt = $ibforums->db->query("SELECT name, password, id, mgroup FROM ibf_members WHERE LOWER(name)='".strtolower($IN['username'])."'");
	$mem = $stmt->fetch();

	if ( empty($mem['id']) )
	{
		do_login("Could not find a record matching that username, please check the spelling");
	}

	$pass    = md5( $IN['password'] );

	if ($pass != $mem['password'])
	{
		do_login("The password entered did not match the one in our records");
	}
	else
	{
		$stmt = $ibforums->db->query("SELECT * FROM ibf_groups WHERE g_id='".$mem['mgroup']."'");

		$GROUP = $stmt->fetch();

		if ($GROUP['g_access_cp'] != 1)
		{
			do_login("You do not have access to the administrative CP");
		}
		else
		{

			//----------------------------------
			// All is good, rejoice as we set a
			// session for this user
			//----------------------------------

			$sess_id = md5( uniqid( microtime() ) );

			$db_string = [
					'ID'           => $sess_id,
					'IP_ADDRESS'   => $IN['IP_ADDRESS'],
					'MEMBER_NAME'  => $mem['name'],
					'MEMBER_ID'    => $mem['id'],
					'SESSION_KEY'  => $pass,
					'LOCATION'     => 'index',
					'LOG_IN_TIME'  => time(),
					'RUNNING_TIME' => time(),
			];

			$stmt = $ibforums->db->insertRow("ibf_admin_sessions", $db_string);

			$IN['AD_SESS'] = $sess_id;

			// Print the "well done page"

			$ADMIN->page_title = "Log in successful";

			$ADMIN->page_detail = "Taking you to the administrative control panel";

			$ADMIN->html .= $SKIN->start_table("Proceed");

			$ADMIN->html .= "<tr><td id='tdrow1'><meta http-equiv='refresh' content='2; url=".$INFO['board_url']."/admin.".$INFO['php_ext']."?adsess=".$IN['AD_SESS']."'><a href='".$INFO['board_url']."/admin.".$INFO['php_ext']."?adsess=".$IN['AD_SESS']."'>( Click here if you do not wish to wait )</a></td></tr>";

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->output();

		}

	}

}


//----------------------------------
// Ok, so far so good. If we have a
// validate session, check the running
// time. if it's older than 2 hours,
// ask for a log in
//----------------------------------


if ($session_validated == 1)
{
	if ($this_session['RUNNING_TIME'] < ( time() - 60*60*2) )
	{
		$session_validated = 0;
		do_login("This administration session has expired");
	}

	//------------------------------
	// Are we checking IP's?
	//------------------------------

	else if ($check_ip == 1)
	{
		if ($this_session['IP_ADDRESS'] != $IN['IP_ADDRESS'])
		{
			$session_validated = 0;
			do_login("Your current IP address does not match the one in our records");
		}
	}
}

if ($session_validated == 1 )
{
	//------------------------------
	// If we get this far, we're good to go..
	//------------------------------

	$IN['AD_SESS'] = $IN['adsess'];

	//------------------------------
	// Lets update the sessions table:
	//------------------------------

	$ibforums->db->exec("UPDATE ibf_admin_sessions SET RUNNING_TIME='".time()."', LOCATION='".$IN['act']."' WHERE MEMBER_ID='".$MEMBER['id']."' AND ID='".$IN['AD_SESS']."'");

	do_admin_stuff();

}
else
{
	//------------------------------
	// Session is not validated...
	//------------------------------

	do_login("Session not validated - please attempt to log in again");

}



function do_login($message="") {
	global $IN, $ADMIN, $SKIN, $std;

	$ibforums = Ibf::app();

	//-------------------------------------------------------
	// Remove all out of date sessions, like a good boy. Woof.
	//-------------------------------------------------------

	$cut_off_stamp = time() - 60*60*2;

	$ibforums->db->exec("DELETE FROM ibf_admin_sessions WHERE RUNNING_TIME < $cut_off_stamp");

	//+------------------------------------------------------

	$ADMIN->page_detail = "You must have administrative access to successfully log into the Invision Board Admin CP.<br>Please enter your forums username and password below";

	if ($message != "")
	{
		$ADMIN->page_detail .= "<br><br><span style='color:red;font-weight:bold'>$message</span>";
	}

	$ADMIN->html .= "<script language='javascript'>
					  <!--
					  	if (top.location != self.location) { top.location = self.location }
					  //-->
					 </script>
					 ";
	//+------------------------------------------------------
	//| SEMI-AUTO Log in ma-thingy?
	//+------------------------------------------------------

	$name  = "";
	$extra = "";

	$mid = intval( $std->my_getcookie('member_id') );

	if ( $mid > 0 )
	{
		$stmt = $ibforums->db->query("SELECT m.id, m.name, m.mgroup, g.g_access_cp FROM ibf_members m, ibf_groups g WHERE m.id=$mid AND g.g_id=m.mgroup AND g.g_access_cp=1");

		if ( $r = $stmt->fetch() )
		{
			$name  = $r['name'];
			$extra = 'onload="document.theAdminForm.password.focus();"';
		}
	}

	//+------------------------------------------------------
	//| SHW DA FRM (txt msg stylee)
	//+------------------------------------------------------

	$ADMIN->html .= $SKIN->start_form( array( 1 => array('login', 'yes') ) );

	$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
	$SKIN->td_header[] = array( "&nbsp;"  , "60%" );

	$ADMIN->html .= $SKIN->start_table( "Verification Required" );

	$ADMIN->html .= $SKIN->add_td_row( array( "Your Forums Username:",
						  "<input type='text' style='width:100%' name='username' value='$name'>",
						 )      );

	$ADMIN->html .= $SKIN->add_td_row( array( "Your Forums Password:",
						  "<input type='password' style='width:100%' name='password' value=''>",
						 )      );

	$ADMIN->html .= $SKIN->end_form("Log in");

	$ADMIN->html .= $SKIN->end_table();

	$SKIN->top_extra = $extra;

	$ADMIN->no_jump = 1;

	$ADMIN->output();

}





function do_admin_stuff() {
	global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;

	if ( $INFO['ipb_reg_number'] )
	{
		list( $a, $b, $c, $d, $e ) = explode( '-', $INFO['ipb_reg_number'] );

		if ( strlen($e) > 9 )
		{
			if ( time() > $e )
			{
				$ADMIN->rebuild_config( array( 'ipb_reg_number' => '' ) );
			}
		}
	}


	/*----------------------------------
	  What do you want to require today?
	------------------------------------*/

	$choice = array(
			 "idx"      => "doframes",
			 "menu"     => "menu",
			 "index"    => "index",
			 "cat"      => "categories",
			 "forum"    => "forums",
			 "mem"      => "member",
			 'group'    => "groups",
			 'mod'      => 'moderator',
			 'op'       => 'settings',
			 'help'     => 'help',
			 'skin'     => 'skins',
			 'wrap'     => 'wrappers',
			 'style'    => 'stylesheets',
			 'image'    => 'imagemacros',
			 'sets'     => 'stylesets',
			 'templ'    => 'templates',
			 'rtempl'   => 'remote_template',
			 'lang'     => 'languages',
			 'import'   => 'skin_import',
			 'modlog'   => 'modlogs',
			 'field'   => 'profilefields',
			 'stats'   => "statistics",
			 'quickhelp' => "quickhelp",
			 'adminlog'  => "adminlogs",
			 'ips'       => 'ips',
			 'mysql'     => 'mysql',
			 'pin'       => 'plugins',
			 'emaillog'  => 'emaillogs',
			 'multimod'  => 'multi_moderate',
			 'prefs'     => "prefs",
			 'spiderlog' => "spiderlogs",
			 'warnlog'      => "warnlogs",
			 'msubs'     => 'subsmanager',
			 'store'     => 'store',
			 'syntax'   => 'syntax', // Leprecon
			 'sskin'    => 'sskin', // SergeS
                        //-------------------------------------
                        // RSS choise (c) vot
                        // Date added: 04.07.2006
                        //-------------------------------------
                        'rss_sources'      => 'rss',
                        'rss_add_source'   => 'rss',
                        'rss_edit_source'  => 'rss',
                        'rss_save_source'  => 'rss',
                        'rss_del_source'   => 'rss',
                        'rss_channels'     => 'rss',
                        'rss_add_channel'  => 'rss',
                        'rss_edit_channel' => 'rss',
                        'rss_save_channel' => 'rss',
                        'rss_del_channel'  => 'rss',
                        'rss_logs'         => 'rss',
                        'rss_del_log'      => 'rss',
			   );


	/***************************************************/

	$IN['act'] = $IN['act'] == '' ? "idx" : $IN['act'];

	// Check to make sure the array key exits..
	if (! isset($choice[$IN['act']]) )
	{
		$IN['act'] = 'idx';
	}

	// Require and run

	if ($IN['act'] == 'idx')
	{
		print $SKIN->frame_set();
		exit;
	}
	else if ($IN['act'] == 'menu')
	{
		$ADMIN->menu();
	}
	else
	{
		require ROOT_PATH."sources/Admin/ad_".$choice[$IN['act']].".php";
	}

}



//+-------------------------------------------------
// GLOBAL ROUTINES
//+-------------------------------------------------

function fatal_error($message="", $help="") {
	echo("$message<br><br>$help");
	exit;
}
