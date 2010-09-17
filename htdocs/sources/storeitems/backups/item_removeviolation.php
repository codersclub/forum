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
		global $ibforums,$DB,$print,$lib;

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

		$reason = "”меньшение уровн€ предупреждений по покупке уменьшени€ предупреждений в магазине проекта DigiMania";

		$save['wlog_notes']  = "<content>{$reason}</content>";
		$save['wlog_notes'] .= "<mod>{$ibforums->input['mod_value']},{$ibforums->input['mod_unit']},{$ibforums->input['mod_indef']}</mod>";
		$save['wlog_notes'] .= "<post>{$ibforums->input['post_value']},{$ibforums->input['post_unit']},{$ibforums->input['post_indef']} </post>";
		$save['wlog_notes'] .= "<susp>{$ibforums->input['susp_value']},{$ibforums->input['susp_unit']}</susp>";

		$save['wlog_mid']     = $ibforums->member['id'];
		$save['wlog_addedby'] = 9431;

		$dbs = $DB->compile_db_insert_string( $save );
		$DB->query("INSERT INTO ibf_warn_logs ({$dbs['FIELD_NAMES']}) VALUES({$dbs['FIELD_VALUES']})");
		$time = time();
		$DB->query("UPDATE ibf_members SET warn_level=warn_level-$remove,viol_remove=viol_remove+$remove,warn_lastwarn=$time WHERE id='{$ibforums->member['id']}' LIMIT 1");

		$lib->write_log("Decreased ".$remove." violations.","item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect('Added onto Post Count','act=store&code=inventory','1');		
		return "";
	}
}
?>



