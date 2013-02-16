<?
//---------------------------------------------------
// IBStore Cannot Use Item
//---------------------------------------------------
class item
{
	var $name = "Rock";
	var $desc = "Not much you can do with this item.";
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

	function on_use()
	{
	}

	function run_job()
	{
	}

	function do_on_use($blank = "", $blank = "", $blank = "")
	{
		global $ibforums, $lib;
		$lib->redirect("You cannot use this item.", "act=store", "1");
		return "";
	}
}

?>


