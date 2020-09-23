<?php

/* ----------------------------------------------------------------------*\
  |   Quiz 1.0								 |
  |   (c) 2007 Valery Votintsev						 |
  |	Email: vot@sources.ru						 |
  |   http://www.sources.ru/						 |
  +------------------------------------------------------------------------+
  | Based on:								 |
  |    IBStore 2.5							 |
  |   (c) 2003 Zachary Anker						 |
  |	Email: wingzero1018@hotmail.com					 |
  |   http://www.subzerofx.com/shop/					 |
  +------------------------------------------------------------------------+
  | You may edit this file as long as you retain this Copyright notice.	 |
  | Redistribution not permitted without permission from Zachary Anker.	 |
  \*--------------------------------------------------------------------- */
use Views\View;

$quiz = new quiz;

class quiz
{

	var $output = "";
	var $temp_output = "";
	var $tempoutput = "";
	var $page_title = "";
	var $parser = "";
	var $mem_titles = array();
	var $club = 0;
	var $store_version = "2.5";
	var $nav = array();

	function quiz()
	{
		global $ibforums, $std, $print, $lib;

		//  if($ibforums->input['code'] != 'useitem')
		//  {
		$this->parser = new PostParser();

		//  }

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_quiz', $ibforums->lang_id);

		if (!$ibforums->vars['store_guest'] && $ibforums->member['id'] == 0)
		{
			$this->error("guest_cant_view");
		} else {
			if ($ibforums->vars['store_guest'] && $ibforums->member['id'] == 0)
			{
				//		$ibforums->input['code'] = "show";
			}
		}

		if (!$ibforums->vars['store_on'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("shop_offline");
		}

		switch ($ibforums->input['code'])
		{
			case 'quiz':
				$this->list_quiz();
				break;
			case 'open':
				$this->quiz_status('OPEN');
				break;
			case 'close':
				$this->quiz_status('CLOSED');
				break;
			case 'create':
				$this->create_quiz();
				break;
			case 'do_create':
				$this->do_create_quiz();
				break;
			case 'delete':
				$this->delete_quiz();
				break;
			case 'do_delete':
				$this->do_delete_quiz();
				break;
			case 'show':
				$this->show_quiz();
				break;
			case 'user_answers':
				$this->user_answers();
				break;
			case 'edit':
				$this->edit_quiz();
				break;
			case 'questions':
				$this->edit_questions();
				break;
			case 'update_quiz':
				$this->update_quiz();
				break;
			case 'update_questions':
				$this->update_questions();
				break;
			case 'take_quiz':
				$this->take_quiz();
				break;
			case 'do_take_quiz':
				$this->do_take_quiz();
				break;
			case 'quiz_results':
				$this->quiz_results();
				break;

			default:
				$this->list_quiz();
				break;
		}

		$out_put = "";

		// add all of are skin output

		$this->output .= View::make("quiz.end_page");

		if ($ibforums->vars['ibstore_safty'] == 1)
		{
			$temp = "
 if(confirm(type)) {
    return true;
 } else {
    return false;
 }";
		}

		$this->output = str_replace("<!--IBS.SAFTY_ON-->", $temp, $this->output);

		$out_put .= $this->output;

		$print->add_output($out_put);

		// do the output

		$print->do_output(array(
		                       'TITLE' => $this->page_title,
		                       'JS'    => 1,
		                       'NAV'   => $this->nav
		                  ));
	}

	//================================================
	//------------------------------------
	// Update the QUIZ settings
	//------------------------------------
	function quiz_status($status = 'OPEN')
	{
		global $ibforums, $std, $lib;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		// Check for the Admin access

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error('no_rights');
		}

		$query = "UPDATE ibf_quiz_info
		SET quiz_status='{$status}'
		WHERE q_id='{$qid}'
		LIMIT 1";

		$ibforums->db->query($query);

		$this->save_log("Quiz {$qid} {$status}.");

		$lib->redirect($msg, "act=quiz&code=show&quiz_id={$qid}", 1);
	}

	//---------------------------------------------
	// Do Delete the Quiz
	//---------------------------------------------
	function do_delete_quiz()
	{
		global $ibforums, $std, $print;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		/*
		  // Check for the member access

		  if($ibforums->member['id'] <= 0) {
		  $this->error('guest_cant_play');
		  }
		 */

		// Check for the Admin access

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error('no_rights');
		}

		$ibforums->db->exec("DELETE
		FROM ibf_quiz_info
		WHERE q_id='{$qid}' LIMIT 1");
		$ibforums->db->exec("DELETE
		FROM ibf_quiz
		WHERE quiz_id='{$qid}'");
		$this->save_log("Quiz ID " . $qid . " Was Deleted");

		$lib->redirect($msg, "act=quiz", 1);
		//  $ADMIN->done_screen("Quiz Deleted", "Administration CP Home", "act=index");
	}

	//---------------------------------------------
	// Edit the Quiz settings
	//---------------------------------------------
	function edit_quiz()
	{
		global $ibforums, $std, $print;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		/*
		  // Check for the member access

		  if($ibforums->member['id'] <= 0) {
		  $this->error('guest_cant_play');
		  }
		 */

		// Check for the Admin access

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error('no_rights');
		}

		//------------------------------
		// Get the Quiz settings

		$stmt = $ibforums->db->query("SELECT *
      	FROM ibf_quiz_info
      	WHERE
      		q_id='{$qid}'
      		{$extra}
      		LIMIT 1");

		if ($stmt->rowCount() == 0)
		{
			$this->error('cannot_find_quiz');
		}

		$settings = $stmt->fetch();

		if ($settings['quiz_status'] == 'OPEN')
		{
			$settings['quiz_status_open'] = 'checked=1';
		} else
		{
			$settings['quiz_status_closed'] = 'checked=1';
		}

		if ($settings['approved'])
		{
			$settings['quiz_approved_yes'] = 'checked=1';
		} else
		{
			$settings['quiz_approved_no'] = 'checked=1';
		}

		#foreach($settings as $k=>$v) echo $k."=".$v."<br>\n";

		$this->nav = array(
			"<a href='{$ibforums->base_url}act=quiz'>{$ibforums->lang['quiz_nav']}</a>",
			"<a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$qid}'>{$settings['quizname']}</a>",
			//			$ibforums->lang['takequiz_nav']
		);

		$this->page_title = $ibforums->lang['editquiz_nav'];

		$settings['items_won'] = explode("=", str_replace("|", "=", $settings['quiz_items']));

		//------------------------------
		// Get Item list for a Prize

		$stmt    = $ibforums->db->query("SELECT id,item_name,stock
	    FROM ibf_store_shopstock
	    WHERE stock > 0
	    ORDER BY item_name DESC");
		$items[] = array('none', 'No Items for Prize');

		if ($stmt->rowCount() >= 16)
		{
			$settings['items_rows'] = 16;
		} else
		{
			$settings['items_rows'] = $stmt->rowCount();
		}

		while ($r = $stmt->fetch())
		{
			if ($r['stock'])
			{
				$stock = "({$r['stock']} In Stock)";
			} else
			{
				$stock = "(Sold Out)";
			}
			$items[]     = array($r['id'], $r['item_name'] . ' ' . $stock);
			$item_name[] = $r['id'] . "=" . $r['item_name'];
		}

		if (is_array($item_name))
		{
			$item_name = implode("||", $item_name);
		}

		$settings['items']     = $items;
		$settings['item_name'] = $item_name;

		//  $settings[]( "quiz_items[]", $items, $settings['items_won'],$rows)
		//					 )      );
		//form_multiselect( "quiz_items[]", $items, $settings['items_won'],$rows);

		$this->output .= View::make("quiz.quiz_edit_header", ['settings' => $settings]);

		$this->output .= View::make("quiz.quiz_q_a_submit");
	}

	//------------------------------------
	// Update the QUIZ settings
	//------------------------------------
	function update_quiz()
	{
		global $ibforums, $lib;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		// Check for the Admin access

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error('no_rights');
		}

		$ibforums->input['quiz_post'] = str_replace("<br>", "\n", $ibforums->input['quiz_post']);
		$ibforums->input['quiz_desc'] = str_replace("<br>", "\n", $ibforums->input['quiz_desc']);

		$item_name = explode("||", $ibforums->input['item_names']);

		foreach ($item_name as $items)
		{
			if (!preg_match("#(.+)=(.+)#is", $items, $match))
			{
				continue;
			}
			$item_names[$match[1]] = $match[2];
		}

		if (is_array($ibforums->input['quiz_items']))
		{
			foreach ($ibforums->input['quiz_items'] as $q_item)
			{
				if ($q_item == "none")
				{
					//		unset($quiz_items);
					$quiz_items = array();
					break;
				}
				$quiz_items[] = $q_item . "=" . $item_names[$q_item];
			}

			$quiz_items = implode("|", $quiz_items);
		} else
		{
			$quiz_items = ($IN['quiz_items'] == "none")
				? ""
				: $IN['quiz_items'];
		}

		$query = "UPDATE ibf_quiz_info
		SET
			quizname='{$ibforums->input['quiz_name']}',
			quizdesc='{$ibforums->input['quiz_desc']}',
			post='{$ibforums->input['quiz_post']}',
			percent_needed='{$ibforums->input['perc_need']}',
			amount_won='{$ibforums->input['winnings']}',
			run_for='{$ibforums->input['q_run']}',
			let_only='{$ibforums->input['let_play']}',
			quiz_status='{$ibforums->input['quiz_status']}',
			timeout='{$ibforums->input['timeout']}',
			pending='{$ibforums->input['pending']}',
			quiz_items='{$quiz_items}'
		WHERE q_id='{$ibforums->input['quiz_id']}'
		LIMIT 1";

		$ibforums->db->exec($query);

		$this->save_log("Quiz " . $qid . " Settings Edited");

		$lib->redirect($msg, "act=quiz&code=show&quiz_id={$qid}", 1);
	}

	//------------------------------------
	// Update the QUIZ questions
	//------------------------------------
	function update_questions()
	{
		global $ibforums, $std, $lib;

		//echo "update_questions started.<br>";
		//foreach($ibforums->input as $k=>$v)
		//{
		//if($k != 'quiz_post') echo $k."=".$v. "<br>";
		//}
		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		// Check for the Admin access

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error('no_rights');
		}

		//  $ibforums->input['quiz_post'] = str_replace("<br>","\n",$ibforums->input['quiz_post']);
		//  $ibforums->input['quiz_desc'] = str_replace("<br>","\n",$ibforums->input['quiz_desc']);

		foreach ($ibforums->input as $field => $info)
		{

			if (!preg_match("#mid_(.+?)_type#", $field, $match))
			{
				continue;
			}

			$mid = $match[1];

			$question = addslashes(stripslashes($ibforums->input['q_' . $mid . '_question']));
			//    $question = str_replace("&lt;br&gt;","<br>",$question);
			$question = str_replace("<br>", "\n", $question);

			if ($info == 'single')
			{
				$answer = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer']));
			} else {
				if ($info == 'dropdown' || $info == 'multiq' || $info == 'radio' || $info == 'checkbox'
				)
				{
					$ibforums->input['q_' . $mid . '_answer_1'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_1']));
					$ibforums->input['q_' . $mid . '_answer_2'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_2']));
					$ibforums->input['q_' . $mid . '_answer_3'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_3']));
					$ibforums->input['q_' . $mid . '_answer_4'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_4']));
					$ibforums->input['q_' . $mid . '_answer_5'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_5']));
					$ibforums->input['q_' . $mid . '_answer_6'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_6']));
					$ibforums->input['q_' . $mid . '_answer_7'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_7']));
					$ibforums->input['q_' . $mid . '_answer_8'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_8']));
					$ibforums->input['q_' . $mid . '_answer_9'] = addslashes(stripslashes($ibforums->input['q_' . $mid . '_answer_9']));

					if (!$ibforums->input['q_' . $mid . '_1_correct'])
					{
						$ibforums->input['q_' . $mid . '_1_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_2_correct'])
					{
						$ibforums->input['q_' . $mid . '_2_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_3_correct'])
					{
						$ibforums->input['q_' . $mid . '_3_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_4_correct'])
					{
						$ibforums->input['q_' . $mid . '_4_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_5_correct'])
					{
						$ibforums->input['q_' . $mid . '_5_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_6_correct'])
					{
						$ibforums->input['q_' . $mid . '_6_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_7_correct'])
					{
						$ibforums->input['q_' . $mid . '_7_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_8_correct'])
					{
						$ibforums->input['q_' . $mid . '_8_correct'] = 0;
					}
					if (!$ibforums->input['q_' . $mid . '_9_correct'])
					{
						$ibforums->input['q_' . $mid . '_9_correct'] = 0;
					}

					$answer = "{answer1_" . $ibforums->input['q_' . $mid . '_1_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_1'] . '}||' . "{answer2_" . $ibforums->input['q_' . $mid . '_2_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_2'] . '}||' . "{answer3_" . $ibforums->input['q_' . $mid . '_3_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_3'] . '}||' . "{answer4_" . $ibforums->input['q_' . $mid . '_4_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_4'] . '}||' . "{answer5_" . $ibforums->input['q_' . $mid . '_5_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_5'] . '}||' . "{answer6_" . $ibforums->input['q_' . $mid . '_6_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_6'] . '}||' . "{answer7_" . $ibforums->input['q_' . $mid . '_7_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_7'] . '}||' . "{answer8_" . $ibforums->input['q_' . $mid . '_8_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_8'] . '}||' . "{answer9_" . $ibforums->input['q_' . $mid . '_9_correct'] . ":" . $ibforums->input['q_' . $mid . '_answer_9'] . '}';
				} else
				{
					if ($info == 'opinion')
					{
						$answer = "";
					}
				}
			}

			$question = str_replace("<br>", "\n", $question);
			$answer   = str_replace("<br>", "\n", $answer);

			$query = "UPDATE ibf_quiz
      	    SET
		question='{$question}',
		answer='{$answer}',
		type='{$info}'
      	    WHERE mid='{$mid}' LIMIT 1";

			//echo $query."<hr>\n";

			$ibforums->db->exec($query);
		}

		$this->save_log("Quiz " . $qid . " Questions Edited");

		$lib->redirect($msg, "act=quiz&code=show&quiz_id={$qid}", 1);
	}

	//---------------------------------------------
	// Edit the Quiz questions
	//---------------------------------------------
	function edit_questions()
	{
		global $ibforums, $std, $print;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);

		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		/*
		  // Check for the member access

		  if($ibforums->member['id'] <= 0) {
		  $this->error('guest_cant_play');
		  }
		 */

		// Check for the Admin access

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error('no_rights');
		}

		// Get the Quiz settings

		$stmt = $ibforums->db->query("SELECT *
      	FROM ibf_quiz_info
      	WHERE
      		q_id='{$qid}'
      		{$extra}
      		LIMIT 1");

		if ($stmt->rowCount() == 0)
		{
			$this->error('cannot_find_quiz');
		}

		$settings = $stmt->fetch();

		//foreach($settings as $k=>$v) echo $k."=".$v."<br>\n";

		$this->nav = array(
			"<a href='{$ibforums->base_url}act=quiz'>{$ibforums->lang['quiz_nav']}</a>",
			"<a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$qid}'>{$settings['quizname']}</a>",
			//			$ibforums->lang['takequiz_nav']
		);

		$this->page_title = $ibforums->lang['questions_nav'];

		$this->output .= View::make("quiz.quiz_question_header", ['settings' => $settings]);

		//--------------------------------
		// Get all the Quiz questions

		$stmt = $ibforums->db->query("SELECT *
      	FROM ibf_quiz
      	WHERE quiz_id='{$qid}'");

		if ($stmt->rowCount() <= 0)
				{
					$this->error("couldnotloadanswer");
				}

		//--------------------------------
		// Loop for all the questions

		$n = 0;

		while ($quiz = $stmt->fetch())
		{

			$quiz['question'] = stripslashes($quiz['question']);

			$this->output .= View::make("quiz.edit_question", ['nq' => $n, 'quiz' => $quiz]);
			$n++;
		}

		$this->output .= View::make("quiz.quiz_q_a_submit");
	}

	//---------------------------------------------
	// Show User Answers for the Quizs
	//
	//---------------------------------------------
	function user_answers()
	{
		global $ibforums, $std, $print;

		if (!$ibforums->member['g_is_supmod'])
		{
			$this->error("access_denied");
		}

		$qid = intval($ibforums->input['quiz_id']);

		$memberid = intval($ibforums->input['userid']);

		if (!$qid)
		{
			$this->error("cannot_find_quiz");
		}

		/*
		  if($ibforums->member['id'] <= 0) {
		  $this->error("guest_cant_play");
		  }

		 */

		// Get the Quiz settings

		$quiz = $this->get_quiz_settings($qid);

		if ($quiz['quiz_status'] == 'CLOSED')
		{
			$quiz['quiz_status'] = $ibforums->lang['quiz_is_closed'];
		} else
		{
			$quiz['quiz_status'] = $ibforums->lang['quiz_is_open'];
		}

		$quiz['quiz_desc'] = str_replace("\n", "<br>", $quiz['quiz_desc']);
		$quiz['post']      = str_replace("\n", "<br>", $quiz['post']);

		$this->nav = array(
			"<a href='{$ibforums->base_url}act=quiz'>{$ibforums->lang['quiz_nav']}</a>",
			"<a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$qid}'>{$quiz['quizname']}</a>",
			$ibforums->lang['user_answers']
		);

		$this->page_title = $ibforums->lang['user_answers'];

		// Get all the groups

		$this->mem_titles = $this->get_groups_settings();

		// Get all the member params

		$member = $this->get_member($memberid);

		// Get all the member answers for the Quiz

		$user_answers = $this->get_user_answers($memberid, $qid);

		//  $member['answers'] = stripslashes($user_answers['answers']);

		$member['time']         = $std->get_date($user_answers['time']);
		$member['time_took']    = $user_answers['time_took'];
		$member['amount_right'] = $user_answers['amount_right'];

		$answers = unserialize($user_answers['answers']);

		//foreach($answers as $k=>$v) echo $k."=".$v."<br>\n";

		$member['num_of_questons'] = count($answers);

		foreach ($answers as $num => $answer)
		{
			if (is_array($answer))
			{
				$answers[$num] = implode("\n", $answer);
			}
		}

		//foreach($user_answers as $k=>$v) echo $k."=".$v."<br>\n";
		// Get all the Quiz questions

		$quiz['questions'] = $this->get_quiz_questions($qid);

		// Count total/real questions

		$quiz['real_questions']  = 0;
		$quiz['total_questions'] = 0;

		foreach ($quiz['questions'] as $q)
		{

			//    foreach($q as $k=>$v) echo $k."=".$v."<br>";

			$quiz['total_questions']++;
			if ($q['type'] != 'opinion')
				$quiz['real_questions']++;
		}

		// Make Header

		$this->output .= View::make("quiz.quiz_u_a_header", ['settings' => $quiz, 'member' => $member]);

		// Loop for all the questions

		foreach ($quiz['questions'] as $q)
		{

			//echo $answers[$q['mid']]."<br>";

			$q['question'] = str_replace("\n", "<br>", $q['question']);

			$q['user_answer'] = $answers[$q['mid']];

			$this->output .= View::make("quiz.user_answer", ['info' => $q]);
		}
	}

	//--------------------------------
	// Get all the Quiz questions
	//--------------------------------
	function get_quiz_questions($qid = 0)
	{
		global $ibforums;

		$ans = array();

		if (!$qid)
			return $ans;

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz
		WHERE quiz_id='{$qid}'
		ORDER BY mid");

		if ($stmt->rowCount() <= 0)
			return $ans;
		//$this->error("couldnotloadanswer");
		// Loop for all the questions

		while ($quiz = $stmt->fetch())
		{
			//echo $quiz['question']."<br>";
			//    if(!$quiz['question']) continue;

			$quiz['question'] = stripslashes($quiz['question']);
			$quiz['answer']   = stripslashes($quiz['answer']);

			$ans[] = $quiz;
		}
		return $ans;
	}

	//---------------------------------------------
	// Get all the members answers for the Quiz
	//---------------------------------------------
	function get_user_answers($mid = 0, $qid = 0)
	{
		global $ibforums;
		$ans = array();

		if (!$qid)
			return $ans;
		if (!$mid)
			return $ans;

		$stmt = $ibforums->db->query("SELECT
			quiz_id,
			ip_address,
			time,
			time_took,
			amount_right,
			answers
		FROM ibf_quiz_winners
		WHERE
			quiz_id='{$qid}' AND
			memberid='{$mid}'
			LIMIT 1");

		if ($stmt->rowCount() == 0)
		{
			//      $this->error('yet_not_played_quiz');
			return $ans;
		}
		return $stmt->fetch();
	}

	//-------------------------------------
	// Get all the member parameters
	// Make additional member info
	//-------------------------------------

	function get_member($mid = 0)
	{
		global $ibforums;

		if ($mid)
		{
			$query = "SELECT
    		m.id, m.name, m.mgroup, m.email, m.joined,
    		m.avatar, m.avatar_size, m.posts, m.aim_name,
    		m.icq_number, m.signature,  m.website, m.yahoo,
    		m.integ_msg, m.title, m.hide_email, m.msnname,
    		m.warn_level, m.warn_lastwarn,
    		m.points,  m.fined, m.rep, m.ratting, m.show_ratting,
    		g.g_id, g.g_title, g.g_icon, g.g_use_signature,
    		g.g_use_avatar, g.g_dohtml,
    		s.id as s_id
    	  FROM ibf_members m
    	  LEFT JOIN ibf_sessions s
    		ON (s.member_id != 0 and
		    m.id=s.member_id and
                    s.login_type != 1)
    	  LEFT JOIN ibf_groups g
    		ON (g.g_id=m.mgroup)
    	  WHERE m.id='{$mid}'
          LIMIT 1
        ";

			//echo    $query."<br>\n";

			$res = $stmt = $ibforums->db->query($query);

			$member = $stmt->fetch($res);

			//  foreach($member as $k=>$v) echo $k."=".$v."<br>\n";
			//    $member = $this->parse_member( &$member );
			$member = $this->parse_member($member);
		} else
			$member['name'] = 'Guest';

		return $member;
	}

	//-------------------------------------
	// Get all the groups parameters
	//-------------------------------------
	function get_groups_settings()
	{
		global $ibforums;
		$row = array();

		$stmt = $ibforums->db->query("SELECT
  		id,
  		title,
  		pips,
  		posts
  	    FROM ibf_titles
  	    ORDER BY posts DESC");

		while ($i = $stmt->fetch())
		{
			$row[$i['id']] = array(
				'TITLE' => $i['title'],
				'PIPS'  => $i['pips'],
				'POSTS' => $i['posts'],
			);
		}
		return $row;
	}

	//-------------------------------------
	// Get the Quiz Settings
	//-------------------------------------
	function get_quiz_settings($qid = 0)
	{
		global $ibforums;
		$row = array();

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$extra = "AND pending='0'";
		}

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz_info
		WHERE
			q_id='{$qid}'
			{$extra}
			LIMIT 1");

		if ($stmt->rowCount() == 0)
		{
			//    $this->error("quiz_is_closed");
			return $row;
		}

		return $stmt->fetch();
	}

	//---------------------------------------------
	// Show the members results of a Quiz
	//---------------------------------------------
	function quiz_results()
	{
		global $ibforums, $std;

		$qid = intval($ibforums->input['quiz_id']);

		if (!$qid)
		{
			$this->error("cannot_find_quiz");
		}

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz_info
		WHERE q_id='{$qid}'
		LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$this->error("cannot_find_quiz");
		}

		$row = $stmt->fetch();

		//    if($row['quiz_status'] == 'CLOSED') $this->error("quiz_is_closed");

		$this->nav = array(
			"<a href='{$ibforums->base_url}act=quiz'>{$ibforums->lang['quiz_nav']}</a>",
			"<a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$qid}'>{$row['quizname']}</a>",
			$ibforums->lang['results_nav']
		);

		$this->page_title = $ibforums->lang['quiz_nav'];

		//    $row['quiz_desc'] = str_replace("\n","<br>",$row['quiz_desc']);
		//    $row['post'] = str_replace("\n","<br>",$row['post']);

		$this->output .= View::make("quiz.quiz_results_header", ['id' => $qid, 'title' => $row['quizname']]);

		// Get all playing members
		$stmt = $ibforums->db->query("SELECT f.*, m.name,m.id
    	        FROM ibf_quiz_winners f
    	        LEFT JOIN ibf_members m ON (m.id=f.memberid)
    	        WHERE f.quiz_id='{$ibforums->input['quiz_id']}'
    	        ORDER BY amount_right DESC");

		while ($member = $stmt->fetch())
		{
			$place++;

			$member['time'] = $std->get_date($member['time']);
			/*
			  if($member['time_took'] <= 0) {
			  $member['time_took'] = $ibforums->lang['results_lessthen'];
			  } else if($member['time_took'] == 1) {
			  $member['time_took'] = $member['time_took'].' '.$ibforums->lang['results_minute'];
			  } else {
			  $member['time_took'] = $member['time_took'].' '.$ibforums->lang['results_minutes'];
			  }
			 */

			$m = intval($member['time_took'] / 60);
			$s = $member['time_took'] - $m * 60;
			if (mb_strlen($s) < 2)
				$s = "0" . $s;

			$member['time_took'] = $m . ":" . $s;

			$this->output .= View::make("quiz.quiz_results_results", ['member' => $member, 'place' => $place]);
		}
	}

	//---------------------------------------------
	// Show the Quiz
	//---------------------------------------------
	function show_quiz()
	{
		global $ibforums;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);

		if (!$qid)
			$this->error("cannot_find_quiz");

		// Get the Quiz settings

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz_info
		WHERE q_id='{$qid}'
		LIMIT 1");

		if ($stmt->rowCount() == 0)
			$this->error("cannot_find_quiz");

		$row = $stmt->fetch();

		$mid = $row['starter_id'];

		//foreach($row as $k=>$v) echo $k."=".$v."<br>\n"; //$row['quizname'];

		if ($row['quiz_status'] == 'CLOSED')
		{
			$row['status'] = $ibforums->lang['quiz_is_closed'];
		} else
		{
			$row['status'] = $ibforums->lang['quiz_is_open'];
		}

		//----------------------------------------------
		// Update the Quiz views counter
		//----------------------------------------------

		$ibforums->db->exec("UPDATE ibf_quiz_info
    	    SET views=views+1
    	    WHERE q_id='{$qid}'");

		// Get all the groups

		$this->mem_titles = $this->get_groups_settings();

		//-------------------------------------
		// Get the quiz AUTHOR parameters
		//-------------------------------------

		if ($mid)
		{

			// Get the Quiz Author

			$query = "SELECT
      		m.id, m.name, m.mgroup, m.email, m.joined,
      		m.avatar, m.avatar_size, m.posts, m.aim_name,
      		m.icq_number, m.signature,  m.website, m.yahoo,
      		m.integ_msg, m.title, m.hide_email, m.msnname,
      		m.warn_level, m.warn_lastwarn,
      		m.points,  m.fined, m.rep, m.ratting, m.show_ratting,
      		g.g_id, g.g_title, g.g_icon, g.g_use_signature,
      		g.g_use_avatar, g.g_dohtml,
      		s.id as s_id
      	  FROM ibf_members m
      	  LEFT JOIN ibf_sessions s
      		ON (s.member_id != 0 and m.id=s.member_id and s.login_type != 1)
      	  LEFT JOIN ibf_groups g
      		ON (g.g_id=m.mgroup)
      	  WHERE m.id='{$mid}'
  	  LIMIT 1
  	";

			$res = $stmt = $ibforums->db->query($query);

			$member = $stmt->fetch($res);

			//    $member = $this->parse_member( &$member );
			$member = $this->parse_member($member);
		} else
		{
			$member['name'] = 'Guest';
		}

		$this->nav = array("<a href='{$ibforums->base_url}act=quiz'>{$ibforums->lang['quiz_nav']}</a>");

		$this->page_title = $row['quizname'] . " - " . $ibforums->lang['quiz_nav'];

		$row['show_results'] = "<input type='button' value='{$ibforums->lang['show_results']}' onclick='document.location=\"{$ibforums->base_url}act=quiz&code=quiz_results&quiz_id={$row['q_id']}\";'>";

		$row['take_quiz'] = "<input type='button' value='{$ibforums->lang['take_quiz']}' onclick='document.location=\"{$ibforums->base_url}act=quiz&code=take_quiz&quiz_id={$row['q_id']}&time={$row['timeout']}\";'>";

		//echo $row['quiz_status']."<br>";
		//echo $row['status']."<br>";

		$row['actions'] = "";

		// Check for the Admin access

		if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group'])
		{

			if ($row['quiz_status'] == 'CLOSED')
			{
				$row['actions'] = "<a href='{$ibforums->base_url}act=quiz&code=open&quiz_id={$row['q_id']}'>{$ibforums->lang['quiz_open']}</a>";
			} else
			{
				$row['actions'] = "<a href='{$ibforums->base_url}act=quiz&code=close&quiz_id={$row['q_id']}'>{$ibforums->lang['quiz_close']}</a>";
			}
			$row['actions'] .= " &middot; <a href='{$ibforums->base_url}act=quiz&code=edit&quiz_id={$row['q_id']}'>{$ibforums->lang['quiz_edit']}</a>";
			$row['actions'] .= " &middot; <a href='{$ibforums->base_url}act=quiz&code=questions&quiz_id={$row['q_id']}'>{$ibforums->lang['quiz_questions']}</a>";
		}

		//-------------------------------
		// Check for Guests can't play

		if (!$ibforums->member['id'])
		{
			$row['take_quiz'] = "";
		}

		//-------------------------------------------
		// Check for the time for this Quiz is actual

		if ($quiz['run_for'])
		{

			$days          = 86400 * $quiz['run_for'];
			$row['status'] = $row['started_on'] + $days;
			$row['status'] -= time();
			$row['status_days'] = round($row['status'] / 86400);
		}

		//-------------------------------
		// Check for players limit

		$close_check = $this->quiz_check($row['q_id'], $row['let_only']);

		if ($close_check)
		{
			if ($close_check['status'])
			{
				$row['take_quiz'] = $ibforums->lang['played_quizalready'];
			}

			if ($close_check['status'] == 'close')
			{
				$row['take_quiz'] = "";
			}
		}

		if ($row['quiz_status'] == 'CLOSED')
		{
			//      $row['take_quiz'] = $ibforums->lang['quiz_closed'];
			$row['take_quiz'] = "";
		}

		// Close the Quiz if the actual time exhausted

		if ($quiz['run_for'])
		{
			if ($row['status_days'] <= 0)
			{
				$row['status_days'] = 0;
			}

			if ($row['quiz_status'] != 'CLOSED' && $row['status_days'] <= 0)
			{
				$stmt = $ibforums->db->exec("UPDATE ibf_quiz_info
  			    SET quiz_status='CLOSED'
  			    WHERE q_id='{$row['q_id']}'
  			    LIMIT 1");
			}
			$row['quiz_status'] = 'CLOSED';
		}

		$row['quiz_status'] = ucfirst(mb_strtolower($row['quiz_status']));

		//    $row['amount_won'] = $std->do_number_format($row['amount_won']);

		if ($ibforums->vars['showplaysleft'])
		{
			if ($close_check['plays_left'] < 1)
			{
				$close_check['plays_left'] = $ibforums->lang['none_played'];
			}

			$this->output = str_replace("<!--Plays Left Header-->", View::make("quiz.plays_left_header"), $this->output);
			$this->output = str_replace("<!--Plays Left Middle-->",
				View::make("quiz.plays_left_middle", ['plays' => $close_check['plays_left']]), $this->output);
		}

		$row['post'] = str_replace("\r", "", $row['post']);
		$row['post'] = str_replace("\n", "<br>", $row['post']);

		$this->output .= View::make("quiz.quiz_show", ['post' => $row, 'author' => $member]);
	}

	//---------------------------------------------
	// List Quizs
	//---------------------------------------------
	function list_quiz()
	{
		global $ibforums, $std, $print;

		$this->nav = array("<a href='" . $ibforums->base_url . "act=quiz'>{$ibforums->lang['quiz_nav']}</a>");

		$this->page_title = $ibforums->lang['quiz_nav'];

		/* Have to add moderator!!!
		  // moderators of current forum
		  $this->forum['moderators'] = $this->get_moderators();

		  if ( $ibforums->member['g_is_supmod'] ) $this->mod = 1;
		 */

		$quiz_button = "<a href='{$ibforums->base_url}act=quiz&code=create'>{$ibforums->lang['create_quiz']}</a>";
		;

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$quiz_button = "";
		}

		$this->output .= View::make(
			"quiz.quiz_header",
			[
				'data' => array(
					'name' => $ibforums->lang['quiz_nav'],
					'last_column' => '',
					'QUIZ_BUTTON' => $quiz_button
				)
			]
		);

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$extra = "WHERE pending='0' AND approved='1'";
		}

		// Get all the Quiz

		$list_quiz = $stmt = $ibforums->db->query("SELECT *
    			 FROM ibf_quiz_info " . $extra);

		while ($quiz = $stmt->fetch($list_quiz))
		{
			//      $quiz['starter'] = ??;
			//echo "1. ".$quiz['quiz_status']."<br>";

			if ($quiz['run_for'])
			{
				$days           = 86400 * $quiz['run_for'];
				$quiz['status'] = $quiz['started_on'] + $days;
				$quiz['status'] -= time();
				$quiz['status_days'] = round($quiz['status'] / 86400);
			}
			//echo "2. ".$quiz['quiz_status']."<br>";

			$quiz['take_quiz'] = "<a href='{$ibforums->base_url}act=quiz&code=take_quiz&quiz_id={$quiz['q_id']}&time={$quiz['timeout']}'>{$ibforums->lang['take_quiz']}</a>";
			$quiz['show_results'] .= "<a href='{$ibforums->base_url}act=quiz&code=quiz_results&quiz_id={$quiz['q_id']}'>{$ibforums->lang['show_results']}</a>";

			if (!$ibforums->member['id'])
			{
				$quiz['take_quiz'] = "";
			}

			$close_check = $this->quiz_check($quiz['q_id'], $quiz['let_only']);

			if ($close_check)
			{

				if ($close_check['status'])
					$quiz['take_quiz'] = $close_check['status']
						? $ibforums->lang['played_quizalready']
						: $quiz['take_quiz'];

				if ($close_check['status'] == 'close')
				{
					//     	  $quiz['take_quiz'] = $ibforums->lang['quiz_closed'];
					$quiz['take_quiz'] = '';
				}
			}
			//echo "3. ".$quiz['quiz_status']."<br>";

			if ($quiz['quiz_status'] == 'CLOSED')
			{
				$quiz['take_quiz'] = $ibforums->lang['quiz_closed'];
			}

			if ($quiz['run_for'])
			{
				if ($quiz['quiz_status'] != 'CLOSED' && $quiz['status_days'] <= 0)
				{
					$ibforums->db->exec("UPDATE ibf_quiz_info
  			      SET quiz_status='CLOSED'
  			      WHERE q_id='{$quiz['q_id']}' LIMIT 1");
				}
			}

			//echo "4. ".$quiz['quiz_status']."<br>";
			if ($quiz['status_days'] <= 0)
			{
				$quiz['status_days'] = 0;
			}

			$quiz['quiz_status'] = ucfirst(mb_strtolower($quiz['quiz_status']));
			$quiz['amount_won']  = $std->do_number_format($quiz['amount_won']);
			$this->output .= View::make("quiz.list_quiz", ['data' => $quiz]);

			if ($ibforums->vars['showplaysleft'])
			{
				if ($close_check['plays_left'] < 1)
				{
					$close_check['plays_left'] = $ibforums->lang['none_played'];
				}

				$this->output = str_replace("<!--Plays Left Header-->",
					View::make("quiz.plays_left_header"), $this->output);
				$this->output = str_replace("<!--Plays Left Middle-->",
					View::make("quiz.plays_left_middle", ['plays' => $close_check['plays_left']]), $this->output);
			}
		}
	}

	//---------------------------------------------
	// Take Quizs
	// This is the code that drove me crazy
	//---------------------------------------------
	function take_quiz()
	{
		global $ibforums, $std, $print;

		// Check for the Quiz ID

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error('cannot_find_quiz');
		}

		// Check for the member ID

		if ($ibforums->member['id'] <= 0)
		{
			$this->error('guest_cant_play');
		}

		// Check for the Admin access

		$extra = "";
		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			//      $extra = "AND pending='0'";
			$extra = "AND approved='1'";
		}

		// Get the Quiz settings

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz_info
		WHERE
			q_id='{$qid}'
			{$extra}
			LIMIT 1");

		if ($stmt->rowCount() == 0)
		{
			$this->error('cannot_find_quiz');
		}

		$settings = $stmt->fetch();

		//foreach($settings as $k=>$v) echo $k."=".$v."<br>\n";

		$this->nav = array(
			"<a href='{$ibforums->base_url}act=quiz'>{$ibforums->lang['quiz_nav']}</a>",
			"<a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$qid}'>{$settings['quizname']}</a>",
			//			$ibforums->lang['takequiz_nav']
		);

		$this->page_title = $ibforums->lang['takequiz_nav'];

		if ($settings['quiz_status'] == 'CLOSED')
			$this->error("quiz_is_closed");

		$settings['time'] = time();

		// Check for the member played allready

		$stmt = $ibforums->db->query("SELECT 1
		FROM ibf_quiz_winners
		WHERE
			quiz_id='{$qid}' AND
			memberid='{$ibforums->member['id']}'
			LIMIT 1");
		if ($stmt->rowCount() > 0)
		{
			$this->error("played_quiz_already");
		}

		$settings['quiz_desc'] = str_replace("\n", "<br>", $settings['quiz_desc']);
		$settings['post']      = str_replace("\n", "<br>", $settings['post']);

		$this->output .= View::make("quiz.quiz_q_a_header", ['settings' => $settings]);

		//--------------------------------
		// Get all the Quiz questions

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz
		WHERE quiz_id='{$qid}'");

		if ($stmt->rowCount() <= 0)
			$this->error("couldnotloadanswer");

		//echo "rows: ".$stmt->rowCount()."<br>\n";

		$n = 0;

		// Loop for all the questions

		while ($quiz = $stmt->fetch())
		{

			//echo $n." type:". $quiz['type']."<br>\n";

			$quiz['question'] = stripslashes($quiz['question']);

			$quiz['question'] = str_replace("\n", "<br>", $quiz['question']);

			//echo $n.". ".$quiz['question']."<br>\n";
			//echo $n.". ".$quiz['answer']."<br>\n";
			$q = $quiz['question'];
			//      $q = $this->postparse($q);
			//      $quiz['question'] = $this->postparse($quiz['question']);
			/*
			  $q = $this->parser->prepare( array(
			  'TEXT'    => $q,
			  'SMILIES' => 1,
			  'CODE'    => 1,
			  'HTML'    => 0
			  )      );
			 */

			//----------------------
			// SINGLE or MultyQ

			if ($quiz['type'] == 'single' || $quiz['type'] == 'multiq')
			{
				if (!$quiz['answer'] || !$quiz['question'])
					continue;
				$quiz['answer'] = stripslashes($quiz['answer']);
				$n++;
				//echo $n.". ".$quiz['answer']."<br>\n";
				$this->output .= View::make("quiz.single_question", ['num' => $n, 'info' => $quiz]);
				//echo "single.<br>\n";
			} //----------------------
			// DROPDOWN

			else if ($quiz['type'] == 'dropdown')
			{
				//echo "dropdown.<br>\n";
				$quiz['dropdown'] = "";
				$quiz['dropdown'] .= "<select name='uanswer_{$quiz['mid']}'>\n
    				<option value='---'>----</option>";

				$answers = explode("||", $quiz['answer']);

				foreach ($answers as $answer)
				{
					//echo $n.". ".$answer."<br>\n";
					if (!preg_match("#{answer([1-9])_.+?:(.+)}#is", $answer, $match))
						continue;
					$match[2] = stripslashes($match[2]);
					$quiz['dropdown'] .= "\n<option value='{$match[2]}'>" . $match[2] . "</option>";
				}

				$quiz['dropdown'] .= "</select>";
				//    	$quiz['question'] = stripslashes($quiz['question']);
				$n++;
				$this->output .= View::make("quiz.dropdown_question", ['num' => $n, 'info' => $quiz]);
			} //----------------------
			// RADIO

			else if ($quiz['type'] == 'radio')
			{
				//echo "radio.<br>\n";
				$quiz['dropdown'] = "";

				$answers = explode("||", $quiz['answer']);

				$i = 0;

				foreach ($answers as $answer)
				{
					$i++;
					if (!preg_match("#{answer([1-9])_.+?:(.+)}#is", $answer, $match))
						continue;
					$match[2] = stripslashes($match[2]);
					$quiz['dropdown'] .= "\n<input type='radio' name='uanswer_{$quiz['mid']}' value='{$match[2]}'> " . $match[2] . "<br>";
				}

				//    	$quiz['question'] = stripslashes($quiz['question']);
				$n++;
				$this->output .= View::make("quiz.dropdown_question", ['num' => $n, 'info' => $quiz]);
			} //----------------------
			// CHECKBOX

			else if ($quiz['type'] == 'checkbox')
			{
				//echo "checkbox.<br>\n";
				$quiz['dropdown'] = "";

				$answers = explode("||", $quiz['answer']);

				$i = 0;

				foreach ($answers as $answer)
				{
					$i++;

					if (!preg_match("#{answer([1-9])_(.+?):(.+)}#is", $answer, $match))
						continue;

					$match[3] = stripslashes($match[3]);
					$quiz['dropdown'] .= "\n<input type='checkbox' name='uanswer_{$quiz['mid']}_{$i}' value='{$match[3]}'> " . $match[3] . "<br>";
				}

				//    	$quiz['question'] = stripslashes($quiz['question']);
				$n++;
				$this->output .= View::make("quiz.dropdown_question", ['num' => $n, 'info' => $quiz]);
			} //----------------------
			// OPINION

			else if ($quiz['type'] == 'opinion')
			{
				//echo "opinion.<br>\n";
				//echo $n.". ".$quiz['question']."<br>\n";
				//echo $n.". ".$quiz['answer']."<br>\n";
				if (!$quiz['question'])
					continue;

				//    	$quiz['question'] = stripslashes($quiz['question']);
				$n++;
				$this->output .= View::make("quiz.opinion_question", ['num' => $n, 'info' => $quiz]);
			} else
			{
				//echo "???.<br>\n";
			}
		}

		$this->output .= View::make("quiz.quiz_q_a_submit");
	}

	//---------------------------------------------
	// Check Quizs Questions and Answers
	//---------------------------------------------

	function do_take_quiz()
	{
		global $ibforums, $std, $print, $lib, $HTTP_POST_VARS;

		$debug = 0; // For debug purposes !!!
		// 0 = no debug messages;
		// 1 = show correct/wrong answers
		// 2 = answers + form variables

		if ($ibforums->member['id'] <= 0)
		{
			$this->error("guest_cant_play");
		}

		$qid = intval($ibforums->input['quiz_id']);
		if (!$qid)
		{
			$this->error("cannot_find_quiz");
		}

		$stmt = $ibforums->db->query("SELECT 1
		FROM ibf_quiz_winners
		WHERE quiz_id='{$qid}' AND
		      memberid='{$ibforums->member['id']}'
		LIMIT 1");

		if ($stmt->rowCount() > 0)
		{
			$this->error("quiz_playedalready");
		}

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz_info
		WHERE q_id='{$qid}'
		LIMIT 1");

		if ($stmt->rowCount() <= 0)
			$this->error('cannot_find_quiz');

		$quiz = $stmt->fetch();

		if ($quiz['quiz_status'] == 'CLOSED')
		{
			$this->error("quiz_is_closed");
		}

		//---------------------------------
		// Get User Answers from the Form

		foreach ($HTTP_POST_VARS as $field => $value)
		{
			if ($debug > 1)
				echo $field . "=" . $value . "<br>\n";

			if (!preg_match("#uanswer_([^_]+)_*(\d+)*#is", $field, $match))
				continue;
			if ($match['2'])
			{
				$temp_answer[$match['1']][$match['2']] = $value;
				if ($debug > 1)
					echo "temp_answer[" . $match['1'] . "][" . $match['2'] . "]=" . $value . "<br>\n";
			} else
			{
				$temp_answer[$match['1']] = $value;
				if ($debug > 1)
					echo "temp_answer[" . $match['1'] . "]=" . $value . "<br>\n";
			}
		}

		$serial_answer = addslashes(serialize($temp_answer));

		if ($debug > 1)
			echo "serial=" . $serial_answer . "<br>\n";

		$correct         = 0;
		$total_questions = 0;

		//---------------------------------
		// Get All the Qestions for this Quiz
		//---------------------------------

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_quiz
		WHERE quiz_id='{$qid}'
		ORDER BY mid");

		while ($check = $stmt->fetch())
		{
			$user_answer    = $temp_answer[$check['mid']];
			$user_answer    = preg_replace("/[\r\n]/", "", $temp_answer[$check['mid']]);
			$correct_answer = $check['answer'];
			$question_num   = $check['mid'];

			if ($debug > 1)
				echo "quiz_answer [{$check['type']}] {$question_num}:<br><b>" . $check['answer'] . "</b><br>\n";

			// Single Answer --------------------

			if ($check['type'] == 'single')
			{
				if ($debug)
					echo "{$question_num}. $correct_answer [1]. ";
				if ($debug)
					echo "user_answer {$question_num} = <b>{$user_answer}</b> ";

				if ($this->tag_convert($user_answer) === $this->tag_convert($correct_answer))
				{
					$correct++;
					if ($debug)
						echo "CORRECT.<br>\n";
				} else if ($debug)
					echo "WRONG.<br>\n";
			} // Multi Answers - Single correct --------------------

			else if ($check['type'] == 'dropdown' || $check['type'] == 'multiq' || $check['type'] == 'radio'
			)
			{
				$answers = explode("||", $correct_answer);

				foreach ($answers as $answer)
				{
					if (!preg_match("#{answer(.+?)_(.+?):(.+)}#is", $answer, $match))
						continue;

					if ($debug)
						echo "{$question_num}.{$match[1]}. {$match[3]} [{$match[2]}]";

					//    	  if(($match[2] == 1) && ($this->tag_convert($user_answer) === $this->tag_convert($match[3])))
					//    	  if($this->tag_convert($user_answer) == $this->tag_convert($match[3]) && ($match[2] == 1))

					if (($this->tag_convert($user_answer) === $this->tag_convert($match[3])))
					{
						if ($debug)
							echo " user_answer {$question_num} =<b>{$user_answer}</b> ";
						if ($match[2])
						{
							$correct++;
							if ($debug)
								echo "  CORRECT";
						} else
						{
							if ($debug)
								echo "  WRONG";
						}
					}
					//else echo "The user answer is wrong.<br>\n";

					if ($debug)
						echo "<br>\n";
				}
				if ($debug)
					echo "correct_answers={$correct}<br>\n";
			} // Multi Answers - Multi correct --------------------

			else if ($check['type'] == 'checkbox')
			{
				$answers = explode("||", $correct_answer);

				/*  Алгоритм для варианта CHECKBOX:
				  Сумма_ответов_юзера = 0.
				  Сумма_правильных_ответов = кол-во ответов с "1" на конце (answer_n_1).
				  Цикл по всем вариантам ответа
				  Если на очередном_ответе нажата галка
				  Если очередной_ответ является правильным
				  то Сумма_ответов_юзера ++
				  Иначе
				  то Сумма_ответов_юзера --
				  КонецЕсли
				  КонецЕсли
				  конец цикла

				  Если сумма_правильных_ответов == сумма_ответов_юзера
				  то юзер ответил ПРАВИЛЬНО.
				  в противном случае
				  юзер ответил НЕВЕРНО.
				  КонецЕсли
				 */

				$user_score     = 0;
				$right_answers  = 0;
				$answer_clicked = array();

				// Loop for all Checkbox answers

				foreach ($answers as $answer)
				{
					if (!preg_match("#{answer(.+?)_(.+?):(.+)}#is", $answer, $match))
						continue;

					if ($debug)
						echo "{$question_num}.{$match[1]}. {$match[3]} [{$match[2]}]";

					$question_number = $match[1];

					if ($match[2]) // This is a correct answer
					{
						$right_answers++;

						if ($this->tag_convert($temp_answer[$question_num][$question_number]) === $this->tag_convert($match[3]))
						{
							$user_score++;
							if ($debug)
								echo " user_answer {$question_num} =<b>{$temp_answer[$question_num][$question_number]}</b> CORRECT";
							if ($debug)
								echo " user_score= {$user_score}";
						}
					} else // This is a non-correct answer
					{
						if ($this->tag_convert($temp_answer[$question_num][$question_number]) === $this->tag_convert($match[3]))
						{
							$user_score--;
							if ($debug)
								echo " user_answer {$question_num} =<b>{$temp_answer[$question_num][$question_number]}</b> WRONG";
							if ($debug)
								echo " user_score= {$user_score}";
						}
					}

					if ($debug)
						echo "<br>\n";
				}

				if ($right_answers > 0 && $right_answers == $user_score)
				{
					$correct++;
				}

				if ($debug)
					echo " quiz right_answers={$right_answers}<br>\n";
				if ($debug)
					echo " user_score={$user_score}<br>\n";
				if ($debug)
					echo " total correct_answers={$correct}<br>\n";
			} // User Opinion - Not checked user answer --------------------

			else if ($check['type'] == 'opinion')
			{
				if ($debug)
					echo "{$question_num}. Opinion:<br>\n";
				//        if($debug) echo "<b>".addslashes($temp_answer[$question_num])."</b><br>\n";
				$temp_answer[$question_num] = str_replace("\r", "", $temp_answer[$question_num]);
				$temp_answer[$question_num] = str_replace("\n", "<br>", $temp_answer[$question_num]);
				if ($debug)
					echo "<b>" . $temp_answer[$question_num] . "</b><br>\n";
				if ($debug)
					echo "SKIPPED.<br>\n";
			}

			if ($check['type'] != 'opinion')
				$total_questions++;

			if ($debug)
				echo "<hr>\n";
		}

		//--------------------------------
		// Count the Quiz playing results

		$correct_percent = round($correct / $total_questions * 100);

		if ($debug)
			echo "total_questions={$total_questions}<br>\n";
		if ($debug)
			echo "correct_answers={$correct}<br>\n";
		if ($debug)
			echo "correct_percent={$correct_percent}<br>\n";

		$quiz['percent_needed'] = round($quiz['percent_needed']);
		$quiz['percent_needed'] = str_replace("%", "", $quiz['percent_needed']);
		$quiz['percent_needed'] = str_replace(",", "", $quiz['percent_needed']);

		if ($correct_percent > 100)
		{
			$correct_percent = 100;
		}

		$time = time();
		//    $time_took = floor(($time - $ibforums->input['starttime'])/60);
		$time_took = intval($ibforums->input['starttime']);

		if ($debug)
			exit;

		//--------------------------------
		// Store all the user answers

		$ibforums->db->exec("INSERT INTO ibf_quiz_winners
		SET
		quiz_id='{$ibforums->input['quiz_id']}',
		memberid='{$ibforums->member['id']}',
		ip_address='{$ibforums->input['IP_ADDRESS']}',
		time='{$time}',
		time_took='{$time_took}',
		amount_right='{$correct}',
		answers='{$serial_answer}'
		");

		//---------------------------
		// Check for WIN!

		if ($correct_percent >= $quiz['percent_needed'])
		{
			//----------------------------------
			// Give him an Item Prize

			if ($quiz['quiz_items'])
			{
				$quiz_items = explode("|", $quiz['quiz_items']);

				// HAVE TO CHECK - If the item NOT sold!!!

				foreach ($quiz_items as $items)
				{
					if (!preg_match("#(.+)=(.+)#is", $items, $match))
						continue;
					$ibforums->db->exec("INSERT INTO ibf_store_inventory
			(i_id,owner_id,item_id,price_payed)
		      VALUES (
			'',
			'{$ibforums->member['id']}',
			'{$match[1]}',
			'{$quiz['amount_won']}'
			)");

					$items_gotten[] = $match[2];
				}
				$extra = str_replace("<#ITEMS#>", implode(", ", $items_gotten), '<br><br>' . $ibforums->lang['quiz_items_gotten']);
				//    	$extra_location = "code=inventory";
				$extra_location = "code=quiz_inventory";
			} else
				//		$extra_location = "code=quiz";
				$extra_location = "code=quiz_results&quiz_id=" . $qid;

			//----------------------------------
			// Give him a DigiMoney Prize
			// CHECK - If Money Prize > 0
			if ($quiz['amount_won'])
			{
				$ibforums->member['points'] += $quiz['amount_won'];
				$ibforums->db->exec("UPDATE ibf_members
  		  SET points='{$ibforums->member['points']}'
  		  WHERE id='{$ibforums->member['id']}'
  		  LIMIT 1");
				$msg = $ibforums->lang['quiz_winner'] . $extra;
			}
		} else
			$msg = $ibforums->lang['quiz_notenoughtcorrect'];

		$msg = str_replace("<CORRECT_PERCENT>", $correct_percent, $msg);
		$msg = str_replace("<CORRECT_NUMBER>", $correct, $msg);
		$msg = str_replace("<TOTAl_QUESTIONS>", $total_questions, $msg);
		$msg = str_replace("<WIN_AMOUNT>", $quiz['amount_won'], $msg);
		$msg = str_replace("<QUIZ_PERCENT_NEEDED>", $quiz['percent_needed'], $msg);

		// !!! MAY BE - Redirect to the Quiz results???
		//    https://devel/index.php?act=quiz&code=quiz_results&quiz_id=1

		$lib->redirect($msg, "act=quiz&" . $extra_location, 1);
	}

	//---------------------------------------------
	// Two Misc functions used for the quiz
	//---------------------------------------------
	function quiz_check($quiz_id, $let_play)
	{
		global $ibforums;

		// Get the Quiz players

		$stmt   = $ibforums->db->query("SELECT
			quiz_id,
			memberid,
			ip_address,
			time,
			time_took,
			amount_right,
			answers
		FROM ibf_quiz_winners
		WHERE quiz_id='{$quiz_id}'");
		$played = $stmt->rowCount();

		// Check for players limit

		if ($played >= $let_play && $let_play != 0)
		{
			$ibforums->db->exec("UPDATE ibf_quiz_info
		  SET quiz_status='CLOSED'
        	  WHERE q_id='{$quiz_id}' LIMIT 1");
			return array(
				'status'     => 'close',
				'plays_left' => $played
			);
		}

		while ($temp = $stmt->fetch())
		{
			if ($temp['memberid'] == $ibforums->member['id'])
			{
				return array('status' => 'open', 'plays_left' => $played);
			}
		}
		return array('status' => false, 'plays_left' => $played);
	}

	//---------------------------------------------
	// Parse and UnParse
	//---------------------------------------------
	function postparse($msg)
	{
		//    $msg = $this->parser->convert( array(
		echo "postparse started.<br>";

		$msg = $this->parser->prepare(array(
		                                   'TEXT'    => $msg,
		                                   'SMILIES' => 1,
		                                   'CODE'    => 1,
		                                   'HTML'    => 0
		                              ));
		echo "postparse msg='" . $msg . "'<br>";
		return $msg;
	}

	/*
	  $this->topic['rules_title'] = trim( $this->parser->prepare(
	  array (
	  'TEXT'          => $this->topic['rules_title'],
	  'SMILIES'       => 1,
	  'CODE'          => 1,
	  'SIGNATURE'     => 0,
	  'HTML'          => 0,
	  )	)  );

	  $this->topic['rules_text']  = trim( $this->parser->prepare(
	  array (
	  'TEXT'          => $this->topic['rules_text'],
	  'SMILIES'       => 1,
	  'CODE'          => 1,
	  'SIGNATURE'     => 0,
	  'HTML'          => 0,
	  )	)  );

	  $data = array(
	  TEXT          => $row['post'],
	  SMILIES       => $row['use_emo'],
	  CODE          => 1,
	  SIGNATURE     => 0,
	  HTML          => 1,
	  HID	      => $this->highlight,
	  TID	      => $this->topic['tid'],
	  MID	      => $row['author_id'],
	  );

	  $row['post'] = $this->parser->prepare($data);

	  $data = array(
	  TEXT      => $poster['signature'],
	  SMILIES   => 1,
	  CODE      => 1,
	  SIGNATURE => 0,
	  HTML      => $ibforums->vars['sig_allow_html'],
	  HID	  => -1,
	  TID	  => 0,
	  MID	  => $row['author_id'],
	  );

	  $poster['signature'] = $this->parser->prepare($data);
	 */

	function unpostparse($msg)
	{
		$msg = $this->parser->unconvert($msg, 1, 0);
		return $msg;
	}

	//---------------------------------------------
	// Error Message
	//---------------------------------------------
	function error($msg, $item = "")
	{
		global $ibforums, $std;
		unset($this->output);
		if (!$item)
		{
			$message = $ibforums->lang['' . $msg . ''];
			if (empty($message))
			{
				die($ibforums->lang['error_error']);
			}
		} else
		{
			$message = $msg;
		}
		$html .= View::make("quiz.error");
		$html .= View::make("quiz.error_row", ['message' => $message]);
		$html .= View::make("quiz.end_page");
		// If you wish to remove it you will have to pay the 40$ fee.
		// See: www.outlaw.ipbhost.com/store/services.php for more infomation on how to pay.
		//	$html .= "<br/><div align='center' class='copyright'>Powered by <a href=\"https://www.subzerofx.com/shop/\" target='_blank'>IBStore</a> {$this->store_version} &copy; 2003-04 &nbsp;<a href='https://www.subzerofx.com/' target='_blank'>SubZeroFX.</a></div><br>";

		$print = new display();

		$print->add_output($html);

		$print->do_output(array(
		                       'OVERRIDE' => 1,
		                       'TITLE'    => $ibforums->lang['error_title'],
		                  ));

		exit;
	}

	function tag_convert($msg)
	{
		$msg = str_replace("'", "&#39;", $msg);
		$msg = str_replace("!", "&#33;", $msg);
		$msg = str_replace("$", "&#036;", $msg);
		$msg = str_replace("|", "&#124", $msg);
		$msg = str_replace("&", "&amp;", $msg);
		$msg = str_replace(">", "&gt;", $msg);
		$msg = str_replace("<", "&lt;", $msg);
		$msg = str_replace('"', "&quot;", $msg);
		$msg = str_replace(",", "&cedil;", $msg);
		$msg = str_replace("&cedil;", "", $msg);
		$msg = str_replace("&nbsp;", "", $msg);
		$msg = mb_strtolower($msg);
		$msg = stripslashes($msg);
		return $msg;
	}

	// This is a helpfull function to convert the username to ID
	// and get any othere info we want
	function getmid($username, $addon = "", $extra_a = "")
	{
		global $ibforums;
		$tables = "id,name";
		$tables .= $addon;
		$extra = "LOWER(name)='" . mb_strtolower($username) . "'";
		$extra .= $extra_a;
		$stmt = $ibforums->db->query("SELECT " . $tables . " FROM ibf_members WHERE " . $extra . " LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$this->error("cannot_finduser");
		}
		$info = $stmt->fetch();
		return $info;
	}

	// This is a helpfull function to convert the userID
	// to username and get any othere info we want
	function getmem($userid, $addon = "", $extra_a = "")
	{
		global $ibforums;
		$tables = "id,name";
		$tables .= $addon;
		$extra = "id='" . $userid . "'";
		$extra .= $extra_a;
		$stmt = $ibforums->db->query("SELECT " . $tables . " FROM ibf_members WHERE " . $extra . " LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$this->error("cannot_finduser");
		}
		$info = $stmt->fetch();
		return $info;
	}

	function do_forwhat()
	{
		global $ibforums, $lib, $print;

		$mid = $ibforums->input['mid'];

		$output = View::make("quiz.ShowTitle");

		$output .= View::make("quiz.ShowHeader");

		$output .= View::make("quiz.ShowFooter");

		$print->add_output("$output");

		$print->do_output(array('TITLE' => "test", 'JS' => 1, 'NAV' => "test"));
	}

	//--------------------------------------------------------------
	// Parse the member info
	//--------------------------------------------------------------

	function correct_cached_fields($member = array())
	{
		global $ibforums;

		if ((!$ibforums->member['id'] or $ibforums->member['show_ratting']) and $member['show_ratting'] and !$member['use_sig'])
		{

			$rep = ($this->forum['inc_postcount'])
				? $member['rep']
				: $member['ratting'];

			$rep_suffix = ($this->forum['inc_postcount'])
				? "t"
				: "f";

			$rep_link = $ibforums->lang['rep_name'] . $ibforums->lang['rep_' . $rep_suffix];

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
				$stuff = array('t'   => $this->topic['tid'],
				               'f'   => $this->forum['id'],
				               'mid' => $member['id'],
				               'p'   => $member['pid']
				);

				if ($ibforums->member['id'] == $member['id'])
				{
					$rep = "<a href='{$ibforums->base_url}act=rep&amp;CODE=03&amp;type={$rep_suffix}&amp;mid=" . $stuff['mid'] . "' target='_blank'>" . $rep_link . "</a>: " . $rep;
				} else
				{
					$down = ($ibforums->member['view_img'])
						? "<{REP_MINUS}>"
						: "<span style='color:red'>-</span>";
					$up   = ($ibforums->member['view_img'])
						? "<{REP_ADD}>"
						: "<span style='color:green'>+</span>";

					$link = "<a href='{$ibforums->base_url}act=rep&amp;CODE=03&amp;type={$rep_suffix}&amp;mid=" . $stuff['mid'] . "' target='_blank'>" . $rep_link . "</a>: ";
					$link .= "<a href='{$ibforums->base_url}act=rep&amp;CODE=02&amp;mid=$stuff[mid]&amp;f=$stuff[f]&amp;t=$stuff[t]&amp;p=$stuff[p]' style='text-decoration:none' target='_blank'>" . $down . "</a>";
					$link .= " [ " . $rep . " ] ";
					$link .= "<a href='{$ibforums->base_url}act=rep&amp;CODE=01&amp;mid=$stuff[mid]&amp;f=$stuff[f]&amp;t=$stuff[t]&amp;p=$stuff[p]' style='text-decoration:none' target='_blank'>" . $up . "</a>";

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

		// /Reputation + show ratting setting
		if ($ibforums->vars['warn_on'] and !stristr($ibforums->vars['warn_protected'], ',' . $member['mgroup'] . ','))
		{
			if ($ibforums->member['id'] != $member['id'] and ($ibforums->member['g_is_supmod'] or $this->moderator['allow_warn']))
			{
				$down = ($ibforums->member['view_img'])
					? "<{WARN_MINUS}>"
					: "<span style='color:green'>-</span>";
				$up   = ($ibforums->member['view_img'])
					? "<{WARN_ADD}>"
					: "<span class='movedprefix'>+</span>";

				$member['warn_add'] = "<a href='{$ibforums->base_url}act=warn&amp;type=add&amp;";
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
					"quiz.warn_title",
					['id' => $member['id'], 'title' => $member['warn_text']]
				);
				$member['warn_text'] .= $member['warn_minus'] . $member['warn_img'] . $member['warn_add'] . "<br>";
			}
		}

		return $member;
	}

	//---------------------------------------------------
	function parse_member($member = array())
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
			    (!$ibforums->member['id'] or ($ibforums->member['view_img'] and
			                                  $ibforums->member['show_icons']))
			)
			{
				$member['sex'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/fem.gif' alt='{$member['field_2']}' title='{$member['field_2']}' border='0'> ";
			}

			// add crlf
			if ($member['member_rank_img'])
				$member['member_rank_img'] .= "<br>";

			// add crlf
			if ($member['title'])
				$member['title'] .= "<br>";

			if ($member['g_icon'] and (!$ibforums->member['id'] or ($ibforums->member['view_img'] and $ibforums->member['show_icons'])))
			{
				$member['member_group_img'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/{$member['g_icon']}' border='0' alt='{$rank}' title='{$rank}'>";
			}

			$member['profile'] = "<a href='{$ibforums->base_url}showuser={$member['id']}' target='_blank'>{$ibforums->lang['link_profile']}</a> · <a href='{$ibforums->base_url}act=Msg&amp;CODE=4&amp;MID={$member['id']}' target='_blank'>PM</a><br>";

			// $member['profile'] = $member['points'] . "+".$member['fined']."+". $member['profile'] ;
			// Show ratting + dgm
			if ((!$ibforums->member['id'] or $ibforums->member['show_ratting'])
			    and intval($member['fined']) > 0
			        and $member['show_ratting']
			)
			{
				$member['member_points'] = $std->do_number_format($member['fined']);
				$member['member_points'] = "<a href='{$ibforums->base_url}act=store&code=showfine&id={$member['id']}'>РџРѕРѕС‰СЂРµРЅРёСЏ</a>: {$member['member_points']} {$ibforums->vars['currency_name']}";
				$member['member_points'] .= "<br>";
			} else
				$member['member_points'] = "";

			if ((!$ibforums->member['id'] or $ibforums->member['show_ratting']) and $ibforums->vars['show_inventory'])
			{
				$member['member_inventory'] = $ibforums->lang['members_inventory'] . "<a href='{$ibforums->base_url}act=store&amp;code=view_inventory&amp;memberid={$member['id']}'>{$ibforums->lang['view_inventory']}</a><br>";
			}

			//--------------------------------------------------------------
			// Profile fields stuff
			//--------------------------------------------------------------

			if ($ibforums->vars['custom_profile_topic'])
			{
				/*
				  foreach( $this->pfields as $id => $pf )
				  {
				  if ( $member[ $id ] != "" )
				  {
				  if ( $pf['fhide'] == 1 and $ibforums->member['g_is_supmod'] != 1 )
				  {
				  $member[ $id ] = "";

				  } elseif ( $pf['ftype'] == 'drop' )
				  {
				  $member[ $id ] = $this->pfields_dd[$id][$member[ $id ]]; // You just know that's going to make no sense tomorrow.
				  }
				  }
				  }
				  }
				 */
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

				$member['profile'] = "<a href='{$ibforums->base_url}showuser={$member['id']}' target='_blank'>{$ibforums->lang['link_profile']}</a> · <a href='{$ibforums->base_url}act=Msg&amp;CODE=4&amp;MID={$member['id']}' target='_blank'>PM</a><br>";
			}
		}

		// Add reputation and warn tools
		return $this->correct_cached_fields($member);
	}

	//**********************************************/
	// save_Quiz_log
	//
	// Add an entry into the admin logs, yeah.
	//**********************************************/

	function save_log($action = "")
	{
		$ibforums = Ibf::app();
		$data = [
			'act'        => $ibforums->input['act'],
			'code'       => $ibforums->input['code'],
			'member_id'  => $ibforums->member['id'],
			'ctime'      => time(),
			'note'       => $action,
			'ip_address' => $ibforums->input['IP_ADDRESS'],
		];

		Ibf::app()->db->insertRow('ibf_admin_logs', $data);
		return true; // to anyone that cares..
	}

}

// Class end
