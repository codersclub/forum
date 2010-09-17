<?php
class skin_store {
function menu($links) {
global $ibforums;
return <<<EOF
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
 <td id="ucpmenu"  valign="top">
    <div class="maintitle">{$ibforums->lang['menu']}</div>
	{$links['points']}
	 <div class="pformstrip">{$ibforums->lang['userlinkes']}</div>
	 <p>
		 <a href="{$ibforums->base_url}act=store&code=inventory"><b>{$ibforums->lang['myinventory']}</b></a><br />
		 <a href="{$ibforums->base_url}act=store&code=bank"><b>{$ibforums->lang['goto_bank']}</b></a><br />
		 <a href="{$ibforums->base_url}act=store&code=donate_money"><b>{$ibforums->lang['donate_money']}</b></a><br />
		 <a href="{$ibforums->base_url}act=store&code=donate_item"><b>{$ibforums->lang['donate_item']}</b></a>
	 </p>
	 <div class="pformstrip">{$ibforums->lang['storecategorys']}</div>
	 <p>
		 {$links['shop']}
	 </p>
	 <div class="pformstrip">{$ibforums->lang['menu_stats']}</div>
	 <p>
		 {$links['stat']}
		 <a href="{$ibforums->base_url}act=store&code=misc_stats"><b>{$ibforums->lang['misc_stats']}</b></a>
	 </p>
	 <div class="pformstrip">{$ibforums->lang['misclink']}</div>
	 <p>
		 <a href="{$ibforums->base_url}act=store&code=quiz"><b>{$ibforums->lang['quiz']}</b></a><br />
		 <a href="{$ibforums->base_url}act=store&code=post_info"><b>{$ibforums->lang['post_info']}</b></a>
	 </p>
	 <!-- Staff URLS --> 
	 <div class="pformstrip">{$ibforums->lang['staff_links']}</div>
	 <p>
		 <b><a href='{$ibforums->base_url}act=store&code=fine'>{$ibforums->lang['fine']}</a></b><br />
		 <b><a href='{$ibforums->base_url}act=store&code=edit_points'>{$ibforums->lang['edit_points']}</a></b><br />
		 <b><a href='{$ibforums->base_url}act=store&code=edit_inventory'>{$ibforums->lang['edit_inventory']}</a></b><br />
	 </p>
	 <!-- End Staff Urls -->
</td>
<td style="padding:2px"><!-- --></td>
 </td>
 <td id="ucpcontent" valign="top">
  <div class="maintitle">{$ibforums->vars['store_name']}</div>
	<table width="100%" border="0" cellspacing="1" cellpadding="4">

EOF;
}
function convert_points($member) {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}act=store&code=doconvertpoint" method="post">
<tr>
	<td class="pformleft"><b>{$ibforums->lang['your_points']} {$member['points']}</b></td>	
	<td class="pformleft"><b>{$ibforums->lang['your_money']} {$member['money']}</b></td>	
</tr>
<tr>
	<td class="pformleft" colspan="2"><input type="text" name="convert_points" value=""> <input type="submit" name="convertpoints" value="{$ibforums->lang['ibstore_to_rpg']}"></td>	
</tr>
<tr>
	<td class="pformleft" colspan="2"><input type="text" name="convert_money" value=""> <input type="submit" name="convertmoney" value="{$ibforums->lang['rpg_to_ibstore']}"></td>	
</tr>
<tr>
	<td class="pformleft" align="center" colspan="8">&nbsp;</td>	
</tr>
</form>
EOF;
}
function cannot_finditems() {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformleft" align="center" colspan="8"><b>{$ibforums->lang['cannot_find_items']}</b></td>	
</tr>
<tr>
	<td class="pformleft" align="center" colspan="8">&nbsp;</td>	
</tr>
EOF;
}
function next_lastlinks($info) {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformstrip" align="left" colspan="2"><a href="{$ibforums->base_url}act=store&code=shop{$info['category']}&page={$info['last']}">{$ibforums->lang['last']}</a></td>	
	<td class="pformstrip" align="center" colspan="2">{$ibforums->lang['showingitems']}</td>	
	<td class="pformstrip" align="right" colspan="4"><a href="{$ibforums->base_url}act=store&code=shop{$info['category']}&page={$info['next']}">{$ibforums->lang['next']}</a></td>	
</tr>
EOF;
}
function member_points() {
global $ibforums;
return <<<EOF
		<div class="row4" align="center"><b>{$ibforums->lang['yourpoints']} {$ibforums->vars['currency_name']}: {$ibforums->member['points']}</b></div>
EOF;
}
function plays_left_header() {
global $ibforums;
return <<<EOF
	<td class="pformstrip" width="10%">{$ibforums->lang['plays_left']}</td>	
EOF;
}

function plays_left_middle($plays) {
global $ibforums;
return <<<EOF
	<td class="row2" width="10%">{$plays}</td>
EOF;
}
function post_info() {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['points_pertopic']}</td>
	<td class="pformleft" width="25%">{$ibforums->vars['pointsper_topic']}</td>
 </tr>
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['points_perreply']}</td>
	<td class="pformleft" width="25%">{$ibforums->vars['pointsper_reply']}</td>
 </tr>
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['pointsper_poll']}</td>
	<td class="pformleft" width="25%">{$ibforums->vars['pointsper_poll']}</td>
 </tr> 
EOF;
}
function make_url($address,$text,$prefix="<b>",$suffix="</b><br />") {
global $ibforums;
return <<<EOF
{$prefix}<a href='{$ibforums->base_url}{$address}'>{$text}</a>{$suffix}
EOF;
}
function misc_stats($stats) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" width="100%" colspan="2">{$ibforums->lang['miscstats_info']}</td>
  </tr>
 <tr>
	<td class="pformleft" width="25%">{$ibforums->lang['misc_totalpoints']}</td>
	<td class="pformleft" width="25%">{$stats['money']}</td>
 </tr>
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['misc_totalbank']}</td>
	<td class="pformleft" width="25%">{$stats['bank']}</td>
 </tr>
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['misc_total']}</td>
	<td class="pformleft" width="25%">{$stats['total']}</td>
 </tr>
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['misc_totalworth']}</td>
	<td class="pformleft" width="25%">{$stats['item']}</td>
 </tr>
<tr>
	<td class="pformleft" width="25%">{$ibforums->lang['misc_totalstock']}</td>
	<td class="pformleft" width="25%">{$stats['stock']}</td>
 </tr>

EOF;
}
function header_stats($name) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" width="100%" colspan="2">{$name}</td>
  </tr>
  <tr>
	<td class="pformstrip" width="50%" colspan="1">{$ibforums->lang['member_name']}</td>
	<td class="pformstrip" width="50%" colspan="1">{$ibforums->lang['member_points']}</td>
  </tr>

EOF;
}
function output_stats($member) {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformleft" width="50%" colspan="1"><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></td>
	<td class="pformleft" width="50%" colspan="1">{$member['points']}</td>
 </tr>
EOF;
}

function quiz_results_header() {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" width="25%">{$ibforums->lang['results_place']}</td>
	<td class="pformstrip" width="25%">{$ibforums->lang['results_name']}</td>
	<td class="pformstrip" width="25%">{$ibforums->lang['results_right']}</td>
	<td class="pformstrip" width="25%">{$ibforums->lang['results_timetook']}</td>
  </tr>

EOF;
}
function quiz_results_results($member,$place) {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformleft" width="25%">{$place}</td>
	<td class="pformleft" width="25%"><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></td>
	<td class="pformleft" width="25%">{$member['amount_right']}</td>
	<td class="pformleft" width="25%">{$member['time_took']}</td>
 </tr>
EOF;
}
function output_stats_end() {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformleft" width="100%" colspan="4">&nbsp;</td>
</tr>	
EOF;
}


function overall_stats_header() {
global $ibforums;

return <<<EOF
  <tr>
	<td class="pformstrip" width="100%" colspan="4">{$ibforums->lang['stats_global']}</td>
  </tr>
  <tr>
	<td class="pformstrip" width="25%" colspan="1">{$ibforums->lang['member_name']}</td>
	<td class="pformstrip" width="25%" colspan="1">{$ibforums->lang['member_points']}</td>
	<td class="pformstrip" width="25%" colspan="1">{$ibforums->lang['member_deposited']}</td>
	<td class="pformstrip" width="25%" colspan="1">{$ibforums->lang['total_points']}</td>
  </tr>

EOF;
}
function output_overall_stats($member) {
global $ibforums;
return <<<EOF
<tr>
	<td class="pformleft" width="25%" colspan="1"><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></td>
	<td class="pformleft" width="25%" colspan="1">{$member['points']}</td>
	<td class="pformleft" width="25%" colspan="1">{$member['deposited']}</td>
	<td class="pformleft" width="25%" colspan="1">{$member['total_points']}</td>	

 </tr>
EOF;
}
function quiz_header() {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" width="10%">{$ibforums->lang['quiz_name']}</td>
	<td class="pformstrip" width="20%">{$ibforums->lang['quiz_desc']}</td>
	<td class="pformstrip" width="5%">{$ibforums->lang['quiz_winnings']}</td>
	<td class="pformstrip" width="5%">{$ibforums->lang['quiz_stats']}</td>
	<!--Plays Left Header-->
	<td class="pformstrip" width="5%">{$ibforums->lang['quiz_status']}</td>	
	<td class="pformstrip" width="10%">{$ibforums->lang['quiz_play']}</td>
  </tr>

EOF;
}
function list_quiz($quiz) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row2" width="10%">{$quiz['quizname']}</td>
	<td class="row2" width="20%">{$quiz['quizdesc']}</td>
	<td class="row2" width="5%">{$quiz['amount_won']}</td>
	<td class="row2" width="5%" >{$quiz['quiz_status']}</td>
	<!--Plays Left Middle-->	
	<td class="row2" width="5%">{$quiz['status_days']} {$ibforums->lang['quiz_days']}</td>
	<td class="row2" width="10%">{$quiz['take_quiz']}</td>
   </tr>
EOF;
}
function quiz_q_a_header($settings) {
global $ibforums;
return <<<EOF
<script language='Javascript' type='text/javascript'>
<!--
	if(document.quiz.timeout.value != 0) {
		int timeout = document.quiz.timeout.value;
		setTimeout('Dotimeout()',timeout * 60000);
	}
	function Dotimeout() {
		document.quiz.take_quiz.disabled = true;
		document.quiz.submit();
	}
//-->
</script>
<form action="{$ibforums->base_url}act=store&code=do_take_quiz&quizid={$ibforums->input[quiz_id]}" name="quiz" method="post">
  <tr>
	<td class="pformstrip" width="10%">{$ibforums->lang['quiz_question']}</td>
	<td class="pformstrip" width="20%">{$ibforums->lang['quiz_answer']}
										<input type="hidden" name="timeout" value="{$settings['timeout']}">
										<input type="hidden" name="starttime" value="{$settings['time']}"></td>
  </tr>

EOF;
}
function single_question($info) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row2" width="50%"><b>{$info['question']}</b></td>
	<td class="row2" width="50%"><input type="text" name="uanswer_{$info['mid']}" value=""></td>
   </tr>
EOF;
}
function dropdown_question($info) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row2" width="50%"><b>{$info['question']}</b></td>
	<td class="row2" width="50%">{$info['dropdown']}</td>
   </tr>
EOF;
}

function quiz_q_a_submit() {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row2" width="100%" colspan="2"><input type="submit" name="take_quiz" value="{$ibforums->lang[quiz_qa_submit]}"></td>
   </tr>
   </form>
EOF;
}


function bank($info,$collect_submit) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" width="100%" colspan="4">{$ibforums->lang['welcome_bank']}</td>
</tr> 
 <tr>
	<td class="pformleft" width="50%" colspan="2">{$ibforums->lang['amount_in']}</td>
	<td class="pformleft" width="50%" colspan="2">{$ibforums->lang['amount_onhand']}</td>
 </tr>
  <tr>
	<td class="pformleft" width="30%" colspan="2">{$ibforums->lang['deposit']}</td>
	<td class="pformleft" width="30%" colspan="1"><form action="{$ibforums->base_url}act=store&code=dobank&type=deposit" name="bank" method="post">
												  <input type="text" name="deposit_amount"></td>
	<td class="pformleft" width="40%" colspan="1"><input type="submit" name="deposit" value="{$ibforums->lang['deposit']}">
												  </form></td>
 </tr>
  <tr>
	<td class="pformleft" width="30%" colspan="2">{$ibforums->lang['withdraw']}</td>
	<td class="pformleft" width="30%" colspan="1"><form action="{$ibforums->base_url}act=store&code=dobank&type=withdraw" name="bank" method="post">
												  <input type="text" name="withdraw_amount"></td>
	<td class="pformleft" width="40%" colspan="1"><input type="submit" name="withdraw" value="{$ibforums->lang['withdraw']}">
												  </form></td>
 </tr>
  <tr>
	<td class="pformleft" width="30%" colspan="1">{$ibforums->lang['interest']}</td>
	<td class="pformleft" width="30%" colspan="1">{$ibforums->lang['yougetinterest']}</td>
	<td class="pformleft" width="40%" colspan="2"><form action="{$ibforums->base_url}act=store&code=dobank&type=collect" name="bank" method="post">
												  <input type="submit" name="collect" value="{$collect_submit}" {$info['disabled']}>
												  </form></td>
   </tr>
   <tr>
	<td class="pformright" colspan="4">&nbsp;</td>
   </tr>  
EOF;
}


function donatemoney($disable) {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}act=store&code=dodonate_money" method="post" onSubmit="return check('{$ibforums->lang['donate_check']}')">
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['donate_to']}</td>
	<td class="pformleft" width="20%"><input type="text" name="username" value="{$ibforums->input['name']}"></td>
   </tr>
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['donate_amount']}</td>
	<td class="pformleft" width="20%"><input type="text" name="amount"></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['message_to']}</td>
	<td class="pformleft" width="20%"><textarea name="message" width="30" height="10" cols="40" rows="5"></textarea></td>
  <tr>
	<td class="pformleft" width="10%" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['submit']}" $disable></center></td>
   </tr>
  </tr>
</form>
EOF;
}

function donateitem($options,$disable="") {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}act=store&code=donate_item" method="post" onSubmit="return check('{$ibforums->lang['donate_check']}')">
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['donate_itemto']}</td>
	<td class="pformleft" width="20%"><input type="text" name="username" value="{$ibforums->input['name']}"></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['donate_senditemto']}</td>
	<td class="pformleft" width="20%"><select name="item">{$options}</select>
</td>
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['message_to']}</td>
	<td class="pformleft" width="20%"><textarea name="message" width="30" height="10" cols="40" rows="5"></textarea></td>
  <tr>
	<td class="pformleft" width="10%" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['submit']}" $disable></center></td>
   </tr>
  </tr>
</form>
EOF;
}
function mass_buy_header() {
global $ibforums;
return <<<EOF
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['mass_buy']}</td>	
EOF;
}

function mass_buy_middle($mass_buy) {
global $ibforums;
return <<<EOF
	<td class="row4" align="center">{$mass_buy}</td>
EOF;
}
function item_info() {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['icon']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['item_name']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['item_desc']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['item_price']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['item_stock']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['item_buyitem']}</td>
	<!--Mass Buy Header-->
  </tr>
EOF;
}

function list_items($item) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row4" align="center"><img src="./html/store/icons/{$item['icon']}"></td>
	<td class="row4" align="center">{$item['item_name']}</td>
	<td class="row4" align="center">{$item['item_desc']}</td>
	<td class="row4" align="center">{$item['sell_price']}</td>	
	<td class="row4" align="center">{$item['stock']}</td>
	<td class="row4" align="center">{$item['item_buyitem']}</td>
	<!--Mass Buy Middle-->
  </tr>
EOF;
}


function show_middle($info) {
global $ibforums;
return <<<EOF
 	<tr>
		<td class="pformstrip" align="center" colspan="2">{$info['welcome_line']}</td>
	</tr>
 	<tr>
		<td class="pformright" align="center" colspan="2"><b>{$info['welcome_desc']}</b></td>
	</tr> 	
EOF;
}
function category_header() {
global $ibforums;
return <<<EOF
 	<tr>
		<td class="pformstrip" align="center" colspan="1">{$ibforums->lang['cat_name']}</td>
		<td class="pformstrip" align="center" colspan="1">{$ibforums->lang['cat_desc']}</td>
	</tr>
EOF;
}
function category($categorys) {
global $ibforums;
return <<<EOF
 	<tr>
		<td class="row2" align="center" colspan="1">{$categorys['cat_name']}</td>
		<td class="row2" align="center" colspan="1">{$categorys['cat_desc']}</td>
	</tr>
EOF;
}

function error() {
global $ibforums;
return <<<EOF
<div class="tableborder">

  <div class="maintitle">{$ibforums->lang['problem']}</div>
 <table width="100%" border="0" cellspacing="1" cellpadding="4">
  <tr>
	<td class="row4" align="center">{$ibforums->lang['error']}</td>
  </tr>
EOF;
}

function error_row($message) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformstrip" align="center">{$message}</td>
  </tr>
EOF;
}
function check() {
global $ibforums;
return <<<EOF
<script language='Javascript' type='text/javascript'>
<!--
	function check(type) {
		<!--IBS.SAFTY_ON-->
		return true;
	}
//-->
</script>
EOF;
}
function inventory_stats($stats) {
global $ibforums;
return <<<EOF
<!--IBS.CHECK-->
	<tr>
		<td class="pformleft" width="50%" colspan="3">{$ibforums->lang['totalmoney']}</td>
		<td class="pformleft" width="50%" colspan="3">{$ibforums->member['points']} {$ibforums->vars['currency_name']}</td>
	</tr>
	<tr>
		<td class="pformleft" width="50%" colspan="3">{$ibforums->lang['totalmarket']}</td>
		<td class="pformleft" width="50%" colspan="3">{$stats['total_value']} {$ibforums->vars['currency_name']}</td>
	</tr>
 <tr>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['icon']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_name']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_desc']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['amount_owned']}</td>
	<!--IBS.RESELL_LANG-->
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['inventory_do']}</td>
  </tr>

EOF;
}

function inventory_middle($user_inventory) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row4" align="center" width="10%"><img src="./html/store/icons/{$user_inventory['icon']}"></td>
	<td class="row4" align="center" width="20%">{$user_inventory['item_name']}</td>
	<td class="row4" align="center" width="20%">{$user_inventory['item_desc']}</td>	
	<td class="row4" align="center" width="10%">{$user_inventory['stock']}</td>
	<!--IBS.RESELL_AMOUNT-->
	<td class="row4" align="center" width="10%"><a href="{$ibforums->base_url}act=store&code=useitem&itemid={$user_inventory['i_id']}" onClick="return check('{$ibforums->lang['useitem_check']}')">{$ibforums->lang['use']}</a><br />
 	<!--IBS.RESELL_ITEM-->	
	<!--IBS.DELETE_ITEM-->
	</td>
 </tr>
EOF;
}
function view_inventory_stats($stats,$member) {
global $ibforums;
return <<<EOF
	<tr>
		<td class="pformleft" width="50%" colspan="3">{$ibforums->lang['viewinginventory']}</td>
		<td class="pformleft" width="50%" colspan="3"><a href='{$ibforums->base_url}showuser={$member['id']}'>{$member['name']}</a></td>
	</tr>
	<tr>
		<td class="pformleft" width="50%" colspan="3">{$ibforums->lang['totalmarket']}</td>
		<td class="pformleft" width="50%" colspan="3">{$stats['total_value']} {$ibforums->vars['currency_name']}</td>
	</tr>
 <tr>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['icon']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_name']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_desc']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['amount_owned']}</td>
	<!--IBS.RESELL_LANG-->
  </tr>

EOF;
}
function noinventory() {
global $ibforums;
return <<<EOF
	<tr>
		<td class="pformleft" width="100%" colspan="6" align="center">{$ibforums->lang['noinventory']}</td>
	</tr>
	<tr>
		<td class="pformleft" width="100%" colspan="6">&nbsp;</td>
	</tr>	
EOF;
}
function view_inventory_middle($user_inventory) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row4" align="center" width="10%"><img src="./html/store/icons/{$user_inventory['icon']}"></td>
	<td class="row4" align="center" width="20%">{$user_inventory['item_name']}</td>
	<td class="row4" align="center" width="20%">{$user_inventory['item_desc']}</td>	
	<td class="row4" align="center" width="10%">{$user_inventory['stock']}</td>
	<!--IBS.RESELL_AMOUNT-->
	</td>
 </tr>
EOF;
}
function fine_users() {
global $ibforums;
return <<<EOF
<script language='Javascript' type='text/javascript'>
<!--
	function check_fine(message) {
		if(document.fine.username.value == "" || document.fine.fine_amount.value == "" || document.fine.reson.value == "") {
			alert(message);
			return false;
		}
		return true;
	}
//-->
</script>
<form action="{$ibforums->base_url}act=store&code=do_fine_users" name="fine" method="post" onSubmit="return check_fine('{$ibforums->lang['didnotfilloutfields']}')">
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_user']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="username"></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_amount']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="fine_amount">
</td>
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_reson']}</b></td>
	<td class="pformleft" width="20%"><textarea name="user_reson" width="30" height="10" cols="40" rows="5"></textarea></td>
  <tr>
	<td class="pformleft" width="10%" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['fine_member']}"></center></td>
   </tr>
  </tr>
</form>
EOF;
}
function edit_users_points() {
global $ibforums;
return <<<EOF
<script language='Javascript' type='text/javascript'>
<!--
	function check_edit(message) {
		if(document.edit.username.value == "") {
			alert(message);
			return false;
		}
		return true;
	}
//-->
</script>
<form action="{$ibforums->base_url}act=store&code=do_edit_points" name="edit" method="post" onSubmit="return check_edit('{$ibforums->lang['didnotfilloutfields']}')">
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_user']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="username"></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['edit_member']}"></center></td>
</form>
EOF;
}

function do_edit_users_points($member) {
global $ibforums;
return <<<EOF
<script language='Javascript' type='text/javascript'>
<!--
	function check_edit(message) {
		if(document.edit.points.value == "") {
			alert(message);
			return false;
		}
		return true;
	}
//-->
</script>
<form action="{$ibforums->base_url}act=store&code=do_do_edit_points" name="edit" method="post" onSubmit="return check_edit('{$ibforums->lang['didnotfilloutfields']}')">
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_user']}</b></td>
	<td class="pformleft" width="20%"><input type="hidden" name="username" value='{$member['name']}'>{$member['name']}</td>
  </tr>
   <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['edit_userspoints']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="points" value='{$member['points']}'></td>
  </tr>
  <tr>
 	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_reson']}</b></td>
	<td class="pformleft" width="20%"><textarea name="user_reson" width="30" height="10" cols="40" rows="5"></textarea></td>
  </tr>
  <tr> 
	<td class="pformleft" width="10%" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['edit_member']}"></center></td>
  </tr>
</form>
EOF;
}
function edit_users_inventory() {
global $ibforums;
return <<<EOF
<script language='Javascript' type='text/javascript'>
<!--
	function check_edit(message) {
		if(document.edit.username.value == "") {
			alert(message);
			return false;
		}
		return true;
	}
//-->
</script>
<form action="{$ibforums->base_url}act=store&code=do_staff_inventory" name="edit" method="post" onSubmit="return check_edit('{$ibforums->lang['didnotfilloutfields']}')">
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_user']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="username"></td>
 </tr>
  <tr>
 	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_reson']}</b></td>
	<td class="pformleft" width="20%"><textarea name="reson" width="30" height="10" cols="40" rows="5"></textarea></td>
  </tr> 
  <tr>
	<td class="pformleft" width="10%" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['edit_member']}"></center></td>
	</tr>
</form>
EOF;
}
function show_users_inventory_header($user) {
global $ibforums;
return <<<EOF
<form action="{$ibforums->base_url}act=store&code=do_do_staff_inventory" name="edit" method="post">
 <tr>

	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['icon']}<input type='hidden' name='userid' value='{$user['id']}'></td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_name']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_desc']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['edit_price']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['edit_delete']}</td>
  </tr>
EOF;
}

function show_users_inventory($inventory) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="row4" align="center" width="10%"><img src="./html/store/icons/{$inventory['icon']}"></td>
	<td class="row4" align="center" width="20%">{$inventory['item_name']}</td>
	<td class="row4" align="center" width="20%">{$inventory['item_desc']}</td>	
	<td class="row4" align="center" width="10%"><input type='text' name='price_{$inventory['i_id']}' value='{$inventory['price_payed']}'>
												<input type='hidden' name='original_{$inventory['i_id']}' value='{$inventory['price_payed']}'></td>
	<td class="row4" align="center" width="10%"><input type='checkbox' name='delete_{$inventory['i_id']}' value='1' unchecked></td>
	</td>
 </tr>
EOF;
}
function edit_inventory_submit() {
global $ibforums;
return <<<EOF
  <tr> 
	<td class="pformleft" width="100%" colspan="6"><center><input type="submit" name="submit" value="{$ibforums->lang['edit_inventory_submit']}"></center></td>
  </tr>
</form>
EOF;
}
function useitem($code) {
global $ibforums;
return <<<EOF
	{$code}
EOF;
}
function end_page() {
global $ibforums;
return <<<EOF
</td>
</table>
</table>
</div>
<!--IBS.Copyright-->
EOF;
}

}
?>
