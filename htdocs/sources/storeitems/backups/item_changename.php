<?
//---------------------------------------------------
// IBStore Change Username
//---------------------------------------------------
class item
{
	var $name = "Change Username";
	var $desc = "Change You're Username to a new one of you're choice.";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";

	// This is triggered when a Admin adds a item to the store
	// after they have selected a name a icon and a description and stock
	function on_add($EXTRA)
	{
	}

	// As soon as a user has hit "Buy Item" this is triggered
	function on_add_edits($admin)
	{
	}

	function on_add_extra()
	{
	}

	// We trigger this function as soon as "Use Item" is clicked.
	// this is were you would put the things a user could change (Like a members title as a e.g)
	function on_buy()
	{
	}

	function on_use($itemid = "")
	{
		global $ibforums;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Change Username</td>
			</tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>New Username:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='username' size='32'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Change User'></td>
			   </tr>
			</form>
EOF;
	}

	// This will take all the input from on_use (If any)
	// and run the querys or change some thing...Which probly would be in a query
	function run_job()
	{
	}

	function do_on_use($blank = "", $blank = "", $blank = "")
	{
		global $ibforums, $print, $lib;
		$stmt = $ibforums->db->query("SELECT name FROM ibf_members WHERE LOWER(name)='{$ibforums->input['username']}' LIMIT 1");
		if ($stmt->rowCount() == 1)
		{
			$lib->itemerror("Sorry, that username is taken.");
		}
		if ($ibforums->vars['ban_names'])
		{
			$names = explode("|", $ibforums->vars['ban_names']);
			foreach ($names as $name)
			{
				if ($name == "")
				{
					continue;
				}
				if (preg_match("/" . preg_quote($n, '/') . "/i", $ibforums->input['username']))
				{
					$lib->itemerror("Sorry, that username is on the banned list.");
				}
			}
		}
		$ibforums->db->exec("UPDATE ibf_members SET name='{$ibforums->input['username']}' WHERE id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_contacts SET contact_name='{$ibforums->input['username']}' WHERE contact_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_forums SET last_poster_name='{$ibforums->input['username']}' WHERE last_poster_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_moderator_logs SET member_name='{$ibforums->input['username']}' WHERE member_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_moderators SET member_name='{$ibforums->input['username']}' WHERE member_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_posts SET author_name='{$ibforums->input['username']}' WHERE author_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_sessions SET member_name='{$ibforums->input['username']}' WHERE member_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_topics SET starter_name='{$ibforums->input['username']}' WHERE starter_id='{$ibforums->member['id']}'");
		$ibforums->db->exec("UPDATE ibf_topics SET last_poster_name='{$ibforums->input['username']}' WHERE last_poster_id='{$ibforums->member['id']}'");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Changed Username to {$ibforums->input['username']}", "act=store", "1");
		return "";
	}
}

?>



