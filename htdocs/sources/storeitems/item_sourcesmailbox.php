<?

//---------------------------------------------------
//
//---------------------------------------------------

class item
{

	var $name = "Sources mail box";
	var $desc = "";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";

	function on_add()
	{
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
		global $ibforums, $lib;
		return <<<EOF

	 <form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
	 <tr><td class='pformstrip' width='100%' colspan='4'>Покупка почтового ящика в домене sources.ru</td></tr>
	 <tr><td class='pformleft' width='50%' colspan='2'><b>Укажите в поле справа желаемый логин</b> (Ваша электроннная почта будет выглядеть <b>логин@sources.ru</b>)</td>
	     <td class='pformleft' width='50%' colspan='1'><input type='text' name='login'></td>
	 </tr>
	 <tr><td class='pformleft' width='50%' colspan='2'><b>Пароль к почтовому ящику (анонимность гарантируется)</b></td>
	     <td class='pformleft' width='50%' colspan='1'><input type='text' name='password'></td>
	 </tr>
	 <tr>
	     <td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Отослать запрос'></td>
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
		$txt = "Участник [url=" . $ibforums->base_url . "showuser=" . $ibforums->member['id'] . "]" . $ibforums->member['name'] . "[/url] заказал на изготовление почтовый ящик " . $ibforums->input['login'] . "@sources.ru ";
		$txt .= " и пожелал использовать к почтовому ящику пароль " . $ibforums->input['password'];
		$lib->sendpm('2', $txt, "Заказ электронного адреса на sources.ru", 9431, 1);
		//		$lib->sendpm('303',$txt,"Заказ электронного адреса на sources.ru",9431,1);
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "'" . $ibforums->member['name'] . "' заказал почтовый ящик '{$ibforums->input['login']}@sources.ru'", "", "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("", "act=store&code=inventory", "1");
		return "";
	}

}

?>
