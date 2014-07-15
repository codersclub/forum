<?php

/**
 * @class Display Class used for rendering process
 *
 */
class display
{
	var $syntax = array();
	var $to_print = "";

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
		global $skin_universal, $ibforums, $std;

		$nav = $skin_universal->start_nav("_NEW");
		$nav .= $skin_universal->topBreadcrumbs($this->makeBreadcrumbs($output_array));
		$nav .= $skin_universal->end_nav();

		return $nav;
	}

	/**
	 * Botton (one-line) breadcrumbs
	 * @param array $output_array
	 * @return string
	 */
	function bottomNav($output_array)
	{
		global $skin_universal;
		return $skin_universal->bottomBreadcrumbs($this->makeBreadcrumbs($output_array));
	}

	//-------------------------------------------
	// vot: Rotate banners from array.
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
	// vot: Output the XAP network banner.
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
		global $Debug, $skin_universal, $ibforums, $std;

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
		// Song * Adjust the Favorites Icon

		//---------------------------------------------
		// Check for DEBUG Mode
		//---------------------------------------------

		if ($ibforums->member['g_access_cp'])
		{
			$input = "";
			$queries = "";
			$sload = "";
			$stats = '';

			if ($ibforums->server_load > 0)
			{
				\Logs\Logger::Stats()->info("Server Load: " . $ibforums->server_load);
			}
		}

		$ex_time = sprintf("%.4f", Debug::instance()->executionTime());

		$query_cnt = Debug::instance()->stats->queriesCount;

		\Logs\Logger::Stats()->info('Script Execution Time: ' . $ex_time);
		\Logs\Logger::Stats()->info('Queries used: ' . $query_cnt);

		/********************************************************/
		// NAVIGATION
		/********************************************************/

		$nav = $this->topNav($output_array);

		//---------------------------------------------------------
		// CSS
		//---------------------------------------------------------
		// Song * CSS based on User CP + common CSS, 29.12.04

		$css = $skin_universal->css_external('common', $ibforums->skin['img_dir']);
		$css .= $skin_universal->css_external($ibforums->skin['css_id'], $ibforums->skin['img_dir']) . "\n";

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

		//-------------------------------------------------------
		// Song + Mixxx * included js, client highlight, 23.12.04

		$js = "";

		if ($ibforums->member['syntax'] == "client")
		{
			$count = 0;

			foreach ($this->syntax as $row => $highlight)
			{
				$js .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/highlight/h_{$row}_{$highlight}.js'></script>\n";
				$count++;
			}

			if ($count)
			{
				$js .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/h_core.js?{$ibforums->vars['client_script_version']}'></script>\n";
			}
		}

		if ($output_array['JS'])
		{
			$js .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/{$output_array['JS']}'></script>";
		}

		// End of Song + Mixxx * included js, 23.12.04
		// Copyrights
		// Yes, I realise that this is silly and easy to remove the copyright, but
		// as it's not concealed source, there's no point having a 1337 fancy hashing
		// algorithm if all you have to do is delete a few lines, so..
		// However, be warned: If you remove the copyright and you have not purchased
		// copyright removal, you WILL be spotted and your licence to use Invision Power Board
		// will be terminated, requiring you to remove your board immediately.
		// So, have a nice day.

		$copyright = "<!-- Copyright Information -->\n\n<div align='center' class='copyright'>Powered by <a rel='nofollow' href=\"http://www.invisionboard.com\" target='_blank'>Invision Power Board</a>{$ur} {$ibforums->version} &copy; 2003 &nbsp;<a rel='nofollow' href='http://www.invisionpower.com' target='_blank'>IPS, Inc.</a></div>\n";

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
		$this_header = $skin_universal->BoardHeader($ibforums->member['id'] && $this->is_new_fav_exists());

		// Show rules link?

		if ($ibforums->vars['gl_show'] and $ibforums->vars['gl_title'])
		{
			if (!$ibforums->vars['gl_link'])
			{
				$ibforums->vars['gl_link'] = $ibforums->base_url . "act=boardrules";
			}

			$this_header = str_replace(
				"<!--IBF.RULES-->",
				$skin_universal->rules_link($ibforums->vars['gl_link'], $ibforums->vars['gl_title']),
				$this_header
			);
		}

		//---------------------------------------
		// Build the members bar
		//---------------------------------------

		if (!$ibforums->member['id'])
		{
			$output_array['MEMBER_BAR'] = $skin_universal->Guest_bar();
		} else
		{
			$pm_js = "";

			if (($ibforums->member['g_max_messages'] > 0) and ($ibforums->member['msg_total'] >= $ibforums->member['g_max_messages']))
			{
				$msg_data['TEXT'] = $ibforums->lang['msg_full'];
			} else
			{
				$ibforums->member['new_msg'] = $ibforums->member['new_msg'] == ""
					? 0
					: $ibforums->member['new_msg'];

				$msg_data['TEXT'] = sprintf($ibforums->lang['msg_new'], $ibforums->member['new_msg']);

				// CBP & vot: Check for NEW PM

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
					$pm_js = $skin_universal->PM_popup();
				}
			}

			$mod_link = "";

			$admin_link = $ibforums->member['g_access_cp']
				? $skin_universal->admin_link()
				: '';

			$valid_link = $ibforums->member['mgroup'] == $ibforums->vars['auth_group']
				? $skin_universal->validating_link()
				: '';

			if ($ibforums->member['mgroup'] == $ibforums->vars['auth_group'])
			{
				$valid_warning = str_replace(
					'*EMAIL*',
					$ibforums->member['email'],
					$skin_universal->member_valid_warning()
				);
			}

			if (!$ibforums->member['g_use_pm'])
			{
				$output_array['MEMBER_BAR'] = $skin_universal->Member_no_usepm_bar($admin_link, $mod_link, $valid_link);
			} else
			{
				$output_array['MEMBER_BAR'] = $pm_js . $skin_universal->Member_bar(
						$msg_data,
						$admin_link,
						$mod_link,
						$valid_link
					);
			}
		}

		// vot:
		// Adjust the page title for russian search bots

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

		$replace[] = "<% JAVASCRIPT %>";
		$change[]  = $js;

		// Song * RSS, 29.01.05

		$replace[] = "<% RSS %>";

		if (!$output_array['RSS'])
		{
			$output_array['RSS'] = $skin_universal->rss();
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

		$replace[] = "<% NAVIGATION %>";
		$change[]  = $nav;

		// Song * secondary navigation

		$replace[] = "<!--IBF.NAVIGATION-->";
		$change[]  = $this->bottomNav($output_array);

		$replace[] = "<% MEMBER BAR %>";

		//todo разрулить условие нафик
		if (empty($output_array['OVERRIDE']) or TRUE /* MEMBER BAR WILL DISPLAY ON ERROR PAGES */)
		{
			$change[] = $output_array['MEMBER_BAR'] . $valid_warning;
		} else
		{
			$change[] = $skin_universal->member_bar_disabled() . $valid_warning;
		}

		//---------------------------------------
		// Do replace in template
		//---------------------------------------

		$ibforums->skin['template'] = str_replace($replace, $change, $ibforums->skin['template']);
		$ibforums->skin['template'] = $this->prepare_output($ibforums->skin['template']);

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

		print $ibforums->skin['template'];

		exit;
	}

	function prepare_output($template)
	{
		global $Debug, $skin_universal, $std;
		$ibforums = Ibf::app();

		$replace = array();
		$change  = array();

		// Load the Macro Set

		$stmt = $ibforums->db->query(
			"SELECT
				macro_value,
				macro_replace
	        FROM ibf_macro
		    WHERE macro_set={$ibforums->skin['macro_id']}"
		);

		//+--------------------------------------------
		//| Get the macros and replace them
		//+--------------------------------------------

		while ($row = $stmt->fetch())
		{
			if ($row['macro_value'])
			{
				$replace[] = "<{" . $row['macro_value'] . "}>";
				$change[]  = $row['macro_replace'];
			}
		}
		$stmt->closeCursor();

		//-----------------------------------
		// vot: header banner
		$replace[] = "<!-- HEADER_BANNER -->";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_header']);

		//-----------------------------------
		// vot: top banner
		$replace[] = "<% TOP NAV BANNER %>";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_top_nav']);

		//-----------------------------------
		// vot: middle banner
		$replace[] = "<% MIDDLE BANNER %>";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_middle']);

		//-----------------------------------
		// vot: bottom banner
		$replace[] = "<% BOTTOM BANNER %>";
		$change[]  = $this->rotate_banner($ibforums->vars['banner_bottom']);

		//-----------------------------------
		// vot: bottom XAP banner
		$replace[] = "<% XAP BANNER %>";
		$change[]  = $this->xap_banner();
		//+--------------------------------------------
		// Stick in banner?
		//+--------------------------------------------

		if ($ibforums->vars['ipshosting_credit'])
		{
			$replace[] = "<!--IBF.BANNER-->";
			$change[]  = $skin_universal->ibf_banner();
		}

		//+--------------------------------------------
		// Stick in chat link?
		//+--------------------------------------------

		if ($ibforums->vars['chat_account_no'])
		{
			$ibforums->vars['chat_height'] += 50;
			$ibforums->vars['chat_width'] += 50;

			$chat_link = ($ibforums->vars['chat_display'] == 'self')
				? $skin_universal->show_chat_link_inline()
				: $skin_universal->show_chat_link_popup();

			$replace[] = "<!--IBF.CHATLINK-->";
			$change[]  = $chat_link;
		}

		$replace[] = "<#IMG_DIR#>";
		$change[]  = $ibforums->vars['img_url']; // vot

		$replace[] = "<#BASE_URL#>"; // vot
		$change[]  = $ibforums->base_url; // vot

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
		global $skin_universal;
		$ibforums = Ibf::app();

		$html = $text;

		if ($macro)
		{
			// Load Macro Values
			$stmt = $ibforums->db->query(
				"SELECT
				macro_value,
				macro_replace
			    FROM ibf_macro
			    WHERE macro_set='{$ibforums->skin['macro_id']}'"
			);
			while ($row = $stmt->fetch())
			{
				if ($row['macro_value'] != "")
				{
					$html = str_replace("<{" . $row['macro_value'] . "}>", $row['macro_replace'], $html);
				}
			}
			$html = str_replace("<#IMG_DIR#>", $ibforums->vars['img_url'], $html);
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
		global $ibforums, $skin_universal;

		//---------------------------------------------------------
		// CSS
		//---------------------------------------------------------
		// CSS based on User CP + common CSS, Song * 29.12.04

		$css = $skin_universal->css_external('common', $ibforums->skin['img_dir']) . "\n";
		$css .= $skin_universal->css_external($ibforums->skin['css_id'], $ibforums->skin['img_dir']) . "\n";

		// Song + Mixxx * included js, client highlight, 23.12.04

		if ($ibforums->member['syntax'] == "client")
		{
			$count = 0;

			foreach ($this->syntax as $row => $highlight)
			{
				$css .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/highlight/h_{$row}_{$highlight}.js'></script>\n";
				$count++;
			}

			if ($count)
			{
				$css .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/h_core.js?{$ibforums->vars['client_script_version']}'></script>\n";
			}
		}

		$html = $skin_universal->pop_up_window($title, $css, $text);

		// Load Macro Values

		$stmt = $ibforums->db->query(
			"SELECT
				macro_value,
				macro_replace
		    FROM ibf_macro
		    WHERE macro_set='{$ibforums->skin['macro_id']}'"
		);

		while ($row = $stmt->fetch())
		{
			if ($row['macro_value'] != "")
			{
				$html = str_replace("<{" . $row['macro_value'] . "}>", $row['macro_replace'], $html);
			}
		}

		$html = str_replace("<#IMG_DIR#>", $ibforums->vars['img_url'], $html);

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

}
