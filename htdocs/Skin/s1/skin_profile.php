<?php

class skin_profile {



function show_forum_stat($info) {
global $ibforums;

// NEED TO require LANG FILE !

$TotalThematic = 0;
$TotalFlame = 0;

$html = "<div class=tableborder>
<div class=maintitle>
<b>{$ibforums->lang['active_stats']} <a style='text-decoration:underline' href='index.php?showuser={$info[member_id]}'>{$info[member_name]}</a> {$ibforums->lang['by_forums']}</b></td>
</div>
<table class='tablebasic' align=center width='100%' cellpadding='4' cellspacing='1'>
<tr class='titlemedium' align=center>
  <td><b>Forum</b></td>
  <td width='10%'><b>Posts</b></td>
  <td width='10%'><b>Percent</b></td>
  <td width='25%'><b>Status</b></td>
</tr>
";

//$html .= $info[member_id]." ".$info[member_name]." ".$info[total]."<br>\n";

$forums = $info['stat'];

foreach ( $forums as $row )
{

  if($row['STATUS']) {
    $TotalThematic += $row['COUNT'];
    $stat ='Thematic';
  } else {
    $TotalFlame += $row['COUNT'];
    $stat ='Flame';
  }

  $per = round(100*($row['COUNT']/$info['total']),2);

//  if ($per>10) $vydelit="forum1";
//  else $vydelit="forum2";

//  echo('<tr class='.$vydelit.' align=center><td><a target=_blank href=\'index.php?showforum='.$row['FID'].'\'>'.$row['FNAME'].'</a></td><td>'.$row['COUNT'].'</td><td>'.$per.'%</td><td>'.$row['STATUS'].'</td></tr>');

  $html .= "<tr align=center>
  <td class='row4' align=left><a target=_blank href='index.php?act=Select&CODE=getalluser&mid={$info[member_id]}&fid={$row['FID']}'>{$row['FNAME']}</a></td>
  <td class='row4'>{$row['COUNT']}</td>
  <td class='row4'>{$per}%</td>
  <td class='row4'>{$stat}</td>
</tr>";

}

$html .= "<tr class=plainborder align=center>
  <td class='row2'><b>Total</b></a></td>
  <td class='row2'><b>{$info['total']}</b></td>
  <td class='row2'><b>100%</b></td>
  <td class='row2'>&nbsp;</td>
</tr>
";

$per = 0;
if($info['total']) $per = round(100*($TotalThematic/$info['total']),2);


$html .= "<tr align=center>
  <td class='row4'>{$ibforums->lang['total_posts']}</a></td>
  <td class='row4'>$TotalThematic</td>
  <td class='row4'>{$per}%</td>
  <td class='row4'><b>Thematic</b></td>
</tr>
";

$per = 100 - $per;

if(!$TotalFlame) $per = 0;

$html .= "<tr align=center>
  <td class='row4'>{$ibforums->lang['total_flame']}</a></td>
  <td class='row4'>$TotalFlame</td>
  <td class='row4'>{$per}%</td>
  <td class='row4'>Flame</td>
</tr>
";

if ( $ibforums->member['g_is_supmod'] ||
     ($info[member_id] == $ibforums->member['id'])) 
{
$html .= "
<form action='{$ibforums->base_url}act=Profile&amp;CODE=save_stat&amp;MID={$info[member_id]}' name='sForm' method='POST'>
<TR>
  <TD class=row3 align=center colspan=4>
  <input type='submit' value='{$ibforums->lang['save_stat']}' class='forminput'>
  </td>
</TR>
</form>
";
}

//<BR>{$ibforums->member['g_is_supmod']} = {$ibforums->member['id']} = {$info[member_id]}

$html .= "<TR>
  <TD class=tablefooter colspan=4><!-- -->
  </td>
</TR>
</table>
</div>
";


  return $html;

}










function warn_level_rating_no_mod($mid, $level,$min=0,$max=10) {
global $ibforums;
return <<<EOF

 <tr>
	<td class="row3" valign='top'><b>{$ibforums->lang['rating_level']}</b></td>
	<td align='left' class='row1'>&lt;&nbsp;$min ( <a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$mid}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$level}</a> ) $max&nbsp;&gt;</td>
  </tr>

EOF;
}

function show_rep($info) {
global $ibforums;
return <<<EOF

  <tr>
    <td class="row3" valign='top'><b>{$ibforums->lang['rep_t_name']}:</b></td>
    <td align='left' class='row1'>{$info['rep']} <a href='{$ibforums->vars['board_url']}/index.php?act=rep&CODE=03&type=t&mid={$info['mid']}'>{$ibforums->lang['rep_details']}</a></td>
  </tr>

EOF;
}

function show_ratting($info) {
global $ibforums;
return <<<EOF

  <tr>
    <td class="row3" valign='top'><b>{$ibforums->lang['rep_f_name']}:</b></td>
    <td align='left' class='row1'>{$info['ratting']} <a href='{$ibforums->vars['board_url']}/index.php?act=rep&CODE=03&type=f&mid={$info['mid']}'>{$ibforums->lang['rep_details']}</a></td>
  </tr>

EOF;
}

/* <--- Jureth --- Show DigiMoney in Profile */ 
function show_fines($info){
global $ibforums;
return <<<EOF

  <tr>
    <td class="row3" valign='top'><b>{$ibforums->lang['fine']}:</b></td>
    <td align='left' class='row1'>{$info['fines']} {$ibforums->vars['currency_name']} <a href='{$ibforums->vars['board_url']}/index.php?act=store&code=showfine&id={$info['mid']}'>{$ibforums->lang['rep_details']}</a></td>
  </tr>
EOF;
}
/* >--- Jureth --- */
 

function show_profile($info) {
global $ibforums;	  
if ($info['mod_forums']) $mod_forums = "	  <tr>
		<td class=\"row3\" valign='top'><b>{$ibforums->lang['mod_forums']}</b></td>
		<td align='left' class='row1'>{$info['mod_forums']}</td>
	  </tr>";	  
return <<<EOF

<script type='text/javascript' src='html/profile.js?{$ibforums->vars['client_script_version']}'></script>
<table class='tablebasic' cellspacing='0' cellpadding='2'>
<tr>
 <td>{$info['photo']}</td>
 <td width='100%' valign='bottom'>
   <div id='profilename'>{$info['name']}{$info['online']}</div>
   <div>
	 <a href='{$info['base_url']}act=Select&amp;CODE=getalluser&amp;mid={$info['mid']}'>{$ibforums->lang['find_posts']}</a> &middot; <a href='{$info['base_url']}act=Select&amp;CODE=getallusertopics&amp;mid={$info['mid']}'>{$ibforums->lang['find_topics']}</a> &middot;
	 <a href='{$info['base_url']}act=Msg&amp;CODE=02&amp;MID={$info['mid']}'>{$ibforums->lang['add_to_contact']}</a>
   </div>
 </td>
</tr>
</table>
<br>
<table cellpadding='0' align='center' cellspacing='2' border='0' width='100%'>
  <tr>
	<td width='50%' valign='top' class="plainborder">
	 <table cellspacing="1" cellpadding='6' width='100%'>
	  <tr>
		<td align='center' colspan='2' class='pformstrip'>{$ibforums->lang['active_stats']}</td>
	  </tr>
	  <tr>
		<td class="row3" width='30%' valign='top'><b>{$ibforums->lang['total_posts']}</b></td>
		<td align='left' width='70%' class='row1'><b>{$info['posts']}</b><br>( {$info['total_pct']}% {$ibforums->lang['total_percent']} )</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['posts_per_day']}</b></td>
		<td align='left' class='row1'><b>{$info['posts_day']}</b></td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['joined']}</b></td>
		<td align='left' class='row1'><b>{$info['joined']}</b></td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['fav_forum']}</b></td>
		<td align='left' class='row1'><a href='{$info['base_url']}act=SF&amp;f={$info['fav_id']}'>{$info['fav_forum']}</a><br>{$info['fav_posts']} {$ibforums->lang['fav_posts']}<br>( {$info['percent']}% {$ibforums->lang['fav_percent']} )</td>
	  </tr>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>Статистика сообщений пользователя</b></td>
		<td align='left' class='row1'><a href="{$ibforums->base_url}act=Profile&amp;CODE=show_stat&amp;MID={$info['mid']}">Показать</a></td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['last_activity']}</b></td>
		<td align='left' class='row1'>{$info['last_activity']}</td>
	  </tr>
	  </table>
	</td>
	
   <td width='50%' valign='top' class="plainborder">
	 <table cellspacing="1" cellpadding='6' width='100%'>
	  <tr>
		<td align='center' colspan='2' class='pformstrip'>{$ibforums->lang['communicate']}</td>
	  </tr>
	  <tr>
		<td class="row3" width='30%' valign='top'><b>{$ibforums->lang['email']}</b></td>
		<td align='left' width='70%' class='row1'>{$info['email']}</td>
	  </tr>
	   <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['integ_msg']}</b></td>
		<td align='left' class='row1'><img alt="Jabber" src="<#IMG_DIR#>/icon_jabber.png" valign="top"> {$info['integ_msg']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['aim']}</b></td>
		<td align='left' class='row1'><img alt="AOL" src="<#IMG_DIR#>/icon_aim.png" valign="top"> {$info['aim_name']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['icq']}</b></td>
		<td align='left' class='row1'>{$info['icq_status']} {$info['icq_number']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['yahoo']}</b></td>
		<td align='left' class='row1'><img alt="Skype" src="<#IMG_DIR#>/icon_skype.png" valign="top"> {$info['yahoo']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['msn']}</b></td>
		<td align='left' class='row1'><img alt="MSN" src="<#IMG_DIR#>/icon_msn.png" valign="top"> {$info['msn_name']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['pm']}</b></td>
		<td align='left' class='row1'><a href='{$info['base_url']}act=Msg&amp;CODE=4&amp;MID={$info['mid']}'>{$ibforums->lang['click_here']}</a></td>
	  </tr>
	  </table>
	</td>
	
  </tr>
  <tr>
	<td width='50%' valign='top' class="plainborder">
	 <table cellspacing="1" cellpadding='6' width='100%'>
	  <tr>
		<td align='center' colspan='2' class='pformstrip'>{$ibforums->lang['info']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['user_local_time']}</b></td>
		<td align='left' class='row1'>{$info['local_time']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['gender']}</b></td>
		<td align='left' class='row1'>{$info['gender']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['birthday']}</b></td>
		<td align='left' class='row1'>{$info['birthday']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['location']}</b></td>
		<td align='left' class='row1'>{$info['location']}</td>
	  </tr>
	  <tr>
		<td class="row3" width='30%' valign='top'><b>{$ibforums->lang['homepage']}</b></td>
		<td align='left' width='70%' class='row1'>{$info['homepage']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['interests']}</b></td>
		<td align='left' class='row1'>{$info['interests']}</td>
	  </tr>
	  <!--{CUSTOM.FIELDS}-->
	  </table>
	</td>
	
        <td width='50%' valign='top' class="plainborder">
	 <table cellspacing="1" cellpadding='6' width='100%'>
	  <tr>
		<td align='center' colspan='2' class='pformstrip'>{$ibforums->lang['post_detail']}</td>
	  </tr>
	  <tr>
		<td class="row3" width='30%' valign='top'><b>{$ibforums->lang['mgroup']}</b></td>
		<td align='left' width='70%'  class='row1'>{$info['group_title']}</td>
	  </tr>
	  {$mod_forums}
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['mtitle']}</b></td>
		<td align='left' class='row1'>{$info['member_title']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['avatar']}</b></td>
		<td align='left' class='row1'>{$info['avatar']}</td>
	  </tr>
	  <tr>
		<td class="row3" valign='top'><b>{$ibforums->lang['siggie']}</b></td>
		<td align='left' class='row1'>{$info['signature']}</td>
    
          {$info['rep']}
          {$info['ratting']}
	  {$info['fines']}

	  <!--{WARN_LEVEL}-->
	  </table>
	</td>
	</tr>
<tr>
  <td colspan=2 valign='top' class="plainborder">
 <div class='pformstrip' align='center'>&lt;( <a href='javascript:history.go(-1)'>назад</a> )</div>
  </td>
</tr>
</table>
	

EOF;
}





function custom_field($title, $value="") {
global $ibforums;
return <<<EOF

	<tr>
              <td class="row3" valign='top'><b>$title</b></td>
              <td align='left' class='row1'>$value</td>
        </tr>

EOF;
}


function warn_level_no_mod($mid, $img, $percent) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="row3" valign='top'><b>{$ibforums->lang['warn_level']}</b></td>
	<td align='left' class='row1'><a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$mid}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$percent}</a>%: {$img}</td>
  </tr>

EOF;
}


function show_photo($name, $photo) {
global $ibforums;
return <<<EOF

<div id="photowrap">
 <div id="phototitle">$name</div>
 <div id="photoimg">$photo</div>
</div>

EOF;
}


function show_card_download($name, $photo, $info) {
global $ibforums;
return <<<EOF

<html>
 <head>
  <title>$name</title>
  <style type="text/css">
	 form { display:inline; }
	 img  { vertical-align:middle }
	 BODY { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #000; margin-left:5%;margin-right:5%;margin-top:5px;  }
	 TABLE, TR, TD { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
	 a:link, a:visited, a:active { text-decoration: underline; color: #000 }
	 a:hover { color: #465584; text-decoration:underline }
	 #profilename { font-size:28px; font-weight:bold; }
	 #photowrap { padding:6px; }
	 #phototitle { font-size:24px; border-bottom:1px solid black }
	 #photoimg   { text-align:center; margin-top:15px } 
	 .plainborder { border:1px solid #345487;background-color:#F5F9FD }
	 .tableborder { border:1px solid #345487;background-color:#FFF }
	 .tablefill   { border:1px solid #345487;background-color:#F5F9FD;padding:6px }
	 .tablepad    { background-color:#F5F9FD;padding:6px }
	 .tablebasic  { width:100%; padding:0px 0px 0px 0px; margin:0px; border:0px }
	 .row1 { background-color: #F5F9FD }
	 .row2 { background-color: #DFE6EF }
	 .row3 { background-color: #EEF2F7 }
	 .row4 { background-color: #E4EAF2 }
  </style>
  <script type='text/javascript' src='html/profile.js?{$ibforums->vars['client_script_version']}'></script>
 </head>
<body>
<table width='100%' height='100%'>
<tr>
 <td valign='middle' align='center' width='400'>
	<div id="phototitle">$name</div>
	<br>
	<table class='tablebasic' cellspacing='6'>
	<tr>
	 <td valign='middle' class='row1'>$photo</td>
	 <td width='100%' class='row1' valign='bottom'>
	   <table class='tablebasic' cellpadding='5'>
		 <tr>
		   <td nowrap='nowrap'>{$ibforums->lang['email']}</td>
		   <td width="100%">{$info['email']}</td>
		 </tr>
		 <tr>
		  <td nowrap='nowrap'>{$ibforums->lang['integ_msg']}</td>
		  <td width="100%">{$info['integ_msg']}</td>
	     </tr>
		 <tr>
		   <td nowrap='nowrap'>{$ibforums->lang['aim']}</td>
		   <td width="100%">{$info['aim_name']}</td>
		 </tr>
		 <tr>
		   <td nowrap='nowrap'>{$ibforums->lang['icq']}</td>
		   <td width="100%">{$info['icq_number']}</td>
		 </tr>
		 <tr>
		   <td nowrap='nowrap'>{$ibforums->lang['yahoo']}</td>
		   <td width="100%">{$info['yahoo']}</td>
		 </tr>
		 <tr>
		   <td nowrap='nowrap'>{$ibforums->lang['msn']}</td>
		   <td width="100%">{$info['msn_name']}</td>
		 </tr>
		 <tr>
		   <td nowrap='nowrap'>{$ibforums->lang['pm']}</b></td>
		   <td><a href='javascript:redirect_to("&amp;act=Msg&amp;CODE=4&amp;MID={$info['mid']}", 1);'>{$ibforums->lang['click_here']}</a></td>
		 </tr>
		</td>
	   </tr>
	  </table>
	 </td>
	</tr>
	</table>
  </td>
 </tr>
</table>
</body>
</html>

EOF;
}


function show_card($name, $photo, $info) {
global $ibforums;
return <<<EOF

<div id="photowrap">
 <div id="phototitle">$name</div>
 <br>
 <table class="tablebasic" cellspacing="6">
 <tr>
  <td valign="middle" class="row1">$photo</td>
  <td width="100%" class="row1" valign="bottom">
    <table class="tablebasic" cellpadding="5">
      <tr>
        <td nowrap="nowrap">{$ibforums->lang['email']}</td>
		<td width="100%">{$info['email']}</td>
	  </tr>
	  <tr>
		<td nowrap="nowrap">{$ibforums->lang['integ_msg']}</td>
		<td width="100%">{$info['integ_msg']}</td>
	  </tr>
	  <tr>
		<td nowrap="nowrap">{$ibforums->lang['aim']}</td>
		<td width="100%">{$info['aim_name']}</td>
	  </tr>
	  <tr>
		<td nowrap="nowrap">{$ibforums->lang['icq']}</td>
		<td width="100%">{$info['icq_number']}</td>
	  </tr>
	  <tr>
		<td nowrap="nowrap">{$ibforums->lang['yahoo']}</td>
		<td width="100%">{$info['yahoo']}</td>
	  </tr>
	  <tr>
		<td nowrap="nowrap">{$ibforums->lang['msn']}</td>
		<td width="100%">{$info['msn_name']}</td>
	  </tr>
	  <tr>
		<td nowrap="nowrap">{$ibforums->lang['pm']}</b></td>
		<td><a href='javascript:redirect_to("&amp;act=Msg&amp;CODE=4&amp;MID={$info['mid']}", 1);'>{$ibforums->lang['click_here']}</a></td>
	  </tr>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 </table>
</div>
<div align="center">
  <a href="{$ibforums->base_url}act=Profile&amp;CODE=showcard&amp;MID={$info['mid']}&amp;download=1">{$ibforums->lang['ac_download']}</a>
  &middot; <a href="javascript:self.close();">{$ibforums->lang['ac_close']}</a>
</div>

EOF;
}


function user_edit($info) {
global $ibforums;
return <<<EOF

&middot; <a href='{$info['base_url']}act=UserCP&amp;CODE=22'>{$ibforums->lang['edit_my_sig']}</a> &middot;
<a href='{$info['base_url']}act=UserCP&amp;CODE=24'>{$ibforums->lang['edit_avatar']}</a> &middot;
<a href='{$info['base_url']}act=UserCP&amp;CODE=01'>{$ibforums->lang['edit_profile']}</a>

EOF;
}


function get_photo($show_photo, $show_width, $show_height) {
global $ibforums;
return <<<EOF

<img src="$show_photo" border="0" alt="User Photo" $show_width $show_height>

EOF;
}


function warn_level_rating($mid, $level,$min=0,$max=10) {
global $ibforums;
return <<<EOF

 <tr>
	<td class="row3" valign='top'><b>{$ibforums->lang['rating_level']}</b></td>
	<td align='left' class='row1'>&lt;&nbsp;$min ( <a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$mid}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$level}</a> ) $max&nbsp;&gt;</td>
  </tr>

EOF;
}


function warn_level($mid, $img, $percent) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="row3" valign='top'><b>{$ibforums->lang['warn_level']}</b></td>
	<td align='left' class='row1'><a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$mid}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$percent}</a>%:{$img}</td>
  </tr>

EOF;
}


}
?>