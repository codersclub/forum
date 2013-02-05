<?
//---------------------------------------------------
// IBStore Increase Interest
//---------------------------------------------------
class item
{
	var $name = "Increase Interest";
	var $desc = "Increase your total interest by 2!";
	var $extra_one = "2";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::instance();
		$Admin->html .= $SKIN->add_td_row(array(
		                                       "<b>Add how much to Interest for User?</b><br>",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));

		return $Admin->html;
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

	function do_on_use($increase, $blank = "", $blank = "")
	{
		global $ibforums, $print, $lib;
		$increase = (int)$increase;
		if ($ibforums->member['extra_intrest'] + $ibforums->vars['base_intrest'] + $increase > 100)
		{
			$lib->itemerror("You're trying to increase you're interest above 100%", '1');
		}
		$increase = $ibforums->member['extra_intrest'] + $increase;
		$ibforums->db->exec("UPDATE ibf_members SET extra_intrest='{$increase}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
		$lib->write_log("Interest Increase By " . $increase, "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect('Interest Rate Increased.', 'act=store&code=bank', '1');
		return "";
	}
}

?>



