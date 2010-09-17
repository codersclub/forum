<?
//---------------------------------------------------
// IBStore Pin Unpin Own Topic
//---------------------------------------------------
class item {
	var $name = "Pin/Unpin Own Topic.";
	var $desc = "Pin/Unpin any topic you started.";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "";
	function on_add($EXTRA) {}
	function on_add_edits($admin) {}
	function on_add_extra(){}

	
		 function on_buy(){}

	function on_use($itemid="") {
		global $ibforums,$DB,$lib;
		return <<<EOF
			 <form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Прикрепить <b>свою</b> тему</td>
			</tr> 
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Укажите в поле справа ссылку на нужную тему(в ссылке должно прослеживаться showtopic=id_топика), <b>автором которой Вы являетесь</b>:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='url'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Изменить состояние темы'></td>
			   </tr>
			</form>
			
EOF;
	}

	function run_job(){}

	function do_on_use($type,$overwrite,$blank) {
		global $ibforums,$DB,$print,$lib;
		// You know first i was going to make this use parse_url then i decided to make it do some thing else if parse URL failed
		// Then i came to my sense's and did it the lazy way :D
		//http://www.subzerofx.com/shop/index.php?showtopic=286
		if(!$ibforums->input['url']) {
			$lib->itemerror("Вы не указали ссылку на тему, которую хотите прикрепить");
		}
		// First remove the board URL and index.php? from the string
		$ibforums->input['url'] = str_replace($ibforums->vars['board_url'],"",$ibforums->input['url']);
		$ibforums->input['url'] = str_replace("index.".$ibforums->vars['php_ext']."?","",$ibforums->input['url']);
		// Assuming showtopic is the as the URL shown above looks like it will work, else we will search through the array
		$num = explode("&",$ibforums->input['url']);
		// Modding tip #1: Never think somebodys going to do some thing right
		if(!preg_match("#showtopic#is",$num['0'])) {
			foreach($num as $var) {
				if(preg_match("#showtopic#is",$var)) {
					$topic = $var;
					break;
				}
			}
		} else $topic = $num['0'];
		
		$topic_num = str_replace("showtopic=","",$topic);
		$topic_num = str_replace("/","",$topic_num);
		$DB->query("SELECT tid,title,starter_id,pinned FROM ibf_topics WHERE tid='{$topic_num}' AND starter_id='{$ibforums->member['id']}' LIMIT 1");
		if($DB->get_num_rows() <= 0) {
			$lib->itemerror("Топик, ссылка на который Вы привели, не найден. Возможно, это неправильная ссылка, или Вы не являетесь автором данного топика.");
		}
		$topic = $DB->fetch_row();
		if($topic['pinned']) {
			$state = 0;
			$pin = "Un-Pinned";
		} else { 
			$state = 1;
			$pin = "Pinned";
		}
		$DB->query("UPDATE ibf_topics SET pinned='{$state}' WHERE tid='{$topic['tid']}' LIMIT 1");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->write_log($ibforums->member['id'],
				$ibforums->member['name'],
				$ibforums->member['id'],
				$ibforums->member['name'],
				0,
				"Использован товар: 'Перевести тему \"{$topic['title']}\" в разряд {$pin}'!",
				"",
				"item");
		$lib->redirect("Topic \"{$topic['title']}\" {$pin}!","showtopic={$topic['tid']}","1");
		return "";
	}
}
?>
