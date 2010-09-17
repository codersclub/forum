<?
//---------------------------------------------------
// IBStore Other Members Signature Change
//---------------------------------------------------
class item {
	var $name = "Any Members Signature Change";
	var $desc = "Change any members signature to a new one.";
	var $extra_one = "4";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA) {
		global $IN,$DB, $SKIN, $ADMIN;
		$ADMIN->HTML .= $SKIN->add_td_row( array( "<b>Protected Groups:</b><br>The User groups who are not allowed to have their signatures changed. (Seperate with a comma \",\")" ,
								  $SKIN->form_input( "extra_one", $EXTRA['extra_one']  )
					 )      );
		return $ADMIN->HTML;
	}
	function on_add_edits($admin) {}

	function on_add_extra(){}



		 function on_buy(){}

	function on_use($itemid="") {
		global $ibforums,$DB;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Any Member Signature Change</td>
			</tr> 
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Username who&quot;s signature to change:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='username'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>New Signature:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><textarea name="signature" cols="50" rows="10"></textarea></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Go!'></td>
			   </tr>
			</form>
EOF;
	}

	function run_job(){}

function do_on_use($protected,$blank="",$blank="") {
		global $ibforums,$DB,$print,$lib;

		if ( (strlen($ibforums->input['signature']) > $ibforums->vars['max_sig_length']) && ($ibforums->vars['max_sig_length']))
		{
			$lib->itemerror("The signature you entered for that member is too long. Maximum Signature length is: {$ibforums->vars['max_sig_length']}.");
		}
		require($ibforums->vars['base_dir']."sources/lib/post_parser.php");
		$parser = new post_parser();  		
		$ibforums->input['signature'] = $parser->convert(  array( 'TEXT'	  => $ibforums->input['signature'],
																  'SMILIES'   => 0,
																  'CODE'      => $ibforums->vars['sig_allow_ibc'],
															      'HTML'      => $ibforums->vars['sig_allow_html'],
																  'SIGNATURE' => 1
														)       );														 
		$DB->query("SELECT id,name,mgroup FROM ibf_members WHERE LOWER(name)='".strtolower($ibforums->input['username'])."' LIMIT 1");
		if($DB->get_num_rows() == 0) {
			$lib->itemerror("We cannot seem to find that name.");
		}
		$member = $DB->fetch_row();
		$protect = explode(",",$protected);
		if($lib->checkprotected($protect,$member['mgroup'])) {
			$lib->itemerror("Sorry, you are trying to change a member who is in the protected group.");
		}
		$DB->query("UPDATE ibf_members SET signature='{$ibforums->input['signature']}' WHERE id='{$member['id']}' LIMIT 1");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->write_log("Changed <a href='{$ibforums->base_url}showuser={$member['id']}'>{$member['name']}'s</a> Signature.","item");
		$lib->redirect("Changed {$member['name']} Signature!","showuser={$member['id']}","1");
		return "";
	}
}
?>


