<?php

//  echo "<h1>Server configuring in progress...</h1>Please, come back later!";
//  exit();

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
  |   > Wrapper script
  |   > Script written by Matt Mecham
  |   > Date started: 14th February 2002
  |
  +--------------------------------------------------------------------------
 */

//-----------------------------------------------
// USER CONFIGURABLE ELEMENTS
//-----------------------------------------------

// Enable module usage?
// (Vital for some mods and IPB enhancements)

define('USE_MODULES', 1);

//-----------------------------------------------
// NO USER EDITABLE SECTIONS BELOW
//-----------------------------------------------

require __DIR__ . '/../app/bootstrap.php';


//--------------------------------
// Import $INFO, now!
//--------------------------------


setlocale(LC_ALL, 'ru_RU.UTF-8');

$INFO['mm_groups'] = array(
    $INFO['admin_group'],
    $INFO['supermoderator_group'],
    $INFO['moderator_group'],
    $INFO['comoderator_group']
);

//--------------------------------
// The clocks a' tickin'
//--------------------------------

$Debug = Debug::instance();
$Debug->startTimer();

//--------------------------------
// Wrap it all up in a nice easy to
// transport super class
//--------------------------------

Ibf::registerApplication(new ForumApplication());
$ibforums = Ibf::app();
//stub
$std  = & $ibforums->functions;
$sess = & $ibforums->session;
//
$ibforums->init();
//move to ibforums?
$print = new display();

unset($INFO);

//-------------------------------
// Call IBStores Funtion Libary
//-------------------------------

require ROOT_PATH . "sources/store/store_functions.php";

$lib = new lib();

//--------------------------------
//  The rest :D
//--------------------------------


//echo "colfor=$colfor, expfor=$expfor, colcat=$colcat, expcat=$expcat<br>\n";
$colcat = intval($ibforums->input['colcat']);
$expcat = intval($ibforums->input['expcat']);
$colfor = intval($ibforums->input['colfor']);
$expfor = intval($ibforums->input['expfor']);

if (!empty($colcat)) {
    $std->set_board_visibility($colcat, false, false);
} else {
    if (!empty($expcat)) {
        $std->set_board_visibility($expcat, false, true);
    }
}

if (!empty($colfor)) {
    $std->set_board_visibility($colfor, true, false);
} else {
    if (!empty($expfor)) {
        $std->set_board_visibility($expfor, true, true);
    }
}

list($ppu, $tpu) = explode("&", $ibforums->member['view_prefs']);

$ibforums->vars['display_max_topics'] = ($tpu > 0)
    ? $tpu
    : $ibforums->vars['display_max_topics'];
$ibforums->vars['display_max_posts']  = ($ppu > 0)
    ? $ppu
    : $ibforums->vars['display_max_posts'];

//--------------------------------
//  Set up the session ID stuff
//--------------------------------

if ($ibforums->session_type == 'cookie') {
    $ibforums->session_id = "";
    $ibforums->base_url   = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'] . '?';
} else {
    $ibforums->base_url = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'] . '?s=' . $ibforums->session_id . '&amp;';
}

if ($INFO['session_hide']) {
    $ibforums->session_id = "";
}

//--------------------------------
//  Set up the forum_read cookie
//--------------------------------

if ($ibforums->member['id']) {
    $std->song_get_forumsread();
}

//--------------------------------
//  Set up our language choice
//--------------------------------

//--------------------------------

if ($ibforums->input['act'] == "Error") {
    $std->Error(array('LEVEL' => 1, 'MSG' => $ibforums->input['type']));

    exit();
}

//--------------------------------

if ($ibforums->input['act'] != 'Login' and $ibforums->input['act'] != 'Reg' and $ibforums->input['act'] != 'Attach') {
    //--------------------------------
    //  Do we have permission to view
    //  the board?
    //--------------------------------

    if ($ibforums->member['g_view_board'] != 1) {
        $std->Error(array('LEVEL' => 1, 'MSG' => 'no_view_board'));
    }

    //--------------------------------
    //  Is the board offline?
    //--------------------------------

    if ($ibforums->vars['board_offline'] == 1) {
        if ($ibforums->member['g_access_offline'] != 1) {
            $std->board_offline();
        }
    }

    //--------------------------------
    //  Is log in enforced?
    //--------------------------------

    if (!$ibforums->member['id'] and $ibforums->vars['force_login'] == 1) {
        require ROOT_PATH . "sources/Login.php";
    }
}

//DEBUG
//echo "<pre>";
//print_r($_SERVER);
//echo "<hr>";
//print_r($ibforums);
//echo "</pre>";
//--------------------------------
// Decide what to do
//--------------------------------

$choice = array(
    "idx"        => "Boards",
    "cms"        => "cms/cms",
    "uploads"    => "cms/uploads",
    "SC"         => "Boards",
    "SF"         => "Forums",
    "SR"         => "Forums",
    "ST"         => "Topics",
    "Login"      => "Login",
    "Post"       => "Post",
    "Poll"       => "lib/add_poll",
    "Reg"        => "Register",
    "Online"     => "Online",
    "Members"    => "Memberlist",
    "Help"       => "Help",
    "Search"     => "Search",
    "Select"     => "Select",
    "Mod"        => "Moderate",
    "Print"      => "misc/print_page",
    "Forward"    => "misc/forward_page",
    "Mail"       => "misc/contact_member",
    "Invite"     => "misc/contact_member",
    "ICQ"        => "misc/contact_member",
    "AOL"        => "misc/contact_member",
    "YAHOO"      => "misc/contact_member",
    "MSN"        => "misc/contact_member",
    "report"     => "misc/contact_member",
    "chat"       => "misc/contact_member",
    "integ"      => "misc/contact_member",
    "Msg"        => "Messenger",
    "UserCP"     => "Usercp",
    "Profile"    => "Profile",
    "Track"      => "misc/tracker",
    "Stats"      => "misc/stats",
    "Attach"     => "misc/attach",
    "ib3"        => "misc/ib3",
    "legends"    => "misc/legends",
    "modcp"      => "mod_cp",
    "calendar"   => "calendar",
    "buddy"      => "browsebuddy",
    "rep"        => "Reputation",
    "boardrules" => "misc/contact_member",
    "mmod"       => "misc/multi_moderate",
    "warn"       => "misc/warn",
    "home"       => "dynamiclite/csite",
    "module"     => "modules",
    "fav"        => "fav",
    "store"      => "store/store",
    "checker"    => "SongFunc",
    "quiz"       => "Quiz",
);

/***************************************************/
//
// Check to make sure the array key exits..
if (!isset($choice[$ibforums->input['act']])) {
    $ibforums->input['act'] = 'idx';
}

if ($ibforums->input['act'] == 'home') {
    require ROOT_PATH . "sources/Boards.php";
} elseif ($ibforums->input['act'] == 'module') {
    if (USE_MODULES == 1) {
        require ROOT_PATH . "modules/module_loader.php";
        $loader = new module_loader();
    } else {
        require ROOT_PATH . "sources/Boards.php";
    }
    // Require and run
} else {
    //if ( $ibforums->input['act'] == 'UserCP' || $ibforums->input['act'] == 'Profile' )
    //{
    //echo "Require: ".ROOT_PATH."sources/".$choice[ $ibforums->input['act'] ].".php<br>";
    //if (file_exists(ROOT_PATH."sources/".$choice[ $ibforums->input['act'] ].".php"))
    //{
    //echo "File: ".ROOT_PATH."sources/".$choice[ $ibforums->input['act'] ].".php Exists<br>";
    //}
    //}
    require ROOT_PATH . "sources/" . $choice[$ibforums->input['act']] . ".php";
}

//{$ibforums->session_id}<br>
//{$sess->session_id}
//+-------------------------------------------------
// GLOBAL ROUTINES
//+-------------------------------------------------

function fatal_error($message = "", $help = "")
{
    echo("$message<br><br>$help");
    exit;
}
