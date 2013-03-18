<?
//---------------------------------------------------
// IBStore Buy Password To Forum
//---------------------------------------------------
class item
{
	var $name = "Buy Password to Forum";
	var $desc = "Get a password from a selected forum!";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";
	var $apply_protect = 0;

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$stmt     = $ibforums->db->query("SELECT id,name FROM ibf_forums WHERE password!='' ORDER BY name DESC");
		while ($r = $stmt->fetch())
		{
			$forums[] = array($r['id'], $r['name']);
		}
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Forum to give password from?</b><br>The Forum that a user will get the Password from when used.",
		                                       $SKIN->form_dropdown('extra_one', $forums, $EXTRA['extra_one'])
		                                  ));
		return $ADMIN->HTML;
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
	}

	function run_job()
	{
	}

	function do_on_use($id, $blank = "", $blank = "")
	{
		global $ibforums, $print, $lib;
		$stmt = $ibforums->db->query("SELECT name,password FROM ibf_forums WHERE id='{$id}' LIMIT 1");
		if (!$stmt->rowCount())
		{
			$lib->itemerror("Cannot find forum ID $id.");
		}
		$forum   = $stmt->fetch();
		$message = "The Password to {$forum['name']} is, {$forum['password']}. <br />
					This password will be valid untill a Admin changes the Password to the {$forum['name']} Forums.";
		$pm_id   = $lib->sendpm($ibforums->member['id'], $message, "Password Too {$forum['name']}.", $ibforums->member['id']);
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Password to the forum, {$forum['name']} has been PM too you.", "act=Msg&CODE=03&VID=in&MSID={$pm_id}", "1");
		return "";
	}
}

?>
