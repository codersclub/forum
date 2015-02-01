<?php
/*----------------------------------------------------------------------*\
|   IBStore 2.5															 |
+------------------------------------------------------------------------+
|   (c) 2003 Zachary Anker												 |
|	Email: wingzero1018@hotmail.com										 |
|   http://www.subzerofx.com/shop/										 |
+------------------------------------------------------------------------+
|	You may edit this file as long as you retain this Copyright notice.	 |
|	Redistribution not permitted without permission from Zachary Anker.	 |
\*--------------------------------------------------------------------- */

use Views\View;

$store = new store;
class store
{
	var $output = "";
	var $temp_output = "";
	var $tempoutput = "";
	var $page_title = "";
	var $store_version = "2.5";
	var $nav = array();

	function store()
	{
		global $ibforums, $std, $print, $lib;

		if ($ibforums->input['code'] != 'useitem')
		{
			$this->parser = new PostParser();
		}

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_store', $ibforums->lang_id);

		if (!$ibforums->vars['store_guest'] && $ibforums->member['id'] == 0)
		{
			$this->error("guest_cant_view");
		} else {
			if ($ibforums->vars['store_guest'] && $ibforums->member['id'] == 0)
			{
				//		$ibforums->input['code'] = "show";
			}
		}
		if (!$ibforums->vars['store_on'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("shop_offline");
		}
		if ($ibforums->vars['reset_onnegitive'] && $ibforums->member['points'] < 0 && $ibforums->member['id'])
		{
			$ibforums->db->exec("UPDATE ibf_members SET points='0' WHERE id='{$ibforums->member['id']}' LIMIT 1");
		}

		switch ($ibforums->input['code'])
		{
			case 'show':
				$this->show();
				break;
			case 'buyitem':
				$this->buyitem();
				break;
			case 'inventory':
				$this->inventory();
				break;
			case 'shop':
				$this->shop();
				break;
			case 'useitem':
				$this->useitem();
				break;
			case 'donate_money':
				$this->donatemoney();
				break;
			case 'donate_item':
				$this->donateitem();
				break;
			case 'donate_items':
				$this->donateitems();
				break;
			case 'bank':
				$this->bank();
				break;
			case 'stats':
				$this->stats();
				break;
			case 'item_effect':
				$this->item_effects();
				break;
			case 'fine':
				$this->fine();
				break;
			case 'do_fine_users':
				$this->do_fine_users();
				break;
			case 'showfine':
				$this->showfine();
				break;
			case 'edit_points':
				$this->edit_points();
				break;
			case 'do_edit_points':
				$this->do_edit_points();
				break;
			case 'do_do_edit_points':
				$this->do_do_edit_points();
				break;
			case 'edit_inventory':
				$this->staff_inventory();
				break;
			case 'do_staff_inventory':
				$this->do_staff_inventory();
				break;
			case 'do_do_staff_inventory':
				$this->do_do_staff_inventory();
				break;
			case 'misc_stats':
				$this->misc_stats();
				break;
			case 'post_info':
				$this->post_info();
				break;
			case 'view_inventory':
				$this->view_inventory();
				break;
			case 'dodonate_money':
				$this->dodonate_money();
				break;
			case 'dobank':
				$this->do_bank();
				break;
			case 'forwhat':
				$this->do_forwhat();
				break;
			default:
				$this->show();
				break;
		}

		// add all of are skin output
		$out_put = View::make("store.menu", ['links' => $this->compile_links()]);
		$this->output .= View::make("store.end_page");
		$this->output = str_replace("<!--IBS.CHECK-->", View::make('store.check'), $this->output);

		if ($ibforums->vars['ibstore_safty'] == 1)
		{
			$temp = "
 if(confirm(type)) {
    return true;
 } else {
    return false;
 }";
		}

		$this->output = str_replace("<!--IBS.SAFTY_ON-->", $temp, $this->output);

		//	if(!$ibforums->member['g_fine_edit'] || $ibforums->member['mgroup'] != $ibforums->vars['admin_group'] || $ibforums->member['g_allow_inventoryedit']) {
		//		$this->output = preg_replace("#<!-- Staff Urls -->(.+)<!-- End Staff Urls -->#","",$this->output);

		if ($ibforums->member['g_fine_edit'] || $ibforums->member['mgroup'] == $ibforums->vars['admin_group'] || $ibforums->member['g_allow_inventoryedit'])
		{
			$out_put .= View::make("store.menu_mod", ['links' => $this->compile_links()]);
		}

		$out_put .= View::make("store.menu_last", ['links' => $this->compile_links()]);

		$out_put .= $this->output;

		// If you wish to remove it you will have to pay the 40$ fee.
		// See: www.outlaw.ipbhost.com/store/services.php for more infomation on how to pay.
		//	$out_put .= "<br><div align='center' class='copyright'>Powered by <a href=\"http://www.subzerofx.com/shop/\" target='_blank'>IBStore</a> {$this->store_version} &copy; 2003-04 &nbsp;<a href='http://www.subzerofx.com/' target='_blank'>SubZeroFX.</a></div><br>";
		$print->add_output("$out_put");

		// do the output
		if (!$ibforums->input['code'])
		{
			$title = $ibforums->lang['ibstore_title'] . ' ' . $this->store_version;
		} else
		{
			$title = $ibforums->lang['ibstore_title'] . ' -> ' . $this->nav[count($this->nav) - 1];
		}
		$print->do_output(array('TITLE' => $title, 'JS' => 0, 'NAV' => $this->nav));
	}

	//---------------------------------------------
	// Front Pages
	//---------------------------------------------
	function show()
	{
		global $ibforums, $std, $print;
		$this->nav = array($ibforums->lang['show_store']);

		// Due to problems with parsing any thing and putting it into the config file we have to parse this stuff on every page load
		$info['welcome_desc'] = $this->postparse(str_replace("*username*", $ibforums->member['name'], $ibforums->vars['welcome_desc']));
		$info['welcome_line'] = $this->postparse(str_replace("*username*", $ibforums->member['name'], $ibforums->vars['welcome_line']));

		$this->output .= View::make("store.show_middle", ['info' => $info]);
		$this->output .= View::make('store.category_header');

		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_category ORDER BY catid DESC");
		while ($temp = $stmt->fetch())
		{
			$temp['cat_desc'] = "{$temp['cat_desc']}";
			$temp['cat_name'] = "<a href='{$ibforums->base_url}act=store&code=shop&category={$temp['catid']}'><b>{$temp['cat_name']}</b></a><br>";
			$this->output .= View::make("store.category", ['categorys' => $temp]);
		}
	}

	//-------------------------------------
	// View Member's Inventory
	//-------------------------------------
	function view_inventory()
	{
		global $ibforums, $lib, $std;
		$this->nav = array($ibforums->lang['inventory_nav']);
		if (!$ibforums->vars['show_inventory'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("using_off_function");
		}

		$stmt   = $ibforums->db->query("SELECT id,name FROM ibf_members
		    WHERE id='{$ibforums->input['memberid']}' LIMIT 1");
		$member = $stmt->fetch();
		$stmt   = $ibforums->db->query("SELECT *, count(id) as stock FROM ibf_store_inventory AS i
		    INNER JOIN ibf_store_shopstock as s ON i.item_id = s.id WHERE i.owner_id='{$member['id']}'
		    GROUP BY i.item_id ORDER BY item_name DESC");
		if ($stmt->rowCount() == 0)
		{
			$this->temp_output = View::make("store.noinventory");
		}
		while ($user_inventory = $stmt->fetch())
		{

			$this->temp_output .= View::make("store.view_inventory_middle", ['user_inventory' => $user_inventory]);
			$resell_price = $std->do_number_format(round(($ibforums->vars['resell_percentage'] / 100) * $user_inventory['price_payed']));
			if ($ibforums->vars['inventory_showresell'])
			{
				$this->temp_output = str_replace("<!--IBS.RESELL_AMOUNT-->", "<td class=\"row4\" align=\"center\" width=\"10%\">{$resell_price}</td>", $this->temp_output);
			}
			$value['total_value'] += $user_inventory['price_payed'];
		}

		$value['total_value'] = $std->do_number_format($value['total_value']);

		$this->tempoutput .= View::make("store.view_inventory_stats", ['stats' => $value, 'member' => $member]);

		if ($ibforums->vars['inventory_showresell'])
		{
			$this->tempoutput = str_replace("<!--IBS.RESELL_LANG-->", "<td class=\"pformstrip\" align=\"center\" width=\"10%\">{$ibforums->lang['resell_amount']}</td>", $this->tempoutput);
		}
		$this->output .= $this->tempoutput;
		$this->output .= $this->temp_output;
		unset($this->tempoutput, $this->temp_output);
	}

	//---------------------------------------------
	// Item Effects
	//---------------------------------------------
	function item_effects()
	{
		global $ibforums;
		if (!$ibforums->vars['allow_resell'] && $ibforums->input['type'] == 'resell')
		{
			$this->error("using_off_function");
		} else {
			if (!$ibforums->vars['allow_deleting'] && $ibforums->input['type'] == 'delete')
			{
				$this->error("using_off_function");
			}
		}
		switch ($ibforums->input['type'])
		{
			case 'resell':
				$this->resell();
				break;
			case 'delete':
				$this->delete_item();
				break;
			default;
				$this->resell();
				break;
		}
	}

	function resell()
	{
		global $ibforums, $lib, $IN;
		$stmt = $ibforums->db->query("SELECT price_payed, item_id FROM ibf_store_inventory WHERE i_id='{$ibforums->input['itemid']}' AND owner_id='{$ibforums->member['id']}' LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$this->error("donot_ownitem");
		}
		$price = $stmt->fetch();
		$ibforums->member['points'] += round(($ibforums->vars['resell_percentage'] / 100) * $price['price_payed']);
		$lib->delete_item($ibforums->input['itemid']);
		$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->member['points']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
		$ibforums->db->exec("UPDATE ibf_store_shopstock SET stock=stock+1 WHERE id='{$IN['itemid']}' LIMIT 1");

		$stmt = $ibforums->db->query("SELECT item_name FROM ibf_store_shopstock
		    WHERE id='{$price['item_id']}' LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$this->error("itemunknowed");
		}
		$item = $stmt->fetch();

		$lib->write_log($ibforums->vars['auto_pm_from'], 'Shop', $ibforums->member['id'], $ibforums->member['name'], round(($ibforums->vars['resell_percentage'] / 100) * $price['price_payed']), "Товар '" . $item['item_name'] . "' возвращён в магазин", $ibforums->input['user_reson'], "resell");
		$lib->redirect("resolditem", "act=store&code=inventory");
	}

	function delete_item()
	{
		global $ibforums, $lib;
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("deleteitem", "act=store&code=inventory");
	}

	//---------------------------------------------
	// Fine the User
	//---------------------------------------------
	function fine()
	{
		global $ibforums;

		$this->nav = array($ibforums->lang['staff_nav'], $ibforums->lang['fine_nav']);

		if ($ibforums->member['g_fine_edit'] != 1 && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("no_edit_permissions");
		}
		$this->output .= View::make("store.fine_users");
	}

	function do_fine_users()
	{
		global $ibforums, $lib;
		$member = $this->getmid($ibforums->input['username'], ",mgroup,points");
		if ($member['mgroup'] == $ibforums->vars['admin_group'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("cannoteditroot");
		}
		//	if($member['points']-$ibforums->input['fine_amount'] <= 0) {
		//		$member['points'] = 0;
		//	} else {
		//		$member['points'] -= $ibforums->input['fine_amount'];
		//	}
		if ($member['id'] == $ibforums->member['id'])
		{
			$this->error("cant_edit_myself");
		}
		if (!$ibforums->input['user_reson'])
		{
			$this->error("blank_reason");
		}

		$member['points'] = $ibforums->input['fine_amount'];

		$ibforums->db->exec("UPDATE ibf_members SET points=points+'{$member['points']}', fined=fined+'{$member['points']}' WHERE id='{$member['id']}' LIMIT 1");
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $member['id'], $ibforums->input['username'], $ibforums->input['fine_amount'], "Поощрён '" . $ibforums->input['username'] . "' на " . $ibforums->input['fine_amount'] . ' ' . $ibforums->vars['currency_name'], $ibforums->input['user_reson'], "fine");
		$lib->redirect("fined_user", "act=store&code=fine");
	}

	function showfine()
	{
		global $ibforums, $std, $print;

		$this->nav = array($ibforums->lang['fine_nav']);

		$mid = $ibforums->input['id'];

		$member = $this->getmem($mid, ",mgroup,points");

		if ($member['mgroup'] == $ibforums->vars['admin_group'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("no_edit_permissions");
		}
		//        if ( $member['id'] == $ibforums->member['id'] ) $this->error("cant_edit_myself");

		// Do some number formation
		//	$ibforums->member['points'] = $std->do_number_format($ibforums->member['points']);

		$this->output .= View::make("store.fine_header");

		// get all the charges for the user
		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_logs " . "WHERE type='fine' " . "AND toid=" . $member['id'] . " ORDER BY time DESC");

		if ($stmt->rowCount() == 0)
		{
			$this->temp_output = View::make("store.nocharges");
		}

		while ($charges = $stmt->fetch())
		{

			// Add all the charges they own to are semi-semi- master variable

			$charges['username'] = str_replace('&lt;', '<', $charges['username']);
			$charges['message']  = str_replace('&lt;', '<', $charges['message']);
			$charges['reason']   = str_replace('&lt;', '<', $charges['reason']);
			$charges['time']     = $std->get_date($charges['time']);
			//	    $charges['sum'     ] = str_replace( '&lt;', '<', $charges['sum'  ] );

			$this->temp_output .= View::make("store.fine_middle", ['charges' => $charges]);

			// Total value +=
			$value['total_value'] += $charges['sum'];
		}

		// More number formating!
		$value['total_value'] = $value['total_value']
			? $value['total_value']
			: 0;
		$value['total_value'] = $std->do_number_format($value['total_value']);

		// In order to cut down on a query we add them all in a odd way, dont complain it cuts down on a query
		$this->tempoutput .= View::make("store.fine_stats", ['stats' => $value]);

		// Funky adding output type stuff
		$this->output .= $this->temp_output;
		$this->output .= $this->tempoutput;
		$this->tempoutput  = "";
		$this->temp_output = "";
	}

	//------------------------------------------
	function edit_points()
	{
		global $ibforums;
		$this->nav = array($ibforums->lang['staff_nav'], $ibforums->lang['edit_nav']);
		if ($ibforums->member['g_fine_edit'] != 2 && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("no_edit_permissions");
		}
		$this->output .= View::make("store.edit_users_points");
	}

	//---------------------------------------------
	// Use an Item
	//---------------------------------------------
	function useitem()
	{
		global $ibforums, $lib;
		$this->nav = array($ibforums->lang['useitem_var']);

		if (empty($ibforums->input['itemid']))
		{
			$this->error("no_itemid");
		}
		$stmt = $ibforums->db->query("SELECT *, count(i_id) as stock FROM ibf_store_inventory AS i
		    INNER JOIN ibf_store_shopstock as s ON i.item_id = s.id WHERE i.i_id='{$ibforums->input['itemid']}' AND i.owner_id={$ibforums->member['id']}
		    GROUP BY i.item_id ORDER BY item_name ASC");
		if ($stmt->rowCount() <= 0)
		{
			$this->error("itemunknowed");
		}
		$item      = $stmt->fetch();
		$file_name = ROOT_PATH . "sources/storeitems/" . $item['module'] . ".php";
		if (!file_exists($file_name))
		{
			die("Error, i can't seem to find the file " . $item['module'] . ".php Please make sure it is actully their, and that it is located in " . $ibforums->vars['base_dir'] . "sources/storeitems");
		}

		require($file_name);
		$itemm = new item;
		if ($ibforums->input['change'] == "" && $html = $itemm->on_use($ibforums->input['itemid'], $item['item_name']))
		{
			$this->output .= View::make("store.useitem", ['code' => $html]);
		} else
		{
			$itemm->do_on_use($item['extra_one'], $item['extra_two'], $item['extra_three']);
			$output = "<center>Запрос выполнен!</center><br><p>";
			$output .= "<ul type='square'>Сейчас Вы можете:";
			$output .= "<li> <a href='http://forum.sources.ru/index.php?act=store&code=inventory'>Перейти к другим Вашим купленным вещам</li>";
			$output .= "<li> <a href='http://forum.sources.ru/index.php?act=store'>Посмотреть другие товары магазина</li>";
			$output .= "<li> <a href='http://forum.sources.ru/index.php?act=idx'>Перейти в Форум на Исходниках.RU</a></li>";
			$output .= "</ul></p>";
			$this->output .= $output;
		}
	}

	//---------------------------------------------
	// Send money to a member
	//---------------------------------------------
	function donatemoney()
	{
		global $ibforums, $std, $print, $lib;
		$this->nav = array($ibforums->lang['money_var']);

		$ibforums->lang['donate_to'] = str_replace("<CURRENCY>", $ibforums->vars['currency_name'], $ibforums->lang['donate_to']);
		if (!$ibforums->member['id'] or ($ibforums->member['points'] <= 0))
		{
			$disabled = "disabled";
		}
		$this->output .= View::make("store.donatemoney", ['disable' => $disabled]);
	}

	function dodonate_money()
	{
		global $ibforums, $lib;

		if (!$ibforums->member['id'])
		{
			$this->error("donate_empty");
		}

		if (empty($ibforums->input['amount']) || empty($ibforums->input['username']))
		{
			$this->error("donate_empty");
		}
		if ($ibforums->input['amount'] <= 0)
		{
			$this->error("donate_notenought");
		}
		if ($ibforums->member['points'] < $ibforums->input['amount'])
		{
			$this->error("donate_notenought");
		}
		$receiver = $this->getmid($ibforums->input['username'], ',points');
		$receiver['points'] += $ibforums->input['amount'];
		$ibforums->member['points'] -= $ibforums->input['amount'];
		$ibforums->db->exec("UPDATE ibf_members SET points='{$receiver['points']}' WHERE id='{$receiver['id']}' LIMIT 1");
		$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->member['points']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");

		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $receiver['id'], $ibforums->input['username'], $ibforums->input['amount'], "Передано " . $ibforums->input['amount'] . " " . $ibforums->vars['currency_name'] . " участнику '" . $ibforums->input['username'] . "'", $ibforums->input['message'], "donate_m");
		//	$lib->write_log("Послано ".$ibforums->input['amount']." участнику ".$ibforums->input['username'],"donate_m");
		$message = str_replace("{to}", $receiver['name'], $ibforums->vars['money_donation']);
		$message = str_replace("{from}", $ibforums->member['name'], $message);
		$message = str_replace("{amount}", $ibforums->input['amount'], $message);
		$message = str_replace("{message}", $ibforums->input['message'], $message);
		$message = str_replace("{currency_name}", $ibforums->vars['currency_name'], $message);
		$message = str_replace("\n", "<br>", $message);
		$message = preg_replace("#{date: (.+?)}#ies", 'date("\\1",time());', $message);
		$lib->sendpm($receiver['id'], $message, $ibforums->lang['sent_money']);
		$lib->redirect("donated", "act=store");
	}

	//---------------------------------------------
	// Send an item to a member
	//---------------------------------------------
	function donateitem()
	{
		global $ibforums, $std, $print, $lib;
		$this->nav = array($ibforums->lang['item_nav']);

		if (!$ibforums->member['id'])
		{
			$disabled = "disabled";
		} else
		{
			if (empty($ibforums->input['submit']))
			{
				$stmt = $ibforums->db->query("SELECT * FROM ibf_store_inventory AS i
        				 INNER JOIN ibf_store_shopstock as s ON i.item_id = s.id WHERE i.owner_id='{$ibforums->member['id']}'
        				 GROUP BY i.item_id ORDER BY item_name ASC");
				if ($stmt->rowCount() <= 0)
				{
					$dropdown = "<option value='no_items'>{$ibforums->lang['noitems']}</option>";
					$disabled = "disabled";
				}
				while ($item = $stmt->fetch())
				{
					$dropdown .= "<option value='{$item['i_id']}'>{$item['item_name']}</option>";
				}
			}
			if (isset($ibforums->input['submit']))
			{

				if (!$ibforums->member['id'])
				{
					$this->error("donate_empty");
				}

				$sendto = $this->getmid($ibforums->input['username']);

				$stmt = $ibforums->db->query("SELECT * FROM ibf_store_inventory AS i
        		    INNER JOIN ibf_store_shopstock as s ON i.item_id = s.id
        		    WHERE i.owner_id='{$ibforums->member['id']}' AND i_id='{$ibforums->input['item']}' LIMIT 1");
				if ($stmt->rowCount() <= 0)
				{
					$this->error("dontown_item");
				}
				$send = $stmt->fetch();

				$ibforums->db->exec("UPDATE ibf_store_inventory SET owner_id='{$sendto['id']}' WHERE i_id='{$send['i_id']}' LIMIT 1");

				$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $sendto['id'], $ibforums->input['username'], $send['price_payed'], "Передан товар '" . $send['item_name'] . "' участнику " . $ibforums->input['username'], $ibforums->input['message'], "donate_i");
				//	$lib->write_log("Послан товар ".$send['item_name']." участнику ".$ibforums->input['username'],"donate_i");
				$ibforums->vars['item_donation'] = str_replace("{to}", $sendto['name'], $ibforums->vars['item_donation']);
				$ibforums->vars['item_donation'] = str_replace("{from}", $ibforums->member['name'], $ibforums->vars['item_donation']);
				$ibforums->vars['item_donation'] = str_replace("{value}", $send['price_payed'], $ibforums->vars['item_donation']);
				$ibforums->vars['item_donation'] = str_replace("{message}", $ibforums->input['message'], $ibforums->vars['item_donation']);
				$ibforums->vars['item_donation'] = str_replace("{currency_name}", $ibforums->vars['currency_name'], $ibforums->vars['item_donation']);
				$ibforums->vars['item_donation'] = str_replace("{item}", $send['item_name'], $ibforums->vars['item_donation']);
				$ibforums->vars['item_donation'] = preg_replace("#{date: (.+?)}#ies", 'date("\\1",time());', $ibforums->vars['item_donation']);

				$lib->sendpm($sendto['id'], $ibforums->vars['item_donation'], $ibforums->lang['sent_item']);
				$lib->redirect("itemsent", "act=store");
			}

		}
		$this->output .= View::make("store.donateitem", ['options' => $dropdown, 'disable' => $disabled]);
	}

	//---------------------------------------------
	// Buying Items
	//---------------------------------------------
	function shop()
	{
		global $ibforums, $std, $print;
		$this->nav = array($ibforums->lang['show_nav']);
		$this->output .= View::make("store.item_info");
		if (isset($ibforums->input['category']))
		{
			$info['category'] = "&category={$ibforums->input['category']}";
			$extra            = "AND category='{$ibforums->input['category']}'";
		}
		$stmt = $ibforums->db->query("SELECT 1 FROM ibf_store_shopstock
		    WHERE stock>0 AND id>'0' " . $extra);

		$total_items  = $stmt->rowCount();
		$limit        = $ibforums->vars['pages_peritems']
			? $ibforums->vars['pages_peritems']
			: 25;
		$current_page = $ibforums->input['page']
			? $ibforums->input['page']
			: 0;
		if ($ibforums->input['page'] < 0)
		{
			$current_page = 0;
		}
		$limit_extra  = "LIMIT $current_page,$limit";
		$info['next'] = $current_page + $limit;
		$info['last'] = $current_page - $limit;

		$stmt          = $ibforums->db->query("SELECT * FROM ibf_store_shopstock
			    WHERE
				id>'0' " . $extra . " AND
				stock>0 AND
				avalible='0'
			    ORDER BY item_name
			    DESC " . $limit_extra);
		$returned_rows = $stmt->rowCount();

		while ($item = $stmt->fetch())
		{
			if ($item['soldout_time'] > 0 && $item['stock'] < 1)
			{
				$restock = $item['soldout_time'] + $item['restock_wait'];
				if ($restock < time())
				{
					$item['stock'] = $item['restock_amount'];
					$query         = $ibforums->db->exec("UPDATE ibf_store_shopstock SET stock='{$item['stock']}', soldout_time='0' WHERE id='{$item['id']}' LIMIT 1");
					$stmt->closeCursor();
				}
			}

			if ($ibforums->member['id'])
			{
				$item['item_buyitem'] = "<a href='{$ibforums->base_url}act=store&code=buyitem&itemid={$item['id']}'>{$ibforums->lang['buyitem']}</a>";
			}
			$item['item_buyitem'] = $item['stock']
				? $item['item_buyitem']
				: $ibforums->lang['soldout'];

			$item['sell_price'] = $std->do_number_format($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price']));
			$item['stock']      = $std->do_number_format($item['stock']);

			$item['item_name'] = $t = str_replace('&gt;', '>', $item['item_name']);
			$item['item_name'] = $t = str_replace('&lt;', '<', $item['item_name']);
			$item['item_desc'] = $t = str_replace('&gt;', '>', $item['item_desc']);
			$item['item_desc'] = $t = str_replace('&lt;', '<', $item['item_desc']);

			$this->output .= View::make("store.list_items", ['item' => $item]);

			if ($ibforums->vars['mass_buyon'])
			{
				$mass_buy = explode(",", $ibforums->vars['mass_buyamount']);
				if (is_array($mass_buy))
				{
					foreach ($mass_buy as $mass)
					{
						if ($mass <= $item['stock'])
						{
							$mass_buy_list[] = "<a href='{$ibforums->base_url}act=store&code=shop&code=buyitem&itemid={$item['id']}&amount={$mass}'>{$mass}</a>";
						}
					}
					if (is_array($mass_buy_list))
					{
						$mass_buylist = "( " . implode("|", $mass_buy_list) . " )";
					} else
					{
						$mass_buylist = "( " . $ibforums->lang['mass_buylittle'] . " )";
					}
				} else
				{
					$mass_buy_list = "( {$mass} )";
				}
				$this->output = str_replace("<!--Mass Buy Header-->", View::make('store.mass_buy_header'), $this->output);
				$this->output = str_replace("<!--Mass Buy Middle-->",
					View::make("store.mass_buy_middle", ['mass_buy' => $mass_buylist]), $this->output);
				unset($mass_buy_list, $mass_buylist);
			}
		}
		$ibforums->lang['showingitems'] = str_replace("<#FIRST#>", $current_page, $ibforums->lang['showingitems']);
		$ibforums->lang['showingitems'] = str_replace("<#LAST#>", $info['next'], $ibforums->lang['showingitems']);
		$ibforums->lang['showingitems'] = str_replace("<#NUM#>", $total_items, $ibforums->lang['showingitems']);
		if ($returned_rows)
		{
			$this->output .= View::make("store.next_lastlinks", ['info' => $info]);
		} else
		{
			$this->output .= View::make("store.cannot_finditems");
		}
	}

	//---------------------------------------------
	//	 Buying a item
	//---------------------------------------------
	function buyitem()
	{
		global $ibforums, $lib;
		$this->nav = array($ibforums->lang['buyitem_nav']);
		$item_id   = $ibforums->input['itemid'];
		$stmt      = $ibforums->db->query("SELECT * FROM ibf_store_shopstock WHERE id='{$item_id}' LIMIT 1");
		$item      = $stmt->fetch();
		if ($item['stock'] <= 0)
		{
			if ($item['restock_wait'] > 0 && $item['soldout_time'] > 0)
			{
				$restock_at = $item['soldout_time'] + $item['restock_wait'];
				if ($restock_at < time())
				{
					$item['stock'] += $item['restock_amount'];
				}
			}
		}
		$file_name = ROOT_PATH . "sources/storeitems/" . $item['module'] . ".php";
		if (!file_exists($file_name))
		{
			die("Error, i can't seem to find the file " . $item['module'] . ".php Please make sure it is actully there, and that it is located in ./sources/storeitems");
		}
		if ($lib->check_item_inventory($ibforums->member['id'], $ibforums->vars['inventory_max']))
		{
			$this->error(str_replace("<#LIMIT#>", $ibforums->vars['inventory_max'], $ibforums->lang['inventory_max']), 1);
		}
		if ($item['item_limit'] > 0)
		{
			$stmt                      = $ibforums->db->query("SELECT * FROM ibf_store_inventory WHERE owner_id='{$ibforums->member['id']}' AND item_id='{$item_id}'");
			$ibforums->input['amount'] = $ibforums->input['amount']
				? $ibforums->input['amount']
				: 1;
			if ($stmt->rowCount() + $ibforums->input['amount'] > $item['item_limit'])
			{
				$this->error(str_replace("<#LIMIT#>", $item['item_limit'], $ibforums->lang['item_limit_hit']), 1);
			}
		}

		$amount = 1;
		if ($ibforums->vars['mass_buyon'])
		{
			if ($ibforums->input['amount'] > $item['stock'])
			{
				$amount = $item['stock'];
			} else {
				if ($amount <= $item['stock'])
				{
					$amount = $ibforums->input['amount'];
				}
			}
		}
		if (!$amount)
		{
			$amount = 1;
		}

		$take_points = round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price']));
		$take_points *= $amount;
		if ($ibforums->member['points'] < $take_points)
		{
			$this->error("not_enought");
		}

		$item['stock'] -= $amount;
		if ($item['stock'] <= 0 && $item['soldout_time'] == 0)
		{
			$extra = ", soldout_time=" . time();
		}
		$ibforums->member['points'] -= $take_points;

		$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->member['points']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
		$ibforums->db->exec("UPDATE ibf_store_shopstock SET stock='{$item['stock']}' " . $extra . " WHERE id='{$item['id']}' LIMIT 1");

		if ($ibforums->vars['mass_buyon'] && $amount > 1)
		{
			while ($i < $amount)
			{
				$ibforums->db->exec("INSERT INTO ibf_store_inventory (i_id,owner_id,item_id,price_payed) VALUES('','{$ibforums->member['id']}','{$item_id}','{$item['sell_price']}')");
				$i++;
			}

			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->vars['auto_pm_from'], 'Shop', round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])), "Куплен товар '" . $item['item_name'] . "x" . $i . "' за " . round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])) . ' ' . $ibforums->vars['currency_name'], $ibforums->input['user_reson'], "bought");
		} else
		{
			$ibforums->db->exec("INSERT INTO ibf_store_inventory(i_id,owner_id,item_id,price_payed) VALUES('','{$ibforums->member['id']}','{$item_id}','{$item['sell_price']}')");
			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->vars['auto_pm_from'], 'Shop', round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])), "Куплен товар '" . $item['item_name'] . "x" . $i . "' за " . round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])) . ' ' . $ibforums->vars['currency_name'], $ibforums->input['user_reson'], "bought");
		}
		$lib->redirect("item_bought", "act=store&code=inventory", "item_bought");
	}

	//---------------------------------------------
	// The users inventory
	//---------------------------------------------
	function inventory()
	{
		global $ibforums, $std, $print;
		$this->nav = array($ibforums->lang['inventory_nav']);
		// Do some number formation
		$ibforums->member['points'] = $std->do_number_format($ibforums->member['points']);
		// get all the items we own
		$stmt = $ibforums->db->query("SELECT *, count(id) as stock FROM ibf_store_inventory AS i
				INNER JOIN ibf_store_shopstock as s ON i.item_id = s.id WHERE i.owner_id={$ibforums->member['id']}
				GROUP BY i.item_id ORDER BY item_name DESC");
		if ($stmt->rowCount() == 0)
		{
			$this->temp_output = View::make("store.noinventory");
		}
		while ($user_inventory = $stmt->fetch())
		{
			// Add all the items they own to are semi-semi- master variable

			$user_inventory['item_name'] = $t = str_replace('&gt;', '>', $user_inventory['item_name']);
			$user_inventory['item_name'] = $t = str_replace('&lt;', '<', $user_inventory['item_name']);
			$user_inventory['item_desc'] = $t = str_replace('&gt;', '>', $user_inventory['item_desc']);
			$user_inventory['item_desc'] = $t = str_replace('&lt;', '<', $user_inventory['item_desc']);

			$this->temp_output .= View::make("store.inventory_middle", ['user_inventory' => $user_inventory]);

			// More number formating
			$resell_price = round($user_inventory['price_payed'] - (($ibforums->member['g_discount'] / 100) * $user_inventory['price_payed']));
			$resell_price = round(($ibforums->vars['resell_percentage'] / 100) * $resell_price);
			$resell_price = $std->do_number_format($resell_price);

			// Do we have resell or delete on? Great  then we will add those parts to the list of actions
			if ($ibforums->vars['allow_resell'])
			{
				$this->temp_output = str_replace("<!--IBS.RESELL_ITEM-->", "<a href=\"{$ibforums->base_url}act=store&code=item_effect&itemid={$user_inventory['i_id']}&type=resell\" onClick=\"return check('{$ibforums->lang['resell_check']}')\">{$ibforums->lang['re_sell']}</a><br>", $this->temp_output);
			}
			if ($ibforums->vars['allow_deleting'])
			{
				$this->temp_output = str_replace("<!--IBS.DELETE_ITEM-->", "<a href=\"{$ibforums->base_url}act=store&code=item_effect&itemid={$user_inventory['i_id']}&type=delete\" onClick=\"return check('{$ibforums->lang['delete_check']}')\">{$ibforums->lang['delete']}</a>", $this->temp_output);
			}
			// Do we want to tell them how much they would get for reselling, yes then we display it
			if ($ibforums->vars['tell_resellamount'])
			{
				$this->temp_output = str_replace("<!--IBS.RESELL_AMOUNT-->", "<td class=\"pformleft\" align=\"center\" width=\"10%\">{$resell_price}</td>", $this->temp_output);
			}
			// Total value +=
			$value['total_value'] += $user_inventory['price_payed'];
		}

		// More number formating!
		$value['total_value'] = $value['total_value']
			? $value['total_value']
			: 0;
		$value['total_value'] = $std->do_number_format($value['total_value']);

		// In order to cut down on a query we add them all in a odd way, dont complain it cuts down on a query
		$this->tempoutput .= View::make("store.inventory_stats", ['stats' => $value]);

		// Add the language part of the resell amount if we want to tell them
		if ($ibforums->vars['tell_resellamount'])
		{
			$this->tempoutput = str_replace("<!--IBS.RESELL_LANG-->", "<td class=\"pformstrip\" align=\"center\" width=\"10%\">{$ibforums->lang['resell_amount']}</td>", $this->tempoutput);
		}
		// Funky adding output type stuff
		$this->output .= $this->tempoutput;
		$this->output .= $this->temp_output;
		$this->tempoutput  = "";
		$this->temp_output = "";
	}

	// Edit User Points
	function do_edit_points()
	{
		global $ibforums;
		$member = $this->getmid($ibforums->input['username'], ",mgroup,points");
		if ($member['mgroup'] == $ibforums->vars['admin_group'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("cannoteditroot");
		}
		$this->output .= View::make("store.do_edit_users_points", ['member' => $member]);
	}

	function do_do_edit_points()
	{
		global $ibforums, $lib;
		$member = $this->getmid($ibforums->input['username']);
		$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->input['points']}' WHERE LOWER(name)='{$ibforums->input['username']}' LIMIT 1");
		//	$lib->add_reason($ibforums->member['id'],$ibforums->member['name'],$member['id'],$ibforums->input['username'],$ibforums->input['points'],"Корректировка остатка на счете ".$ibforums->input['username'].": ".$ibforums->input['points']." ".$ibforums->vars['currency_name'],$ibforums->input['user_reson'],"edit");
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $member['id'], $ibforums->input['username'], $ibforums->input['points'], "Корректировка счета '" . $ibforums->input['username'] . "': " . $ibforums->input['points'] . " " . $ibforums->vars['currency_name'], $ibforums->input['user_reson'], "edit");
		$lib->redirect("edited_user", "act=store");
	}

	function staff_inventory()
	{
		global $ibforums;
		$this->nav = array($ibforums->lang['staff_nav'], $ibforums->lang['inventoryedit_nav']);
		if (!$ibforums->member['g_allow_inventoryedit'] && $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$this->error("no_edit_permissions");
		}
		$this->output .= View::make("store.edit_users_inventory");
	}

	function do_staff_inventory()
	{
		global $ibforums, $std;
		$user = $this->getmid($ibforums->input['username']);
		$this->output .= View::make("store.show_users_inventory_header", ['user' => $user]);
		$stmt = $ibforums->db->query("SELECT i.*,s.*
		    FROM ibf_store_inventory i
		    LEFT JOIN ibf_store_shopstock s on (i.item_id=s.id)
		    WHERE i.owner_id='{$user['id']}' ORDER BY i.i_id DESC");
		while ($item = $stmt->fetch())
		{
			$this->output .= View::make("store.show_users_inventory", ['inventory' => $item]);
		}
		$this->output .= View::make("store.edit_inventory_submit");
	}

	function do_do_staff_inventory()
	{
		global $ibforums, $lib;
		$member = $this->getmid($ibforums->input['username']);
		$i      = 0;
		$ii     = 0;
		$stmt   = $ibforums->db->query("SELECT i_id FROM ibf_store_inventory WHERE owner_id='{$ibforums->input['userid']}' ORDER BY i_id DESC");
		while ($edit = $stmt->fetch())
		{
			if ($ibforums->input['delete_' . $edit['i_id']] == 1)
			{
				$temp = $ibforums->db->exec("DELETE FROM ibf_store_inventory WHERE i_id='{$edit['i_id']}' AND owner_id='{$ibforums->input['userid']}' LIMIT 1");
				$i++;
			} else
			{
				if ($ibforums->input['price_' . $edit['i_id']] != $ibforums->input['original_' . $edit['i_id']])
				{
					$price = $ibforums->input['price_' . $edit['i_id']];
					$temp  = $ibforums->db->exec("UPDATE ibf_store_inventory SET price_payed='{$price}' WHERE i_id='{$edit['i_id']}' AND owner_id='{$ibforums->input['userid']}' LIMIT 1");
				}
			}
		}
		if ($i > 0 && $ii == 0)
		{
			$msg = "Deleted " . $i . " Items from User IDs " . $ibforums->input['userid'] . " Inventory";
		} else {
			if ($i == 0 && $ii > 0)
			{
				$msg = "Edited User ID " . $ibforums->input['userid'] . " Inventory. Changed " . $ii . " Items prices";
			} else
			{
				if ($i > 0 && $ii > 0)
				{
					$msg = "Deleted " . $i . " Items from User IDs " . $ibforums->input['userid'] . " Inventory <br> Edited User ID " . $ibforums->input['userid'] . " Inventory. Changed " . $ii . " Items prices";
				}
			}
		}
		//	$lib->add_reason($ibforums->member['id'],$ibforums->member['name'],$ibforums->input['userid'],'',0,$msg,$ibforums->input['reson'],'inventory');
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->input['userid'], $ibforums->input['username'], 0, $msg, $ibforums->input['reson'], "inventory");
		$lib->redirect("inventory_edited", "act=store");
	}

	//---------------------------------------------
	// Misc
	//---------------------------------------------
	// Print the Rules and Price for it
	function post_info()
	{
		global $std, $ibforums;
		$this->nav = array($ibforums->lang['postinfo_nav']);

		$this->output .= View::make("store.post_info");
		$this->output .= View::make("store.output_stats_end");
	}

	//---------------------------------------------
	// Parse and UnParse
	//---------------------------------------------
	function postparse($msg)
	{
		$msg = $this->parser->convert(array(
		                                   'TEXT'    => $msg,
		                                   'SMILIES' => 1,
		                                   'CODE'    => 1,
		                                   'HTML'    => 0
		                              ));
		return $msg;
	}

	function unpostparse($msg)
	{
		$msg = $this->parser->unconvert($msg, 1, 0);
		return $msg;
	}

	//---------------------------------------------
	// Error Message
	//---------------------------------------------
	function error($msg, $item = "")
	{
		global $ibforums, $std;
		unset($this->output);
		if (!$item)
		{
			$message = $ibforums->lang['' . $msg . ''];
			if (empty($message))
			{
				die($ibforums->lang['error_error']);
			}
		} else
		{
			$message = $msg;
		}
		$html .= View::make("store.error");
		$html .= View::make("store.error_row", ['message' => $message]);
		$html .= View::make("store.end_page");
		// If you wish to remove it you will have to pay the 40$ fee.
		// See: www.outlaw.ipbhost.com/store/services.php for more infomation on how to pay.
		//	$html .= "<br/><div align='center' class='copyright'>Powered by <a href=\"http://www.subzerofx.com/shop/\" target='_blank'>IBStore</a> {$this->store_version} &copy; 2003-04 &nbsp;<a href='http://www.subzerofx.com/' target='_blank'>SubZeroFX.</a></div><br>";

		$print = new display();

		$print->add_output($html);

		$print->do_output(array(
		                       'OVERRIDE' => 1,
		                       'TITLE'    => $ibforums->lang['error_title'],
		                  ));

		exit;
	}

	//---------------------------------------------
	// Compile all of are shop links
	//---------------------------------------------
	function compile_links()
	{
		global $ibforums;
		// Make all categorys
		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_category WHERE catid>'0' ORDER BY catid DESC");
		if ($ibforums->vars['show_shopcat'])
		{
			$link['shop'] .= View::make(
				"store.make_url",
				['address' => "act=store&code=shop", 'text' => $ibforums->lang['shop']]
			);
		}
		$i = 0;
		while ($temp = $stmt->fetch())
		{
			if (isset($ibforums->input['view_all']) || $i < 10)
			{
				$link['shop'] .= View::make(
					"store.make_url",
					['address' => "act=store&code=shop&category={$temp['catid']}", 'text' => $temp['cat_name']]
				);
			} else {
				if ($i >= 10 && empty($ibforums->input['view_all']))
				{
					$link['shop'] .= View::make(
						"store.make_url",
						['address' => "act=store&code=shop&view_all=all", 'text' => $ibforums->lang['view_all_cats']]
					);
					break;
				}
			}
			$i++;
		}
		// Make the stat links
		if ($ibforums->vars['richest_onhand'])
		{
			$link['stat'] .= View::make(
				"store.make_url",
				['address' => "act=store&code=stats&type=member", 'text' => $ibforums->lang['stats_member']]
			);
		}
		if ($ibforums->vars['richest_bank'])
		{
			$link['stat'] .= View::make(
				"store.make_url",
				['address' => "act=store&code=stats&type=bank", 'text' => $ibforums->lang['stats_bank']]
			);
		}
		if ($ibforums->vars['richest_overall'])
		{
			$link['stat'] .= View::make(
				"store.make_url",
				['address' => "act=store&code=stats", 'text' => $ibforums->lang['stats_overall']]
			);
		}
		if ($ibforums->vars['show_memberpoints'])
		{
			$link['points'] .= View::make("store.member_points");
		}
		return $link;
	}

	function tag_convert($msg)
	{
		$msg = str_replace("'", "&#39;", $msg);
		$msg = str_replace("!", "&#33;", $msg);
		$msg = str_replace("$", "&#036;", $msg);
		$msg = str_replace("|", "&#124", $msg);
		$msg = str_replace("&", "&amp;", $msg);
		$msg = str_replace(">", "&gt;", $msg);
		$msg = str_replace("<", "&lt;", $msg);
		$msg = str_replace('"', "&quot;", $msg);
		$msg = str_replace(",", "&cedil;", $msg);
		$msg = str_replace("&cedil;", "", $msg);
		$msg = str_replace("&nbsp;", "", $msg);
		$msg = mb_strtolower($msg);
		$msg = stripslashes($msg);
		return $msg;
	}

	//---------------------------------------------
	// Stats
	//---------------------------------------------
	function misc_stats()
	{
		global $std, $ibforums;
		$this->nav = array($ibforums->lang['miscstats_nav']);
		$stmt      = $ibforums->db->query("SELECT points,deposited FROM ibf_members WHERE points>'0' OR deposited>'0'");
		while ($money = $stmt->fetch())
		{
			$total_money += $money['points'];
			$total_bank += $money['deposited'];
		}
		$stmt = $ibforums->db->query("SELECT sell_price,stock FROM ibf_store_shopstock WHERE stock>'0' AND avalible='0'");
		while ($items = $stmt->fetch())
		{
			$total_item += $items['sell_price'];
			$total_stock += $items['stock'];
		}
		$stats = array(
			'money' => $std->do_number_format($total_money),
			'bank'  => $std->do_number_format($total_bank),
			'item'  => $std->do_number_format($total_item),
			'stock' => $std->do_number_format($total_stock),
			'total' => $std->do_number_format($total_money + $total_bank)
		);
		$this->output .= View::make("store.misc_stats", ['stats' => $stats]);
		$this->output .= View::make("store.output_stats_end");
	}

	function stats()
	{
		global $std, $ibforums;
		$this->nav = array($ibforums->lang['stats_nav']);

		if ($ibforums->input['type'] == 'member')
		{
			$header = $ibforums->lang['richestmember'];
			$query  = "SELECT id,name,points FROM ibf_members WHERE id>'0' AND points>'0' ORDER BY points DESC LIMIT " . $ibforums->vars['richest_showamount'];
		} else {
			if ($ibforums->input['type'] == 'bank')
			{
				$header = $ibforums->lang['richestbank'];
				$query  = "SELECT id,name,deposited FROM ibf_members WHERE id>'0' AND deposited>'0' ORDER BY deposited DESC LIMIT " . $ibforums->vars['richest_showamount'];
			}
		}
		if (isset($ibforums->input['type']))
		{
			$this->output .= View::make("store.header_stats", ['name' => $header]);
			$stmt = $ibforums->db->query($query);
			while ($temp = $stmt->fetch())
			{
				if ($ibforums->input['type'] == 'bank')
				{
					$temp['points'] = $temp['deposited'];
				}
				$temp['points'] = $std->do_number_format($temp['points']);
				$this->output .= View::make("store.output_stats", ['member' => $temp]);
			}
			$this->output .= View::make("store.output_stats_end");
		} else
		{
			$this->output .= View::make("store.overall_stats_header");
			$stmt = $ibforums->db->query("SELECT id,name,points,deposited FROM ibf_members WHERE id>'0' AND points>'0' AND deposited>'0' ORDER BY points+deposited DESC LIMIT " . $ibforums->vars['richest_showamount']);
			while ($temp = $stmt->fetch())
			{
				$temp['total_points'] = $std->do_number_format($temp['points'] + $temp['deposited']);
				$temp['points']       = $std->do_number_format($temp['points']);
				$temp['deposited']    = $std->do_number_format($temp['deposited']);
				$this->output .= View::make("store.output_overall_stats", ['member' => $temp]);
			}
			$this->output .= View::make("store.output_stats_end");
		}
	}

	// This is a helpfull function to convert the username to ID
	// and get any othere info we want
	function getmid($username, $addon = "", $extra_a = "")
	{
		global $ibforums;
		$tables = "id,name";
		$tables .= $addon;
		$extra = "LOWER(name)='" . mb_strtolower($username) . "'";
		$extra .= $extra_a;
		$stmt = $ibforums->db->query("SELECT " . $tables . " FROM ibf_members WHERE " . $extra . " LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$this->error("cannot_finduser");
		}
		$info = $stmt->fetch();
		return $info;
	}

	// This is a helpfull function to convert the userID
	// to username and get any othere info we want
	function getmem($userid, $addon = "", $extra_a = "")
	{
		global $ibforums;
		$tables = "id,name";
		$tables .= $addon;
		$extra = "id='" . $userid . "'";
		$extra .= $extra_a;
		$stmt = $ibforums->db->query("SELECT " . $tables . " FROM ibf_members WHERE " . $extra . " LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$this->error("cannot_finduser");
		}
		$info = $stmt->fetch();
		return $info;
	}

	//---------------------------------------------
	// Bank :D
	//---------------------------------------------
	function bank()
	{
		global $ibforums, $std, $print, $lib;
		$this->nav = array($ibforums->lang['bank_nav']);

		if (!$ibforums->vars['bank_on'])
		{
			$this->error("bankoffline");
		}

		$time = time();

		$interest = $ibforums->vars['base_intrest'] + $ibforums->member['extra_intrest'];

		if ($interest > 100)
		{
			$interest = 100;
		}

		$interest        = $interest / 100 + 1;
		$interest_points = $ibforums->member['deposited'] * $interest - $ibforums->member['deposited'];

		$ibforums->lang['yougetinterest'] = str_replace("<USERINTEREST> <CURRENCY>", $std->do_number_format(round($interest_points)) . ' ' . $ibforums->vars['currency_name'], $ibforums->lang['yougetinterest']);
		$ibforums->lang['amount_in']      = str_replace("<POINTS> <CURRENCY>", $std->do_number_format($ibforums->member['deposited']) . ' ' . $ibforums->vars['currency_name'], $ibforums->lang['amount_in']);
		$ibforums->lang['amount_onhand']  = str_replace("<POINTS> <CURRENCY>", $std->do_number_format($ibforums->member['points']) . ' ' . $ibforums->vars['currency_name'], $ibforums->lang['amount_onhand']);
		$ibforums->lang['interest']       = str_replace("<INTEREST>", $std->do_number_format($ibforums->vars['base_intrest'] + $ibforums->member['extra_intrest']), $ibforums->lang['interest']);

		$cancollect       = $time - $ibforums->member['last_collect'];
		$cancollect       = ceil($cancollect / 86400) - 1;
		$info['disabled'] = "disabled";
		$collect_submit   = $ibforums->lang['collect_intrest'];
		if ($ibforums->member['auto_collect'] == 1)
		{
			$collect_submit = $ibforums->lang['auto_collect_on'];
		} else {
			if ($cancollect >= 1 && $ibforums->member['auto_collect'] == 0)
			{
				$can_collect      = true;
				$info['disabled'] = "";
			} else
			{
				if ($cancollect >= 1 && $ibforums->member['auto_collect'] == 1)
				{
					$auto_interest                 = $interest_points * $cancollect;
					$ibforums->member['deposited'] = round($ibforums->member['deposited'] + $auto_interest);
					$ibforums->db->exec("UPDATE ibf_members SET last_collect='{$time}', deposited='{$ibforums->member['deposited']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");

					$lib->write_log($ibforums->vars['auto_pm_from'], 'Shop', $ibforums->member['id'], $ibforums->member['name'], $auto_interest, "Начислен процент: " . $auto_interest . ' ' . $ibforums->vars['currency_name'] . " Всего денег в банке: " . $ibforums->member['deposited'] . ' ' . $ibforums->vars['currency_name'], "", "auto_collect");
				}
			}
		}

		$this->output .= View::make("store.bank", ['info' => $info, 'collect_submit' => $collect_submit]);
	}

	function do_bank()
	{
		global $ibforums, $lib, $std;
		if (empty($ibforums->input['type']))
		{
			$this->bank();
			return;
		}
		$time     = time();
		$interest = $ibforums->vars['base_intrest'] + $ibforums->member['extra_intrest'];
		if ($interest > 100)
		{
			$interest = 100;
		}
		$interest        = $interest / 100 + 1;
		$interest_points = $ibforums->member['deposited'] * $interest - $ibforums->member['deposited'];

		$cancollect                               = $time - $ibforums->member['last_collect'];
		$ibforums->vars['days_interestcollected'] = $ibforums->vars['days_interestcollected']
			? $ibforums->vars['days_interestcollected']
			: 86400;
		$cancollect                               = ceil($cancollect / $ibforums->vars['days_interestcollected']) - 1;
		if ($cancollect >= 1)
		{
			$last_collect = ",last_collect='{$time}'";
		}
		if ($ibforums->input['type'] == 'collect')
		{
			if ($cancollect <= 0)
			{
				return $this->bank();
			}
			$ibforums->member['deposited'] = round($ibforums->member['deposited'] + $interest_points);
			$ibforums->db->exec("UPDATE ibf_members SET deposited='{$ibforums->member['deposited']}',last_collect='{$time}' WHERE id='{$ibforums->member['id']}' LIMIT 1");

			$lib->write_log($ibforums->vars['auto_pm_from'], 'Shop', $ibforums->member['id'], $ibforums->member['name'], round($interest_points), "Начислен процент: " . $std->do_number_format(round($interest_points)) . ' ' . $ibforums->vars['currency_name'] . " Всего денег в банке: " . $std->do_number_format(round($ibforums->member['deposited'])) . ' ' . $ibforums->vars['currency_name'], "", "collect");
			$lib->redirect("collected", "act=store&code=bank");
		}
		if ($ibforums->input['type'] == 'deposit')
		{
			if ($ibforums->member['points'] < $ibforums->input['deposit_amount'] || $ibforums->input['deposit_amount'] <= 0)
			{
				$this->error("notenought_deposit");
			}
			$ibforums->member['points'] -= $ibforums->input['deposit_amount'];
			$ibforums->member['deposited'] += $ibforums->input['deposit_amount'];
			$ibforums->db->exec("UPDATE ibf_members SET deposited='{$ibforums->member['deposited']}', points='{$ibforums->member['points']}'" . $last_collect . " WHERE id='{$ibforums->member['id']}' LIMIT 1");
			$lib->redirect("deposited_in", "act=store&code=bank");
		} else {
			if ($ibforums->input['type'] == 'withdraw')
			{
				if ($ibforums->input['withdraw_amount'] > $ibforums->member['deposited'] || $ibforums->input['withdraw_amount'] <= 0)
				{
					$this->error("withdraw_tolittle");
				}
				$ibforums->member['deposited'] -= $ibforums->input['withdraw_amount'];
				$ibforums->member['points'] += $ibforums->input['withdraw_amount'];
				$ibforums->db->exec("UPDATE ibf_members SET deposited='{$ibforums->member['deposited']}', points='{$ibforums->member['points']}'" . $last_collect . " WHERE id='{$ibforums->member['id']}' LIMIT 1");
				$lib->redirect("withdrew_out", "act=store&code=bank");
			}
		}

	}

	function do_forwhat()
	{
		global $ibforums, $lib, $print;

		$mid = $ibforums->input['mid'];

		$output = View::make("store.ShowTitle");

		$output .= View::make("store.ShowHeader");

		$output .= View::make("store.ShowFooter");

		$print->add_output("$output");

		$print->do_output(array('TITLE' => "test", 'JS' => 1, 'NAV' => "test"));

	}

} // Class end
