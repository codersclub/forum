<?php

/*
+--------------------------------------------------------------------------
|   Invision Board v1.2
|   ========================================
|   > mod_global_poll function library
|   > Module written by Koksi
|        > Koksi member at ibforen.de, ibplanet.de
|        > email: Koksi@ibforen.de
|
+--------------------------------------------------------------------------
|   > mod_global_poll Module Version Number: 1.2c (2003-08-18)
|   > <c> 2003 by Koksi
|                                             >русский перевод> bizzznesmen
+--------------------------------------------------------------------------
*/

class global_poll
{

	var $html = "";
	var $output = "";
	var $lang = "";
	var $parser = "";

	function getOutput()
	{

		global $ibforums, $std, $root_path;
		require ROOT_PATH . "sources/lib/post_parser.php";

		$this->parser = new post_parser();

		// We need the user's language:
		$this->lang_id = $ibforums->member['language'] == ""
			? ($ibforums->vars['default_language'] == ""
				? 'en'
				: $ibforums->vars['default_language'])
			: $ibforums->member['language'];
		if (file_exists($root_path . "lang/" . $this->lang_id . "/mod_global_poll_lang.php"))
		{
			$this->lang = $std->load_words($this->lang, 'mod_global_poll_lang', $this->lang_id);
		} else
		{
			die("Could not load required language file 'lang/$this->lang_id/mod_global_poll.php'");
		}

		$this->html = $std->load_template('mod_global_poll_skin');

		$stmt = $ibforums->db->query("SELECT starter_id, choices, votes, poll_question, tid
		    FROM ibf_polls
	            WHERE tid='" . $ibforums->vars['global_poll'] . "'");

		if ($stmt->rowCount() > 0)
		{
			$poll_data = $stmt->fetch();
			$this->output .= $this->get_poll($poll_data);
		}

		return $this->output;

	}

	function get_poll($poll_data)
	{

		global $ibforums, $std;

		$stmt = $ibforums->db->query("SELECT p.author_id,p.author_name,p.post,p.use_emo,
			    post_date,t.title,t.icon_id
		    FROM ibf_posts p
 		     LEFT JOIN ibf_topics t ON (t.tid=p.topic_id)
		    WHERE p.topic_id='" . $poll_data["tid"] . "' and p.new_topic=1 LIMIT 1");

		$poll_text = $stmt->fetch();

		$emoticon                = $poll_text['icon_id']
			? "<img src='" . $ibforums->vars['img_url'] . "/icon" . $poll_text['icon_id'] . ".gif' alt='' />&nbsp;"
			: "";
		$data                    = array(
			TEXT      => $poll_text["post"],
			SMILIES   => $poll_text['use_emo'],
			CODE      => 1,
			SIGNATURE => 0,
			HTML      => 1,
		);
		$header["poll_text"]     = $this->parser->prepare($data);
		$header["poll_question"] = $poll_data["poll_question"];
		$header["global_poll"]   = $this->lang["global_poll"];
		$header["header_text"]   = $emoticon . " " . $this->lang['poll_topic'] . " : <a href={$ibforums->base_url}showtopic=" . $poll_data["tid"] . "&view=getnewpost>" . $poll_text["title"] . "</a> " . $this->lang['by'] . " <a href={$ibforums->base_url}showuser=" . $poll_text['author_id'] . ">" . $poll_text['author_name'] . "</a>";
		$header["form_url"]      = $ibforums->base_url . "act=Poll&amp;t=" . $poll_data["tid"];

		$this->output .= $this->html->global_poll_table_header($header);

		// Have we voted in this poll?

		$stmt  = $ibforums->db->query("SELECT member_id from ibf_voters WHERE member_id='" . $ibforums->member['id'] . "' and tid='" . $ibforums->vars['global_poll'] . "'");
		$voter = $stmt->fetch();

		if ($voter['member_id'] != 0)
		{
			$check       = 1;
			$poll_footer = $this->lang['poll_you_voted'];
		}

		if (($poll_data['starter_id'] == $ibforums->member['id']) and ($ibforums->vars['allow_creator_vote'] != 1))
		{
			$check       = 1;
			$poll_footer = $this->lang['poll_you_created'];
		}

		if (!$ibforums->member['id'])
		{
			$check       = 1;
			$poll_footer = $this->lang['poll_no_guests'];
		}

		// is the topic locked?

		if ($this->topic['state'] == 'closed')
		{
			$check       = 1;
			$poll_footer = $this->lang['topic_locked'];
		}

		if ($check == 1)
		{
			// Show the results

			$poll_answers = unserialize(stripslashes($poll_data['choices']));
			reset($poll_answers);

			$total_votes = 0;

			foreach ($poll_answers as $entry)
			{
				$id     = $entry[0];
				$choice = $entry[1];
				$votes  = $entry[2];

				$total_votes += $votes;

				if (!$choice)
				{
					continue;
				}

				if ($ibforums->vars['poll_tags'])
				{
					$choice = $this->parser->parse_poll_tags($choice);
				}

				$percent = $votes == 0
					? 0
					: $votes / $poll_data['votes'] * 100;
				$percent = sprintf('%.2f', $percent);
				$width   = $percent > 0
					? (int)$percent * 2
					: 0;
				$html .= $this->html->Render_row_results($votes, $id, $choice, $percent, $width);
			}
			$total_votes = $this->lang['pv_total_votes'] . ": " . $total_votes;

			$html .= $this->html->show_total_votes($total_votes);

		} else
		{
			$poll_answers = unserialize(stripslashes($poll_data['choices']));
			reset($poll_answers);

			// Show poll form

			foreach ($poll_answers as $entry)
			{
				$id     = $entry[0];
				$choice = $entry[1];
				$votes  = $entry[2];

				if (!$choice)
				{
					continue;
				}

				if ($ibforums->vars['poll_tags'])
				{
					$choice = $this->parser->parse_poll_tags($choice);
				}

				$html .= $this->html->Render_row_form($votes, $id, $choice);
			}
			$poll_footer = "<input type='submit' name='submit'   value='{$this->lang['poll_add_vote']}' class='forminput'>&nbsp;" . "<input type='submit' name='nullvote' value='{$this->lang['poll_null_vote']}' class='forminput'>";
		}

		$html .= $this->html->global_poll_table_footer($poll_footer);

		return $html;
	}

}
