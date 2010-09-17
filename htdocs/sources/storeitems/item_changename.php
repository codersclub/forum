<?
//---------------------------------------------------
// IBStore Change Username
//---------------------------------------------------
class item {
	var $name = "Изменить логин";
	var $desc = "Изменение логина пользователя";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";

	// This is triggered when a Admin adds a item to the store
	// after they have selected a name a icon and a description and stock
	function on_add($EXTRA) {}
	// As soon as a user has hit "Buy Item" this is triggered
	function on_add_edits($admin){}
	function on_add_extra(){}


	// We trigger this function as soon as "Use Item" is clicked.
	// this is were you would put the things a user could change (Like a members title as a e.g)
		 function on_buy(){}

	function on_use($itemid="") {
		global $ibforums,$DB;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Изменить логин</td>
			</tr> 
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Ваш новый логин:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='username' size='32'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='3'><input type='submit' name='change' value='Применить'></td>
			   </tr>
			</form>
EOF;
	}
	// This will take all the input from on_use (If any)
	// and run the querys or change some thing...Which probly would be in a query
	function run_job(){}

function do_on_use($blank="",$blank="",$blank="") {
		global $ibforums,$DB,$print,$lib;

		$username = trim( str_replace( '|'	, '&#124;' 	, $ibforums->input['username'] ));
		$username = preg_replace( "/\s{2,}/"	, " "		, $username );

		$DB->query("SELECT name FROM ibf_members WHERE 
			    LOWER(name)='".addslashes(strtolower($username))."' and 
			    id!='".$ibforums->member['id']."' LIMIT 1");

		if( $DB->get_num_rows() > 0 ) 
		{
			$lib->itemerror("Извините, такое имя уже использовано.");
		}

		if ( $ibforums->vars['ban_names'] ) 
		{
			$names = explode("|", $ibforums->vars['ban_names']);
			foreach ($names as $name) 
			{
				if ( $name == "" ) continue;

				if ( preg_match("/".preg_quote($name, '/' )."/i", $username) ) 
				{
					$lib->itemerror("Вы не можете использовать такое имя.");
				}
			}
		}	

		$len_u = $username;
		$len_u = preg_replace("/&#([0-9]+);/", "-", $len_u );

		if (empty($username))
		{
			$lib->itemerror("Вы не можете использовать такое имя.");
		}

		if (strlen($len_u) < 3)
		{
			$lib->itemerror("Вы не можете использовать такое имя.");
		}

		if (strlen($len_u) > 32) 
		{
			$lib->itemerror("Вы не можете использовать такое имя.");
		}
		
		if ( preg_match("#[a-z]+#i", $username) && preg_match("#[а-я]+#i", $username) ) 
		{
			$lib->itemerror("Вы не можете использовать такое имя.");
		}

		$DB->query("UPDATE ibf_members SET name='".addslashes($username)."' WHERE id='".$ibforums->member['id']."'");
		$lib->delete_item($ibforums->input['itemid']);

		$lib->write_log($ibforums->member['id'],
				$ibforums->member['name'],
				$ibforums->member['id'],
				$ibforums->member['name'],
				0,
				"'{$ibforums->member['name']}' изменил свой ник на '{$username}'",
				"",
				"item");

		$lib->redirect("","act=store","1");
		return "";
	}
}




