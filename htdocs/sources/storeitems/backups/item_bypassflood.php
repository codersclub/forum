<?
//---------------------------------------------------
// IBStore Decrease Flood Control
//---------------------------------------------------
class item
{
	var $name = "Decrease Flood Control";
	var $desc = "Lower your total flood control by 10 seconds.";
	var $extra_one = "10";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::instance();
		$ADMIN->Html .= $SKIN->add_td_row(array(
		                                       "<b>The amount of Seconds to take off of flood control?</b><br>The amount the users flood control is decreased by when this item is used.",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));

		return $ADMIN->Html;
	}

	function on_add_edits($admin)
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::instance();
		require_once($INFO['base_dir'] . "sources/store/edit_check.php");
		$is_their = row_check($INFO['sql_tbl_prefix'] . "members", "flood_control");
		if (!$is_their)
		{
			$stmt = $ibforums->db->query("ALTER TABLE `ibf_members` ADD `flood_control` INT( 3 ) DEFAULT '0' NOT NULL");
		}
		$istheir    = file_check("sources/functions.php", "m.flood_control");
		$flood_edit = file_check("sources/Post.php", "if ( time() - $ibforums->member['last_post'] < $ibforums->vars['flood_control'] - $ibforums->member['flood_control'] )");
		if (!$istheir || $flood_edit)
		{
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Before you can add this item you have to make the following edit.<br>"
			                                  ));
		}
		if (!$istheir)
		{
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Open ./sources/functions.php<br>"
			                                  ));
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Find: m.points<br>"
			                                  ));
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Add After: ,m.flood_control<br>"
			                                  ));
		}
		if (!$flood_edit)
		{
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Open ./sources/Post.php<br>"
			                                  ));
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Find:<br>if ( time() - $<b></b>ibforums->member['last_post'] < $<b></b>ibforums->vars['flood_control'] ) <br>"
			                                  ));
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "Replace With:<br> if ( time() - $<b></b>ibforums->member['last_post'] < $<b></b>ibforums->vars['flood_control'] - $<b></b>ibforums->member['flood_control'] )<br>"
			                                  ));
		}
		if ($flood_edit && $istheir)
		{
			return false;
		} else
		{
			$ADMIN->Html .= $SKIN->add_td_row(array(
			                                       "After that you may continue on with adding this item.<br><br>"
			                                  ));
			return $ADMIN->Html;
		}
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

	function do_on_use($add_flood, $blank = "", $blank = "")
	{
		global $ibforums, $print, $lib;
		$add_flood = (int)$add_flood;
		$ibforums->input['flood_control'] += $add_flood;
		$ibforums->db->exec("UPDATE ibf_members SET flood_control='{$ibforums->member['flood_control']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
		$lib->delete_item($ibforums->input['itemid']);
		$add_flood = $ibforums->vars['flood_control'] - $ibforums->member['flood_control'] - $add_flood;
		if ($add_flood < 1)
		{
			$add_flood = 0;
		}
		$lib->write_log("Flood Control is now " . $add_flood, "item");
		$lib->redirect('Youre flood control is now, {$add_flood}.', 'act=store&code=inventory', '1');
		return "";
	}
}

?>



