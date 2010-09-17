<?
//---------------------------------------------------
// IBStore Upload Emoticion
//---------------------------------------------------
class item {
	var $name = "Upload Emoticion";
	var $desc = "Upload A New Emoticion!";
	var $extra_one = "1";
	var $extra_two = "800";
	var $extra_three = "jpg,gif,jpeg";

	function on_add($EXTRA) {
		global $IN,$DB, $SKIN, $ADMIN;
		
		$ADMIN->HTML .= $SKIN->add_td_row( array( "<b>Clickable Emoticion?</b><br>yes to add a Emoticion as clickable, no to not add it as a clickable Emoticion/" ,
								  $SKIN->form_yes_no( "extra_one", $EXTRA['extra_one']  )
					 )      );
					 
		$ADMIN->HTML .= $SKIN->add_td_row( array( "<b>Maximum Emoticion Size?</b><br>The Maximum size in Bytes that a emoticion can be." ,
								  $SKIN->form_input( "extra_two", $EXTRA['extra_two']  )
					 )      );
					 
		$ADMIN->HTML .= $SKIN->add_td_row( array( "<b>Allowed File Types?</b><br>The files types the emoticion is allowed to have. Seperate with a comma" ,
								  $SKIN->form_input( "extra_three", $EXTRA['extra_three']  )
					 )      );
		return $ADMIN->HTML;
	}
	function on_add_edits($admin) {}
	function on_add_extra(){}


	
		 function on_buy(){}

	function on_use($itemid="") {
		global $ibforums,$DB;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'  enctype='multipart/form-data'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Upload Emoticon</td>
			</tr> 
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Emoticon Code:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='input' name='ecode' value=':test:'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Emoticon Image:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='file' name='emoticion'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Go!'></td>
			   </tr>
			</form>
EOF;
	}
	function run_job(){}

	function do_on_use($click,$max_size,$allowed) {
		global $ibforums,$DB,$print,$std,$lib,$HTTP_POST_FILES;
		$ibforums->input['ecode'] = str_replace(" ","",$ibforums->input['ecode']);
		if($ibforums->input['ecode'] == "" || !$ibforums->input['ecode']) { 
			$lib->itemerror("Error, Please enter a Emoticion Code.");
		}
		$file['name'] = $HTTP_POST_FILES['emoticion']['name'];
		$file['size'] = $HTTP_POST_FILES['emoticion']['size'];
		$file['type'] = $HTTP_POST_FILES['emoticion']['type'];
		$file['type'] = preg_replace( "/^(.+?);.*$/", "\\1", $file['type'] );
		$file['type'] = explode("/",$file['type']);
		$allowed = str_replace(".","",$allowed);
		$file['type'] = $file['type'][count($file['type'])-1];
		$allow = explode(",",$allowed);
		if(!in_array($file['type'],$allow)) {
			$allowed = str_replace(",",", .",$allowed);
			$lib->itemerror("Error, the emoticion you are to upload is not in the list of valid files, the allowed file types are .{$allowed}.");
		}
		if($file['size'] >= $max_size) {
			$lib->itemerror("File size to big max emoticion size allowed is, ".$max_size." Bytes, you are trying to upload ".$file['size']." Bytes.");
		}
		if (!is_dir($ibforums->vars['html_dir'].'emoticons')) {
			$lib->itemerror("Could not locate the emoticions directory, please make sure the html_dir path is set correctly.");
		}

		if($HTTP_POST_FILES['emoticion']['name'] == "" || !$HTTP_POST_FILES['emoticion']['name'] || ($HTTP_POST_FILES['emoticion']['name'] == "none")) {
			$lib->itemerror("Please choose a emoticion to upload.");		
		}
		@chmod($ibforums->vars['html_dir'].'emoticons',0777);
		if(!@move_uploaded_file($HTTP_POST_FILES['emoticion']['tmp_name'], $ibforums->vars['html_dir'].'emoticons'."/".$file['name'])) {
			$lib->itemerror("Upload failed, please make sure the emoticions folder is chmoded to 777.");
		} else {
			@chmod($ibforums->vars['html_dir'].'emoticons'."/".$file['name'],0777);
		}
		$clickable = "Unclickable";
		if($click) $clickable = "Clickable";
	
		$ibforums->input['ecode'] = stripslashes($ibforums->input['ecode']);										  
		$DB->query("INSERT INTO ibf_emoticons (id,typed,image,clickable) VALUES('','{$ibforums->input['ecode']}','{$file['name']}','{$click}')");		
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Uploaded Emoticon as {$clickable}.","act=store", "1");
	}
}
?>