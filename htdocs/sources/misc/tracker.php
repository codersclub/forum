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
|   > Topic Tracker module
|   > Module written by Matt Mecham
|   > Date started: 5th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new tracker;

class tracker
{

	var $output = "";
	var $base_url = "";

	var $forum = array();
	var $topic = array();
	var $category = array();
	var $type = 'topic';

	function __construct($is_sub = 0)
	{

		//------------------------------------------------------
		// $is_sub is a boolean operator.
		// If set to 1, we don't show the "topic subscribed" page
		// we simply end the subroutine and let the caller finish
		// up for us.
		//------------------------------------------------------

		global $ibforums, $std, $print;

		if ($ibforums->member['disable_mail'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_mail', 'EXTRA' => $ibforums->member['disable_mail_reason']));
		}

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_emails', $ibforums->lang_id);

		//------------------------------------------------------
		// Check the input
		//------------------------------------------------------

		if ($ibforums->input['type'] == 'forum')
		{
			$this->type = 'forum';
		}

		$ibforums->input['t'] = intval($ibforums->input['t']);
		$ibforums->input['f'] = intval($ibforums->input['f']);

		//------------------------------------------------------
		// Get the forum info based on the forum ID, get the category name, ID, and get the topic details
		//------------------------------------------------------

		if ($this->type == 'forum')
		{
			$stmt = $ibforums->db->query("SELECT f.id as fid, f.read_perms, f.password FROM ibf_forums f WHERE f.id='" . $ibforums->input['f'] . "'");
		} else
		{
			$stmt = $ibforums->db->query("SELECT t.tid, f.id as fid, f.read_perms, f.password FROM ibf_topics t, ibf_forums f WHERE t.tid='" . $ibforums->input['t'] . "' AND t.forum_id=f.id");
		}

		$this->topic = $stmt->fetch();

		//------------------------------------------------------
		// Error out if we can not find the forum
		//------------------------------------------------------

		if (!$this->topic['fid'])
		{
			if ($is_sub != 1)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			} else
			{
				return;
			}
		}

		//------------------------------------------------------
		// Error out if we can not find the topic
		//------------------------------------------------------

		if ($this->type != 'forum')
		{
			if (!$this->topic['tid'])
			{
				if ($is_sub != 1)
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
				} else
				{
					return;
				}
			}
		}

		$this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

		$this->base_url_NS = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

		//------------------------------------------------------
		// Check viewing permissions, private forums,
		// password forums, etc
		//------------------------------------------------------

		if (!$ibforums->member['id'])
		{
			if ($is_sub != 1)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_guests'));
			} else
			{
				return;
			}
		}

		if ($std->check_perms($this->topic['read_perms']) != TRUE)
		{
			if ($is_sub != 1)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'forum_no_access'));
			} else
			{
				return;
			}
		}

		if ($this->topic['password'] != "")
		{

			if (!$c_pass = $std->my_getcookie('iBForum' . $this->topic['fid']))
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'forum_no_access'));
			}

			if ($c_pass != $this->topic['password'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'forum_no_access'));
			}

		}

		//------------------------------------------------------
		// Have we already subscribed?
		//------------------------------------------------------

		if ($this->type == 'forum')
		{
			$stmt = $ibforums->db->query("SELECT frid from ibf_forum_tracker WHERE forum_id='" . $this->topic['fid'] . "' AND member_id='" . $ibforums->member['id'] . "'");
		} else
		{
			$stmt = $ibforums->db->query("SELECT trid from ibf_tracker WHERE topic_id='" . $this->topic['tid'] . "' AND member_id='" . $ibforums->member['id'] . "'");
		}

		if ($stmt->rowCount())
		{
			if ($is_sub != 1)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'already_sub'));
			} else
			{
				return;
			}
		}

		//------------------------------------------------------
		// Add it to the DB
		//------------------------------------------------------

		if ($this->type == 'forum')
		{

			$data = [
				'member_id'  => $ibforums->member['id'],
				'forum_id'   => $this->topic['fid'],
				'start_date' => time(),
			];

			$ibforums->db->insertRow("ibf_forum_tracker", $data);

		} else
		{
			$data = [
				'member_id'  => $ibforums->member['id'],
				'topic_id'   => $this->topic['tid'],
				'start_date' => time(),
			];

			$ibforums->db->insertRow("ibf_tracker", $data);

		}

		if ($is_sub != 1)
		{
			if ($this->type == 'forum')
			{
				$print->redirect_screen($ibforums->lang['sub_added'], "act=SF&f={$this->topic['fid']}");
			} else
			{
				$print->redirect_screen($ibforums->lang['sub_added'], "act=ST&f={$this->topic['fid']}&t={$this->topic['tid']}&st={$ibforums->input['st']}");
			}
		} else
		{
			return;
		}
	}
}

?>





