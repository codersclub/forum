<?
//---------------------------------------------------
// IBStore Post Count
//---------------------------------------------------
class item
{
	var $name = "Add 100 msgs to post count";
	var $desc = "Add 100 more post to your total post count";
	var $extra_one = "100";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Add How much to post count?</b><br>The amount of posts that are added to the members post count.",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));
		return $ADMIN->HTML;
	}

	function on_add_edits($admin)
	{
		global $ADMIN, $INFO;
		$ibforums = Ibf::app();
		$checker  = $INFO['base_dir'] . "sources/store/edit_check.php";
		require_once($checker);
		$is_their = row_check($INFO['sql_tbl_prefix'] . "members", "post_addon");
		if (!$is_their)
		{
			$stmt = $ibforums->db->query("ALTER TABLE `ibf_members` ADD `post_addon` INT( 9 ) DEFAULT '0' NOT NULL AFTER `posts`");
		}
		return false;
	}

	function on_add_extra()
	{
	}

	function on_buy()
	{
	}

	function run_job()
	{
	}

	function on_use($itemid = "", $itemname = "")
	{
	}

	function do_on_use($addons, $blank = "", $blank = "")
	{
		// vot		global $ibforums,$print,$lib;
		global $ibforums, $lib;
		$addons = (int)$addons;
		$ibforums->db->exec("UPDATE ibf_members SET posts=posts+$addons,post_addon=post_addon+$addons WHERE id='{$ibforums->member['id']}' LIMIT 1");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "Использован товар 'Покупка постов'. Куплено и добавлено " . $addons . " сообщений.", "", "item");
		// vot
		//		$lib->redirect('Added onto Post Count','act=store&code=inventory','1');
		return "";
	}
}

?>



