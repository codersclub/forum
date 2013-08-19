<?
//---------------------------------------------------
//
//---------------------------------------------------

class item
{
	var $name = "Real thing";
	var $desc = "";
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

	function on_use($itemid = "")
	{
		global $ibforums, $lib;
		return <<<EOF

	 <form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
	 <tr><td class='pformstrip' width='100%' colspan='4'>Доставка купленного в магазине Digitex товара</td></tr>
	 <tr><td class='pformleft' width='100%' colspan='2'><b>Укажите в поле справа</b> Ваш реальный (почтовый) адрес для доставки Вам купленного товара.<br>
	 Если Вы поняли, что ошиблись в адресе, когда уже отослали его, срочно напишите личное письмо администратору.</td>
	     <td class='pformleft' width='100%' colspan='1'><input type='text' name='address' size='40'></td>
	 </tr>
	 <tr>
	     <td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Отослать адрес'></td>
	 </tr>
	 </form>

EOF;
	}

	function run_job()
	{
	}

	function do_on_use($price, $stock, $blank = "")
	{
		global $ibforums, $std, $lib;

		$stmt = $ibforums->db->query("SELECT ii.item_name,ii.item_desc FROM ibf_store_inventory i, ibf_store_shopstock ii
			    WHERE i.i_id='" . $ibforums->input['itemid'] . "' and ii.id=i.item_id");

		if ($item = $stmt->fetch())
		{
			$txt = 'Участник [url=' . $ibforums->base_url . 'showuser=' . $ibforums->member['id'];
			$txt .= ']' . $ibforums->member['name'] . '[/url] купил вещь "' . $item['item_name'] . '" ';
			$txt .= '("' . $item['item_desc'] . '") и попросил доставить её по адресу: ';
			$txt .= $ibforums->input['address'];

			$txt = str_replace("&lt;br&gt;", " ", $txt);
			$std->sendpm('2', $txt, "Покупка вещи", 9431, 1, 1);
			//			$std->sendpm('303',$txt,"Покупка вещи",9431,1,1);
		}

		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, '"' . $ibforums->member['name'] . '" заказал доставку товара "' . $row['item_name'] . '"', '', 'item');
		$lib->delete_item($ibforums->input['itemid']);
		return "";

	}
}

?>



