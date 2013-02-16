<?
//---------------------------------------------------
// IBStore Open/Close Own Topic
//---------------------------------------------------
class item
{
	var $name = "Open/Close Own Topics.";
	var $desc = "Open/Close any topic you started.";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA)
	{
	}

	function on_add_edits($admin)
	{
	}

	function on_add_extra()
	{
	}

	function on_buy()
	{
	}

	function on_use($itemid = "")
	{
		global $ibforums, $lib;
		return <<<EOF
			 <form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Open/Close Own Topic</td>
			</tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Topic URL:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='url'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Submit'></td>
			   </tr>
			</form>

EOF;
	}

	function run_job()
	{
	}

	function do_on_use($type, $overwrite, $blank)
	{
		global $ibforums, $print, $lib;
		if (!$ibforums->input['url'])
		{
			$lib->itemerror("Please enter a URL.");
		}
		$ibforums->input['url'] = str_replace($ibforums->vars['board_url'], "", $ibforums->input['url']);
		$ibforums->input['url'] = str_replace("index." . $ibforums->vars['php_ext'] . "?", "", $ibforums->input['url']);
		$num                    = explode("&", $ibforums->input['url']);

		if (!preg_match("#showtopic#is", $num['0']))
		{
			foreach ($num as $var)
			{
				if (preg_match("#showtopic#is", $var))
				{
					$topic = $var;
					break;
				}
			}
		} else
		{
			$topic = $num['0'];
		}
		$topic_num = str_replace("showtopic=", "", $topic);
		$topic_num = str_replace("/", "", $topic_num);
		$stmt      = $ibforums->db->query("SELECT tid,title,starter_id,state FROM ibf_topics WHERE tid='{$topic_num}' AND starter_id='{$ibforums->member['id']}' LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$lib->itemerror("We could not find the topics whos URL you entered, This could be due to a invalid URL, or you not being the topic starter.");
		}
		$topic = $stmt->fetch();
		if ($topic['state'] == 'open')
		{
			$state = 'closed';
		} else
		{
			$state = 'open';
		}
		$ibforums->db->exec("UPDATE ibf_topics SET state='{$state}' WHERE tid='{$topic['tid']}' LIMIT 1");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "Topic \"<a href='{$ibforums->base_url}showtopic={$topic['tid']}'>{$topic['title']}</a>\" " . ucfirst(strtolower($state)) . "!", "", "item");
		$lib->redirect("Topic \"{$topic['title']}\" " . ucfirst(strtolower($state)) . "!", "showtopic={$topic['tid']}", "1");
		return "";
	}
}

?>
