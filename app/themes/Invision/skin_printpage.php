<?php

class skin_printpage {



function pp_header($forum_name, $topic_title, $topic_starter,$fid, $tid) {
global $ibforums;
return <<<EOF
<!DOCTYPE html>
    <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
    <title>{$topic_title} -> {$ibforums->vars['board_name']} [Powered by Invision Power Board]</title>
    </head>
    <body bgcolor='#FFFFFF' alink='#000000' vlink='#000000' link='#000000'>
     <table width='90%' border='0' align='center' cellpadding='6'>
      <tr>
       <td><b><span style='font-family:arial; font-size:x-large; color:#4C77B6'><b>{$ibforums->lang['title']}</b></span>
       	   <br><span style='font-family:arial; font-size:small; color:#000000'><b><a href='{$ibforums->base_url}act=ST&amp;f=$fid&amp;t=$tid'>{$ibforums->lang['topic_here']}</a></b></span>
       </td>
      </tr>
      <tr>
       <td><span style='font-family:arial; size:small; color:#000000'><b>{$ibforums->vars['board_name']} &gt; $forum_name &gt; <span style='color:red'>$topic_title</span></b></span></td>
      </tr>
     </table>
     <br>
     <br>

EOF;
}


function choose_form($fid, $tid, $title) {
global $ibforums;
return <<<EOF

<div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$ibforums->lang['tvo_title']}&nbsp;$title</div>
 <div class='tablepad'>
  <b><a href='{$ibforums->base_url}act=Print&amp;client=printer&amp;f=$fid&amp;t=$tid'>{$ibforums->lang['o_print_title']}</a></b>
  <br>
  {$ibforums->lang['o_print_desc']}
  <br><br>
  <b><a href='{$ibforums->base_url}act=Print&amp;client=html&amp;f=$fid&amp;t=$tid'>{$ibforums->lang['o_html_title']}</a></b>
  <br>
  {$ibforums->lang['o_html_desc']}
  <br><br>
  <b><a href='{$ibforums->base_url}act=Print&amp;client=wordr&amp;f=$fid&amp;t=$tid'>{$ibforums->lang['o_word_title']}</a></b>
  <br>
  {$ibforums->lang['o_word_desc']}
 </div>
 <div align='center' class='pformstrip'>&lt;&lt;<a href='{$ibforums->base_url}showtopic=$tid'>{$ibforums->lang['back_topic']}</a></div>
</div>
<br>

EOF;
}


function pp_postentry($poster, $entry) {
global $ibforums;
return <<<EOF

	<table width='90%' align='center' cellpadding='6' border='1'>
	<tr>
	 <td bgcolor='#EEEEEE'><span style='font-family:arial; size:small; color:#000000'><b>{$ibforums->lang['by']}: {$entry['author_name']}</b> {$ibforums->lang['on']} {$entry['post_date']}</b></span></td>
	</tr>
	<tr>
	 <td><span style='font-family:arial; size:medium; color:#000000'>{$entry['post']}</span></td>
	</tr>
	</table>
	<br>

EOF;
}


function pp_end() {
global $ibforums;
return <<<EOF

    <center><span style='font-family:arial; size:xx-small; color:#000000'>Powered by Invision Power Board (https://www.invisionboard.com)<br>&copy; Invision Power Services (https://www.invisionpower.com)</span></center>

EOF;
}


}
