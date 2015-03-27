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
|   > Edit post library
|   > Module written by Matt Mecham
|   > Date started: 19th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

require_once dirname(__FILE__) . '/PostEditHistory.php';

use Views\View;

class post_functions extends Post
{

	var $nav = array();
	var $title = "";
	var $post = array();
	var $topic = array();
	var $upload = array();
	var $moderator = array(
		'member_id'   => 0,
		'member_name' => "",
		'edit_post'   => 0
	);
	var $orig_post = array();
	var $edit_title = 0;

	//-----------------------------------
	function post_functions($class)
	{

		global $ibforums, $std;

		//-------------------------------------------------
		// Lets load the topic from the database before
		// we do anything else.
		//-------------------------------------------------

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_topics
			    WHERE tid='" . intval($ibforums->input['t']) . "'");

		$this->topic = $stmt->fetch();

		//-------------------------------------------------
		// Is it legitimate?
		//-------------------------------------------------

		if (!$this->topic['tid'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		//-------------------------------------------------
		// Load the old post
		//-------------------------------------------------

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_posts
			    WHERE pid='" . intval($ibforums->input['p']) . "'");

		$this->orig_post = $stmt->fetch();

		if (!$this->orig_post['pid'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		// Check for the URL parameters are modified manually

		if (($this->orig_post['pid'] != intval($ibforums->input['p'])) || ($this->orig_post['topic_id'] != intval($ibforums->input['t'])) || ($this->orig_post['forum_id'] != intval($ibforums->input['f']))
		)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		//-------------------------------------------------
		// Load the moderator IF the user is moderator
		//-------------------------------------------------

		if ($ibforums->member['id'])
		{
			$stmt = $ibforums->db->query("SELECT
					member_id,
					member_name,
					mid,
					edit_post,
					edit_topic
				    FROM ibf_moderators
				    WHERE
					forum_id='" . $this->orig_post['forum_id'] . "'
					AND (member_id='" . $ibforums->member['id'] . "'
					  OR (is_group=1
						AND group_id='" . $ibforums->member['mgroup'] . "'))");

			$this->moderator = $stmt->fetch();
		}

		//-------------------------------------------------
		// Lets do some tests to make sure that we are
		// allowed to edit this topic
		//-------------------------------------------------

		$can_edit = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$can_edit = 1;
		}

		if ($this->moderator['edit_post'])
		{
			$can_edit = 1;
		}

		if ($this->orig_post['author_id'] == $ibforums->member['id'] and $ibforums->member['g_edit_posts'])
		{
			if (!$this->orig_post['has_modcomment'])
			{
				// Have we set a time limit?
				if ($ibforums->member['g_edit_cutoff'] > 0)
				{
					if ($this->orig_post['post_date'] > (time() - (intval($ibforums->member['g_edit_cutoff']) * 60)))
					{
						$can_edit = 1;
					}

				} else
				{
					$can_edit = 1;
				}

			} elseif ($ibforums->member['is_mod'])
			{
				$can_edit = 1;
			}
		}

		if (!$can_edit)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'not_op'
			            ));
		}

		// Is the topic locked?

		if ($this->topic['state'] != 'open')
		{
			if (!$ibforums->member['id'] or
			    !($ibforums->member['g_post_closed'] and
			      ($ibforums->member['g_is_supmod'] or
			       $class->moderator['mid']))
			)
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'locked_topic'
				            ));
			}
		}

		//-----------------------------
		// // Do we have edit topic abilities?
		//-----------------------------

		if ($this->orig_post['new_topic'] == 1)
		{
			if ($ibforums->member['g_is_supmod'] == 1)
			{
				$this->edit_title = 1;

			} elseif ($this->moderator['edit_topic'] == 1)
			{
				$this->edit_title = 1;

			} elseif ($ibforums->member['g_edit_topic'] == 1 and
			          $ibforums->member['id'] == $this->topic['starter_id']
			)
			{
				$this->edit_title = 1;
			}
		}
	}

	//-------------------------------------
	function process($class)
	{

		global $ibforums, $std, $print;

		//-------------------------------------------------
		// Parse the post, and check for any errors.
		// overwrites saved post intentionally
		//-------------------------------------------------

		$this->post = $class->compile_post();

		if (($class->obj['post_errors'] != "") or
		    ($class->obj['preview_post'] != "") or
		    $class->upload_errors
		)
		{
			// Show the form again
			$this->show_form($class);
		} else
		{
			$this->complete_edit($class);
		}
	}

	//------------------------------------------
	function complete_edit(Post $class)
	{

		global $ibforums, $std, $print;

		$dbs = array();

		$this->post['queued'] = $this->orig_post['queued'];

		if (($ibforums->input['mod_options'] != "")
		    or ($ibforums->input['mod_options'] != 'nowt')
		)
		{
			// Pin Topic

			if ($ibforums->input['mod_options'] == 'pin')
			{
				if ($ibforums->member['g_is_supmod'] == 1
				    or $class->moderator['pin_topic'] == 1
				)
				{
					$dbs['pinned']      = 1;
					$dbs['pinned_date'] = time();

					$class->moderate_log('Pinned topic from post form', $ibforums->input['TopicTitle']);
				}

				// Close Topic

			} elseif ($ibforums->input['mod_options'] == 'close')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['close_topic'] == 1
				)
				{
					$dbs['state'] = "closed";

					$class->moderate_log('Closed topic from post form', $ibforums->input['TopicTitle']);
				}

				// Pin & Close Topic

			} elseif ($ibforums->input['mod_options'] == 'pinclose')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    ($class->moderator['pin_topic'] == 1 AND
				     $class->moderator['close_topic'] == 1)
				)
				{
					$dbs['pinned']      = 1;
					$dbs['pinned_date'] = time();
					$dbs['state']       = "closed";

					$class->moderate_log('Pinned & closed topic from post form', $ibforums->input['TopicTitle']);
				}

				// Delete Topic

			} elseif ($ibforums->input['mod_options'] == 'delete')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['delete_post'] == 1
				)
				{
					$this->post['use_sig']        = 1;
					$this->post['append_edit']    = 1;
					$this->post['has_modcomment'] = 1;
					$this->post['edit_name']      = $ibforums->member['name'];

					$class->moderate_log('Decline post within answer', $ibforums->input['TopicTitle']);
				}

				// Decline Topic

			} elseif ($ibforums->input['mod_options'] == 'decline')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['topic_q'] == 1
				)
				{
					if ($this->orig_post['new_topic'] == 1)
					{
						$dbs['approved'] = 0;
					}

					$this->post['queued'] = 1;

					$ibforums->db->exec("UPDATE ibf_forums
						    SET has_mod_posts=1
						    WHERE id='" . $class->forum['id'] . "'");
					$class->obj['moderate'] = 1;
					$class->moderate_log('Hide topic within answer', $ibforums->input['TopicTitle']);
				}

				// Hide Topic

			} elseif ($ibforums->input['mod_options'] == 'hide')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or $class->moderator['hide_topic'] == 1)
				{
					$dbs['hidden'] = 1;
					$class->moderate_log('Hide topic within answer', $ibforums->input['TopicTitle']);
				}
			}
		}

		//-------------------------------------------------
		// Reset some data
		//-------------------------------------------------

		// Do we have to adjust the attachments?
		$new_attachments = $class->process_upload();

		if ($attachments = Attachment::getPostAttachmentsFromRow($this->orig_post))
		{
			$class->process_edituploads($attachments);

			$this->post['attach_id']   = $this->orig_post['attach_id'];
			$this->post['attach_type'] = $this->orig_post['attach_type'];
			$this->post['attach_hits'] = $this->orig_post['attach_hits'];
			$this->post['attach_file'] = $this->orig_post['attach_file'];
		}
		if ($new_attachments && count($new_attachments) > 0)
		{

			$class->replace_attachments_tags($this->post['post'], $new_attachments, $this->orig_post['pid']);

		}

		$this->post['attach_exists'] = (isset($attachments) and (bool)count($attachments));

		$this->post['ip_address']  = $this->orig_post['ip_address'];
		$this->post['topic_id']    = $this->orig_post['topic_id'];
		$this->post['author_id']   = $this->orig_post['author_id'];
		$this->post['pid']         = $this->orig_post['pid'];
		$this->post['post_date']   = $this->orig_post['post_date'];
		$this->post['author_name'] = $this->orig_post['author_name'];
		$this->post['edit_name']   = $ibforums->member['name'];
		$this->post['edit_time']   = time();

		//-------------------------------------------------
		// If the post icon has changed, update the topic post icon
		//-------------------------------------------------

		if ($this->orig_post['new_topic'] == 1)
		{
			if ($this->post['icon_id'] != $this->orig_post['icon_id'])
			{
				$dbs['icon_id'] = $this->post['icon_id'];
			}
		}

		//-------------------------------------------------
		// Update topic title?
		//-------------------------------------------------

		$dbf = array();

		if (!$this->topic['club'] and
		    (($ibforums->member['g_is_supmod'] or
		      $class->moderator['edit_post']) and
		     $ibforums->input['bump'])
		)
		{
			$dbf['last_title'] = addslashes($this->topic['title']);
			$dbf['last_id']    = $this->topic['tid'];
		}

		if ($this->edit_title == 1)
		{
			if ($ibforums->vars['etfilter_punct'])
			{
				$ibforums->input['TopicTitle'] = preg_replace("/\?{1,}/", "?", $ibforums->input['TopicTitle']);
				$ibforums->input['TopicTitle'] = preg_replace("/(&#33;){1,}/", "&#33;", $ibforums->input['TopicTitle']);
			}

			if ($ibforums->vars['etfilter_shout'])
			{
				$ibforums->input['TopicTitle'] = ucwords(mb_strtolower($ibforums->input['TopicTitle']));
			}

			$ibforums->input['TopicTitle'] = trim($class->parser->bad_words($ibforums->input['TopicTitle']));
			$ibforums->input['TopicDesc']  = trim($class->parser->bad_words($ibforums->input['TopicDesc']));

			if ($ibforums->input['club_only'] and
			    $std->check_perms($ibforums->member['club_perms']) == FALSE
			)
			{
				$ibforums->input['club_only'] = "0";
			}

			if ($ibforums->input['TopicTitle'])
			{
				if ($ibforums->input['TopicTitle'] != $this->topic['title'] or
				    $ibforums->input['TopicDesc'] != $this->topic['description'] or
				    $ibforums->input['club_only'] != $this->topic['club']
				)
				{
					$dbs['title']       = $ibforums->input['TopicTitle'];
					$dbs['description'] = $ibforums->input['TopicDesc'];
					$dbs['club']        = $ibforums->input['club_only'];

					if (!$ibforums->input['club_only'] and
					    (($ibforums->member['g_is_supmod'] or
					      $class->moderator['edit_post']) and
					     $ibforums->input['bump'])
					)
					{
						$dbf['last_title'] = addslashes($ibforums->input['TopicTitle']);
						$dbf['last_id']    = $this->topic['tid'];
					}

					if ($this->moderator['edit_topic'] == 1 OR
					    $ibforums->member['g_is_supmod'] == 1
					)
					{
						$class->moderate_log("Edited topic title or description '{$this->topic['title']}' to '{$ibforums->input['TopicTitle']}' via post form", $this->topic['title']);
					}
				}
			}
		}

		$time = time();

		if (($ibforums->member['g_is_supmod'] or
		     $class->moderator['edit_post']) and
		    $ibforums->input['bump']
		)
		{
			$dbs['last_poster_id']   = $ibforums->member['id'];
			$dbs['last_poster_name'] = $ibforums->member['name'];

			if (!$this->topic['club'] and !$dbs['club'])
			{
				$dbf['last_poster_id']   = $ibforums->member['id'];
				$dbf['last_poster_name'] = $ibforums->member['name'];
			}

			$dbs['last_post'] = $time;
			$dbf['last_post'] = $time;
		}

		//-------------------------------------------------
		// Update the Topic (ibf_topics)
		//-------------------------------------------------

		if (count($dbs))
		{
			$dbs = array_map([$ibforums->db, 'quote'], $dbs);
			$ibforums->db->updateRow("ibf_topics", $dbs, "tid='" . $this->topic['tid'] . "'");

			if ($this->topic['has_mirror'])
			{
				$ibforums->db->updateRow("ibf_topics", $dbs, "mirrored_topic_id='" . $this->topic['tid'] . "'");
			}

			//----------------------------------------------------
			// Index the Topic Title for the Indexed Search Engine
			//----------------------------------------------------

			$std->index_reindex_title($this->topic['tid'], $class->forum['id'], $ibforums->input['TopicTitle']);

		}

		//-------------------------------------------------
		// Update the Forum (ibf_forums)
		//-------------------------------------------------

		if (count($dbf))
		{
			$dbf = array_map([$ibforums->db, 'qoute'], $dbf);

			$ibforums->db->updateRow("ibf_forums", $dbf, "id='" . $class->forum['id'] . "'");
		}

		//-------------------------------------------------
		// Update the database (ibf_posts)
		//-------------------------------------------------

		if (!$this->post['append_edit'])
		{
			$this->post['append_edit'] = 1;

			if ($ibforums->member['g_append_edit'] and $this->orig_post['author_id'] == $ibforums->member['id'])
			{
				if ($ibforums->input['add_edit'] != 'yes')
				{
					$this->post['append_edit'] = 0;
				}
			}
		}

		if (!$this->post['has_modcomment'])
		{
			$this->post['has_modcomment'] = $std->mod_tag_exists($this->post['post'], 1);
		}

		if ($this->orig_post['delete_after']
		    and !$this->post['delete_after']
		)
		{
			if (!($moderator['delete_post']
			      or $ibforums->member['g_is_supmod']
			      or $ibforums->member['g_delay_delete_posts'])
			)
			{
				$this->post['delete_after'] = $this->orig_post['delete_after'];
			}
		}

		if ($this->orig_post['new_topic'])
		{
			$this->post['delete_after'] = 0;
		}

		$ibforums->db->updateRow("ibf_posts", array_map([
		                                                $ibforums->db,
		                                                'quote'
		                                                ], $this->post), "pid='" . $this->post['pid'] . "'");

		PostEditHistory::addItem($this->post['pid'], $this->orig_post['post']);

		//----------------------------------------------------
		// Index the Post body for the Indexed Search Engine
		//----------------------------------------------------

		$std->index_reindex_post($this->post['pid'], $this->topic['tid'], $class->forum['id'], $this->post['post']);

		//-------------------------------------------------
		// Redirect them back to the topic
		//-------------------------------------------------

		$print->redirect_screen($ibforums->lang['post_edited'], "showtopic={$this->topic['tid']}&view=findpost&p={$this->post['pid']}");

	}

	//-----------------------------------------------------
	function show_form($class)
	{

		global $ibforums, $std, $print;

		if (isset($ibforums->input['restore_id']))
		{
			$history_item  = PostEditHistory::getOneItem($this->orig_post['pid'], $ibforums->input['restore_id']);
			$_POST['Post'] = $history_item['old_text'];
			if (($this->moderator['mid'] != "" && $ibforums->member['id'] != 0) || $ibforums->member['g_is_supmod'] == 1)
			{
				$modflag = true;
			} else
			{
				$modflag = false;
			}

			$this->post['post'] = $class->parser->convert(array(
			                                                   'TEXT'     => $_POST['Post'],
			                                                   'SMILIES'  => $ibforums->input['enableemo'],
			                                                   'CODE'     => $class->forum['use_ibc'],
			                                                   'HTML'     => $class->forum['use_html'],
			                                                   'MOD_FLAG' => $modflag,
			                                              ), $this->forum['id']);
		}

		//-------------------------------------------------
		// Sort out the "raw" textarea input and make it safe incase
		// we have a <textarea> tag in the raw post var.
		//
		// использовать нужно именно $_POST['Post'], ибо переменная в $ibforums->input пропущена через functions::clean_value()
		// и после пропускания ещё через txt_htmlspecialchars, получается ерунда. В дальнейшем, их надо бы согласовать как-нибудь
		// но сначала надо разобраться какая ф-ция, как и где используется.
		//-------------------------------------------------

		$raw_post = isset($_POST['Post'])
			? $std->txt_htmlspecialchars($_POST['Post'])
			: $class->parser->unconvert($this->orig_post['post'], $class->forum['use_ibc'], $class->forum['use_html']);

		if (isset($raw_post))
		{
			$raw_post = $std->txt_raw2form($raw_post);

			$raw_post = str_replace(array("&#091;", "&#093;"), array("&amp;#091;", "&amp;#093;"), $raw_post);
		}

		//-------------------------------------------------
		// Is this the first post in the topic?
		//-------------------------------------------------

		if ($this->edit_title == 1)
		{
			$topic_title = isset($ibforums->input['TopicTitle'])
				? $ibforums->input['TopicTitle']
				: $this->topic['title'];
			$topic_desc  = isset($ibforums->input['TopicDesc'])
				? $ibforums->input['TopicDesc']
				: $this->topic['description'];

			$topic_title = View::make(
				"post.topictitle_fields",
				['data' => array('TITLE' => $topic_title, 'DESC' => $topic_desc)]
			);

			if ($std->check_perms($ibforums->member['club_perms']) == TRUE and
			    $ibforums->member['club_perms'] != $class->forum['read_perms']
			)
			{
				if ((!$class->obj['preview_post'] and $this->topic['club']) or
				    ($class->obj['preview_post'] and $ibforums->input['club_only'])
				)
				{
					$checked = "checked";
				}

				$rights_options = View::make("post.rights_options", ['checked' => $checked]);
			}

		}

		//-------------------------------------------------
		// Do we have any posting errors?
		//-------------------------------------------------

		if ($class->obj['post_errors'])
		{
			if ($ibforums->member['id'] and $class->obj['post_errors'] == "no_mail")
			{
				$ibforums->lang[$class->obj['post_errors']] = sprintf($ibforums->lang[$class->obj['post_errors']], $ibforums->member['disable_mail_reason']);
			}

			$class->output .= View::make("post.errors", ['data' => $ibforums->lang[$class->obj['post_errors']]]);
		}
		if ($class->upload_errors)
		{
			foreach ($class->upload_errors as $error_message)
			{
				$class->output .= View::make("post.errors", ['data' => $error_message]);
			}
		}

		if ($class->obj['preview_post'])
		{
			$this->post['post'] = $class->parser->post_db_parse($class->parser->prepare(array(
			                                                                                 'TEXT'    => $this->post['post'],
			                                                                                 'CODE'    => $class->forum['use_ibc'],
			                                                                                 'SMILIES' => $ibforums->input['enableemo'],
			                                                                                 'HTML'    => $class->forum['use_html']
			                                                                            )),

				$class->forum['use_html'] AND $ibforums->member['g_dohtml']
					? 1
					: 0);

			$class->output .= View::make("post.preview", ['data' => $this->post['post']]);
		}

		$class->check_upload_ability();

		$class->output .= $class->html_start_form(array(
		                                               1 => array('CODE', '09'),
		                                               2 => array('t', $this->topic['tid']),
		                                               3 => array('p', $ibforums->input['p']),
		                                               4 => array('st', $ibforums->input['st']),
		                                          ));

		//---------------------------------------
		// START TABLE
		//---------------------------------------

		$class->output .= View::make("post.table_structure");

		//---------------------------------------

		$start_table = View::make(
			"post.table_top",
			['data' => "{$ibforums->lang['top_txt_edit']} {$this->topic['title']}"]
		);

		$name_fields = $class->html_name_field();

		$post_box = $class->html_post_body($raw_post);

		$end_form = View::make("post.EndForm", ['data' => $ibforums->lang['submit_edit']]);

		$post_icons = $class->html_post_icons($this->orig_post['icon_id']);

		if ($class->obj['can_upload'])
		{
			if ($attachments = Attachment::getPostAttachmentsFromRow($this->orig_post))
			{

				$upload_field .= View::make(
					"post.edit_upload_field",
					['data' => $std->size_format($ibforums->member['g_attach_max'] * 1024), 'files' => $attachments]
				);
			} else
			{
				$upload_field = View::make(
					"post.Upload_field",
					['data' => $std->size_format($ibforums->member['g_attach_max'] * 1024)]
				);
			}
		}

		$mod_options = $class->mod_options();

		//---------------------------------------

		$class->output = str_replace("<!--START TABLE-->", $start_table, $class->output);
		$class->output = str_replace("<!--NAME FIELDS-->", $name_fields, $class->output);
		$class->output = str_replace("<!--POST BOX-->", $post_box, $class->output);
		$class->output = str_replace("<!--POST ICONS-->", $post_icons, $class->output);
		$class->output = str_replace("<!--END TABLE-->", $end_form, $class->output);
		$class->output = str_replace("<!--UPLOAD FIELD-->", $upload_field, $class->output);
		$class->output = preg_replace("/<!--RIGHTS OPTIONS-->/", "$rights_options", $class->output);
		$class->output = str_replace("<!--MOD OPTIONS-->", $mod_options . $edit_option, $class->output);

		if ($class->forum['show_rules'])
		{
			if ($class->forum['rules_title'])
			{
				$class->forum['rules_title'] = trim($class->parser->prepare(array(
				                                                                 'TEXT'      => $class->forum['rules_title'],
				                                                                 'SMILIES'   => 1,
				                                                                 'CODE'      => 1,
				                                                                 'SIGNATURE' => 0,
				                                                                 'HTML'      => 0,
				                                                            )));

				$class->forum['rules_text'] = trim($class->parser->prepare(array(
				                                                                'TEXT'      => $class->forum['rules_text'],
				                                                                'SMILIES'   => 1,
				                                                                'CODE'      => 1,
				                                                                'SIGNATURE' => 0,
				                                                                'HTML'      => 0,
				                                                           )));

				$class->forum['rules_text'] = str_replace(";&lt;br&gt;", "<br>", $class->forum['rules_text']);
			}
		}

		$class->output = str_replace("<!--FORUM RULES-->", $std->print_forum_rules($class->forum), $class->output);
		$class->output = str_replace("<!--TOPIC TITLE-->", $topic_title, $class->output);

		//---------------------------------------

		$class->html_add_smilie_box();

		//---------------------------------------
		// Add in siggy buttons and such
		//---------------------------------------

		$class->html_checkboxes('edit', "", $this->orig_post['use_emo'], $this->orig_post['delete_after']);

		//---------------------------------------

		$class->html_topic_summary($this->topic['tid']);

		$this->nav = array(
			"<a href='{$class->base_url}&act=SC&c={$class->forum['cat_id']}'>{$class->forum['cat_name']}</a>",
			"<a href='{$class->base_url}&act=SF&f={$class->forum['id']}'>{$class->forum['name']}</a>",
			"<a href='{$class->base_url}&act=ST&f={$class->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
		);

		$this->title = $ibforums->lang['editing_post'] . ' ' . $this->topic['title'];

		$print->add_output("$class->output");

		$print->do_output(array(
		                       'TITLE' => $this->title . " -> " . $ibforums->vars['board_name'],
		                       'NAV'   => $class->nav_extra,
		                  ));

	}

}
