<?
//---------------------------------------------------
//
//---------------------------------------------------

class item {
	var $name = "Real thing";
	var $desc = "";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";

	function on_add($EXTRA) {}
	function on_add_edits($admin) {}
	function on_add_extra(){}

function on_buy() {
	}
	
	function on_use($itemid="") {}

	function run_job(){}

function do_on_use($price,$stock,$blank="") {
		global $ibforums, $DB, $std, $lib;

		require "./sources/lib/emailer.php";
		$email = new emailer();

		$show_popup = 1;
		
		$DB->query("SELECT item_id FROM ibf_store_inventory WHERE i_id='".$ibforums->input['itemid']."'");

		if ( $row = $DB->fetch_row() ) 
		 {

		    $DB->query("SELECT item_name FROM ibf_store_shopstock WHERE id='".$row['item_id']."'");

   		    if ( $row = $DB->fetch_row() ) 
  		     {

	
        		$txt = 'Участник [url='.$ibforums->base_url.'showuser='.$ibforums->member['id'].']'.$ibforums->member['name'].'[/url] купил вещь "'.$row['item_desc'].'". Секретный код 1707.';
        
	
			$db_string = $std->compile_db_string( array( 
														 'member_id'      => $ibforums->member['id'],
														 'msg_date'       => time(),
														 'read_state'     => '0',
														 'title'          => "Покупка вещи",
														 'message'        => $std->remove_tags($txt),
														 'from_id'        => $ibforums->member['id'],
														 'vid'            => 'in',
														 'recipient_id'   => '2',
														 'tracking'       => 0,
												)      );
			
			$DB->query("INSERT INTO ibf_messages (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
			$new_id = $DB->get_insert_id();
			unset($db_string);
			
			//-----------------------------------------------------
			
			$DB->query("UPDATE ibf_members SET ".
						"msg_total = msg_total + 1, "                    .
						"new_msg = new_msg + 1, "                        .
						"msg_from_id='"     . $ibforums->member['id']    .  "', ".
						"msg_msg_id='"      . $new_id                    .  "', ".
						"show_popup='"      . $show_popup                .  "' ".
						"WHERE id='2'");
						
			$email->get_template("pm_notify", 3);
			
			$email->build_message( array(
													'NAME'   => "Vot",
													'POSTER' => $ibforums->member['name'],
													'TITLE'  => "Покупка вещи",
													'LINK'   => "?act=Msg&CODE=03&VID=in&MSID=$new_id",
												  )
											);
											
			$email->subject = $ibforums->lang['pm_email_subject'];
			$email->to      = "admin@sources.ru";
        		$email->send_mail();

			$db_string = $std->compile_db_string( array( 
														 'member_id'      => $ibforums->member['id'],
														 'msg_date'       => time(),
														 'read_state'     => '0',
														 'title'          => "Покупка вещи",
														 'message'        => $std->remove_tags($txt),
														 'from_id'        => $ibforums->member['id'],
														 'vid'            => 'in',
														 'recipient_id'   => '303',
														 'tracking'       => 0,
												)      );
			
			$DB->query("INSERT INTO ibf_messages (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
			$new_id = $DB->get_insert_id();
			unset($db_string);
			
			//-----------------------------------------------------
			
			$DB->query("UPDATE ibf_members SET ".
						"msg_total = msg_total + 1, "                    .
						"new_msg = new_msg + 1, "                        .
						"msg_from_id='"     . $ibforums->member['id']    .  "', ".
						"msg_msg_id='"      . $new_id                    .  "', ".
						"show_popup='"      . $show_popup                .  "' ".
						"WHERE id='303'");
						
			$email->get_template("pm_notify", 3);
			
			$email->build_message( array(
													'NAME'   => "Song",
													'POSTER' => $ibforums->member['name'],
													'TITLE'  => "Покупка вещи",
													'LINK'   => "?act=Msg&CODE=03&VID=in&MSID=$new_id",
												  )
											);
											
			$email->subject = $ibforums->lang['pm_email_subject'];
			$email->to      = "song@kmtn.ru";
        		$email->send_mail();

        	   }

        	}

		$lib->write_log('Участник '.$ibforums->member['name'].' купил вещь "'.$row['item_desc'].'"','item');
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect('','act=store&code=inventory','1');		
		return "";


	}
}
?>



