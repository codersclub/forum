<?php
/*---------------------------------------------------------------------*\
|   IBStore 2.5															|
+-----------------------------------------------------------------------+
|   (c) 2003 Zachary Anker												|
|	Email: wingzero1018@hotmail.com										|
|   http://www.subzerofx.com/shop/										|
+-----------------------------------------------------------------------+
|	You may edit this file as long as you retain this Copyright notice.	|
|	Redistribution not permitted without permission from Zachary Anker.	|
\*---------------------------------------------------------------------*/

if (!defined('IN_ACP'))
{
	exit("<h1>Incorrect access</h1> You may not access this file directly.");
}

$idx = new ad_store();

class ad_store
{

	var $base_url = "";
	var $ibsversion = "25";

	var $parser = "";
	var $store_lib = "";

	var $extra_shop = "";

	var $item_notfound = "<b>Item File Missing</b>";
	var $make_safe = array(
		'inventory_max',
		'resell_percentage',
		'richest_showamount',
		'base_intrest',
		'default_points',
		'pointsper_poll',
		'pointsper_topic',
		'pointsper_reply',
		'days_interestcollected'
	);
	var $trailingslash = "";
	// Get are update mirror :D
	var $mirror_update = "http://www.outlaw.ipbhost.com/store/";

	function ad_store()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums       = Ibf::app();
		$this->base_url = $INFO['board_url'] . "/admin." . $INFO['php_ext'] . "?adsess=" . $IN['AD_SESS'];
		$this->parser = new PostParser();
		require_once($INFO['base_dir'] . "sources/store/store_functions.php");
		$this->store_lib = new lib;
		if ($INFO['show_shopcat'])
		{
			$this->extra_shop = "(All items will apper in Shop still)";
		}
		if ($IN['code'] == 'quiz_settings' && $IN['quiztype'])
		{
			if ($IN['dowhat'] == 2)
			{
				$IN['code'] = "edit_questions";
			} else
			{
				if ($IN['dowhat'] == 1)
				{
					$IN['code'] = "quiz_settings";
				} else
				{
					if ($IN['dowhat'] == 0)
					{
						$IN['code'] = "qdelete";
					}
				}
			}
		}
		if ($IN['code'] == 'itemproperties' && $IN['itemtype'])
		{
			if ($IN['dowhat'] == 1)
			{
				$IN['code'] = "do_item_delete";
			}
		}
		switch ($IN['code'])
		{
			case 'storesettings':
				$this->storesettings();
				break;
			case 'dostoresettings':
				if ($IN['members_defaultpoints'] != $INFO['default_points'])
				{
					$stmt = $ibforums->db->query("ALTER TABLE ibf_members CHANGE points points INT( 11 ) DEFAULT '{$IN['members_defaultpoints']}' NOT NULL ");
				}
				$_POST['days_interestcollected'] = $_POST['days_interestcollected'] * 86400;
				$this->save_config(array(
				                        'store_on',
				                        'store_guest',
				                        'store_name',
				                        'currency_name',
				                        'welcome_line',
				                        'welcome_desc',
				                        'richest_onhand',
				                        'richest_bank',
				                        'richest_overall',
				                        'richest_showamount',
				                        'pointsper_topic',
				                        'pointsper_reply',
				                        'topic_over',
				                        'topic_pointsover',
				                        'bank_on',
				                        'base_intrest',
				                        'ibstore_safty',
				                        'has_edited_ibstore',
				                        'allow_resell',
				                        'resell_percentage',
				                        'allow_deleting',
				                        'tell_resellamount',
				                        'showplaysleft',
				                        'mass_buyon',
				                        'mass_buyamount',
				                        'pointsper_poll',
				                        'what_else',
				                        'reset_onnegitive',
				                        'default_points',
				                        'show_shopcat',
				                        'show_inventory',
				                        'inventory_showresell',
				                        'inventory_max',
				                        'members_defaultpoints',
				                        'money_donation',
				                        'item_donation',
				                        'store_regid',
				                        'pages_peritems',
				                        'show_memberpoints',
				                        'days_interestcollected'
				                   ));
				break;
			case 'add_category':
				$this->add_category();
				break;
			case 'do_add_category':
				$this->do_add_category();
				break;
			case 'add':
				$this->add();
				break;
			case 'itemproperties':
				$this->itemproperties();
				break;
			case 'do_stockadd_two':
				$this->do_stockadd_two();
				break;
			case 'do_stockedits':
				$this->do_stockedits();
				break;
			case 'itemedit':
				$this->itemedit();
				break;

			case 'quiz_settings':
				$this->quiz_settings();
				break;
			case 'do_quiz_settings':
				$this->do_quiz_settings();
				break;
			case 'do_quiz_settings_add':
				$this->do_quiz_settings_add();
				break;
			case 'qdowhat':
				$this->qdowhat();
				break;
			case 'qdelete':
				$this->qdelete();
				break;
			case 'do_quiz_update':
				$this->do_quiz_update();
				break;
			case 'edit_questions':
				$this->edit_questions();
				break;
			case 'do_editquestions':
				$this->update_questions();
				break;

			case 'edit_cat':
				$this->select_category();
				break;
			case 'edit_category':
				$this->edit_category();
				break;
			case 'do_cat_edit':
				$this->do_cat_edit();
				break;
			case 'update':
				$this->update_check();
				break;
			case 'item_logs':
				$this->item_logs();
				break;
			case 'mod_logs':
				$this->mod_logs();
				break;
			case 'item_update':
				$this->item_updater();
				break;
			case 'update_page':
				$this->update_page();
				break;
			case 'item_restore':
				$this->item_restore();
				break;
			case 'item_adder':
				$ADMIN->error("Sorry this feature is not usable yet.");
				break;
			case 'recount':
				$this->recount();
				break;
			case 'dorecount':
				$this->dorecount();
				break;
			case 'do_item_delete':
				$this->delete_item();
				break;
			case 'clearlog':
				$this->clearlog();
				break;
			default:
				$this->storesettings();
				break;
		}

	}

	function storesettings()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('dostoresettings', 'IBStore Settings', 'Change your Store settings below');

		$ADMIN->html .= $SKIN->start_table("Main Settings");
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Is the store on?</b><br>Does not effect Root Admins.",
		                                       $SKIN->form_yes_no("store_on", $INFO['store_on'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow guest to view the store?</b><br>If no, guest will get a error message when trying to view the shop.",
		                                       $SKIN->form_yes_no("store_guest", $INFO['store_guest'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Stores Name?</b><br>The name of you're store (Can be any thing).",
		                                       $SKIN->form_input("store_name", $INFO['store_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Currency Name?</b><br>Yen, Zeny, Points, Money ect.",
		                                       $SKIN->form_input("currency_name", $INFO['currency_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Welcome Line:</b><br>A line of text to show users on the stores main page.",
		                                       $SKIN->form_input("welcome_line", $INFO['welcome_line'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Welcome Description:</b><br>The welcome description to you're store, Smilies and BBCode are enabled.<br> Use *username* to show the users, username. ",
		                                       $SKIN->form_textarea("welcome_desc", $INFO['welcome_desc'], 50, 10)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn Safty Messages on?</b><br>This will add a Javascript Confirm message so a user must hit Ok before being able preform a action. (including sending items,money using items,reselling and deleting items) Javascript MUST be enabled for this to work",
		                                       $SKIN->form_yes_no("ibstore_safty", $INFO['ibstore_safty'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Basic Shop category?</b><br>This will remove the base Shop category.",
		                                       $SKIN->form_yes_no("show_shopcat", $INFO['show_shopcat'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum Amount of Items In Inventory?</b><br>The amount of items a user is allowed to have in their inventory at one time.<br /> (0 or blank to disable)",
		                                       $SKIN->form_input("inventory_max", $INFO['inventory_max'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show the Members Points?</b><br>If yes the amount of points the member has will show up in the store.",
		                                       $SKIN->form_yes_no("show_memberpoints", $INFO['show_memberpoints'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Items Per a Page?</b><br>The amount of items that will be shown in a page at one time when buying a item.(Will show a Next and Last link to move through them)",
		                                       $SKIN->form_input("pages_peritems", $INFO['pages_peritems'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Items', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Money Donations Message:</b><br>The message sent to the user who recived the donation gets.",
		                                       $SKIN->form_textarea("money_donation", $INFO['money_donation'], 60, 7)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Item Donations Message:</b><br>This is the message that the person who recived the item will get.",
		                                       $SKIN->form_textarea("item_donation", $INFO['item_donation'], 60, 7)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Items', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow users to Resell their items back to the store?</b><br>If no the resell link will disapper.",
		                                       $SKIN->form_yes_no("allow_resell", $INFO['allow_resell'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>If above is on, the percentage they get back</b><br>So if a item is bought for 10 Points, and the percentage is set to 50% they will get 5 Points back.",
		                                       $SKIN->form_input("resell_percentage", $INFO['resell_percentage'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow Deleting of items?</b><br>If no the delete link will disapper.",
		                                       $SKIN->form_yes_no("allow_deleting", $INFO['allow_deleting'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Tell the users how much they will get back if they resell?</b><br>",
		                                       $SKIN->form_yes_no("tell_resellamount", $INFO['tell_resellamount'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Overall Stats Paths', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Richest Member?</b><br>Shows the Richest member counting on hand money only.",
		                                       $SKIN->form_yes_no("richest_onhand", $INFO['richest_onhand'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Richest Member In Bank?</b><br>Shows the Richest member in deposited bank money.",
		                                       $SKIN->form_yes_no("richest_bank", $INFO['richest_bank'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Richest Member Overall?</b><br>Counting  both bank and onhand money.",
		                                       $SKIN->form_yes_no("richest_overall", $INFO['richest_overall'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Richest/On hand/Bank/Overall members to show?</b><br>E.G if you put 5 it will show the top five<br> richest members of the ones you have turned on.",
		                                       $SKIN->form_input("richest_showamount", $INFO['richest_showamount'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_basic('View Inventory', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn on Viewing Other Inventorys?</b><br>If yes othere members will be able to see any users inventory.",
		                                       $SKIN->form_yes_no("show_inventory", $INFO['show_inventory'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Resell Price?</b><br>If Yes it will show the amount of points the owner of that item would get for reselling it.",
		                                       $SKIN->form_yes_no("inventory_showresell", $INFO['inventory_showresell'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Bank', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Is the Bank on?</b><br>If no Members can not use the Bank.",
		                                       $SKIN->form_yes_no("bank_on", $INFO['bank_on'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Base interest Rate?</b><br>Amount of interest a User Gets.",
		                                       $SKIN->form_input("base_intrest", $INFO['base_intrest'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Interest collectable every...</b><br>Amount of days a user has to wait to collect interest.<br /> E.G: Setting this to 1 lets them collect daily.",
		                                       $SKIN->form_input("days_interestcollected", ceil($INFO['days_interestcollected'] / 86400)) . '...days'
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Quizs', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show How Amount Played?</b><br>This will add a Users Played field into the Quiz that will say how many people have played.",
		                                       $SKIN->form_yes_no("showplaysleft", $INFO['showplaysleft'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Buying', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Is Mass Buy On?</b><br>If no you can leave the below blank.",
		                                       $SKIN->form_yes_no("mass_buyon", $INFO['mass_buyon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Mass Buy Amounts:</b><br>Seperate with comma.<br> E.G. If you put in 2,5 it will let users buy items in groups of two, or groups of 5.",
		                                       $SKIN->form_input("mass_buyamount", $INFO['mass_buyamount'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_basic('Points', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Reset Users Points to 0?</b><br>If a users points goes into negtive value and this is on they will be reset to 0 points.",
		                                       $SKIN->form_yes_no("reset_onnegitive", $INFO['reset_onnegitive'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Points Per New Register?</b><br>The amount of points new users who Register get.",
		                                       $SKIN->form_input("members_defaultpoints", $INFO['members_defaultpoints'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Points to give Per a new Poll</b><br>Amount of points to give a user when they make a new Poll.",
		                                       $SKIN->form_input("pointsper_poll", $INFO['pointsper_poll'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Points to give Per a new Topic?</b><br>Amount of points a user will get per a new Topic made.",
		                                       $SKIN->form_input("pointsper_topic", $INFO['pointsper_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Points to give Per a Reply</b><br>Amount of points to give a user when they make a Reply.",
		                                       $SKIN->form_input("pointsper_reply", $INFO['pointsper_reply'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>If a users Topic gets over...</b><br>Use 0 to disable.",
		                                       $SKIN->form_input("topic_over", $INFO['topic_over']) . '...of replys give him a bonus'
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount to give if the above contion happens?</b><br>Amount of points to give a user if there Topic gets the set amount of Replys.",
		                                       $SKIN->form_input("topic_pointsover", $INFO['topic_pointsover'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What else?:</b><br>(you may indicate here the rules of adding points)",
		                                       $SKIN->form_textarea("what_else", $INFO['what_else'], 80, 10)
		                                  ));

		if (!$INFO['store_regid'])
		{
			$INFO['store_regid'] = "unknown";
		}
		$ADMIN->html .= "<input type='hidden' name='store_regid' value='{$INFO['store_regid']}'>";
		$ADMIN->html .= "<input type='hidden' name='has_edited_ibstore' value='1'>";
		$ADMIN->html .= $SKIN->end_table();

		$this->common_footer();
	}

	function recount()
	{
		global $SKIN, $ADMIN, $INFO, $MEMBER;
		$ibforums           = Ibf::app();
		$ADMIN->page_detail = "Recount/Reset effects caused by IBStore.";
		$ADMIN->page_title  = "Recount/Reset effects";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dorecount'),
		                                       2 => array('act', 'store'),
		                                  ));
		$SKIN->td_header[] = array("Name", "10%");
		$SKIN->td_header[] = array("Description", "40%");
		$SKIN->td_header[] = array("Options", "40%");
		$SKIN->td_header[] = array("Do", "5%");
		$ADMIN->html .= $SKIN->start_table("Options");
		if ($MEMBER['mgroup'] == 4)
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "Point Recounter",
			                                       "Recount the current members points to the amount of posts they have. (Uses the current amount of points per a reply as amount per a posts)",
			                                       "Reset members points to 0 then add the points? " . $SKIN->form_yes_no("reset") . "<br>(If no members keep current points) <b> This will ignore any addon posts </b>",
			                                       "<input type='radio' name='doaction' value='pointsrecount'>",
			                                  ));
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("Sorry Point Recounting is for Root Admins only.", "center");
		}
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Posts Reset",
		                                       "Reset all of your members posts to there real number, can be used to reset posts gained by Add to Post count item. (Beta Still)",
		                                       "",
		                                       "<input type='radio' name='doaction' value='resetposts'>",
		                                  ));
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("Do Selected Option");
	}

	function dorecount()
	{
		global $ADMIN, $SKIN, $MEMBER, $INFO, $IN;
		$ibforums = Ibf::app();
		if ($IN['doaction'] == 'pointsrecount')
		{
			require(ROOT_PATH . "/sources/store/edit_check.php");
			if (row_check("ibf_members", "post_addon"))
			{
				$extra = ",post_addon";
			}
			$stmt = $ibforums->db->query("SELECT id,name,posts" . $extra . ",points FROM ibf_members WHERE posts>0 AND id>0 ORDER BY posts DESC");
			while ($user = $stmt->fetch())
			{
				$points = $INFO['pointsper_reply'];
				$user['posts'] -= $user['post_addon'];
				$points *= $user['posts'];
				if ($IN['reset'] == 0)
				{
					$points += $user['points'];
				}
				$temp = $ibforums->db->exec("UPDATE ibf_members
						    SET points='{$points}'
						    WHERE id='{$user['id']}'
						    LIMIT 1");
				$amount++;
			}
			$stat_recount     = "Post Recount";
			$done_description = "All members posts have been recounted, {$amount} of members got there points reset.";
		} else
		{
			if ($IN['doaction'] == 'resetposts')
			{
				$ibforums->db->exec("UPDATE ibf_members
				    SET posts=posts-post_addon,post_addon=0");
				$stat_recount     = "Posts Reset";
				$done_description = "All members posts have been reset to there real ones.";
			} else
			{
				if ($IN['doaction'] == 'resetusershop')
				{

				}
			}
		}
		$ADMIN->save_log("Recounted Stat: {$stat_recount}");

		$ADMIN->done_screen($done_description, "Administration CP Home", "act=index");
	}

	function code_edit($code)
	{
		$code = str_replace('"', "'", $code);
		$code = htmlspecialchars($code);
		$code = str_replace("{", "{<b></b>", $code);
		$code = str_replace("}", "<b></b>}", $code);
		$code = str_replace("$", "$<b></b>", $code);
		return '<table border="0" cellspacing="0" cellpadding="1" width="100%" style="border:1px black solid;background-color:#ffffff"><tr><td>' . $code . '</td></tr></table>';
	}

	function update_page()
	{
		global $SKIN, $ADMIN, $INFO;
		$ibforums = Ibf::app();
		$this->check();
		$this->common_header('', 'IBStore Update Portal', 'Update Portal');

		$ADMIN->html .= $SKIN->start_table("Updates");
		$ADMIN->html .= $SKIN->add_td_basic("<b><a href='{$this->base_url}&act=store&code=item_update'>Item Update</a></b> - Update all of you're IBStore Items Here.");
		$ADMIN->html .= $SKIN->add_td_basic("&nbsp;");
		$ADMIN->html .= $SKIN->add_td_basic("<b><a href='{$this->base_url}&act=store&code=update'>IBStore Update</a></b> - Manually check for IBStore updates.");

		//$ADMIN->html .= $SKIN->add_td_basic("<b><a href='{$this->base_url}&act=store&code=ibstore_auto'>IBStore Updater</a></b> - Automatically update IBStore to its latest version.");

		$ADMIN->html .= $SKIN->add_td_basic("&nbsp;");
		$ADMIN->html .= $SKIN->add_td_basic("<b><a href='{$this->base_url}&act=store&code=item_restore'>Item Restore</a></b> - If you need to restore you're items to the what they where before there last update run this.");

		//$ADMIN->html .= $SKIN->add_td_basic("<b><a href='{$this->base_url}&act=store&code=ibstore_restore'>IBStore Restore</a></b> - Restore IBStore to the point before its last upgrade.");

		$ADMIN->html .= $SKIN->add_td_basic("&nbsp;");
		$ADMIN->html .= $SKIN->add_td_basic("<b><a href='{$this->base_url}&act=store&code=item_adder'>Item Adder</a></b> - Add any new items that have come out for IBStore here.");
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");

	}

	function item_adder()
	{
		global $SKIN, $ADMIN, $IN, $INFO;
		$ibforums = Ibf::app();
		$this->check();
		$this->common_header('', 'IBStore Item Adder', 'Get the latest Items here.');

		$ADMIN->html .= $SKIN->start_table("Adding");
		$ADMIN->html .= $SKIN->add_td_basic("Now Adding New Items to you're shop.");

		$fp = fopen($this->mirror_update . "update_portal.php?update=newitems&boardurl={$INFO['board_url']}&version={$this->ibsversion}&ip={$IN['IP_ADDRESS']}&tempory=" . time() . "&id={$INFO['ibstore_id']}&regid={$INFO['store_regid']}", "r");
		while ($items = fread($fp, 1024))
		{
			$new_items .= $items;
		}
		fclose($fp);

		$new_items = "" . $new_items . " ";
		eval($new_items);
		$handle = opendir(ROOT_PATH . "/sources/storeitems/");
		while ($items = readdir($handle))
		{
			if (preg_match("/item_/", $items))
			{
				$items               = str_replace("." . $INFO['php_ext'], "", $items);
				$CURRENTITEM[$items] = $items;
			}
		}
		closedir($handle);
		foreach ($NEWITEMS as $new => $temp)
		{
			if (!$CURRENTITEM[$new])
			{
				$ADMIN->html .= $SKIN->add_td_basic("Now Adding the Item {$new}");
				$message = $this->update($new . "." . $INFO['php_ext'], 1);
				if (!$message)
				{
					$ADMIN->html .= $SKIN->add_td_basic("{$new} Succesfully Added.");
				} else
				{
					$ADMIN->html .= $SKIN->add_td_basic($message);
				}
				$ADMIN->html .= $SKIN->add_td_basic("&nbsp;");
			}
		}
		$ADMIN->html .= $SKIN->add_td_basic("All New Items Added.");
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");
	}

	function item_restore()
	{
		global $SKIN, $ADMIN, $INFO;
		$ibforums = Ibf::app();
		$this->check();
		$this->common_header('', 'IBStore Item Restore', 'Restore You\'re IBStore Items');

		$ADMIN->html .= $SKIN->start_table("Restore");
		$ADMIN->html .= $SKIN->add_td_basic("Now Restoring IBStore Items");
		$handle = opendir(ROOT_PATH . "/sources/storeitems/backups/");
		while ($items = readdir($handle))
		{
			if (preg_match("/item_/", $items))
			{
				if ($items != '.' || $items != '..')
				{
					$ADMIN->html .= $SKIN->add_td_basic("Now Restoring: {$items}");
					if (!@copy(ROOT_PATH . "/sources/storeitems/backups/" . $items, ROOT_PATH . "/sources/storeitems/" . $items))
					{
						$ADMIN->html .= $SKIN->add_td_basic("Error: Unable To Restore {$items}");
					} else
					{
						$ADMIN->html .= $SKIN->add_td_basic("{$items} has been restored");
						@unlink(ROOT_PATH . "/sources/storeitems/backups/" . $items);
					}
					$ADMIN->html .= $SKIN->add_td_basic("&nbsp;");
				}
			}

		}
		closedir($handle);
		$ADMIN->html .= $SKIN->add_td_basic('Items Restored Succesfully.');

		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");
	}

	function update($items, $request_new = 0)
	{
		global $INFO, $IN, $ADMIN;
		$ibforums = Ibf::app();

		$fp   = fopen($this->mirror_update . "update_portal.php?update={$items}&boardurl={$INFO['board_url']}&version={$this->ibsversion}&ip={$IN['IP_ADDRESS']}&tempory=" . time() . "&id={$INFO['ibstore_id']}&regid={$INFO['store_regid']}", "r");
		$file = ROOT_PATH . "/sources/storeitems/" . $items;
		if (!$request_new)
		{
			if (@copy($file, ROOT_PATH . "/sources/storeitems/backups/" . $items))
			{
			} else
			{
				return "Error: Could not make a backup of {$items}.<br> This could be due to the folder ./sources/storeitems/backup/ not being chmoded to 777.<br>
						Or you could have restrictions on using the function copy().";
			}
		}
		$pf       = fopen($file, "a+");
		$truncate = true;

		while ($temp = fread($fp, 1024))
		{
			if ($temp == '404')
			{
				fclose($pf);
				fclose($fp);
				return "Error: Got 404 error when trying to get the item {$items}";
			} else
			{
				if ($temp == 'special_option')
				{
					if (!@touch(ROOT_PATH . "/sources/storeitems/ibs_lock.lock", time()))
					{
						$fh = @fopen(ROOT_PATH . "/sources/storeitems/ibs_lock.lock", "w");
						@fwrite($fh, 'Safty thingy ma bob', 19);
						@fclose($fh);
					}
					@chmod(ROOT_PATH . "/sources/storeitems/ibs_lock.lock", 0666);
					$ADMIN->error("You have been banned from using upgrading");
					return;
				}
			}
			if ($truncate)
			{
				ftruncate($pf, 0);
				$truncate = false;
			}
			fwrite($pf, $temp, strlen($temp));
		}
		if ($truncate)
		{
			return "Error: Could not get the item {$items} from the master server.";
		}
		fclose($pf);
		fclose($fp);
		@chmod($file, 0777);
		@chmod(ROOT_PATH . "/sources/storeitems/backups/" . $items, 0777);
		return false;
	}

	function item_updater()
	{
		global $SKIN, $ADMIN, $INFO;
		$ibforums = Ibf::app();
		$this->check();
		$this->common_header('', 'IBStore Item Updater', 'Update IBStore Items');

		$ADMIN->html .= $SKIN->start_table("Updating");
		$ADMIN->html .= $SKIN->add_td_basic('Now Updating Items, Please Wait. This way take a few minutes depending on how fast your hosts connection is.');
		$ADMIN->html .= $SKIN->add_td_basic("<span color='red'><b>NOTE:</b></span> If you try to update to many times you will be banned from running ANY UPDATES.");
		$handle = opendir(ROOT_PATH . "/sources/storeitems/");
		while ($items = readdir($handle))
		{
			if (preg_match("/item_/", $items))
			{
				if ($items != '.' || $items != '..')
				{
					$message = $this->update($items);
					if (!$message)
					{
						$ADMIN->html .= $SKIN->add_td_basic($items . ' Has been updated to the latest version.');
					} else
					{
						$ADMIN->html .= $SKIN->add_td_basic($message);
					}
					$ADMIN->html .= $SKIN->add_td_basic("&nbsp;");
				}
			}

		}
		closedir($handle);
		$ADMIN->html .= $SKIN->add_td_basic('Updating Complete. If you have any problems after the update look at the Item Restore page.');

		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");

	}

	function delete_item()
	{
		global $ADMIN, $SKIN, $IN;
		$ibforums = Ibf::app();
		$this->check();
		$ibforums->db->exec("DELETE FROM ibf_store_shopstock WHERE id='{$IN['id']}' LIMIT 1");
		$ibforums->db->exec("DELETE FROM ibf_store_inventory WHERE item_id='{$IN['id']}' LIMIT 1");
		// IBStore: delete addon
		$ADMIN->save_log("Deleted Item");

		$ADMIN->done_screen("Item Deleted", "Administration CP Home", "act=index");

	}

	function item_logs()
	{
		global $SKIN, $ADMIN, $IN, $INFO, $MEMBER;
		$ibforums = Ibf::app();
		if ($MEMBER['mgroup'] != '4')
		{
			$ADMIN->error("Sorry, this is a Root Admin Feature Only.");
		}

		$ADMIN->page_detail = "This is where you can get a list of all of the IBStore Logs";
		$ADMIN->page_title  = "IBStore Logs";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'item_logs'),
		                                       2 => array('act', 'store'),
		                                  ));
		$SKIN->td_header[] = array("Log ID", "20%");
		$SKIN->td_header[] = array("Username", "20%");
		$SKIN->td_header[] = array("Action", "20%");
		$SKIN->td_header[] = array("Type", "30%");
		$SKIN->td_header[] = array("Date", "30%");
		$ADMIN->html .= $SKIN->start_table("Logs");

		if (empty($IN['page_num']))
		{
			$IN['page_num'] = 0;
		}
		$page    = $IN['page_num'];
		$limit   = 25;
		$pagetwo = $page + $limit;
		$stmt    = $ibforums->db->query("SELECT *
			    FROM ibf_store_logs
                            ORDER BY time DESC
                            LIMIT $page,$limit");
		while ($logs = $stmt->fetch())
		{

			$logs['message'] = str_replace('&gt;', '>', $logs['message']);
			$logs['message'] = str_replace('&lt;', '<', $logs['message']);
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       $logs['logid'],
			                                       $logs['username'],
			                                       $logs['message'],
			                                       $logs['type'],
			                                       date("F j, Y, g:i a", $logs['time'])
			                                  ));
		}

		$ADMIN->html .= $SKIN->add_td_basic("Showing Logs {$page} Through {$pagetwo}");
		if ($page != 0)
		{
			$last_page = $page - $limit;
			$next_last = "<a href='{$this->base_url}&act=store&code=item_logs&page_num={$last_page}'>&laquo; Last</a> || ";
		}
		$next_last .= "<a href='{$this->base_url}&act=store&code=item_logs&page_num={$pagetwo}'>Next &raquo;</a>";
		$ADMIN->html .= $SKIN->add_td_basic("<center><b>" . $next_last . "</b></center>");
		$ADMIN->html .= $SKIN->add_td_basic("<a href='{$this->base_url}&act=store&code=clearlog&type=ibf_store_logs'>Clear Logs</a>");
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");
	}

	function postparse($msg, $code = 1, $emoticon = 1)
	{
		$msg = $this->parser->convert(array(
		                                   'TEXT'    => $msg,
		                                   'SMILIES' => $emoticon,
		                                   'CODE'    => $code,
		                                   'HTML'    => 0
		                              ));
		return $msg;
	}

	function unpostparse($msg)
	{
		$msg = $this->parser->unconvert($msg, 1, 0);
		return $msg;
	}

	function mod_logs()
	{
		global $SKIN, $ADMIN, $IN, $INFO, $MEMBER;
		$ibforums = Ibf::app();
		if ($MEMBER['mgroup'] != '4')
		{
			$ADMIN->error("Sorry, this is a Root Admin Feature Only.");
		}

		$ADMIN->page_detail = "This is where you can get a list of all of the IBStore Mod Logs";
		$ADMIN->page_title  = "IBStore Mod Logs";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'mod_logs'),
		                                       2 => array('act', 'store'),
		                                  ));
		$SKIN->td_header[] = array("Log ID", "10%");
		$SKIN->td_header[] = array("Username", "20%");
		$SKIN->td_header[] = array("Action", "20%");
		$SKIN->td_header[] = array("User Reson", "30%");
		$SKIN->td_header[] = array("Type", "10%");
		$SKIN->td_header[] = array("Date", "30%");
		$ADMIN->html .= $SKIN->start_table("Logs");

		if (empty($IN['page_num']))
		{
			$IN['page_num'] = 0;
		}
		$page    = $IN['page_num'];
		$limit   = 25;
		$pagetwo = $page + $limit;
		$stmt    = $ibforums->db->query("SELECT *
                            FROM ibf_store_modlogs
                            ORDER BY time DESC
                            LIMIT $page,$limit");
		while ($logs = $stmt->fetch())
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       $logs['id'],
			                                       $logs['username'],
			                                       $logs['reson'],
			                                       $logs['user_reson'],
			                                       ucfirst($logs['type']),
			                                       date("F j, Y, g:i a", $logs['time'])
			                                  ));
		}
		$ADMIN->html .= $SKIN->add_td_basic("Showing Logs {$page} Through {$pagetwo}");
		if ($page != 0)
		{
			$last_page = $page - $limit;
			$next_last = "<a href='{$this->base_url}&act=store&code=mod_logs&page_num={$last_page}'>&laquo; Last</a> || ";
		}
		$next_last .= "<a href='{$this->base_url}&act=store&code=mod_logs&page_num={$pagetwo}'>Next &raquo;</a>";
		$ADMIN->html .= $SKIN->add_td_basic("<center><b>" . $next_last . "</b></center>");
		$ADMIN->html .= $SKIN->add_td_basic("<a href='{$this->base_url}&act=store&code=clearlog&type=ibf_store_modlogs'>Clear Logs</a>");
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");
	}

	function clearlog()
	{
		global $ADMIN, $SKIN, $IN;
		$ibforums = Ibf::app();
		$ibforums->db->exec("DELETE FROM " . $IN['type']);
		$ADMIN->save_log("Table: " . $IN['type'] . " Deleted of all logs.");

		$ADMIN->done_screen("Log Cleared", "Administration CP Home", "act=index");
	}

	function update_check()
	{
		global $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$this->common_header('', 'IBStore Update', 'Check for Updates');
		$ADMIN->html .= $SKIN->start_table("Update");
		$ADMIN->html .= $SKIN->add_td_basic("<img borer='0' src='" . $this->mirror_update . "updatecheck.jpeg?version={$this->ibsversion}'>");
		$ADMIN->html .= $SKIN->add_td_basic("You can download the latest version <a href='http://mods.ibplanet.com/db/?act=mod&id=2013'>Here</a>");
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("");

	}

	//-------------------------------------------------------------
	function do_quiz_update()
	{
		global $ADMIN, $IN;
		$ibforums  = Ibf::app();
		$item_name = explode("||", $IN['item_names']);
		foreach ($item_name as $items)
		{
			if (!preg_match("#(.+)=(.+)#is", $items, $match))
			{
				continue;
			}
			$item_names[$match[1]] = $match[2];
		}
		if (is_array($IN['quiz_items']))
		{
			foreach ($IN['quiz_items'] as $q_item)
			{
				if ($q_item == "none")
				{
					unset($quiz_items);
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

		$ibforums->db->exec("UPDATE ibf_quiz_info
			    SET quizname='{$IN['quiz_name']}',
				quizdesc='{$IN['quiz_desc']}',
				percent_needed='{$IN['perc_need']}',
				amount_won='{$IN['winnings']}',
				run_for='{$IN['q_run']}',
				let_only='{$IM['let_play']}',
				quiz_status='{$IN['quiz_status']}',
				timeout='{$IN['timeout']}',
				pending='{$IN['pending']}',
				quiz_items='{$quiz_items}'
			    WHERE q_id='{$IN['updateid']}'
                            LIMIT 1");
		$ADMIN->save_log("Quiz &quot;{$IN['quiz_name']}&quot; Edited.");

		$ADMIN->done_screen("Quiz Edited", "Administration CP Home", "act=index");
	}

	function edit_questions()
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('do_editquestions', 'IBStore Quizs', 'Edit A Quizs Questions & Answers');

		$ADMIN->html .= $SKIN->start_table("Quiz");
		$ADMIN->html .= $SKIN->add_td_basic("<span style='color:red'><b>Not Case Senstive!</b></span>");
		$ADMIN->html .= $SKIN->add_td_basic("Leave any Multiple Correct Answer, or Drop Down Answer field blank to not use that one.");

		$stmt = $ibforums->db->query("SELECT *
                    FROM ibf_quiz
			    WHERE quiz_id='{$IN['updateid']}'");
		if ($stmt->rowCount() <= 0)
		{
			$ADMIN->error("Could not find any Questions and Answers.");
		}
		$types = array(
			'single'   => "Single Question & Answer.",
			'multiq'   => "Single Question & Multiple Correct Answers.",
			'dropdown' => "Single Question & Drop Down Answers."
		);
		while ($quiz = $stmt->fetch())
		{
			$ADMIN->html .= $SKIN->add_td_basic("<b>&nbsp;<br />" . $types[$quiz['type']] . "</b>");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Question: </b>",
			                                       $SKIN->form_input('q_' . $quiz['mid'] . '_question', $this->unpostparse($quiz['question']))
			                                  ));
			if ($quiz['type'] == 'single')
			{
				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>Answer: </b>",
				                                       $SKIN->form_input('q_' . $quiz['mid'] . '_answer', $quiz['anwser'])
				                                  ));
			} else
			{
				if ($quiz['type'] == 'dropdown' || $quiz['type'] == 'multiq')
				{
					$answers = explode("||", $quiz['anwser']);
					foreach ($answers as $answer)
					{
						if (!preg_match("#{answer(1|2|3|4)_(0|1):(.+)}#is", $answer, $match))
						{
							continue;
						}
						if ($quiz['type'] == 'dropdown')
						{
							if ($match[2] == 1)
							{
								$checkbox = $SKIN->form_checkbox("q_" . $quiz['mid'] . "_" . $match[1] . "_correct", 1);
							} else
							{
								$checkbox = $SKIN->form_checkbox("q_" . $quiz['mid'] . "_" . $match[1] . "_correct", 0);
							}
							$extra[$match[1]] = " Is Correct Answer? " . $checkbox;
						}
						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       "<b>Answer {$num_letter[$match[1]]}: </b>",
						                                       $SKIN->form_input('q_' . $quiz['mid'] . '_answer_' . $match[1], $match[3]) . $extra[$match[1]]
						                                  ));
					}
					if ($quiz['type'] == 'multiq')
					{
						$ADMIN->html .= "<input type='hidden' name='q_" . $quiz['mid'] . "_1_correct' value='1'>";
						$ADMIN->html .= "<input type='hidden' name='q_" . $quiz['mid'] . "_2_correct' value='1'>";
						$ADMIN->html .= "<input type='hidden' name='q_" . $quiz['mid'] . "_3_correct' value='1'>";
						$ADMIN->html .= "<input type='hidden' name='q_" . $quiz['mid'] . "_4_correct' value='1'>";
					}
					unset($extra);
				}
			}
			$ADMIN->html .= "<input type='hidden' name='mid_" . $quiz['mid'] . "_type_" . $quiz['type'] . "' value='" . $quiz['mid'] . "'>";
		}
		$ADMIN->html .= "<input type='hidden' name='quiz_id' value='" . $IN['updateid'] . "'>";
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("Update!");
	}

	function update_questions()
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$quiz_id  = $IN['quiz_id'];

		foreach ($_POST as $field => $info)
		{
			if (!preg_match("#mid_(.+?)_type_(.+)#", $field, $match))
			{
				continue;
			}
			$mid      = $match[1];
			$question = addslashes(stripslashes($IN['q_' . $mid . '_question']));
			if ($question == "" || empty($question))
			{
				continue;
			}
			if ($match[2] == 'single')
			{
				$answer = addslashes(stripslashes($IN['q_' . $mid . '_answer']));

				$ibforums->db->exec("UPDATE ibf_quiz
                                            SET question='{$question}',
                                                anwser='{$answer}'
					    WHERE mid='{$mid}'
					    LIMIT 1");
			} else
			{
				if ($match[2] == 'dropdown' || $match[2] == 'multiq')
				{
					$IN['q_' . $mid . '_answer_1'] = addslashes(stripslashes($IN['q_' . $mid . '_answer_1']));
					$IN['q_' . $mid . '_answer_2'] = addslashes(stripslashes($IN['q_' . $mid . '_answer_2']));
					$IN['q_' . $mid . '_answer_3'] = addslashes(stripslashes($IN['q_' . $mid . '_answer_3']));
					$IN['q_' . $mid . '_answer_4'] = addslashes(stripslashes($IN['q_' . $mid . '_answer_4']));
					if (!$IN['q_' . $mid . '_1_correct'])
					{
						$IN['q_' . $mid . '_1_correct'] = 0;
					}
					if (!$IN['q_' . $mid . '_2_correct'])
					{
						$IN['q_' . $mid . '_2_correct'] = 0;
					}
					if (!$IN['q_' . $mid . '_3_correct'])
					{
						$IN['q_' . $mid . '_3_correct'] = 0;
					}
					if (!$IN['q_' . $mid . '_4_correct'])
					{
						$IN['q_' . $mid . '_4_correct'] = 0;
					}
					$quiz_answers = "{answer1_" . $IN['q_' . $mid . '_1_correct'] . ":" . $IN['q_' . $mid . '_answer_1'] . '}||' . "{answer2_" . $IN['q_' . $mid . '_2_correct'] . ":" . $IN['q_' . $mid . '_answer_2'] . '}||' . "{answer3_" . $IN['q_' . $mid . '_3_correct'] . ":" . $IN['q_' . $mid . '_answer_3'] . '}||' . "{answer4_" . $IN['q_' . $mid . '_4_correct'] . ":" . $IN['q_' . $mid . '_answer_4'] . '}';

					$ibforums->db->exec("UPDATE ibf_quiz
                                            SET question='{$question}',
                                                anwser='{$quiz_answers}'
					    WHERE mid='{$mid}'
					    LIMIT 1");
				}
			}

		}
		$ADMIN->save_log("Quiz ID, " . $quiz_id . " Was Edited");

		$ADMIN->done_screen("Quiz Edited", "Administration CP Home", "act=index");
	}

	function quiz_settings()
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header($IN['quiztype']
			? "do_quiz_update"
			: "do_quiz_settings", 'IBStore Quizs', 'Add a Quiz');

		$ADMIN->html .= $SKIN->start_table("Quiz details");
		if (!$IN['quiztype'])
		{
			$quiz['name']      = "";
			$quiz['desc']      = "";
			$quiz['bbcode']    = 1;
			$quiz['perc_need'] = 80;
			$quiz['winnings']  = 500;
			$quiz['timeout']   = 20;
			$quiz['let_play']  = 0;
			$quiz['runtime']   = 7;
			$quiz['items_won'] = array();
		} else
		{
			$stmt = $ibforums->db->query("SELECT *
                                    FROM ibf_quiz_info
				    WHERE q_id='{$IN['updateid']}'
				    LIMIT 1");
			if ($stmt->rowCount() <= 0)
			{
				$ADMIN->error("Could not find Quiz.");
			}
			$quiz_info         = $stmt->fetch();
			$quiz['name']      = $quiz_info['quizname'];
			$quiz['desc']      = $this->unpostparse($quiz_info['quizdesc']);
			$quiz['bbcode']    = 1;
			$quiz['perc_need'] = $quiz_info['percent_needed'];
			$quiz['winnings']  = $quiz_info['amount_won'];
			$quiz['timeout']   = $quiz_info['timeout'];
			$quiz['let_play']  = $quiz_info['let_only'];
			$quiz['runtime']   = $quiz_info['run_for'];
			$quiz['items_won'] = explode("=", str_replace("|", "=", $quiz_info['quiz_items']));

			$ADMIN->html .= "<input type='hidden' name='updateid' value='{$IN['updateid']}'>";
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Quiz Name: </b><br>The Name for this Quiz.",
		                                       $SKIN->form_input("quiz_name", $quiz['name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Quiz Description: </b><br>Description for this quiz.<br /> BBCode and Emoticions are Enabled!",
		                                       $SKIN->form_textarea("quiz_desc", $quiz['desc'], 60, 10)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enable BBCode?</b><br>If yes will parse all BBCodes in Quiz Questions.",
		                                       $SKIN->form_yes_no("bbcode", $quiz['bbcode'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Percent Needed: </b><br>Percent of correct answer s needed, to get the winnings.",
		                                       $SKIN->form_input("perc_need", $quiz['perc_need'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Winnings: </b><br>The amount of points a user gets if they get or go over the percent needed.",
		                                       $SKIN->form_input("winnings", $quiz['winnings'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Time Out: </b><br>The amount of minutes before a quiz auto submits it self. This is to stop people from being able to look up answers. (0 will disable it)",
		                                       $SKIN->form_input("timeout", $quiz['timeout'])
		                                  ));
		if ($IN['quiztype'])
		{
			$status[] = array('OPEN', 'Open');
			$status[] = array('CLOSED', 'Closed');
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Quiz Status: </b>",
			                                       $SKIN->form_dropdown('quiz_status', $status, $quiz_info['quiz_status'])
			                                  ));
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Only let X amount of users play: </b><br>After X (X being what you set) users have played the quiz will automatically close. (Put 0 to disable)",
		                                       $SKIN->form_input("let_play", $quiz['let_play'])
		                                  ));

		$stmt    = $ibforums->db->query("SELECT
                              id,item_name,stock
			    FROM ibf_store_shopstock
			    ORDER BY item_name DESC");
		$items[] = array('none', 'No Items for Prize');
		if ($stmt->rowCount() >= 8)
		{
			$row = 8;
		} else
		{
			$row = $stmt->rowCount();
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
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Item Prizes:</b><br> The Item(s) a user will win from the quiz if they get the needed percentage of correct anwsers.",
		                                       $SKIN->form_multiselect("quiz_items[]", $items, $quiz['items_won'], $row)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Quiz Running Time: </b>",
		                                       $SKIN->form_input("q_run", $quiz['runtime']) . '...days.'
		                                  ));
		$ADMIN->html .= "<input type='hidden' name='item_names' value='{$item_name}'>";
		$ADMIN->html .= "<input type='hidden' name='isadding' value='1'>";
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();

	}

	function do_quiz_settings()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('do_quiz_settings', 'IBStore Quizs', 'Add a Quiz');
		$time = time();
		if ($IN['isadding'])
		{
			$item_name = explode("||", $IN['item_names']);
			foreach ($item_name as $items)
			{
				if (!preg_match("#(.+)=(.+)#is", $items, $match))
				{
					continue;
				}
				$item_names[$match[1]] = $match[2];
			}
			if (is_array($IN['quiz_items']))
			{
				foreach ($IN['quiz_items'] as $q_item)
				{
					if ($q_item == "none")
					{
						unset($quiz_items);
						break;
					}
					$quiz_items[] = $q_item . "=" . $item_names[$q_item];
				}
				$quiz_items = is_array($quiz_items)
					? implode("|", $quiz_items)
					: $quiz_items;
			} else
			{
				$quiz_items = ($IN['quiz_items'] == "none")
					? ""
					: $IN['quiz_items'];
			}

			$params = array(
				'quizname'       => $IN['quiz_name'],
				'quizdesc'       => $IN['quiz_desc'],
				'percent_needed' => $IN['perc_need'],
				'amount_won'     => $IN['winnings'],
				'started_on'     => $time,
				'run_for'        => $IN['q_run'],
				'let_only'       => $IN['let_play'],
				'quiz_status'    => 'OPEN',
				'timeout'        => $IN['timeout'],
				'pending'        => 1,
				'quiz_items'     => $quiz_items
			);
			$ibforums->db->insertRow('ibf_quiz_info', $params);
			//$ibforums->db->exec("INSERT INTO ibf_quiz_info
			//	    VALUES('','{}','{}','{}','{}','{}','{}','{}','OPEN','{}','1','{}')");

			$temp['quizid'] = $ibforums->db->lastInsertId();
		}

		if ($IN['isadding'])
		{
			$quizid = $temp['quizid'];
			unset($IN['isadding']);
		} else
		{
			$quizid = $IN['quizid'];
		}
		if (!$IN['i'])
		{
			$IN['i'] = 1;
		} else
		{
			if ($IN['quiz_question'])
			{
				$IN['i']++;
			}
		}
		$ADMIN->html .= "<input type='hidden' name='quizid' value='" . $quizid . "'>";
		$ADMIN->html .= "<input type='hidden' name='bbcode' value='" . $IN['bbcode'] . "'>";
		$ADMIN->html .= "<input type='hidden' name='i' value='" . $IN['i'] . "'>";
		$ADMIN->html .= "<input type='hidden' name='quiz_name' value='" . $IN['quiz_name'] . "'>";

		if ($IN['addtype'] == 'single')
		{
			$this->addsingle();
		} else
		{
			if ($IN['addtype'])
			{
				$this->addmulti();
			}
		}

		if ($IN['action'] == 'finish')
		{
			$this->finishquiz();
		} else
		{
			if ($IN['action'] == 'multiq' || $IN['action'] == 'dropdown')
			{
				$this->multi();
			} else
			{
				$this->single();
			}
		}

		$ADMIN->html .= $SKIN->start_table("Next");

		$SKIN->td_header[] = array("{none}", "50%");
		$SKIN->td_header[] = array("{none}", "50%");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Next Add A...</b><br>The type of quiz question to add. <b>(Select Finished Adding to stop adding questions)</b>",
		                                       $SKIN->form_dropdown('action', array(
		                                                                           0 => array(
			                                                                           'single',
			                                                                           'Single Question & Answer'
		                                                                           ),
		                                                                           1 => array(
			                                                                           'dropdown',
			                                                                           'Single Question & Dropdown Answers'
		                                                                           ),
		                                                                           2 => array(
			                                                                           'multiq',
			                                                                           'Single Question & Multiple Correct Answers'
		                                                                           ),
		                                                                           3 => array(
			                                                                           'finish',
			                                                                           'Done. Add Quiz!'
		                                                                           )
		                                                                      ))
		                                  ));

		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("Submit");
	}

	function finishquiz()
	{
		global $ADMIN, $SKIN, $IN;
		$ibforums = Ibf::app();

		$ibforums->db->exec("UPDATE ibf_quiz_info
			    SET pending='0'
			    WHERE q_id='{$IN['quizid']}'
			    LIMIT 1");
		$ADMIN->save_log("Quiz &quot;{$IN['quiz_name']}&quot; Created.");

		$ADMIN->done_screen("Quiz Created", "Administration CP Home", "act=index");
	}

	function addsingle()
	{
		global $IN;
		$ibforums = Ibf::app();

		if (!$IN['quiz_question'] || !$IN['answer'])
		{
			return false;
		}

		if ($IN['bbcode'])
		{
			$IN['quiz_question'] = $this->postparse($IN['quiz_question'], 1, 0);
		}
		$IN['quiz_question'] = addslashes($IN['quiz_question']);

		$ibforums->db->exec("INSERT INTO ibf_quiz
			    VALUES('','{$IN['quizid']}','{$IN['quiz_question']}','{$IN['answer']}','single')");
	}

	function addmulti()
	{
		global $IN;
		$ibforums = Ibf::app();

		if (!$IN['quiz_question'])
		{
			return false;
		}
		if (!$IN['answer_1'] && !$IN['answer_2'] && !$IN['answer_3'] && !$IN['answer_4'])
		{
			return false;
		}

		if ($IN['bbcode'])
		{
			$IN['quiz_question'] = $this->postparse($IN['quiz_question'], 1, 0);
		}
		$IN['quiz_question'] = addslashes($IN['quiz_question']);
		if ($IN['addtype'] == 'multiq')
		{
			$correct = 1;
		} else
		{
			$correct = 0;
		}

		if (!$IN['correct_1'])
		{
			$IN['correct_1'] = $correct;
		}
		if (!$IN['correct_2'])
		{
			$IN['correct_2'] = $correct;
		}
		if (!$IN['correct_3'])
		{
			$IN['correct_3'] = $correct;
		}
		if (!$IN['correct_4'])
		{
			$IN['correct_4'] = $correct;
		}

		$quiz_answers = "{answer1_" . $IN['correct_1'] . ":" . $IN['answer_1'] . '}||' . "{answer2_" . $IN['correct_2'] . ":" . $IN['answer_2'] . '}||' . "{answer3_" . $IN['correct_3'] . ":" . $IN['answer_3'] . '}||' . "{answer4_" . $IN['correct_4'] . ":" . $IN['answer_4'] . '}';

		$ibforums->db->exec("INSERT INTO ibf_quiz
			    VALUES('','{$IN['quizid']}','{$IN['quiz_question']}','{$quiz_answers}','{$IN['addtype']}')");
	}

	function multi()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$ADMIN->html .= $SKIN->start_table("Quiz Questions & Answers");
		$ADMIN->html .= $SKIN->add_td_basic("<b><span style='color:red'>Not Case Sensetive</span></b>");
		if ($IN['addtype'])
		{
			$type    = array('multiq' => "Multiple Correct Answers", 'dropdown' => "Drop Down Answer");
			$message = "New Question & Answer Added! <br />Currently adding a <b>" . $type[$IN['action']] . "</b>! Number of questions: {$IN['i']} <br />";
		}
		$ADMIN->html .= $SKIN->add_td_basic($message . " <b>Leave any field (expect for question) blank to not use that one.</b>");
		if ($IN['action'] == 'dropdown')
		{
			$extra[] = " Is Correct Answer? " . $SKIN->form_checkbox("correct_1");
			$extra[] = " Is Correct Answer? " . $SKIN->form_checkbox("correct_2");
			$extra[] = " Is Correct Answer? " . $SKIN->form_checkbox("correct_3");
			$extra[] = " Is Correct Answer? " . $SKIN->form_checkbox("correct_4");
		}
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Question: </b>",
		                                       $SKIN->form_input('quiz_question')
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Answer One: </b>",
		                                       $SKIN->form_input('answer_1') . $extra[0]
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Answer Two: </b>",
		                                       $SKIN->form_input('answer_2') . $extra[1]
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Answer Three: </b>",
		                                       $SKIN->form_input('answer_3') . $extra[2]
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Answer Four: </b>",
		                                       $SKIN->form_input('answer_4') . $extra[3]
		                                  ));

		$ADMIN->html .= "<input type='hidden' name='addtype' value='{$IN['action']}'>";

		$ADMIN->html .= $SKIN->end_table();
	}

	function single()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$ADMIN->html .= $SKIN->start_table("Quiz Questions & Answers");
		$ADMIN->html .= $SKIN->add_td_basic("<b><span style='color:red'>Not Case Sensetive</span></b>");
		if ($IN['addtype'])
		{
			$message = "New <b>Single</b> Question & Answer Added! <br /> Number of questions: {$IN['i']}";
		}
		$ADMIN->html .= $SKIN->add_td_basic($message . " <b>Leave any field (expect for question) blank to not use that one.</b>");
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Question: </b>",
		                                       $SKIN->form_input('quiz_question')
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Answer: </b>",
		                                       $SKIN->form_input('answer')
		                                  ));

		$ADMIN->html .= "<input type='hidden' name='addtype' value='single'>";

		$ADMIN->html .= $SKIN->end_table();
	}

	function qdowhat()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('quiz_settings', 'IBStore Quizs', 'Add a Quiz');
		$stmt = $ibforums->db->query("SELECT *
                            FROM ibf_quiz_info
                            WHERE q_id>'0'");
		while ($temp = $stmt->fetch())
		{
			$quizs[] = array($temp['q_id'], $temp['quizname']);
		}
		if (!is_array($quizs))
		{
			$quizs[] = array('', 'Cannot find any Quizs');
		}

		$ADMIN->html .= $SKIN->start_table("Quizs");
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Quiz To Edit: </b>",
		                                       $SKIN->form_dropdown('updateid', $quizs)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What To Do: </b>",
		                                       $SKIN->form_dropdown('dowhat', array(
		                                                                           array(1, 'Choose an Action'),
		                                                                           array(1, 'Edit'),
		                                                                           array(2, 'Edit Questions & Answers'),
		                                                                           array(0, 'Delete Quiz')
		                                                                      ))
		                                  ));
		$ADMIN->html .= "<input type='hidden' name='quiztype' value='1'>";
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();
	}

	function qdelete()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$ibforums->db->exec("DELETE FROM ibf_quiz_info
			    WHERE q_id='{$IN['updateid']}'
			    LIMIT 1");
		$ibforums->db->exec("DELETE FROM ibf_quiz
			    WHERE quiz_id='{$IN['updateid']}'");
		$ADMIN->save_log("Quiz ID, " . $IN['updateid'] . " Was Deleted");

		$ADMIN->done_screen("Quiz Deleted", "Administration CP Home", "act=index");
	}

	//-----------------------------------------------------------------------

	function add()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('itemproperties', 'IBStore Stocker', 'Stock up you\'re store here');
		$ADMIN->html .= $SKIN->start_table("Add Item");

		$handle = opendir(ROOT_PATH . "/sources/storeitems/");
		$item[] = array('', 'Select A Item');
		while ($items = readdir($handle))
		{
			if (preg_match("/item_/", $items))
			{
				if ($items != '.' || $items != '..')
				{
					$items  = str_replace("." . $INFO['php_ext'], "", $items);
					$item[] = array($items, $this->store_lib->item_names($items));
				}
			}

		}

		closedir($handle);

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Select a Item To Stock:</b><br>",
		                                       $SKIN->form_dropdown('item_name', $item, $IN['item_name'])
		                                  ));

		$ADMIN->html .= "<input type='hidden' name='itemtype' value='0'>";
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();
	}

	function itemproperties()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('do_stockedits', 'IBStore Stocker Extra', 'Stock up you\'re store here');
		$IN['item_name'] .= '.' . $INFO['php_ext'];
		$file_name = $INFO['base_dir'] . "sources/storeitems/" . $IN['item_name'];

		if (!file_exists($file_name) && !$IN['itemtype'])
		{
			$ADMIN->error("Error, i can't seem to find the file " . $IN['item_name'] . " Please make sure it is actully their, and that it is located in " . $INFO['base_dir'] . "sources/storeitems");
		} else
		{
			if (!$IN['itemtype'])
			{
				require_once($file_name);
				$add_item = new item;
			}
		}
		if (!$IN['itemtype'])
		{
			$item['name']          = $add_item->name;
			$item['desc']          = $add_item->desc;
			$item['item_limit']    = 0;
			$item['price']         = 1;
			$item['stock']         = 50;
			$item['restockamount'] = 10;
			$item['restockwait']   = 1;
			$item['module']        = $IN['item_name'];
			$EXTRA['extra_one']    = $add_item->extra_one;
			$EXTRA['extra_two']    = $add_item->extra_two;
			$EXTRA['extra_three']  = $add_item->extra_three;
		} else
		{
			$stmt      = $ibforums->db->query("SELECT *
                                    FROM ibf_store_shopstock
                                    WHERE id='{$IN['id']}'
                                    LIMIT 1");
			$itemm     = $stmt->fetch();
			$file_name = $INFO['base_dir'] . "sources/storeitems/" . $itemm['module'] . "." . $INFO['php_ext'];
			if (!file_exists($file_name))
			{
				$ADMIN->error("Error, i can't seem to find the file " . $itemm['item_name'] . " Please make sure it is actully their, and that it is located in " . $INFO['base_dir'] . "sources/storeitems");
			}
			require_once($file_name);
			$add_item         = new item;
			$item['id']       = $itemm['id'];
			$item['name']     = stripslashes($itemm['item_name']);
			$item['desc']     = stripslashes($itemm['item_desc']);
			$item['icon']     = $itemm['icon'];
			$item['avalible'] = $itemm['avalible'];
			$item['price']    = $itemm['sell_price'];
			$item['stock']    = $itemm['stock'];
			preg_match("#(.+?)_(.+?)#", $itemm['restock_type'], $match);
			$item['restockamount'] = $itemm['restock_amount'];
			$item['restockwait']   = $match[1];
			$item['item_timewait'] = $match[2];
			$item['module']        = $itemm['module'] . '.php';
			$item['item_limit']    = $itemm['item_limit'];
			$item['category']      = $itemm['category'];
			$EXTRA['extra_one']    = $itemm['extra_one'];
			$EXTRA['extra_two']    = $itemm['extra_two'];
			$EXTRA['extra_three']  = $itemm['extra_three'];
		}

		$item['item_wait'][] = array('dont', "Don't Restock");
		$item['item_wait'][] = array('m', "Minute(s)");
		$item['item_wait'][] = array('h', "Hour(s)");
		$item['item_wait'][] = array('d', "Day(s)");
		$item['item_wait'][] = array('w', "Week(s)");

		$ADMIN->html .= $SKIN->start_table("Item Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Item Name:</b><br>The items name that will apper in the store.",
		                                       $SKIN->form_input("item__name", $item['name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Item Description:</b><br>A short description telling users what the item does.",
		                                       $SKIN->form_input("item_desc", $item['desc'])
		                                  ));

		if ($IN['itemtype'])
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Hide This Item?</b><br>Usualfull if you want to be able to stop people form buying this item. If yes it will not apper in the shop untill you turn it to \"No\".",
			                                       $SKIN->form_dropdown("item_on", array(
			                                                                            0 => array(0, 'No'),
			                                                                            1 => array(1, 'Yes')
			                                                                       ), $item['avalible'])
			                                  ));
		}

		$ADMIN->html .= $SKIN->add_td_basic('Stock/Price', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of points to sell item for:</b><br>Items Price.",
		                                       $SKIN->form_input("item_price", $item['price'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount to Stock:</b><br>Items Stock.",
		                                       $SKIN->form_input("item_stock", $item['stock'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Restock Settings', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Restock Amount:</b><br>The amount of Stock that is added to this item when the stock falls below zero.",
		                                       $SKIN->form_input("restock_amount", $item['restockamount'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of time to wait?</b><br>The amount of time to wait before restocking",
		                                       $SKIN->form_input("stock_wait", $item['restockwait'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Restock wait in?</b><br>This lets you select wether you want to wait to restock in Minutes, Hours or Days.",
		                                       $SKIN->form_dropdown("item_waitin", $item['item_wait'], $item['item_timewait'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Other Options', 'left', 'catrow2');

		$icons[] = array('blank.gif', 'Select A Icon');

		$handle = opendir($INFO['html_dir'] . "/store/icons/");

		while ($icon = readdir($handle))
		{
			if (preg_match("/(.jpg|.gif|.png)/", $icon))
			{
				if ($icon != '.' || $icon != '..')
				{
					$icons[]        = array($icon, $icon);
					$random_image[] = $INFO['html_url'] . "/store/icons/" . $icon;
				}
			}
		}
		if ($IN['itemtype'])
		{
			$image = $INFO['html_url'] . "/store/icons/" . $item['icon'];
		} else
		{
			$image    = $random_image[mt_rand(0, count($random_image) - 1)];
			$icons[0] = array($image, 'Select A Icon');
		}
		$ADMIN->html .= "<script language='javascript'>
				 <!--
			 	function show_icon() {
		 			var icon_url = '{$INFO['html_url']}/store/icons/' + document.theAdminForm.item_icon.value;
					document.images['iconpreview'].src = icon_url;
				}
				//-->
				</script>
				";
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Select a Item Icon:</b><br>",
		                                       $SKIN->form_dropdown('item_icon', $icons, $item['icon'], "onChange='show_icon()'") . "&nbsp;&nbsp;<img src='{$image}' name='iconpreview' border='0'>",
		                                  ));
		$category[] = array('shop', 'Main Category');
		$stmt       = $ibforums->db->query("SELECT *
                            FROM ibf_store_category
                            WHERE catid>'0'
                            ORDER BY catid DESC");
		while ($temp = $stmt->fetch())
		{
			$category[] = array($temp['catid'], $temp['cat_name']);
		}
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Select a Category:</b><br>This is the Category that the item will apper in." . $this->extra_shop,
		                                       $SKIN->form_dropdown('item_category', $category, $item['category'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Item Limiter?</b><br>This is the amount of items a user is allowed to have of this one item at a time. <br />(Put 0 or leave blank to disable)",
		                                       $SKIN->form_input("item_limit", $item['item_limit'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_basic('Extra Settings', 'left', 'catrow2');

		if (!$html = $add_item->on_add($EXTRA))
		{
			$extra_html = $SKIN->add_td_basic('No Extra Options');
		} else
		{
			/*
						if($add_item->apply_protect) {
							$stmt = $ibforums->db->query("SELECT g_id,
															   g_title
														FROM ibf_groups
														ORDER BY g_title DESC");
							$groups[] = array('d','Dont Protect Anyone');
							$row = $stmt->rowCount();
							while($r = $stmt->fetch()) {
								$groups[] = array($r['g_id'],$r['g_title']);
								if($row > 10) $row = 10;

							}
							$extra_html = $SKIN->add_td_row( array("<b>Protected Groups:</b><br>The User groups who are not effected by this item.",
											   $SKIN->form_multiselect( "protect_groups[]", $groups, array(),$row)
											   )      );
						}
			*/
			$extra_html .= $html;
		}
		$ADMIN->html .= $extra_html;
		$ADMIN->html .= "<input type='hidden' name='itemname' value='{$item['module']}'>";
		if ($IN['itemtype'])
		{
			$ADMIN->html .= "<input type='hidden' name='itemtype' value='1'>";
		}
		if ($IN['itemtype'])
		{
			$ADMIN->html .= "<input type='hidden' name='id' value='{$item['id']}'>";
		}
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();
	}

	function check()
	{
		global $ADMIN, $SKIN;
		$ibforums = Ibf::app();
		if (file_exists(ROOT_PATH . "/sources/storeitems/ibs_lock.lock"))
		{
			$ADMIN->error("You have been banned from running any updates");
		}
		return;
	}

	function do_stockedits()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('do_stockadd_two', 'IBStore Item Edits', 'The Edits you have to do to you\'re items before adding them');
		$file_name = $INFO['base_dir'] . "sources/storeitems/" . $IN['itemname'];
		if (!file_exists($file_name))
		{
			$ADMIN->error("Error, i can't seem to find the file " . $IN['itemname'] . " Please make sure it is actully their, and that it is located in " . $INFO['base_dir'] . "sources/storeitems");
		}
		$IN['item_module']  = preg_replace("/." . $INFO['php_ext'] . "/", "", $IN['itemname']);
		$IN['restock_type'] = $IN['stock_wait'] . '_' . $IN['item_waitin'];
		if (isset($IN['item_waitin']))
		{
			if ($IN['item_waitin'] == 'm')
			{
				$IN['stock_wait'] = 60 * $IN['stock_wait'];
			} else
			{
				if ($IN['item_waitin'] == 'h')
				{
					$IN['stock_wait'] = 3600 * $IN['stock_wait'];
				} else
				{
					if ($IN['item_waitin'] == 'd')
					{
						$IN['stock_wait'] = 86400 * $IN['stock_wait'];
					} else
					{
						if ($IN['item_waitin'] == 'w')
						{
							$IN['stock_wait'] = 604800 * $IN['stock_wait'];
						}
					}
				}
			}
		}
		//$IN['protect_groups'] = implode("|",$IN['protect_groups']);
		require_once($file_name);
		$item = new item;
		if ($html = $item->on_add_edits($this))
		{
			$ADMIN->html .= "<input type='hidden' name='item__name' 	value='{$IN['item__name']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_icon' 		value='{$IN['item_icon']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_price' 	value='{$IN['item_price']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_desc' 		value='{$IN['item_desc']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_module' 	value='{$IN['item_module']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_stock' 	value='{$IN['item_stock']}'>";
			$ADMIN->html .= "<input type='hidden' name='avalible' 		value='{$IN['avalible']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_category' 	value='{$IN['item_category']}'>";
			$ADMIN->html .= "<input type='hidden' name='extra_one' 		value='{$IN['extra_one']}'>";
			$ADMIN->html .= "<input type='hidden' name='extra_two' 		value='{$IN['extra_two']}'>";
			$ADMIN->html .= "<input type='hidden' name='extra_three' 	value='{$IN['extra_three']}'>";
			$ADMIN->html .= "<input type='hidden' name='restock_amount'	value='{$IN['restock_amount']}'>";
			$ADMIN->html .= "<input type='hidden' name='stock_wait' 	value='{$IN['stock_wait']}'>";
			$ADMIN->html .= "<input type='hidden' name='item_limit'		value='{$IN['item_limit']}'>";
			if ($IN['itemtype'])
			{
				$ADMIN->html .= "<input type='hidden' name='itemtype' value='1'>";
			}
			if ($IN['itemtype'])
			{
				$ADMIN->html .= "<input type='hidden' name='restock_type' value='{$IN['restock_type']}'>";
			}
			//if($IN['itemtype']) $ADMIN->html .= "<input type='hidden' name='protect_groups' value='{$IN['protect_groups']}'>";
			if ($IN['itemtype'])
			{
				$ADMIN->html .= "<input type='hidden' name='id' value='{$IN['id']}'>";
			}
			$ADMIN->html .= $SKIN->start_table("Edits");
			$ADMIN->html .= $html;
		} else
		{
			if ($IN['itemtype'])
			{
				$this->do_stockedits_edit();
			} else
			{
				$this->do_stockadd_two();
			}
		}

		$this->common_footer();
	}

	function do_stockedits_edit()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$ibforums->db->exec("UPDATE ibf_store_shopstock
			    SET item_name='{$IN['item__name']}',
				   icon='{$IN['item_icon']}',
				   item_desc='{$IN['item_desc']}',
				   sell_price='{$IN['item_price']}',
				   stock='{$IN['item_stock']}',
				   category='{$IN['item_category']}',
				   avalible='{$IN['item_on']}',
				   extra_one='{$IN['extra_one']}',
				   extra_two='{$IN['extra_two']}',
				   extra_three='{$IN['extra_three']}',
				   restock_amount='{$IN['restock_amount']}',
				   restock_wait='{$IN['stock_wait']}',
				   item_limit='{$IN['item_limit']}',
				   restock_type='{$IN['restock_type']}'
			    WHERE id='{$IN['id']}'
                            LIMIT 1");

		$ADMIN->save_log("Updated Item: " . $IN['item__name']);

		$ADMIN->done_screen("Updated Item", "Administration CP Home", "act=index");
	}

	function do_stockadd_two()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		require_once(ROOT_PATH . "/sources/storeitems/" . $IN['item_module'] . '.php');
		$item = new item;
		$item->on_add_extra();
		if ($IN['itemtype'])
		{
			$this->do_stockedits_edit();
			return;
		}
		$IN['item__name'] = addslashes($IN['item__name']);
		$IN['item_desc']  = addslashes($IN['item_desc']);
		$ibforums->db->exec("INSERT INTO ibf_store_shopstock
				    (id,item_name,icon,item_desc,sell_price,module,stock,avalible,category,extra_one,extra_two,extra_three,soldout_time,restock_amount,restock_wait,item_limit,restock_type)
					VALUES('','{$IN['item__name']}','{$IN['item_icon']}','{$IN['item_desc']}','{$IN['item_price']}','{$IN['item_module']}',
					'{$IN['item_stock']}','{$IN['avalible']}','{$IN['item_category']}','{$IN['extra_one']}','{$IN['extra_two']}','{$IN['extra_three']}','0','{$IN['restock_amount']}','{$IN['stock_wait']}','{$IN['item_limit']}','{$IN['restock_type']}')");
		$ADMIN->save_log("Added Item: " . $IN['item_module'] . " To Store");

		$ADMIN->done_screen("Forum Configurations updated", "Administration CP Home", "act=index");

	}

	function itemedit()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('itemproperties', 'IBStore Edit Items', 'Edit Items you have already stocked');
		$ADMIN->html .= $SKIN->start_table("Edit Item:");

		$item[] = array('', 'Select A Item');
		$stmt   = $ibforums->db->query("SELECT
                                id,item_name,stock,module
                            FROM ibf_store_shopstock
                            WHERE id>0");
		while ($temp = $stmt->fetch())
		{
			if (file_exists($INFO['base_dir'] . 'sources/storeitems/' . $temp['module'] . '.' . $INFO['php_ext']))
			{
				if (!$temp['stock'])
				{
					$extra = " (Sold Out!)";
				} else
				{
					$extra = " (Stock: {$temp['stock']})";
				}

			} else
			{
				$extra = ' - ' . $this->item_notfound;
			}

			$item[] = array($temp['id'], $temp['item_name'] . $extra);
		}
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Select the Item to Edit:</b><br>",
		                                       $SKIN->form_dropdown('id', $item, $IN['item_module'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What do you want to do?</b><br>This will let you ethere edit or delete a item from the shop.",
		                                       $SKIN->form_dropdown("dowhat", array(
		                                                                           0 => array(0, 'Edit'),
		                                                                           1 => array(1, 'Delete')
		                                                                      ))
		                                  ));
		$ADMIN->html .= "<input type='hidden' name='itemtype' value='1'>";
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();
	}

	function add_category()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$this->common_header('do_add_category', 'IBStore Add Category', 'Add a new shop Category');

		$ADMIN->html .= $SKIN->start_table("Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category Name:</b><br>The Categorys Name that will apper in the Shop Categorys.",
		                                       $SKIN->form_input("cat_name", $IN['cat_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category Description:</b><br>A short little Description if for the Category (This will apper on the portal, BBCode is enabled).",
		                                       $SKIN->form_textarea("cat_desc", $IN['cat_desc'], 50, 5)
		                                  ));

		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();
	}

	function select_category()
	{
		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$this->common_header('edit_category', 'IBStore Category Editer', 'Select a category to edit');
		$stmt = $ibforums->db->query("SELECT *
                            FROM ibf_store_category
                            ORDER BY catid DESC");
		while ($cat = $stmt->fetch())
		{
			$catt[] = array($cat['catid'], $cat['cat_name']);
		}
		if (!is_array($catt))
		{
			$catt[] = array('', 'Cannot find any Categorys');
		}
		$ADMIN->html .= $SKIN->start_table("Category");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Select the Category to effect:</b><br>The Category you want to edit/delete.",
		                                       $SKIN->form_dropdown('catid', $catt, $IN['catid'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What do you want to do?</b>",
		                                       $SKIN->form_dropdown('effect', array(
		                                                                           0 => array('edit', 'Edit'),
		                                                                           1 => array('del', 'Delete')
		                                                                      ))
		                                  ));

		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer("Go!");

	}

	function edit_category()
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		if ($IN['effect'] == 'del')
		{
			$ibforums->db->exec("DELETE FROM ibf_store_category
                                    WHERE catid='{$IN['catid']}'
                                    LIMIT 1");
			$ADMIN->save_log("Deleted An Category");

			$ADMIN->done_screen("Category Delete.", "Administration CP Home", "act=index");
		}
		$stmt = $ibforums->db->query("SELECT *
                            FROM ibf_store_category
                            WHERE catid='{$IN['catid']}'
                            LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$ADMIN->error("We could not find the category your looking for");
		}
		$row = $stmt->fetch();
		$this->common_header('do_cat_edit', 'IBStore Category Editer', 'Edit Categorys');
		$ADMIN->html .= $SKIN->start_table("Category");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category Name:</b><br>",
		                                       $SKIN->form_input("cat_name", $row['cat_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category Description:</b><br>",
		                                       $SKIN->form_textarea("cat_desc", $this->unpostparse($row['cat_desc']), 50, 5)
		                                  ));
		$ADMIN->html .= "<input type='hidden' name='catid' value='{$row['catid']}'>";
		$ADMIN->html .= $SKIN->end_table();
		$this->common_footer();

	}

	function do_cat_edit()
	{
		global $IN, $ADMIN;
		$ibforums       = Ibf::app();
		$IN['cat_desc'] = addslashes($this->postparse($IN['cat_desc']));
		$ibforums->db->exec("UPDATE ibf_store_category
                            SET cat_name='{$IN['cat_name']}',
                                cat_desc='{$IN['cat_desc']}'
                            WHERE catid='{$IN['catid']}'
                            LIMIT 1");
		$ADMIN->save_log("Edited Category: " . $IN['cat_name']);

		$ADMIN->done_screen("Category Updated!", "Administration CP Home", "act=index");

	}

	function do_add_category()
	{
		global $IN, $INFO, $ITEM, $SKIN, $ADMIN;
		$ibforums       = Ibf::app();
		$IN['cat_desc'] = addslashes($this->postparse($IN['cat_desc']));
		$ibforums->db->exec("INSERT INTO ibf_store_category
                            (catid,cat_name,cat_desc) VALUES
                            ('','{$IN['cat_name']}','{$IN['cat_desc']}')");
		$ADMIN->save_log("Added Category: " . $IN['cat_name']);

		$ADMIN->done_screen("Forum Configurations updated", "Administration CP Home", "act=index");

	}

	// save_config, common_header and common_footer are all functions writen by Invision Power Boards with slight modifications to some
	function save_config($new)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$master = array();

		if (is_array($new))
		{
			if (count($new) > 0)
			{
				foreach ($new as $field)
				{
					$master[$field] = stripslashes($_POST[$field]);
					$master[$field] = str_replace("'", "&middot;", $master[$field]);
					if (in_array($field, $this->make_safe))
					{
						$master[$field] = $this->store_lib->make_numsafe($master[$field]);
					}
				}

				$ADMIN->rebuild_config($master);
			}
		}

		$ADMIN->save_log("Board Settings Updated, Back Up Written");

		$ADMIN->done_screen("Forum Configurations updated", "Administration CP Home", "act=index");

	}

	//-------------------------------------------------------------
	//
	// Common header: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------

	function common_header($formcode = "", $section = "", $extra = "")
	{

		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$extra = $extra
			? $extra . "<br>"
			: $extra;

		$ADMIN->page_detail = $extra . "Please check the data you are entering before submitting the changes";
		$ADMIN->page_title  = "$section";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $formcode),
		                                       2 => array('act', 'store'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "50%");
		$SKIN->td_header[] = array("{none}", "50%");

		//+-------------------------------

		//$ADMIN->html .= $SKIN->start_table("IBStore");

	}

	function common_footer($button = "Submit Changes", $js = "")
	{

		global $IN, $INFO, $SKIN, $ADMIN;
		$ibforums = Ibf::app();

		$ADMIN->html .= $SKIN->end_form($button, $js);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

}


