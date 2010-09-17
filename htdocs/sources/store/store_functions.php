<?PHP
/*---------------------------------------------------------------------*\
|   IBStore 2.5															|
+-----------------------------------------------------------------------+
|   (c) 2003 Zachary Anker												|
|	Email: wingzero1018@hotmail.com										|
|   http://www.subzerofx.com/shop/										|
+-----------------------------------------------------------------------+
|	You may edit this file as long as you retain this Copyright notice.	|	
|	Redistribution not permitted without permission from Zachary Anker.	|	
\*---------------------------------------------------------------------*/
class lib {		
    function item_names($item) {		
	$items['addtopost'] 			= "Add To Post Count";
	$items['autocollect'] 			= "Auto Collect Interest";
	$items['basicsteal'] 			= "Basic Steal";
	$items['bypassflood'] 			= "Bypass Flood Control";
	$items['decreaseflood'] 		= "Decrease Flood Control";
	$items['changegroup'] 			= "Change Group";
	$items['changename'] 			= "Change Username";
	$items['decreaseothereinterest']	= "Decrease Others Interest";
	$items['increaseinterest'] 		= "Increase Your Interest";
	$items['mysterybox'] 			= "Mystery Box";
	$items['mysterything']			= "Mystery Thing";
	$items['randommoney'] 			= "Random Money";
	$items['unusable'] 			= "Unusable";
	$items['uploadavatar'] 			= "Upload Avatar";
	$items['uploadotheresavatar'] 	= "Upload Others Avatar";
	$items['nameeffect'] 			= "Name Effect";
	$items['closeopenowntopic'] 	= "Close/Open Own Topics";
	$items['opencloseanytopic'] 	= "Close/Open Any Topic";
	$items['pinunpinowntopic'] 		= "Pin/Unpin Own Topics";
	$items['pinunpinanytopic'] 		= "Pin/Unpin Any Topic";
	$items['otherssignaturechange'] = "Change Other Members Signature";
	$items['uploademoticon'] 		= "Upload Emoticion";
	$items['buypasswordforum'] 		= "Buy Password to Selected Forum";
	$items['changeyourtitle']		= "Change Your Own Member Title";
	$items['changeotherestitle']	= "Change Any Members Title";
	$items['unknown'] 				= $item.' - Unknown Item';
	/* Although these are not the Default IBStore Items its easyer to add them here
	   instead of having to have the Mod Authors do it */

	// Munja Pets Addon Items (Can be gotten here: http://mods.ibplanet.com/db/?mod=2096)
	$items['munjpet_bed']				 = "Munja Pets: Bed";
	$items['munjpet_changename'] 		 = "Munja Pets: Name Change";
	$items['munjpet_munjpet_changesex']  = "Munja Pets: Change Gender";
	$items['munjpet_changespecies'] 	 = "Munja Pets: Change Species";
	$items['munjpet_food'] 				 = "Munja Pets: Food";
	$items['munjpet_toy'] 				 = "Munja Pets: Toy"; 
	// Advance Steal (Can be gotten here: http://mods.ibplanet.com/db/?mod=2069)
	$items['simplesteal']				= "Basic Steal V2";
	$items['getsteal']					= "Steal Helpers";
	$items['defendsteal'] 				= "Steal Defence";
	$items['advancesteal'] 				= "Advance Steal";

	$item = str_replace(".php","",str_replace("item_","",$item));
		
	if(isset($items[$item])) {
	    return $items[$item];
	} else return $items['unknown'];

    }		

    function make_numsafe($make_safe) {
	$make_safe = str_replace(",","",$make_safe);
	$make_safe = str_replace(".","",$make_safe);
	$make_safe = str_replace("%","",$make_safe);
	while(preg_match("#&(.+?);#",$make_safe,$match)) {
	    $make_safe = str_replace($match[0],"",$make_safe);
	}
	$make_safe = trim($make_safe);
	$make_safe = str_replace("&","",$make_safe);
	$make_safe = str_replace("$","",$make_safe);
	$make_safe = str_replace("_","",$make_safe);
	$make_safe = (int) $make_safe;
	return $make_safe;
    }

	function check_item_inventory($userid,$max,$addon=0) {
		global $DB;
		if($max <= 0) return false;
		$DB->query("SELECT 1 FROM ibf_store_inventory WHERE owner_id='{$userid}'");
		if($DB->get_num_rows()+$addon > $max) return true;

		return false;
	}

	function load_extra($itemid) {
		global $DB;
		$DB->query("SELECT item_id FROM ibf_store_inventory WHERE i_id='{$itemid}' LIMIT 1");
		$itemid = $DB->fetch_row();
		$itemid = $itemid['item_id'];
		$DB->query("SELECT extra_one,extra_two,extra_three FROM ibf_store_shopstock WHERE id='{$itemid}' LIMIT 1");
		$extra = $DB->fetch_row();
		return $extra;
	}		

	function write_log($fromid=0,$fromname='',$toid=0,$toname='',$sum=0,$message='',$reason,$type='') {
		global $ibforums,$DB;
		$time = time();
//		switch($type) {
//			case 'item':
//				$type = "Item";
//				break;
//			case 'donate_m':
//				$type = "Sent Money";
//				break;
//			case 'donate_i':
//				$type = "Sent Item";
//				break;
//			case 'collect_int':
//				$type = "Collected Interest";
//				break;
//			case 'auto_collect_int':
//				$type = "Auto Collected Interest";
//				break;
//			case 'bought_item':
//				$type = "Bought Item";
//				break;			
//			case 'use_item':
//				$type = "Use Item";
//				break;			
//			case 'edit':
//				$type = "Edit";
//				break;			
//			default:
//				break;
//		}	

							    //logid, fromid,                      message,                reason,   username,     toid,     toname,      type,     sum,     time,
		$DB->query("INSERT INTO ibf_store_logs VALUES('','{$fromid}','".addslashes($message)."','".addslashes($reason)."','{$fromname}','{$toid}','{$toname}','{$type}','{$sum}','{$time}')");	
//		$DB->query("INSERT INTO ibf_store_logs VALUES('','{$ibforums->member['id']}','".addslashes($message)."','".addslashes($reason)."','{$ibforums->member['name']}','{$toid}','{$toname}','{$type}','{$sum}','{$time}')");	
//		$DB->query("INSERT INTO ibf_store_logs VALUES('','".addslashes($message)."','{$ibforums->member['name']}','{$type}','{$time}')");	
		                                            //logid      message               username                     type      time
	}

	function write_log_old($message,$type="") {
		global $ibforums,$DB;
		$time = time();
		switch($type) {
			case 'item':
				$type = "Item";
				break;
			case 'donate_m':
				$type = "Sent Money";
				break;
			case 'donate_i':
				$type = "Sent Item";
				break;
			case 'collect_int':
				$type = "Collected Interest";
				break;
			case 'auto_collect_int':
				$type = "Auto Collected Interest";
				break;
			case 'bought_item':
				$type = "Bought Item";
				break;			
			case 'use_item':
				$type = "Use Item";
				break;			
			default:
				break;
		}	

							    //logid, fromid, message, username, toid, toname, type, sum, time,
		$DB->query("INSERT INTO ibf_store_logs VALUES('','".addslashes($message)."','{$ibforums->member['name']}','{$type}','{$time}')");	
		                                            //logid      message               username                     type      time
	}

	function output($output) {
//		if(!@preg_match("#Powered By <a href=\"http://www.subzerofx.com/shop/\" target='_blank'>IBStore</a>#is",$output)) {
//			die("<br>
//				<b>Parse error</b>:  parse error, unexpected '=' in <b>".@str_replace('\\','/',@getcwd())."/sources/store/store.php</b> on line <b>951</b><br>");
//		}
		return $output;
	}

	function redirect($get_lang,$location="",$item=0) {
		global $print,$ibforums;
		if(!$item) {
			$message = $ibforums->lang[''.$get_lang.''];		
		} else {
			$message = $get_lang;
		}	

		$print->redirect_screen($message, $location, "html");
		exit;
	}

	function delete_item($itemid) {
		global $DB,$ibforums;
		$DB->query("DELETE FROM ibf_store_inventory WHERE i_id='{$itemid}' AND owner_id='{$ibforums->member['id']}' LIMIT 1");
	}	

	function sendpm($sendto,$message,$title,$sender_id="",$popup=0) {
		global $std, $DB, $ibforums;
		if(!$sender_id) $sender_id = $ibforums->member['id'];
		$db_string = $std->compile_db_string( array( 
					 'member_id'      => $sendto,
					 'msg_date'       => time(),
					 'read_state'     => '0',
					 'title'          => $title,
					 'message'        => $std->remove_tags($message),
					 'from_id'        => $sender_id,
					 'vid'            => 'in',
					 'recipient_id'   => $sendto,
					 'tracking'       => 0,
					)      );
		$DB->query("INSERT INTO ibf_messages (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
		$message_id = $DB->get_insert_id();
		$db_string = array();
		if($popup) $extra = ",show_popup=1";
		$DB->query("UPDATE ibf_members SET msg_total = msg_total + 1,
						   new_msg = new_msg + 1,
						   msg_from_id='{$sender_id}',
						   msg_msg_id='{$message_id}'
						   ".$extra."
					   WHERE id='{$sendto}' LIMIT 1");
		return $message_id;
	}

	function add_reason($userid,$name,$toid,$toname,$sum,$reson,$users_reson,$type) {
		global $DB;
		$time = time();
		$reson = addslashes(stripslashes($reson));
		$users_reson = addslashes(stripslashes($users_reson));

//		$DB->query("INSERT INTO ibf_store_modlogs (id,username,reson,user_reson,type,time) VALUES('','{$name}','{$reson}','{$users_reson}','{$type}','{$time}')");
		$DB->query("INSERT INTO ibf_store_modlogs (id,fromid,username,toid,toname,sum,reson,user_reson,type,time) VALUES('','{$userid}','{$name}','{$toid}','{$toname}','{$sum}','{$reson}','{$users_reson}','{$type}','{$time}')");
                                                       //  id, fromid, username, toid, toname, sum, reson, user_reson, type, time

	}

	function parsepost($msg) {
		return $msg;
	}

	function checkprotected($groups,$group) {
		global $ibforums;
		if(!is_array($groups)) {
			if($groups == $group) return true;
		} else {
			foreach($groups as $groupss) {
				if($groupss == $group) return true;
			}

		}
		return false;
	}

	function itemerror($message) {
		global $std;

		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'any_error' , 'EXTRA' => $message ) );

	}
}
?>