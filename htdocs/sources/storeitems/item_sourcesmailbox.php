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
	 <tr><td class='pformstrip' width='100%' colspan='4'>������� ��������� ����� � ������ sources.ru</td></tr>
	 <tr><td class='pformleft' width='50%' colspan='2'><b>������� � ���� ������ �������� �����</b> (���� ������������ ����� ����� ��������� <b>�����@sources.ru</b>)</td>
	     <td class='pformleft' width='50%' colspan='1'><input type='text' name='login'></td>
	 </tr>
	 <tr><td class='pformleft' width='50%' colspan='2'><b>������ � ��������� ����� (����������� �������������)</b></td>
	     <td class='pformleft' width='50%' colspan='1'><input type='text' name='password'></td>
	 </tr>
	 <tr>
	     <td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='�������� ������'></td>
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
		$txt = "�������� [url=" . $ibforums->base_url . "showuser=" . $ibforums->member['id'] . "]" . $ibforums->member['name'] . "[/url] ������� �� ������������ �������� ���� " . $ibforums->input['login'] . "@sources.ru ";
		$txt .= " � ������� ������������ � ��������� ����� ������ " . $ibforums->input['password'];
		$lib->sendpm('2', $txt, "����� ������������ ������ �� sources.ru", 9431, 1);
		//		$lib->sendpm('303',$txt,"����� ������������ ������ �� sources.ru",9431,1);
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "'" . $ibforums->member['name'] . "' ������� �������� ���� '{$ibforums->input['login']}@sources.ru'", "", "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("", "act=store&code=inventory", "1");
		return "";
	}

}

?>
