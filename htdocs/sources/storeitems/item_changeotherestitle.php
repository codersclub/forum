<?
//---------------------------------------------------
// IBStore Change Other Members Title
//---------------------------------------------------
class item
{
	var $name = "Change Others Member Title";
	var $desc = "Change any ones title to a new one!";
	var $extra_one = "1";
	var $extra_two = "10";
	var $extra_three = "4";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Minimum amount of characters for title?</b><br>The least amount of charaters the new title can have.",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Maximum amount of characters for title?</b><br>The maximum amount of characters the new title can have.",
		                                       $SKIN->form_input("extra_two", $EXTRA['extra_two'])
		                                  ));
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Protected Member Groups:</b><br>The user groups that can not have their titles changed. (Seperate with a ',' so 5,4,2,1 ect)",
		                                       $SKIN->form_input("extra_three", $EXTRA['extra_three'])
		                                  ));

		return $ADMIN->HTML;
	}

	function on_add_edits()
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
		global $ibforums;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Change Othere Members Title</td>
			</tr>
			 <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Users Title To Change:<strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='username'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>New Title:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='new_title'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Change Title'></td>
			   </tr>
			</form>
EOF;
	}

	function run_job()
	{
	}

	function do_on_use($minimum, $maximum, $protected)
	{
		global $ibforums, $print, $lib;
		$minimum   = (int)$minimum;
		$maximum   = (int)$maximum;
		$protected = (int)$protected;
		$stmt      = $ibforums->db->query("SELECT id,name,mgroup FROM ibf_members WHERE name='{$ibforums->input['username']}' LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$lib->itemerror("We cannot find the member whos title you are trying to change. Please check you\'re spelling.");
		}
		$change_info = $stmt->fetch();
		$protected   = explode(",", $protected);
		if ($lib->checkprotected($protected, $change_info['mgroup']))
		{
			$lib->itemerror("You are trying to change a protected member's group.");
		}
		if (strlen($ibforums->input['new_title']) < $minimum)
		{
			$lib->itemerror("To little characters in title, please add some more to it.");
		}

		if (strlen($ibforums->input['new_title']) > $maximum)
		{
			$lib->itemerror("To many characters in title, please shorten it.");
		}
		$ibforums->db->exec("UPDATE ibf_members SET title='{$ibforums->input['new_title']}' WHERE id='{$change_info['id']}' LIMIT 1");
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "Changed " . $change_info['name'] . " Title To " . $ibforums->input['new_title'], "", "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Changed Title", "act=store&code=inventory", 1);
		return "";
	}
}

?>
