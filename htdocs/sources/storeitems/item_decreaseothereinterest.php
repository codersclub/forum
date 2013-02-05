<?
//---------------------------------------------------
// IBStore Decrease Otheres Interest
//---------------------------------------------------
class item
{
	var $name = "Decrease Othere Users Interest";
	var $desc = "Decrease another users total interest by 2!";
	var $extra_one = "2";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::instance();
		$ADMIN->Html .= $SKIN->add_td_row(array(
		                                       "<b>How much it will decrease the interest?</b><br>",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));

		return $ADMIN->Html;
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
		global $ibforums;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}&u=200' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Username to Decrease Interest</td>
			</tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Username:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='name'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Go!'></td>
			   </tr>
			</form>
EOF;
	}

	function run_job()
	{
	}

	function do_on_use($increase, $protected, $blank = "")
	{
		global $ibforums, $print, $lib;
		$increase  = (int)$increase;
		$protected = (int)$protected;
		$stmt      = $ibforums->db->query("SELECT id,name,mgroup FROM ibf_members WHERE LOWER(name)='" . strtolower($ibforums->input['name']) . "' LIMIT 1");
		if ($stmt->rowCount() <= 0)
		{
			$lib->itemerror("Cannot find member.");
		}

		$ibforums->db->exec("UPDATE ibf_members SET extra_intrest=extra_intrest-$increase WHERE id='{$change['id']}' LIMIT 1");
		$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "Interest Decrease By " . $increase, "", "item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect('Interest Rate Decreased.', 'act=store&code=bank', '1');
		return "";
	}
}

?>



