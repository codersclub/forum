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
  |   > Moderation core module
  |   > Module written by Matt Mecham
  |   > Date started: 19th February 2002
  |
  |   > Module Version 1.0.0
  +--------------------------------------------------------------------------
 */

require_once ROOT_PATH . '/sources/lib/classes/topic.class.php';

$idx = new Moderate;

class Moderate
{

	var $output = "";
	var $base_url = "";
	var $html = "";
	var $moderator = "";
	var $modfunc = "";
	var $forum = array();
	var $topic = array();
	var $upload_dir = "";

	//***********************************************************************************/
	//
	// Our constructor, load words, load skin, print the topic listing
	//
	//***********************************************************************************/

	function Moderate()
	{

		global $ibforums, $std, $print, $skin_universal;

		//-------------------------------------
		// Make sure this is a POST request, not a naughty IMG redirect
		//-------------------------------------

		if ($ibforums->input['CODE'] != '04' && $ibforums->input['CODE'] != '02' && $ibforums->input['CODE'] != '20' && $ibforums->input['CODE'] != '22' && $ibforums->input['CODE'] != '70' && $ibforums->input['CODE'] != '72' && $ibforums->input['CODE'] != '82' && $ibforums->input['CODE'] != '18' && $ibforums->input['CODE'] != '19' && $ibforums->input['CODE'] != '24' && $ibforums->input['CODE'] != '25' && $ibforums->input['CODE'] != '28' && $ibforums->input['CODE'] != '29' && $ibforums->input['CODE'] != '33' && $ibforums->input['CODE'] != '34' && $ibforums->input['CODE'] != '35' && $ibforums->input['CODE'] != '36')
		{
			if ($_POST['act'] == '')
			{
				$this->Error(array(
				                  'LEVEL' => 1,
				                  'MSG'   => 'incorrect_use'
				             ));
			}
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['CODE'] != '02' && $ibforums->input['CODE'] != '05')
		{
			if ($ibforums->input['auth_key'] != $std->return_md5_check())
			{
				$this->Error(array('LEVEL' => 1, 'MSG' => 'del_post'));
			}
		}

		//-------------------------------------
		// Compile the language file
		//-------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_mod', $ibforums->lang_id);

		$this->html = $std->load_template('skin_mod');

		//-------------------------------------
		// Check the input
		//-------------------------------------

		if ($ibforums->input['p'])
		{
			$ibforums->input['p'] = intval($ibforums->input['p']);

			if (!$ibforums->input['p'])
			{
				$this->Error(array(
				                  'LEVEL' => 1,
				                  'MSG'   => 'missing_files'
				             ));
			}
		}

		$ibforums->input['f'] = intval($ibforums->input['f']);

		if (!$ibforums->input['f'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'missing_files'
			             ));
		}

		$ibforums->input['st'] = intval($ibforums->input['st']);

		//-------------------------------------
		// Get the forum info based on the forum ID,
		// get the category name, ID, and get the topic details
		//-------------------------------------

		$stmt = $ibforums->db->query("SELECT
			f.*,
			c.name as cat_name,
			c.id as cat_id
		    FROM
			ibf_forums f,
			ibf_categories c
		    WHERE
			f.id=" . $ibforums->input['f'] . "
			AND c.id=f.category");

		$this->forum = $stmt->fetch();

		if (!$this->forum['id'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'missing_files'
			             ));
		}

		if ($ibforums->input['t'])
		{
			$ibforums->input['t'] = intval($ibforums->input['t']);

			if (!$ibforums->input['t'])
			{
				$this->Error(array(
				                  'LEVEL' => 1,
				                  'MSG'   => 'missing_files'
				             ));
			} else
			{
				$stmt = $ibforums->db->query("SELECT
					tid,
					title,
					description,
					posts,
					state,
					starter_id,
					pinned,
					forum_id,
					last_post,
					hidden
				    FROM ibf_topics
				    WHERE tid='" . $ibforums->input['t'] . "'");

				$this->topic = $stmt->fetch();

				if (empty($this->topic['tid']))
				{
					$this->Error(array(
					                  'LEVEL' => 1,
					                  'MSG'   => 'missing_files'
					             ));
				}

				if ($this->forum['id'] != $this->topic['forum_id'])
				{
					$this->moderate_error();
				}
			}
		}

		//-------------------------------------
		// Error out if we can not find the forum
		//-------------------------------------

		$this->base_url = $ibforums->base_url;

		//-------------------------------------
		// Are we a moderator?
		//-------------------------------------

		if ($ibforums->member['id'])
		{
			if ($ibforums->member['g_is_supmod'] != 1)
			{
				$stmt = $ibforums->db->query("SELECT *
				    FROM ibf_moderators
				    WHERE
					forum_id=" . $this->forum['id'] . "
					AND (member_id=" . $ibforums->member['id'] . "
					    OR (is_group=1
					    AND group_id=" . $ibforums->member['mgroup'] . "))");

				$this->moderator = $stmt->fetch();
			}
		}

		//-------------------------------------
		// Load mod module...
		//-------------------------------------

		require(ROOT_PATH . 'sources/lib/modfunctions.php');

		$this->modfunc = new modfunctions();

		$this->modfunc->init($this->forum);

		$this->upload_dir = $ibforums->vars['upload_dir'];

		//-------------------------------------
		// Convert the code ID's into something
		// use mere mortals can understand....
		//-------------------------------------

		switch ($ibforums->input['CODE'])
		{
			case '02':
				$this->move_form();
				break;
			case '03':
				$this->delete_form();
				break;
			case '04':
				$this->delete_post();
				break;
			case '05':
				$this->edit_form();
				break;
			case '00':
				$this->close_topic();
				break;
			case '01':
				$this->open_topic();
				break;
			case '08':
				$this->delete_topic();
				break;
			case '12':
				$this->do_edit();
				break;
			case '14':
				$this->do_move();
				break;
			case '15':
				$this->pin_topic();
				break;
			case '16':
				$this->unpin_topic();
				break;
			case '17':
				$this->rebuild_topic();
				break;

			// Song * show/hide topics

			case '26':
				$this->hide_topic();
				break;
			case '27':
				$this->show_topic();
				break;

			// Song * delete delayed

			case '28':
				$this->delete_delayed(1);
				break;
			case '29':
				$this->delete_delayed(0);
				break;

			case '32':
				$this->some_delete_delayed();
				break;

			// Song * decided topics

			case '33':
				$this->decided_topics(1);
				break;
			case '34':
				$this->decided_topics(0);
				break;

			// Song * decline/restore post

			case '18':
				$this->decline_restore_post(1);
				break;
			case '19':
				$this->decline_restore_post(0);
				break;

			// Song * add to faq, 02.05.05

			case '35':
				$this->add_to_faq();
				break;

			case '36':
				$this->do_add_to_faq($ibforums->input['forum_id']);
				break;

			//-------------------------
			case '20':
				$this->poll_edit_form();
				break;
			case '21':
				$this->poll_edit_do();
				break;
			//-------------------------
			case '22':
				$this->poll_delete_form();
				break;
			case '23':
				$this->poll_delete_do();
				break;
			case '24':
				$this->poll_close_do('closed');
				break;
			case '25':
				$this->poll_close_do('open');
				break;
			//-------------------------
			case '30':
				$this->unsubscribe_all_form();
				break;
			case '31':
				$this->unsubscribe_all();
				break;

			//-------------------------

			case '50':
				$this->split_start();
				break;

			case '51':
				$this->split_complete();
				break;

			//-------------------------

			case '60':
				$this->merge_start();
				break;
			case '70':
				$this->attach_form();
				break;
			case '61':
				$this->merge_complete();
				break;
			case '62':
				$this->pin_post();
				break;
			case '63':
				$this->unpin_post();
				break;
			case '66':
				$this->start_deleting();
				break;
			case '67':
				$this->complete_moving();
				break;
			case '68':
				$this->complete_deleting();
				break;
			case '71':
				$this->do_add_attach();
				break;
			case '72':
				$this->do_delete_attach();
				break;
			case '81':
				$this->do_add_data();
				break;
			case '82':
				$this->do_delete_data();
				break;

			//-------------------------

			case '90':
				$this->topic_history();
				break;

			case '91':
				$this->mirror_topic_form();
				break;
			case '92':
				$this->do_mirror_topic();
				break;

			case '93':
				$this->delete_mirror_topic_form();
				break;
			case '94':
				$this->do_delete_mirror_topic();
				break;

			default:
				$this->moderate_error();
				break;
		}

		// If we have any HTML to print, do so...

		$print->add_output("$this->output");
		$print->do_output(array(
		                       'TITLE' => $this->page_title,
		                       'JS'    => 0,
		                       'NAV'     => $this->nav
		                  ));
	}

	/*	 * ********************************************** */

	// TOPIC HISTORY:
	// ---------------
	//
	/*	 * ********************************************** */

	function topic_history()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_access_cp'] == 1)
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$tid = intval($ibforums->input['t']);

		//-----------------------------------------
		// Get all info for this topic-y-poos
		//-----------------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_topics WHERE tid='$tid'");

		$topic = $stmt->fetch();

		if ($topic['last_post'] == $topic['start_date'])
		{
			$avg_posts = 1;
		} else
		{
			$avg_posts = round(($topic['posts'] + 1) / ((($topic['last_post'] - $topic['start_date']) / 86400)), 1);
		}

		if ($avg_posts < 0)
		{
			$avg_posts = 1;
		}

		if ($avg_posts > ($topic['posts'] + 1))
		{
			$avg_posts = $topic['posts'] + 1;
		}

		$data = array(
			'th_topic'      => $topic['title'],
			'th_desc'       => $topic['description'],
			'th_start_date' => $std->get_date($topic['start_date']),
			'th_start_name' => $std->make_profile_link($topic['starter_name'], $topic['starter_id']),
			'th_last_date'  => $std->get_date($topic['last_post']),
			'th_last_name'  => $std->make_profile_link($topic['last_poster_name'], $topic['last_poster_id']),
			'th_avg_post'   => $avg_posts,
		);

		$this->output .= $this->html->topic_history($data);

		$this->output .= $this->html->mod_log_start();

		// Do we have any logs in the mod-logs DB about this topic? eh? well?

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_moderator_logs
			    WHERE topic_id='$tid'
			    ORDER BY ctime DESC");

		if (!$stmt->rowCount())
		{
			$this->output .= $this->html->mod_log_none();
		} else
		{
			while ($row = $stmt->fetch())
			{
				$row['member'] = $std->make_profile_link($row['member_name'], $row['member_id']);
				$row['date']   = $std->get_date($row['ctime']);
				$this->output .= $this->html->mod_log_row($row);
			}
		}

		$this->output .= $this->html->mod_log_end();

		$this->page_title = $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	//=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+

	/*	 * ********************************************** */
	// SPLIT TOPICS:
	// ---------------
	//
	/*	 * ********************************************** */

	function split_start()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['split_merge'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		//-----------------------------------------

		$this->parser = new PostParser();

		//-----------------------------------------

		$source    = array();
		$source[1] = array('CODE', '51');
		$source[2] = array('t', $this->topic['tid']);
		$source[3] = array('f', $this->forum['id']);
		$n         = 4;

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^pozt(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$source[$n] = array('post_' . $match[1], 1);
					$n++;
				}
			}
		}

		if ($n == 4)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'no_msg_checked'
			             ));
		}

		$jump_html = $std->build_forum_jump(0, 1);

		$this->output = $this->html_start_form($source);

		$this->output .= $this->html->table_top($ibforums->lang['st_top'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);

		$this->output .= $this->html->mod_exp($ibforums->lang['st_explain']);

		$this->output .= $this->html->split_body($jump_html);

		$this->output .= $this->html->split_end_form($ibforums->lang['st_submit']);

		$this->page_title = $ibforums->lang['st_top'] . " " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	//=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+

	function split_complete()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['split_merge'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		//------------------------------------------
		// Check the input
		//------------------------------------------

		if (!$ibforums->input['title'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'complete_form'
			             ));
		}

		//------------------------------------------
		// Get the post ID's to split
		//------------------------------------------

		$ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^post_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		$affected_ids = count($ids);

		//------------------------------------------
		// Do we have enough?
		//------------------------------------------

		if ($affected_ids < 1)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'split_not_enough'
			             ));
		}

		//------------------------------------------
		// Do we too many?
		//------------------------------------------

		$count = $ibforums->db
			->prepare("SELECT count(pid) as cnt FROM ibf_posts WHERE topic_id=:tid")
			->bindParam(':tid', $this->topic['tid'], PDO::PARAM_INT)
			->execute()
			->fetchColumn();

		if ($affected_ids >= $count)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'split_too_much'
			             ));
		}

		// Complete the PID string

		$pid_string = implode(",", $ids);

		//----------------------------------------------------
		// Check the forum we're moving this too
		//----------------------------------------------------

		$ibforums->input['fid'] = intval($ibforums->input['fid']);

		if ($ibforums->input['fid'] != $this->forum['id'])
		{
			$f = $ibforums->db->prepare("SELECT
					id,
					subwrap,
					sub_can_post
				    FROM ibf_forums
				    WHERE id=:id")
				->bindParam(':id', $ibforums->input['fid'], PDO::PARAM_INT)
				->execute()
				->fetch();

			if (!$f)
			{
				$this->Error(array(
				                  'LEVEL' => 1,
				                  'MSG'   => 'move_no_forum'
				             ));
			}

			if ($f['subwrap'] == 1 and $f['sub_can_post'] != 1)
			{
				$this->Error(array(
				                  'LEVEL' => 1,
				                  'MSG'   => 'forum_no_post_allowed'
				             ));
			}
		}

		// Song * change code tag

		$posts = $ibforums->db->query("SELECT
					pid,
					forum_id,
					post
				   FROM ibf_posts
				   WHERE
					post LIKE '%[code%' AND
					pid IN ($pid_string)");

		$updated_query = $ibforums->db
			->prepare("UPDATE ibf_posts
				SET post=:post
				WHERE pid=:pid");

		foreach ($posts as $post)
		{
			if ($txt = preg_replace("#\[code\s*?(=\s*?(.*?)|)\s*\](.*?)\[/code\]#ies", "\$this->modfunc->regex_code_syntax('\\3', '\\2', " . $post['forum_id'] . ")", $post['post']) and
			    $txt != $post['post']
			)
			{
				$updated_query
					->bindParam(':post', $txt, PDO::PARAM_STR)
					->bindParam(':pid', $post['pid'], PDO::PARAM_INT)
					->execute();
				$updated_query->closeCursor();
			}
		}

		//----------------------------------------------------
		// Complete a new dummy topic
		//----------------------------------------------------

		$new_topic = array(
			'title'            => $ibforums->input['title'],
			'description'      => $ibforums->input['desc'],
			'state'            => 'open',
			'posts'            => 0,
			'starter_id'       => 0,
			'starter_name'     => 0,
			'start_date'       => time(),
			'last_poster_id'   => 0,
			'last_poster_name' => 0,
			'last_post'        => time(),
			'icon_id'          => 0,
			'author_mode'      => 1,
			'poll_state'       => 0,
			'last_vote'        => 0,
			'views'            => 0,
			'forum_id'         => $ibforums->input['fid'],
			'approved'         => 1,
			'pinned'           => 0,
		);

		$ibforums->db->insertRow("ibf_topics", $new_topic);

		$new_topic_id = $ibforums->db->lastInsertId();

		//----------------------------------------------------
		// Move the posts
		//----------------------------------------------------

		$ibforums->db->exec("UPDATE ibf_posts
			    SET
				forum_id='" . $ibforums->input['fid'] . "',
				topic_id='" . $new_topic_id . "'
			    WHERE pid IN ($pid_string)");

		// Song * remain notify in old topic
		// get last remained post in old topic

		$stmt = $ibforums->db->query("SELECT max(pid) as pid
			    FROM ibf_posts
			    WHERE topic_id='" . $this->topic['tid'] . "'");

		if ($last_post = $stmt->fetch())
		{
			$split_line = "\n\n[COLOR=gray][SIZE=0]" . $ibforums->lang['split_old_topic'] . "&quot;[URL={$this->base_url}showtopic={$new_topic_id}]{$new_topic['title']}[/URL]&quot;[/SIZE][/COLOR]";

			$ibforums->db->exec("UPDATE ibf_posts SET post=Concat(post,'" . addslashes($split_line) . "')
				    WHERE pid='" . $last_post['pid'] . "'");
		}

		//----------------------------------------------------
		// NEW TOPIC: Get the last / first post in the "new" topic
		//----------------------------------------------------

		$stmt = $ibforums->db->query("SELECT
				author_id,
				author_name,
				post_date
			    FROM ibf_posts
			    WHERE topic_id='$new_topic_id'
			    ORDER BY post_date DESC
			    LIMIT 1");

		$last_post = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT
				pid,
				author_id,
				author_name,
				post_date
			    FROM ibf_posts
			    WHERE topic_id='$new_topic_id'
			    ORDER BY post_date
			    LIMIT 1");

		$first_post = $stmt->fetch();

		// Get the number of posts in this "new" topic

		$stmt = $ibforums->db->query("SELECT count(pid) as posts
			    FROM ibf_posts
			    WHERE
				queued != 1 AND
				topic_id='$new_topic_id'");

		$post_count = $stmt->fetch();

		// Remove 1 from the count as we don't count the first post

		$post_count['posts']--;

		//----------------------------------------------------
		// NEW TOPIC: Update new topic entry in DB
		//----------------------------------------------------

		$new_topic = array(
			'posts'            => $post_count['posts'],
			'starter_id'       => $first_post['author_id'],
			'starter_name'     => $first_post['author_name'],
			'start_date'       => $first_post['post_date'],
			'last_poster_id'   => $last_post['author_id'],
			'last_poster_name' => $last_post['author_name'],
			'last_post'        => $last_post['post_date'],
			'author_mode'      => $first_post['author_id']
				? 1
				: 0,
		);

		$stmt = $ibforums->db->updateRow("ibf_topics", array_map([
		                                                         $ibforums->db,
		                                                         'quote'
		                                                         ], $new_topic), "tid=" . $ibforums->db->quote($new_topic_id));

		//----------------------------------------------------
		// NEW TOPIC: Reset the new_topic bit
		//----------------------------------------------------

		$ibforums->db->exec("UPDATE ibf_posts
			    SET new_topic=0
			    WHERE topic_id='$new_topic_id'");

		// Song * remain notify in new topic

		$moved_line = "[COLOR=gray][SIZE=0]" . $ibforums->lang['split_topic'] . "&quot;[URL={$this->base_url}showtopic={$this->topic['tid']}]{$this->topic['title']}[/URL]&quot;[/SIZE][/COLOR]";

		$ibforums->db->exec("UPDATE ibf_posts
			    SET
			    	delete_after=0,
				new_topic=1,
				post=Concat(post,'\n\n" . addslashes($moved_line) . "')
			    WHERE
				topic_id='$new_topic_id' AND
				pid='" . $first_post['pid'] . "'");

		unset($last_post);
		unset($first_post);
		unset($post_count);
		unset($new_topic);

		//----------------------------------------------------
		// OLD TOPIC: Get the last / first post in the "old" topic
		//----------------------------------------------------

		$stmt = $ibforums->db->query("SELECT
				author_id,
				author_name,
				post_date
			    FROM ibf_posts
			    WHERE topic_id='" . $this->topic['tid'] . "'
			    ORDER BY post_date DESC
			    LIMIT 1");

		$last_post = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT
				pid,
				author_id,
				author_name,
				post_date
			    FROM ibf_posts
			    WHERE topic_id='" . $this->topic['tid'] . "'
			    ORDER BY post_date
			    LIMIT 1");

		$first_post = $stmt->fetch();

		// Get the number of posts in this "new" topic

		$stmt = $ibforums->db->query("SELECT count(pid) as posts
			    FROM ibf_posts
			    WHERE
				queued != 1 AND
				topic_id='" . $this->topic['tid'] . "'");

		$post_count = $stmt->fetch();

		// Remove 1 from the count as we don't count the first post

		$post_count['posts']--;

		//----------------------------------------------------
		// OLD TOPIC: Update new topic entry in DB
		//----------------------------------------------------

		$new_topic = array(
			'posts'            => $post_count['posts'],
			'starter_id'       => $first_post['author_id'],
			'starter_name'     => $first_post['author_name'],
			'start_date'       => $first_post['post_date'],
			'last_poster_id'   => $last_post['author_id'],
			'last_poster_name' => $last_post['author_name'],
			'last_post'        => $last_post['post_date'],
			'author_mode'      => $first_post['author_id']
				? 1
				: 0,
		);

		$stmt = $ibforums->db->updateRow("ibf_topics", array_map([
		                                                         $ibforums->db,
		                                                         'quote'
		                                                         ], $new_topic), "tid=" . $ibforums->db->quote($this->topic['tid']));

		//----------------------------------------------------
		// OLD TOPIC: Reset the new_topic bit
		//----------------------------------------------------

		$ibforums->db->exec("UPDATE ibf_posts
			    SET new_topic=0
			    WHERE topic_id='" . $this->topic['tid'] . "'");

		$ibforums->db->exec("UPDATE ibf_posts
			    SET new_topic=1
			    WHERE
				topic_id='" . $this->topic['tid'] . "' AND
				pid='" . $first_post['pid'] . "'");

		//----------------------------------------------------
		// Update the forum(s)
		//----------------------------------------------------

		$this->recount($this->topic['forum_id']);

		if ($this->topic['forum_id'] != $ibforums->input['fid'])
		{
			$this->recount($ibforums->input['fid']);
		}

		$this->moderate_log("Split topic '{$this->topic['title']}'");

		$print->redirect_screen($ibforums->lang['st_redirect'], "act=SF&f=" . $this->forum['id']);
	}

	/*	 * ********************************************** */

	// MERGE TOPICS:
	// ---------------
	//
	/*	 * ********************************************** */

	function merge_start()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['split_merge'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '61'),
		                                            2 => array('t', $this->topic['tid']),
		                                            3 => array('f', $this->forum['id']),
		                                       ));

		$this->output .= $this->html->table_top($ibforums->lang['mt_top'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);

		$this->output .= $this->html->mod_exp($ibforums->lang['mt_explain']);

		$this->output .= $this->html->merge_body($this->topic['title'], $this->topic['description']);

		$this->output .= $this->html->end_form($ibforums->lang['mt_submit']);

		$this->page_title = $ibforums->lang['mt_top'] . " " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	//=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+

	function merge_complete()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['split_merge'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		//------------------------------------------
		// Check the input
		//------------------------------------------

		if ($ibforums->input['topic_url'] == "" or
		    $ibforums->input['title'] == ""
		)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'complete_form'
			             ));
		}

		//------------------------------------------
		// Get the topic ID of the entered URL
		//------------------------------------------

		preg_match("/(\?|&amp;)(t|showtopic)=(\d+)($|&amp;)/", $ibforums->input['topic_url'], $match);

		$old_id = intval(trim($match[3]));

		if ($old_id == "")
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'mt_no_topic'
			             ));
		}

		//------------------------------------------
		// Get the topic from the DB
		//------------------------------------------

		$stmt = $ibforums->db->query("SELECT
				tid,
				title,
				forum_id,
				last_post,
				last_poster_id,
				last_poster_name,
				posts,
				views
			    FROM ibf_topics
			    WHERE tid='$old_id'");

		if (!$old_topic = $stmt->fetch())
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'mt_no_topic'
			             ));
		}

		//------------------------------------------
		// Did we try and merge the same topic?
		//------------------------------------------

		if ($old_id == $this->topic['tid'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'mt_same_topic'
			             ));
		}

		//------------------------------------------
		// Do we have moderator permissions for this
		// topic (ie: in the forum the topic is in)
		//------------------------------------------

		$pass = FALSE;

		if ($this->topic['forum_id'] == $old_topic['forum_id'])
		{
			$pass = TRUE;
		} else
		{
			if ($ibforums->member['g_is_supmod'] == 1)
			{
				$pass = TRUE;
			} else
			{
				$stmt = $ibforums->db->query("SELECT mid
					    FROM ibf_moderators
					    WHERE
						forum_id=" . $old_topic['forum_id'] . "
						AND (member_id='" . $ibforums->member['id'] . "'
						OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");

				if ($stmt->rowCount())
				{
					$pass = TRUE;
				}
			}
		}

		if ($pass == FALSE)
		{
			// No, we don't have permission

			$this->moderate_error();
		}

		//----------------------------------------------------
		// Update the posts, remove old polls, subs and topic
		//----------------------------------------------------

		$moved_line = "[COLOR=gray][SIZE=0]" . $ibforums->lang['moved_post'] . "&quot;" . $old_topic['title'] . "&quot;[/SIZE][/COLOR]";

		// Move the posts to the new Topis
		$ibforums->db->exec("UPDATE
				ibf_posts
			    SET
				forum_id='" . $this->topic['forum_id'] . "',
				topic_id='" . $this->topic['tid'] . "' ,
				post=Concat(post,'\n\n" . addslashes($moved_line) . "')
			    WHERE
				topic_id='" . $old_topic['tid'] . "'");

		// Move the search words to the new Topis
		$ibforums->db->exec("UPDATE
				ibf_search
			    SET
				fid='" . $this->topic['forum_id'] . "',
				tid='" . $this->topic['tid'] . "'
			    WHERE
				tid='" . $old_topic['tid'] . "'");

		$ibforums->db->exec("DELETE
			    FROM ibf_polls
			    WHERE tid='" . $old_topic['tid'] . "'");

		$ibforums->db->exec("DELETE
			    FROM ibf_voters
			    WHERE tid='" . $old_topic['tid'] . "'");

		$ibforums->db->exec("DELETE
			    FROM ibf_tracker
			    WHERE topic_id='" . $old_topic['tid'] . "'");

		$ibforums->db->exec("DELETE
			    FROM ibf_topics
			    WHERE tid='" . $old_topic['tid'] . "'");

		//----------------------------------------------------
		// Update the newly merged topic
		//----------------------------------------------------

		$updater = array(
			'title'       => $ibforums->db->quote($ibforums->input['title']),
			'description' => $ibforums->db->quote($ibforums->input['desc']),
			'views'       => "views+{$old_topic['views']}",
		);

		if ($old_topic['last_post'] > $this->topic['last_post'])
		{
			$updater['last_post']        = $ibforums->db->quote($old_topic['last_post']);
			$updater['last_poster_name'] = $ibforums->db->quote($old_topic['last_poster_name']);
			$updater['last_poster_id']   = $ibforums->db->quote($old_topic['last_poster_id']);
		}

		// We need to now count the original post, which isn't in the "posts" field 'cos it was a new topic

		$old_topic['posts']++;

		$ibforums->db->updateRow('ibf_topics', $updater, 'tid=' . $ibforums->db->quote($this->topic['tid']));

		//----------------------------------------------------
		// Fix up the "new_topic" attribute.
		//----------------------------------------------------

		$ibforums->db->exec("UPDATE ibf_posts
			    SET new_topic=0
			    WHERE topic_id='" . $this->topic['tid'] . "'");

		$stmt = $ibforums->db->query("SELECT
				pid,
				author_name,
				author_id,
				post_date
			    FROM ibf_posts
			    WHERE topic_id='" . $this->topic['tid'] . "'
			    ORDER BY post_date
			    LIMIT 1");

		if ($first_post = $stmt->fetch())
		{
			$ibforums->db->exec("UPDATE ibf_posts
				    SET
					new_topic=1,
					delete_after=0
				    WHERE pid='" . $first_post['pid'] . "'");
		}

		//----------------------------------------------------
		// Reset the post count for this topic
		//----------------------------------------------------

		$amode = $first_post['author_id']
			? 1
			: 0;

		$stmt = $ibforums->db->query("SELECT count(pid) as posts
			    FROM ibf_posts
			    WHERE
				queued != 1 AND
				topic_id='" . $this->topic['tid'] . "'");

		if ($post_count = $stmt->fetch())
		{
			$post_count['posts']--; //Remove first post

			$ibforums->db->exec("UPDATE ibf_topics
			           SET
					posts=" . $post_count['posts'] . ",
					starter_name='" . $first_post['author_name'] . "',
					starter_id='" . $first_post['author_id'] . "',
					start_date='" . $first_post['post_date'] . "',
					author_mode=$amode
			           WHERE tid='" . $this->topic['tid'] . "'");
		}

		//----------------------------------------------------
		// Update the forum(s)
		//----------------------------------------------------

		$this->recount($this->topic['forum_id']);

		if ($this->topic['forum_id'] != $old_topic['forum_id'])
		{
			$this->recount($old_topic['forum_id']);
		}

		$this->moderate_log("Merged topic '{$old_topic['title']}' with '{$this->topic['title']}'");

		$print->redirect_screen($ibforums->lang['mt_redirect'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid']);
	}

	/*	 * ********************************************** */

	// UNSUBSCRIBE ALL FORM:
	// ---------------------
	//
	/*	 * ********************************************** */

	function unsubscribe_all_form()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$stmt = $ibforums->db->query("SELECT COUNT(trid) as subbed
			    FROM ibf_tracker
			    WHERE topic_id='" . $this->topic['tid'] . "'");

		$tracker = $stmt->fetch();

		/* if (! $tracker = $stmt->fetch() )
		  {
		  $this->moderate_error();
		  } */

		if ($tracker['subbed'] < 1)
		{
			$text = $ibforums->lang['ts_none'];
		} else
		{
			$text = sprintf($ibforums->lang['ts_count'], $tracker['subbed']);
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '31'),
		                                            2 => array('t', $this->topic['tid']),
		                                            3 => array('f', $this->forum['id']),
		                                       ));

		$this->output .= $this->html->table_top($ibforums->lang['ts_title'] . " &gt; " . $this->forum['name'] . " &gt; " . $this->topic['title']);

		$this->output .= $this->html->mod_exp($text);

		$this->output .= $this->html->end_form($ibforums->lang['ts_submit']);

		$this->page_title = $ibforums->lang['ts_title'] . " &gt; " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	//---------------------------------

	function unsubscribe_all()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		// Delete the subbies based on this topic ID

		$ibforums->db->exec("DELETE
			    FROM ibf_tracker
			    WHERE topic_id='" . $this->topic['tid'] . "'");

		$print->redirect_screen($ibforums->lang['ts_redirect'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	/*	 * ********************************************** */

	// EDIT POLL FORM:
	// ---------------
	//
	/*	 * ********************************************** */

	function poll_delete_form()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['delete_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_polls
			    WHERE tid='" . $this->topic['tid'] . "'");

		$poll_data = $stmt->fetch();

		if (!$poll_data['pid'])
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '23'),
		                                            2 => array('t', $this->topic['tid']),
		                                            3 => array('f', $this->forum['id']),
		                                       ));

		$this->output .= $this->html->table_top($ibforums->lang['pd_top'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);

		$this->output .= $this->html->mod_exp($ibforums->lang['pd_text']);

		$this->output .= $this->html->end_form($ibforums->lang['pd_submit']);

		$this->page_title = $ibforums->lang['pd_top'] . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	function poll_delete_do()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['delete_topic'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		// Remove the poll

		$ibforums->db->exec("DELETE
			    FROM ibf_polls
			    WHERE tid='" . $this->topic['tid'] . "'");

		// Remove from poll votes

		$ibforums->db->exec("DELETE
			    FROM ibf_voters
			    WHERE tid='" . $this->topic['tid'] . "'");

		// Update topic

		$ibforums->db->exec("UPDATE ibf_topics
			    SET
				poll_state='',
				last_vote=''
			    WHERE tid='" . $this->topic['tid'] . "'");

		// Boing!

		$print->redirect_screen($ibforums->lang['pd_redirect'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	function poll_close_do($state)
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['close_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$ibforums->db->exec("UPDATE ibf_polls
			    SET state='" . $state . "'
			    WHERE tid='" . $this->topic['tid'] . "'");

		if ($state == "closed")
		{

			// vot: BAD MESSAGE
			$ibforums->db->exec("UPDATE ibf_topics
				    SET description='голосование окончено'
				    WHERE tid='" . $this->topic['tid'] . "'");
		}

		// Boing!
		$print->redirect_screen($ibforums->lang['pd_redirect'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	function poll_edit_do()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} else {
			if ($this->moderator['edit_post'])
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_polls
			    WHERE tid='" . $this->topic['tid'] . "'");

		$poll_data = $stmt->fetch();

		if (!$poll_data['pid'])
		{
			$this->moderate_error();
		}

		$poll_answers = unserialize(stripslashes($poll_data['choices']));

		reset($poll_answers);

		$new_poll_array = array();
		$ids            = array();
		$rearranged     = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^POLL_(\d+)$/", $key, $match))
			{
				if (isset($ibforums->input[$match[0]]))
				{
					$ids[] = $match[1];
				}
			}
		}

		//--------------------------------------------------

		foreach ($poll_answers as $entry)
		{
			$rearranged[$entry[0]] = array($entry[0], $entry[1], $entry[2]);
		}

		//--------------------------------------------------

		foreach ($ids as $nid)
		{
			//-----------------------------------
			// Is it a current poll thingy?
			//-----------------------------------

			if (strlen($rearranged[$nid][1]) > 0)
			{
				$new_poll_array[] = array($rearranged[$nid][0], $ibforums->input['POLL_' . $nid], $rearranged[$nid][2]);
			} else
			{
				if (strlen($ibforums->input['POLL_' . $nid]) > 0)
				{
					$new_poll_array[] = array($nid, $ibforums->input['POLL_' . $nid], 0);
				}
			}
		}

		//---------------------------------
		// Take care of any new ones...
		//---------------------------------

		$poll_data['choices'] = addslashes(serialize($new_poll_array));

		// Song * multiple choices

		$query = "UPDATE ibf_polls
			  SET
				choices='" . $poll_data['choices'] . "',
				poll_question='" . $ibforums->input['poll_question'] . "'";

		if ($ibforums->input['multi_poll'])
		{
			$query .= ",
				is_multi_poll='1',
				multi_poll_min='" . $ibforums->input['multi_poll_min'] . "',
				multi_poll_max='" . $ibforums->input['multi_poll_max'] . "' ";
			$query .= ",
				is_weighted_poll='0',
				weighted_poll_places='0' ";
		}

		if ($ibforums->input['weighted_poll'])
		{
			$query .= ",
				is_multi_poll='0',
				multi_poll_min='0',
				multi_poll_max='0' ";
			$query .= ",
				is_weighted_poll='1',
				weighted_poll_places='" . $ibforums->input['weighted_poll_places'] . "' ";
		}

		// Song * poll life, 25.03.05

		$life = intval($ibforums->input['life']);

		if ($life and $life < 366)
		{
			$life = time() + 60 * 60 * 24 * $life;

			$query .= ", live_before='" . $life . "' ";
		} else
		{
			$query .= ", live_before=NULL ";
		}

		$query .= "WHERE tid='" . $this->topic['tid'] . "'";

		$stmt = $ibforums->db->query($query);

		//------------------------
		// Update the topic table to change the poll_only value.

		$poll_state = $ibforums->input['pollonly'] == 1
			? 'closed'
			: 'open';

		$ibforums->db->exec("UPDATE ibf_topics
			    SET poll_state='$poll_state'
			    WHERE tid='" . $this->topic['tid'] . "'");

		$this->moderate_log("Edited a Poll");

		$print->redirect_screen($ibforums->lang['pe_done'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	//--------------------------------------

	function poll_edit_form()
	{
		global $std, $ibforums, $print;

		$ibforums->vars['max_poll_choices'] = $ibforums->vars['max_poll_choices']
			? $ibforums->vars['max_poll_choices']
			: 10;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['edit_post'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$stmt = $ibforums->db->query("SELECT * FROM ibf_polls WHERE tid='" . $this->topic['tid'] . "'");

		$poll_data = $stmt->fetch();

		if (!$poll_data['pid'])
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '21'),
		                                            2 => array('t', $this->topic['tid']),
		                                            3 => array('f', $this->forum['id']),
		                                       ));

		$this->output .= $this->html->table_top($ibforums->lang['pe_top'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);

		$this->output .= $this->html->poll_edit_top();

		$poll_answers = unserialize(stripslashes($poll_data['choices']));

		reset($poll_answers);

		foreach ($poll_answers as $entry)
		{
			$id     = $entry[0];
			$choice = $entry[1];
			$votes  = $entry[2];

			$this->output .= $this->html->poll_entry($id, $choice);
		}

		if (count($poll_answers) < $ibforums->vars['max_poll_choices'])
		{
			for ($i = count($poll_answers) + 1; $i <= $ibforums->vars['max_poll_choices']; $i++) // Jureth: added +1 in the loop counter
			{
				$this->output .= $this->html->poll_edit_new_entry($i);
			}
		}

		// Song * poll life, 25.03.05

		$days_left = "";

		if ($poll_data['live_before'] and !($poll_data['state'] == 'closed' or $this->topic['state'] == 'closed'))
		{
			$days_left = round(($poll_data['live_before'] - time()) / 86400);

			if ($days_left < 1)
			{
				$days_left = 1;
			}
		}

		$this->output .= $this->html->poll_select_form($poll_data['poll_question'], $days_left);

		// Song * multiple choices

		$this->output .= $this->html->poll_select_form_additions_multi($poll_data['multi_poll_min'], $poll_data['multi_poll_max'], $poll_data['is_multi_poll']);
		$this->output .= $this->html->poll_select_form_additions_weighted($poll_data['weighted_poll_places'], $poll_data['is_weighted_poll']);

		$this->output .= $this->html->end_form($ibforums->lang['pe_submit']);

		$this->page_title = $ibforums->lang['pe_top'] . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	/*	 * ********************************************** */

	// MOVE FORM:
	// ---------------
	//
	/*	 * ********************************************** */

	function move_form()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['move_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '14'),
		                                            2 => array('tid', $this->topic['tid']),
		                                            3 => array('sf', $this->forum['id']),
		                                       ));

		$jump_html = $std->build_forum_jump(0, 0, 0);

		$this->output .= $this->html->table_top($ibforums->lang['top_move'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);
		$this->output .= $this->html->mod_exp($ibforums->lang['move_exp']);
		$this->output .= $this->html->move_form($jump_html, $this->forum['name']);
		$this->output .= $this->html->end_form($ibforums->lang['submit_move']);

		$this->page_title = $ibforums->lang['t_move'] . ": " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	/*	 * ********************************************** */

	function do_move()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['move_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		//----------------------------------
		// Check for input..
		//----------------------------------

		if (!$ibforums->input['sf'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_source'
			             ));
		}

		//----------------------------------

		if (!$ibforums->input['move_id']
		    or $ibforums->input['move_id'] == -1
		)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_forum'
			             ));
		}

		//----------------------------------

		if ($ibforums->input['move_id'] == $ibforums->input['sf'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_same_forum'
			             ));
		}

		//----------------------------------

		$stmt = $ibforums->db->query("SELECT
				id,
				subwrap,
				sub_can_post,
				name,
				redirect_on
			    FROM ibf_forums
			    WHERE id IN(" . $ibforums->input['sf'] . "," . $ibforums->input['move_id'] . ")");

		if ($stmt->rowCount() != 2)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_forum'
			             ));
		}

		$source = intval($ibforums->input['sf']);
		$moveto = intval($ibforums->input['move_id']);

		$source_name = "";
		$dest_name   = "";

		//-----------------------------------
		// Check for an attempt to move into a subwrap forum
		//-----------------------------------

		while ($f = $stmt->fetch())
		{
			if ($f['id'] == $ibforums->input['sf'])
			{
				$source_name = $f['name'];
			} else
			{
				$dest_name = $f['name'];
			}

			if (($f['subwrap'] == 1 and $f['sub_can_post'] != 1) OR $f['redirect_on'] == 1)
			{
				$this->Error(array(
				                  'LEVEL' => 1,
				                  'MSG'   => 'forum_no_post_allowed'
				             ));
			}
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_topics
			    WHERE tid='" . $ibforums->input['tid'] . "'");

		if (!$this->topic = $stmt->fetch())
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_forum'
			             ));
		}

		$ibforums->input['leave'] = $ibforums->input['leave'] == 'y'
			? 1
			: 0;

		$this->modfunc->topic_move($this->topic['tid'], $ibforums->input['sf'], $ibforums->input['move_id'], $ibforums->input['leave']);

		$ibforums->input['t'] = $this->topic['tid'];

		$this->moderate_log("Moved a topic from $source_name to $dest_name");

		// Resync the forums..

		$this->modfunc->forum_recount($source);

		$this->modfunc->forum_recount($moveto);

		//Jureth		$this->modfunc->forum_recount_queue($source);
		//Jureth		$this->modfunc->forum_recount_queue($moveto);

		$print->redirect_screen($ibforums->lang['p_moved'], "act=SF&f=" . $this->forum['id'] . "&st=" . $ibforums->input['st']);
	}

	/*	 * ********************************************** */

	// MOVE FORM:
	// ---------------
	//
	/*	 * ********************************************** */

	function mirror_topic_form()
	{

		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['move_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '92'),
		                                            2 => array('tid', $this->topic['tid']),
		                                            3 => array('sf', $this->forum['id']),
		                                       ));

		$jump_html = $std->build_forum_jump(0, 0, 0);

		$this->output .= $this->html->table_top($ibforums->lang['top_mirror'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);
		$this->output .= $this->html->mod_exp($ibforums->lang['mirror_exp']);
		$this->output .= $this->html->mirror_form($jump_html, $this->forum['name']);
		$this->output .= $this->html->end_form($ibforums->lang['submit_mirror']);

		$this->page_title = $ibforums->lang['t_move'] . ": " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	/*	 * ********************************************** */

	function do_mirror_topic()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['move_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		//----------------------------------
		// Check for input..
		//----------------------------------

		if (!$ibforums->input['sf'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_source'
			             ));
		}

		//----------------------------------

		if (!$ibforums->input['move_id']
		    or $ibforums->input['move_id'] == -1
		)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_forum'
			             ));
		}

		$source = intval($ibforums->input['sf']);
		$moveto = $ibforums->input['move_id'];

		$source_name = "";
		$dest_name   = "";

		//-----------------------------------
		// Check for an attempt to move into a subwrap forum
		//-----------------------------------

//no queries -- jureth
//		while ($f = $stmt->fetch())
//		{
//			if ($f['id'] == $ibforums->input['sf'])
//			{
//				$source_name = $f['name'];
//			} else
//			{
//				$dest_name = $f['name'];
//			}
//
//			if (($f['subwrap'] == 1 and $f['sub_can_post'] != 1) OR $f['redirect_on'] == 1)
//			{
//				$this->Error(array(
//				                  'LEVEL' => 1,
//				                  'MSG'   => 'forum_no_post_allowed'
//				             ));
//			}
//		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_topics
			    WHERE tid='" . $ibforums->input['tid'] . "'");

		if (!$this->topic = $stmt->fetch())
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_forum'
			             ));
		}

		$ibforums->input['leave'] = $ibforums->input['leave'] == 'y'
			? 1
			: 0;

		$this->modfunc->topic_mirror_to_another_forum($this->topic['tid'], $ibforums->input['sf'], $ibforums->input['move_id'], $ibforums->input['leave']);

		$ibforums->input['t'] = $this->topic['tid'];

		$this->moderate_log("Mirror a topic from $source_name to $dest_name");

		// Resync the forums..

		$this->modfunc->forum_recount($source);

		$this->modfunc->forum_recount($moveto);

		//Jureth		$this->modfunc->forum_recount_queue($source);
		//Jureth		$this->modfunc->forum_recount_queue($moveto);

		$print->redirect_screen($ibforums->lang['p_moved'], "act=SF&f=" . $this->forum['id'] . "&st=" . $ibforums->input['st']);
	}

	/*	 * ********************************************** */

	// DELETE MIRROR FORM:
	// ---------------
	//
	/*	 * ********************************************** */

	function delete_mirror_topic_form()
	{

		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['move_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '94'),
		                                            2 => array('tid', $this->topic['tid']),
		                                            3 => array('sf', $this->forum['id']),
		                                       ));

		$stmt   = $ibforums->db->query("SELECT f.name, f.id FROM ibf_forums f
					INNER JOIN ibf_topics t ON (t.forum_id = f.id)
					WHERE t.`mirrored_topic_id` = {$this->topic['tid']}");
		$forums = array();
		while ($row = $stmt->fetch())
		{
			$forums[$row['id']] = $row['name'];
		}
		$this->output .= $this->html->table_top($ibforums->lang['top_delete_mirror'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);
		$this->output .= $this->html->mod_exp($ibforums->lang['delete_mirror_exp']);
		$this->output .= $this->html->delete_mirror_form($forums, $this->forum['name']);
		$this->output .= $this->html->end_form($ibforums->lang['submit_delete_mirror']);

		$this->page_title = $ibforums->lang['t_move'] . ": " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	/**     * ********************************************* */
	function do_delete_mirror_topic()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['move_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		//----------------------------------
		// Check for input..
		//----------------------------------

		if (!$ibforums->input['sf'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_source'
			             ));
		}

		$source      = intval($ibforums->input['sf']);
		$delete_from = $ibforums->input['delete_from'];

		/**
		 * Даже, если ничего не перенадо, то стоит пройтись. Если зеркал не осталось,
		 * то ф-ция topic_delete_mirrors() поправит флаг ibf_topics.has_mirror
		 */
		!$delete_from && $delete_from = array();

		$source_name = "";
		$dest_name   = "";

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_topics
			    WHERE tid='" . $ibforums->input['tid'] . "'");

		if (!$this->topic = $stmt->fetch())
		{

			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_forum'
			             ));
		}

		$ibforums->input['leave'] = $ibforums->input['leave'] == 'y'
			? 1
			: 0;

		$this->modfunc->topic_delete_mirrors($this->topic['tid'], $delete_from);

		$ibforums->input['t'] = $this->topic['tid'];

		$this->moderate_log("Mirror a topic from $source_name to $dest_name");

		$this->modfunc->forum_recount($source);

		$this->modfunc->forum_recount($moveto);

		$print->redirect_screen($ibforums->lang['p_moved'], "act=SF&f=" . $this->forum['id'] . "&st=" . $ibforums->input['st']);
	}

	function do_add_attach()
	{
		global $ibforums, $std, $print;

		if (!$ibforums->member['g_is_supmod'])
		{
			if (!$this->moderator['can_attach'])
			{
				$this->moderate_error();
			}
		}

		preg_match("/(\?|&amp;)(t|showtopic)=(\d+)($|&amp;)/", $ibforums->input['attach_link'], $match);

		// safe
		$id = intval(trim($match[3]));

		// safe
		$ibforums->input['tid'] = intval($ibforums->input['tid']);

		if (!$id)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'mt_no_topic'
			             ));
		}

		$stmt = $ibforums->db->query("SELECT tid
			    FROM ibf_topiclinks
			    WHERE
				tid='" . $ibforums->input['tid'] . "' and
				link='" . $id . "'");

		if ($stmt->rowCount())
		{

			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'link_already_exists'
			             ));
		}

		$ibforums->db->exec("INSERT INTO ibf_topiclinks
			    VALUES ('" . $ibforums->input['tid'] . "','" . $id . "')");

		$print->redirect_screen($ibforums->lang['p_moved'], "act=Mod&auth_key={$ibforums->input['auth_key']}&CODE=70&t=" . $ibforums->input['tid'] . "&f=" . $this->forum['id']);
	}

	function do_delete_attach()
	{
		global $ibforums, $std, $print;

		if ($ibforums->member['g_is_supmod'] != 1)
		{
			if ($this->moderator['can_attach'] == 0)
			{
				$this->moderate_error();
			}
		}

		if ($ibforums->input['linkid'] == "")
		{
			$this->Error(array('LEVEL' => 1, 'MSG' => 'mt_no_topic'));
		}

		// safe
		$ibforums->input['tid'] = intval($ibforums->input['tid']);
		// safe
		$ibforums->input['linkid'] = intval($ibforums->input['linkid']);

		$ibforums->db->exec("DELETE FROM ibf_topiclinks
			    WHERE
				tid='" . $this->topic['tid'] . "' and
				link='" . $ibforums->input['linkid'] . "'");

		$print->redirect_screen($ibforums->lang['p_moved'], "act=Mod&auth_key={$ibforums->input['auth_key']}&CODE=70&t=" . $this->topic['tid'] . "&f=" . $this->forum['id']);
	}

	function do_add_data()
	{
		global $ibforums, $std, $print;

		if ($ibforums->member['g_is_supmod'] != 1)
		{
			if ($this->moderator['can_attach'] == 0)
			{
				$this->moderate_error();
			}
		}

		// safe
		$ibforums->input['tid'] = intval($ibforums->input['tid']);

		$stmt = $ibforums->db->query("INSERT INTO ibf_topicsinfo
				(tid,name,link,date)
			    VALUES
				('" . $ibforums->input['tid'] . "','" . addslashes($ibforums->input['name']) . "','" . addslashes($ibforums->input['link']) . "','" . time() . "')");

		$print->redirect_screen($ibforums->lang['p_moved'], "act=Mod&auth_key={$ibforums->input['auth_key']}&CODE=70&t=" . $ibforums->input['tid'] . "&f=" . $this->forum['id']);
	}

	function do_delete_data()
	{
		global $ibforums, $std, $print;

		if ($ibforums->member['g_is_supmod'] != 1)
		{
			if ($this->moderator['can_attach'] == 0)
			{
				$this->moderate_error();
			}
		}

		if (!$ibforums->input['linkid'])
		{
			$this->Error(array('LEVEL' => 1, 'MSG' => 'mt_no_topic'));
		}

		// safe
		$ibforums->input['linkid'] = intval($ibforums->input['linkid']);

		$ibforums->db->exec("DELETE FROM ibf_topicsinfo
			    WHERE id='" . $ibforums->input['linkid'] . "'");

		$print->redirect_screen($ibforums->lang['p_moved'], "act=Mod&auth_key={$ibforums->input['auth_key']}&CODE=70&t=" . $this->topic['tid'] . "&f=" . $this->forum['id']);
	}

	function get_attached_links()
	{
		global $ibforums;

		// vot: BAD MESSAGE

		$html = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$this->base_url}' method='get' name='jumpmenu'>
		         <input type='hidden' name='act' value='Mod'>
                         <input type='hidden' name='auth_key' value='{$ibforums->input['auth_key']}'>
		         <input type='hidden' name='CODE' value='72'>
		         <input type='hidden' name='f' value='{$this->forum['id']}'>
		         <input type='hidden' name='t' value='{$this->topic['tid']}'>
			 <select name='linkid' class='forminput'>
		         <optgroup label=\"Выберите ссылку для удаления\">";

		$stmt = $ibforums->db->query("SELECT
				t.tid as topic_id,
				t.title,
				t.description
			    FROM
				ibf_topics t,
				ibf_topiclinks tl
			    WHERE
				tl.tid='" . $this->topic['tid'] . "' and
				t.tid=tl.link
			    ORDER BY t.tid");

		$i = 0;

		while ($r = $stmt->fetch())
		{
			$desc = ($r['description'])
				? " (" . $r['description'] . ")"
				: "";

			$html .= "<option value=\"{$r['topic_id']}\"" . $selected . ">&nbsp;&nbsp;[" . $r['topic_id'] . "] -> " . $r['title'] . $desc . "</option>\n";

			$i++;
		}

		$html .= "</optgroup>\n</select>&nbsp;<input type='submit' value='Удалить ссылку' ";
		if (!$i)
		{
			$html .= "disabled ";
		}

		$html .= "class='forminput'></form>";
		return $html;
	}

	function get_attached_data()
	{
		global $ibforums;

		// vot: BAD MESSAGE

		$html = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$this->base_url}' method='get' name='jumpmenu'>
		         <input type='hidden' name='act' value='Mod'>
                         <input type='hidden' name='auth_key' value='{$ibforums->input['auth_key']}'>
		         <input type='hidden' name='CODE' value='82'>
		         <input type='hidden' name='f' value='{$this->forum['id']}'>
		         <input type='hidden' name='t' value='{$this->topic['tid']}'>
			 <select name='linkid' class='forminput'>
		         <optgroup label=\"Выберите ссылку для удаления\">";

		$stmt = $ibforums->db->query("SELECT
				id,
				name,
				link
			    FROM ibf_topicsinfo
			    WHERE tid='" . $this->topic['tid'] . "'
			    ORDER BY date DESC");

		$i = 0;
		while ($r = $stmt->fetch())
		{
			$html .= "<option value=\"{$r['id']}\"" . $selected . ">&nbsp;&nbsp;" . $r['name'] . " (" . $r['link'] . ")</option>\n";
			$i++;
		}

		$html .= "</optgroup>\n</select>&nbsp;<input type='submit' value='Удалить ссылку' ";
		if (!$i)
		{
			$html .= "disabled ";
		}

		$html .= "class='forminput'></form>";
		return $html;
	}

	function attach_form()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['can_attach'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output .= $this->html->table_top($ibforums->lang['top_attach'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);
		$this->output .= $this->html->mod_exp($ibforums->lang['attach_exp']);
		$jump_html = $this->get_attached_links();
		$this->output .= $this->html->attach_form1($ibforums->lang['attach_edit'], $jump_html, $this->topic['title']);

		$this->output .= $this->html_start_form(array(
		                                             1 => array('CODE', '71'),
		                                             2 => array('tid', $this->topic['tid']),
		                                             3 => array('auth_key', $ibforums->input['auth_key']),
		                                        ));

		$this->output .= $this->html->attach_form2();
		$this->output .= $this->html->end_form($ibforums->lang['submit_attach']);

		$this->output .= "<br>";
		$this->output .= $this->html->table_top($ibforums->lang['data_exp']);
		$data_links = $this->get_attached_data();
		$this->output .= $this->html->attach_form1($ibforums->lang['data_edit'], $data_links, $this->topic['title']);
		$this->output .= $this->html_start_form(array(
		                                             1 => array('CODE', '81'),
		                                             2 => array('tid', $this->topic['tid']),
		                                             3 => array('auth_key', $ibforums->input['auth_key']),
		                                             4 => array('name', $ibforums->input['name']),
		                                             5 => array('link', $ibforums->input['link']),
		                                        ));
		$this->output .= $this->html->mod_exp($ibforums->lang['data_add']);
		$this->output .= $this->html->attach_form3();
		$this->output .= $this->html->end_form($ibforums->lang['submit_data_attach']);

		$this->page_title = $ibforums->lang['top_attach'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	/*	 * ********************************************** */

	function delete_post()
	{
		global $std, $ibforums, $print;

		// Get this post id.

		$sql = "SELECT
				pid,
				attach_file,
				author_id,
				attach_id,
				post_date,
				new_topic,
				has_modcomment
			    FROM ibf_posts
			    WHERE
				forum_id='" . $this->forum['id'] . "'
				AND topic_id='" . $this->topic['tid'] . "'
				AND pid='" . $ibforums->input['p'] . "'";

		$stmt = $ibforums->db->query($sql);

		if (!$post = $stmt->fetch())
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['delete_post'])
		{
			$passed = 1;
		} elseif ($ibforums->member['g_delete_own_posts'] and
		          $ibforums->member['id'] == $post['author_id'] and
		          !$post['has_modcomment']
		)
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		// Check to make sure that this isn't the first post in the topic..

		if ($post['new_topic'] == 1)
		{
			$this->moderate_error('no_delete_post');
		}

		//---------------------------------------
		// vot: delete the search words for this post
		//---------------------------------------

		$std->index_del_post($post['pid']);

		//---------------------------------------
		// delete the post
		//---------------------------------------
		$ibforums->db->exec("UPDATE ibf_posts SET use_sig = 2, edit_time='" . time() . "'
		    WHERE pid='" . $post['pid'] . "'");

		//---------------------------------------
		// Update the stats
		//---------------------------------------

		$ibforums->db->exec("UPDATE ibf_stats SET TOTAL_REPLIES=TOTAL_REPLIES-1");

		//---------------------------------------
		// Decrease the users post count
		//---------------------------------------

		if ($this->forum['inc_postcount'])
		{
			$ibforums->db->exec("UPDATE ibf_members
				    SET posts=posts-1
				    WHERE id='" . $post['author_id'] . "'");
		}

		//---------------------------------------
		// Get the latest post details
		//---------------------------------------
		$topic = topic::create_from_array($this->topic);
		$topic->update_last_post_time();

		$this->moderate_log("Deleted a post");

		if (!$ibforums->input['ajax'])
		{
			$print->redirect_screen($ibforums->lang['post_deleted'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
		} else
		{

			require ROOT_PATH . "/sources/Topics.php";
			header('Content-Type: text/html; charset=windows-1251');
			$row = $ibforums->db->query("
			SELECT
									p.*,
									m.id, m.name, m.mgroup, m.email, m.joined,
									m.gender,
									m.avatar, m.avatar_size, m.posts, m.aim_name,
									m.icq_number, m.signature,  m.website, m.yahoo,
									m.integ_msg, m.title, m.hide_email, m.msnname,
									m.warn_level, m.warn_lastwarn,
									m.points,  m.fined, m.rep, m.ratting, m.show_ratting,
									g.g_id, g.g_title, g.g_icon, g.g_use_signature,
									g.g_use_avatar, g.g_dohtml

								    FROM ibf_posts p
									  LEFT JOIN ibf_members m
										ON (p.author_id=m.id)
									  LEFT JOIN ibf_groups g
										ON (g.g_id=m.mgroup)
								    WHERE p.pid='" . $post['pid'] . "'")->fetch();

			$count = 1;
			$out   = (new Topics())->process_one_post($row, 0, $count, true);
			echo $print->prepare_output($out);
			die;
		}
	}

	/*	 * ********************************************** */

	// DELETE TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function delete_form()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['delete_topic'] == 1)
			{
				$passed = 1;
			} else
			{
				if ($this->topic['starter_id'] == $ibforums->member['id'])
				{
					if ($ibforums->member['g_delete_own_topics'] == 1)
					{
						$passed = 1;
					}
				} else
				{
					$passed = 0;
				}
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output = $this->html->delete_js();

		$this->output .= $this->html_start_form(array(
		                                             1 => array('CODE', '08'),
		                                             2 => array('t', $this->topic['tid'])
		                                        ));

		$this->output .= $this->html->table_top($ibforums->lang['top_delete'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);
		$this->output .= $this->html->mod_exp($ibforums->lang['delete_topic']);
		$this->output .= $this->html->end_form($ibforums->lang['submit_delete']);

		$this->page_title = $ibforums->lang['t_delete'] . ": " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	//-----------------------------------------------
	function delete_topic()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} elseif ($this->moderator['delete_topic'] == 1)
		{
			$passed = 1;
		} elseif ($this->topic['starter_id'] == $ibforums->member['id'])
		{
			if ($ibforums->member['g_delete_own_topics'] == 1)
			{
				$passed = 1;
			}
		} else
		{
			$passed = 0;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		// Do we have a linked topic to remove?
		$stmt = $ibforums->db->query("SELECT tid
			    FROM ibf_topics
			    WHERE
				state='link' AND
				moved_to LIKE '" . $this->topic['tid'] . '&' . $this->forum['id'] . "%'");

		if ($linked_topic = $stmt->fetch())
		{
			$ibforums->db->exec("DELETE
				    FROM ibf_topics
				    WHERE tid='" . $linked_topic['tid'] . "'");
		}

		$mirror_topics_q = $stmt = $ibforums->db->query('SELECT tid,forum_id FROM ibf_topics WHERE state=\'mirror\' AND mirrored_topic_id =' . $this->topic['tid']);

		$tmp_forum = $this->modfunc->forum;
		while ($row = $stmt->fetch($mirror_topics_q))
		{

			$this->modfunc->forum['id'] = $row['forum_id'];
			$this->modfunc->topic_delete($row['tid']);
		}
		$this->modfunc->forum = $tmp_forum;

		$this->modfunc->topic_delete($this->topic['tid']);

		//---------------------------------------
		// vot: delete the search words for this topic
		//---------------------------------------

		$std->index_del_topic($this->topic['tid']);

		//Jureth: forum_recount() was called in topic_delete()
		//Not needed		$this->modfunc->forum_recount_queue($this->forum['id']);

		$this->moderate_log("Deleted a topic");

		if (!$ibforums->input['ajax'])
		{
			$print->redirect_screen($ibforums->lang['p_deleted'], "act=SF&f=" . $this->forum['id']);
		} else
		{

		}
	}

	/*	 * ********************************************** */

	// EDIT TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function edit_form()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['edit_topic'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		$this->output = $this->html_start_form(array(
		                                            1 => array('CODE', '12'),
		                                            2 => array('t', $this->topic['tid'])
		                                       ));

		$this->output .= $this->html->table_top($ibforums->lang['top_edit'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);
		$this->output .= $this->html->mod_exp($ibforums->lang['edit_topic']);
		$this->output .= $this->html->topictitle_fields($this->topic['title'], $this->topic['description']);
		$this->output .= $this->html->end_form($ibforums->lang['submit_edit']);

		$this->page_title = $ibforums->lang['t_edit'] . ": " . $this->topic['title'];

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	function do_edit()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['edit_topic'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (empty($this->topic['tid']))
		{
			$this->moderate_error();
		}

		if (trim($ibforums->input['TopicTitle']) == "")
		{
			$this->Error(array(
			                  'LEVEL' => 2,
			                  'MSG'   => 'no_topic_title'
			             ));
		}

		$topic_title = preg_replace("/'/", "/\\'/", $ibforums->input['TopicTitle']);
		$topic_desc  = preg_replace("/'/", "/\\'/", $ibforums->input['TopicDesc']);

		$ibforums->db->exec("UPDATE
				ibf_topics
			    SET
				title='$topic_title',
				description='$topic_desc'
			    WHERE tid='" . $this->topic['tid'] . "'");

		//-------------------------------
		// vot: Reindex the topic title!
		//-------------------------------

		$std->index_reindex_title($this->topic['tid'], $this->forum['id'], $topic_title);

		if ($this->topic['tid'] == $this->forum['last_id'])
		{
			$ibforums->db->exec("UPDATE ibf_forums
				    SET last_title='$topic_title'
				    WHERE id='" . $this->forum['id'] . "'");
		}

		$this->moderate_log("Moderator edited a topic title: From '{$this->topic['tid']}' to '$topic_title'");

		$print->redirect_screen($ibforums->lang['p_edited'], "act=SF&f=" . $this->forum['id']);
	}

	/*	 * ********************************************** */

	// OPEN TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function open_topic()
	{
		global $std, $ibforums, $print;

		if ($this->topic['state'] == 'open')
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->topic['starter_id'] == $ibforums->member['id'])
			{
				if ($ibforums->member['g_open_close_posts'] == 1)
				{
					$passed = 1;
				}
			} else
			{
				$passed = 0;
			}
		}

		if ($this->moderator['open_topic'] == 1)
		{
			$passed = 1;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		$this->modfunc->topic_open($this->topic['tid']);

		$this->moderate_log("Opened Topic");

		$print->redirect_screen($ibforums->lang['p_opened'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	/*	 * ********************************************** */

	// CLOSE TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function close_topic()
	{
		global $std, $ibforums, $print;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->topic['starter_id'] == $ibforums->member['id'])
			{
				if ($ibforums->member['g_open_close_posts'] == 1)
				{
					$passed = 1;
				}
			} else
			{
				$passed = 0;
			}
		}

		if ($this->moderator['close_topic'] == 1)
		{
			$passed = 1;
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		$this->modfunc->topic_close($this->topic['tid']);

		$this->moderate_log("Locked Topic");

		$print->redirect_screen($ibforums->lang['p_closed'], "act=SF&f=" . $this->forum['id']);
	}

	/*	 * ********************************************** */

	// PIN TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function pin_topic()
	{
		global $std, $ibforums, $print;

		if ($this->topic['pinned'])
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['pin_topic'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		$this->modfunc->topic_pin($this->topic['tid']);

		$this->moderate_log("Pinned Topic");

		$print->redirect_screen($ibforums->lang['p_pinned'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	/*	 * ********************************************** */

	// UNPIN TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function unpin_topic()
	{
		global $std, $ibforums, $print;

		if (!$this->topic['pinned'])
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['unpin_topic'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		$this->modfunc->topic_unpin($this->topic['tid']);

		$this->moderate_log("Unpinned Topic");

		$print->redirect_screen($ibforums->lang['p_unpinned'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	// Song * show/hide topics
	/*	 * ********************************************** */
	// HIDE TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function hide_topic()
	{
		global $std, $ibforums, $print;

		if ($this->topic['hidden'])
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['hide_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		$this->modfunc->topic_hide($this->topic['tid']);

		$this->moderate_log("Hidden Topic");

		$print->redirect_screen($ibforums->lang['p_pinned'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	/*	 * ********************************************** */

	// SHOW TOPIC:
	// ---------------
	//
	/*	 * ********************************************** */

	function show_topic()
	{
		global $std, $ibforums, $print;

		if (!$this->topic['hidden'])
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['hide_topic'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		$this->modfunc->topic_show($this->topic['tid']);

		$this->moderate_log("Show Topic");

		$print->redirect_screen($ibforums->lang['p_pinned'], "act=ST&f=" . $this->forum['id'] . "&t=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	//+---------------------------------------------------------------------------------------------

	/*	 * ********************************************** */
	// MODERATE ERROR:
	// ---------------
	//
	// Function for error messages in this script
	//
	/*	 * ********************************************** */

	function moderate_error($msg = 'moderate_no_permission')
	{

		$this->Error(array('LEVEL' => 2, 'MSG' => $msg));

		// Make sure we exit..

		exit();
	}

	/*	 * ********************************************** */

	// MODERATE LOG:
	// ---------------
	//
	// Function for adding the mod action to the DB
	//
	/*	 * ********************************************** */

	function moderate_log($title = 'unknown')
	{
		global $std, $ibforums;

		$this->modfunc->add_moderate_log($ibforums->input['f'], $ibforums->input['t'], $ibforums->input['p'], $this->topic['title'], $title);
	}

	/*	 * ********************************************** */

	// Re Count topics for the forums:
	// ---------------
	//
	// Handles simple moderation functions, saves on
	// writing the same code over and over.
	// ASS_U_ME's that the requesting user has been
	// authenticated by this stage.
	//
	/*	 * ********************************************** */

	function recount($fid = "")
	{
		global $ibforums, $root_path, $std;

		if (!$fid)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_source'
			             ));
		}

		/* <--- Jureth ---: Double. Function are identical. */
		$this->modfunc->forum_recount($fid);
	}

	/*	 * ************************************************** */

	// HTML: start form.
	// ------------------
	// Returns the HTML for the <FORM> opening tag
	/*	 * ************************************************** */

	function html_start_form($additional_tags = array())
	{
		global $ibforums, $std;

		$form = "<form name='REPLIER' action='{$this->base_url}' method='POST'>" . "<input type='hidden' name='st' value='" . $ibforums->input['st'] . "' />" . "<input type='hidden' name='act' value='Mod' />" . "<input type='hidden' name='s' value='" . $ibforums->session_id . "' />" . "<input type='hidden' name='f' value='" . $this->forum['id'] . "' />" . "<input type='hidden' name='auth_key' value='" . $std->return_md5_check() . "' />";

		// Any other tags to add?

		if (isset($additional_tags))
		{
			foreach ($additional_tags as $k => $v)
			{
				$form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}'>";
			}
		}

		return $form;
	}

	//-------------------------------------
	function start_deleting()
	{
		global $std, $ibforums, $print;

		//------------------------------------------
		// Check for Moderator permissions
		//------------------------------------------

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['delete_post'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		//------------------------------------------
		// Colect the checkboxes status
		//------------------------------------------

		$idz = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^pozt(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$idz[] = $match[1];
				}
			}
		}

		if (count($idz) < 1)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'no_msg_checked'
			             ));
		}

		//------------------------------------------
		// Output the form "ARE YOU SHURE"?
		//------------------------------------------

		$this->output .= $this->html_start_form(array(
		                                             1 => array('CODE', '68'),
		                                             2 => array('t', $this->topic['tid']),
		                                             3 => array('pidz', implode(',', $idz))
		                                        ));

		$this->output .= $this->html->table_top($ibforums->lang['deleting_postz'] . ' "' . $this->topic['title'] . '"');
		$this->output .= $this->html->mod_exp($ibforums->lang['delete_postz']);
		$this->output .= $this->html->end_form($ibforums->lang['submit_delete_postz']);

		$this->page_title = $ibforums->lang['deleting_postz'] . ' "' . $this->topic['title'] . '"';

		$this->nav = array(
			"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
			"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
		);
	}

	//--------------------------------------------
	function pin_post()
	{
		global $std, $ibforums, $print;

		//------------------------------------------
		// Check for Moderator permissions
		//------------------------------------------

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['can_pin_post'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (!$this->topic['tid'])
		{
			$this->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		//------------------------------------------
		// Colect the checkboxes status
		//------------------------------------------

		$idz = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^pozt(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$idz[] = $match[1];
				}
			}
		}

		if (count($idz) < 1)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'no_msg_checked'
			             ));
		} elseif (count($idz) > 1)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'too_many_msg_checked'
			             ));
		}

		$idz = implode(",", $idz);

		$ibforums->db->exec("UPDATE ibf_topics
			    SET pinned_post='" . $idz . "'
			    WHERE tid='" . $this->topic['tid'] . "'");

		$print->redirect_screen($ibforums->lang['postz_moved'], "showtopic=" . $this->topic['tid']);
	}

	function unpin_post()
	{
		global $std, $ibforums, $print;

		//------------------------------------------
		// Check for Moderator permissions
		//------------------------------------------

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} else {
			if ($this->moderator['can_pin_post'] == 1)
			{
				$passed = 1;
			} else
			{
				$passed = 0;
			}
		}

		if ($passed != 1)
		{
			$this->moderate_error();
		}

		if (!$this->topic['tid'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'missing_files'
			             ));
		}

		$ibforums->db->exec("UPDATE ibf_topics
			    SET pinned_post=NULL
			    WHERE tid='" . $this->topic['tid'] . "'");

		$print->redirect_screen($ibforums->lang['postz_moved'], "showtopic=" . $this->topic['tid']);
	}

	//---------------------------
	function complete_deleting()
	{
		global $std, $ibforums, $print;

		//------------------------------------------
		// Check for Moderator permissions
		//------------------------------------------

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['delete_post'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		//-------------------------------------------
		// check for posts separated by commas
		//-------------------------------------------

		$ibforums->input['pidz'] = trim($ibforums->input['pidz']);

		if (!$ibforums->input['pidz'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'no_msg_checked'
			             ));
		}

		if (!preg_match("#(\d|,)#", $ibforums->input['pidz']))
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'no_msg_checked'
			             ));
		}

		$stmt = $ibforums->db->query("SELECT pid
			    FROM ibf_posts
			    WHERE
				pid IN ({$ibforums->input['pidz']})
				AND new_topic=1");

		if ($stmt->rowCount())
		{
			$this->moderate_error('no_delete_post');
		}

		$stmt = $ibforums->db->query("SELECT pid
			    FROM ibf_posts
			    WHERE
				pid IN ({$ibforums->input['pidz']})
				AND forum_id != {$this->forum['id']}");

		if ($stmt->rowCount())
		{
			$this->moderate_error();
		}

		//---------------------------------------
		// Start the removing
		//---------------------------------------
		// Remove the attached files
		$stmt = $ibforums->db->query("SELECT
				author_id,
				attach_id
			    FROM ibf_posts
			    WHERE pid IN ({$ibforums->input['pidz']})");

		$ids = array();
		while ($post = $stmt->fetch())
		{
			$ids[$post['author_id']]++;
		}

		// Decrease the members post counters
		if ($this->forum['inc_postcount'])
		{
			foreach ($ids as $mid => $count)
			{
				$ibforums->db->exec("UPDATE ibf_members
				    SET posts=posts-$count
				    WHERE id='" . $mid . "'");
			}
		}

		//---------------------------------------
		// Remove posts
		$data = [
			'use_sig'      => 1,
			'decline_time' => time(),
			'edit_name'    => $ibforums->db->quote($ibforums->member['name']),
			'append_edit'  => 1,
		];
		$stmt = $ibforums->db->updateRow("ibf_posts", $data, "pid IN ({$ibforums->input['pidz']})");

		//--------------------------------------------
		// vot: delete the search words for this post
		//--------------------------------------------

		$std->index_del_posts($ibforums->input['pidz']);

		//---------------------------------------
		// Recount the forum & the topis stats
		$idz = explode(',', $ibforums->input['pidz']);
		$idz = count($idz);

		$ibforums->db->exec("UPDATE ibf_stats
			    SET TOTAL_REPLIES=TOTAL_REPLIES-$idz");

		$this->recount_topic($this->topic['tid']);

		$this->recount($this->forum['id']);

		//Jureth: not needed		$this->modfunc->forum_recount_queue($this->forum['id']);

		$this->moderate_log("Deleted $idz posts");

		$print->redirect_screen("", "showtopic=" . $this->topic['tid'] . "&st=" . $ibforums->input['st']);
	}

	//---------------------------------------
	// Move the topics to new forum
	function complete_moving()
	{
		global $std, $ibforums, $print;

		//------------------------------------------
		// Check for Moderator permissions
		//------------------------------------------

		$passed = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$passed = 1;
		} elseif ($this->moderator['split_merge'] == 1)
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		//------------------------------------------
		// Check for Topic ID have to be moved is present
		//------------------------------------------

		if (!trim($ibforums->input['where2move']))
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'complete_form'
			             ));
		}

		//--------------------------------------------
		// Get the topic ID of the entered URL
		// (Ещё бы я это сам писал! Делать нечего! :Р)
		//--------------------------------------------

		preg_match("/(\?|&amp;)(t|showtopic)=(\d+)($|&amp;)/", $ibforums->input['where2move'], $match);

		$id = intval(trim($match[3]));

		if (!$id)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'mt_no_topic'
			             ));
		}

		if ($id == $this->topic['tid'])
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'mt_same_topic'
			             ));
		}

		$stmt = $ibforums->db->query("SELECT
				tid,
				forum_id,
				state
			    FROM ibf_topics
			    WHERE tid='" . $id . "'");

		$new = $stmt->fetch();

		if (!$new)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'move_no_topic'
			             ));
		} elseif ($new['state'] == "closed")
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'locked_topic'
			             ));
		}

		//------------------------------------------
		// Colect the checkboxes status
		//------------------------------------------

		$idz = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^pozt(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$idz[] = (int)$match[1];
				}
			}
		}

		if (count($idz) < 1)
		{
			$this->Error(array(
			                  'LEVEL' => 1,
			                  'MSG'   => 'no_msg_checked'
			             ));
		}

		$placeholders = IBPDO::placeholders($idz);

		$posts_count = $ibforums->db->
			prepare("SELECT count(*)
			    FROM ibf_posts
			    WHERE
				pid IN ({$placeholders}) AND
				new_topic=1")
			->execute($idz)
			->fetchColumn();

		if ($posts_count > 0)
		{
			$this->moderate_error('no_delete_post');
		}

		//------------------------------------------
		// Disable to the Moderator
		// to moderate not his forums
		//------------------------------------------

		$posts_count = $ibforums->db
			->prepare("SELECT count(*)
			    FROM ibf_posts
			    WHERE
				pid IN ({$placeholders}) AND
				forum_id != ?")
			->execute(array_merge($idz, [$this->forum['id']]))
			->fetchColumn();

		if ($posts_count > 0)
		{
			$this->moderate_error();
		}

		// Song * change code tag
		// vot: BAD QUERY: LIKE

		$posts = $ibforums->db
			->prepare("SELECT
					pid,
					forum_id,
					post
				   FROM ibf_posts
				   WHERE
					post LIKE '%[code%' and
					pid IN ({$placeholders})")
			->execute($idz);

		$update_query = $ibforums->db
			->prepare("UPDATE ibf_posts
				SET post=:post
				WHERE pid=:pid");
		foreach ($posts as $post)
		{
			if ($txt = preg_replace("#\[code\s*?(=\s*?(.*?)|)\s*\](.*?)\[/code\]#ies", "\$this->modfunc->regex_code_syntax('\\3', '\\2', " . $post['forum_id'] . ")", $post['post']) and
			    $txt != $post['post']
			)
			{
				$update_query
					->bindParam(':post', $txt, PDO::PARAM_STR)
					->bindParam(':pid', $post['pid'], PDO::PARAM_INT)
					->execute();
				$update_query->closeCursor();
			}
		}

		$moved_line = "[COLOR=gray][SIZE=0]" . $ibforums->lang['moved_post'] . "&quot;[URL={$this->base_url}showtopic={$this->topic['tid']}]{$this->topic['title']}[/URL]&quot;[/SIZE][/COLOR]";

		//------------------------------------------
		// Process our action
		//------------------------------------------
		// Move our posts

		$time = time();

		$ibforums->db->prepare("UPDATE ibf_posts
			    SET
				topic_id=?,
				forum_id=?,
				edit_time=?,
				post=Concat(post,'\n\n" . addslashes($moved_line) . "')
			    WHERE pid IN (" . $placeholders . ")")
			->execute(array_merge([$new['tid'], $new['forum_id'], $time], $idz));

		// Move the search words to the new Topis
		//todo: is that still needed?
		$ibforums->db->prepare("UPDATE
				ibf_search
			    SET
				fid=?,
				tid=?
			    WHERE
				pid IN (" . $placeholders . ")")
			->execute(array_merge([$new['forum_id'], $new['tid']], $idz));


		// Update New_Topic
		$ibforums->db->prepare("UPDATE ibf_posts
			    SET new_topic=0
			    WHERE topic_id=?")
			->execute([$new['tid']]);

		$first_post = $ibforums->db->prepare("SELECT
				pid,
				author_name,
				author_id,
				post_date
			    FROM ibf_posts
			    WHERE topic_id=?
			    ORDER BY post_date
			    LIMIT 0,1")
			->execute([$new['tid']])
			->fetch();

		if (!empty($first_post))
		{
			$ibforums->db->prepare("UPDATE ibf_posts
				    SET
					new_topic=1,
					delete_after=0
				    WHERE pid=?")
				->execute([$first_post['pid']]);
		}

		// Recount OLD & NEW topic stats
		$this->recount_topic($this->topic['tid']);
		$this->recount_topic($new['tid']);

		$this->recount($this->topic['forum_id']);
		//Jureth:not needed		$this->modfunc->forum_recount_queue($this->forum['id']);

		if ($this->forum['id'] != $new['forum_id'])
		{
			$this->recount($new['forum_id']);
			//Jureth:not needed			$this->modfunc->forum_recount_queue($new['forum_id']);
		}

		$print->redirect_screen("", "showtopic=$id&view=getlastpost");
	}

	//-------------------------------------
	function recount_topic($tid)
	{
		global $ibforums, $std;

		$stmt  = $ibforums->db->query("SELECT COUNT(pid) AS posts
			    FROM ibf_posts
			    WHERE topic_id='" . $tid . "' and queued != 1");
		$posts = $stmt->fetch();
		$posts = $posts['posts'] - 1;

		$stmt = $ibforums->db->query("SELECT
				post_date,
				author_id,
				author_name
			    FROM ibf_posts
			    WHERE
				topic_id='" . $tid . "'
				AND queued != 1
			    ORDER BY pid DESC
			    LIMIT 1");

		$last_post = $stmt->fetch();

		$ibforums->db->exec("UPDATE ibf_topics
			    SET
				last_post='" . $last_post['post_date'] . "',
				last_poster_id='" . $last_post['author_id'] . "',
				last_poster_name='" . $last_post['author_name'] . "',
				posts='" . $posts . "'
			    WHERE tid='" . $tid . "'");
	}

	// Song, Mixxx * js system, 06.05.05
	//-------------------------------------------
	function Error($error = array())
	{
		global $ibforums, $std;

		if (!$ibforums->input['linkID'])
		{
			$std->Error($error);
		} else
		{
			switch ($ibforums->input['CODE'])
			{
				case '18':
				case '19':
				case '28':
				case '29':
				case '33':
				case '34':
					$std->JsError($error);
					break;

				default:
					$std->Error($error);
					break;
			}
		}

		exit();
	}

	//-----------------------------------------------
	// Song * decline and restore post

	function decline_restore_post($state)
	{
		global $ibforums, $print, $std, $skin_universal;

		// Get this post id.
		$pid = $ibforums->input['p'];
		$tid = $ibforums->input['t'];
		$fid = $ibforums->input['f'];

		if (!$pid or !$tid or !$fid)
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['delete_post'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		$stmt = $ibforums->db->query("
	SELECT
			p.*,
			m.id, m.name, m.mgroup, m.email, m.joined,
			m.gender,
			m.avatar, m.avatar_size, m.posts, m.aim_name,
			m.icq_number, m.signature,  m.website, m.yahoo,
			m.integ_msg, m.title, m.hide_email, m.msnname,
			m.warn_level, m.warn_lastwarn,
			m.points,  m.fined, m.rep, m.ratting, m.show_ratting,
			g.g_id, g.g_title, g.g_icon, g.g_use_signature,
			g.g_use_avatar, g.g_dohtml

		    FROM ibf_posts p
			  LEFT JOIN ibf_members m
				ON (p.author_id=m.id)
			  LEFT JOIN ibf_groups g
				ON (g.g_id=m.mgroup)
		    WHERE p.pid='" . $pid . "'");

		$row = $stmt->fetch();

		if ($row['topic_id'] != $tid or $row['forum_id'] != $fid)
		{
			$this->moderate_error();
		}

		$mod = $state;

		if (!$state and $std->mod_tag_exists($row['post'], 1))
		{
			$mod = 1;
		}

		$ibforums->db->exec("UPDATE ibf_posts
		    SET
			use_sig='" . $state . "',
			has_modcomment='" . $mod . "',
			append_edit='" . $state . "',
			decline_time='" . time() . "',
			edit_name='" . $ibforums->member['name'] . "'
		    WHERE pid='" . $pid . "'");

		$row['use_sig']        = $state;
		$row['decline_time']   = time();
		$row['append_edit']    = $state;
		$row['edit_name']      = $ibforums->member['name'];
		$row['has_modcomment'] = $mod;

		$topic = topic::create_from_array($this->topic);
		$topic->update_last_post_time();

		if (!$ibforums->input['ajax'])
		{
			$print->redirect_screen("", "showtopic=" . $this->topic['tid'] . "&view=findpost&p=" . $pid, "html");
		} else
		{
			require ROOT_PATH . "/sources/Topics.php";

			header('Content-Type: text/html; charset=windows-1251');

			$count = 1;
			$out   = (new Topics())->process_one_post($row, 0, $count, true);
			echo $print->prepare_output($out);
			die;
		}
	}

	//--------------------------------------
	// Song * delayed delete posts, 13.04.05

	function delete_delayed($state)
	{
		global $ibforums, $print, $std;

		if (!$this->forum['days_off'])
		{
			$this->moderate_error('missing_files');
		}

		$passed = 0;

		$stmt = $ibforums->db->query("SELECT
			author_id,
			has_modcomment,
			new_topic,
			post
		    FROM ibf_posts
		    WHERE
			forum_id='" . $this->forum['id'] . "'
			AND topic_id='" . $this->topic['tid'] . "'
			AND pid='" . $ibforums->input['p'] . "'");

		if (!$post = $stmt->fetch())
		{
			$this->moderate_error();
		}

		if ($post['new_topic'])
		{
			$this->moderate_error('no_delete_post');
		}

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['delete_post'])
		{
			$passed = 1;
		} elseif ($ibforums->member['g_delay_delete_posts'] and
		          $ibforums->member['id'] == $post['author_id'] and
		          (!$state or !$std->mod_tag_exists($post['post'], 1, 0))
		)
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}
		if ($ibforums->input['ajax'] == 'on')
		{
			if ($ibforums->input['days'] == '0')
			{
				$state = 0;
			} else
			{
				$state = $std->delayed_time("", intval($ibforums->input['days']), 1);
			}
		} else
		{
			$state = ($state)
				? $std->delayed_time("", $this->forum['days_off'], 1)
				: 0;
		}

		$ibforums->db->exec("UPDATE ibf_posts
		    SET delete_after='" . $state . "'
		    WHERE pid='" . $ibforums->input['p'] . "'");

		if ($ibforums->input['ajax'] == 'on')
		{
			$ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);
			header('Content-type: text/html; charset=windows-1251');
			echo $std->get_autodelete_message($state, $ibforums->lang['delete_waiting_message'], $ibforums->lang['delete_through_message']);
			die;
		}
		if (!$ibforums->input['linkID'])
		{
			$print->redirect_screen("", "showtopic=" . $this->topic['tid'] . "&view=findpost&p=" . $ibforums->input['p'], "html");
		} else
		{
			$url = $ibforums->base_url . "act=Mod&CODE=" . (($state)
				? "29"
				: "28") . "&f={$this->forum['id']}&t={$this->topic['tid']}&p={$ibforums->input['p']}&auth_key=" . $std->return_md5_check();

			$print->redirect_js_screen("{$ibforums->input['linkID']}", (($state)
				? "post_delete2"
				: "post_delete1"), $url);
		}
	}

	//------------------------------------
	function some_delete_delayed()
	{
		global $ibforums, $print, $std;

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['delete_post'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed or !$ibforums->input['t'])
		{
			$this->moderate_error();
		}

		if (!$this->forum['days_off'])
		{
			$this->moderate_error('missing_files');
		}

		$source = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^pozt(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$source[] = $match[1];
				}
			}
		}

		if (!count($source))
		{
			$this->moderate_error('no_msg_checked');
		}

		$ids = array();

		$stmt = $ibforums->db->query("SELECT
			pid,
			new_topic,
			forum_id
		    FROM ibf_posts
		    WHERE pid IN (" . implode(",", $source) . ")");

		while ($post = $stmt->fetch())
		{
			if ($post['new_topic'] or $post['forum_id'] != $this->forum['id'])
			{
				continue;
			}

			$ids[] = $post['pid'];
		}

		if (!count($ids))
		{
			$this->moderate_error('no_msg_checked');
		}

		unset($source);

		$ibforums->db->exec("UPDATE ibf_posts
		    SET delete_after='" . $std->delayed_time("", $this->forum['days_off'], 1) . "'
		    WHERE pid IN (" . implode(",", $ids) . ")");

		unset($ids);

		$print->redirect_screen("", "showtopic={$this->topic['tid']}&st={$ibforums->input['st']}", "html");
	}

	//--------------------------------------
	// Song * decided topics, 20.04.05

	function decided_topics($state)
	{
		global $ibforums, $print, $std;

		if (!$this->forum['decided_button'])
		{
			$this->moderate_error('missing_files');
		}

		$passed = 0;

		$stmt = $ibforums->db->query("SELECT
			starter_id,
			approved,
			has_mirror
		    FROM ibf_topics
		    WHERE
			forum_id='" . $this->forum['id'] . "'
			AND tid='" . $this->topic['tid'] . "'");

		if (!$topic = $stmt->fetch() or !$topic['approved'])
		{
			$this->moderate_error();
		}

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['mid'])
		{
			$passed = 1;
		} elseif ($ibforums->member['g_use_decided'] and
		          $ibforums->member['id'] == $topic['starter_id']
		)
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		$ibforums->db->prepare("UPDATE ibf_topics SET decided = :state WHERE tid=:tid")->execute([
		                                                                                           ':state' => $state,
		                                                                                           ':tid'   => $this->topic['tid']
		                                                                                           ]);

		if ($topic['has_mirror'])
		{
			// update mirrors for this topic
			$ibforums->db->prepare("UPDATE ibf_topics SET decided = :state WHERE mirrored_topic_id=:tid")->execute([
			                                                                                                         ':state' => $state,
			                                                                                                         ':tid'   => $this->topic['tid']
			                                                                                                         ]);
		}

		if (!$ibforums->input['linkID'])
		{
			$print->redirect_screen("", "showtopic=" . $this->topic['tid'] . "&st=" . $ibforums->input['st'], "html");
		} else
		{
			if ($state)
			{
				$code = "34";

				$label = "solve2";
			} else
			{
				$code = "33";

				$label = "solve1";
			}

			$url = $ibforums->base_url . "act=Mod&CODE=" . $code . "&t={$this->topic['tid']}&f={$this->forum['id']}&auth_key=" . $std->return_md5_check();

			$print->redirect_js_screen(array('qsolveTop', 'qsolveBottom'), $label, $url);
		}
	}

	//----------------------------------
	// Song * add to faq, 02.05.05

	function do_add_to_faq($fid = 0)
	{
		global $ibforums, $std, $print;

		$fid = intval($fid);

		if (!$fid)
		{
			$this->moderate_error();
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['add_to_faq'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		$ids = array();

		if ($ibforums->input['p'])
		{
			$stmt = $ibforums->db->query("SELECT
				topic_id,
				forum_id
			    FROM ibf_posts
			    WHERE pid='" . $ibforums->input['p'] . "'");

			if (!$post = $stmt->fetch() or $post['forum_id'] != $this->forum['id'] or $post['topic_id'] != $this->topic['tid'])
			{
				$this->moderate_error();
			}

			$ids[] = $ibforums->input['p'];
		} else
		{
			$arr = array();

			foreach ($ibforums->input as $key => $value)
			{
				if (preg_match("/^pozt(\d+)$/", $key, $match))
				{
					if ($ibforums->input[$match[0]])
					{
						$arr[] = $match[1];
					}
				}
			}

			if (!count($arr))
			{
				$this->moderate_error();
			}

			$stmt = $ibforums->db->query("SELECT
				pid,
				topic_id,
				forum_id
			    FROM ibf_posts
			    WHERE pid IN (" . implode(",", $arr) . ")");

			while ($post = $stmt->fetch())
			{
				if ($post['topic_id'] == $this->topic['tid'] and $post['forum_id'] == $this->forum['id'])
				{
					$ids[] = $post['pid'];
				}
			}

			unset($arr);

			if (!count($ids))
			{
				$this->moderate_error();
			}
		}

		$stmt = $ibforums->db->query("SELECT
			sub_can_post,
			status
		    FROM ibf_forums
		    WHERE id='" . $fid . "'");

		if (!$forum = $stmt->fetch() or !$forum['sub_can_post'] or !$forum['status'])
		{
			$this->moderate_error('forum_read_only');
		}

		$time = time();

		// Insert NEW topic to the FAQ forum

		$topic = array(
			'title'            => $this->topic['title'],
			'description'      => $this->topic['description'],
			'state'            => "open",
			'posts'            => count($ids) - 1,
			'starter_id'       => $ibforums->member['id'],
			'starter_name'     => $ibforums->member['name'],
			'start_date'       => time(),
			'last_poster_id'   => $ibforums->member['id'],
			'last_poster_name' => $ibforums->member['name'],
			'last_post'        => $time,
			'icon_id'          => 0,
			'author_mode'      => 1,
			'poll_state'       => 0,
			'last_vote'        => 0,
			'views'            => 0,
			'forum_id'         => $fid,
			'approved'         => 1,
			'pinned'           => 0
		);

		$ibforums->db->insertRow("ibf_topics", $topic);

		$tid = $ibforums->db->lastInsertId();

		//-----------------------------------------------
		// vot: index new topic
		//-----------------------------------------------

		$std->index_reindex_title($tid, $fid, $this->topic['title']);

		// Insert the posts to the NEW topic in the FAQ forum

		$posts = $stmt = $ibforums->db->query("SELECT *
			     FROM ibf_posts
			     WHERE pid IN (" . implode(",", $ids) . ")
			     ORDER BY pid");

		while ($post = $stmt->fetch($posts))
		{
			$post = array(
				'author_id'   => $post['author_id'],
				'use_emo'     => $post['use_emo'],
				'ip_address'  => $post['ip_address'],
				'post_date'   => $time,
				'edit_time'   => $time,
				'icon_id'     => $post['icon_id'],
				'post'        => $post['post'],
				'author_name' => $post['author_name'],
				'forum_id'    => $fid,
				'topic_id'    => $tid,
				'queued'      => $post['queued'],
				'attach_id'   => $post['attach_id'],
				'attach_hits' => $post['attach_hits'],
				'attach_type' => $post['attach_type'],
				'new_topic'   => 0,
			);

			$stmt = $ibforums->db->insertRow("ibf_posts", $post);
			$pid  = $ibforums->db->lastInsertId();

			//-----------------------------------------------
			// vot: index new post
			// SOME IDEAS:
			//
			// may be, more usefull to copy old search words
			// to new posts ???
			//-----------------------------------------------

			$std->index_reindex_post($pid, $tid, $fid, $post['post']);
		}

		$stmt = $ibforums->db->query("SELECT min(pid) as min
		    FROM ibf_posts
		    WHERE topic_id='" . $tid . "'");

		if ($min = $stmt->fetch())
		{
			$ibforums->db->exec("UPDATE ibf_posts
			    SET new_topic=1
			    WHERE pid='" . $min['min'] . "'");
		}

		// Recount topic & posts in the target FAQ forum
		$this->recount($fid);

		$ibforums->db->exec("UPDATE ibf_stats
		    SET
			TOTAL_TOPICS=TOTAL_TOPICS+1,
			TOTAL_REPLIES=TOTAL_REPLIES+" . intval(count($ids) - 1));

		$ibforums->db->exec("UPDATE ibf_posts
		    SET added_to_faq=1
		    WHERE pid IN (" . implode(",", $ids) . ")");

		unset($ids);

		$ibforums->db->exec("INSERT INTO ibf_topiclinks
		    VALUES ('" . $this->topic['tid'] . "','" . $tid . "')");

		$ibforums->db->exec("INSERT INTO ibf_topiclinks
		    VALUES ('" . $tid . "','" . $this->topic['tid'] . "')");

		$print->redirect_screen("", "showtopic={$tid}", "html");
	}

	//---------------------------------------
	function add_to_faq()
	{
		global $ibforums, $std;

		if (!$this->forum['faq_id'])
		{
			$this->moderate_error('missing_files');
		}

		$passed = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$passed = 1;
		} elseif ($this->moderator['add_to_faq'])
		{
			$passed = 1;
		} else
		{
			$passed = 0;
		}

		if (!$passed)
		{
			$this->moderate_error();
		}

		$forums   = array();
		$children = array();

		$faq = $std->forums_array($this->forum['faq_id'], $this->forum, $forums, $children);

		if (count($faq))
		{
			$this->output .= $this->html->table_top($ibforums->lang['top_copy'] . " " . $this->forum['name'] . " &gt; " . $this->topic['title']);

			$this->output .= $this->html->mod_exp($ibforums->lang['copy_exp']);

			$forums = "<form name='forummenu' action='{$ibforums->base_url}' method='get'>\n";
			$forums .= "<input type='hidden' name='act' value='Mod'>\n";
			$forums .= "<input type='hidden' name='f' value='{$this->forum['id']}'>\n";
			$forums .= "<input type='hidden' name='t' value='{$this->topic['tid']}'>\n";
			$forums .= "<input type='hidden' name='auth_key' value='" . $std->return_md5_check() . "'>\n";
			$forums .= "<input type='hidden' name='CODE' value='36'>\n";

			if ($ibforums->input['p'])
			{
				$forums .= "<input type='hidden' name='p' value='{$ibforums->input['p']}'>\n";
			} else
			{
				foreach ($ibforums->input as $key => $value)
				{
					if (preg_match("/^pozt(\d+)$/", $key, $match))
					{
						if ($ibforums->input[$match[0]])
						{
							$forums .= "<input type='hidden' name='{$match[0]}' value='1'>\n";
						}
					}
				}
			}

			$forums .= "<select name='forum_id' class='forminput'>\n";

			foreach ($faq as $row)
			{
				if ($row['parent_id'] == -1 and !$row['sub_can_post'])
				{
					continue;
				}

				$forums .= $std->menu_row($row['id'], 0, $row['name']);
			}

			$forums .= "</select>";

			$this->output .= $this->html->move_form2($forums, $this->forum['name']);

			$this->output .= $this->html->end_form($ibforums->lang['jmp_go']);

			$this->page_title = $ibforums->lang['t_copy'] . ": " . $this->topic['title'];

			$this->nav = array(
				"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
				"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
			);
		} else
		{
			$this->do_add_to_faq($this->forum['faq_id']);
		}
	}

}
