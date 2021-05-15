<?php

use Views\View;

/**
 * @class Display Class used for rendering process
 *
 */
class display
{
	var $syntax = array();
	var $to_print = "";
	public $js;

	function __construct()
	{
		$this->js = new \JS\JS();
	}

	function is_new_fav_exists()
	{
		$ibforums = Ibf::app();
		//        $std->update_favorites();

		$stmt = $ibforums->db->query(
			"SELECT f.tid
			FROM ibf_favorites f
			INNER JOIN ibf_topics t ON f.tid=t.tid
			INNER JOIN ibf_log_topics tr ON (f.mid=tr.mid AND f.tid=tr.tid)
			WHERE
				f.mid='" . $ibforums->member['id'] . "' AND
				t.last_post>tr.logTime
		    LIMIT 1"
		);

		return $stmt->rowCount();
	}

	//-------------------------------------------
	// Appends the parsed HTML to our class var
	//-------------------------------------------

	function add_output($to_add)
	{
		$this->to_print .= $to_add;

		//return 'true' on success
		return true;
	}

	private function makeBreadcrumbs($output_array){
		$ibf = Ibf::app();
		$items = [ "<a href='{$ibf->base_url}'>{$ibf->vars['board_name']}</a>" ];

		if (empty($output_array['OVERRIDE']))
		{
			if (is_array($output_array['NAV']))
			{
				$items = array_merge($items, $output_array['NAV']);
				//<{F_NAV}>
				//<{F_NAV_SEP}>
			}
		}
		return $items;
	}

	function topNav($output_array)
	{

		$nav = View::make("global.start_nav", ['NEW' => "_NEW"]);
		$nav .= View::make("global.topBreadcrumbs", ['items' => $this->makeBreadcrumbs($output_array)]);
		$nav .= View::make("global.end_nav");

		return $nav;
	}

	/**
	 * Botton (one-line) breadcrumbs
	 * @param array $output_array
	 * @return string
	 */
	function bottomNav($output_array)
	{
		return View::make("global.bottomBreadcrumbs", ['items' => $this->makeBreadcrumbs($output_array)]);
	}

	//-------------------------------------------
	// Rotate banners from array.
	//-------------------------------------------

	function rotate_banner($banners = "")
	{
		global $ibforums, $std;

		$my_banners = array();
		$my_banners = explode("|", $banners);
		$content    = "";

		//if ($std->check_perms( $ibforums->member['club_perms'] ) or
		if ($ibforums->member['is_mod'] or
		    $ibforums->member['g_is_supmod']
		)
		{
			return $content;
		}

		$num_of_banners = count($my_banners);

		if ($num_of_banners)
		{
			$i = rand(0, $num_of_banners - 1);
			//debug:	  $content = "$num_of_banners:$i:".$my_banners[$i];
			$content = $my_banners[$i];
		}

		return $content;
	}

	//-------------------------------------------
	// Output the XAP network banner.
	//-------------------------------------------

	function xap_banner()
	{
		global $ibforums, $std;

		$cache_dir = public_path('sources') . '/lib/TNX-something-long-random-string-324sd54ywey/'; // здесь ОБЯЗАТЕЛЬНО укажите свое название папки вместо cache, минимум 12 символов!

		require_once($cache_dir . 'tnx.php');
		$tnx = new TNX_n('vot', $cache_dir); // ваш логин в системе

		$content = "<!-- XAP banner -->\n";
		$content .= $tnx->show_link(1); // выводим первую ссылку
		$content .= $tnx->show_link(1); // выводим вторую ссылку, желательно в другом месте страницы, ниже
		$content .= $tnx->show_link(1); // выводим третью ссылку, желательно в другом месте страницы, ниже
		$content .= $tnx->show_link(); // выводим оставшиеся, желательно в другом месте страницы, ниже

		return $content;
	}

	//-------------------------------------------
	// Parses all the information and prints it.
	//-------------------------------------------

	function do_output($output_array)
	{
		global $Debug, $ibforums, $std;

		if ($ibforums->input['show_cp_order_number'] == 1)
		{
			// Show the IPS Copyright Removal order number.
			// Note, this is designed to allow IPS validate boards
			// who've purchased copyright removal. The order number
			// is the only thing shown and the order number is unique
			// to the person who paid and is no good to anyone else.
			// Showing the order number poses no risk at all -
			//  the information is useless to anyone outside of IPS.
			flush();
			print ($ibforums->vars['ips_cp_purchase'] != "")
				? $ibforums->vars['ips_cp_purchase']
				: '0';
			exit();
		}
		//---------------------------------------------
		// Check for DEBUG Mode
		//---------------------------------------------

		if ($ibforums->member['g_access_cp'])
		{
			if (FALSE) //if ( $DB->obj['debug'] ) //todo need to move to the debug class or remove it completely
			{
				flush();
				print "<html><head><title>mySQL Debugger</title><body bgcolor='white'><style type='text/css'> TABLE, TD, TR, BODY { font-family: verdana,arial, sans-serif;color:black;font-size:11px }</style>";
				print $ibforums->debug_html;
				print "</body></html>";
				exit();
			}

			$input = "";
			$queries = "";
			$sload = "";
			$stats = '';

			if ($ibforums->server_load > 0)
			{
				$sload = "&nbsp; [ Server Load: " . $ibforums->server_load . " ]";
			}

			//+----------------------------------------------

			if (Debug::instance()->level >= 2)
			{
				$stats .= "<br>\n<div class='tableborder'>\n<div class='pformstrip'>FORM and GET Input</div><div class='row1' style='padding:6px'>\n";

				foreach ($ibforums->input as $k => $v)
				{
					$stats .= "<strong>$k</strong> = $v<br>\n";
				}

				$stats .= "</div>\n</div>";

			}

			//+----------------------------------------------

			if (Debug::instance()->level >= 3 && isset(Debug::instance()->stats->cachedQueries))
			{
				$stats .= "<br>\n<div class='tableborder'>\n<div class='pformstrip'>Queries Used</div><div class='row1' style='padding:6px'>";

				foreach (Debug::instance()->stats->cachedQueries as $q)
				{
					$q = htmlspecialchars($q);
					$q = preg_replace("/^SELECT/i", "<span class='red'>SELECT</span>", $q);
					$q = preg_replace("/^UPDATE/i", "<span class='blue'>UPDATE</span>", $q);
					$q = preg_replace("/^DELETE/i", "<span class='orange'>DELETE</span>", $q);
					$q = preg_replace("/^INSERT/i", "<span class='green'>INSERT</span>", $q);
					$q = str_replace("LEFT JOIN", "<span class='red'>LEFT JOIN</span>", $q);

					$q = preg_replace(
						"/(" . $ibforums->vars['sql_tbl_prefix'] . ")(\S+?)([\s\.,]|$)/",
						"<span class='purple'>\\1\\2</span>\\3",
						$q
					);

					$stats .= "$q<hr>\n";
				}

				$stats .= "</div>\n</div>";
			}
		}

		if (Debug::instance()->level > 0)
		{
			$ex_time = sprintf(
				"%.4f",
				Debug::instance()
					->executionTime()
			);

			$query_cnt = Debug::instance()->stats->queriesCount;

			$timestamp = gmdate('j.m.y, H:i T');

			$stats = View::make(
				"global.RenderScriptStatsRow",
				['ex_time' => $ex_time, 'query_cnt' => $query_cnt, 'timestamp' => $timestamp, 'sload' => $sload]
			);

		}
		/********************************************************/
		// NAVIGATION
		/********************************************************/

		$nav = $this->topNav($output_array);

		//---------------------------------------------------------
		// CSS
		//---------------------------------------------------------
		$css = View::make("global.css_external", ['css' => $ibforums->skin->getCSSFile()]) . "\n";
		if ($ibforums->member['syntax'] == "prism-coy") {
		$css .= View::make("global.css_external", ['css' => 'assets/stylesheets/prism-theme-coy.css']) . "\n";
		} elseif ($ibforums->member['syntax'] == "prism-twilight") {
		$css .= View::make("global.css_external", ['css' => 'assets/stylesheets/prism-theme-twilight.css']) . "\n";
		}
		//---------------------------------------------------------

		$extra = "";
		$ur    = '(U)';

		if ($ibforums->vars['ipb_reg_number'])
		{
			$ur = '(R)';

			if ($ibforums->vars['ipb_reg_show'] and $ibforums->vars['ipb_reg_name'])
			{
				$extra = "<div align='center' class='copyright'>Registered to: " . $ibforums->vars['ipb_reg_name'] . "</div>";
			}
		}

		if ($ibforums->member['syntax'] == "client")
		{
			$count = 0;

			foreach ($this->syntax as $row => $highlight)
			{
				$this->js->addRaw("<script type='text/javascript' src='{$ibforums->vars['board_url']}/highlight/h_{$row}_{$highlight}.js'></script>", \JS\JS::HEAD);
				$count++;
			}

			if ($count)
			{
				$this->js->addLocal("h_core.js");
			}
		}

        $this->js->addLocal('prism.js');
        $this->js->addLocal('prism-bcb-cmake.js');

		if ($output_array['JS'])
		{
			$this->js->addLocal($output_array['JS']);
		}

		// Copyrights
		// Yes, I realise that this is silly and easy to remove the copyright, but
		// as it's not concealed source, there's no point having a 1337 fancy hashing
		// algorithm if all you have to do is delete a few lines, so..
		// However, be warned: If you remove the copyright and you have not purchased
		// copyright removal, you WILL be spotted and your licence to use Invision Power Board
		// will be terminated, requiring you to remove your board immediately.
		// So, have a nice day.

		$copyright = "<!-- Copyright Information -->\n\n<div align='center' class='copyright'>Powered by <a rel='nofollow' href=\"https://www.invisionboard.com\" target='_blank'>Invision Power Board</a>{$ur} {$ibforums->version} &copy; 2003 &nbsp;<a rel='nofollow' href='https://www.invisionpower.com' target='_blank'>IPS, Inc.</a></div>\n";

		if ($ibforums->vars['ips_cp_purchase'])
		{
			$copyright = "";
		}

		$copyright .= $extra;

		// Awww, cmon, don't be mean! Literally thousands of hours have gone into
		// coding Invision Power Board and all we ask in return is one measly little line
		// at the bottom. That's fair isn't it?
		// No? Hmmm...
		// Have you seen how much it costs to remove the copyright from UBB? o_O

		/*		 * ***************************************************** */
		// Build the board header
		$this->js->addLocal('global.js', true);
		$this->js->addLocal('jqcd/jqcd.js');
		
		$this->add_js_variables();

		$this->exportJSLang([
				'text_enter_url',
		        'text_enter_url_name',
		        'text_enter_email',
		        'text_enter_email_name',
		        'list_prompt',
		        'text_enter_image',
		        'text_spoiler',
		        'text_quote',
		        'text_img',
		        'text_url',
		        'text_list',
		        'error_no_url',
		        'error_no_title',
		        'error_no_email',
		        'error_no_email_name',
		        'text_enter_spoiler',
		        'text_enter_quote',
		        'tag_list_numbered',
		        'tag_list_numbered_rome',
		        'tag_list_marked',
		        'tpl_q1'
			]);
		$this_header = View::make(
			"global.BoardHeader",
			['fav_active' => $ibforums->member['id'] && $this->is_new_fav_exists()]
		);

		// Board Global Message

		$message = '';
		if ($ibforums->vars['global_message_on'])
		{
			$message = preg_replace("/\n/", "<br>", stripslashes($ibforums->vars['global_message']));
			$message = View::make('boards.globalMessage', ['message' => $message]);
		}

		// Show rules link?

		if ($ibforums->vars['gl_show'] and $ibforums->vars['gl_title'])
		{
			if (!$ibforums->vars['gl_link'])
			{
				$ibforums->vars['gl_link'] = $ibforums->base_url . "act=boardrules";
			}

			$this_header = str_replace(
				"<!--IBF.RULES-->",
				View::make(
					"global.rules_link",
					['url' => $ibforums->vars['gl_link'], 'title' => $ibforums->vars['gl_title']]
				),
				$this_header
			);
		}

		//---------------------------------------
		// Build the members bar
		//---------------------------------------

		if (!$ibforums->member['id'])
		{
			$output_array['MEMBER_BAR'] = View::make("global.Guest_bar");
		} else
		{
			if (($ibforums->member['g_max_messages'] > 0) and ($ibforums->member['msg_total'] >= $ibforums->member['g_max_messages']))
			{
				$msg_data['TEXT'] = $ibforums->lang['msg_full'];
			} else
			{
				$ibforums->member['new_msg'] = $ibforums->member['new_msg'] == ""
					? 0
					: $ibforums->member['new_msg'];

				$msg_data['TEXT'] = sprintf($ibforums->lang['msg_new'], $ibforums->member['new_msg']);

				// Check for NEW PM

				if ($ibforums->member['new_msg'])
				{
					$msg_data['TEXT'] .= " <img border=0 src='{$ibforums->vars['board_url']}/html/sys-img/bat.gif'>";
				}
			}

			//---------------------------------------
			// Do we have a pop up to show?
			//---------------------------------------

			if ($ibforums->member['show_popup'])
			{
				$ibforums->db->exec(
					"UPDATE ibf_members
					SET show_popup=0
					WHERE id='" . $ibforums->member['id'] . "'"
				);

				if ($ibforums->input['act'] != 'Msg')
				{
					$this->js->AddInline('pm_popup();', \JS\JS::BOTTOM);

				}
			}

			$mod_link = "";

			$admin_link = $ibforums->member['g_access_cp']
				? View::make("global.admin_link")
				: '';

			$valid_link = $ibforums->member['mgroup'] == $ibforums->vars['auth_group']
				? View::make("global.validating_link")
				: '';

			if ($ibforums->member['mgroup'] == $ibforums->vars['auth_group'])
			{
				$valid_warning = str_replace(
					'*EMAIL*',
					$ibforums->member['email'],
					View::make("global.member_valid_warning")
				);
			}

			if (!$ibforums->member['g_use_pm'])
			{
				$output_array['MEMBER_BAR'] = View::make(
					"global.Member_no_usepm_bar",
					['ad_link' => $admin_link, 'mod_link' => $mod_link, 'val_link' => $valid_link]
				);
			} else
			{
				$output_array['MEMBER_BAR'] = View::make(
						'global.Member_bar',
						[
							'msg'      => $msg_data,
							'ad_link'  => $admin_link,
							'mod_link' => $mod_link,
							'val_link' => $valid_link
						]
					);
			}
		}

		// Adjust the page title for russian search bots
		//todo jrth: hmmm
		$output_array['TITLE'] = str_replace(".RU", ".Ру", $output_array['TITLE']);

		// Check for OFFLINE BOARD

		if ($ibforums->vars['board_offline'])
		{
			$output_array['TITLE'] = $ibforums->lang['warn_offline'] . " " . $output_array['TITLE'];
		}

		$replace = array();
		$change  = array();

		//---------------------------------------
		// Get the template
		//---------------------------------------

		$replace[] = "<% CSS %>";
		$change[]  = $css;

		$replace[] = "<% JAVASCRIPT_HEAD %>";
		$change[]  = $this->js->render(\JS\JS::HEAD);
		$replace[] = "<% JAVASCRIPT_TOP %>";
		$change[]  = $this->js->render(\JS\JS::TOP);
		$replace[] = "<% JAVASCRIPT_BOTTOM %>";
		$change[]  = $this->js->render(\JS\JS::BOTTOM);

		$replace[] = "<% RSS %>";

		if (!$output_array['RSS'])
		{
			$output_array['RSS'] = View::make("global.rss");
		}

		$change[] = $output_array['RSS'];

		// Replace Blocks in the template

		$replace[] = "<% TITLE %>";
		$change[]  = $output_array['TITLE'];

		$replace[] = "<% BOARD %>";
		$change[]  = $this->to_print;

		$replace[] = "<% STATS %>";
		$change[]  = $stats;

		$replace[] = "<% GENERATOR %>";
		$change[]  = "";

		$replace[] = "<% COPYRIGHT %>";
		$change[]  = $copyright;

		$replace[] = "<% BOARD HEADER %>";
		$change[]  = $this_header;

		$replace[] = "<% GLOBAL MESSAGE %>";
		$change[]  = $message;

		$replace[] = "<% NAVIGATION %>";
		$change[]  = $nav;

		$replace[] = "<!--IBF.NAVIGATION-->";
		$change[]  = $this->bottomNav($output_array);

		$replace[] = "<% MEMBER BAR %>";
		$change[] = $output_array['MEMBER_BAR'] . $valid_warning;

        //tags
        $tags = [
            'uid' => Ibf::app()->member['id'],
        ];
        if (isset(Ibf::app()->input['showforum'])) {
            $tags['shown-forum-id'] = Ibf::app()->input['showforum'];
        }elseif (isset(Ibf::app()->input['showtopic'])) {
            $tags['shown-topic-id'] = Ibf::app()->input['showtopic'];
        }elseif(isset(Ibf::app()->input['showuser'])){
            $tags['shown-profile'] = Ibf::app()->input['showuser'];
        }
        array_walk($tags, function(&$item, $key){ $item = sprintf('data-%s="%s"', $key, $item); });

		$replace[] = "<% BODY_DATA_TAGS %>";
		$change[] = implode(' ', $tags);
		//todo добавить data зависящие от контента

		//---------------------------------------
		// Do replace in template
		//---------------------------------------
		$output = View::make('global.wrapper');
		$output = str_replace($replace, $change, $output);
		$output = $this->prepare_output($output);

		//---------------------------------------
		// Start GZIP compression
		//---------------------------------------

		if ($ibforums->vars['disable_gzip'] != 1)
		{
			$buffer = ob_get_contents();
			ob_end_clean();
			ob_start('ob_gzhandler');
			print $buffer;
		}

		$this->do_headers();

		print $output;

		\Logs::info('Stats', 'Queries used: ' . Debug::instance()->stats->queriesCount);
		\Logs::info('Stats', 'Script execution time: ' . sprintf('%.4f', Debug::instance()->executionTime()));

		exit;
	}

	function add_js_variables() {
		global $ibforums;
		$this->js->addVariable('base_url', Ibf::app()->base_url);
		$this->js->addVariable('session_id', Ibf::app()->session->session_id);
		$this->js->addVariable('max_attach_size', $ibforums->member['g_attach_max']);
		$this->js->addVariable('st', $ibforums->input['st']);
		$this->js->addVariable('text_spoiler_hidden_text', Ibf::app()->lang['spoiler']);
		$this->js->addVariable('text_cancel', Ibf::app()->lang['js_cancel']);
		$this->js->addVariable('upload_attach_too_big', Ibf::app()->lang['upload_to_big']);
		$this->js->addVariable('js_base_url', Ibf::app()->vars['board_url'] . '/index.' . Ibf::app()->vars['php_ext'] . '?s=' . Ibf::app()->session->session_id . '&');	
	}
	
	function prepare_output($template)
	{
		global $Debug, $std;
		$ibforums = Ibf::app();

		$replace = array();
		$change  = array();

		//+--------------------------------------------
		//| Get the macros and replace them
		//+--------------------------------------------

		foreach($ibforums->skin->getMacroValues() as $macro_value => $macro_replace)
		{
			if ($macro_value)
			{
				$replace[] = "<{" . $macro_value . "}>";
				$change[]  = $macro_replace;
			}
		}

		//-----------------------------------
		// header banner
		$replace[] = "<!-- HEADER_BANNER -->";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_header']);

		//-----------------------------------
		// top banner
		$replace[] = "<% TOP NAV BANNER %>";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_top_nav']);

		//-----------------------------------
		// middle banner
		$replace[] = "<% MIDDLE BANNER %>";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_middle']);

		//-----------------------------------
		// bottom banner
		$replace[] = "<% BOTTOM BANNER %>";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_bottom']);

		//-----------------------------------
		// bottom XAP banner
		$replace[] = "<% XAP BANNER %>";
//		$change[]  = $this->xap_banner();
		$change[]  = '';

		//+--------------------------------------------
		// Stick in banner?
		//+--------------------------------------------

		if ($ibforums->vars['ipshosting_credit'])
		{
			$replace[] = "<!--IBF.BANNER-->";
			$change[]  = View::make("global.ibf_banner");
		}

		//+--------------------------------------------
		// Stick in chat link?
		//+--------------------------------------------

		if ($ibforums->vars['chat_account_no'])
		{
			$ibforums->vars['chat_height'] += 50;
			$ibforums->vars['chat_width'] += 50;

			$chat_link = ($ibforums->vars['chat_display'] == 'self')
				? View::make("global.show_chat_link_inline")
				: View::make("global.show_chat_link_popup");

			$replace[] = "<!--IBF.CHATLINK-->";
			$change[]  = $chat_link;
		}

		$replace[] = "<#IMG_DIR#>";
		$change[]  = $ibforums->skin->getImagesPath();

		$replace[] = "<#BASE_URL#>";
		$change[]  = $ibforums->base_url;

		return str_replace($replace, $change, $template);
	}

	//-------------------------------------------
	// print the headers
	//-------------------------------------------

	function do_headers()
	{
		global $ibforums;

		if ($ibforums->vars['print_headers'])
		{
			@header("HTTP/1.0 200 OK");
			@header("HTTP/1.1 200 OK");
			@header("Content-type: text/html; charset=" . $ibforums->vars['charset']);

			if ($ibforums->vars['nocache'])
			{
				@header("Cache-Control: no-cache, must-revalidate, max-age=0");
				@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				@header("Pragma: no-cache");
			}
		}
	}

	function redirect_js_screen($do, $action, $params, $do_echo = "")
	{

		@header("conten-type: text/javascript");

		$to_echo = "";

		if ($do)
		{
			if (!is_array($do))
			{
				$to_echo = "toChangeLink('{$do}',{$action},'{$params}');";
			} elseif (count($do))
			{
				foreach ($do as $to_do)
				{
					$to_echo .= "toChangeLink('{$to_do}',{$action},'{$params}');";
				}
			}
		}

		$to_echo = $to_echo . $do_echo;

		$to_echo = str_replace("[nn]", "<br>", $to_echo);

		echo $to_echo;

		exit();
	}

	//-------------------------------------------
	// print a pure redirect screen
	//-------------------------------------------

	function redirect_screen($text = "", $url = "", $type = "")
	{
		global $std, $ibforums;

		if ($ibforums->input['debug'])
		{
			flush();
			exit();
		}

		$std->boink_it($ibforums->base_url . $url, $type);

		exit();
	}

	//-------------------------------------------
	// print text only,
	// without css, title and initial tags
	// macros are optional
	//-------------------------------------------
	function text_only($text = "", $macro = false)
	{
		$ibforums = Ibf::app();

		$html = $text;

		if ($macro)
		{
			foreach($ibforums->skin->getMacroValues() as $macro_value => $macro_replace)
			{
				if ($macro_value != "")
				{
					$html = str_replace("<{" . $macro_value . "}>", $macro_replace, $html);
				}
			}
			$html = str_replace("<#IMG_DIR#>", $ibforums->skin->getImagesPath(), $html);
		}

		if ($ibforums->vars['disable_gzip'] != 1)
		{
			$buffer = ob_get_contents();
			ob_end_clean();
			ob_start('ob_gzhandler');
			print $buffer;
		}

		$this->do_headers();

		echo($html);
		exit;
	}

	//-------------------------------------------
	// print a minimalist screen suitable for small
	// pop up windows
	//-------------------------------------------

	function pop_up_window($title = 'Invision Power Board', $text = "")
	{
		global $ibforums;

		//---------------------------------------------------------
		// CSS
		//---------------------------------------------------------
		$this->js->addLocal('global.js');
		$css = View::make("global.css_external", ['css' => $ibforums->skin->getCSSFile()]) . "\n";

		if ($ibforums->member['syntax'] == "client")
		{
			$count = 0;

			foreach ($this->syntax as $row => $highlight)
			{
				$this->js->addRaw("<script type='text/javascript' src='{$ibforums->vars['board_url']}/highlight/h_{$row}_{$highlight}.js'></script>\n", \JS\JS::HEAD);
				$count++;
			}

			if ($count)
			{
				$this->js->addLocal('h_core.js');
			}
		}

		$this->add_js_variables();

		$html = View::make("global.pop_up_window", ['title' => $title, 'js' => $this->js->render(\JS\JS::HEAD) . $this->js->render(\JS\JS::TOP), 'css' => $css, 'text' => $text]);

		foreach($ibforums->skin->getMacroValues() as $macro_value => $macro_replace)
		{
			if ($macro_value != "")
			{
				$html = str_replace("<{" . $macro_value . "}>", $macro_replace, $html);
			}
		}

		$html = str_replace("<#IMG_DIR#>", $ibforums->skin->getImagesPath(), $html);

		if ($ibforums->vars['disable_gzip'] != 1)
		{
			$buffer = ob_get_contents();
			ob_end_clean();
			ob_start('ob_gzhandler');
			print $buffer;
		}

		$this->do_headers();

		echo($html);
		exit;
	}

	function diff_text($oldtext = "", $newtext = "", $add_prefix = "", $add_suffix = "", $del_prefix = "", $del_suffix = "", $norm_prefix, $norm_suffix, $add_p_word = "", $add_s_word = "", $del_p_word = "", $del_s_word = "")
	{
		global $ibforums;
		// add_prefix - будет записано перед добавленной строкой
		// add_suffix - будет записано после добавленной строки
		// del_prefix - будет записано перед удаленной строкой
		// del_suffix - будет записано после удаленной строки
		// norm_prefix - будет записано перед общей строкой
		// norm_suffix - будет записано после общей строки
		// add_p_word - будет записано перед добавленным словом
		// add_s_word - будет записано после добавленного слова
		// del_p_word - будет записано перед удаленным словом
		// del_s_word - будет записано после удаленного слова

		require $ibforums->vars['base_dir'] . "sources/lib/simplediff.php";
		$diff = simpleDiff::diff_to_array(false, $oldtext, $newtext, true);

		foreach ($diff as $i => $line)
		{
			list($type, $old, $new) = $line;

			if ($type == simpleDiff::INS)
			{
				$out .= $add_prefix . $new . $add_suffix;
			} elseif ($type == simpleDiff::DEL)
			{
				$out .= $del_prefix . $old . $del_suffix;
			} elseif ($type == simpleDiff::CHANGED)
			{
				$lineDiff = simpleDiff::wdiff($old . ' ', $new . ' ');
				// Don't show new things in deleted line
				$lineDiff = str_replace('  ', ' ', $lineDiff);
				$lineDiff = str_replace('-] [-', ' ', $lineDiff);
				$lineDiff = preg_replace('!\[-(.*)-\]!U', "$del_p_word\\1$del_s_word", $lineDiff);
				$lineDiff = preg_replace('!\{\+(.*)\+\}!U', "$add_p_word\\1$add_s_word", $lineDiff);

				$out .= $norm_prefix . $lineDiff . $norm_suffix;
			} elseif ($type == simpleDiff::SAME)
			{
				$out .= $norm_prefix . $old . $norm_suffix;
			}
		}

		return $out;
	}

	public function exportJSLang($ids) {
		foreach($ids as $id) {
			if (isset(Ibf::app()->lang[$id])){
				$this->js->addVariable($id, Ibf::app()->lang[$id]);
			}
		}
	}
}
