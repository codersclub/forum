<?
//---------------------------------------------------
// IBStore Change Your Own Members Title
//---------------------------------------------------
class item
{
	var $name = "Change Your Member Title";
	var $desc = "Change your title to any one you want";
	var $extra_one = "1";
	var $extra_two = "10";
	var $extra_three = "";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::instance();
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Minimum amount of characters for title?</b><br>The least amount of charaters the new title can have.",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Maximum amount of characters for title?</b><br>The maximum amount of characters the new title can have.",
		                                       $SKIN->form_input("extra_two", $EXTRA['extra_two'])
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
				<td class='pformstrip' width='100%' colspan='4'>Change Your Member Title</td>
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

	function do_on_use($minimum, $maximum, $blank = "")
	{
		global $ibforums, $print, $lib;
		$minimum   = (int)$minimum;
		$maximum   = (int)$maximum;
		$protected = (int)$protected;

		if (strlen($ibforums->input['new_title']) < $minimum)
		{
			$lib->itemerror("To little characters in title, please add some more to it.");
		}

		if (strlen($ibforums->input['new_title']) > $maximum)
		{
			$lib->itemerror("To many characters in title, please shorten it.");
		}
		$ibforums->db->exec("UPDATE ibf_members SET title='{$ibforums->input['new_title']}' WHERE id='{$ibforums->member['id']}}' LIMIT 1");
		$lib->write_log("Changed Title To " . $ibforums->input['new_title'], "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Changed Title", "act=store&code=inventory", 1);
		return "";
	}
}

?>
