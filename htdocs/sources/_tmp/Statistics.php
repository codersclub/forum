<?php

   //------------------------------------------------------------------------------------
   // INVISIONBOARD STATISTICS (JOHNATHAN @ IBPLANET.COM)
   //------------------------------------------------------------------------------------

	$idx = new Statistics;

	class Statistics {
 	var $output     = "";
  	var $page_title = "";
  	var $nav        = array();
  	var $html       = "";

  	function Statistics() {
   		global $ibforums, $INFO, $DB, $std, $print;

   //--------------------------------------------
   // HTML AND LANGUAGE MODULES
   //--------------------------------------------

  	$ibforums->lang = $std->load_words($ibforums->lang, 'lang_statistics', $ibforums->lang_id );
 
	require "./Skin/".$ibforums->skin_id."/skin_Statistics.php";
   
	$this->html = new skin_Statistics();
  	$this->base_url = 		              "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";
	$ibforums->input['act'];
	$data = array();

   //------------------------------------------------------------------------------------
   // TOP 50 MOST VIEWED POSTS
   //------------------------------------------------------------------------------------


$statistics = $DB->query('
	SELECT title, tid, last_post, last_poster_name, views, forum_id 
	FROM ibf_topics 
	WHERE forum_id !=10000
	ORDER BY views 
	DESC LIMIT 50
');

while ($stat1 = mysql_fetch_array($statistics)) {
		
$data['viewthread'] .= "<tr width=100%><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f=$stat1[forum_id]&t=$stat1[tid]>$stat1[title]</a></TD><TD width=10% class=row2><font color=888888>$stat1[views]</TD></TR>";
	
	}


   //------------------------------------------------------------------------------------
   // TOP 50 POST WITH MOST REPLIES
   //------------------------------------------------------------------------------------


$statistics = $DB->query('
	SELECT title,tid,last_post,last_poster_name, posts, forum_id 
	FROM ibf_topics 
	WHERE forum_id !=10000
	ORDER BY posts DESC LIMIT 50
');
	
while ($stat2 = mysql_fetch_array($statistics)) {
		
$data['replythread'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f=$stat2[forum_id]&t=$stat2[tid]>$stat2[title]</a></TD><TD width=10% class=row2><font color=888888>$stat2[posts]</TD></TR>";

	}


   //------------------------------------------------------------------------------------
   // TOP 100 USERS WITH MOST POSTS
   //------------------------------------------------------------------------------------


$statistics = $DB->query('
	SELECT  name, id, posts 
	FROM ibf_members 
	ORDER BY posts 
	DESC LIMIT 25
');

while ($stat3 = mysql_fetch_array($statistics)) {

$data['poster'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&MID=$stat3[id]>$stat3[name]</a></TD><TD width=10% class=row2 align=center><font color=888888>$stat3[posts]</TD></TR>";
	
	}


   //------------------------------------------------------------------------------------
   // TOP 25 POLLS WITH MOST VOTES
   //------------------------------------------------------------------------------------

$statistics = $DB->query('
	SELECT poll_question, tid, forum_id, votes 
	FROM ibf_polls 
	WHERE forum_id !=1000 
	ORDER BY votes DESC LIMIT 25
');

while ($stat4 = mysql_fetch_array($statistics)) {

$data['poll_question'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f=$stat4[forum_id]&t=$stat4[tid]>$stat4[poll_question]</a></TD><TD width=10% class=row2 align=center><font color=888888>$stat4[votes]</TD></TR>";

        }


   //------------------------------------------------------------------------------------
   // TOP 25 TOPIC STARTERS
   //------------------------------------------------------------------------------------

	
$statistics = $DB->query('
	SELECT last_poster_id, starter_id, count( last_poster_id ) AS clast_poster_id, starter_name 
	FROM ibf_topics 
	WHERE starter_id >= 1
	GROUP BY starter_name 
	ORDER BY clast_poster_id 
	DESC LIMIT 10
');


while ($stat5 = mysql_fetch_array($statistics)) {

$data['threadstart'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&MID=$stat5[id]>$stat5[starter_name]</a></TD><TD width=10% class=row2 align=center><font color=888888>$stat5[clast_poster_id]</TD></TR>";

	}


   //------------------------------------------------------------------------------------
   // TOP 5 FORUMS WITH MOST TOTAL TOPICS
   //------------------------------------------------------------------------------------
	
$statistics = $DB->query('
	SELECT id, name, topics, posts 
	FROM ibf_forums
	ORDER BY topics 
	DESC LIMIT 5
');

while ($stat6 = mysql_fetch_array($statistics)) {

$data['forumtopics'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?showforum=$stat6[id]>$stat6[name]</a></TD><TD width=10% class=row2 align=center><font color=888888>$stat6[topics]</TD></TR>";

	}


   //------------------------------------------------------------------------------------
   // TOP 5 FORUMS WITH MOST TOTAL REPLIES
   //------------------------------------------------------------------------------------

	
$statistics = $DB->query('
	SELECT id, name, topics, posts 
	FROM ibf_forums 
	ORDER BY posts 
	DESC LIMIT 5
');

while ($stat7 = mysql_fetch_array($statistics)) {

$data['forumposts'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?showforum=$stat7[id]>$stat7[name]</a></TD><TD width=10% class=row2 align=center><font color=888888>$stat7[posts]</TD></TR>";

	}


   //------------------------------------------------------------------------------------
   // 10 MOST RECENT MEMBERS & THEIR POST NUMBERS
   //------------------------------------------------------------------------------------

$statistics = $DB->query('
	SELECT id, posts, name, joined
	FROM ibf_members 
	ORDER BY joined
	DESC LIMIT 10
');

while ($stat8 = mysql_fetch_array($statistics)) {

$data['posts'] .= "<tr><Td width=90% class=row2 align=left>&nbsp;&nbsp; <a href={$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&MID=$stat8[id]>$stat8[name]</a></TD><TD width=10% class=row2 align=center><font color=888888>$stat8[posts]</TD></TR>";

	}




   //------------------------------------------------------------------------------------
   // TOP 10 POSTERS OF THE WEEK (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------

$time_high = time();
$time_low = $time_high - (60*60*24*7);
$pot = $DB->query("
	SELECT COUNT(p.pid) as tpost, m.id, m.name, m.joined, m.posts 
	FROM ibf_posts p, ibf_members m 
	WHERE m.id > 0 AND m.id=p.author_id and post_date < $time_high and post_date > $time_low 
	GROUP BY p.author_id 
	ORDER BY tpost 
	DESC LIMIT 10
");

while ($row = mysql_fetch_array($pot))
{
 	$row[tpost] = number_format($row[tpost]);
 	$data[week_posters] .= 

"<tr><td width='90%' class='row2' align=left>&nbsp;&nbsp; 

<a href='{$ibforums->base_url}showuser={$row[id]}'>{$row[name]}</a>

</TD><TD width=10% class=row2 align=center><font color=888888>

{$row[tpost]}

</td></tr>";

}
 
   			 //------------------------------------------------------------------------------------
  			 // INVISIONBOARD STATISTICS (USER CONTRIBUTED MODULES)
  			 //------------------------------------------------------------------------------------



   //------------------------------------------------------------------------------------
   // TOP 10 POSTERS OF THE MONTH (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------


$time_high = time();
$time_low = $time_high - (60*60*24*30);
$pot = $DB->query("
	SELECT COUNT(p.pid) as tpost, m.id, m.name, m.joined, m.posts 
	FROM ibf_posts p, ibf_members m 
	WHERE m.id > 0 AND m.id=p.author_id and post_date < $time_high and post_date > $time_low 
	GROUP BY p.author_id 
	ORDER BY tpost 
	DESC LIMIT 10
");

while ($row = mysql_fetch_array($pot))
{
 	$row[tpost] = number_format($row[tpost]);
 	$data[month_posters] .= 

"<tr><td width='90%' class='row2' align=left>&nbsp;&nbsp; 

<a href='{$ibforums->base_url}showuser={$row[id]}'>{$row[name]}</a>
	
</TD><TD width=10% class=row2 align=center><font color=888888>

{$row[tpost]}
	
</td></tr>";

}




   //------------------------------------------------------------------------------------
   // MOST USED INSTANT MESSENGERS (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------


$pot = $DB->query("
	SELECT aim_name, integ_msg, msnname, yahoo, icq_number 
	FROM ibf_members
");
	$data[aim] = 0;
	$data[msn] = 0;
	$data[yahoo] = 0;
	$data[icq] = 0;
	$data[im] = 0;
	$data[none] = 0;

while ($info = mysql_fetch_array($pot))
{
 	if ($info[aim_name] != '')
		$data[aim]++;
	if ($info[msnname] != '')
		$data[msn]++;
	if ($info[yahoo] != '')
		$data[yahoo]++;
	if ($info[icq_number] != '')
		$data[icq]++;
	if ($info[integ_msg] != '')
		$data[im]++;
	if ($info[aim_name] == '' && $info[msnname] == '' && $info[yahoo] == '' && $info[icq_number] == '' && $info[integ_msg] == '')
		$data[none]++;
}


   //------------------------------------------------------------------------------------
   // NUMBER OF TOPICS PER MONTH (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------



$statistics = $DB->query('SELECT start_date FROM ibf_topics WHERE (tid <> 0) ORDER BY start_date');
$january = 0;
$february = 0;
$march = 0;
$april = 0;
$may = 0;
$june = 0;
$july = 0;
$august = 0;
$september = 0;
$october = 0;
$november = 0;
$december = 0;
while ($stats = mysql_fetch_array($statistics))
{
 	$month = date('F', $stats['start_date']);
	if ($month == 'January')
		$january++;
	else if ($month == 'February')
		$february++;
	else if ($month == 'March')
		$march++;
	else if ($month == 'April')
		$april++;
	else if ($month == 'May')
		$may++;
	else if ($month == 'June')
		$june++;
	else if ($month == 'July')
		$july++;
	else if ($month == 'August')
		$august++;
	else if ($month == 'September')
		$september++;
	else if ($month == 'October')
		$october++;
	else if ($month == 'November')
		$november++;
	else if ($month == 'December')
		$december++;
}
$january = number_format($january);
$february = number_format($february);
$march = number_format($march);
$april = number_format($april);
$may = number_format($may);
$june = number_format($june);
$july = number_format($july);
$august = number_format($august);
$september = number_format($september);
$october = number_format($october);
$november = number_format($november);
$december = number_format($december);
$data['topics_by_month'] = <<<EOF
<tr>
	<td class='row2'><font color=888888>
		January
	</td>
	<td class='row2'><font color=888888>
		{$january}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		February
	</td>
	<td class='row2'><font color=888888>
		{$february}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		March
	</td>
	<td class='row2'><font color=888888>
		{$march}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		April
	</td>
	<td class='row2'><font color=888888>
		{$april}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		May
	</td>
	<td class='row2'><font color=888888>
		{$may}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		June
	</td>
	<td class='row2'><font color=888888>
		{$june}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		July
	</td>
	<td class='row2'><font color=888888>
		{$july}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		August
	</td>
	<td class='row2'><font color=888888>
		{$august}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		Septemember
	</td>
	<td class='row2'><font color=888888>
		{$september}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		October
	</td>
	<td class='row2'><font color=888888>
		{$october}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		November
	</td>
	<td class='row2'><font color=888888>
		{$november}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		December
	</td>
	<td class='row2'><font color=888888>
		{$december}
	</td>
</tr>
EOF;

   //------------------------------------------------------------------------------------
   // NUMBER OF REGISTRATIONS PER MONTH (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------


$statistics = $DB->query('SELECT joined FROM ibf_members WHERE (id <> 0) ORDER BY joined');
$january = 0;
$february = 0;
$march = 0;
$april = 0;
$may = 0;
$june = 0;
$july = 0;
$august = 0;
$september = 0;
$october = 0;
$november = 0;
$december = 0;
while ($stats = mysql_fetch_array($statistics))
{
 	$month = date('F', $stats['joined']);
	if ($month == 'January')
		$january++;
	else if ($month == 'February')
		$february++;
	else if ($month == 'March')
		$march++;
	else if ($month == 'April')
		$april++;
	else if ($month == 'May')
		$may++;
	else if ($month == 'June')
		$june++;
	else if ($month == 'July')
		$july++;
	else if ($month == 'August')
		$august++;
	else if ($month == 'September')
		$september++;
	else if ($month == 'October')
		$october++;
	else if ($month == 'November')
		$november++;
	else if ($month == 'December')
		$december++;
}
$january = number_format($january);
$february = number_format($february);
$march = number_format($march);
$april = number_format($april);
$may = number_format($may);
$june = number_format($june);
$july = number_format($july);
$august = number_format($august);
$september = number_format($september);
$october = number_format($october);
$november = number_format($november);
$december = number_format($december);
$data['users_by_month'] .= <<<EOF
<tr>
	<td class='row2'><font color=888888>
		January
	</td>
	<td class='row2'><font color=888888>
		{$january}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		February
	</td>
	<td class='row2'><font color=888888>
		{$february}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		March
	</td>
	<td class='row2'><font color=888888>
		{$march}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		April
	</td>
	<td class='row2'><font color=888888>
		{$april}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		May
	</td>
	<td class='row2'><font color=888888>
		{$may}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		June
	</td>
	<td class='row2'><font color=888888>
		{$june}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		July
	</td>
	<td class='row2'><font color=888888>
		{$july}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		August
	</td>
	<td class='row2'><font color=888888>
		{$august}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		Septemember
	</td>
	<td class='row2'><font color=888888>
		{$september}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		October
	</td>
	<td class='row2'><font color=888888>
		{$october}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		November
	</td>
	<td class='row2'><font color=888888>
		{$november}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		December
	</td>
	<td class='row2'><font color=888888>
		{$december}
	</td>
</tr>
EOF;


   //------------------------------------------------------------------------------------
   // NUMBER OF POSTS PER MONTH (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------

$statistics = $DB->query('SELECT post_date FROM ibf_posts WHERE (pid <> 0) ORDER BY post_date');
$january = 0;
$february = 0;
$march = 0;
$april = 0;
$may = 0;
$june = 0;
$july = 0;
$august = 0;
$september = 0;
$october = 0;
$november = 0;
$december = 0;
while ($stats = mysql_fetch_array($statistics))
{
 	$month = date('F', $stats['post_date']);
	if ($month == 'January')
		$january++;
	else if ($month == 'February')
		$february++;
	else if ($month == 'March')
		$march++;
	else if ($month == 'April')
		$april++;
	else if ($month == 'May')
		$may++;
	else if ($month == 'June')
		$june++;
	else if ($month == 'July')
		$july++;
	else if ($month == 'August')
		$august++;
	else if ($month == 'September')
		$september++;
	else if ($month == 'October')
		$october++;
	else if ($month == 'November')
		$november++;
	else if ($month == 'December')
		$december++;
}
$january = number_format($january);
$february = number_format($february);
$march = number_format($march);
$april = number_format($april);
$may = number_format($may);
$june = number_format($june);
$july = number_format($july);
$august = number_format($august);
$september = number_format($september);
$october = number_format($october);
$november = number_format($november);
$december = number_format($december);
$data['posts_by_month'] = <<<EOF
<tr>
	<td class='row2'><font color=888888>
		January
	</td>
	<td class='row2'><font color=888888>
		{$january}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		February
	</td>
	<td class='row2'><font color=888888>
		{$february}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		March
	</td>
	<td class='row2'><font color=888888>
		{$march}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		April
	</td>
	<td class='row2'><font color=888888>
		{$april}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		May
	</td>
	<td class='row2'><font color=888888>
		{$may}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		June
	</td>
	<td class='row2'><font color=888888>
		{$june}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		July
	</td>
	<td class='row2'><font color=888888>
		{$july}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		August
	</td>
	<td class='row2'><font color=888888>
		{$august}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		Septemember
	</td>
	<td class='row2'><font color=888888>
		{$september}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		October
	</td>
	<td class='row2'><font color=888888>
		{$october}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		November
	</td>
	<td class='row2'><font color=888888>
		{$november}
	</td>
</tr>
<tr>
	<td class='row2'><font color=888888>
		December
	</td>
	<td class='row2'><font color=888888>
		{$december}
	</td>
</tr>
EOF;

   //------------------------------------------------------------------------------------
   // TEN MOST RECENT ACTIVE USERS (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------


$statistics = $DB->query('SELECT last_activity, id, name FROM ibf_members WHERE id > 0 ORDER BY last_activity desc LIMIT 5');
while ($stats = mysql_fetch_array($statistics))
{
 	$date = $std->get_date($stats['last_activity'],'LONG');
	
	$data['last_active'] .= <<<EOF
<tr>
	<td width='50%' class='row2' align='left'>
		<a href="{$ibforums->base_url}showuser={$stats[id]}">{$stats[name]}</a>
	</td>
	<td width='50%' class='row2' align='left'><font color=888888>
		&nbsp;{$date}
	</td>
</tr>
EOF;
}


   //------------------------------------------------------------------------------------
   // TEN USERS WITH HIGEST POSTS PER DAY  (AUTHOR: OMEGA13A)
   //------------------------------------------------------------------------------------

$currect_time = time();
$statistics = $DB->query('SELECT (posts / (('.$currect_time.' - joined) / 86400)) rate, id, name FROM ibf_members WHERE id >  0 ORDER BY rate desc LIMIT 10');
while ($stats = mysql_fetch_array($statistics))
{ 
 	$data['fastest_users'] .= <<<EOF
<tr>
	<td width='90%' class='row2' align='left'><a href="{$ibforums->base_url}showuser={$stats[id]}">{$stats[name]}</a></td>
	<td width='10%' class='row2' align='left'><font color=888888>&nbsp;{$stats[rate]}</td>
</tr>
EOF;
}



   //-----------------------------------------------------
   // RENDER THE STATISTICS
   //-----------------------------------------------------

	$this->output=$this->html->Statistics($data);
	$print->add_output("$this->output");
	$print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, NAV => $this->nav ) );
	$this->page_title = $ibforums->vars['board_name'];
	$this->nav        = array( $ibforums->lang['page_title'] );

	}


     }

?>