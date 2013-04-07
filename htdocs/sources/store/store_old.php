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

		$this->html = $std->load_template('skin_store');

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
			case 'quiz':
				$this->quiz();
				break;
			case 'take_quiz':
				$this->take_quiz();
				break;
			case 'do_take_quiz':
				$this->do_take_quiz();
				break;
			case 'stats':
				$this->stats();
				break;
			case 'quiz_results':
				$this->quiz_results();
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
		$out_put = $this->html->menu($this->compile_links());
		$this->output .= $this->html->end_page();
		$this->output = str_replace("<!--IBS.CHECK-->", $this->html->check(), $this->output);

		if ($ibforums->vars['ibstore_safty'] == 1)
		{
			$temp = " if(confirm(type)) {
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
			$out_put .= $this->html->menu_mod($this->compile_links());
		}

		$out_put .= $this->html->menu_last($this->compile_links());

		$out_put .= $this->output;

		// If you wish to remove it you will have to pay the 40$ fee.
		// See: www.outlaw.ipbhost.com/store/services.php for more infomation on how to pay.
		$out_put .= "<br><div align='center' class='copyright'>Powered by <a href=\"http://www.subzerofx.com/shop/\" target='_blank'>IBStore</a> {$this->store_version} &copy; 2003-04 &nbsp;<a href='http://www.subzerofx.com/' target='_blank'>SubZeroFX.</a></div><br>";
		$print->add_output("$out_put");

		// do the output
		if (!$ibforums->input['code'])
		{
			$title = $ibforums->lang['ibstore_title'] . ' ' . $this->store_version;
		} else
		{
			$title = $ibforums->lang['ibstore_title'] . ' -> ' . $this->nav[count($this->nav) - 1];
		}
		$print->do_output(array('TITLE' => $title, 'JS' => 1, 'NAV' => $this->nav));
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

		$this->output .= $this->html->show_middle($info);
		$this->output .= $this->html->category_header($info);

		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_category ORDER BY catid DESC");
		while ($temp = $stmt->fetch())
		{
			$temp['cat_desc'] = "{$temp['cat_desc']}";
			$temp['cat_name'] = "<a href='{$ibforums->base_url}act=store&code=shop&category={$temp['catid']}'><b>{$temp['cat_name']}</b></a><br>";
			$this->output .= $this->html->category($temp);
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
			$this->temp_output = $this->html->noinventory();
		}
		while ($user_inventory = $stmt->fetch())
		{

			$this->temp_output .= $this->html->view_inventory_middle($user_inventory);
			$resell_price = $std->do_number_format(round(($ibforums->vars['resell_percentage'] / 100) * $user_inventory['price_payed']));
			if ($ibforums->vars['inventory_showresell'])
			{
				$this->temp_output = str_replace("<!--IBS.RESELL_AMOUNT-->", "<td class=\"row4\" align=\"center\" width=\"10%\">{$resell_price}</td>", $this->temp_output);
			}
			$value['total_value'] += $user_inventory['price_payed'];
		}

		$value['total_value'] = $std->do_number_format($value['total_value']);

		$this->tempoutput .= $this->html->view_inventory_stats($value, $member);

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
		global $ibforums, $lib;
		//vot: added "item_id" field in select
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

		//vot: get item name added (6 lines below)
		$stmt = $ibforums->db->query("SELECT item_name FROM ibf_store_shopstock
		    WHERE id='{$price['item_id']}' LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$this->error("itemunknowed");
		}
		$item = $stmt->fetch();

		// vot: log added
		$lib->write_log(0, '', round(($ibforums->vars['resell_percentage'] / 100) * $price['price_payed']), "Товар '" . $item['item_name'] . "' возвращён в магазин", "Resell item");
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
		$this->output .= $this->html->fine_users();
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

		$lib->add_reason($ibforums->member['id'], $ibforums->member['name'], $member['id'], $member['name'], $member['points'], "Поощрён участник " . $member['name'] . " ­  " . $member['points'] . " " . $ibforums->vars['currency_name'], $ibforums->input['user_reson'], "fine");
		$ibforums->db->exec("UPDATE ibf_members SET points=points+'{$member['points']}' WHERE id='{$member['id']}' LIMIT 1");
		//vot: log added

		$lib->write_log($member, $ibforums->input['username'], $ibforums->input['fine_amount'], "Поощрён участник " . $ibforums->input['username'] . " на " . $ibforums->input['fine_amount'] . ' ' . $ibforums->vars['currency_name'], "Fine User");
		//	$lib->write_log("Поощрён участник ".$ibforums->input['username']." на ".$ibforums->input['fine_amount'].' '.$ibforums->vars['currency_name'],"Fine User");
		$lib->redirect("fined_user", "act=store");
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
		$this->output .= $this->html->edit_users_points();
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
			$this->output .= $this->html->useitem($html);
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
		$this->output .= $this->html->donatemoney($disabled);
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
		$reciver = $this->getmid($ibforums->input['username'], ',points');
		$reciver['points'] += $ibforums->input['amount'];
		$ibforums->member['points'] -= $ibforums->input['amount'];
		$ibforums->db->exec("UPDATE ibf_members SET points='{$reciver['points']}' WHERE id='{$reciver['id']}' LIMIT 1");
		$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->member['points']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");

		$lib->write_log($reciver['id'], $ibforums->input['username'], $ibforums->input['amount'], "Передано " . $ibforums->input['amount'] . " " . $ibforums->vars['currency_name'] . " участнику " . $ibforums->input['username'], "donate_m");
		//	$lib->write_log("Послано ".$ibforums->input['amount']." участнику ".$ibforums->input['username'],"donate_m");
		$message = str_replace("{to}", $reciver['name'], $ibforums->vars['money_donation']);
		$message = str_replace("{from}", $ibforums->member['name'], $message);
		$message = str_replace("{amount}", $ibforums->input['amount'], $message);
		$message = str_replace("{message}", $ibforums->input['message'], $message);
		$message = str_replace("{currency_name}", $ibforums->vars['currency_name'], $message);
		$message = str_replace("\n", "<br>", $message);
		$message = preg_replace("#{date: (.+?)}#ies", 'date("\\1",time());', $message);
		$lib->sendpm($reciver['id'], $message, $ibforums->lang['sent_money']);
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
				$stmt   = $ibforums->db->query("SELECT * FROM ibf_store_inventory AS i
			INNER JOIN ibf_store_shopstock as s ON i.item_id = s.id
			WHERE i.owner_id='{$ibforums->member['id']}' AND i_id='{$ibforums->input['item']}' LIMIT 1");
				if ($stmt->rowCount() <= 0)
				{
					$this->error("dontown_item");
				}
				$send = $stmt->fetch();
				$ibforums->db->exec("UPDATE ibf_store_inventory SET owner_id='{$sendto['id']}' WHERE i_id='{$send['i_id']}' LIMIT 1");

				$lib->write_log($sendto, $ibforums->input['username'], $send['price_payed'], "Передан товар " . $send['item_name'] . " участнику " . $ibforums->input['username'], "donate_i");
				//	    $lib->write_log("Послан товар ".$send['item_name']." участнику ".$ibforums->input['username'],"donate_i");
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
		$this->output .= $this->html->donateitem($dropdown, $disabled);
	}

	//---------------------------------------------
	// Buying Items
	//---------------------------------------------
	function shop()
	{
		global $ibforums, $std, $print;
		$this->nav = array($ibforums->lang['show_nav']);
		$this->output .= $this->html->item_info();
		if (isset($ibforums->input['category']))
		{
			$info['category'] = "&category={$ibforums->input['category']}";
			$extra            = "AND category='{$ibforums->input['category']}'";
		}
		$stmt         = $ibforums->db->query("SELECT 1 FROM ibf_store_shopstock WHERE id>'0' " . $extra);
		$total_items  = $stmt->rowCount();
		$limit        = $ibforums->vars['pages_peritems']
			? $ibforums->vars['pages_peritems']
			: 25;
		$current_page = $ibforums->input['page']
			? $ibforums->input['page']
			: 0;
		$limit_extra  = "LIMIT $current_page,$limit";
		$info['next'] = $current_page + $limit;
		$info['last'] = $current_page - $limit;

		$stmt          = $ibforums->db->query("SELECT * FROM ibf_store_shopstock WHERE id>'0' " . $extra . " AND avalible='0' ORDER BY item_name DESC " . $limit_extra);
		$returned_rows = $stmt->rowCount();
		while ($item = $stmt->fetch())
		{
			if ($item['soldout_time'] > 0 && $item['stock'] < 1)
			{
				$restock = $item['soldout_time'] + $item['restock_wait'];
				if ($restock < time())
				{
					$item['stock'] = $item['restock_amount'];
					$ibforums->db->exec("UPDATE ibf_store_shopstock SET stock='{$item['stock']}', soldout_time='0' WHERE id='{$item['id']}' LIMIT 1");
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

			$this->output .= $this->html->list_items($item);

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
				$this->output = str_replace("<!--Mass Buy Header-->", $this->html->mass_buy_header(), $this->output);
				$this->output = str_replace("<!--Mass Buy Middle-->", $this->html->mass_buy_middle($mass_buylist), $this->output);
				unset($mass_buy_list, $mass_buylist);
			}
		}
		$ibforums->lang['showingitems'] = str_replace("<#FIRST#>", $current_page, $ibforums->lang['showingitems']);
		$ibforums->lang['showingitems'] = str_replace("<#LAST#>", $info['next'], $ibforums->lang['showingitems']);
		$ibforums->lang['showingitems'] = str_replace("<#NUM#>", $total_items, $ibforums->lang['showingitems']);
		if ($returned_rows)
		{
			$this->output .= $this->html->next_lastlinks($info);
		} else
		{
			$this->output .= $this->html->cannot_finditems();
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

			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])), "Куплен товар '" . $item['item_name'] . "x" . $i . "' за " . round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])) . ' ' . $ibforums->vars['currency_name'], "bought_item");
			//	    $lib->write_log("Куплен товар '".$item['item_name']."x".$i."' за ".round($item['sell_price'] - (($ibforums->member['g_discount']/100)*$item['sell_price'])).' '.$ibforums->vars['currency_name'],"bought_item");
		} else
		{
			$ibforums->db->exec("INSERT INTO ibf_store_inventory(i_id,owner_id,item_id,price_payed) VALUES('','{$ibforums->member['id']}','{$item_id}','{$item['sell_price']}')");
			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])), "Куплен товар '" . $item['item_name'] . "x" . $i . "' за " . round($item['sell_price'] - (($ibforums->member['g_discount'] / 100) * $item['sell_price'])) . ' ' . $ibforums->vars['currency_name'], "bought_item");
			//	    $lib->write_log("Куплен товар '".$item['item_name']."' за ".round($item['sell_price'] - (($ibforums->member['g_discount']/100)*$item['sell_price'])).' '.$ibforums->vars['currency_name'],"bought_item");
		}
		// vot: redirect added
		$lib->redirect("item_bought", "act=store&code=inventory", "item_bought");
		// vot: ATTENTION: $item->on_buy() does not work here!!!
		// vot		    require($file_name);
		// vot		    $itemm = new item;
		// vot		    if(!$itemm->on_buy()) {
		// vot			$lib->redirect("item_bought","act=store&code=inventory","item_bought");
		// vot		    }
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
			$this->temp_output = $this->html->noinventory();
		}
		while ($user_inventory = $stmt->fetch())
		{
			// Add all the items they own to are semi-semi- master variable

			$user_inventory['item_name'] = $t = str_replace('&gt;', '>', $user_inventory['item_name']);
			$user_inventory['item_name'] = $t = str_replace('&lt;', '<', $user_inventory['item_name']);
			$user_inventory['item_desc'] = $t = str_replace('&gt;', '>', $user_inventory['item_desc']);
			$user_inventory['item_desc'] = $t = str_replace('&lt;', '<', $user_inventory['item_desc']);

			$this->temp_output .= $this->html->inventory_middle($user_inventory);

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
		$this->tempoutput .= $this->html->inventory_stats($value);

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
		$this->output .= $this->html->do_edit_users_points($member);
	}

	function do_do_edit_points()
	{
		global $ibforums, $lib;
		$member = $this->getmid($ibforums->input['username']);
		$lib->add_reason($ibforums->member['id'], $ibforums->member['name'], $member['id'], $ibforums->input['username'], $ibforums->input['points'], "Корректировка: " . $ibforums->input['username'] . ": " . $ibforums->input['points'] . " " . $ibforums->vars['currency_name'], $ibforums->input['user_reson'], "edit");
		$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->input['points']}' WHERE LOWER(name)='{$ibforums->input['username']}' LIMIT 1");
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
		$this->output .= $this->html->edit_users_inventory();
	}

	function do_staff_inventory()
	{
		global $ibforums, $std;
		$user = $this->getmid($ibforums->input['username']);
		$this->output .= $this->html->show_users_inventory_header($user);
		$stmt = $ibforums->db->query("SELECT i.*,s.*
		    FROM ibf_store_inventory i
		    LEFT JOIN ibf_store_shopstock s on (i.item_id=s.id)
		    WHERE i.owner_id='{$user['id']}' ORDER BY i.i_id DESC");
		while ($item = $stmt->fetch())
		{
			$this->output .= $this->html->show_users_inventory($item);
		}
		$this->output .= $this->html->edit_inventory_submit();
	}

	function do_do_staff_inventory()
	{
		global $ibforums, $lib;
		$i    = 0;
		$ii   = 0;
		$stmt = $ibforums->db->query("SELECT i_id FROM ibf_store_inventory WHERE owner_id='{$ibforums->input['userid']}' ORDER BY i_id DESC");
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
		$lib->add_reason($ibforums->member['id'], $ibforums->member['name'], $ibforums->input['userid'], '', 0, $msg, $ibforums->input['reson'], 'inventory');
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
		// vot	$ibforums->vars['pointsper_topic'] = $std->do_number_format($ibforums->vars['pointsper_topic']);
		// vot	$ibforums->vars['pointsper_reply'] = $std->do_number_format($ibforums->vars['pointsper_reply']);
		// vot	$ibforums->vars['pointsper_poll'] = $std->do_number_format($ibforums->vars['pointsper_poll']);
		// vot: require added for Sale Rules as HTML:
		//	require($ibforums->vars['base_dir']."sources/store/rules.php");

		$this->output .= $this->html->post_info();
		$this->output .= $this->html->output_stats_end();
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
		$html .= $this->html->error();
		$html .= $this->html->error_row($message);
		$html .= $this->html->end_page();
		// If you wish to remove it you will have to pay the 40$ fee.
		// See: www.outlaw.ipbhost.com/store/services.php for more infomation on how to pay.
		$html .= "<br/><div align='center' class='copyright'>Powered by <a href=\"http://www.subzerofx.com/shop/\" target='_blank'>IBStore</a> {$this->store_version} &copy; 2003-04 &nbsp;<a href='http://www.subzerofx.com/' target='_blank'>SubZeroFX.</a></div><br>";

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
			$link['shop'] .= $this->html->make_url("act=store&code=shop", $ibforums->lang['shop']);
		}
		$i = 0;
		while ($temp = $stmt->fetch())
		{
			if (isset($ibforums->input['view_all']) || $i < 10)
			{
				$link['shop'] .= $this->html->make_url("act=store&code=shop&category={$temp['catid']}", $temp['cat_name']);
			} else {
				if ($i >= 10 && empty($ibforums->input['view_all']))
				{
					$link['shop'] .= $this->html->make_url("act=store&code=shop&view_all=all", $ibforums->lang['view_all_cats']);
					break;
				}
			}
			$i++;
		}
		// Make the stat links
		if ($ibforums->vars['richest_onhand'])
		{
			$link['stat'] .= $this->html->make_url("act=store&code=stats&type=member", $ibforums->lang['stats_member']);
		}
		if ($ibforums->vars['richest_bank'])
		{
			$link['stat'] .= $this->html->make_url("act=store&code=stats&type=bank", $ibforums->lang['stats_bank']);
		}
		if ($ibforums->vars['richest_overall'])
		{
			$link['stat'] .= $this->html->make_url("act=store&code=stats", $ibforums->lang['stats_overall']);
		}
		if ($ibforums->vars['show_memberpoints'])
		{
			$link['points'] .= $this->html->member_points();
		}
		return $link;
	}

	//---------------------------------------------
	// Two Misc functions used for the quiz
	//---------------------------------------------
	function quiz_check($quiz_id, $let_play)
	{
		global $ibforums;
		$stmt   = $ibforums->db->query("SELECT * FROM ibf_store_quizwinners WHERE quiz_id='{$quiz_id}'");
		$played = $stmt->rowCount();
		if ($played >= $let_play && $let_play != 0)
		{
			$ibforums->db->exec("UPDATE ibf_store_quizinfo SET quiz_status='CLOSED' WHERE q_id='{$quiz_id}' LIMIT 1");
			return array('status' => 'close', 'plays_left' => $played);
		}
		while ($temp = $stmt->fetch())
		{
			if ($temp['memberid'] == $ibforums->member['id'])
			{
				return array('status' => 'open', 'plays_left' => $played);
			}
		}
		return array('status' => false, 'plays_left' => $played);
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
		$msg = strtolower($msg);
		$msg = stripslashes($msg);
		return $msg;
	}

	//---------------------------------------------
	// Quizs
	//---------------------------------------------
	function quiz()
	{
		global $ibforums, $std, $print;

		$this->nav = array($ibforums->lang['quiz_nav'], $ibforums->lang['main_quizs_nav']);
		$this->output .= $this->html->quiz_header();

		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$extra = "WHERE pending='0'";
		}

		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_quizinfo " . $extra);
		while ($quiz = $stmt->fetch())
		{
			$days           = 86400 * $quiz['run_for'];
			$quiz['status'] = $quiz['started_on'] + $days;
			$quiz['status'] -= time();
			$quiz['status_days'] = round($quiz['status'] / 86400);
			$quiz['take_quiz']   = "<a href='{$ibforums->base_url}act=store&code=take_quiz&quiz_id={$quiz['q_id']}&time={$quiz['timeout']}'>{$ibforums->lang['take_quiz']}</a>";

			$close_check = $this->quiz_check($quiz['q_id'], $quiz['let_only']);
			if ($close_check)
			{
				if ($close_check['status'])
				{
					$quiz['take_quiz'] = $close_check['status']
						? $ibforums->lang['played_quizalready']
						: $quiz['take_quiz'];
				}
				if ($close_check['status'] == 'close')
				{
					$quiz['take_quiz'] = $ibforums->lang['quiz_closed'];
				}
			}
			if ($quiz['quiz_status'] == 'CLOSED')
			{
				$quiz['take_quiz'] = $ibforums->lang['quiz_closed'];
			}

			$quiz['take_quiz'] .= "<br><a href='{$ibforums->base_url}act=store&code=quiz_results&quiz_id={$quiz['q_id']}'>{$ibforums->lang['show_results']}</a>";

			if ($quiz['quiz_status'] != 'CLOSED' && $quiz['status_days'] <= 0)
			{
				$update = $ibforums->db->exec("UPDATE ibf_store_quizinfo SET quiz_status='CLOSED' WHERE q_id='{$quiz['q_id']}' LIMIT 1");
			}
			if ($quiz['status_days'] <= 0)
			{
				$quiz['status_days'] = 0;
			}
			$quiz['quiz_status'] = ucfirst(strtolower($quiz['quiz_status']));
			$quiz['amount_won']  = $std->do_number_format($quiz['amount_won']);
			$this->output .= $this->html->list_quiz($quiz);
			if ($ibforums->vars['showplaysleft'])
			{
				if ($close_check['plays_left'] < 1)
				{
					$close_check['plays_left'] = $ibforums->lang['none_played'];
				}
				$this->output = str_replace("<!--Plays Left Header-->", $this->html->plays_left_header(), $this->output);
				$this->output = str_replace("<!--Plays Left Middle-->", $this->html->plays_left_middle($close_check['plays_left']), $this->output);
			}

		}
	}

	//---------------------------------------------
	// Take Quizs
	// This is the code that drove me crazy
	//---------------------------------------------
	function take_quiz()
	{
		global $ibforums, $std, $print;

		$this->nav = array($ibforums->lang['quiz_nav'], $ibforums->lang['takequiz_nav']);
		$stmt      = $ibforums->db->query("SELECT 1 FROM ibf_store_quizwinners WHERE quiz_id='{$ibforums->input['quiz_id']}' AND memberid='{$ibforums->member['id']}' LIMIT 1");
		if ($stmt->rowCount() > 0)
		{
			$this->error("played_quiz_already");
		}
		if ($ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$extra = "AND pending='0'";
		}
		$stmt = $ibforums->db->query("SELECT quiz_status,timeout FROM ibf_store_quizinfo WHERE q_id='{$ibforums->input['quiz_id']}' " . $extra . " LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$this->error("quiz_is_closed");
		}
		$settings = $stmt->fetch();
		if ($settings['quiz_status'] == 'CLOSED')
		{
			$this->error("quiz_is_closed");
		}
		$settings['time'] = time();

		$this->output .= $this->html->quiz_q_a_header($settings);
		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_quizs WHERE quiz_id='{$ibforums->input['quiz_id']}'");
		if ($stmt->rowCount() <= 0)
		{
			$this->error("couldnotloadanswer");
		}
		while ($quiz = $stmt->fetch())
		{
			if ($quiz['type'] == 'single' || $quiz['type'] == 'multiq')
			{
				if (!$quiz['anwser'] || !$quiz['question'])
				{
					continue;
				}
				$quiz['anwser']   = stripslashes($quiz['anwser']);
				$quiz['question'] = stripslashes($quiz['question']);
				$this->output .= $this->html->single_question($quiz);
			} else {
				if ($quiz['type'] == 'dropdown')
				{
					$quiz['dropdown'] = "<select name='uanswer_{$quiz['mid']}'>\n
										 <option value='---'>----</option>";
					$answers          = explode("||", $quiz['anwser']);
					foreach ($answers as $answer)
					{
						if (!preg_match("#{answer(1|2|3|4)_.+?:(.+)}#is", $answer, $match))
						{
							continue;
						}
						$match[2] = stripslashes($match[2]);
						$quiz['dropdown'] .= "\n<option value='{$match[2]}'>" . $match[2] . "</option>";
					}
					$quiz['dropdown'] .= "</select>";
					$quiz['question'] = stripslashes($quiz['question']);
					$this->output .= $this->html->dropdown_question($quiz);
				}
			}

		}

		$this->output .= $this->html->quiz_q_a_submit();
	}

	//---------------------------------------------
	// Check Quizs Questions and Answers
	//---------------------------------------------
	function do_take_quiz()
	{
		global $ibforums, $std, $print, $lib, $HTTP_POST_VARS;
		$stmt = $ibforums->db->query("SELECT 1 FROM ibf_store_quizwinners WHERE quiz_id='{$ibforums->input['quizid']}' AND memberid='{$ibforums->member['id']}' LIMIT 1");
		if ($stmt->rowCount() > 0)
		{
			$this->error("quiz_playedalready");
		}
		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_quizinfo WHERE q_id='{$ibforums->input['quizid']}' LIMIT 1");
		$quiz = $stmt->fetch();
		if ($quiz['quiz_status'] == 'CLOSED')
		{
			$this->error("quiz_is_closed");
		}
		foreach ($HTTP_POST_VARS as $field => $value)
		{
			if (!preg_match("#uanswer_(.+)#is", $field, $match))
			{
				continue;
			}
			$temp_answer[$match['1']] = $value;
		}
		$correct         = 0;
		$total_questions = 0;
		$stmt            = $ibforums->db->query("SELECT * FROM ibf_store_quizs WHERE quiz_id='{$ibforums->input['quizid']}' ORDER BY mid");
		while ($check = $stmt->fetch())
		{
			$user_answer    = $temp_answer[$check['mid']];
			$correct_answer = $check['anwser'];
			if ($check['type'] == 'single')
			{
				if ($this->tag_convert($user_answer) === $this->tag_convert($correct_answer))
				{
					$correct++;
				}
			} else {
				if ($check['type'] == 'dropdown' || $check['type'] == 'multiq')
				{
					$answers = explode("||", $correct_answer);
					foreach ($answers as $answer)
					{
						if (!preg_match("#{answer.+?_(.+?):(.+)}#is", $answer, $match))
						{
							continue;
						}
						if ($this->tag_convert($user_answer) === $this->tag_convert($match[2]) && ($match[1] == 1))
						{
							$correct++;
						}
					}
				}
			}
			$total_questions++;
		}
		$correct_percent        = round($correct / $total_questions * 100);
		$quiz['percent_needed'] = round($quiz['percent_needed']);
		$quiz['percent_needed'] = str_replace("%", "", $quiz['percent_needed']);
		$quiz['percent_needed'] = str_replace(",", "", $quiz['percent_needed']);
		if ($correct_percent > 100)
		{
			$correct_percent = 100;
		}
		$time      = time() - $ibforums->input['starttime'];
		$time_took = floor($time / 60);
		$ibforums->db->exec("INSERT INTO ibf_store_quizwinners VALUES('{$ibforums->input['quizid']}','{$ibforums->member['id']}','{$correct}','{$time_took}')");
		if ($correct_percent >= $quiz['percent_needed'])
		{
			if ($quiz['quiz_items'])
			{

				$quiz_items = explode("|", $quiz['quiz_items']);
				foreach ($quiz_items as $items)
				{
					if (!preg_match("#(.+)=(.+)#is", $items, $match))
					{
						continue;
					}

					$ibforums->db->exec("INSERT INTO ibf_store_inventory(i_id,owner_id,item_id,price_payed) VALUES('','{$ibforums->member['id']}','{$match[1]}','{$quiz['amount_won']}')");
					$items_gotten[] = $match[2];
				}
				$extra          = str_replace("<#ITEMS#>", implode(", ", $items_gotten), '<br><br>' . $ibforums->lang['quiz_items_gotten']);
				$extra_location = "code=inventory";

			} else {
				$extra_location = "code=quiz";
			}
			$ibforums->member['points'] += $quiz['amount_won'];
			$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->member['points']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
			$msg = $ibforums->lang['quiz_winner'] . $extra;

		} else {
			$msg = $ibforums->lang['quiz_notenoughtcorrect'];
		}

		$msg = str_replace("<CORRECT_PERCENT>", $correct_percent, $msg);
		$msg = str_replace("<CORRECT_NUMBER>", $correct, $msg);
		$msg = str_replace("<TOTAl_QUESTIONS>", $total_questions, $msg);
		$msg = str_replace("<WIN_AMOUNT>", $quiz['amount_won'], $msg);
		$msg = str_replace("<QUIZ_PERCENT_NEEDED>", $quiz['percent_needed'], $msg);

		$lib->redirect($msg, "act=store&" . $extra_location, 1);
	}

	//---------------------------------------------
	// Show the members results of a Quiz
	//---------------------------------------------
	function quiz_results()
	{
		global $ibforums;
		$this->nav = array($ibforums->lang['results_nav']);

		$this->output .= $this->html->quiz_results_header();
		$stmt = $ibforums->db->query("SELECT f.*, m.name,m.id
								FROM ibf_store_quizwinners f
								LEFT JOIN ibf_members m ON (m.id=f.memberid)
								WHERE f.quiz_id='{$ibforums->input['quiz_id']}' ORDER BY amount_right DESC");
		while ($member = $stmt->fetch())
		{
			$place++;
			if ($member['time_took'] <= 0)
			{
				$member['time_took'] = $ibforums->lang['results_lessthen'];
			} else {
				if ($member['time_took'] == 1)
				{
					$member['time_took'] = $member['time_took'] . ' ' . $ibforums->lang['results_minute'];
				} else
				{
					$member['time_took'] = $member['time_took'] . ' ' . $ibforums->lang['results_minutes'];
				}
			}
			$this->output .= $this->html->quiz_results_results($member, $place);
		}
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
		$this->output .= $this->html->misc_stats($stats);
		$this->output .= $this->html->output_stats_end();
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
			$this->output .= $this->html->header_stats($header);
			$stmt = $ibforums->db->query($query);
			while ($temp = $stmt->fetch())
			{
				if ($ibforums->input['type'] == 'bank')
				{
					$temp['points'] = $temp['deposited'];
				}
				$temp['points'] = $std->do_number_format($temp['points']);
				$this->output .= $this->html->output_stats($temp);
			}
			$this->output .= $this->html->output_stats_end();
		} else
		{
			$this->output .= $this->html->overall_stats_header();
			$stmt = $ibforums->db->query("SELECT id,name,points,deposited FROM ibf_members WHERE id>'0' AND points>'0' AND deposited>'0' ORDER BY points+deposited DESC LIMIT " . $ibforums->vars['richest_showamount']);
			while ($temp = $stmt->fetch())
			{
				$temp['total_points'] = $std->do_number_format($temp['points'] + $temp['deposited']);
				$temp['points']       = $std->do_number_format($temp['points']);
				$temp['deposited']    = $std->do_number_format($temp['deposited']);
				$this->output .= $this->html->output_overall_stats($temp);
			}
			$this->output .= $this->html->output_stats_end();
		}
	}

	// This is a helpfull function to convert the username to ID and get any othere info we want
	function getmid($username, $addon = "", $extra_a = "")
	{
		global $ibforums;
		$tables = "id,name";
		$tables .= $addon;
		$extra = "LOWER(name)='" . strtolower($username) . "'";
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
					$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $auto_interest, "Начислен процент: " . $auto_interest . ' ' . $ibforums->vars['currency_name'] . " Всего денег в банке: " . $ibforums->member['deposited'] . ' ' . $ibforums->vars['currency_name'], "auto_collect_int");
				}
			}
		}

		$this->output .= $this->html->bank($info, $collect_submit);
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
			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], round($interest_points), "Начислен процент: " . $std->do_number_format(round($interest_points)) . ' ' . $ibforums->vars['currency_name'] . " Всего денег в банке: " . $std->do_number_format(round($ibforums->member['deposited'])) . ' ' . $ibforums->vars['currency_name'], "collect_int");
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

		$output = $this->html->ShowTitle();

		$output .= $this->html->ShowHeader();

		$output .= $this->html->ShowFooter();

		$print->add_output("$output");

		$print->do_output(array('TITLE' => "test", 'JS' => 1, 'NAV' => "test"));

	}

}

?>
