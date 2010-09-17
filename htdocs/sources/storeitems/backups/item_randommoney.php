<?
//---------------------------------------------------
// IBStore Random Money
//---------------------------------------------------
class item {
	var $name = "Random Money";
	var $desc = "Get a random amount of money between 0 and 1000!";
	var $extra_one = "0";
	var $extra_two = "1000";
	var $extra_three = "";
	function on_add($EXTRA) {
		global $IN,$DB, $SKIN, $ADMIN;

		$ADMIN->Html .= $SKIN->add_td_row( array( "<b>Lowest Amount?</b><br>The lowest random amount that can be given." ,
								  $SKIN->form_input( "extra_one", $EXTRA['extra_one']  )
					 )      );

		$ADMIN->Html .= $SKIN->add_td_row( array( "<b>Highest Amount?</b><br>The highest random amount that can be given." ,
								  $SKIN->form_input( "extra_two", $EXTRA['extra_two']  )
					 )      );


		return $ADMIN->Html;
	}
	function on_add_edits($admin) {}
	function on_add_extra(){}

	
		 function on_buy(){}

	function on_use() {}

	function run_job(){}

	function do_on_use($random_min,$random_max,$blank="") {
		global $ibforums,$DB,$print,$lib;
		$random_min = (int) $random_min;
		$random_max = (int) $random_max;
		$random = mt_rand($random_min,$random_max);
		$DB->query("UPDATE ibf_members SET points=points+$random WHERE id='{$ibforums->member['id']}' LIMIT 1");
		$lib->write_log("Got {$random} {$ibforums->vars['currency_name']}","item");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("You Gained, {$random} {$ibforums->vars['currency_name']}.",'act=store&code=inventory','1');		
		return "";
	}
}
?>



