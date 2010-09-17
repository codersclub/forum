<?

//Uncomment this line, to disable access to the server part.
if($INFO['board_offline']) {

	$INFO['client_disabled_msg'] = $INFO['offline_msg'];
}

	$INFO['match_browser'] = 0;

	$INFO['plg_offline_client'] = 1;

//Disables some redirects in IPB.
//Should always be 1 here.
	$INFO['plg_disable_redirect']   	=	'1';

//Prevents redirection to the Error page in IPB.
//Should always be 1 here.
	$INFO['plg_catch_err']	   		=	'1';

//Allows to costumize login scheme in IPB.
//Should always be 1 here.
	$INFO['plg_custom_login']		=	'1';

//User's ID for IPB.
//Used in customized IPB if plg_custom_login is set to 1 only.
	$INFO['plg_user_id']			=	'0';

//User's md5-password for IPB.
//Used in customized IPB if plg_custom_login is set to 1 only.
	$INFO['plg_user_pass']			=	'0';

//Used in customized IPB if plg_custom_login is set to 1 only.
//Allows or disallows guests for plugin session.
	$INFO['plg_allow_guests']		=	'0';

//Use or not time restrictions to fetch from IPB's DB.
//Used in Offline Clien scripts only!
	$INFO['client_db_restrictions_on']	=	'1';

//Percent of scheduled CPU usage of GetPosts process.
//Used in Offline Client scripts in case of
//client_db_restriction is set only.
	$INFO['client_loads_factor']		=	'30';

//Seems on Windows usleep() does not work.
//But to prevent frizes in forum work
//we should use usleep() function only
	$INFO['client_use_usleep']		=	'1';

//Maximum period to load messages.
//Set it to 0 to remove restriction.
//	$INFO['client_max_load_period']		=	'691200';//==week


	$INFO['client_do_log']			=	'1';

	$INFO['client_log_file']		=	"log/index.html";

	$INFO['client_do_debug_log']		=	0;

	$INFO['client_force_load_all_db']	=	0;

	$INFO['client_copyright_msg']		=	"";//"[SIZE=0][COLOR=gray]This message was created with [URL=http://forum.sources.ru/index.php?showforum=84]Forumizer[/URL] (c) avGolf2[/COLOR][/SIZE]";
?>
