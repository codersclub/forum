<?
//---------------------------------------------------
// IBStore Create Forum
//---------------------------------------------------
class item {
	var $name = "Create Forum";
	var $desc = "Create a Reguler Forum!";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";
	var $apply_protect = 0;

	function on_add($EXTRA) {
		global $IN,$DB, $SKIN, $ADMIN;
	}
	function on_add_edits($admin) {}
	
	function on_add_extra(){}

	

		 function on_buy(){}

	function on_use($itemid="") {
		global $ibforums;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Change Your Member Title</td>
			</tr> 
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>New Title:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='new_title'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>New Title:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='new_title'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>New Title:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='new_title'></td>
			 </tr>			 			 
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Change Title'></td>
			   </tr>
			</form>
EOF;
	}
		
	function run_job(){}

	function do_on_use($id,$blank="",$blank="") {
		global $ibforums,$DB,$print,$lib;
		$DB->query("SELECT name,password FROM ibf_forums WHERE id='{$id}' LIMIT 1");
		if(!$DB->get_num_rows()) $lib->itemerror("Cannot find forum ID $id.");
		$forum = $DB->fetch_row();
		$message = "The Password to {$forum['name']} is, {$forum['password']}. <br /> 
					This password will be valid untill a Admin changes the Password to the {$forum['name']} Forums.";
		$pm_id = $lib->sendpm($ibforums->member['id'],$message,"Password Too {$forum['name']}.",$ibforums->member['id']);
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Password to the forum, {$forum['name']} has been PM too you.","act=Msg&CODE=03&VID=in&MSID={$pm_id}","1");
		return "";
	}
}
?>