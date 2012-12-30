<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board IPB Click Site
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
|   > IPB Portal Script
|   > Written By Matthew Mecham
|   > Date started: 1st July 2003
|
+--------------------------------------------------------------------------
|
|   >> D-Site CMS modifications
|   >> Copyright (c) Anton 2004-2005
|   >> module version 0.2
|
+--------------------------------------------------------------------------
*/

//-----------------------------------------------
// USER CONFIGURABLE ELEMENTS
//-----------------------------------------------

// Define the path to where the forums directory is
// located. This might be "./forums/" for example.
// Don't forget the trailing slash!

//define( 'ROOT_PATH', "c:/www/d-site/" );
//define( 'ROOT_PATH', "/var/www/htdocs/d-site/" );
//define( 'ROOT_PATH', "d:/vhosts/ibf/" );
//define( 'ROOT_PATH', "/home/forum/htdocs/" );
define( 'ROOT_PATH', "/usr/local/www/votforum/htdocs/" );

  if ( !ROOT_PATH ) {

          print "<h1>Please, set correct ROOT_PATH variable!</h1>";
          exit();
  }


// Define the URL to this script. If you're happy to
// use relative URLs - you can leave this blank.
// You can also rename the script here.

$this_script = 'd-site.php';


//******************************************************************************
//******************************************************************************
//********************  NO USER EDITABLE SECTIONS BELOW  ***********************
//******************************************************************************
//******************************************************************************

error_reporting  (E_ERROR | E_WARNING | E_PARSE); //error_reporting  (E_ALL);


set_magic_quotes_runtime(0);

class Debug {
    function startTimer() {
        global $starttime;
        $mtime = microtime ();
        $mtime = explode (' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $starttime = $mtime;
    }
    function endTimer() {
        global $starttime;
        $mtime = microtime ();
        $mtime = explode (' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $endtime = $mtime;
        $totaltime = round (($endtime - $starttime), 5);
        return $totaltime;
    }
}

class info {

        var $member     = array();
        var $input      = array();
        var $session_id = "";
        var $base_url   = "";
        var $vars       = "";
        var $skin_id    = "0";     // Skin Dir name
        var $skin_rid   = "";      // Real skin id (numerical only)
        var $lang_id    = "en";
        var $skin       = "";
        var $lang       = "";
        var $server_load = 0;
        var $version    = "v1.0";
        var $lastclick  = "";
        var $location   = "";
        var $debug_html = "";
        var $perm_id    = "";
        var $forum_read = array();
        var $topic_cache = "";
        var $session_type = "";

        function info() {
                global $sess, $std, $DB, $INFO, $this_script;

                $this->vars = &$INFO;

                $this->vars['TEAM_ICON_URL']   = $INFO['html_url'] . '/team_icons';
                $this->vars['AVATARS_URL']     = $INFO['html_url'] . '/avatars';
                $this->vars['EMOTICONS_URL']   = $INFO['html_url'] . '/emoticons';
                $this->vars['mime_img']        = $INFO['html_url'] . '/mime_types';
                $this->vars['dynamiclite']     = $this_script.'?';
        }
}

//--------------------------------
// Import $INFO, now!
//--------------------------------

require ROOT_PATH."conf_global.php";

//--------------------------------
// The clocks a' tickin'
//--------------------------------

$Debug = new Debug;
$Debug->startTimer();

//--------------------------------
// Require our global functions
//--------------------------------

require ROOT_PATH."sources/functions.php";

$std   = new FUNC;
$print = new display();
$sess  = new session();

//--------------------------------
// Load the DB driver and such
//--------------------------------

$INFO['sql_driver'] = !$INFO['sql_driver'] ? 'mySQL' : $INFO['sql_driver'];

$to_require = ROOT_PATH."sources/Drivers/".$INFO['sql_driver'].".php";
require ($to_require);

$DB = new db_driver;

$DB->obj['sql_database']     = $INFO['sql_database'];
$DB->obj['sql_user']         = $INFO['sql_user'];
$DB->obj['sql_pass']         = $INFO['sql_pass'];
$DB->obj['sql_host']         = $INFO['sql_host'];
$DB->obj['sql_charset']      = $INFO['sql_charset'];
$DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];

$DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;

// Get a DB connection

$DB->connect();

//--------------------------------
// Wrap it all up in a nice easy to
// transport super class
//--------------------------------

$ibforums             = new info();

//--------------------------------
//  Set up our vars
//--------------------------------

$ibforums->input      = $std->parse_incoming();

//--------------------------------
//  The rest :D
//--------------------------------

$ibforums->member     = $sess->authorise();
$ibforums->skin       = $std->load_skin();
$ibforums->lastclick  = $sess->last_click;
$ibforums->location   = $sess->location;
$ibforums->session_id = $sess->session_id;


//--------------------------------
//  Set up the session ID stuff
//--------------------------------

if ( $ibforums->session_type == 'cookie' )
{
        $ibforums->session_id = "";
        $ibforums->base_url   = $ibforums->vars['board_url'].'/index.'.$ibforums->vars['php_ext'].'?';
}
else
{
        $ibforums->base_url = $ibforums->vars['board_url'].'/index.'.$ibforums->vars['php_ext'].'?s='.$ibforums->session_id.'&amp;';
}

$ibforums->js_base_url = $ibforums->vars['board_url'].'/index.'.$ibforums->vars['php_ext'].'?s='.$ibforums->session_id.'&';

//--------------------------------
//  Set up the skin stuff
//--------------------------------

$ibforums->skin_rid   = $ibforums->skin['set_id'];
$ibforums->skin_id    = 's'.$ibforums->skin['set_id'];

$ibforums->vars['img_url']   = $ibforums->vars['board_url'].'/style_images/' . $ibforums->skin['img_dir'];

//--------------------------------
//  Set up our language choice
//--------------------------------

if ($ibforums->vars['default_language'] == "")
{
        $ibforums->vars['default_language'] = 'en';
}

$ibforums->lang_id = $ibforums->member['language'] ? $ibforums->member['language'] : $ibforums->vars['default_language'];

if ( ($ibforums->lang_id != $ibforums->vars['default_language']) and (! is_dir( ROOT_PATH."lang/".$ibforums->lang_id ) ) )
{
        $ibforums->lang_id = $ibforums->vars['default_language'];
}

$ibforums->lang = $std->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);
$ibforums->lang = $std->load_words($ibforums->lang, 'lang_csite', $ibforums->lang_id );

//--------------------------------
//  Load D-Site add-on modules
//--------------------------------

require ROOT_PATH."sources/dynamiclite/mod_nav.php";
require ROOT_PATH."sources/dynamiclite/mod_cat.php";

require ROOT_PATH."sources/Post.php";

require ROOT_PATH."sources/dynamiclite/mod_art.php";
require ROOT_PATH."sources/dynamiclite/mod_usr.php";
require ROOT_PATH."sources/dynamiclite/mod_misc.php";
require ROOT_PATH."sources/dynamiclite/online_stats.php";


$MISC = new mod_misc;
$USR  = new mod_usr();
$NAV  = new mod_nav();
$CAT  = new mod_cat;
$ART  = new mod_art();


$online_stats = new online_stats();

$skin_universal = $std->load_template('skin_global');


//--------------------------------
// Require and run
//--------------------------------

require ROOT_PATH."sources/dynamiclite/csite.php";

$DSITE = new csite;

$DSITE->click_site();



//  $run = new click_site();


//+-------------------------------------------------
// GLOBAL ROUTINES
//+-------------------------------------------------

function fatal_error($message="", $help="") {
        echo("$message<br><br>$help");
        exit;
}
?>
