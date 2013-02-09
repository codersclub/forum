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
|   > Topic display in printable format module
|   > Module written by Matt Mecham
|   > Date started: 25th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Printable;

class Printable
{

	var $output = "";
	var $base_url = "";
	var $html = "";
	var $moderator = array();
	var $forum = array();
	var $topic = array();
	var $category = array();
	var $mem_groups = array();
	var $mem_titles = array();
	var $mod_action = array();
	var $poll_html = "";
	var $parser = "";

	/***********************************************************************************/
	//
	// Our constructor, load words, load skin, print the topic listing
	//
	/***********************************************************************************/

	function Printable()
	{

		global $ibforums, $std, $print, $skin_universal;

		require "sources/lib/post_parser.php";

		$this->parser = new post_parser();

		$this->parser->prepareIcons();

		/***********************************/
		// Compile the language file
		/***********************************/

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_printpage', $ibforums->lang_id);

		$this->html = $std->load_template('skin_printpage');

		/***********************************/
		// Check the input
		/***********************************/

		$ibforums->input['t'] = intval($ibforums->input['t']);
		$ibforums->input['f'] = intval($ibforums->input['f']);

		if (!$ibforums->input['t'] or !$ibforums->input['f'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		//-------------------------------------
		// Get the forum info based on the forum ID, get the category name, ID, and get the topic details
		//-------------------------------------

		$stmt = $ibforums->db->query("SELECT
			t.*,
			f.name as forum_name,
			f.id as forum_id,
			f.read_perms,
			f.password,
			f.reply_perms,
			f.start_perms,
			f.allow_poll,
			f.posts as forum_posts,
			f.topics as forum_topics,
			c.name as cat_name,
			c.id as cat_id
		    FROM
			ibf_topics t,
			 ibf_forums f,
			 ibf_categories c
		    WHERE
			t.tid='" . $ibforums->input['t'] . "'AND
			f.id = t.forum_id AND
			f.category=c.id");

		$this->topic = $stmt->fetch();

		$this->forum = array(
			'id'         => $this->topic['forum_id'],
			'name'       => $this->topic['forum_name'],
			'posts'      => $this->topic['forum_posts'],
			'topics'     => $this->topic['forum_topics'],
			'read_perms' => $this->topic['read_perms'],
			'allow_poll' => $this->topic['allow_poll'],
			'password'   => $this->topic['password']
		);

		$this->category = array(
			'name' => $this->topic['cat_name'],
			'id'   => $this->topic['cat_id'],
		);
		//-------------------------------------
		// Error out if we can not find the forum
		//-------------------------------------

		if (!$this->forum['id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		// Song * club tool

		if ($this->topic['club'] and $std->check_perms($ibforums->member['club_perms']) == FALSE)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'is_broken_link'));
		}

		//-------------------------------------
		// Error out if we can not find the topic
		//-------------------------------------

		if (!$this->topic['tid'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		$this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

		/***********************************/
		// Check viewing permissions, private forums,
		// password forums, etc
		/***********************************/

		if ((!$this->topic['pin_state']) and
		    (!$ibforums->member['g_other_topics'])
		)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_view_topic'));
		}

		$bad_entry = $this->check_access();

		if ($bad_entry == 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_view_topic'));
		}

		//------------------------------------------------------------
		//
		// Main logic engine
		//
		//------------------------------------------------------------

		if ($ibforums->input['client'] == 'choose')
		{
			// Show the "choose page"

			$this->page_title = $this->topic['title'];

			$this->nav = array(
				"<a href='{$this->base_url}&act=SF&f={$this->forum['id']}'>{$this->forum['name']}</a>",
				"<a href='{$this->base_url}&act=ST&f={$this->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>"
			);

			$this->output = $this->html->choose_form($this->forum['id'], $this->topic['tid'], $this->topic['title']);

			$print->add_output("$this->output");

			$print->do_output(array(
			                       'TITLE' => $this->topic['title'] . " -> " . $ibforums->vars['board_name'],
			                       'JS'    => 0,
			                       'NAV'     => $this->nav
			                  ));

			exit(); // Incase we haven't already done so :p
		} else
		{
			$header = 'text/html';
			$ext    = '.html';

			switch ($ibforums->input['client'])
			{
				case 'printer':
					$header = 'text/html';
					$ext    = '.html';
					break;
				case 'html':
					$header = 'unknown/unknown';
					$ext    = '.html';
					break;
				default:
					$header = 'application/msword';
					$ext    = '.doc';
			}
		}

		$title = substr(str_replace(" ", "_", preg_replace("/&(lt|gt|quot|#124|#036|#33|#39);/", "", $this->topic['title'])), 0, 12);

		@header("Content-type: $header");

		if ($ibforums->input['client'] != 'printer')
		{
			@header("Content-Disposition: attachment; filename=$title" . $ext);
		}

		print $this->get_posts();

		exit;

	}

	function get_posts()
	{
		global $ibforums, $std;

		/***********************************/
		// Render the page top
		/***********************************/

		$posts_html = $this->html->pp_header($this->forum['name'], $this->topic['title'], $this->topic['starter_name'], $this->forum['id'], $this->topic['tid']);

		$stmt = $ibforums->db->query("SELECT * FROM ibf_posts WHERE topic_id='" . $this->topic['tid'] . "' and queued !='1' and use_sig=0 ORDER BY pid");

		// Loop through to pick out the correct member IDs.
		// and push the post info into an array - maybe in the future
		// we can add page spans, or maybe save to a PDF file?

		$the_posts      = array();
		$mem_ids        = "";
		$member_array   = array();
		$cached_members = array();

		while ($i = $stmt->fetch())
		{
			$the_posts[] = $i;

			if ($i['author_id'] != 0)
			{
				if (preg_match("/'" . $i['author_id'] . "',/", $mem_ids))
				{
					continue;
				} else
				{
					$mem_ids .= "'" . $i['author_id'] . "',";
				}
			}
		}

		// Fix up the member_id string
		$mem_ids = preg_replace("/,$/", "", $mem_ids);

		// Get the member profiles needed for this topic

		if ($mem_ids != "")
		{
			$stmt = $ibforums->db->query("SELECT id,name,mgroup,email,joined,ip_address,avatar,avatar_size,posts,aim_name,icq_number,signature,
							   website,yahoo,title,hide_email,msnname from ibf_members WHERE id in ($mem_ids)");

			while ($m = $stmt->fetch())
			{
				if ($m['id'] and $m['name'])
				{
					if (isset($member_array[$m['id']]))
					{
						continue;
					} else
					{
						$member_array[$m['id']] = $m;
					}
				}
			}
		}

		/***********************************/
		// Format and print out the topic list
		/***********************************/

		$td_col_cnt = 0;

		foreach ($the_posts as $row)
		{

			$poster = array();

			// Get the member info. We parse the data and cache it.
			// It's likely that the same member posts several times in
			// one page, so it's not efficient to keep parsing the same
			// data

			if ($row['author_id'] != 0)
			{
				// Is it in the hash?
				if (isset($cached_members[$row['author_id']]))
				{
					// Ok, it's already cached, read from it
					$poster          = $cached_members[$row['author_id']];
					$row['name_css'] = 'normalname';
				} else
				{
					// Ok, it's NOT in the cache, is it a member thats
					// not been deleted?
					if ($member_array[$row['author_id']])
					{
						$row['name_css'] = 'normalname';
						$poster          = $member_array[$row['author_id']];
						// Add it to the cached list
						$cached_members[$row['author_id']] = $poster;
					} else
					{
						// It's probably a deleted member, so treat them as a guest
						$poster          = $std->set_up_guest($row['author_id']);
						$row['name_css'] = 'unreg';
					}
				}
			} else
			{
				// It's definately a guest...
				$poster          = $std->set_up_guest($row['author_name']);
				$row['name_css'] = 'unreg';
			}

			//--------------------------------------------------------------

			$row['post_css'] = $td_col_count % 2
				? 'post1'
				: 'post2';

			++$td_col_count;

			//--------------------------------------------------------------

			//--------------------------------------------------------------

			$row['post_date'] = $std->get_date($row['post_date']);

			$row['post'] = $this->parse_message($row);

			//--------------------------------------------------------------
			// Siggie stuff
			//--------------------------------------------------------------

			if (!$ibforums->vars['SIG_SEP'])
			{
				$ibforums->vars['SIG_SEP'] = "<br><br>--------------------<br>";
			}

			if ($poster['signature'] and $ibforums->member['view_sigs'])
			{
				$row['signature'] = "<!--Signature-->{$ibforums->vars['SIG_SEP']}<span class='signature'>{$poster['signature']}</span><!--E-Signature-->";
			}

			if (trim($row['post']))
			{
				$posts_html .= $this->html->pp_postentry($poster, $row);
			}
		}

		/***********************************/
		// Print the footer
		/***********************************/

		$posts_html .= $this->html->pp_end();

		return $posts_html;
	}

	function parse_message($post = array())
	{

		$message = $this->parser->prepare(array(
		                                       'TEXT'      => $post['post'],
		                                       'SMILIES'   => $post['use_emo'],
		                                       'CODE'      => 1,
		                                       'SIGNATURE' => 0,
		                                       'HTML'      => 1,
		                                       'HID'       => -1,
		                                       'TID'       => $post['topic_id'],
		                                       'MID'       => $post['author_id'],
		                                  ));

		$message = preg_replace("/<!--EDIT\|(.+?)\|(.+?)-->/", "", $message);
		//$message = preg_replace( "#<!--emo&(.+?)-->.+?<!--endemo-->#", "\\1" , $message );

		//$message = preg_replace( "#<!--c1-->(.+?)<!--ec1-->#", "\n\n------------ CODE SAMPLE ----------\n"  , $message );
		//$message = preg_replace( "#<!--c2-->(.+?)<!--ec2-->#", "\n-----------------------------------\n\n"  , $message );

		//$message = preg_replace( "#<!--QuoteBegin-->(.+?)<!--QuoteEBegin-->#"                       , "\n\n------------ QUOTE ----------\n" , $message );
		//$message = preg_replace( "#<!--QuoteBegin--(.+?)\+(.+?)-->(.+?)<!--QuoteEBegin-->#"         , "\n\n------------ QUOTE ----------\n" , $message );
		//$message = preg_replace( "#<!--QuoteEnd-->(.+?)<!--QuoteEEnd-->#"                           , "\n-----------------------------\n\n" , $message );

		$message = preg_replace("#<!--Flash (.+?)-->.+?<!--End Flash-->#e", "(FLASH MOVIE)", $message);
		//$message = preg_replace( "#<img src=[\"'](\S+?)['\"].+"."?".">#"                            , "(IMAGE: \\1)"   , $message );
		$message = preg_replace("#<a href=[\"'](http|https|ftp|news)://(\S+?)['\"].+?" . ">(.+?)</a>#", "\\1://\\2", $message);
		//$message = preg_replace( "#<a href=[\"']mailto:(.+?)['\"]>(.+?)</a>#"                       , "(EMAIL: \\2)"   , $message );

		//$message = preg_replace( "#<!--sql-->(.+?)<!--sql1-->(.+?)<!--sql2-->(.+?)<!--sql3-->#e"    , "\n\n--------------- SQL -----------\n\\2\n----------------\n\n", $message);
		//$message = preg_replace( "#<!--html-->(.+?)<!--html1-->(.+?)<!--html2-->(.+?)<!--html3-->#e", "\n\n-------------- HTML -----------\n\\2\n----------------\n\n", $message);

		return $message;

	}

	function check_access()
	{
		global $ibforums, $std;

		$return = 1;

		$this->m_group = $ibforums->member['mgroup'];

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
}

