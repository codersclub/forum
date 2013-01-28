<?php
/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@ibforums.com
|   Licence Info: http://www.invisionpower.com
+---------------------------------------------------------------------------
|
|   > Moderator Core Functions
|   > Module written by Matt Mecham
|
+--------------------------------------------------------------------------
| NOTE:
| This module does not do any access/permission checks, it merely
| does what is asked and returns - see function for more info
+--------------------------------------------------------------------------
*/

class modfunctions
{
	//------------------------------------------------------
	// @modfunctions: constructor
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE)
	//------------------------------------------------------

	var $topic = "";
	var $forum = "";
	var $error = "";

	var $auto_update = FALSE;

	var $stm = "";
	var $upload_dir = "";

	var $moderator = "";
	var $parser = "";
	var $last_id = 0;
	var $cache_syntax = array();

	function modfunctions()
	{
		global $ibforums;

		$this->error = "";

		$this->upload_dir = $ibforums->vars['upload_dir'];

		return TRUE;
	}

	//------------------------------------------------------
	// @init: initialize module (allows us to create new obj)
	// -----------
	// Accepts: References to @$forum [ @$topic , @$moderator ]
	// Returns: NOTHING (TRUE)
	//------------------------------------------------------

	function init($forum, $topic = "", $moderator = "")
	{
		$this->forum = $forum;

		if (is_array($topic))
		{
			$this->topic = $topic;
		}

		if (is_array($moderator))
		{
			$this->moderator = $moderator;
		}

		return TRUE;
	}

	//------------------------------------------------------
	// @topic_add_reply: Appends topic with reply
	// -----------
	// Accepts: $post, $tids = array( 'tid', 'forumid' );
	//
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_add_reply($post = "", $tids = array(), $incpost = 0)
	{
		global $std, $ibforums;

		if ($post == "")
		{
			return FALSE;
		}

		if (count($tids) < 1)
		{
			return FALSE;
		}

		$post = array(
			'author_id'    => $ibforums->member['id'],
			'use_emo'      => 1,
			'ip_address'   => $ibforums->input['IP_ADDRESS'],
			'post_date'    => time(),
			'icon_id'      => 0,
			'post'         => $post,
			'author_name'  => $ibforums->member['name'],
			'forum_id'     => "",
			'topic_id'     => "",
			'queued'       => 0,
			'attach_id'    => "",
			'attach_hits'  => "",
			'attach_type'  => "",
			'delete_after' => $std->delayed_time($post, $this->forum['days_off'], 0, $this->moderator),
		);

		//-------------------------------------
		// Add posts...
		//-------------------------------------

		$seen_fids = array();
		$add_posts = 0;

		foreach ($tids as $row)
		{
			$tid = intval($row[0]);
			$fid = intval($row[1]);
			$pa  = array();
			$ta  = array();

			if (!in_array($fid, $seen_fids))
			{
				$seen_fids[] = $fid;
			}

			if ($tid and $fid)
			{
				$pa             = $post;
				$pa['forum_id'] = $fid;
				$pa['topic_id'] = $tid;

				$ibforums->db->exec("ibf_posts", $pa);

				$this->last_id = $ibforums->db->lastInsertId();

				$ta = array(
					'last_poster_id'   => $ibforums->db->quote($ibforums->member['id']),
					'last_poster_name' => $ibforums->db->quote($ibforums->member['name']),
					'last_post'        => $ibforums->db->quote($pa['post_date']),
					'posts'            => 'posts+1',
				);

				$ibforums->db->updateRow("ibf_topics", $ta, "tid=$tid");

				$add_posts++;
			}
		}

		if ($this->auto_update != FALSE)
		{
			if (count($seen_fids) > 0)
			{
				foreach ($seen_fids as $id)
				{
					$this->forum_recount($id);
				}
			}
		}

		if ($add_posts > 0)
		{
			$ibforums->db->exec("UPDATE ibf_stats
				    SET TOTAL_REPLIES=TOTAL_REPLIES+" . $add_posts);

			//-------------------------------------------------
			// Update current members stuff
			//-------------------------------------------------

			$pcount = "";
			$mgroup = "";

			if (($this->forum['inc_postcount']) and ($incpost != 0))
			{
				//------------------------------------
				// Increment the users post count
				//------------------------------------

				$pcount = "posts=posts+" . $add_posts . ", ";
			}

			//------------------------------------
			// Are we checking for auto promotion?
			//------------------------------------

			if ($ibforums->member['g_promotion'] != '-1&-1' and !$ibforums->member['disable_group'])
			{
				list($gid, $gposts) = explode('&', $ibforums->member['g_promotion']);

				if ($gid > 0 and $gposts > 0)
				{
					if ($ibforums->member['posts'] + $add_posts >= $gposts and !$ibforums->member['warn_level'])
					{
						$mgroup = "mgroup='$gid',old_group='$gid',";
					}
				}
			}

			$ibforums->db->exec("UPDATE ibf_members
				    SET
					$pcount
					$mgroup
					last_post=" . time() . "
				    WHERE id=" . $ibforums->member['id']);

		}

		return TRUE;

	}

	// Song * show/hide topics
	//------------------------------------------------------
	// @topic_hide: hide topic ID's
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_hide($id)
	{
		global $ibforums;

		$this->stm_init();
		$this->stm_add_hide();
		$this->stm_exec($id);
	}

	// Song * show/hide topics
	//------------------------------------------------------
	// @topic_show: show topic ID's
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_show($id)
	{
		global $ibforums;

		$this->stm_init();
		$this->stm_add_show();
		$this->stm_exec($id);
	}

	//------------------------------------------------------
	// @topic_close: close topic ID's
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_close($id)
	{
		global $ibforums;

		$this->stm_init();
		$this->stm_add_close();
		$this->stm_exec($id);
	}

	//------------------------------------------------------
	// @topic_open: open topic ID's
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_open($id)
	{
		global $ibforums;

		$this->stm_init();
		$this->stm_add_open();
		$this->stm_exec($id);
	}

	//------------------------------------------------------
	// @topic_pin: pin topic ID's
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_pin($id)
	{
		global $ibforums;

		$this->stm_init();
		$this->stm_add_pin();
		$this->stm_exec($id);
	}

	//------------------------------------------------------
	// @topic_unpin: unpin topic ID's
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_unpin($id)
	{
		global $ibforums;

		$this->stm_init();
		$this->stm_add_unpin();
		$this->stm_exec($id);
	}

	//------------------------------------------------------
	// @topic_delete: deletetopic ID(s)
	// -----------
	// Accepts: $ftid (array (topic_id=>forum_id) | topic_id as string )
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function topic_delete($ftid)
	{
		global $sess, $std, $ibforums;

		$this->error = "";

		if (is_array($ftid))
		{
			if (count($ftid))
			{
				$tid = " IN (" . implode(",", array_keys($ftid)) . ")";
			} else
			{
				return FALSE;
			}
		} else
		{
			if (intval($ftid))
			{
				$tid = "=$ftid";
			} else
			{
				return FALSE;
			}
		}

		// mark topics deleted
		//		$ibforums->db->exec("UPDATE ibf_topics SET approved=0, deleted=1 WHERE tid".$tid);

		$ibforums->db->exec("DELETE FROM ibf_topics
			    WHERE tid" . $tid);

		//------------------------------------
		// Remove polls assigned to this topic
		//------------------------------------

		$ibforums->db->exec("DELETE FROM ibf_polls
			    WHERE tid" . $tid);

		//------------------------------------
		// Remove poll voters assigned to this poll/topic
		//------------------------------------

		$ibforums->db->exec("DELETE FROM ibf_voters
			    WHERE tid" . $tid);

		//------------------------------------
		// Remove polls assigned to this topic
		//------------------------------------

		// vot: BAD QUERY: post NOT LIKE

		$stmt = $ibforums->db->query("SELECT p.author_id
			    FROM
				ibf_posts p,
				ibf_forums f
			    WHERE
				p.topic_id" . $tid . " AND
				p.post NOT LIKE '[MOD]Тема перенесена из%' AND
				p.post NOT LIKE '[MOD]Topic moved here from%' AND
				p.forum_id=f.id AND
				f.inc_postcount=1 AND
				p.author_id<>0");

		if ($stmt->rowCount())
		{
			$ids = array();

			while ($row = $stmt->fetch())
			{
				$ids[$row['author_id']]++;
			}

			foreach ($ids as $mid => $count)
			{
				$ibforums->db->exec("UPDATE ibf_members
					    SET posts=posts-$count
					    WHERE id='" . $mid . "'");
			}
		}

		$ibforums->db->exec("DELETE FROM ibf_log_topics
			    WHERE tid" . $tid);

		$ibforums->db->exec("DELETE FROM ibf_topiclinks
			    WHERE tid" . $tid);

		$ibforums->db->exec("DELETE FROM ibf_topiclinks
			    WHERE link" . $tid);

		$ibforums->db->exec("DELETE FROM ibf_topicsinfo
			    WHERE tid" . $tid);

		//------------------------------------
		// Remove the attachments
		//------------------------------------

		$stmt = $ibforums->db->query("SELECT attach_id
			    FROM ibf_posts
			    WHERE
				attach_id <> ''
				AND topic_id" . $tid);

		if ($stmt->rowCount())
		{
			while ($r = $stmt->fetch())
			{
				/*
				if ( is_file($this->upload_dir."/".$r['attach_id']) )
				{
					@unlink($this->upload_dir."/".$r['attach_id']);
				}
				*/
				Attachment::deleteAllPostAttachments($r);
			}
		}

		//------------------------------------
		// vot:
		// Remove search words for this topic
		//------------------------------------

		$std->index_del_topics($tid);

		//		$ibforums->db->exec("DELETE
		//			    FROM ibf_search
		//			    WHERE tid".$tid);

		//------------------------------------
		// Remove the posts
		//------------------------------------

		$ibforums->db->exec("DELETE
			    FROM ibf_posts
			    WHERE topic_id" . $tid);

		//------------------------------------
		// Recount forum...
		//------------------------------------

		if (is_array($ftid))
		{
			foreach ($ftid as $fid)
			{
				$this->forum_recount($fid);
			}
		} elseif ($this->forum['id'])
		{
			$this->forum_recount($this->forum['id']);
		}

		$this->stats_recount();

	}

	//--------------------------------------------------
	// Song * leave message within transfering or mirroring topic

	function leave_message($source, $moveto, $topic, $is_mirror = false)
	{
		global $ibforums, $std;

		!is_array($source) && $source = array($source);

		$forum_links = array();

		foreach ($source as $current_forum_id)
		{
			$info = $this->get_forum_name_by_id($current_forum_id);
			if (isset($info['parent']) && $is_mirror == false)
			{
				$forum_links[] = "[URL={$info['url']}]{$info['parent']} => {$info['name']}[/URL]";
			} else
			{
				$forum_links[] = "[URL={$info['url']}]{$info['name']}[/URL]";
			}
		}
		$post_text = "[MOD]" . ($is_mirror
			? $ibforums->lang['topic_mirrored']
			: $ibforums->lang['topic_moved']);
		$post_text .= " " . implode(', ', $forum_links);
		$post_text .= "[/MOD]";

		require_once ROOT_PATH . "sources/lib/post_parser.php";

		$this->parser = new post_parser();

		$post = array(
			'author_id'    => $ibforums->member['id'],
			'use_emo'      => 0,
			'ip_address'   => $ibforums->input['IP_ADDRESS'],
			'post_date'    => time(),
			'icon_id'      => $ibforums->input['iconid'],
			'post'         => $this->parser->convert(array(
			                                              TEXT     => $post_text,
			                                              SMILIES  => 0,
			                                              CODE     => 0,
			                                              HTML     => 0,
			                                              MOD_FLAG => True,
			                                         )),
			'author_name'  => $ibforums->member['name'],
			'forum_id'     => $moveto,
			'topic_id'     => $topic,
			'queued'       => 0,
			'attach_id'    => "",
			'attach_hits'  => "",
			'attach_type'  => "",
			'delete_after' => $std->delayed_time("", $source_forum['days_off'], 1),
		);

		$ibforums->db->insertRow("ibf_posts", $post);

	}

	/**
	 *
	 * @param integer $forum_id
	 * @return array('name' => string, 'url' => string) or false if not found
	 */
	function get_forum_name_by_id($forum_id)
	{

		global $ibforums, $std;

		settype($forum_id, 'integer');
		$stmt = $ibforums->db->query("SELECT
				name,
				parent_id,
				days_off
			    FROM ibf_forums
			    WHERE id='" . $forum_id . "'");

		if ($source_forum = $stmt->fetch())
		{
			if ($source_forum['parent_id'] == -1)
			{
				return array(
					'name' => $source_forum['name'],
					'url'  => "{$ibforums->base_url}showforum={$forum_id}"
				);

			} else
			{
				$stmt = $ibforums->db->query("SELECT name
					    FROM ibf_forums
					    WHERE id=" . $source_forum['parent_id']);

				if ($source_parent_forum = $stmt->fetch())
				{
					return array(
						'name'   => $source_forum['name'],
						'parent' => $source_parent_forum['name'],
						'url'    => "{$ibforums->base_url}showforum={$forum_id}"
					);
				}
			}
		}
		return false;

	}

	// vot: BAD - strange function. WHY TO CHANGE the code tag???

	//--------------------------------------------------
	// Song * change CODE tag within moving

	function syntax($code, $syntax)
	{
		return "[CODE=" . $syntax . "]" . $code . "[/CODE]";
	}

	//--------------------------------------------------
	function error($code)
	{
		return "[CODE]" . $code . "[/CODE]";
	}

	//--------------------------------------------------
	function regex_code_syntax($code, $syntax, $id)
	{
		global $std;
		$ibforums = Ibf::instance();

		if ($syntax)
		{
			return $this->syntax($code, $syntax);
		}

		if (!$id)
		{
			return $this->error($code);
		}

		if (!$this->cache_syntax[$id])
		{
			$hid = $std->get_highlight_id($id);

			if (!$hid)
			{
				return $this->error($code);
			}

			$stmt = $ibforums->db->query("SELECT syntax
			    FROM ibf_syntax_list
			    WHERE id='" . $hid . "'");

			if (!$syntax = $stmt->fetch())
			{
				return $this->error($code);
			}

			$this->cache_syntax[$id] = $syntax['syntax'];
		}

		return $this->syntax($code, $this->cache_syntax[$id]);

	}

	//------------------------------------------------------
	// @topic_move: move topic ID(s)
	// -----------
	// Accepts: $topics (array | string) $source,
	//          $moveto
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------
	//
	// Jureth: Multisource patch. $source now is ( array | string )
	// Arrays must be in form $topic_id=>$forum_id
	//------------------------------------------------------

	function topic_move($topics, $source, $moveto, $leavelink = 0)
	{
		global $std, $ibforums;

		$this->error = "";

		$moveto = intval($moveto); //Jureth

		if (is_array($topics))
		{
			//Jureth
			if (!is_array($source))
			{
				$source = array_fill_keys($topics, $source);
			}
			$sourcequery = " IN(" . implode(",", $source) . ")";

			if (count($topics) > 0)
			{
				$tid = " IN(" . implode(",", $topics) . ")";
			} else
			{
				return FALSE;
			}
		} else
		{
			//Jureth
			if (!is_array($source))
			{
				//Jureth				$source[$topics] = $source;
				$source = array($topics => $source); //Jureth: fucked 5 version! (c)not me!!!!
			}
			$sourcequery = "=" . $source[$topics];

			if (intval($topics))
			{
				$tid = "=$topics";
			} else
			{
				return FALSE;
			}
		}

		// Song * permit activation topic if the destination forum is
		// moderated within topics, 06.04.2005

		$preview = "";

		$stmt = $ibforums->db->query("SELECT preview_posts
			    FROM ibf_forums
			    WHERE id='" . $moveto . "'");

		$moderate = $stmt->fetch();

		if ($moderate['preview_posts'] and
		    ($moderate['preview_posts'] == 1 or
		     $moderate['preview_posts'] == 2)
		)
		{
			$preview = ", approved=0";
		}

		//----------------------------------
		// Update the topic
		//----------------------------------

		$sql = "UPDATE ibf_topics
			    SET forum_id=" . $moveto . $preview . "
			    WHERE
				forum_id" . $sourcequery . "
				AND tid" . $tid;

		$ibforums->db->query($sql);

		//----------------------------------
		// Update the posts
		//----------------------------------

		// Song * change code tag

		// vot: BAD QUERY: LIKE

		$stmt = $ibforums->db->query("SELECT
					pid,
					post,
					topic_id
				   FROM ibf_posts
				   WHERE
					post LIKE '%[code%'
					AND forum_id" . $sourcequery . "
					AND topic_id" . $tid);

		while ($post = $stmt->fetch())
		{
			if ($txt = preg_replace("#\[code\s*?(=\s*?(.*?)|)\s*\](.*?)\[/code\]#ies", //Jureth					 "\$this->regex_code_syntax('\\3', '\\2', ".$source.")", $post['post']) and
				"\$this->regex_code_syntax('\\3', '\\2', " . $source[$post['topic_id']] . ")", $post['post']) and //Jureth
			    $txt != $post['post']
			)
			{
				$ibforums->db->exec("UPDATE ibf_posts
					    SET post='" . addslashes($txt) . "'
					    WHERE pid='" . $post['pid'] . "'");
			}
		}

		//----------------------------------
		// Move posts to new forum
		//----------------------------------

		$ibforums->db->exec("UPDATE ibf_posts
			    SET forum_id=$moveto
			    WHERE
				forum_id" . $sourcequery . "
				AND topic_id" . $tid);

		//==================================
		//----------------------------------
		// vot: Indexed search
		// Move search words to new forum
		//----------------------------------

		$std->index_move_topics($tid, $moveto);

		//----------------------------------
		// Update the polls
		//----------------------------------

		$ibforums->db->exec("UPDATE ibf_polls
			    SET forum_id=$moveto
			    WHERE
				forum_id" . $sourcequery . "
				AND tid" . $tid);

		//----------------------------------
		// Are we leaving a stink er link?
		//----------------------------------

		if ($leavelink)
		{
			$move_string = "";

			$stmt = $ibforums->db->query("SELECT
					name,
					parent_id
				    FROM ibf_forums
				    WHERE id=" . $moveto);

			if ($moved_forum = $stmt->fetch())
			{
				$moved_forum['name'] = str_replace('&#33;', '!', $moved_forum['name']);

				$move_string = "&" . $moved_forum['name'] . "&" . $ibforums->member['id'] . "&" . $ibforums->member['name'];

				if ($moved_forum['parent_id'] != -1)
				{
					$stmt = $ibforums->db->query("SELECT name
						    FROM ibf_forums
						    WHERE id=" . $moved_forum['parent_id']);

					if ($moved_forum = $stmt->fetch())
					{
						$move_string .= "&" . $moved_forum['name'];
					}
				}
			}

			$stmt = $ibforums->db->query("SELECT *
					  FROM ibf_topics
					  WHERE tid" . $tid);

			while ($row = $stmt->fetch())
			{
				$data = [
					'title'            => $row['title'],
					'description'      => $row['description'],
					'state'            => 'link',
					'posts'            => 0,
					'views'            => 0,
					'starter_id'       => $row['starter_id'],
					'start_date'       => $row['start_date'],
					'starter_name'     => $row['starter_name'],
					'last_post'        => $row['last_post'],
					'forum_id'         => $source[$row['tid']], //Jureth
					'approved'         => 1,
					'pinned'           => 0,
					'moved_to'         => $row['tid'] . '&' . $moveto . $move_string,
					'last_poster_id'   => $row['last_poster_id'],
					'last_poster_name' => $row['last_poster_name'],
					'link_time'        => time(),
					'club'             => $row['club'],
				];

				// Make a link to moved topic

				$ibforums->db->insertRow("ibf_topics", $data);
			}

			if (is_array($topics))
			{
				foreach ($topics as $topic)
				{
					$this->leave_message($source[$topic], $moveto, $topic); //Jureth
				}
			} //Jureth			else $this->leave_message($source[$topic],$moveto,$topics); //Jureth
			else
			{
				$this->leave_message($source[$topics], $moveto, $topics);
			} //Jureth
		}

		// Song * NEW
		// change fid id for transfering topic
		$ibforums->db->exec("UPDATE ibf_log_topics
			    SET
				fid=$moveto,
				logTime='" . time() . "'
			    WHERE tid" . $tid);

		$std->song_set_forumread($moveto);

		//----------------------------------
		// Sort out subscriptions
		//----------------------------------

		$stmt = $ibforums->db->query("SELECT
				tr.*,
				m.id,
				m.mgroup,
				m.org_perm_id,
				f.read_perms,
				f.id,
				t.tid,
				g.g_id,
				g.g_perm_id
			    FROM ibf_tracker tr
			    LEFT JOIN ibf_topics t
				ON (tr.topic_id=t.tid)
			    LEFT JOIN ibf_forums f
				ON (t.forum_id=f.id)
			    LEFT JOIN ibf_members m
				ON (m.id=tr.member_id)
			    LEFT JOIN ibf_groups g
				ON (g.g_id=m.mgroup)
			    WHERE tr.topic_id" . $tid);

		$trid_to_delete = array();

		while ($r = $stmt->fetch())
		{
			//----------------------------------------
			// Match the perm group against forum_mask
			//----------------------------------------

			$perm_id = $r['g_perm_id'];

			if ($r['org_perm_id'])
			{
				$perm_id = $r['org_perm_id'];
			}

			$pass = 0;

			$forum_perm_array = explode(",", $r['read_perms']);

			foreach (explode(',', $perm_id) as $u_id)
			{
				if (in_array($u_id, $forum_perm_array))
				{
					$pass = 1;
				}
			}

			if ($pass != 1)
			{
				$trid_to_delete[] = $r['trid'];
			}
		}

		if (count($trid_to_delete) > 0)
		{
			$ibforums->db->exec("DELETE
				    FROM ibf_tracker
				    WHERE trid IN(" . implode(',', $trid_to_delete) . ")");
		}

		return TRUE;

	}

	//------------------------------------------------------
	// @topic_move: move topic ID(s)
	// -----------
	// Accepts: $topics (array | string) $source,
	//          $moveto
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------
	//
	// Jureth: Multisource patch. $source now is ( array | string )
	// Arrays must be in form $topic_id=>$forum_id
	//------------------------------------------------------

	function topic_mirror_to_another_forum($topics, $source, $moveto, $leavelink = 0)
	{
		global $std, $ibforums;

		$this->error = "";

		!is_array($moveto) && $moveto = array($moveto);

		foreach ($moveto as $i => $v)
		{
			$moveto[$i] = intval($v);
		}
		!is_array($topics) && $topics = array($topics);

		/*
		 * убрать из массива дубликаты, чтобы не создавать несколько зеркал в одном разделе
		 */
		$moveto = array_unique($moveto);

		if (count($topics) > 0)
		{
			$tid = " IN(" . implode(",", $topics) . ")";
		} else
		{
			return FALSE;
		}

		foreach ($moveto as $current_move_forum)
		{

			$stmt = $ibforums->db->query("SELECT t.*,
							f.name as forum_name,
							f.parent_id as parent_forum_id
						  FROM ibf_topics t
						  JOIN ibf_forums f ON (t.forum_id = f.id)
						  WHERE tid $tid
						  ");
			while ($current_topic = $stmt->fetch())
			{
				if ($current_topic['forum_id'] == $current_move_forum)
				{
					// не создавать зеркало в том же разделе, где находится топик
					continue 2;
				}
				$stmt = $ibforums->db->query("SELECT * FROM ibf_topics
							WHERE mirrored_topic_id = {$current_topic['tid']} AND
								 forum_id = {$current_move_forum}
							LIMIT 1");
				if ($stmt->fetch())
				{
					// в этом разделе уже есть зеркало на эту тему
					continue 2;
				}
				$source_forum['name'] = str_replace('&#33;', '!', $current_topic['forum_name']);

				$move_string = "&" . $source_forum['name'] . "&" . $ibforums->member['id'] . "&" . $ibforums->member['name'];

				if ($current_topic['parent_forum_id'] != -1)
				{
					$stmt = $ibforums->db->query("SELECT name
							    FROM ibf_forums
							    WHERE id=" . $current_topic['parent_forum_id']);

					if ($source_forum = $stmt->fetch())
					{
						$move_string .= "&" . $source_forum['name'];
					}
				}

				$data = [
					'title'             => $current_topic['title'],
					'description'       => $current_topic['description'],
					'state'             => 'mirror',
					'posts'             => $current_topic['posts'],
					'views'             => 0,
					'starter_id'        => $current_topic['starter_id'],
					'start_date'        => $current_topic['start_date'],
					'starter_name'      => $current_topic['starter_name'],
					'last_post'         => $current_topic['last_post'],
					'forum_id'          => $current_move_forum,
					'approved'          => 1,
					'pinned'            => 0,
					'moved_to'          => $current_topic['tid'] . '&' . $current_topic['forum_id'] . $move_string,
					'last_poster_id'    => $current_topic['last_poster_id'],
					'last_poster_name'  => $current_topic['last_poster_name'],
					'link_time'         => time(),
					'club'              => $current_topic['club'],
					'mirrored_topic_id' => $current_topic['tid'],
					'icon_id'           => $current_topic['icon_id'],
					'decided'           => $current_topic['decided'],
				];
				// Make a link to moved topic

				$ibforums->db->insertRow("ibf_topics", $data);

				$current_topic_id = $ibforums->db->lastInsertId();

				$ibforums->db->exec("UPDATE ibf_forums
					  SET
					    last_title='" . $current_topic['title'] . "',
					    last_id='" . $current_topic_id . "',
					    last_post='" . time() . "',
					    last_poster_name='" . $current_topic['last_poster_name'] . "',
					    last_poster_id='" . $current_topic['last_poster_id'] . "'
					  WHERE id='" . $current_move_forum . "'");

				$std->copy_topicread_status($current_topic['tid'], $current_topic_id, $current_move_forum);
			}
			$data = [
				'has_mirror' => 1,
			];
			$ibforums->db->updateRow("ibf_topics", $data, "tid $tid");
			// Song * NEW
			// change fid id for transfering topic
			$ibforums->db->exec("UPDATE ibf_log_topics
					    SET
						fid=$current_move_forum,
						logTime='" . time() . "'
					    WHERE tid" . $tid);

			// $std->song_set_forumread($current_move_forum);

		}

		if (!is_array($source))
		{
			$source = array_fill_keys($topics, $source);

		}

		foreach ($topics as $topic)
		{
			$this->leave_message($moveto, $source[$topic], $topic, true); //Jureth
		}

		//----------------------------------
		// Sort out subscriptions
		//----------------------------------

		$stmt = $ibforums->db->query("SELECT
				tr.*,
				m.id,
				m.mgroup,
				m.org_perm_id,
				f.read_perms,
				f.id,
				t.tid,
				g.g_id,
				g.g_perm_id
			    FROM ibf_tracker tr
			    LEFT JOIN ibf_topics t
				ON (tr.topic_id=t.tid)
			    LEFT JOIN ibf_forums f
				ON (t.forum_id=f.id)
			    LEFT JOIN ibf_members m
				ON (m.id=tr.member_id)
			    LEFT JOIN ibf_groups g
				ON (g.g_id=m.mgroup)
			    WHERE tr.topic_id" . $tid);

		$trid_to_delete = array();

		while ($r = $stmt->fetch())
		{
			//----------------------------------------
			// Match the perm group against forum_mask
			//----------------------------------------

			$perm_id = $r['g_perm_id'];

			if ($r['org_perm_id'])
			{
				$perm_id = $r['org_perm_id'];
			}

			$pass = 0;

			$forum_perm_array = explode(",", $r['read_perms']);

			foreach (explode(',', $perm_id) as $u_id)
			{
				if (in_array($u_id, $forum_perm_array))
				{
					$pass = 1;
				}
			}

			if ($pass != 1)
			{
				$trid_to_delete[] = $r['trid'];
			}
		}

		if (count($trid_to_delete) > 0)
		{
			$ibforums->db->exec("DELETE
				    FROM ibf_tracker
				    WHERE trid IN(" . implode(',', $trid_to_delete) . ")");
		}

		return TRUE;

	}

	/**
	 * Удаляют зеркала для топика из указанных разделов
	 *
	 */
	function topic_delete_mirrors($topic_id, array $forum_ids)
	{
		$ibforums = Ibf::instance();
		array_walk($forum_ids, function (&$v)
		{
			$v = intval($v);
		});
		$topic_id = intval($topic_id);

		if ($forum_ids)
		{
			$query = "DELETE FROM ibf_topics WHERE `mirrored_topic_id` = $topic_id " . ' AND forum_id in (' . implode(', ', $forum_ids) . ')';
			$ibforums->db->query($query);
		}

		// if no more mirrors, then set `has_mirror` = false
		$query = "select count(*) as cnt from ibf_topics t1 where t1.mirrored_topic_id = $topic_id LIMIT 1";
		$stmt  = $ibforums->db->query($query);

		$row = $stmt->fetch();

		if ($row['cnt'] == 0)
		{
			$ibforums->db->exec("UPDATE ibf_topics SET has_mirror = false WHERE tid = $topic_id");
		}

		return true;
	}

	//------------------------------------------------------
	// @stats_recount: Recount all topics & posts
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stats_recount()
	{
		global $ibforums, $std;

		$stmt   = $ibforums->db->query("SELECT COUNT(tid) as tcount
			    FROM ibf_topics
			    WHERE approved=1");
		$topics = $stmt->fetch();

		$stmt  = $ibforums->db->query("SELECT COUNT(pid) as pcount
			    FROM ibf_posts
			    WHERE queued != 1");
		$posts = $stmt->fetch();

		$posts = $posts['pcount'] - $topics['tcount'];

		$ibforums->db->exec("UPDATE ibf_stats
			    SET
				TOTAL_TOPICS=" . $topics['tcount'] . ",
				TOTAL_REPLIES=" . $posts);
	}

	//------------------------------------------------------
	// @forum_recount_queue: Resets use_mod_posts boolean
	// -----------
	// Accepts: forum_id
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	//------------------------------------------------------
	// @forum_recount: Recount topic & posts in a forum
	// -----------
	// Accepts: forum_id
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function forum_recount($fid = "")
	{
		global $ibforums, $std;

		$fid = intval($fid);

		if (!$fid)
		{
			if ($this->forum['id'])
			{
				$fid = $this->forum['id'];
			} else
			{
				return FALSE;
			}
		}

		//----------------------------------------------
		// Get the topics..
		//----------------------------------------------

		$stmt = $ibforums->db->query("SELECT COUNT(tid) as count
			    FROM ibf_topics
			    WHERE
				forum_id=$fid");

		$topics = $stmt->fetch();

		//---------------------------------------------
		// Jureth: Get premoderated topics...
		//---------------------------------------------
		$stmt          = $ibforums->db->query("SELECT `approved`
			    FROM ibf_topics
			    WHERE
				approved=0
				and forum_id=$fid
			    LIMIT 0,1;");
		$topics_premod = $stmt->rowCount();

		//----------------------------------------------
		// Get the posts..
		//----------------------------------------------

		$stmt = $ibforums->db->query("SELECT COUNT(pid) as count
			    FROM ibf_posts
			    WHERE
				forum_id=$fid");

		$posts = $stmt->fetch();

		//---------------------------------------------
		// Jureth: Get premoderated posts
		//---------------------------------------------
		$stmt         = $ibforums->db->query("SELECT `queued`
			    FROM ibf_posts
			    WHERE
				queued = 1
				AND forum_id=$fid
			    LIMIT 0,1;");
		$posts_premod = $stmt->rowCount();

		//----------------------------------------------
		// Get the forum last poster..
		//----------------------------------------------

		$stmt = $ibforums->db->query("SELECT
				tid,
				title,
				last_poster_id,
				last_poster_name,
				last_post
			    FROM ibf_topics
			    WHERE
				approved=1 AND
				forum_id=$fid AND
				club=0
			    ORDER BY last_post DESC
			    LIMIT 1");

		$last_post = $stmt->fetch();

		//----------------------------------------------
		// Get real post count by removing topic starting posts from the count
		//----------------------------------------------

		$real_posts = intval($posts['count']) - intval($topics['count']) - intval($posts_premod);

		//----------------------------------------------
		// Reset this forums stats
		//----------------------------------------------

		$data = [
			'last_poster_id'   => $last_post['last_poster_id'],
			'last_poster_name' => $last_post['last_poster_name'],
			'last_post'        => $last_post['last_post'],
			'last_title'       => $last_post['title'],
			'last_id'          => $last_post['tid'],
			'topics'           => intval($topics['count']) < 1
				? 0
				: intval($topics['count']),
			'posts'            => intval($real_posts) < 1
				? 0
				: intval($real_posts),
			'has_mod_posts'    => (intval($posts_premod) + intval($topics_premod)) < 1
				? 0
				: 1
		];

		$ibforums->db->updateRow("ibf_forums", $data, "id=" . $fid);

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_init: Clear statement ready for multi-actions
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_init()
	{
		$this->stm = array();

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_exec: Executes stored statement
	// -----------
	// Accepts: Array ID's | Single ID
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_exec($id)
	{
		global $ibforums;

		if (count($this->stm) < 1)
		{
			return FALSE;
		}

		$final_array = array();

		foreach ($this->stm as $idx => $real_array)
		{
			foreach ($real_array as $k => $v)
			{
				$final_array[$k] = $v;
			}
		}

		if (is_array($id))
		{
			if (count($id) > 0)
			{
				$ibforums->db->updateRow("ibf_topics", array_map([
				                                                 $ibforums->db,
				                                                 'quote'
				                                                 ], $final_array), "tid IN(" . implode(",", $id) . ")");

				return TRUE;
			} else
			{
				return FALSE;
			}
		} else
		{
			if (intval($id) != "")
			{
				$ibforums->db->updateRow("ibf_topics", array_map([
				                                                 $ibforums->db,
				                                                 'quote'
				                                                 ], $final_array), "tid=" . intval($id));
			} else
			{
				return FALSE;
			}
		}
	}

	// Song * show/hide topics
	//------------------------------------------------------
	// @stm_add_hide: add hide command to statement
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_hide()
	{
		$this->stm[] = array('hidden' => 1);

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_add_show: add show command to statement
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_show()
	{
		$this->stm[] = array('hidden' => 0);

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_add_pin: add pin command to statement
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_pin()
	{
		$this->stm[] = array(
			'pinned'      => 1,
			'pinned_date' => time()
		);

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_add_unpin: add unpin command to statement
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_unpin()
	{
		$this->stm[] = array('pinned' => 0);

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_add_close: add close command to statement
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_close()
	{
		global $ibforums;

		// vot: BAD MESSAGE. NEED TO LOAD LANGUAGE FILE

		if ($ibforums->input['why_close'] == 'Введите причину закрытия темы: ')
		{
			$ibforums->input['why_close'] = "";
		}

		// Song * why_close topic

		require ROOT_PATH . "sources/lib/post_parser.php";

		$parser = new post_parser();

		$ibforums->input['why_close'] = $parser->macro($ibforums->input['why_close']);

		// vot: BAD MESSAGE. NEED TO LOAD LANGUAGE FILE

		$ibforums->input['why_close'] = '[b][size=5][color=red]Закрыто[/color][/size] ' . $ibforums->member['name'] . '[/b] ' . date('d-m-Y', time()) . ': ' . $ibforums->input['why_close'];

		$this->stm[] = array(
			'state'     => 'closed',
			'why_close' => $ibforums->input['why_close']
		);

		return TRUE;

	}

	//------------------------------------------------------
	// @stm_add_open: add open command to statement
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_open()
	{
		$this->stm[] = array('state' => 'open');

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_add_title: add edit title command to statement
	// -----------
	// Accepts: new_title
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_title($new_title = '')
	{
		if ($new_title == "")
		{
			return FALSE;
		}

		$this->stm[] = array('title' => $new_title);

		return TRUE;
	}

	//------------------------------------------------------
	// @stm_add_desc: add edit description command to statement
	// -----------
	// Accepts: new_title
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stm_add_desc($new_desc = '')
	{
		if ($new_desc == "")
		{
			return FALSE;
		}

		$this->stm[] = array('description' => $new_desc);

		return TRUE;
	}

	//------------------------------------------------------
	// @sql_prune_create: returns formatted SQL statement
	// -----------
	// Accepts: forum_id, poss_starter_id, poss_topic_state, poss_post_min
	//			poss_date_expiration, poss_ignore_pin_state
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function sql_prune_create($forum_id, $starter_id = "", $topic_state = "", $post_min = "", $date_exp = "", $ignore_pin = "")
	{
		$sql = "SELECT tid
			FROM ibf_topics
			WHERE
				approved=1 AND
				forum_id=" . intval($forum_id) . " ";

		if (intval($date_exp))
		{
			$sql .= "AND last_post < $date_exp ";
		}

		if (intval($starter_id))
		{
			$sql .= "AND starter_id=$starter_id ";

		}

		if (intval($post_min))
		{
			$sql .= "AND posts < $post_min ";
		}

		if ($topic_state != 'all')
		{
			if ($topic_state)
			{
				$sql .= "AND state='$topic_state' ";
			}
		}

		if ($ignore_pin != "")
		{
			$sql .= "AND pinned <> 1 ";
		}

		return $sql;

	}

	//------------------------------------------------------
	// @mm_authorize: Authorizes current member
	// -----------
	// Accepts: (NOTHING: Should already be passed to init)
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function mm_authorize()
	{
		global $ibforums, $std;

		$pass_go = FALSE;

		if ($ibforums->member['id'])
		{
			if ($ibforums->member['g_is_supmod'])
			{
				$pass_go = TRUE;
			} else
			{
				if ($this->moderator['can_mm'] == 1)
				{
					$pass_go = TRUE;
				}
			}
		}

		return $pass_go;
	}

	//------------------------------------------------------
	// @mm_check_id_in_forum: Checks to see if mm_id is in
	//                        the forum saved topic_mm_id
	// -----------
	// Accepts: (forum_topic_mm_id , this_mm_id)
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function mm_check_id_in_forum($forum_topic_mm_id, $this_mm_id)
	{
		$retval = FALSE;

		if (stristr($forum_topic_mm_id, ',' . $this_mm_id . ','))
		{
			$retval = TRUE;
		}

		return $retval;
	}

	//------------------------------------------------------
	// @add_moderate_log: Adds entry to mod log
	// -----------
	// Accepts: (forum_id, topic_id, topic_title, post_id, title)
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function add_moderate_log($fid, $tid, $pid, $t_title, $mod_title = 'Unknown')
	{
		global $std, $ibforums;

		$data = [
			'forum_id'     => $fid,
			'topic_id'     => $tid,
			'post_id'      => $pid,
			'member_id'    => $ibforums->member['id'],
			'member_name'  => $ibforums->member['name'],
			'ip_address'   => $ibforums->input['IP_ADDRESS'],
			'http_referer' => $_SERVER['HTTP_REFERER'],
			'ctime'        => time(),
			'topic_title'  => $t_title,
			'action'       => $mod_title,
			'query_string' => $_SERVER['QUERY_STRING']
		];

		$ibforums->db->insertRow("ibf_moderator_logs", $data);
	}

} // end of modfunctions class
