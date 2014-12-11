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
  |   > Topic display module
  |   > Module written by Matt Mecham
  |   > Date started: 18th February 2002
  |
  |	> Module Version Number: 1.1.0
  +--------------------------------------------------------------------------
 */
use Views\View;

$idx = new Topics;

class Topics
{

	var $output = "";
	var $base_url = "";
	var $moderator = array();
	var $mod = array();
	var $forum = array();
	var $topic = array();
	var $category = array();
	var $mem_titles = array();
	var $mod_action = array();
	var $nav_extra = array();
	var $poll_html = "";
	var $colspan = 0;
	var $parser = "";
	var $mimetypes = "";
	var $mod_panel_html = "";
	var $warn_range = 0;
	var $warn_done = 0;
	var $pfields = array();
	var $pfields_dd = array();
	var $md5_check = "";
	var $highlight = -1;
	var $first = 0;
	var $rows = 0;
	var $code_text = array();
	var $code_counter = 0;
	var $mod_tags = "";
	var $alter_post = 0;
	var $trid = 0;
	var $log_time = 0;
	private $cached_members = array();

	/*	 * ******************************************************************************** */

	//
	// Our constructor, load words, load skin, print the topic listing
	//
	/*	 * ******************************************************************************** */

	function get_moderators()
	{
		global $ibforums, $std;

		if (!$this->forum['id'])
		{
			return "";
		}

		$stmt = $ibforums->db->query("SELECT mid,
			member_name AS mod_name,
			member_id AS mod_id,
			is_group, group_id, group_name, post_q, topic_q
		    FROM ibf_moderators
		    WHERE forum_id='" . $this->forum['id'] . "'");

		if (!$stmt->rowCount())
		{
			return "";
		}

		while ($r = $stmt->fetch())
		{
			if ($r['mod_id'] == -1)
			{
				$r['mod_id'] = $r['mid'];
			}

			if ($r['mod_id'])
			{
				$this->mod[$r['mod_id']] = array(
					'name'    => $r['mod_name'],
					'id'      => $r['mod_id'],
					'isg'     => $r['is_group'],
					'gname'   => $r['group_name'],
					'gid'     => $r['group_id'],
					'post_q'  => $r['post_q'],
					'topic_q' => $r['topic_q']
				);
			}
		}

		$mod_string = "Модераторы: ";

		foreach ($this->mod as $moderator)
		{
			if ($moderator['isg'] == 1)
			{
				$mod_string .= "<a href='{$ibforums->base_url}act=Members&amp;max_results=30&amp;filter={$moderator['gid']}&amp;sort_order=asc&amp;sort_key=name&amp;st=0&amp;b=1'>{$moderator['gname']}</a>, ";
			} else
			{
				$mod_string .= "<a href='{$ibforums->base_url}showuser={$moderator['id']}'>{$moderator['name']}</a>, ";
			}
		}

		$mod_string = preg_replace("!,\s+$!", "", $mod_string);

		return $mod_string;
	}

	// Song * attachments, 16.03.05

	function set_attach($row = array())
	{
		global $ibforums;

		if ($row['attach_size'])
		{
			return;
		}

		$path = $ibforums->vars['upload_dir'] . "/" . $row['attach_id'];

		$size = "Error";

		if (file_exists($path) and $row['attach_type'])
		{
			$size = round(filesize($path) / 1024, 2);
		}

		$ibforums->db->exec("UPDATE ibf_posts SET
			attach_size='" . $size . "'
		    WHERE pid='" . $row['pid'] . "'");

		$row['attach_size'] = $size;
	}

	// Song * attachments, 16.03.05
	// Song * safe code tag withing wrapping long lines, 03.11.2004

	function cut_code_tag_text($code, $syntax)
	{

		// add new syntax record
		if ($syntax)
		{
			$txt = "[CODE=" . $syntax . "]" . $code . "[/CODE]";
		} else
		{
			$txt = "[CODE]" . $code . "[/CODE]";
		}

		$temp = $this->code_counter;

		$this->code_text[$temp] = $txt;

		$this->code_counter++;

		return "song_code_" . $temp . "#";
	}

	// vot: safe code tag withing wrapping long lines, 03.11.2004

	function cut_html_tag_text($code)
	{

		$this->code_text[$this->code_counter] = "<" . $code . ">";

		//	$this->code_counter++;

		return "song_code_" . $this->code_counter++ . "#";
	}

	// Song * safe code tag withing wrapping long lines, 03.11.2004
	/**
	 * @param PDOStatementWrapper $stmt
	 * @param int $pinned
	 * @param int $offset
	 * @param bool $preview_one_post
	 */
	function process_posts($stmt, $pinned = 0, $offset = 0, $preview_one_post = false)
	{
		global $ibforums, $std;

		// Song * quote rights

		$qr = $std->check_perms($this->forum['reply_perms']);

		if ($qr == TRUE)
		{
			// Do we have the ability to post in

			if ($this->topic['state'] == 'closed')
			{
				if (!($ibforums->member['g_is_supmod']
				      or $this->mod[$ibforums->member['id']])
				)
				{
					$qr = FALSE;
				}
			}

			// Song * Old Topics Flood, 15.03.05
			if ($std->user_reply_flood($this->topic['start_date']))
			{
				$qr = FALSE;
			}
		}

		//-------------------------------------
		// Format and print out the topic list
		//-------------------------------------

		$post_count = 0; // Use this as our master bater, er... I mean counter.

		if ($pinned)
		{
			$post_count = 0;
		} else
		{
			$post_count = $this->first + $offset;
		}

		$this->alter_post = 0;

		while ($row = $stmt->fetch())
		{

			// Song * premoderation, 16.03.05

			if ($row['queued'] and !$std->premod_rights($row['author_id'], $this->mod[$ibforums->member['id']]['post_q'], $row['app']))
			{
				continue;
			}

			$this->output .= $this->process_one_post($row, $pinned, $post_count, $qr, $preview_one_post);
			$this->output .= View::make("topic.RowSeparator");
		} //while
	}

	function process_one_post($row, $pinned, &$post_count, $qr, $preview = false)
	{
		global $ibforums, $std, $print;
		// Song * quote with post link, 26.11.04
		// remember posts that have been on the page
		$this->parser->cache_posts[$row['pid']] = $row['pid'];

		$poster = array();

		// Get the member info. We parse the data and cache it.
		// It's likely that the same member posts several times in
		// one page, so it's not efficient to keep parsing the same
		// data

		if ($row['author_id'])
		{
			// reset if the current post declined
			if ($row['use_sig'] and isset($this->cached_members[$row['author_id']]))
			{
				$this->cached_members[$row['author_id']] = array();
			}

			// Is it in the hash?
			if (isset($this->cached_members[$row['author_id']]['id']))
			{
				// Ok, it's already cached, read from it
				$poster = $this->cached_members[$row['author_id']];

				// Song * correct cached fields for new post id

				$poster['pid']     = $row['pid'];
				$poster['rep']     = $row['rep'];
				$poster['use_sig'] = $row['use_sig'];
				$poster            = $this->correct_cached_fields($poster);

				// Song * correct cached fields for new post id

				$row['name_css'] = 'normalname';
			} else
			{
				$row['name_css'] = 'normalname';
				$poster          = $this->parse_member($row);

				// Add it to the cached list
				if (!$row['use_sig'])
				{
					$this->cached_members[$row['author_id']] = $poster;
				}
			}
		} else
		{
			// It's definately a guest...
			$poster          = $std->set_up_guest($row['author_name']);
			$row['name_css'] = 'unreg';
		}

		//--------------------------------------------------------------

		$row['post_css'] = $post_count % 2
			? 'post1'
			: 'post2';

		if ($ibforums->member['id'] and
		    ($ibforums->member['g_is_supmod'] or
		     ($this->mod[$ibforums->member['id']] and
		      $this->mod[$ibforums->member['id']]['post_q'])) and
		    $row['queued']
		)
		{
			$row['post_css'] = 'darkrow2';
		}

		//--------------------------------------------------------------
		// Do word wrap?
		//--------------------------------------------------------------
		// Song * word wrapping only for text out of the code tag text, 03.11.2004

		if ($ibforums->vars['post_wordwrap'] > 0 and !$row['use_sig'])
		{
			// check if code tag text is present
			if ($ibforums->member['syntax'] != 'none' and
			    (mb_strpos($row['post'], "[code") !== FALSE or
			     mb_strpos($row['post'], "[CODE") !== FALSE)
			)
			{
				// first state of post
				$old_old_post = $row['post'];

				// reset array
				$this->code_text    = array();
				$this->code_counter = 0;

				// collect code tag text to array and remove it
				$row['post'] = preg_replace_callback('#\[code\s*?(?:=\s*?(.*?)|)\s*\](.*?)\[/code\]#is',
						function($a) {
								return $this->cut_code_tag_text($a[2], $a[1]);
						}
						, $row['post']);

				// if changing has been
				if ($row['post'] != $old_old_post)
				{
					// state of post after removing code tag text
					$old_post = $row['post'];

					// do word wrap
					$row['post'] = $this->parser->my_wordwrap($row['post'], $ibforums->vars['post_wordwrap']);

					// restore code tag text from an array if post has been wrapped
					if ($row['post'] != $old_post)
					{
						foreach ($this->code_text as $idx => $code)
						{

							$row['post'] = str_replace("song_code_{$idx}#", $code, $row['post']);
						}
					} else
					{
						$row['post'] = $old_old_post;
					}
				}

				// do word wrap, code tag text is absent
			} else
			{
				$row['post'] = $this->parser->my_wordwrap($row['post'], $ibforums->vars['post_wordwrap']);
			}
		}

		if (($row['attach_id'] or $row['attach_exists']) and !$row['use_sig'])
		{

			$attachments = Attachment::getPostAttachmentsFromRow($row);
		}
		//--------------------------------------------------------------

		if (!$row['use_sig'] || $preview)
		{
			$data = array(
				'TEXT'        => $row['post'],
				'SMILIES'     => $row['use_emo'],
				'CODE'        => 1,
				'SIGNATURE'   => 0,
				'HTML'        => 1,
				'HID'         => $this->highlight,
				'TID'         => $this->topic['tid'],
				'MID'         => $row['author_id'],
				'ATTACHMENTS' => $attachments
			);

			$row['post'] = $this->parser->prepare($data);
		}

		//--------------------------------------------------------------
		// vot: HighLight the search words found:

		if ($ibforums->input['hl'])
		{

			// reset array
			$this->code_text = array();

			// vot: collect html tag text to array and remove it
			$row['post'] = preg_replace("#<(.*?)>#ies", "\$this->cut_html_tag_text('\\1')", $row['post']);

			$keywords = str_replace("+", " ", $ibforums->input['hl']);

			if (preg_match("/,(and|or),/i", $keywords))
			{
				while (preg_match("/,(and|or),/i", $keywords, $match))
				{
					$word_array = explode("," . $match[1] . ",", $keywords);

					if (is_array($word_array))
					{
						foreach ($word_array as $keywords)
						{
							$row['post'] = preg_replace("/(?<!<)(" . preg_quote($keywords, '/') . ")/i", "<span class='searchlite'>\\1</span>", $row['post']);
						}
					}
				}
			} else
			{
				$row['post'] = preg_replace("/(" . preg_quote($keywords, '/') . ")/i", "<span class='searchlite'>\\1</span>", $row['post']);
			}

			// vot: restore code tag text from an array if post has been wrapped
			foreach ($this->code_text as $idx => $code)
			{
				$row['post'] = str_replace("song_code_{$idx}#", $code, $row['post']);
			}
		}

		//--------------------------------------------------------------

		if ($row['append_edit'] == 1 and $row['edit_time'] != "" and $row['edit_name'] != "" and !$row['use_sig'])
		{
			$e_time = View::make("global.time", ['unixtime' => $row['edit_time'], 'class' => 'post-edit-time']);

			$row['post'] .= View::make(
				"topic.renderEditedPostMessage",
				['message' => sprintf($ibforums->lang['edited_by'], $row['edit_name'], $e_time)]
			);
		}

		if ($row['delete_after'] && !$row['use_sig'] && mb_strlen(rtrim($row['post'])) > 0)
		{

			$days = $std->get_autodelete_message($row['delete_after'], $ibforums->lang['delete_waiting_message'], $ibforums->lang['delete_through_message']);
			$row['post'] .= "<span class='autodelete_message'>{$days}</span>";
		}
		$row['deleting'] = (bool)$row['delete_after'];

		// Song * Add to FAQ button, 02.05.04

		if ($ibforums->member['id'] and $this->forum['faq_id'] and
		                                ($this->moderator['add_to_faq'] or $ibforums->member['g_is_supmod'])
		)
		{
			$row['add_to_faq'] = "<a href='{$this->base_url}act=Mod&amp;CODE=35&amp;auth_key=" . $this->md5_check . "&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$row['pid']}'>" . (($row['added_to_faq'])
				? '<span class="b-added-to-faq__message">' . $ibforums->lang['added_to_FAQ']. '</span>'
				: $ibforums->lang['to_FAQ']) . "</a>";
		}

		// Song * delete post button

		if ((($post_count and !$this->first) or $this->first) and !$row['use_sig'])
		{
			$row['delete_button'] = ($qr)
				? $this->delete_button($row['pid'], $poster, $row['queued'])
				: "";
		}

		// Song * delete delayed button, 13.04.05

		if ($this->forum['days_off'] && !$row['use_sig'])
		{
			$row['delete_delayed'] = ($qr)
				? $this->delayed_delete_button($row, $poster, $post_count)
				: "";
		}

		// Song * edit post button
		if (!$row['use_sig'])
		{
			$row['edit_button'] = ($qr == FALSE)
				? ""
				: $this->edit_button($row['pid'], $poster, $row['post_date']);
		}
		if ($row['use_sig'])
		{
			$row['show_preview_button'] = ($qr == FALSE)
				? ""
				: $this->show_preview_button($row['pid'], $poster, $row['post_date'], $preview);
		}

		// negram * history edit post button
		if ($row['edit_time'] && $row['edit_name'] != "")
		{

			$row['edit_history_button'] = ($qr == FALSE)
				? ""
				: $this->edit_history_button($row['pid'], $poster, $row['post_date']);
		} else
		{
			$row['edit_history_button'] = '';
		}

		// Song * restore/decline buttons

		if (!$row['queued'] and ($this->moderator['delete_post'] or $ibforums->member['g_is_supmod']))
		{
			if ($row['use_sig'])
			{
				$row['restore_decline'] = "<a href='{$ibforums->base_url}act=Mod&amp;CODE=19&amp;auth_key=" . $this->md5_check . "&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$row['pid']}' onclick=\"return restoreAndDecline(this,{$row['pid']});\"><{P_RESTORE}></a>";
			} else
			{
				$row['restore_decline'] = "<a href='{$ibforums->base_url}act=Mod&amp;CODE=18&amp;auth_key=" . $this->md5_check . "&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$row['pid']}' onclick=\"return restoreAndDecline(this,{$row['pid']});\"><{P_DECLINE}></a>";
			}
		}

		// Pin\UnPin checkbox

		if (!$pinned)
		{
			$row['checkbox'] = $this->checkbox($row['pid']);
		}

		// Song * today/yesterday

		$row['old_post_date'] = $row['post_date'];

		$row['std_post_date'] = date('r', $row['post_date']);
		$row['post_date'] = $std->get_date($row['post_date']);

		$row['post_icon'] = ($row['icon_id'] and $ibforums->member['view_img'])
			? "<img src='" . $ibforums->skin['ImagesPath'] . "/icon{$row['icon_id']}.gif' alt='' />&nbsp;&nbsp;"
			: "";

		$row['ip_address'] = $this->view_ip($row, $poster);

		$row['report_link'] = ($ibforums->vars['disable_reportpost'] != 1 and $ibforums->member['id'])
			? View::make("topic.report_link", ['data' => $row])
			: "";

		// Song * reputation

		$row['rep_options'] = $this->rep_options($poster['id'], $row['pid']);

		// Song * attachments, 16.03.05

		if (($row['attach_id'] or $row['attach_exists']) and !$row['use_sig'])
		{

			$attachments = Attachment::getPostAttachmentsFromRow($row);

			//----------------------------------------------------
			// If we've not already done so, lets grab our mime-types
			//----------------------------------------------------

			if (!is_array($this->mimetypes))
			{
				require "./conf_mime_types.php";
				$this->mimetypes = $mime_types;
				unset($mime_types);
			}

			foreach ($attachments as $attach)
			{
				($attach instanceof Attachment);

				if (in_array($attach->attachId(), $this->parser->attachments))
				{
					/*
					 * skip attachments which was atready displayed via [attach] tag
					 */
					continue;
				} elseif (in_array($row['pid'], $this->parser->post_attachments))
				{
					continue;
				}
				$row['attachment'][] = $this->parser->render_attach($attach, '');
				continue;
			} // foreach
			$row['attachment'] = is_array($row['attachment'])
				? "<br>" . implode("<br>", $row['attachment'])
				: '';
			unset($attachments);
		}

		//--------------------------------------------------------------
		// Siggie stuff
		//--------------------------------------------------------------

		$row['signature'] = "";

		if ($poster['signature'] and $ibforums->member['view_sigs'] and !$row['use_sig'])
		{
			if (!$row['g_use_signature'])
			{
				$poster['signature'] = "[color=gray][size=1]Подпись выключена.[/size][/color]";
			}

			$data = array(
				'TEXT'      => $poster['signature'],
				'SMILIES'   => 1,
				'CODE'      => 1,
				'SIGNATURE' => 0,
				'HTML'      => $ibforums->vars['sig_allow_html'],
				'HID'       => -1,
				'TID'       => 0,
				'MID'       => $row['author_id'],
			);

			$poster['signature'] = $this->parser->prepare($data);

			if ($ibforums->vars['sig_allow_html'])
			{
				$poster['signature'] = $this->parser->parse_html($poster['signature'], 0);
			}

			if ($ibforums->vars['post_wordwrap'] > 0)
			{
				$poster['signature'] = $this->parser->my_wordwrap($poster['signature'], $ibforums->vars['post_wordwrap']);
			}

			$row['signature'] = View::make("global.signature_separator", ['sig' => $poster['signature']]);
		}

		// Song * quote
		$poster['name'] = str_replace(array("&#39;", "'"), array("", ""), $poster['name']);
		$name           = $poster['name'];

		$poster['name'] = str_replace(array("[", "]"), array("&amp;#091;", "&amp;#093;"), $poster['name']);

		$rname = str_replace("\'", "\\'", $poster['name']);

		if (!$row['use_sig'])
		{
			$poster['name'] = "<a onmouseover=\"get_name('[b]{$poster['name']}[/b]');\" href=\"javascript:Insert();\">";
		} else
		{
			$poster['name'] = "<a href='{$ibforums->base_url}showuser={$poster['id']}' target='_blank'>";
		}
		// /Song * quote
		// Song * quote and quickquote post buttons

		$row['quote'] = ($qr == FALSE or $row['use_sig'])
			? ""
			: "<a href='{$this->base_url}act=Post&amp;CODE=06&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$row['pid']}'><{P_QUOTE}></a>";

		// Song * quickquote post buttons

		$row['quick_quote'] = ($qr == FALSE or $row['use_sig'])
			? ""
			: "<a onmouseover=\"get_selection('{$rname}', '{$row['old_post_date']}', '{$row['pid']}');\" href=\"javascript:Insert();\"><{P_QUICKQUOTE}></a>";

		// Song * leave link from guests nick too

		if (!$row['author_id'])
		{
			$poster['name'] .= "<span class='unreg'>";
		}
		$poster['name'] .= $name;

		if (!$row['author_id'])
		{
			$poster['name'] .= "</span>";
		}
		$poster['name'] .= "</a>";

		//--------------------------------------------------------------
		// Parse HTML tag on the fly
		//--------------------------------------------------------------

		if ($this->forum['use_html'] == 1 and !$row['use_sig'])
		{
			// So far, so good..

			if (stristr($row['post'], '[dohtml]'))
			{
				// [doHTML] tag found..

				$parse = ($this->forum['use_html'] AND $row['g_dohtml'])
					? 1
					: 0;

				$row['post'] = $this->parser->post_db_parse($row['post'], $parse);
			}
		}

		$post_actions = [];

		if ($ibforums->member['id'] and
		    ($ibforums->member['g_is_supmod'] or
		     ($this->mod[$ibforums->member['id']] and
		      $this->mod[$ibforums->member['id']]['topic_q'])) and
		    !$this->topic['approved'] and !$post_count
		)
		{
			$post_actions[] = View::make(
				"topic.approveTopicLink",
				['fid' => $this->forum['id'], 'tid' => $this->topic['tid']]
			);
			$post_actions[] = View::make(
				"topic.rejectTopicLink",
				['fid' => $this->forum['id'], 'tid' => $this->topic['tid']]
			);

		}

		if ($ibforums->member['id'] and
		    ($ibforums->member['g_is_supmod'] or
		     ($this->mod[$ibforums->member['id']] and
		      $this->mod[$ibforums->member['id']]['post_q'])) and
		    $row['queued'] and $this->topic['approved']
		)
		{
			$post_actions[] = View::make(
				"topic.approvePostLink",
				['fid' => $this->forum['id'], 'tid' => $this->topic['tid'], 'pid' => $row['pid']]
			);
		}

		$row['queued'] = $row['queued'] || !$this->topic['approved'];

		$this->alter_post = $row['pid'];

		//--------------------------------------------------------------
		// A bit hackish - but there are lots of <br> => <br /> changes to make
		//--------------------------------------------------------------
		// Song * online
		if ($row['s_id'])
		{
			$poster['online'] = View::make("topic.renderElementOnline");
		} else
		{
			$poster['online'] = "";
		}

		// Song * fixed any post css
		// title of post
		if ($pinned)
		{
			$row['pinned_title'] = $ibforums->lang['entry_pinned_post'];
			$row['post_css']     = "pinned_topic";
			$row['pinned'] = TRUE;
		} else
		{
			$row['pinned_title'] = $ibforums->lang['entry_post'];
		}

		if (trim($row['post']) != '')
		{
			$post_count++;
			$poster['postcount'] = $post_count;

			array_push($post_actions,
				$row['queued_link'],
				$row['quick_quote'],
				$row['add_to_faq'],
				$row['restore_decline'],
				$row['report_link'],
				$row['delete_button'],
				$row['edit_button'],
				$row["show_preview_button"],
				$row['edit_history_button'],
				$row['quote'],
				$row['delete_delayed']
			);
			$row['html_actions'] = View::make(
				"global.renderActionButtons",
				[
					'actions'      => $post_actions,
					'list_classes' => 'b-post__actions',
					'item_classes' => 'b-post-action-button'
				]
			);

			// Song * message has been deleted by moderator, 13.11.2004, or by author (negram, January 2011)

			if ($row['use_sig'])
			{
				if ($ibforums->input['ajax'])
				{
					header('Content-Type: text/html; charset=utf-8');
					echo $print->prepare_output(
						View::make(
							"topic.RenderDeletedRow",
							['post' => $row, 'author' => $poster, 'preview' => $preview]
						)
					);
					exit;
				} else
				{
					return View::make(
						"topic.RenderDeletedRow",
						['post' => $row, 'author' => $poster, 'preview' => $preview]
					);
				}
			} else
			{

				if (intval($ibforums->member['post_wrap_size']) != 0 && $ibforums->member['post_wrap_size'] < mb_strlen(strip_tags($row['post'])) && $row['new_topic'] != 1)
				{
					$row['post'] = '<div class="spoiler closed"><div class="spoiler_header" onclick="openCloseParent(this)">Многа букав</div><div class="body">' . $row['post'] . '</div></div>';
				}

				return View::make("topic.RenderRow", ['post' => $row, 'author' => $poster]);
			}
		}
	}

	// Song * cut mod tags for cancelled posts, 01.12.2004

	function mod_tags_cut($the_tag, $txt)
	{

		$the_tag = mb_strtoupper($the_tag);

		$this->mod_tags .= "[" . $the_tag . "]" . $txt . "[/" . $the_tag . "]";
	}

	function Topics()
	{
		global $ibforums, $std, $print;

		$this->md5_check = $std->return_md5_check();

		$this->base_url = $ibforums->base_url;
		//-------------------------------------
		// Compile the language file
		//-------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_error', $ibforums->lang_id);

		$this->parser = new PostParser();

		//-------------------------------------
		// Check the input
		//-------------------------------------

		$ibforums->input['t'] = intval($ibforums->input['t']);

		if ($ibforums->input['t'] < 0)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		//-------------------------------------
		// Get the forum info based on the forum ID,
		// get the category name, ID, and get the topic details
		//-------------------------------------

		if (!$ibforums->topic_cache['tid'])
		{
			$stmt = $ibforums->db->query("SELECT
				t.*,
				f.topic_mm_id, f.name as forum_name,
				f.quick_reply, f.id as forum_id,
				f.read_perms, f.start_perms, f.reply_perms,
				f.parent_id, f.use_html,
			        f.forum_highlight, f.highlight_fid,
			        f.allow_poll, f.password,
				f.posts as forum_posts,
				f.topics as forum_topics, f.upload_perms,
			        f.show_rules, f.rules_text, f.rules_title,
				f.red_border, f.siu_thumb, f.inc_postcount,
				f.days_off, f.decided_button, f.faq_id ,
				c.name as cat_name, c.id as cat_id
			    FROM
				ibf_topics t,
				ibf_forums f,
				ibf_categories c
			    WHERE
				t.tid='" . $ibforums->input['t'] . "'
				AND f.id=t.forum_id
				AND f.category=c.id");

			$this->topic = $stmt->fetch();
		} else
		{
			$this->topic = $ibforums->topic_cache;
		}

		$this->highlight = $this->topic['forum_highlight']
			? $this->topic['highlight_fid']
			: -1;

		$this->forum = array(
			'id'            => $this->topic['forum_id'],
			'name'          => $this->topic['forum_name'],
			'posts'         => $this->topic['forum_posts'],
			'topics'        => $this->topic['forum_topics'],
			'read_perms'    => $this->topic['read_perms'],
			'start_perms'   => $this->topic['start_perms'],
			'reply_perms'   => $this->topic['reply_perms'],
			'allow_poll'    => $this->topic['allow_poll'],
			'upload_perms'  => $this->topic['upload_perms'],
			'parent_id'     => $this->topic['parent_id'],
			'password'      => $this->topic['password'],
			'quick_reply'   => $this->topic['quick_reply'],
			'use_html'      => $this->topic['use_html'],
			'topic_mm_id'   => $this->topic['topic_mm_id'],
			'siu_thumb'     => $this->topic['siu_thumb'],
			'inc_postcount' => $this->topic['inc_postcount'],
			'days_off'      => $this->topic['days_off'],
			'decided'       => $this->topic['decided_button'],
			'faq_id'        => $this->topic['faq_id'],
		);

		$this->category = array(
			'name' => $this->topic['cat_name'],
			'id'   => $this->topic['cat_id'],
		);

		$ibforums->input['f']    = $this->forum['id'];
		$this->parser->siu_thumb = $this->forum['siu_thumb'];
		//-------------------------------------
		// Error out if we can not find the forum or the topic
		//-------------------------------------

		if (!$this->forum['id'] or !$this->topic['tid'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'is_broken_link'));
		}

		// Song * NEW + topic subscribe, 16.01.05

		if ($ibforums->member['id'])
		{
			$stmt = $ibforums->db->query("SELECT trid
			    FROM ibf_tracker
			    WHERE
				member_id='" . $ibforums->member['id'] . "' and
				topic_id='" . $this->topic['tid'] . "'");

			if ($stmt->rowCount())
			{
				$trid = $stmt->fetch();

				$this->trid = $trid['trid'];
			}
		}

		//-------------------------------------
		// If this forum is a link, then
		// redirect them to the new location
		//-------------------------------------

		if ($this->topic['state'] == 'link')
		{
			$f_stuff = explode("&", $this->topic['moved_to']);

			$print->redirect_screen($ibforums->lang['topic_moved'], "showtopic={$f_stuff[0]}");
		} elseif ($this->topic['state'] == 'mirror')
		{

			$f_stuff = explode("&", $this->topic['moved_to']);
			$append  = '';
			if (isset($ibforums->input['view']))
			{
				$append = "&view={$ibforums->input['view']}";
			}

			if (isset($ibforums->input['st']))
			{
				$append = "&st={$ibforums->input['st']}";
			}

			$print->redirect_screen($ibforums->lang['topic_moved'], "showtopic={$f_stuff[0]}$append");
		}

		// Song * club tool

		if ($this->topic['club'] and $std->check_perms($ibforums->member['club_perms']) == FALSE)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'is_broken_link'));
		}

		// Song * user ban patch

		$std->user_ban_check($this->forum);

		// get moderators
		$moderators = $this->get_moderators();

		//-------------------------------------
		// Error out if the topic is not approved
		//-------------------------------------
		// Song * premoderation, 16.03.05

		if (!$this->topic['approved'])
		{
			$this->topic['state'] = "closed";

			if (!$std->premod_rights($this->topic['starter_id'], $this->mod[$ibforums->member['id']]['topic_q'], $this->topic['app']))
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}
		}

		//-------------------------------------
		// Check viewing permissions, private forums,
		// password forums, etc
		//-------------------------------------

		if ((!$this->topic['pinned']) and ((!$ibforums->member['g_other_topics']) AND ($this->topic['starter_id'] != $ibforums->member['id'])))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_view_topic'));
		}

		$bad_entry = $this->check_access();

		if ($bad_entry)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_view_topic'));
		}

		if ($ibforums->member['id'] and !$ibforums->member['g_is_supmod'])
		{
			$stmt = $ibforums->db->query("SELECT *
				    FROM ibf_moderators
				    WHERE
					forum_id=" . $this->forum['id'] . "
					AND (member_id=" . $ibforums->member['id'] . "
					     OR (is_group=1
						 AND group_id='" . $ibforums->member['mgroup'] . "'))");

			$this->moderator = $stmt->fetch();
		}

		if ($ibforums->input['act'] == 'Mod')
		{
			return;
		}

		//--------------------------------------------------------------------
		// Are we looking for an older / newer topic?
		//--------------------------------------------------------------------

		if (isset($ibforums->input['view']))
		{
			if ($ibforums->input['view'] == 'new' or $ibforums->input['view'] == 'old')
			{
				$query = "SELECT tid
				  FROM ibf_topics
				  WHERE
					approved=1 and
					state<>'link' and
					forum_id='" . $this->forum['id'] . "'";

				if ($ibforums->input['view'] == 'new')
				{
					$query .= " and last_post > '" . $this->topic['last_post'] . "'";
				} else
				{
					$query .= " and last_post < '" . $this->topic['last_post'] . "'";
				}

				$sort = ($ibforums->input['view'] == 'old')
					? " DESC"
					: "";

				$query .= " ORDER BY last_post{$sort} LIMIT 1";

				$stmt = $ibforums->db->query($query);

				if ($stmt->rowCount())
				{
					$this->topic = $stmt->fetch();
					$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid']);
				} else
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_newer'
					            ));
				}
			} else {
				if ($ibforums->input['view'] == 'getlastpost')
				{
					$this->return_last_post();
				} else
				{
					if ($ibforums->input['view'] == 'getnewpost')
					{

						$last_read_time = $std->get_topic_last_read($this->topic['tid']);

						$topic_is_unreaded = ($last_read_time == 0);

						if ($topic_is_unreaded)
						{

							$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid']);
						} else
						{
							$stmt = $ibforums->db->query("SELECT
						pid,
						post_date
					    FROM ibf_posts
					    WHERE
						queued != 1
						AND topic_id='" . $this->topic['tid'] . "'
						AND use_sig = 0
						AND post_date > $last_read_time
					    ORDER BY post_date
					    LIMIT 1");

							if ($post = $stmt->fetch())
							{

								$pid = "&#entry" . $post['pid'];

								$stmt = $ibforums->db->query("SELECT COUNT(pid) AS posts
						    FROM ibf_posts
						    WHERE
							topic_id='" . $this->topic['tid'] . "'
							AND pid <= '" . $post['pid'] . "'");

								if (!$cposts = $stmt->fetch() or $cposts['posts'] == 0)
								{
									$std->Error(array('LEVEL' => 1, 'MSG' => 'no_post'));
								}

								if ((($cposts['posts']) % $ibforums->vars['display_max_posts']) == 0)
								{
									$pages = ($cposts['posts']) / $ibforums->vars['display_max_posts'];
								} else
								{
									$number = (($cposts['posts']) / $ibforums->vars['display_max_posts']);
									$pages  = ceil($number);
								}

								$st = ($pages - 1) * $ibforums->vars['display_max_posts'];

								$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid'] . "&amp;st=$st" . $pid);
							} else
							{

								$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid'] . "&amp;view=getlastpost");
							}
						}

						exit();
					} elseif ($ibforums->input['view'] == 'findpost')
					{
						$pid = intval($ibforums->input['p']);

						if ($pid > 0)
						{
							$stmt = $ibforums->db->query("SELECT COUNT(pid) AS posts
					    FROM ibf_posts
					    WHERE
						topic_id='" . $this->topic['tid'] . "' and
						pid <= '" . $pid . "'");

							if (!$cposts = $stmt->fetch() or !$cposts['posts'])
							{
								$tid = 0;

								$stmt = $ibforums->db->query("SELECT topic_id
						    FROM ibf_posts
						    WHERE pid='" . $pid . "'");

								if ($topic = $stmt->fetch())
								{
									$tid = $topic['topic_id'];
								}

								if ($tid and $tid != $this->topic['tid'])
								{
									// repeat query
									$stmt = $ibforums->db->query("SELECT COUNT(pid) AS posts
							 FROM ibf_posts
							 WHERE topic_id='" . $tid . "'
							 AND pid <= '" . $pid . "'");
								}

								if (!$tid or !$cposts = $stmt->fetch() or $cposts['posts'] == 0)
								{
									$std->Error(array(
									                 'LEVEL' => 1,
									                 'MSG'   => 'no_post'
									            ));
								}

								$this->topic['tid'] = $tid;
							}

							if ((($cposts['posts']) % $ibforums->vars['display_max_posts']) == 0)
							{
								$pages = ($cposts['posts']) / $ibforums->vars['display_max_posts'];
							} else
							{
								$number = (($cposts['posts']) / $ibforums->vars['display_max_posts']);
								$pages  = ceil($number);
							}

							$st = ($pages - 1) * $ibforums->vars['display_max_posts'];

							$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid'] . "&amp;st=$st" . "&#entry" . $pid);
							exit();
						} else
						{
							$this->return_last_post();
						}
					}
				}
			}
		}

		$this->forum['JUMP'] = $std->build_forum_jump_topics();

		//----------------------------------------------
		// Update the topic views counter and topic logs
		//----------------------------------------------

		$ibforums->db->exec("UPDATE ibf_topics
		    SET views=views+1
		    WHERE tid='" . $this->topic['tid'] . "'");

		$std->song_set_topicread($this->forum['id'], $this->topic['tid']);

		if ($this->topic['has_mirror'])
		{
			$ibforums->db->exec("UPDATE ibf_topics
				    SET views=views+1
				    WHERE mirrored_topic_id='{$this->topic['tid']}'");

			// update forums
			$q = "SELECT forum_id, tid FROM ibf_topics WHERE mirrored_topic_id='{$this->topic['tid']}' ";

			$stmt = $ibforums->db->query($q);
			while ($row = $stmt->fetch())
			{
				$log_time = 0;
				if ($ibforums->member['id'])
				{
					$stmt = $ibforums->db->query("SELECT logTime
					    FROM ibf_log_topics
					    WHERE
						tid='" . $row['tid'] . "' AND
						mid='" . $ibforums->member['id'] . "'");

					if ($stmt->rowCount())
					{
						$log_time = $stmt->fetch();

						$log_time = $log_time['logTime'];
					}
				}

				$std->song_set_topicread($row['forum_id'], $row['tid']);
			}
		}

		//----------------------------------------
		// If this is a sub forum, we need to get
		// the cat details, and parent details
		//----------------------------------------
		// Song * endless forums, 20.12.04

		$this->base_url = $ibforums->base_url;

		if ($this->forum['parent_id'] > 0)
		{
			$stmt = $ibforums->db->query("SELECT
				f.id as forum_id,
				f.name as forum_name,
				c.id,
				c.name
			    FROM
				ibf_forums_order fo,
				ibf_forums f,
				ibf_categories c
			    WHERE
				fo.id='" . $this->forum['id'] . "'
				AND f.id=fo.pid
				AND c.id=f.category");

			while ($row = $stmt->fetch())
			{
				if ($row['forum_id'] == $this->forum['parent_id'])
				{
					$this->category['id']   = $row['id'];
					$this->category['name'] = $row['name'];
				}

				$this->nav_extra[] = "<a href='" . $this->base_url . "showforum={$row['forum_id']}'>{$row['forum_name']}</a>";
			}

			$this->nav_extra = array_reverse($this->nav_extra);
		}

		// add category and current forum
		array_unshift($this->nav_extra, "<a href='" . $this->base_url . "act=SC&amp;c={$this->category['id']}'>{$this->category['name']}</a>");

		array_push($this->nav_extra, "<a href='" . $this->base_url . "showforum={$this->forum['id']}'>{$this->forum['name']}</a>");

		//-------------------------------------
		// Get all the member groups and
		// member title info
		//-------------------------------------

		$stmt = $ibforums->db->query("SELECT
			id,
			title,
			pips,
			posts
		    FROM ibf_titles
		    ORDER BY posts DESC");

		while ($i = $stmt->fetch())
		{
			$this->mem_titles[$i['id']] = array(
				'TITLE' => $i['title'],
				'PIPS'  => $i['pips'],
				'POSTS' => $i['posts'],
			);
		}

		//-------------------------------------
		// Are we a moderator?
		//-------------------------------------

		$this->mod_action = array(
			'CLOSE_TOPIC'         => '00',
			'OPEN_TOPIC'          => '01',
			'MOVE_TOPIC'          => '02',
			'DELETE_TOPIC'        => '03',
			'EDIT_TOPIC'          => '05',
			'PIN_TOPIC'           => '15',
			'HIDE_TOPIC'          => '26',
			'SHOW_TOPIC'          => '27',
			'UNPIN_TOPIC'         => '16',
			'UNSUBBIT'            => '30',
			'SPLIT_TOPIC'         => '50',
			'MERGE_TOPIC'         => '60',
			'DELETE_POSTZ'        => '66',
			'MOVE_POSTZ'          => '67',
			'ATTACH_LINKS'        => '70',
			'PIN_POST'            => '62',
			'UNPIN_POST'          => '63',
			'TOPIC_HISTORY'       => '90',
			'DELETE_DELAYED'      => '32',
			'FAQ_POSTS'           => '35',
			'MIRROR_TOPIC'        => '91',
			'DELETE_TOPIC_MIRROR' => '93',
		);

		//-------------------------------------
		// Get the reply, and posting buttons
		//-------------------------------------
		// Song * define rights for creating topic

		$this->topic['TOPIC_BUTTON'] = ($this->allow_topic())
			? "<a href='" . $this->base_url . "act=Post&amp;CODE=00&amp;f=" . $this->forum['id'] . "'><{A_POST}></a>"
			: '';

		$this->topic['POLL_BUTTON'] = ($this->forum['allow_poll'])
			? "<a href='" . $this->base_url . "act=Post&amp;CODE=10&amp;f=" . $this->forum['id'] . "'><{A_POLL}></a>"
			: '';

		$this->topic['UPPER_POLL_BUTTON'] = $this->topic['POLL_BUTTON'];

		$this->topic['REPLY_BUTTON'] = $this->reply_button();

		// Song * decided topics, 20.04.05

		if ($this->forum['decided'])
		{
			$this->decided_button($this->topic['SOLVE_UPPER_BUTTON'], $this->topic['SOLVE_DOWN_BUTTON']);
		}

		// /Song * decided topics, 20.04.05

		if ($ibforums->input['hl'])
		{
			$ibforums->input['hl'] = $std->clean_value(urldecode($ibforums->input['hl']));

			$hl = '&amp;hl=' . $ibforums->input['hl'];
		}

		// Song * show all posts at once

		if ($ibforums->input['view'] == "showall" and $this->topic['posts'] > $ibforums->vars['max_show_all_posts'])
		{
			$ibforums->input['view'] = "";
		}

		//-------------------------------------
		// Generate the forum page span links
		//-------------------------------------

		if ($ibforums->input['view'] != "showall")
		{
			$this->topic['SHOW_PAGES'] = $std->build_pagelinks(array(
			                                                        'TOTAL_POSS' => ($this->topic['posts'] + 1),
			                                                        'PER_PAGE'   => $ibforums->vars['display_max_posts'],
			                                                        'CUR_ST_VAL' => $ibforums->input['st'],
			                                                        'L_SINGLE'   => "",
			                                                        'BASE_URL'   => $this->base_url . "showtopic=" . $this->topic['tid'] . $hl,
			                                                   ));
		}

		$this->topic['why_close'] = ($this->topic['state'] == 'closed')
			? $this->topic['why_close']
			: '';

		if ($this->topic['why_close'])
		{
			$this->topic['why_close'] = $this->parser->prepare(array(
			                                                        'TEXT'    => $this->topic['why_close'],
			                                                        'SMILIES' => 1,
			                                                        'CODE'    => 1,
			                                                        'HTML'    => 0
			                                                   ));
		}

		$this->topic['end_why_close'] = $this->topic['why_close'];

		if ($this->topic['SHOW_PAGES'] != '' and $this->topic['state'] == 'closed')
		{
			$this->topic['end_why_close'] = '<br><br>' . $this->topic['end_why_close'];
			$this->topic['why_close'] .= '<br><br>';
		}

		// Song * show all posts at once

		if ($ibforums->input['view'] != "showall" and ($this->topic['posts'] + 1) > $ibforums->vars['display_max_posts'])
		{
			$this->topic['go_new'] = View::make(
				"topic.golastpost_link",
				['fid' => $this->forum['id'], 'tid' => $this->topic['tid']]
			);
		}

		//-------------------------------------
		// Fix up some of the words
		//-------------------------------------

		$this->topic['TOPIC_START_DATE'] = $std->get_date($this->topic['start_date']);

		$ibforums->lang['topic_stats'] = preg_replace("/<#START#>/", $this->topic['TOPIC_START_DATE'], $ibforums->lang['topic_stats']);
		$ibforums->lang['topic_stats'] = preg_replace("/<#POSTS#>/", $this->topic['posts'], $ibforums->lang['topic_stats']);

		if ($this->topic['description'])
			$this->topic['description'] = ', ' . $this->topic['description'];

		//-------------------------------------
		// Render the page top
		//-------------------------------------

		$this->forum['moderators'] = $moderators;

		$this->topic['fav_text'] = "";

		if ($ibforums->member['id'])
		{
			$favs = $ibforums->member['favorites']->getTopicIds();
			$txt = in_array($this->topic['tid'], $favs)
				? $ibforums->lang['fav_remove']
				: $ibforums->lang['fav_add'];
			$this->topic['fav_text'] = View::make(
				"topic.favoriteButton",
				['tid' => $this->topic['tid'], 'text' => $txt]
			);
			unset($txt);
		}

		$links = "";

		$stmt = $ibforums->db->query("SELECT
			t.tid as topic_id,
			t.title,
			t.description
		    FROM
			ibf_topics t,
			ibf_topiclinks tl
		    WHERE
			tl.tid='" . $this->topic['tid'] . "'
			AND t.tid=tl.link
		    ORDER BY t.tid");

		if ($stmt->rowCount())
		{
			$links = $ibforums->lang['attached_links'] . "<ul class='topic-attached-links-list'>";

			while ($r = $stmt->fetch())
			{
				$desc = ($r['description'])
					? " (" . $r['description'] . ")"
					: "";

				$links .= "<li><a href='{$ibforums->base_url}showtopic={$r['topic_id']}' target='_blank'>" . $r['title'] . "</a>" . $desc . "</li>";
			}
		}

		$stmt = $ibforums->db->query("SELECT
			name,
			link
		    FROM ibf_topicsinfo
		    WHERE tid='" . $this->topic['tid'] . "'
		    ORDER BY date DESC");

		if ($stmt->rowCount())
		{
			if (!$links)
				$links = $ibforums->lang['attached_links'] . "<ul>";

			while ($r = $stmt->fetch())
			{
				$links .= "<li><a href='{$r['link']}' target='_blank'>" . $r['name'] . "</a></li>";
			}
		}

		if ($links)
			$links = $links . "</ul><br>";

		$this->topic['links'] = $links;

		if ($this->moderator['mid'] or $ibforums->member['g_is_supmod'])
		{
			$this->topic['modform_open']  = "<form method='POST' style='display:inline' name='modform' action='{$ibforums->base_url}'>";
			$this->topic['modform_close'] = "</form>";
		}

		// Song * club tool

		$this->topic['club'] = $this->topic['club']
			? $ibforums->lang['club_topic']
			: "";

		// Song * subscribe the topic, 16.01.05

		$this->topic['subscribe'] = ($this->trid)
			? "<a href='{$ibforums->base_url}act=UserCP&amp;CODE=27&amp;id-{$this->trid}=1'>{$ibforums->lang['untrack_topic']}</a>"
			: "<a href='{$ibforums->base_url}act=Track&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}'>{$ibforums->lang['track_topic']}</a>";

		$this->output .= View::make(
			"topic.PageTop",
			['data' => array('TOPIC' => $this->topic, 'FORUM' => $this->forum)]
		);

		//-------------------------------------
		// Do we have a poll?
		//-------------------------------------

		if ($this->topic['poll_state'])
		{
			$this->output = str_replace("<!--{IBF.POLL}-->", $this->parse_poll(), $this->output);
		} else
		{
			// Can we start a poll? Is this our topic and is it still open?

			if ($this->topic['state'] != "closed" AND $ibforums->member['id'] AND $ibforums->member['g_post_polls'] AND $this->forum['allow_poll'])
			{
				if ((($this->topic['starter_id'] == $ibforums->member['id']) AND ($ibforums->vars['startpoll_cutoff'] > 0) AND ($this->topic['start_date'] + ($ibforums->vars['startpoll_cutoff'] * 3600) > time()))
				    OR ($ibforums->member['g_is_supmod'] == 1)
				)
				{
					$this->output = str_replace("<!--{IBF.START_NEW_POLL}-->",
						View::make(
							"topic.start_poll_button",
							['fid' => $this->forum['id'], 'tid' => $this->topic['tid']]
						), $this->output);
				}
			}
		}

		//--------------------------------------------
		// Extra queries?
		//--------------------------------------------

		$join_profile_query = "";
		$join_get_fields    = "";

		if ($ibforums->vars['custom_profile_topic'] == 1)
		{
			//--------------------------------------------
			// Get the data for the profile fields
			//--------------------------------------------

			$stmt = $ibforums->db->query("SELECT
				fid,
				ftype,
				fhide,
				fcontent
			    FROM ibf_pfields_data");

			while ($r = $stmt->fetch())
			{
				$this->pfields['field_' . $r['fid']] = $r;

				if ($r['ftype'] == 'drop')
				{
					foreach (explode('|', $r['fcontent']) as $i)
					{
						list($k, $v) = explode('=', $i, 2);

						$this->pfields_dd['field_' . $r['fid']][$k] = $v;
					}
				}
			}

			$join_profile_query = "LEFT JOIN ibf_pfields_content pc
                                       ON (pc.member_id=p.author_id)";
			$join_get_fields    = ", pc.*";
		}

		//--------------------------------------------
		// Grab the posts we'll need
		//--------------------------------------------

		$this->first = intval($ibforums->input['st']);
		if ($this->first < 0)
			$this->first = 0; // vot

		$this->parser->prepareIcons();

		//--------------------------------------------
		// Optimized query?
		// MySQL.com insists that forcing LEFT JOIN or
		// STRAIGHT JOIN helps the query optimizer, so..
		//--------------------------------------------
		// Song * reputation + session_id
		$query = "SELECT
			p.*,
			s.id as s_id,
			m.id, m.name, m.mgroup, m.email, m.joined,
			m.gender,
			m.avatar, m.avatar_size, m.posts, m.aim_name,
			m.icq_number, m.signature,  m.website, m.yahoo,
			m.integ_msg, m.title, m.hide_email, m.msnname,
			m.warn_level, m.warn_lastwarn,
			m.points,  m.fined, m.rep, m.ratting, m.show_ratting,
			g.g_id, g.g_title, g.g_icon, g.g_use_signature,
			g.g_use_avatar, g.g_dohtml
			$join_get_fields
		  FROM ibf_posts p
		  LEFT JOIN ibf_members m
			ON (p.author_id=m.id)
		  LEFT JOIN ibf_sessions s
			ON (s.member_id != 0 and m.id=s.member_id and s.login_type != 1)
		  LEFT JOIN ibf_groups g
			ON (g.g_id=m.mgroup)
		  $join_profile_query
		  WHERE ";

		// Song * pinned post

		$pinned = 0;

		if ($this->topic['pinned_post'])
		{
			$query_top_post = $query . "p.pid='" . $this->topic['pinned_post'] . "'";

			$query .= "p.pid != '" . $this->topic['pinned_post'] . "' and ";

			$stmt = $ibforums->db->query("SELECT pid
			    FROM ibf_posts
			    WHERE
				queued != 1
				AND topic_id='" . $this->topic['tid'] . "'
			    ORDER BY pid
			    LIMIT " . $this->first . ",1");

			$post = $stmt->fetch();

			if ($post and $post['pid'] > $this->topic['pinned_post'])
			{
				$this->first--;
				$pinned++;
			}
		}
		$preview = isset($ibforums->input['preview'])
			? (int)$ibforums->input['preview']
			: NULL;
		if ($preview)
		{
			$query .= " p.pid = $preview AND ";
		}

		$query .= "p.topic_id='" . $this->topic['tid'] . "' GROUP BY p.pid ORDER BY p.pid";

		// 4 show all posts at once
		if ($ibforums->input['view'] != "showall")
		{
			$query .= " LIMIT " . $this->first . "," . $ibforums->vars['display_max_posts'];
		}

		// 4 pinned post
		if ($this->topic['pinned_post'])
		{
			$stmt = $ibforums->db->query($query_top_post);

			if (!$stmt->rowCount())
				$pinned++;

			// show_post
			$this->process_posts($stmt, 1);
		}

		$stmt = $ibforums->db->query($query);

		if (!$stmt->rowCount() and $this->first >= $ibforums->vars['display_max_posts'])
		{

			$pcount = $ibforums->db->query("SELECT
				COUNT(pid) as pcount
			    FROM ibf_posts
			    WHERE
				topic_id='" . $this->topic['tid'] . "'
				AND queued != 1")
				->fetch();

			$pcount['pcount'] = ($pcount['pcount'])
				? $pcount['pcount'] - 1
				: 0;

			$ibforums->db->exec("UPDATE ibf_topics
			    SET posts='" . $pcount['pcount'] . "'
			    WHERE tid='" . $this->topic['tid'] . "'");

			$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid'] . "&amp;view=getlastpost");

			exit();
		}

		// show_posts
		$this->process_posts($stmt, 0, $pinned, $preview);

		//-------------------------------------
		// Print the footer
		//-------------------------------------

		$report_link = ($ibforums->member['id'])
			? View::make(
				"topic.new_report_link",
				[
					'data' => array(
						'TOPIC' => $this->topic,
						'FORUM' => $this->forum,
					)
				]
			)
			: "";

		$this->output .= View::make(
			"topic.TableFooter",
			[
				'data'        => array(
					'TOPIC' => $this->topic,
					'FORUM' => $this->forum
				),
				'report_link' => $report_link
			]
		);

		//+----------------------------------------------------------------
		// Process users active in this forum
		//+----------------------------------------------------------------

		if ($ibforums->vars['no_au_topic'] != 1)
		{
			//+-----------------------------------------
			// Get the users
			//+-----------------------------------------

			$cut_off = ($ibforums->vars['au_cutoff'] != "")
				? $ibforums->vars['au_cutoff'] * 60
				: 900;

			$time = time() - $cut_off;

			$stmt = $ibforums->db->query("SELECT
				s.member_id,
				s.member_name,
				s.login_type,
				s.location,
				s.org_perm_id,
				g.suffix, g.prefix, g.g_perm_id
			    FROM ibf_sessions s
			    LEFT JOIN ibf_groups g
				ON (g.g_id=s.member_group)
			    WHERE
				s.r_in_topic='" . $this->topic['tid'] . "'
				AND s.running_time > $time");

			//+-----------------------------------------
			// Cache all printed members so we don't double print them
			//+-----------------------------------------

			$cached = array();
			$active = array(
				'guests'  => 0,
				'anon'    => 0,
				'members' => 0,
				'names'   => ""
			);

			while ($result = $stmt->fetch())
			{
				// Quick check

				$result['g_perm_id'] = $result['org_perm_id']
					? $result['org_perm_id']
					: $result['g_perm_id'];

				if ($this->forum['read_perms'] != '*')
				{
					if ($result['g_perm_id'])
					{
						if (!preg_match("/(^|,)(" . str_replace(",", '|', $result['g_perm_id']) . ")(,|$)/", $this->forum['read_perms']))
						{
							continue;
						}
					} else
					{
						continue;
					}
				}

				if ($result['member_id'] == 0)
				{
					$active['guests']++;
				} else
				{
					if (empty($cached[$result['member_id']]))
					{
						$cached[$result['member_id']] = 1;

						if ($result['login_type'] == 1)
						{
							if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and ($ibforums->vars['disable_admin_anon'] != 1))
							{
								$active['names'] .= "<a href='{$ibforums->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*, ";
								$active['anon']++;
							} else
							{
								$active['anon']++;
							}
						} else
						{
							$active['members']++;
							$active['names'] .= "<a href='{$ibforums->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>, ";
						}
					}
				}
			}

			$active['names'] = preg_replace("/,\s+$/", "", $active['names']);

			$ibforums->lang['active_users_title']   = sprintf($ibforums->lang['active_users_title'], ($active['members'] + $active['guests'] + $active['anon']));
			$ibforums->lang['active_users_detail']  = sprintf($ibforums->lang['active_users_detail'], $active['guests'], $active['anon']);
			$ibforums->lang['active_users_members'] = sprintf($ibforums->lang['active_users_members'], $active['members']);

			$this->output = str_replace("<!--IBF.TOPIC_ACTIVE-->",
				View::make("topic.topic_active_users", ['active' => $active]), $this->output);
		}

		//+----------------------------------------------------------------
		// Print it
		//+----------------------------------------------------------------

		$this->output = str_replace("<!--IBF.MOD_PANEL-->", $this->moderation_panel(), $this->output);

		// Enable quick reply box?

		if ($this->topic['quick_reply'] and
		    $this->topic['state'] != 'closed' and
		    $this->topic['poll_state'] != 'closed' and
		    $std->check_perms($this->topic['reply_perms']) == TRUE and
		    $std->user_reply_flood($this->topic['start_date']) == FALSE
		)
		{
			$show = "none";

			$ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

			if ($ibforums->member['quick_reply'])
				$show = "show";

			$show_quick_reply_box_closed = TRUE;

			$q       = 0;
			$warning = "";

			if (($this->forum['preview_posts'] == 1 or $this->forum['preview_posts'] == 3 or $ibforums->member['mod_posts'])
			    and $show == "show"
			)
				$q = 1;

			// check ip
			if (!$q)
			{
				$q = $std->ip_control($this->forum, $ibforums->input['IP_ADDRESS']);

				if (!$q)
				{
					$stmt = $ibforums->db->query("SELECT mod_posts
					    FROM ibf_preview_user
					    WHERE
						mid='" . $ibforums->member['id'] . "' and
						(fid='" . $this->forum['id'] . "' or
						 fid='" . $this->forum['parent_id'] . "')");

					if ($stmt->rowCount())
					{
						$row = $stmt->fetch();

						if ($row['mod_posts'])
							$q = 1;
					}
				}
			}

			// Song * mod buttons, 08.11.2004

			$mod_buttons = "";

			if ($ibforums->member['is_mod'])
			{
				if ($this->moderator['mid'] or $ibforums->member['g_is_supmod'])
				{
					$mod_buttons .= View::make("global.mod_buttons");
				}

				if ($ibforums->member['g_is_supmod'])
				{
					$mod_buttons .= View::make("global.global_mod_buttons");
				}

				$mod_buttons .= View::make("global.common_mod_buttons");

				if ($mod_buttons)
					$mod_buttons = View::make("global.mod_buttons_label") . $mod_buttons;
			}

			// Post Warnings

			if ($q)
				$warning = $ibforums->lang['mod_posts_warning'];

			// Song * decided topics, 20.04.05

			$topic_decided = "";
			if ($this->topic['SOLVE_UPPER_BUTTON'] and !$this->topic['decided'])
			{
				$topic_decided = ($ibforums->member['id'] == $this->topic['starter_id'])
					? View::make("global.topic_decided")
					: "";
			}

			$this->output = str_replace("<!--IBF.QUICK_REPLY_OPEN-->",
				View::make(
					"topic.quick_reply_box_open",
					[
						'fid'           => $this->topic['forum_id'],
						'tid'           => $this->topic['tid'],
						'show'          => $show,
						'warning'       => $warning,
						'key'           => $this->md5_check,
						'syntax_select' => $std->code_tag_button($this->highlight),
						'mod_buttons'   => $mod_buttons,
						'topic_decided' => $topic_decided
					]
				), $this->output);

			if (($std->check_perms($this->forum['upload_perms']) == TRUE) and ($ibforums->member['g_attach_max'] > 0))
			{
				$upload_field = View::make(
					"topic.Upload_field",
					['data' => $std->size_format($ibforums->member['g_attach_max'] * 1024)]
				);
				$this->output = str_replace('<!--UPLOAD FIELD-->', $upload_field, $this->output);
			}

			$this->html_add_smilie_box();
			$this->html_checkboxes($this->topic['tid']);
			$this->output = str_replace("<!--IBF.NAME_FIELD-->", $this->html_name_field(), $this->output);
		}

		// End of Quick Reply

		$this->topic['id'] = $this->topic['forum_id'];

		//------------------
		// IBF forum rules
		//------------------

		if ($this->topic['rules_title'])
		{
			$this->topic['rules_title'] = trim($this->parser->prepare(array(
			                                                               'TEXT'      => $this->topic['rules_title'],
			                                                               'SMILIES'   => 1,
			                                                               'CODE'      => 1,
			                                                               'SIGNATURE' => 0,
			                                                               'HTML'      => 0,
			                                                          )));

			$this->topic['rules_text'] = trim($this->parser->prepare(array(
			                                                              'TEXT'      => $this->topic['rules_text'],
			                                                              'SMILIES'   => 1,
			                                                              'CODE'      => 1,
			                                                              'SIGNATURE' => 0,
			                                                              'HTML'      => 0,
			                                                         )));

			$this->topic['rules_text'] = str_replace(";&lt;br&gt;", "<br>", $this->topic['rules_text']);

			$this->output = str_replace("<!--IBF.FORUM_RULES-->", $std->print_forum_rules($this->topic), $this->output);
		}

		$actions = [
			$this->topic['REPLY_BUTTON'],
			$this->topic['TOPIC_BUTTON'],
			$this->topic['POLL_BUTTON'],
			$this->topic['SOLVE_UPPER_BUTTON'],
		];
		$this->output = str_replace("<!--IBF.TOPIC_HEADER_BUTTONS-->",
			View::make(
				"global.renderActionButtons",
				[
					'actions'      => $actions,
					'list_classes' => 'b-topic-header-buttons',
					'item_classes' => 'b-topic-header-button'
				]
			), $this->output);
		$actions = [
			$show_quick_reply_box_closed ? View::make("topic.quick_reply_box_closed")
				: '',
			$this->topic['REPLY_BUTTON'],
			$this->topic['TOPIC_BUTTON'],
			$this->topic['POLL_BUTTON'],
			$this->topic['SOLVE_DOWN_BUTTON']
       ];
		$this->output = str_replace("<!--IBF.TOPIC_BOTTOM_BUTTONS-->",
			View::make(
				"global.renderActionButtons",
				[
					'actions'      => $actions,
					'list_classes' => 'b-topic-footer-buttons',
					'item_classes' => 'b-topic-footer-button'
				]
			), $this->output);
		//+----------------------------------------------------------------
		// Topic multi-moderation - yay!
		//+----------------------------------------------------------------

		$this->output = str_replace("<!--IBF.MULTIMOD-->", $this->multi_moderation(), $this->output);

		// Pass it to our print routine

		$print->add_output($this->output);

		$print->do_output(array(
		                       'TITLE' => str_replace(array(
		                                                   "!",
		                                                   "&#33;"
		                                              ), array(
		                                                      "",
		                                                      ""
		                                                 ), "{$this->topic['title']} -> " . $ibforums->vars['board_name']),
		                       'JS'    => "",
		                       'NAV'   => $this->nav_extra,
		                       'RSS'   => View::make("global.rss", ['param' => "?t={$this->topic['tid']}"]),
		                  ));
	}

	// end of class
	//-------------------------------------------------
	function html_name_field()
	{
		global $ibforums;

		return $ibforums->member['id']
			? View::make("topic.nameField_reg")
			: View::make("topic.nameField_unreg", ['data' => $ibforums->input['UserName']]);
	}

	function html_checkboxes($tid = "")
	{
		global $ibforums;

		$default_checked = array(
			'emo' => 'checked="checked"',
			'tra' => $ibforums->member['auto_track']
				? 'checked="checked"'
				: ''
		);

		// Make sure we're not previewing them and they've been unchecked!
		if (isset($ibforums->input['enablesig']) AND !$ibforums->input['enablesig'])
		{
			$default_checked['sig'] = "";
		}

		if (isset($ibforums->input['enableemo']) AND !$ibforums->input['enableemo'])
		{
			$default_checked['emo'] = "";
		}

		if (isset($ibforums->input['enabletrack']) AND !$ibforums->input['enabletrack'])
		{
			$default_checked['tra'] = "";
		} elseif (isset($ibforums->input['enabletrack']) AND $ibforums->input['enabletrack'])
		{
			$default_checked['tra'] = 'checked="checked"';
		}

		$this->output = str_replace('<!--IBF.EMO-->',
			View::make("topic.get_box_enableemo", ['checked' => $default_checked['emo']]), $this->output);

		if ($this->trid)
		{
			$this->output = str_replace('<!--IBF.TRACK-->', View::make("topic.get_box_alreadytrack"), $this->output);
		} else
		{
			$this->output = str_replace('<!--IBF.TRACK-->',
				View::make("topic.get_box_enabletrack", ['checked' => $default_checked['tra']]), $this->output);
		}

		// Song * offtopic checkbox, 19.04.05

		if ($this->forum['days_off'] and ($this->moderator['delete_post'] or $ibforums->member['g_is_supmod'] or $ibforums->member['g_delay_delete_posts']))
		{
			$this->output = str_replace('<!--IBF.OFFTOP-->',
				View::make("topic.get_box_enable_offtop", ['checked' => $default_checked['offtop']]), $this->output);
		}
	}

	function html_add_smilie_box()
	{
		global $ibforums;

		$show_table = 0;
		$count      = 0;
		$smilies    = "<tr align='center'>\n";

		// Get the smilies from the DB
		// Song * smile skin
		if (!$ibforums->member['id'])
			$id = 1; else
			$id = $ibforums->member['sskin_id'];

		if (!$id)
			$id = 1;

		$stmt = $ibforums->db->query("SELECT
				typed,
				image
			    FROM ibf_emoticons
			    WHERE clickable='1' and skid='" . $id . "'");
		// /Song * smile skin

		while ($elmo = $stmt->fetch())
		{

			$show_table++;
			$count++;

			// Make single quotes as URL's with html entites in them
			// are parsed by the browser, so ' causes JS error :o

			if (mb_strstr($elmo['typed'], "&#39;"))
			{
				$in_delim  = '"';
				$out_delim = "'";
			} else
			{
				$in_delim  = "'";
				$out_delim = '"';
			}

			if (!$ibforums->member['id'])
				$sskin = 'Main'; else
			{
				$sskin = $ibforums->member['sskin_name'];
				if (!$ibforums->member['view_img'] or $ibforums->member['sskin_id'] == 0)
					$sskin = 0;
			}

			if ($sskin)
			{
				$smile = "<img src='{$ibforums->vars['board_url']}/smiles/$sskin/" . $elmo['image'] . "' alt='{$elmo['typed']}' border='0'>";
			} else
				$smile = $elmo['typed'];

			$smilies .= "<td><a href={$out_delim}javascript:emoticon($in_delim" . $elmo['typed'] . "$in_delim){$out_delim}>{$smile}</a>&nbsp;</td>\n";

			if ($count == $ibforums->vars['emo_per_row'])
			{
				$smilies .= "</tr>\n\n<tr align='center'>";
				$count = 0;
			}
		}

		if ($count != $ibforums->vars['emo_per_row'])
		{
			for ($i = $count; $i < $ibforums->vars['emo_per_row']; ++$i)
			{
				$smilies .= "<td>&nbsp;</td>\n";
			}
			$smilies .= "</tr>";
		}

		$table = View::make("topic.smilie_table");

		if ($show_table)
		{
			$table        = preg_replace("/<!--THE SMILIES-->/", $smilies, $table);
			$this->output = preg_replace("/<!--SMILIE TABLE-->/", $table, $this->output);
		}
	}

	//--------------------------------------------------------------
	// Parse the member info
	//--------------------------------------------------------------

	function correct_cached_fields($member = array())
	{
		global $ibforums;

		// Song * Reputation + show ratting setting

		if ((!$ibforums->member['id'] or $ibforums->member['show_ratting']) and $member['show_ratting'] and !$member['use_sig'])
		{

			// Song * dual ratting, 15.01.05

			$rep = ($this->forum['inc_postcount'])
				? $member['rep']
				: $member['ratting'];

			$rep_suffix = ($this->forum['inc_postcount'])
				? "t"
				: "f";

			$rep_link = $ibforums->lang['rep_name'] . $ibforums->lang['rep_' . $rep_suffix];

			// /dual ratting, 15.01.05

			$tmp_rep = empty($rep)
				? 0
				: $rep;

			if ($ibforums->vars['rep_goodnum'] and $tmp_rep >= $ibforums->vars['rep_goodnum'])
			{
				$member['title'] = $ibforums->vars['rep_goodtitle'] . ' ' . $member['title'];
			}

			if ($ibforums->vars['rep_badnum'] and $tmp_rep <= $ibforums->vars['rep_badnum'])
			{
				$member['title'] = $ibforums->vars['rep_badtitle'] . ' ' . $member['title'];
			}

			if (empty($rep))
			{
				if (!is_numeric($rep))
				{
					$rep = $ibforums->lang['rep_none'];
				} else
				{
					$rep .= " " . $ibforums->lang['rep_postfix'];
				}
			} else
				$rep .= " " . $ibforums->lang['rep_postfix'];

			if ($ibforums->member['id'])
			{
				$stuff = array(
					't'   => $this->topic['tid'],
					'f'   => $this->forum['id'],
					'mid' => $member['id'],
					'p'   => $member['pid']
				);

				if ($ibforums->member['id'] == $member['id'])
				{
					$rep = "<a href='{$ibforums->vars['board_url']}/index.php?act=rep&amp;CODE=03&amp;type={$rep_suffix}&amp;mid=" . $stuff['mid'] . "' target='_blank'>" . $rep_link . "</a>: " . $rep;
				} else
				{
					$down = ($ibforums->member['view_img'])
						? "<{REP_MINUS}>"
						: "<span style='color:red'>-</font>";
					$up   = ($ibforums->member['view_img'])
						? "<{REP_ADD}>"
						: "<span style='color:green'>+</font>";

					$link = "<a href='{$ibforums->vars['board_url']}/index.php?act=rep&amp;CODE=03&amp;type={$rep_suffix}&amp;mid=" . $stuff['mid'] . "' target='_blank'>" . $rep_link . "</a>: ";
					$link .= "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=rep&amp;CODE=02&amp;mid=$stuff[mid]&amp;f=$stuff[f]&amp;t=$stuff[t]&amp;p=$stuff[p]' style='text-decoration:none' target='_blank'>" . $down . "</a>";
					$link .= " [ " . $rep . " ] ";
					$link .= "<a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=rep&amp;CODE=01&amp;mid=$stuff[mid]&amp;f=$stuff[f]&amp;t=$stuff[t]&amp;p=$stuff[p]' style='text-decoration:none' target='_blank'>" . $up . "</a>";

					$rep = $link;
				}
			} else
				$rep = $rep_link . ": " . $rep;

			$member['rep'] = $rep;
		} else
			$member['rep'] = "";

		// add crlf
		if ($member['rep'])
			$member['rep'] .= "<br>";

		// /Song * Reputation + show ratting setting
		// Song * WARNINGS
		if ($ibforums->vars['warn_on'] and !stristr($ibforums->vars['warn_protected'], ',' . $member['mgroup'] . ','))
		{
			if ($ibforums->member['id'] != $member['id'] and ($ibforums->member['g_is_supmod'] or $this->moderator['allow_warn']))
			{
				$down = ($ibforums->member['view_img'])
					? "<{WARN_MINUS}>"
					: "<span style='color:green'>-</font>";
				$up   = ($ibforums->member['view_img'])
					? "<{WARN_ADD}>"
					: "<span class='movedprefix'>+</span>";

				$member['warn_add'] = "<a class='e-add-warning-button' href='{$ibforums->base_url}act=warn&amp;type=add&amp;";
				$member['warn_add'] .= "mid=" . $member['id'] . "&amp;t={$this->topic['tid']}&amp;p=" . $member['pid'] . "&amp;f=";
				$member['warn_add'] .= $this->forum['id'] . "&amp;st=" . intval($ibforums->input['st']);
				$member['warn_add'] .= "' title='{$ibforums->lang['tt_warn_add']}' style='text-decoration:none' target='_blank'>" . $up . "</a>";

				$member['warn_minus'] = "<a href='{$ibforums->base_url}act=warn&amp;type=minus&amp;";
				$member['warn_minus'] .= "mid=" . $member['id'] . "&amp;t=" . $this->topic['tid'] . "&amp;p=" . $member['pid'] . "&amp;f=";
				$member['warn_minus'] .= $this->forum['id'] . "&amp;st=" . intval($ibforums->input['st']);
				$member['warn_minus'] .= "' title='{$ibforums->lang['tt_warn_minus']}' style='text-decoration:none' target='_blank'>" . $down . "</a>";
				$member['warn_img'] = " [ " . $member['warn_level'] . " ] ";

				$member['warn_text'] = $ibforums->lang['tt_rating'];
				$member['warn_text'] = View::make(
					"topic.warn_title",
					['id' => $member['id'], 'title' => $member['warn_text']]
				);
				$member['warn_text'] .= $member['warn_minus'] . $member['warn_img'] . $member['warn_add'];
			}
		}

		return $member;
	}

	//---------------------------------------------------
	function parse_member(&$member = array())
	{
		global $ibforums, $std;

		if (!$member['use_sig'])
		{
			$member['avatar'] = $std->get_avatar($member['avatar'], $ibforums->member['view_avs'], $member['avatar_size']);

			if ($ibforums->member['id'] and $member['g_use_avatar'])
			{
				$member['avatar'] = $member['g_use_avatar'];
			}

			if ($member['avatar'])
				$member['avatar'] .= "<br>";

			$rank = "";
			$pips = 0;

			foreach ($this->mem_titles as $k => $v)
			{
				if ($member['posts'] >= $v['POSTS'])
				{
					$rank = $this->mem_titles[$k]['TITLE'];

					if (!$member['title'])
					{
						if ($this->mod[$member['id']])
						{
							$member['title'] = "Moderator";
						} elseif (!$ibforums->member['id'] or $ibforums->member['show_status'])
						{
							$member['title'] = $rank;
						}
					} elseif ($member['mgroup'] != $ibforums->vars['admin_group'] and $this->mod[$member['id']])
					{
						$member['title'] = "Moderator";
					}

					$pips = $v['PIPS'];
					break;
				}
			}

			// Show status + show icons
			if ($pips)
			{
				if (!$ibforums->member['id'] or $ibforums->member['view_img'])
					$pip = "<{A_STAR}>"; else
					$pip = "*";

				if (!$ibforums->member['id'] or $ibforums->member['show_status'])
					if (preg_match("/^\d+$/", $pips))
					{
						for ($i = 1; $i <= $pips; ++$i)
							$member['member_rank_img'] .= $pip;
					} elseif (!$ibforums->member['id'] or ($ibforums->member['show_icons'] and $ibforums->member['view_img']))
					{
						$member['member_rank_img'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/$pips' border='0' alt='*'>";
					}
			}

			// Member sex
			$member['sex'] = "";

			if ($member['gender'] == "f" and
			    (!$ibforums->member['id'] or ($ibforums->member['view_img'] and $ibforums->member['show_icons']))
			)
			{
				$member['sex'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/fem.gif' alt='{$member['field_2']}' title='{$member['field_2']}' border='0'> ";
			}

//			// add crlf
//			if ($member['member_rank_img'])
//				$member['member_rank_img'] .= "<br>";
//
//			// add crlf
//			if ($member['title'])
//				$member['title'] .= "<br>";

			if ($member['g_icon'] and (!$ibforums->member['id'] or ($ibforums->member['view_img'] and $ibforums->member['show_icons'])))
			{
				$member['member_group_img'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/{$member['g_icon']}' border='0' alt='{$rank}' title='{$rank}'>";
			}

			$member['profile'] = "<a href='{$this->base_url}showuser={$member['id']}' target='_blank'>{$ibforums->lang['link_profile']}</a> &middot; <a href='{$this->base_url}act=Msg&amp;CODE=4&amp;MID={$member['id']}' target='_blank'>PM</a>";

			// $member['profile'] = $member['points'] . "+".$member['fined']."+". $member['profile'] ;
			// Show ratting + dgm
			if ((!$ibforums->member['id'] or $ibforums->member['show_ratting'])
			    and intval($member['fined']) > 0
			        and $member['show_ratting']
			)
			{

				$member['member_points'] = $std->do_number_format($member['fined']);
				$member['member_points'] = '<a href="' . $this->base_url . 'act=store&code=showfine&id=' . $member['id'] . '">' . $ibforums->lang['fine'] . '</a>: ' . $member['member_points'] . ' ' . $ibforums->vars['currency_name'];
				$member['member_points'] .= "<br>";
			} else
				$member['member_points'] = "";

			if ((!$ibforums->member['id'] or $ibforums->member['show_ratting']) and $ibforums->vars['show_inventory'])
			{
				$member['member_inventory'] = $ibforums->lang['members_inventory'] . "<a href='{$ibforums->base_url}act=store&amp;code=view_inventory&amp;memberid={$member['id']}'>{$ibforums->lang['view_inventory']}</a>";
			}

			//--------------------------------------------------------------
			// Profile fields stuff
			//--------------------------------------------------------------

			if ($ibforums->vars['custom_profile_topic'])
			{
				foreach ($this->pfields as $id => $pf)
				{
					if ($member[$id] != "")
					{
						if ($pf['fhide'] == 1 and $ibforums->member['g_is_supmod'] != 1)
						{
							$member[$id] = "";
						} elseif ($pf['ftype'] == 'drop')
						{
							$member[$id] = $this->pfields_dd[$id][$member[$id]]; // You just know that's going to make no sense tomorrow.
						}
					}
				}
			}
		} else
		{
			$member['avatar'] = "";

			// status
			if ($this->mod[$member['id']] and !$member['title'])
				$member['title'] = "Moderator";

			if ($member['title'])
				$member['title'] .= "<br>";

			// group icon
			if ($member['g_icon'] and (!$ibforums->member['id'] or ($ibforums->member['view_img'] and $ibforums->member['show_icons'])))
			{
				$member['member_group_img'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/{$member['g_icon']}' border='0'>";
			}

			$member['profile'] = "<a href='{$this->base_url}showuser={$member['id']}' target='_blank'>{$ibforums->lang['link_profile']}</a> &middot; <a href='{$this->base_url}act=Msg&amp;CODE=4&amp;MID={$member['id']}' target='_blank'>PM</a><br>";
		}

		//--------------------------------------------------------------
		// Warny porny?
		//--------------------------------------------------------------

		if ($ibforums->vars['warn_on']
		    and !stristr($ibforums->vars['warn_protected'], ',' . $member['mgroup'] . ',')
		)
		{
			if ($ibforums->member['g_is_supmod'] or $this->moderator['allow_warn'] or
			    ($ibforums->vars['warn_show_own'] and $ibforums->member['id'] == $member['id'])
			)
			{
				// Work out which image to show.

				if (!$ibforums->vars['warn_show_rating'])
				{
					if ($member['warn_level'] <= $ibforums->vars['warn_min'])
					{
						$member['warn_img']     = '<{WARN_0}>';
						$member['warn_percent'] = 0;
					} elseif ($member['warn_level'] >= $ibforums->vars['warn_max'])
					{
						$member['warn_img']     = '<{WARN_5}>';
						$member['warn_percent'] = 100;
					} else
					{
						$member['warn_percent'] = $member['warn_level']
							? sprintf("%.0f", (($member['warn_level'] / $ibforums->vars['warn_max']) * 100))
							: 0;

						if ($member['warn_percent'] > 100)
						{
							$member['warn_percent'] = 100;
						}

						if ($member['warn_percent'] >= 81)
						{
							$member['warn_img'] = '<{WARN_5}>';
						} elseif ($member['warn_percent'] >= 61)
						{
							$member['warn_img'] = '<{WARN_4}>';
						} elseif ($member['warn_percent'] >= 41)
						{
							$member['warn_img'] = '<{WARN_3}>';
						} elseif ($member['warn_percent'] >= 21)
						{
							$member['warn_img'] = '<{WARN_2}>';
						} elseif ($member['warn_percent'] >= 1)
						{
							$member['warn_img'] = '<{WARN_1}>';
						} else
						{
							$member['warn_img'] = '<{WARN_0}>';
						}
					}

					if ($member['warn_percent'] < 1)
						$member['warn_percent'] = 0;

					$member['warn_text'] = View::make(
						"topic.warn_level_warn",
						['id' => $member['id'], 'percent' => $member['warn_percent']]
					);
				} else
				{
					// Ratings mode..

					$member['warn_text'] = $ibforums->lang['tt_rating'];
					$member['warn_text'] = View::make(
						"topic.warn_title",
						['id' => $member['id'], 'title' => $member['warn_text']]
					);
					$member['warn_text'] .= $member['warn_level'];

					// Song * new separated warning system
					if ($ibforums->member['is_new_warn_exixts'])
					{
						$member['warn_text'] .= View::make("topic.renderNewWarnNotice");
					} elseif (!$member['warn_level'])
					{
						$member['warn_text'] = "";
					}
					// /Song * new warning system
				}
			}
		}

		// Add reputation and warn tools
		return $this->correct_cached_fields($member);
	}

	//--------------------------------------------------------------
	// Render the delete button
	//--------------------------------------------------------------

	function delete_button($post_id, $poster, $queued = 0)
	{
		global $ibforums, $std;

		if (!$ibforums->member['id'])
			return "";

		$func = ($queued)
			? "'{$ibforums->base_url}act=modcp&amp;CODE=domodposts&amp;f={$this->forum['id']}&amp;tid={$this->topic['tid']}&amp;PID_{$post_id}=remove&amp;alter={$this->alter_post}'"
			: "'{$this->base_url}act=Mod&amp;CODE=04&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$post_id}&amp;st={$ibforums->input['st']}&amp;auth_key={$this->md5_check}'";

		$button = "<a href={$func} onclick='return deletePost(this,$post_id)'><{P_DELETE}></a>";

		if ($ibforums->member['g_is_supmod'] or $this->moderator['delete_post'])
		{
			return $button;
		}

		if ($poster['id'] == $ibforums->member['id'] and $ibforums->member['g_delete_own_posts'])
		{
			return $button;
		}

		return "";
	}

	//------------------------------------
	function checkbox($pid)
	{
		global $ibforums;

		if (!$ibforums->member['id'])
			return "";

		$checkbox = '<input class="forminput" type="checkbox" name="pozt' . $pid . '" value="1">';

		if ($ibforums->member['g_is_supmod'] or $this->moderator['delete_post'] or $this->moderator['split_merge'])
		{
			return $checkbox;
		}

		return "";
	}

	//------------------------------------------
	// Song * delete delayed, 13.04.05

	function delayed_delete_button(&$row, $poster, $post_count)
	{
		global $ibforums, $std;

		if (!$ibforums->member['id'])
			return "";

		if (!$row['delete_after'])
		{
			$button = (!$this->first and !$post_count)
				? ""
				: $button = "<a href='{$ibforums->base_url}act=Mod&amp;CODE=28&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$row['pid']}&amp;auth_key={$this->md5_check}' onclick=\"return startAutoDelete({$this->forum['days_off']},this);\"'><{P_X}></a>";
			;
		} else
		{
			$days = $std->get_autodelete_message($row['delete_after'], $ibforums->lang['delete_waiting'], $ibforums->lang['delete_through']);

			$button = "<a href='{$ibforums->base_url}act=Mod&amp;CODE=29&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$row['pid']}&amp;auth_key={$this->md5_check}' onclick=\"return stopAutoDelete({$this->forum['days_off']},this);\"'>{$days}</a>";
		}

		if ($ibforums->member['g_is_supmod'] or $this->moderator['delete_post'])
		{
			return $button;
		}

		if ($poster['id'] == $ibforums->member['id'] and $ibforums->member['g_delay_delete_posts'])
		{
			return $button;
		}

		return "";
	}

	//--------------------------------------------------------------
	// Render the edit button
	//--------------------------------------------------------------
	function edit_button($post_id, $poster, $post_date)
	{
		global $ibforums;

		if ($ibforums->member['id'] == "" or $ibforums->member['id'] == 0)
		{
			return "";
		}

		$button = "<a href=\"{$this->base_url}act=Post&amp;CODE=08&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$post_id}&amp;st={$ibforums->input['st']}\"><{P_EDIT}></a>";

		if ($ibforums->member['g_is_supmod'])
			return $button;

		if ($this->moderator['edit_post'])
			return $button;

		if ($poster['id'] == $ibforums->member['id'] and ($ibforums->member['g_edit_posts']))
		{

			// Have we set a time limit?

			if ($ibforums->member['g_edit_cutoff'] > 0)
			{
				if ($post_date > (time() - (intval($ibforums->member['g_edit_cutoff']) * 60)))
				{
					return $button;
				} else
				{
					return "";
				}
			} else
			{
				return $button;
			}
		}

		return "";
	}

	//--------------------------------------------------------------
	// Render the edit button
	//--------------------------------------------------------------
	function show_preview_button($post_id, $poster, $post_date, $preview)
	{
		global $ibforums;

		if ($ibforums->member['id'] == "" or $ibforums->member['id'] == 0)
		{
			return "";
		}

		if ($preview)
		{
			$button = "<a onclick=\"return hideDeletedPost(this, $post_id)\" href=\"{$this->base_url}showtopic=" . $this->topic['tid'] . "&amp;view=findpost&amp;p={$post_id}\">{$ibforums->lang['show_preview_button']}</a>";
		} else
		{
			$button = "<a onclick=\"return previewDeletedPost(this, $post_id)\" href=\"{$this->base_url}showtopic=" . $this->topic['tid'] . "&amp;preview={$post_id}\">{$ibforums->lang['show_preview_button']}</a>";
		}

		if ($ibforums->member['g_is_supmod'])
			return $button;

		if ($this->moderator['edit_post'])
			return $button;

		if ($poster['id'] == $ibforums->member['id'] and ($ibforums->member['g_edit_posts']))
		{
			return $button;
		}

		return "";
	}

	//--------------------------------------------------------------
	// Render the edit button
	//--------------------------------------------------------------

	function edit_history_button($post_id, $poster, $post_date)
	{
		global $ibforums;

		if ($ibforums->member['id'] == "" or $ibforums->member['id'] == 0)
		{
			return "";
		}

		$button = "<a href=\"{$this->base_url}act=Post&amp;CODE=16&amp;f={$this->forum['id']}&amp;t={$this->topic['tid']}&amp;p={$post_id}&amp;st={$ibforums->input['st']}\"><{P_EDIT_HISTORY}></a>";

		if ($ibforums->member['g_is_supmod'])
			return $button;

		if ($this->moderator['edit_post'])
			return $button;

		if ($poster['id'] == $ibforums->member['id'] and ($ibforums->member['g_edit_posts']))
		{

			// Have we set a time limit?

			if ($ibforums->member['g_edit_cutoff'] > 0)
			{
				if ($post_date > (time() - (intval($ibforums->member['g_edit_cutoff']) * 60)))
				{
					return $button;
				} else
				{
					return "";
				}
			} else
			{
				return $button;
			}
		}

		return "";
	}

	//--------------------------------------------------------------
	// Render the IP address
	//--------------------------------------------------------------

	function view_ip($row, $poster)
	{
		global $ibforums;

		if ($ibforums->member['g_is_supmod'] != 1 && $this->moderator['view_ip'] != 1)
		{
			return "";
		} else
		{
			$row['ip_address'] = $poster['mgroup'] == $ibforums->vars['admin_group']
				? ""
				: "[ <a href='{$ibforums->base_url}act=modcp&amp;CODE=ip&amp;incoming={$row['ip_address']}&amp;f={$this->forum['id']}'>{$row['ip_address']}</a> ]";

			return ($row['ip_address'])
				? View::make("topic.ip_show", ['data' => $row['ip_address']])
				: "";
		}
	}

	//--------------------------------------------------------------
	// Reputation Hack
	//--------------------------------------------------------------

	function rep_options($memid, $pid)
	{
		global $ibforums;

		if (!$ibforums->member['id'])
		{
			return "";
		} else
		{
			if ($memid and $ibforums->member['id'] != $memid)
			{
				$stuff = array('t' => $this->topic['tid'], 'f' => $this->forum['id'], 'mid' => $memid, 'p' => $pid);

				return View::make("topic.rep_options_links", ['stuff' => $stuff]);
			}
		}
	}

	//--------------------------------------------------------------
	// Render the topic multi-moderation
	//--------------------------------------------------------------

	function multi_moderation()
	{
		global $ibforums, $std;

		$mm_html = "";

		$pass_go = FALSE;

		if ($ibforums->member['id'])
		{
			if ($ibforums->member['g_is_supmod'])
			{
				$pass_go = TRUE;
			} else if ($this->moderator['can_mm'] == 1)
			{
				$pass_go = TRUE;
			}
		}

		if ($pass_go != TRUE)
		{
			return "";
		}

		$this->forum['topic_mm_id'] = $std->clean_perm_string($this->forum['topic_mm_id']);

		if ($this->forum['topic_mm_id'] == "")
		{
			return "";
		}

		//----------------------------------------
		// Get the topic mod thingies
		//----------------------------------------

		$stmt = $ibforums->db->query("SELECT
				mm_id,
				mm_title
			    FROM ibf_topic_mmod
			    WHERE
				mm_id IN(" . implode(",", explode(",", $this->forum['topic_mm_id'])) . ")
			    ORDER BY mm_id ASC");

		if ($stmt->rowCount())
		{
			$mm_html = View::make("topic.mm_start", ['tid' => $this->topic['tid']]);

			while ($r = $stmt->fetch())
			{
				$mm_html .= View::make("topic.mm_entry", ['id' => $r['mm_id'], 'name' => $r['mm_title']]);
			}

			$mm_html .= View::make("topic.mm_end");
		}

		return $mm_html;
	}

	//--------------------------------------------------------------
	// Render the moderator links
	//--------------------------------------------------------------

	function moderation_panel()
	{
		global $ibforums, $std;

		$mod_links = "";

		if (!isset($ibforums->member['id']))
			return "";

		$skcusgej = 0;

		if ($ibforums->member['id'] == $this->topic['starter_id'])
		{
			$skcusgej = 1;
		}

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$skcusgej = 1;
		}

		if ($this->moderator['mid'] != "")
		{
			$skcusgej = 1;
		}

		if ($skcusgej == 0)
		{
			return "";
		}

		$actions = array(
			'MOVE_TOPIC',
			'CLOSE_TOPIC',
			'OPEN_TOPIC',
			'DELETE_TOPIC',
			'EDIT_TOPIC',
			'PIN_TOPIC',
			'UNPIN_TOPIC',
			'UNSUBBIT',
			'MERGE_TOPIC',
			'SPLIT_TOPIC',
			'ATTACH_LINKS',
			'UNPIN_POST',
			'PIN_POST',
			'DELETE_POSTZ',
			'MOVE_POSTZ',
			'DELETE_DELAYED',
			'FAQ_POSTS',
			'HIDE_TOPIC',
			'SHOW_TOPIC',
			'MIRROR_TOPIC',
			'DELETE_TOPIC_MIRROR'
		);

		foreach ($actions as $key)
		{
			if ($ibforums->member['g_is_supmod'])
			{
				$mod_links .= $this->append_link($key);
			} elseif ($this->moderator['mid'])
			{
				if ($key == 'MERGE_TOPIC' or $key == 'SPLIT_TOPIC' or $key == 'MOVE_POSTZ')
				{
					if ($this->moderator['split_merge'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'DELETE_POSTZ')
				{
					if ($this->moderator['delete_post'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'PIN_POST' or $key == 'UNPIN_POST')
				{
					if ($this->moderator['can_pin_post'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'ATTACH_LINKS')
				{
					if ($this->moderator['can_attach'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'HIDE_TOPIC')
				{
					if ($this->moderator['hide_topic'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'SHOW_TOPIC')
				{
					if ($this->moderator['hide_topic'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'DELETE_DELAYED')
				{
					if ($this->moderator['delete_post'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'FAQ_POSTS')
				{
					if ($this->moderator['add_to_faq'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} elseif ($key == 'DELETE_TOPIC_MIRROR')
				{
					if ($this->moderator['mirror_topic'] == 1)
					{
						$mod_links .= $this->append_link($key);
					}
				} else
				{
					if ($this->moderator[mb_strtolower($key)])
					{
						$mod_links .= $this->append_link($key);
					}
				}
			} elseif ($key == 'OPEN_TOPIC' or $key == 'CLOSE_TOPIC')
			{
				if ($ibforums->member['g_open_close_posts'])
				{
					$mod_links .= $this->append_link($key);
				}
			} elseif ($key == 'DELETE_TOPIC')
			{
				if ($ibforums->member['g_delete_own_topics'])
				{
					$mod_links .= $this->append_link($key);
				}
			}
		}

		if ($ibforums->member['g_access_cp'] == 1)
		{
			$mod_links .= $this->append_link('TOPIC_HISTORY');
		}

		if ($mod_links != "")
		{
			return View::make(
				"topic.Mod_Panel",
				[
					'data' => $mod_links,
					'fid'  => $this->forum['id'],
					'tid'  => $this->topic['tid'],
					'key'  => $this->md5_check
				]
			);
		}
	}

	//---------------------------------------------
	function append_link($key = "")
	{
		global $ibforums;

		if (!$key)
			return "";

		if ($this->topic['state'] == 'open' and $key == 'OPEN_TOPIC')
			return "";

		if ($this->topic['state'] == 'closed' and $key == 'CLOSE_TOPIC')
			return "";

		if ($this->topic['state'] == 'moved' and ($key == 'CLOSE_TOPIC' or $key == 'MOVE_TOPIC'))
			return "";

		if ($this->topic['pinned'] and $key == 'PIN_TOPIC')
			return "";

		if (!$this->topic['pinned'] and $key == 'UNPIN_TOPIC')
			return "";

		// Song * pin/unpin post

		if ($key == 'PIN_POST' and $this->topic['pinned_post'])
			return "";
		if ($key == 'UNPIN_POST' and !$this->topic['pinned_post'])
			return "";

		// Song * hide/show topic, 11.04.05

		if ($key == 'HIDE_TOPIC' and $this->topic['hidden'])
			return "";
		if ($key == 'SHOW_TOPIC' and !$this->topic['hidden'])
			return "";

		// Song * delete posts delayed

		if ($key == 'DELETE_DELAYED' and !$this->forum['days_off'])
			return "";

		// Song * add to faq, 02.05.05

		if ($key == 'FAQ_POSTS' and !$this->forum['faq_id'])
			return "";

		if ($key == 'DELETE_TOPIC_MIRROR' and !$this->topic['has_mirror'])
		{
			return '';
		}
		++$this->colspan;

		return View::make("topic.mod_wrapper", ['id' => $this->mod_action[$key], 'text' => $ibforums->lang[$key]]);
	}

	//----------------------------------------------------
	// Song * decided topics, 20.04.05

	function decided_button(&$upper_button, &$down_button)
	{
		global $ibforums;

		$upper_button = "";

		$down_button = "";

		if (!$ibforums->member['id'] or !$ibforums->member['g_use_decided'])
		{
			return;
		}

		if ($this->topic['state'] == 'closed')
		{
			// Do we have the ability to post in
			// closed topics?

			if (!($ibforums->member['g_is_supmod'] or $this->moderator['mid']))
			{
				return;
			}
		}

		if ($this->topic['decided'])
		{
			$caption = $ibforums->lang['topic_not_decided'];

			$code = 34;
		} else
		{
			$caption = '<span class="b-topic-decided-btn">' . $ibforums->lang['topic_decided'] . '</span>';

			$code = 33;
		}

		$button = "href='{$ibforums->base_url}act=Mod&amp;CODE={$code}&amp;t={$this->topic['tid']}&amp;f={$this->forum['id']}&amp;auth_key={$this->md5_check}&amp;st={$ibforums->input['st']}'>{$caption}</a>";

		if ($this->moderator['mid'] or $ibforums->member['g_is_supmod'] or $ibforums->member['id'] == $this->topic['starter_id'])
		{
			$upper_button = "<a id='qsolveTop' onclick=\"return JSRequest(this.href,unique_id(this));\" " . $button;

			$down_button = "<a id='qsolveBottom' onclick=\"return JSRequest(this.href,unique_id(this));\" " . $button;
		}

		return;
	}

	//--------------------------------------------------------------
	// Render the reply button
	//--------------------------------------------------------------

	function reply_button()
	{
		global $ibforums, $std;

		if ($this->topic['state'] == 'closed')
		{
			// Do we have the ability to post in
			// closed topics?

			if ($ibforums->member['g_is_supmod'] or $this->moderator['mid'])
			{
				return "<a href='{$this->base_url}act=Post&amp;CODE=02&amp;f=" . $this->forum['id'] . "&amp;t=" . $this->topic['tid'] . "'><{A_LOCKED_B}></a>";
			} else
				return "<{A_LOCKED_B}>";
		}

		if ($std->check_perms($this->forum['reply_perms']) == FALSE)
			return "";

		// Song * Old Topics Flood, 15.03.05

		if ($std->user_reply_flood($this->topic['start_date']))
			return "";

		if ($this->topic['state'] == 'moved')
		{
			return "<{A_MOVED_B}>";
		}

		if ($this->topic['poll_state'] == 'closed')
		{
			return "<{A_POLLONLY_B}>";
		}

		$reply_title = TopicDraft::draftExists($this->topic['tid'])
			? $ibforums->lang['topic_draft']
			: '<{A_REPLY}>';

		return "<a href='{$this->base_url}act=Post&amp;CODE=02&amp;f=" . $this->forum['id'] . "&amp;t=" . $this->topic['tid'] . "'>$reply_title</a>";
	}

	static function topic_has_draft($tid)
	{
		global $ibforums, $std;
	}

	//-------------------------------------
	function check_access()
	{
		global $ibforums, $std;

		$return = 1;

		if ($std->check_perms($this->forum['read_perms']) == TRUE)
		{
			$return = 0;
		}

		if ($this->forum['password'] != "")
		{

			if (!$c_pass = $std->my_getcookie('iBForum' . $this->forum['id']))
			{
				return 1;
			}

			if ($c_pass == $this->forum['password'])
			{
				return 0;
			} else
			{
				return 1;
			}
		}

		return $return;
	}

	//--------------------------------------------------------------
	// Process and parse the poll
	//--------------------------------------------------------------

	function parse_poll()
	{
		global $ibforums, $std;

		$html        = "";
		$check       = 0;
		$poll_footer = "";

		$ibforums->lang  = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

		//----------------------------------
		// Get the poll information...
		//----------------------------------

		$stmt = $ibforums->db->query("SELECT *
		    FROM ibf_polls
		    WHERE tid='" . $this->topic['tid'] . "'");

		$poll_data = $stmt->fetch();

		if (!$poll_data['pid'])
			return;

		if (!$poll_data['poll_question'])
			$poll_data['poll_question'] = $this->topic['title'];

		// Song * poll life, 25.03.05

		if ($poll_data['live_before'] and !($poll_data['state'] == 'closed' or $this->topic['state'] == 'closed'))
		{
			$days_left = round(($poll_data['live_before'] - time()) / 86400);

			if ($days_left < 1)
				$days_left = 1;

			$ibforums->lang['poll_life_descr3'] = sprintf($ibforums->lang['poll_life_descr3'], $days_left);

			$expired = View::make("poll.poll_expired_row");

			if (time() > $poll_data['live_before'])
			{
				$ibforums->db->exec("UPDATE ibf_polls
				    SET state='closed'
				    WHERE tid='" . $this->topic['tid'] . "'");
				// vot: BAD QUERY
				$ibforums->db->exec("UPDATE ibf_topics
				    SET description='голосование окончено'
				    WHERE tid='" . $this->topic['tid'] . "'");

				$poll_data['state'] = "closed";
			}
		} else
		{
			$expired = "";
		}

		// /Song * poll life, 25.03.05
		//----------------------------------

		$delete_link = "";
		$edit_link   = "";
		$can_close   = 0;
		$can_edit    = 0;
		$can_delete  = 0;

		if ($this->moderator['edit_post'])
			$can_edit = 1;

		if ($this->moderator['delete_post'])
			$can_delete = 1;

		if ($this->moderator['close_topic'])
			$can_close = 1;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$can_edit   = 1;
			$can_delete = 1;
			$can_close  = 1;
		}

		if ($can_edit == 1)
		{
			$edit_link = View::make(
				"poll.edit_link",
				['tid' => $this->topic['tid'], 'fid' => $this->forum['id'], 'key' => $this->md5_check]
			);
		}

		if ($can_delete == 1)
		{
			$delete_link = View::make(
				"poll.delete_link",
				['tid' => $this->topic['tid'], 'fid' => $this->forum['id'], 'key' => $this->md5_check]
			);
		}

		if ($can_edit == 1)
		{
			$edit_link = View::make(
				"poll.edit_link",
				['tid' => $this->topic['tid'], 'fid' => $this->forum['id'], 'key' => $this->md5_check]
			);
		}

		if ($can_close == 1)
		{
			$close_link = ($poll_data['state'] == 'open')
				? View::make(
					"poll.close_link",
					['tid' => $this->topic['tid'], 'fid' => $this->forum['id'], 'key' => $this->md5_check]
				)
				: View::make(
					"poll.restore_link",
					['tid' => $this->topic['tid'], 'fid' => $this->forum['id'], 'key' => $this->md5_check]
				);
		}

		//----------------------------------

		$voter = array('id' => 0);

		//----------------------------------
		// Have we voted in this poll?
		//----------------------------------

		$stmt = $ibforums->db->query("SELECT member_id
		    FROM ibf_voters
		    WHERE
			member_id='" . $ibforums->member['id'] . "' and
			tid='" . $this->topic['tid'] . "'");

		$voter = $stmt->fetch();

		if ($voter['member_id'] != 0)
		{
			$check       = 1;
			$poll_footer = $ibforums->lang['poll_you_voted'];
		}

		if ($poll_data['starter_id'] == $ibforums->member['id'] and $ibforums->vars['allow_creator_vote'] != 1)
		{
			$check       = 1;
			$poll_footer = $ibforums->lang['poll_you_created'];
		}

		if (!$ibforums->member['id'])
		{
			$check       = 1;
			$poll_footer = $ibforums->lang['poll_no_guests'];
		}

		//----------------------------------
		// is the topic locked?
		//----------------------------------

		if ($poll_data['state'] == 'closed' or $this->topic['state'] == 'closed')
		{
			$check       = 1;
			$poll_footer = $ibforums->lang['poll_closed'];
		}

		if ($ibforums->vars['allow_result_view'] == 1)
		{
			if ($ibforums->input['mode'] == 'show')
			{
				$check       = 1;
				$poll_footer = "";
			}
		}

		if ($check == 1)
		{
			//---------------------
			// Show the results
			//---------------------

			$all_votes   = 0;
			$total_votes = 0;

			$html = View::make(
				"poll.poll_header",
				[
					'tid'     => $this->topic['tid'],
					'poll_q'  => $poll_data['poll_question'],
					'edit'    => $edit_link,
					'delete'  => $delete_link,
					'close'   => $close_link,
					'min_max' => '',
					'expired' => $expired
				]
			);

			$poll_answers = unserialize(stripslashes($poll_data['choices']));

			reset($poll_answers);
			foreach ($poll_answers as $entry)
				$total_votes += $entry[2];
			$all_votes += $total_votes;

			if ($ibforums->member['id'])
			{
				reset($poll_answers);

				foreach ($poll_answers as $ind => $entry)
				{
					$id     = $ind + 1; //$entry[0];
					$choice = $entry[1];
					$votes  = $entry[2];

					if (mb_strlen($choice) < 1)
						continue;

					if ($ibforums->vars['poll_tags'])
						$choice = $this->parser->parse_poll_tags($choice);

					if ($ibforums->vars['post_wordwrap'] > 0)
					{
						$choice = $this->parser->my_wordwrap($choice, $ibforums->vars['post_wordwrap']);
					}

					// Song * multiple choices

					$percent = $votes == 0
						? 0
						: $votes / $total_votes * 100;

					$percent = sprintf('%.2f', $percent);
					$width   = $percent > 0
						? (int)$percent * 2
						: 0;
					$bar     = "";

					if ($votes > 0)
					{
						$bar = "<img src='{$ibforums->skin['ImagesPath']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''>";
						$bar .= "<img src='{$ibforums->skin['ImagesPath']}/bar.gif' border='0' width='$width' height='11' align='middle' alt=''>";
						$bar .= "<img src='{$ibforums->skin['ImagesPath']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''>&nbsp;[{$percent}%]</td>";
					}

					$html .= View::make(
						"poll.Render_row_results",
						['votes' => $votes, 'id' => $id, 'answer' => $choice, 'procent_bar' => $bar]
					);
					// /Song * multiple choices
				}

				$html .= View::make(
					"poll.show_total_votes",
					['votes' => $poll_data['votes'], 'total_votes' => $total_votes]
				);
			} else
				$html .= "</table><b>" . $ibforums->lang['guests_no_view'] . "</b>";
		} else
		{
			$poll_answers = unserialize(stripslashes($poll_data['choices']));
			reset($poll_answers);

			//---------------------
			// Show poll form
			//---------------------

			if ($poll_data['is_multi_poll'])
			{
				$min_max = '( Минимально пунктов выбора: ' . $poll_data['multi_poll_min'] . ', Максимально пунктов выбора: ' . $poll_data['multi_poll_max'] . ' )';
			}
			if ($poll_data['is_weighted_poll'])
			{
				$places  = $poll_data['weighted_poll_places'];
				$min_max = '( Распределите ' . $places . ' мест )';
				while (list($key, $value) = each($poll_answers))
				{
					if ($value)
						$poll_count++;
				}
			}

			$html = View::make(
				"poll.poll_header",
				[
					'tid'     => $this->topic['tid'],
					'poll_q'  => $poll_data['poll_question'],
					'edit'    => $edit_link,
					'delete'  => $delete_link,
					'close'   => $close_link,
					'min_max' => $min_max,
					'expired' => $expired
				]
			);
			$type = ($poll_data['is_multi_poll'])
				? "checkbox"
				: "radio";
			$name = 'poll_vote';

			if ($poll_data['is_weighted_poll'])
				$html .= View::make("poll.weighted_js", ['count' => $poll_count]);
			if ($poll_data['is_weighted_poll'] || $poll_data['is_multi_poll'])
				$i = 1;

			if ($ibforums->member['id'])
			{
				foreach ($poll_answers as $ind => $entry)
				{
					$id     = $ind + 1; //$entry[0];
					$choice = $entry[1];
					$votes  = $entry[2];

					if (mb_strlen($choice) < 1)
						continue;

					if ($ibforums->vars['poll_tags'])
					{
						$choice = $this->parser->parse_poll_tags($choice);
					}

					if ($ibforums->vars['post_wordwrap'] > 0)
					{
						$choice = $this->parser->my_wordwrap($choice, $ibforums->vars['post_wordwrap']);
					}

					// Song * multiple choices

					if ($poll_data['is_multi_poll'])
					{
						$name = 'poll_vote[' . $i++ . ']';
						$id++;
					}

					if ($poll_data['is_weighted_poll'])
					{
						$name = 'poll_vote[' . $i++ . ']';
						$html .= View::make(
							"poll.Render_row_form_weighted",
							[
								'id'         => $id,
								'choice'     => $choice,
								'name'       => $name,
								'places'     => $places,
								'poll_count' => $poll_count
							]
						);
					} else
						$html .= View::make(
							"poll.Render_row_form",
							['votes' => $votes, 'id' => $id, 'answer' => $choice, 'type' => $type, 'name' => $name]
						);
				}
			} else
				$html .= "</table><b>" . $ibforums->lang['guests_no_view'] . "</b>";
		}

		$html .= View::make("poll.ShowPoll_footer");

		if ($poll_footer != "")
		{
			//-----------------------------
			// Already defined..
			//-----------------------------

			$html = str_replace("<!--IBF.VOTE-->", $poll_footer, $html);
		} else
		{
			//-----------------------------
			// Not defined..
			//-----------------------------

			if ($ibforums->vars['allow_result_view'] == 1)
			{
				if ($ibforums->input['mode'] == 'show')
				{
					// We are looking at results..

					$html = str_replace("<!--IBF.SHOW-->", View::make("poll.button_show_voteable"), $html);
				} else
				{
					$html = str_replace("<!--IBF.SHOW-->", View::make("poll.button_show_results"), $html);
					$html = str_replace("<!--IBF.VOTE-->", View::make("poll.button_vote"), $html);
				}
			} else
			{
				//-----------------------------
				// Do not allow result viewing
				//-----------------------------

				$html = str_replace("<!--IBF.VOTE-->", View::make("poll.button_vote"), $html);
				$html = str_replace("<!--IBF.SHOW-->", View::make("poll.button_null_vote"), $html);
			}
		}

		$html = str_replace("<!--IBF.POLL_JS-->",
			View::make("poll.poll_javascript", ['tid' => $this->topic['tid'], 'fid' => $this->forum['id']]), $html);

		return $html;
	}

	//---------------------------------------------
	function return_last_post()
	{
		global $ibforums, $std;

		$st = 0;

		if ($this->topic['posts'])
		{
			if ((($this->topic['posts'] + 1) % $ibforums->vars['display_max_posts']) == 0)
			{
				$pages = ($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
			} else
			{
				$number = (($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
				$pages  = ceil($number);
			}

			$st = ($pages - 1) * $ibforums->vars['display_max_posts'];
		}

		$stmt = $ibforums->db->query("SELECT max(pid) as pid
			    FROM ibf_posts
			    WHERE
				queued != 1 AND
				use_sig = 0 AND
				topic_id='" . $this->topic['tid'] . "'");
		$post = $stmt->fetch();

		$std->boink_it($ibforums->base_url . "showtopic=" . $this->topic['tid'] . "&amp;st=$st&" . "#entry" . $post['pid']);
		exit();
	}

	//---------------------------------------------------
	// Song * new topic rights
	//---------------------------------------------------

	function allow_topic()
	{
		global $ibforums, $std;

		if (!$ibforums->member['g_post_new_topics']
		    or $std->check_perms($this->forum['start_perms']) == FALSE
		)
			return 0;

		return 1;
	}

}
