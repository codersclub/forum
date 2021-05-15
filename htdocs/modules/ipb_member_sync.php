<?php
/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2 Module File
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
|   > Member Sync Module File
|   > Module written by Matt Mecham
|   > Date started: 7th July 2003
|
+--------------------------------------------------------------------------
|
| USAGE:
| ------
|
| This module is designed to hold any module modifications to with registration
| It doesn't do much in itself, but custom code can be added to handle
| synchronization, etc.
|
| - on_create_account: Is called upon successful account creation
| - on_register_form: Is called when the form is displayed
| - on_login: Is called when logged in succcessfully
| - on_delete: Is called when member deleted (single, multiple)
| - on_email_change: When email address change is confirmed
| - on_profile_update: When profile is updated (msn, sig, etc)
| - on_pass_change: When password is updated
| - on_group_change: When the member's membergroup has changed
| - on_name_change: When the member's name has been changed
+--------------------------------------------------------------------------
*/

class ipb_member_sync
{
	var $class = "";

	function __construct()
	{

	}

	//-----------------------------------------------
	// register_class($class)
	//
	// Register a $this-> with this class
	//
	//-----------------------------------------------

	function register_class(&$class)
	{
		$this->class = $class;
	}

	//-----------------------------------------------
	// on_create_account($member)
	//
	// $member = array( 'id', 'name', 'email',
	// 'password', 'mgroup'...etc)
	//
	//-----------------------------------------------

	function on_create_account($member)
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

	//-----------------------------------------------
	// on_register_form()
	//
	//
	//-----------------------------------------------

	function on_register_form()
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

	//-----------------------------------------------
	// on_login()
	//
	// $member = array( 'id', 'name', 'email', 'pass')
	//           ...etc
	//-----------------------------------------------

	function on_login($member = array())
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

	//-----------------------------------------------
	// on_delete($ids)
	//
	// $ids = array | integer
	// If array, will contain list of ids
	//-----------------------------------------------

	function on_delete($ids = array())
	{
		global $std, $ibforums;

		$type = "";

		//---- START

		if (is_array($ids) and count($ids) > 0)
		{
			$type = 'arr';
		} else
		{
			$type = 'int';
		}

		//---- END
	}

	//-----------------------------------------------
	// on_email_change($id, $new_email)
	//
	// $id        = int member_id
	// $new_email = string new email address
	//-----------------------------------------------

	function on_email_change($id, $new_email)
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

	//-----------------------------------------------
	// on_pass_change($id, $new_raw)
	//
	// $id        = int member_id
	// $new_raw   = string new plain text password
	//-----------------------------------------------

	function on_pass_change($id, $new_raw)
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

	//-----------------------------------------------
	// on_profile_update($member)
	//
	// $member = array: avatar, avatar_size, aim_name
	// icq_number, location, website, yahoo, interests
	// integ_msg, msnname, id, name
	//
	//-----------------------------------------------

	function on_profile_update($member = array())
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

	function regex_count_choices()
	{

		++$this->poll_count;

		return "<br>";

	}

	//-----------------------------------------------
	// on_group_change()
	//
	// $id        = int member_id
	// $new_group = new int() group id
	//-----------------------------------------------

	function on_group_change($id, $new_group)
	{
		global $std, $ibforums;

		if ($new_group == $ibforums->vars['member_group'] and $ibforums->member['posts'] == 500)
		{
			$topic = array(
				'title'            => $ibforums->member['name'],
				'description'      => "приём в Клуб",
				'state'            => 'open',
				'posts'            => 0,
				'starter_id'       => 8617,
				'starter_name'     => "Forum_Bot",
				'start_date'       => time(),
				'last_poster_id'   => 8617,
				'last_poster_name' => "Forum_Bot",
				'last_post'        => time(),
				'icon_id'          => 0,
				'author_mode'      => 1,
				'poll_state'       => "open",
				'last_vote'        => 0,
				'views'            => 0,
				'forum_id'         => $ibforums->vars['club'],
				'approved'         => 1,
				'pinned'           => 0
			);

			$ibforums->db->insertRow("ibf_topics", $topic);

			$tid = $ibforums->db->lastInsertId();

			$message = "Профиль: [URL={$ibforums->base_url}showuser={$ibforums->member['id']}]{$ibforums->member['name']}[/URL]\n";

			$message .= "Посещемость в разделах: [URL={$ibforums->base_url}act=Profile&CODE=show_stat&MID={$ibforums->member['id']}]нажмите ссылку[/URL]\n";

			$message .= "Рейтинги:[List]";

			$message .= "[*][URL={$ibforums->base_url}act=rep&CODE=03&type=t&mid={$ibforums->member['id']}]полезный[/URL]: [b]" . intval($ibforums->member['rep']) . "[/b]\n";

			$message .= "[*][URL={$ibforums->base_url}act=rep&CODE=03&type=f&mid={$ibforums->member['id']}]флеймовый[/URL]: [b]" . intval($ibforums->member['ratting']) . "[/b][/List]\n";

			$message .= "Зарегистрирован на Форуме с " . $std->format_date_without_time($ibforums->member['joined']) . "\n";

			$message .= "Дата рождения: ";

			if ($ibforums->member['bday_month'])
			{
				$message .= $ibforums->member['bday_day'] . "." . $ibforums->member['bday_month'] . "." . $ibforums->member['bday_year'] . "\n";
			} else
			{
				$message .= "(нет информации)\n";
			}

			$message .= "Взыскания: ";

			$stmt = $ibforums->db->query("SELECT COUNT(wlog_id) as cnt FROM ibf_warn_logs WHERE wlog_mid='" . $ibforums->member['id'] . "' and wlog_type='neg'");

			$row = $stmt->fetch();

			if ($row['cnt'] == "0")
			{
				$message .= "нет\n";
			} else
			{
				$message .= "[URL={$ibforums->base_url}act=warn&CODE=view&mid={$ibforums->member['id']}]";
				$message .= "да, " . $row['cnt'] . " раз.";
				$message .= "[/URL]\n";
			}

			$message .= "\n[GM][URL={$ibforums->base_url}act=checker&CODE=club_enable&mid={$ibforums->member['id']}]Принять данного участника в группу &quot;Клуб&quot;![/URL]\n";

			$message .= "[b]Примечание[/b]: сообщение добавлено от имени председателя, с целью, чтобы последний мог видеть вышеприведённую ссылку.[/GM]\n";

			$message .= "The topic was created by [b]Forum_Bot[/b]\n";

			//		$message .= "\nI apologise for any inconvenience.";

			$post = array(
				'author_id'   => $ibforums->vars['club_boss'],
				'use_emo'     => 0,
				'ip_address'  => $ibforums->input['IP_ADDRESS'],
				'post_date'   => time(),
				'edit_time'   => time(),
				'icon_id'     => 0,
				'post'        => $message,
				'author_name' => "Forum_Bot",
				'forum_id'    => $ibforums->vars['club'],
				'topic_id'    => $tid,
				'queued'      => 0,
				'attach_id'   => "",
				'attach_hits' => 0,
				'attach_type' => "",
				'new_topic'   => 1,
			);

			$ibforums->db->insertRow("ibf_posts", $post);

			$poll_choices = "За<br>Против<br>Воздержался<br>";

			$poll_choices = preg_replace("/<br><br>/", "", $poll_choices);

			$poll_choices = preg_replace("/<br>/e", "\$this->regex_count_choices()", $poll_choices);

			$poll_array = array();

			$count = 0;

			$polls = explode("<br>", $poll_choices);

			foreach ($polls as $polling)
			{
				if (!$polling)
				{
					continue;
				}

				$poll_array[] = array($count, $polling, 0);

				$count++;
			}

			$life = 7;

			$life = time() + 60 * 60 * 24 * $life;

			$question = $ibforums->member['name'] . ": Согласны ли вы принять в Клуб данного участника Форума?";

			$poll = array(
				'tid'                  => $tid,
				'forum_id'             => $ibforums->vars['club'],
				'start_date'           => time(),
				'choices'              => addslashes(serialize($poll_array)),
				'starter_id'           => 8617,
				'votes'                => 0,
				'poll_question'        => $question,
				'is_multi_poll'        => 0,
				'multi_poll_min'       => 0,
				'multi_poll_max'       => 0,
				'is_weighted_poll'     => 0,
				'weighted_poll_places' => 0,
				'live_before'          => $life,
			);

			$ibforums->db->insertRow("ibf_polls", $poll);

			$ibforums->db->exec("UPDATE ibf_forums SET last_title='" . addslashes($topic['title']) . "',
						  last_id='" . $tid . "',
						  last_post='" . time() . "',
						  last_poster_name='Forum_Bot',
						  last_poster_id='8617',
						  topics=topics+1
			    WHERE id='" . $ibforums->vars['club'] . "'");

			$ibforums->db->exec("UPDATE ibf_stats SET TOTAL_TOPICS=TOTAL_TOPICS+1");

			$ibforums->db->exec("UPDATE ibf_members SET disable_group=1 WHERE id='" . $ibforums->member['id'] . "'");
		}

	}

	//-----------------------------------------------
	// on_name_change()
	//
	// $id        = int member_id
	// $new_group = new name
	//-----------------------------------------------

	function on_name_change($id, $new_name)
	{
		global $std, $ibforums;

		//---- START

		//---- END
	}

}


