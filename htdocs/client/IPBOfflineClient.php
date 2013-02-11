<?

// Prepare Invission environment

//echo "Parameter &lt;&lt;something&gt;&gt; is :" . $something . "<br>";

	$B_Board = intval($B_Board);
	$B_Main = intval($B_Main);
	$B_Parent = intval($B_Parent);
	$U_Username = addslashes($U_USername);
	$greater = intval($greater);
	$sNick = addslashes($sNick);

	define( 'ROOT_PATH', "../" );

	require "../../conf_global.php";

	require "client_conf.php";
	require "client_log.php";

	if(!empty($INFO['client_disabled_msg'])) {

		// Future version of Forumizer will have support of
		// "MESSAGE:" command
		echo "MESSAGE:" . $INFO['client_disabled_msg'];
		exit();
	}

	require "../sources/functions.php";

	error_reporting  (E_ERROR | E_WARNING | E_PARSE);
	set_magic_quotes_runtime(0);

	$std   = new FUNC;
	$print = new display();
	$sess  = new session();

	$to_require = "../sources/Drivers/".$INFO['sql_driver'].".php";
	require ($to_require);

// Connect to database
	$DB = new db_driver;
	$DB->obj['sql_database']     = $INFO['sql_database'];
	$DB->obj['sql_user']         = $INFO['sql_user'];
	$DB->obj['sql_pass']         = $INFO['sql_pass'];
	$DB->obj['sql_host']         = $INFO['sql_host'];
	$DB->obj['sql_charset']      = $INFO['sql_charset'];
	$DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];
	$DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;
	$DB->connect();

//Create Info class
	class Info {

	var $vars       = array();
	var $input      = array();
	var $lang_id    = "en";
	var $lang       = [];
	var $skin_id    = "s1";     // Skin Dir name

		function Info() {

			global $sess, $std, $DB, $INFO;

			if($INFO['plg_catch_err']) {

				$INFO['plg_catch_err'] = &$this;
			}

			$this->vars = &$INFO;

			$this->vars['TEAM_ICON_URL']   = $INFO['html_url'] . '/team_icons';
			$this->vars['AVATARS_URL']     = $INFO['html_url'] . '/avatars';
			$this->vars['EMOTICONS_URL']   = $INFO['html_url'] . '/emoticons';
			$this->vars['mime_img']        = $INFO['html_url'] . '/mime_types';
		}

		function Error($msg) {

			echo "MESSAGE:" . $msg;
			exit();
		}
	}

	$ibforums = new Info();
	$ibforums->lang       = $std->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);
	$ibforums->input      = $std->parse_incoming();


// Authenticate the user
	$userId = 0;
	$userName = "";

	if( $sNick != "" && $sPass != "" ) {

		$ibforums->vars['plg_user_pass'] = md5($sPass);
		$query = "SELECT id
			FROM ibf_members
			WHERE name='".addslashes($sNick)."'";
		$DB->query($query);

		$row = $DB->fetch_row();
		$ibforums->vars['plg_user_id'] = $row['id'];
	}

	$ibforums->input['act'] = "forumizer";

	$sess->authorise();

	$ibforums->member = $sess->member;
	$userId = $ibforums->member['id'];

	$userName = $ibforums->member['name'];

// Process action
	$actionArr = array(
		'FList' => 'ForumList',
		'Posts' => 'GetPosts',
		'Reply' => 'Reply',
		'GetPrivate' => 'Private',
		'ReplyPrivate' => 'ReplyPrivate'
	);

	if(empty($Action)) {

		$Action = "Posts";
	}

	$currAction = "IPB" . $actionArr[$Action];
	require "sources/" . $currAction . ".php";

	$index = new $currAction;

	$index->Process();
?>
