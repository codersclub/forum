<?
//---------------------------------------------------
// IBStore Mystery Box
//---------------------------------------------------

class item
{
	var $name = "Mystery Box";
	var $desc = "Who knows what item you will get from this";
	var $extra_one = "1";
	var $extra_two = "0";
	var $extra_three = "";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$Admin->html .= $SKIN->add_td_row(array(
		                                       "<b>Only give items with a sell price above?</b><br>This will only give items out with a sellprice above what you set.",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));
		$Admin->html .= $SKIN->add_td_row(array(
		                                       "<b>Only give stocked items?</b><br>Only give items that are in stock?",
		                                       $SKIN->form_yes_no("extra_two", $EXTRA['extra_two'])
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

	function do_on_use($price, $stock, $blank = "")
	{
		global $ibforums, $print, $lib;
		$stock = (int)$stock;
		$price = (int)$price;
		if ($stock)
		{
			$stock = "AND stock>'0'";
		} else
		{
			$stock = "";
		}
		$stmt = $ibforums->db->query("SELECT * FROM ibf_store_shopstock WHERE sell_price>'{$price}' " . $stock);
		if (!$stmt->rowCount())
		{
			$lib->redirect("Unable to find any items to give, item has not been delete.");
		}
		while ($temp = $stmt->fetch())
		{
			$giveable_items[$i]      = $temp['id'];
			$giveable_item_name[$i]  = $temp['item_name'];
			$giveable_item_price[$i] = $temp['sell_price'];
			$i++;
		}
		$giveitem  = mt_rand(0, count($giveable_items));
		$temp      = $giveable_items[$giveitem];
		$item_name = $giveable_item_name[$giveitem];
		$price     = $giveable_item_price[$giveitem];
		$ibforums->db->exec("INSERT INTO ibf_store_inventory VALUES('','{$ibforums->member['id']}','{$temp}','{$price}')");
		$lib->write_log("Got Item " . $item_name, "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect('You got ' . $item_name . ' Worth ' . $price . ' ' . $ibforums->vars['currency_name'] . '.', 'act=store&code=inventory', '1');
		return "";
	}
}

?>



