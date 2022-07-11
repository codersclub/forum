<?php

class skin_quiz {

//---------------------------------------
function quiz_edit_header($settings)
{
global $ibforums;

$itemlist = $this->form_multiselect( "quiz_items[]", $settings['items'], $settings['items_won'],$settings['items_rows']);


return <<<EOF

<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$settings['quizname']}</div>
   {$settings['modform_open']}

  <table cellspacing='1'>
  <form action="{$ibforums->base_url}act=quiz&code=update_quiz" name="quiz" method="post">
  <input name="quiz_id" value="{$settings['q_id']}" type="hidden">

  <input type='hidden' name='item_names' value='{$settings['item_name']}'>";

    <tr class='darkrow2'>
      <th class="titlemedium" width="30%">{$ibforums->lang['quiz_setting']}</th>
      <th class="titlemedium" width="70%">{$ibforums->lang['quiz_value']}</th>
    </tr>

    <tr>
      <td class='row2'><b>Quiz Name:</b><br>The Name for this Quiz.</td>
      <td class='row2'>
	<input name="quiz_name" value="{$settings['quizname']}" size="80" class="textinput" type="text">
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Quiz Short Description:</b><br>Description for this quiz.<br>BBCode and Emoticions are Enabled!</td>
      <td class='row2'>
	<input name="quiz_desc" value="{$settings['quizdesc']}" size="80" class="textinput" type="text">
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Quiz Description:</b><br>Description for this quiz.<br>BBCode and Emoticions are Enabled!</td>
      <td class='row2'>
      <textarea name="quiz_post" cols="60" rows="10" wrap="soft" class="multitext">{$settings['post']}</textarea>
      </td>
    </tr>

<!-- !!! CHECK FOR WHAT TO MAKE SELECTED {$settings['approved']} -->
    <tr>
      <td class='row2'><b>Approve the Quiz?</b><br>If 'yes,' the Quiz will be visible to all member, else visible for admin and author only.</td>
      <td class='row2'>
      <input type='radio' name='approved' value='1' {$settings['quiz_approved_yes']}> Yes &nbsp;&nbsp;&nbsp;
      <input type='radio' name='approved' value='0' {$settings['quiz_approved_no']}>No
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Quiz Status:</b><br></td>
      <td class='row2'>
      <input type='radio' name='quiz_status' value='OPEN' {$settings['quiz_status_open']}> Open &nbsp;&nbsp;&nbsp;
      <input type='radio' name='quiz_status' value='CLOSED' {$settings['quiz_status_closed']}> Closed
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Quiz Running Time:</b><br></td>
      <td class='row2'>
        <input name="q_run" value="{$settings['run_for']}" size="4" class="textinput" type="text">
        days
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Only let X amount of users play:</b><br>After X (X being what you set) users have played the quiz will automatically close. (Put 0 to disable)</td>
      <td class='row2'>
        <input name="let_play" value="{$settings['let_only']}" size="4" class="textinput" type="text">
        players
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Time Out:</b><br>The amount of minutes before a quiz auto submits it self. This is to stop people from being able to look up answers. (0 will disable it)</td>
      <td class='row2'>
        <input name="timeout" value="{$settings['timeout']}" size="4" class="textinput" type="text">
        minutes
      </td>
    </tr>


    <tr>
      <td class='row2'><b>Percent Needed:</b><br>Percent of correct answer s needed, to get the winnings.</td>
      <td class='row2'>
        <input name="perc_need" value="{$settings['percent_needed']}" size="4" class="textinput" type="text">
        %
      </td>
    </tr>

    <tr>
      <td class='row2'><b>Winnings:</b><br>The amount of points a user gets if they get or go over the percent needed.</td>
      <td class='row2'>
        <input name="winnings" value="{$settings['amount_won']}" size="4" class="textinput" type="text">
        Dgm
      </td>
    </tr>

<!-- !!! CHECK FOR WHAT TO MAKE SELECTED -->
    <tr>
      <td class='row2'><b>Enable BBCode?</b><br>If yes will parse all BBCodes in Quiz Questions.</td>
      <td class='row2'><input type='radio' name='bbcode' value='1'> Yes &nbsp;&nbsp;&nbsp;<input type='radio' name='bbcode_enabled' value='0'>No</td>
    </tr>

    <tr>
      <td class='row2'><b>Item Prizes:</b><br>The Item(s) a user will win from the quiz if they get the needed percentage of correct answers.</td>
      <td class='row2'>
<!--{$settings['quiz_items']}-->
{$itemlist}
      </td>
    </tr>

<!--
    <tr>
      <td class='row2'><b></b><br></td>
      <td class='row2'>{$settings['']}</td>
    </tr>
-->

EOF;
}


//------------------------------------------
function quiz_show($post,$author) {
global $ibforums;

return <<<EOF
<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$post['quizname']}</div>
   {$post['modform_open']}

  <table cellspacing='1'>
    <tr>
      <td valign='middle' class='row4' width='1%'>
        {$author['member_group_img']}
        <span class='postdata'>{$author['name']}</span>{$author['online']}
      </td>
      <td class='row4' valign='top' width="99%">
      <div style='width:20%;float:left'>{$post['status']}</div>
      <div align='right' style='width:80%;float:right'>{$post['actions']}</div>
      </td>
    </tr>
    <tr>
      <td valign='top' class='post1'>
        <span class='postdetails'>{$author['avatar']}
        <b>{$author['sex']}{$author['title']}</b>
        {$author['member_rank_img']}
        {$author['profile']}<br>
        {$author['member_points']}
        {$author['rep']}
        {$author['warn_text']}</span>
        <!--$ author[gender]-->

<div align='left' class='post1' style='float:left;width:auto'>
<b>{$author['ip_address']}</b></div>
<img src='{$ibforums->skin['ImagesPath']}/spacer.gif' alt='' width='160' height='1'><br>

      </td>
      <td width='100%' valign='top' class='post1'>
        <div class='postcolor'>{$post['quizdesc']}<hr>{$post['post']}</div>
        {$member['signature']}
      </td>
    </tr>
    <tr>
      <td class='row2' colspan='2' align='center'>
	{$post['show_results']}
	{$post['take_quiz']}
      </td>
    </tr>
EOF;
}


//-------------------------------------------
function quiz_question_header($settings)
{
global $ibforums;
return <<<EOF

<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$settings['quizname']}</div>
   {$settings['modform_open']}
 <div class="row2 center">
  <div style="width:468px; text-align:left; padding: 8px 4px 8px 4px">
  {$settings['quizdesc']}
  <hr>
  {$settings['post']}
  </div>
 </div>

  <table cellspacing='1'>
  <form action='{$ibforums->base_url}act=quiz' name='quiz' method='post'>
  <input name='quiz_id' value='{$settings['q_id']}' type='hidden'>
  <input name='code' value='update_questions' type='hidden'>

EOF;
}




//-------------------------------------
function edit_question($nq=0,$quiz=array())
{
  $nq++;
  $type = $this->form_dropdown('mid_'.$quiz['mid'].'_type',
   			 array(
      			0 => array('single','Single Answer [text field]'),
      			1 => array('multiq','Multiple Correct Answers [text field]'),
      			2 => array('dropdown','Drop Down Answers [1 or more correct]'),
      			3 => array('radio','Radio-button Answers [1 or more correct]'),
      			4 => array('checkbox','CheckBox Answers [1 or more correct]'),
      			5 => array('opinion','NonChecked Answer')
      			 ),
      			$quiz['type']
      			 );

  $html = "
  <tr>
    <td class='titlemedium'>
      <big><b>Question&nbsp;{$nq}:</b></big>
    </td>
    <td class='titlemedium'>
    &nbsp; Type: {$type}
    </td>
  </tr>

  <tr>
    <td class='row2' valign='middle'>&nbsp;</td>
    <td class='row2' valign='middle'>
    <textarea name='q_{$quiz['mid']}_question' cols='80' rows='4' wrap='soft' class='multitext'>{$quiz['question']}</textarea></td>
  </tr>
";

  if($quiz['type'] == 'single')
  {
    $html .= "
  <tr class='row2' valign='middle'>
    <input type='hidden' name='q_{$quiz['mid']}_1_correct' value='1'>
    <td><b>Answer:</b></td>
    <td>
    <input name='q_{$quiz['mid']}_answer' value='{$quiz['answer']}' size='100' class='textinput' type='text'>
    </td>
  </tr>
";

  }
  else if($quiz['type'] == 'dropdown' ||
      	$quiz['type'] == 'multiq' ||
      	$quiz['type'] == 'radio' ||
      	$quiz['type'] == 'checkbox'
      	)
  {
    $answers = explode("||",$quiz['answer']);

    for($na=0; $na<9; $na++)
    {
      $answer = $answers[$na];
      $naw = $na+1;

      $z= preg_match("#{answer([1-9])_(0|1):(.+)}#is",$answer,$match);

      if($quiz['type'] == 'multiq')
      {
        $html .= "<input type='hidden' name='q_{$quiz['mid']}_{$naw}_correct' value='1'>\n";
        $extra[$match[1]] = "";
      }
      else
      {
        if($match[2] == 1)
        {
          $checkbox = $this->form_checkbox("q_".$quiz['mid']."_".$naw."_correct",1);
        } else {
          $checkbox = $this->form_checkbox("q_".$quiz['mid']."_".$naw."_correct",0);
        }

        $extra[$naw] = "<br>Correct? ".$checkbox;

      }

      $html .= "
  <tr class='row2' valign='middle'>
    <td><b>Answer {$naw}:</b> {$extra[$naw]}</td>
    <td>
    <input name='q_{$quiz['mid']}_answer_{$naw}' value='{$match[3]}' size='100' class='textinput' type='text'>
    </td>
  </tr>
";
    }
  }

  else if($quiz['type'] == 'opinion')
  {
    $html .= "
  <tr class='row2' valign='middle'>
    <td><b>Answer: </b></td>
    <td>
    <b>Nonchecked User Input</b>
    </td>
  </tr>
";
  }


  return $html;
}



//--------------------------------------------
function quiz_u_a_header($settings,$member)
{
global $ibforums;

$correct_percent = round($member['amount_right']/$settings['real_questions'] * 100);
if($correct_percent > 100) $correct_percent = 100;


return <<<EOF

<div class="tableborder">

 <div class='maintitle'><{CAT_IMG}>&nbsp;{$ibforums->lang['user_answers']} {$ibforums->lang['in_the_quiz']} "{$settings['quizname']}"</div>
   {$post['modform_open']}

 <div class="row2 center">
  <div style="width:468px; text-align:left; padding: 8px 4px 8px 4px">
  {$settings['quizdesc']}
  <hr>
  {$settings['post']}
  </div>
 </div>

  <table cellspacing='1'>
    <tr>
      <td valign='middle' class='row4' width='1%'>
        {$member['member_group_img']}
        <span class='postdata'>{$member['name']}</span>{$member['online']}
      </td>
      <td class='row4' valign='top' width="99%">
<!-- date & others -->
      {$settings['quiz_status']}
      </td>
    </tr>
    <tr>
      <td valign='top' class='post1'>

        <span class='postdetails'>{$member['avatar']}
        <b>{$author['sex']}{$member['title']}</b>
        {$member['member_rank_img']}
        {$member['profile']}<br>
        {$member['member_points']}
        {$member['rep']}
        {$member['warn_text']}</span>
        <!--$ author[gender]-->

<div align='left' class='post1' style='float:left;width:auto'>
<b>{$member['ip_address']}</b></div>
<img src='{$ibforums->skin['ImagesPath']}/spacer.gif' alt='' width='160' height='1'><br>
      </td>

      <td width='100%' valign='top' class='post1'>
        <div class='postcolor'>
<b>  Time:        {$member['time']}</b><br>
  Timeout:     {$settings['timeout']} min.<br>
<b>  Time took:   {$member['time_took']}</b><br>
  Total Questions:   {$settings['total_questions']}<br>
  Real Questions:   {$settings['real_questions']}<br>
<b>  Correct answers: {$member['amount_right']}</b><br>
  Correct percent: <b>{$correct_percent}%</b><br>
        </div>
<!--        {$member['signature']}-->
      </td>
    </tr>
  </table>


  <table cellspacing='1'>
  <form action="{$ibforums->base_url}act=quiz&code=do_take_quiz&quiz_id={$ibforums->input['quiz_id']}" name="quiz" method="post">
  <input type="hidden" name="timeout" value="{$settings['timeout']}">
  <input type="hidden" name="starttime" value="{$settings['time']}">
    <tr class='darkrow2'>
      <th class="titlemedium" width="50%">{$ibforums->lang['quiz_question']}</th>
      <th class="titlemedium" width="50%">{$ibforums->lang['quiz_answer']}</th>
    </tr>

EOF;
}



//-----------------------------------
function user_answer($info)
{
  global $ibforums;

  $info['user_answer']=str_replace("\n","<br>",$info['user_answer']);

  return <<<EOF

  <tr valign="top">
    <td class="row2"><b>{$info['question']}</b></td>
    <td class="row2">{$info['user_answer']}</td>
  </tr>

EOF;
}





//-----------------------------
function end_page() {
global $ibforums;
return <<<EOF

</table>
<div class='darkrow1' style='height:5px'><!-- --></div>
</div>


EOF;
}


//-------------------------------
function plays_left_header() {
global $ibforums;
return <<<EOF

     <th class="titlemedium" width="5%">{$ibforums->lang['plays_left']}</th>

EOF;
}


//--------------------------------------
function plays_left_middle($plays)
{
  global $ibforums;
  return <<<EOF

      <td align='center' class='row2'>{$plays}</td>

EOF;
}


//------------------------------------
function quiz_header($data=array())
{
  global $ibforums;
  return <<<EOF

<div align='right'>{$data['QUIZ_BUTTON']}</div>

<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$data['name']}</div>
   {$data['modform_open']}
   <table cellspacing='1'>
    <tr class='darkrow2'>
     <td align='center' class='titlemedium'><img src='{$ibforums->skin['ImagesPath']}/spacer.gif' alt='' width='20' height='1'></td>
     <td align='center' class='titlemedium'><img src='{$ibforums->skin['ImagesPath']}/spacer.gif' alt='' width='20' height='1'></td>
     <th class="titlemedium" width="45%">{$ibforums->lang['quiz_name']}</th>
     <th class="titlemedium" width='14%'>{$ibforums->lang['quiz_starter']}</th>
     <!--Plays Left Header-->
     <th class="titlemedium" width="5%">{$ibforums->lang['quiz_winnings']}</th>
     <th class="titlemedium" width="5%">{$ibforums->lang['quiz_stats']}</th>
     <th class="titlemedium" width="5%">{$ibforums->lang['quiz_status']}</th>
     <th class="titlemedium" width="5%">{$ibforums->lang['quiz_play']}</th>
<!--th class="pformstrip" width="5%">{$ibforums->lang['quiz_desc']}</th-->
  </tr>

EOF;
}



function list_quiz($data) {
  global $ibforums;
  return <<<EOF

    <tr>
      <td align='center' class='row4'>{$data['img']}</td>
      <td align='center' class='row2'>{$data['icon']}</td>
      <td class='row4'>
        <a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$data['q_id']}'>
          <b>{$data['quizname']}</b>
        </a>
        <br>
        <span class='desc'>{$data['quizdesc']}</span>{$data['queued_link']}
      </td>
      <td align='center' class='row2'>
        <a href='{$ibforums->base_url}showuser={$data['starter_id']}'>{$data['starter_name']}</a>
      </td>

      <!--Plays Left Middle-->

      <td align='center' class='row4'>{$data['amount_won']}</td>
      <td align='center' class='row2'>{$data['quiz_status']}</td>
      <td align='center' class='row2'>{$data['status_days']} {$ibforums->lang['quiz_days']}</td>
      <td class='row2'>{$data['take_quiz']}<br>{$data['show_results']}</td>
      {$data['mod_checkbox']}
    </tr>

EOF;
}

function quiz_results_header($id=0,$title="") {
global $ibforums;
return <<<EOF

<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$ibforums->lang['results_nav']} &quot;<a href="{$ibforums->base_url}act=quiz&code=show&quiz_id={$id}">{$title}</a>&quot;</div>
   {$data['modform_open']}

  <table cellspacing='1'>

    <tr class='darkrow2'>
      <th class="titlemedium" width="5%">{$ibforums->lang['results_place']}</th>
      <th class="titlemedium" width="20%">{$ibforums->lang['results_name']}</th>
      <th class="titlemedium" width="20%">{$ibforums->lang['date']}</th>
      <th class="titlemedium" width="20%">{$ibforums->lang['results_right']}</th>
      <th class="titlemedium" width="20%">{$ibforums->lang['results_timetook']}</th>
    </tr>

EOF;
}


function quiz_results_results($member,$place)
{
  global $ibforums,$std;

  $qid = intval($ibforums->input['quiz_id']);

  if($member['memberid'] > 0)
  {
    $member['name'] = "<a href='{$ibforums->base_url}showuser={$member['id']}'>{$member['name']}</a>";
  } else {
    $member['name'] = $ibforums->lang['guest_name'];
  }

  if ( $ibforums->member['g_is_supmod'] ||
       ($ibforums->member['id'] == $member['memberid'])
     )
  {
    $member['amount_right'] .= " &nbsp; <a href='{$ibforums->base_url}act=quiz&code=user_answers&quiz_id={$qid}&userid={$member['id']}'>{$ibforums->lang['answers']}</a>";
  }

return <<<EOF

  <tr>
    <td align='center' class='row2'>{$place}</td>
    <!--td class="pformleft" width="25%"><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></td-->
    <td align='center' class='row2'>{$member['name']}</td>
    <td align='center' class='row2'>{$member['time']}</td>
    <td align='center' class='row2'>{$member['amount_right']}</td>
    <td align='center' class='row2'>{$member['time_took']}</td>
  </tr>

EOF;
}




function quiz_q_a_header($settings) {
global $ibforums;
return <<<EOF

<div class="tableborder">
 <div class='maintitle'><{CAT_IMG}>&nbsp;{$settings['quizname']}</div>
   {$settings['modform_open']}
 <div class="row2 center">
  <div style="width:468px; text-align:left; padding: 8px 4px 8px 4px">
  {$settings['quizdesc']}
  <hr>
  {$settings['post']}
  </div>
 </div>

<script>
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

  <table cellspacing='1'>
  <form action="{$ibforums->base_url}act=quiz&code=do_take_quiz&quiz_id={$ibforums->input['quiz_id']}" name="quiz" method="post">
  <input type="hidden" name="timeout" value="{$settings['timeout']}">
  <input type="hidden" name="starttime" value="{$settings['time']}">
    <tr class='darkrow2'>
      <th class="titlemedium" width="50%">{$ibforums->lang['quiz_question']}</th>
      <th class="titlemedium" width="50%">{$ibforums->lang['quiz_answer']}</th>
    </tr>

EOF;
}


function single_question($num,$info) {
global $ibforums;
return <<<EOF

  <tr valign="top">
    <td class="row2">{$num}. <b>{$info['question']}</b></td>
    <td class="row2"><input type="text" name="uanswer_{$info['mid']}" value="" style="width:100%"></td>
  </tr>

EOF;
}


function opinion_question($num,$info) {
global $ibforums;
return <<<EOF

  <tr valign="top">
    <td class="row2">{$num}. <b>{$info['question']}</b></td>
    <td class="row2"><textarea name="uanswer_{$info['mid']}" style="width:100%" rows="10"></textarea></td>
  </tr>

EOF;
}


function dropdown_question($num,$info) {
global $ibforums;
return <<<EOF

  <tr valign="top">
    <td class="row2">{$num}. <b>{$info['question']}</b></td>
    <td class="row2">{$info['dropdown']}</td>
  </tr>

EOF;
}


function quiz_q_a_submit() {
global $ibforums;
return <<<EOF

  <tr>
    <td class="activeuserstrip" align="center" colspan="2"><input type="submit" name="take_quiz" value="{$ibforums->lang['quiz_qa_submit']}"></td>
  </tr>
</form>
EOF;
}





//+--------------------------------------------------------------------

function form_checkbox( $name, $checked=0, $val=1, $js=array() )
{
  if ($checked == 1)
  {
    return "<input type='checkbox' name='$name' value='$val' checked='checked'>";
  }
  else
  {
    return "<input type='checkbox' name='$name' value='$val'>";
  }
}

//+--------------------------------------------------------------------

function form_multiselect($name,
			  $list=array(),
			  $default=array(),
			  $size=5,
			  $js="")
{
  if ($js != "")
  {
    $js = ' '.$js.' ';
  }

  //$html = "<select name='$name".'[]'."'".$js." id='dropdown' multiple='multiple' size='$size'>\n";
  $html = "<select name='$name"."'".$js." class='dropdown' multiple='multiple' size='$size'>\n";
  foreach ($list as $k => $v)
  {
    $selected = "";

    if ( count($default) > 0 )
    {
      if ( in_array( $v[0], $default ) )
      {
	$selected = ' selected="selected"';
      }
    }

    $html .= "<option value='".$v[0]."'".$selected.">".$v[1]."</option>\n";
  }

  $html .= "</select>\n\n";

  return $html;

}


//+--------------------------------------------------------------------

function form_dropdown($name, $list=array(), $default_val="", $js="")
{
  if ($js != "")
  {
    $js = ' '.$js.' ';
  }

  $html = "<select name='$name'".$js." class='dropdown'>\n";

  foreach ($list as $k => $v)
  {
    $selected = "";

    if ( ($default_val != "") and ($v[0] == $default_val) )
    {
      $selected = ' selected';
    }

    $html .= "<option value='".$v[0]."'".$selected.">".$v[1]."</option>\n";
  }

  $html .= "</select>\n\n";

  return $html;

}

function warn_title($id, $title)
{
  global $ibforums;
  return <<<EOF

<a href="javascript:PopUp('{$ibforums->base_url}act=warn&amp;mid={$id}&amp;CODE=view','Pager','500','450','0','1','1','1')">{$title}</a>:

EOF;
}

//-----------------------------------------------------
function error()
{
  global $ibforums;
  return <<<EOF

<div class="tableborder">

  <div class="maintitle">{$ibforums->lang['problem']}</div>
  <table cellspacing="1">
  <tr>
	<td class="row4" align="center">{$ibforums->lang['error']}</td>
  </tr>

EOF;
}


function error_row($message)
{
  global $ibforums;
  return <<<EOF

  <tr>
	<td class="pformstrip" align="center">{$message}</td>
  </tr>

EOF;
}


function check()
{
  global $ibforums;
  return <<<EOF

<script>
<!--
function check(type) {
 <!--IBS.SAFTY_ON-->
 return true;
}
//-->
</script>

EOF;
}
}
