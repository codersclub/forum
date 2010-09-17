<?
//---------------------------------------------------
// IBStore Post Count 
//---------------------------------------------------
class item {
	var $name = "Remove violations";
	var $desc = "";
	var $extra_one = "1";
	var $extra_two = "";
	var $extra_three = "";
	
	function on_add($EXTRA) {
		global $IN,$DB, $SKIN, $ADMIN;
		$ADMIN->HTML .= $SKIN->add_td_row( array( "<b>Add How much violations count to decrease?</b>" ,
								  $SKIN->form_input( "extra_one", $EXTRA['extra_one']  )
					 )      );
		return $ADMIN->HTML;
	}
	function on_add_edits($admin) {
		global $DB,$ADMIN,$INFO;
		$checker = $INFO['base_dir']."sources/store/edit_check.php";
		require_once($checker);
		$is_their = row_check($INFO['sql_tbl_prefix']."members","viol_remove");
		if(!$is_their) {
			$DB->query("ALTER TABLE `ibf_members` ADD `viol_remove` INT( 9 ) DEFAULT '0' NOT NULL AFTER `posts`");
		}
		return false;
	}
	function on_add_extra(){}
	
	
	
        function on_buy(){}

	function on_use($itemid="") {}

	function run_job(){}

	function do_on_use($remove,$blank="",$blank="") {
		global $ibforums,$DB,$print,$lib,$std;


		$remove = (int) $remove;

	        if ( ! $ibforums->vars['warn_past_max'] )
	        {
	        	$ibforums->vars['warn_min'] = $ibforums->vars['warn_min'] ? $ibforums->vars['warn_min'] : 0;
	        	$ibforums->vars['warn_max'] = $ibforums->vars['warn_max'] ? $ibforums->vars['warn_max'] : 10;
			if ( $remove < $ibforums->vars['warn_min'] ) $std->Error( array( LEVEL => '1', MSG => 'no_warn_max' ) );
	        }

		$save = array();

		$save['wlog_type'] = 'pos';
		$save['wlog_date'] = time();

		$DB->query("SELECT warn_level FROM ibf_members WHERE id='".$ibforums->member['id']."'");
		$row = $DB->fetch_row();
		$warn_level = intval($row['warn_level']);
		if ( $warn_level - $remove < 0 ) $std->Error( array( LEVEL => '1', MSG => 'no_warn_max' ) );

	        $DB->query("DELETE FROM ibf_warnings WHERE mid='".$ibforums->member['id'].
			   "' and level='".$warn_level."'");
		$warn_level = $warn_level - $remove;

		if ( $warn_level > 0 ) $group = 15 + $warn_level; 
		 elseif ( $ibforums->member['posts'] > 499 ) $group = 25; else $group = 3;

		$mes = "Уменьшение уровня предупреждений по покупке уменьшения предупреждений в магазине проекта DigiMania";

		if ( ($group == 3) or ($group == 25) ) $mes .= "\r\n\r\n[color=green]Вы обратно переведены в группу участников.[/color]"; else
 	  	 $mes .= "\r\n\r\n[color=red]Вы переведены в группу нарушивших правила уровня ".$warn_level.".[/color]";

		$save['wlog_notes']  = "<content>{$mes}</content>";
		$save['wlog_notes'] .= "<mod>{$ibforums->input['mod_value']},{$ibforums->input['mod_unit']},{$ibforums->input['mod_indef']}</mod>";
		$save['wlog_notes'] .= "<post>{$ibforums->input['post_value']},{$ibforums->input['post_unit']},{$ibforums->input['post_indef']} </post>";
		$save['wlog_notes'] .= "<susp>{$ibforums->input['susp_value']},{$ibforums->input['susp_unit']}</susp>";

		$save['wlog_mid']     = $ibforums->member['id'];
		$save['wlog_addedby'] = 9431;

		$dbs = $DB->compile_db_insert_string( $save );
		$DB->query("INSERT INTO ibf_warn_logs ({$dbs['FIELD_NAMES']}) VALUES({$dbs['FIELD_VALUES']})");
		$DB->query("UPDATE ibf_members SET 
			warn_level=warn_level-'".$remove."',
			viol_remove=viol_remove+'".$remove."',
			warn_lastwarn='".time()."',
			mgroup='".$group."' WHERE id='".$ibforums->member['id']."' LIMIT 1");

		$lib->write_log($ibforums->member['id'],
				$ibforums->member['name'],
				$ibforums->member['id'],
				$ibforums->member['name'],
				0,
				"Использован товар 'Уменьшение уровня предупреждений' на ".$remove." единиц.",
				"",
				"item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect('Added onto Post Count','act=store&code=inventory','1');		
		return "";
	}
}
?>



