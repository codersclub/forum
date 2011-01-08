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

  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
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

  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
    <tr> 
      <td valign='middle' class='row4' width='1%'>{$author['member_group_img']} <span class='postdata'>{$author['name']}</span>{$author['online']}</td>
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
        <!--$ author[field_1]-->

<div align='left' class='post1' style='float:left;width:auto'>
<b>{$author['ip_address']}</b></div>
<img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='160' height='1'><br> 

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
 <div class="row2">
 <center>
  <div style="width:468px; text-align:left; padding: 8px 4px 8px 4px">
  {$settings['quizdesc']}
  <hr>
  {$settings['post']}
 </div>
 </center>
 </div>

  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
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

/*
    if($quiz['type'] == 'multiq')
    {
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_1_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_2_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_3_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_4_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_5_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_6_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_7_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_8_correct' value='1'>";
      $html .= "<input type='hidden' name='q_".$quiz['mid']."_9_correct' value='1'>";
    }

*/
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

//foreach($settings as $k=>$v) echo $k."=".$v."<br>\n";
//echo "<hr>";
//foreach($member as $k=>$v) echo $k."=".$v."<br>\n";

$correct_percent = round($member['amount_right']/$settings['real_questions'] * 100);
if($correct_percent > 100) $correct_percent = 100;


return <<<EOF

<div class="tableborder">

 <div class='maintitle'><{CAT_IMG}>&nbsp;{$ibforums->lang['user_answers']} {$ibforums->lang['in_the_quiz']} "{$settings['quizname']}"</div>
   {$post['modform_open']}

 <div class="row2">
 <center>
  <div style="width:468px; text-align:left; padding: 8px 4px 8px 4px">
  {$settings['quizdesc']}
  <hr>
  {$settings['post']}
  </div>
 </center>
 </div>

  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
    <tr> 
      <td valign='middle' class='row4' width='1%'>{$member['member_group_img']} <span class='postdata'>{$member['name']}</span>{$member['online']}</td>
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
        <!--$ author[field_1]-->

<div align='left' class='post1' style='float:left;width:auto'>
<b>{$member['ip_address']}</b></div>
<img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='160' height='1'><br> 
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


  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
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
   <table width='100%' border='0' cellspacing='1' cellpadding='4'>
    <tr class='darkrow2'> 
     <td align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
     <td align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
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
      <td class='row4'><a href='{$ibforums->base_url}act=quiz&code=show&quiz_id={$data['q_id']}'><b>{$data['quizname']}</b></a>
      <br><span class='desc'>{$data['quizdesc']}</span>{$data['queued_link']}</td>
      <td align='center' class='row2'><a href='{$ibforums->base_url}showuser={$data['starter_id']}'>{$data['starter_name']}</a></td>

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

  <table width='100%' border='0' cellspacing='1' cellpadding='4'>

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
 <div class="row2">
 <center>
  <div style="width:468px; text-align:left; padding: 8px 4px 8px 4px">
  {$settings['quizdesc']}
  <hr>
  {$settings['post']}
 </div>
 </center>
 </div>

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

  <table width='100%' border='0' cellspacing='1' cellpadding='4'>
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






/*
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
<form action="{$ibforums->base_url}act=quiz&code=do_edit_points" name="edit" method="post" onSubmit="return check_edit('{$ibforums->lang['didnotfilloutfields']}')">
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
<form action="{$ibforums->base_url}act=quiz&code=do_do_edit_points" name="edit" method="post" onSubmit="return check_edit('{$ibforums->lang['didnotfilloutfields']}')">
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


function show_users_inventory($inventory) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" align="center" width="10%"><img src="{$ibforums->vars['board_url']}/html/store/icons/{$inventory['icon']}"></td>
	<td class="pformleft" align="center" width="20%">{$inventory['item_name']}</td>
	<td class="pformleft" align="center" width="20%">{$inventory['item_desc']}</td>	
	<td class="pformleft" align="center" width="10%"><input type='text' name='price_{$inventory['i_id']}' value='{$inventory['price_payed']}'>
												<input type='hidden' name='original_{$inventory['i_id']}' value='{$inventory['price_payed']}'></td>
	<td class="pformleft" align="center" width="10%"><input type='checkbox' name='delete_{$inventory['i_id']}' value='1' unchecked></td>
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
 if(document.fine.username.value == "" || document.fine.fine_amount.value == "" || document.fine.user_reson.value == "") {
  alert(message);
  return false;
 }
 return true;
}
//-->
</script>
<form action="{$ibforums->base_url}act=quiz&code=do_fine_users" name="fine" method="post" onSubmit="return check_fine('{$ibforums->lang['didnotfilloutfields']}')">
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_user']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="username"></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_amount']}</b></td>
	<td class="pformleft" width="20%"><input type="text" name="fine_amount"></td>
  <tr>
	<td class="pformleft" width="10%"><b>{$ibforums->lang['fine_reson']}</b></td>
	<td class="pformleft" width="20%"><textarea name="user_reson" width="30" height="10" cols="40" rows="5"></textarea></td>
  <tr>
	<td class="pformleft" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['fine_member']}"></center></td>
   </tr>
  </tr>
</form>

EOF;
}


function fine_header() {
global $ibforums;
return <<<EOF
  <tr>
	<td class="titlemedium">{$ibforums->lang['fine_date']}</td>
	<td class="titlemedium">{$ibforums->lang['fine_amount']}</td>
	<td class="titlemedium" width="30%">{$ibforums->lang['fine_action']}</td>
	<td class="titlemedium" width="30%">{$ibforums->lang['fine_reson']}</td>
	<td class="titlemedium">{$ibforums->lang['fine_user']}</td>
  </tr>
EOF;
}


function fine_middle($charges) {
global $ibforums;
return <<<EOF

  <tr>
        <td class="pformleft" align="center">{$charges['time']}</td>
        <td class="pformleft" align="center">{$charges['sum']}</td>	
        <td class="pformleft">{$charges['message']}</td>
        <td class="pformleft">{$charges['reason']}</td>
        <td class="pformleft" align="center">{$charges['username']}</td>
  </tr>

EOF;
}


function fine_stats($stats) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformleft" colspan="2">{$ibforums->lang['totalmoney']}</td>
	<td class="pformleft" colspan="3">{$stats['total_value']} {$ibforums->vars['currency_name']}</td>
  </tr>
EOF;
}




function show_users_inventory_header($user) {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}act=quiz&code=do_do_staff_inventory" name="edit" method="post">
  <tr>

	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['icon']}<input type='hidden' name='userid' value='{$user['id']}'></td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_name']}</td>
	<td class="pformstrip" align="center" width="20%">{$ibforums->lang['inventory_desc']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['edit_price']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['edit_delete']}</td>
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
	<td class="pformstrip" width="25%">{$ibforums->lang['member_name']}</td>
	<td class="pformstrip" width="25%">{$ibforums->lang['member_points']}</td>
	<td class="pformstrip" width="25%">{$ibforums->lang['member_deposited']}</td>
	<td class="pformstrip" width="25%">{$ibforums->lang['total_points']}</td>
  </tr>


EOF;
}


function output_overall_stats($member) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" width="25%"><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></td>
	<td class="pformleft" width="25%">{$member['points']}</td>
	<td class="pformleft" width="25%">{$member['deposited']}</td>
	<td class="pformleft" width="25%">{$member['total_points']}</td>	

  </tr>

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
<form action="{$ibforums->base_url}act=quiz&code=do_staff_inventory" name="edit" method="post" onSubmit="return check_edit('{$ibforums->lang['didnotfilloutfields']}')">
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


function output_stats_end() {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" width="100%" colspan="4">&nbsp;</td>
  </tr>	

EOF;
}


function view_inventory_middle($user_inventory) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" align="center" width="10%"><img src="{$ibforums->vars['board_url']}/html/store/icons/{$user_inventory['icon']}"></td>
	<td class="pformleft" align="center" width="20%">{$user_inventory['item_name']}</td>
	<td class="pformleft" align="center" width="20%">{$user_inventory['item_desc']}</td>	
	<td class="pformleft" align="center" width="10%">{$user_inventory['stock']}</td>
	<!--IBS.RESELL_AMOUNT-->
	</td>
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


function category($categorys) {
global $ibforums;
return <<<EOF

  <tr valign="top">
	<td class="pformleft" align="center">{$categorys['cat_name']}</td>
	<td class="pformleft">{$categorys['cat_desc']}</td>
  </tr>

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


function category_header() {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformstrip" align="center">{$ibforums->lang['cat_name']}</td>
	<td class="pformstrip" align="center">{$ibforums->lang['cat_desc']}</td>
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


function nocharges() {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" width="100%" colspan="6" align="center">{$ibforums->lang['nocharges']}</td>
  </tr>
  <tr>
	<td class="pformleft" width="100%" colspan="6">&nbsp;</td>
  </tr>	

EOF;
}


function inventory_middle($user_inventory) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" align="center" width="10%"><img src="{$ibforums->vars['board_url']}/html/store/icons/{$user_inventory['icon']}"></td>
	<td class="pformleft" align="center" width="20%">{$user_inventory['item_name']}</td>
	<td class="pformleft" align="center" width="20%">{$user_inventory['item_desc']}</td>	
	<td class="pformleft" align="center" width="10%">{$user_inventory['stock']}</td>
	<!--IBS.RESELL_AMOUNT-->
	<td class="pformleft" align="center" width="10%"><a href="{$ibforums->base_url}act=quiz&code=useitem&itemid={$user_inventory['i_id']}" onClick="return check('{$ibforums->lang['useitem_check']}')">{$ibforums->lang['use']}</a><br>
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


function output_stats($member) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformleft" width="50%"><a href="{$ibforums->base_url}showuser={$member['id']}">{$member['name']}</a></td>
	<td class="pformleft" width="50%">{$member['points']}</td>
  </tr>

EOF;
}









function list_items($item) {
global $ibforums;
return <<<EOF
  <tr>
	<td class="pformleft" align="center" valign="top">{$item['item_name']}<br><br><img src="{$ibforums->vars['board_url']}/html/store/icons/{$item['icon']}"></td>
	<td class="pformleft" align="justify" valign="top">{$item['item_desc']}</td>
	<td class="pformleft" align="center">{$item['sell_price']}</td>	
	<td class="pformleft" align="center">{$item['stock']}</td>
	<td class="pformleft" align="center">{$item['item_buyitem']}</td>
	<!--Mass Buy Middle-->
  </tr>

EOF;
}


function mass_buy_middle($mass_buy) {
global $ibforums;
return <<<EOF

	<td class="pformleft" align="center">{$mass_buy}</td>

EOF;
}


function item_info() {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['icon']}</td>
	<td class="pformstrip" align="center" width="40%">{$ibforums->lang['item_desc']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['item_price']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['item_stock']}</td>
	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['item_buyitem']}</td>
	<!--Mass Buy Header-->
  </tr>

EOF;
}


function mass_buy_header() {
global $ibforums;
return <<<EOF

	<td class="pformstrip" align="center" width="10%">{$ibforums->lang['mass_buy']}</td>	

EOF;
}


function donateitem($options,$disable="") {
global $ibforums;
return <<<EOF
<!--IBS.CHECK-->

<form action="{$ibforums->base_url}act=quiz&code=donate_item" method="post" onSubmit="return check('{$ibforums->lang['donate_check']}')">
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['donate_itemto']}</td>
	<td class="pformleft" width="20%"><input type="text" name="username" value="{$ibforums->input['name']}"></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['donate_senditemto']}</td>
	<td class="pformleft" width="20%"><select name="item">{$options}</select></td>
  </tr>
  <tr>
	<td class="pformleft" width="10%">{$ibforums->lang['message_to']}</td>
	<td class="pformleft" width="20%"><textarea name="message" width="30" height="10" cols="40" rows="5"></textarea></td>
  <tr>
	<td class="pformleft" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['submit']}" $disable></center></td>
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
	<td class="pformleft" width="30%"><form action="{$ibforums->base_url}act=quiz&code=dobank&type=deposit" name="bank" method="post">
												  <input type="text" name="deposit_amount"></td>
	<td class="pformleft" width="40%"><input type="submit" name="deposit" value="{$ibforums->lang['deposit']}">
												  </form></td>
  </tr>
  <tr>
	<td class="pformleft" width="30%" colspan="2">{$ibforums->lang['withdraw']}</td>
	<td class="pformleft" width="30%"><form action="{$ibforums->base_url}act=quiz&code=dobank&type=withdraw" name="bank" method="post">
												  <input type="text" name="withdraw_amount"></td>
	<td class="pformleft" width="40%"><input type="submit" name="withdraw" value="{$ibforums->lang['withdraw']}">
												  </form></td>
  </tr>
  <tr>
	<td class="pformleft" width="30%">{$ibforums->lang['interest']}</td>
	<td class="pformleft" width="30%">{$ibforums->lang['yougetinterest']}</td>
	<td class="pformleft" width="40%" colspan="2"><form action="{$ibforums->base_url}act=quiz&code=dobank&type=collect" name="bank" method="post">
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
<!--IBS.CHECK-->

<form action="{$ibforums->base_url}act=quiz&code=dodonate_money" method="post" onSubmit="return check('{$ibforums->lang['donate_check']}')">
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
  </tr>
  <tr>
	<td class="pformleft" colspan="2"><center><input type="submit" name="submit" value="{$ibforums->lang['submit']}" $disable></center></td>
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


function next_lastlinks($info)
{
  global $ibforums;
  return <<<EOF

  <tr>
	<td class="pformstrip" align="left" colspan="2"><a href="{$ibforums->base_url}act=quiz&code=shop{$info['category']}&page={$info['last']}">{$ibforums->lang['last']}</a></td>
	<td class="pformstrip" align="center" colspan="2">{$ibforums->lang['showingitems']}</td>
	<td class="pformstrip" align="right" colspan="4"><a href="{$ibforums->base_url}act=quiz&code=shop{$info['category']}&page={$info['next']}">{$ibforums->lang['next']}</a></td>
  </tr>

EOF;
}


function PageTop($data) {
global $ibforums;
return <<<EOF

<div align='left'>Модераторы: {$data['moderators']}</div>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td align='left' width="20%" nowrap="nowrap">{$data['SHOW_PAGES']}{$data['show_all_topics']}</td>
 <td align='right' width="80%">{$data[TOPIC_BUTTON]}{$data[POLL_BUTTON]}</td>
</tr>
</table>

<div class="tableborder">
  <div class='maintitle'><{CAT_IMG}>&nbsp;{$data['name']}</div>
   {$data['modform_open']}
   <table width='100%' border='0' cellspacing='1' cellpadding='4'>
    <tr class='darkrow2'> 
     <td align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
     <td align='center' class='titlemedium'><img src='{$ibforums->vars['img_url']}/spacer.gif' alt='' width='20' height='1'></td>
     <th width='45%' align='left' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_title']}</th>
     <th width='14%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_topic_starter']}</th>
     <th width='7%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_replies']}</th>
     <th width='7%' align='center' nowrap="nowrap" class='titlemedium'>{$ibforums->lang['h_hits']}</th>
     {$data['last_column']}
    </tr>

EOF;
}


function menu_last($links="") {
global $ibforums;
return <<<EOF

  </td>
  <td style="padding:2px"><!-- --></td>
  </td>
  <td id="ucpcontent" valign="top">
  <div class="maintitle">QQMainTitle: {$ibforums->vars['store_name']}</div>
  <table width="100%" border="0" cellspacing="1" cellpadding="4">

EOF;
}


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
		 <a href="{$ibforums->base_url}act=quiz&code=inventory"><b>{$ibforums->lang['myinventory']}</b></a><br>
		 <a href="{$ibforums->base_url}act=quiz&code=bank"><b>{$ibforums->lang['goto_bank']}</b></a><br>
		 <a href="{$ibforums->base_url}act=quiz&code=donate_money"><b>{$ibforums->lang['donate_money']}</b></a><br>
		 <a href="{$ibforums->base_url}act=quiz&code=donate_item"><b>{$ibforums->lang['donate_item']}</b></a>
	</p>
	<div class="pformstrip">{$ibforums->lang['storecategorys']}</div>
	<p>
		 {$links['shop']}
	</p>
	<div class="pformstrip">{$ibforums->lang['menu_stats']}</div>
	<p>
		 {$links['stat']}
		 <a href="{$ibforums->base_url}act=quiz&code=misc_stats"><b>{$ibforums->lang['misc_stats']}</b></a>
	</p>
	<div class="pformstrip">{$ibforums->lang['misclink']}</div>
	<p>
<!-- vot
		 <a href="{$ibforums->base_url}act=quiz&code=quiz"><b>{$ibforums->lang['quiz']}</b></a><br>
vot -->
		 <a href="{$ibforums->base_url}act=quiz&code=post_info"><b>{$ibforums->lang['post_info']}</b></a>
	</p>

EOF;
}


function menu_mod($links) {
global $ibforums;
return <<<EOF

	<div class="pformstrip">{$ibforums->lang['staff_links']}</div>
	<p>
		<b><a href='{$ibforums->base_url}act=quiz&code=fine'>{$ibforums->lang['fine']}</a></b><br>
		<b><a href='{$ibforums->base_url}act=quiz&code=edit_points'>{$ibforums->lang['edit_points']}</a></b><br>
		<b><a href='{$ibforums->base_url}act=quiz&code=edit_inventory'>{$ibforums->lang['edit_inventory']}</a></b><br>
	</p>

EOF;
}

function header_stats($name) {
global $ibforums;
return <<<EOF

  <tr>
	<td class="pformstrip" colspan="2">{$name}</td>
  </tr>
  <tr>
	<td class="pformstrip" width="50%">{$ibforums->lang['member_name']}</td>
	<td class="pformstrip" width="50%">{$ibforums->lang['member_points']}</td>
  </tr>


EOF;
}


function misc_stats($stats) {
global $ibforums;
return <<<EOF
 <tr>
	<td class="pformstrip" width="100%" colspan="2">{$ibforums->lang['miscstats_info']}</td>
 </tr>
 <tr>
	<td class="pformleft" width="50%">{$ibforums->lang['misc_totalpoints']}</td>
	<td class="pformleft" width="25%">{$stats['money']}</td>
 </tr>
 <tr>
	<td class="pformleft" width="50%">{$ibforums->lang['misc_totalbank']}</td>
	<td class="pformleft" width="25%">{$stats['bank']}</td>
 </tr>
 <tr>
	<td class="pformleft" width="50%">{$ibforums->lang['misc_total']}</td>
	<td class="pformleft" width="25%">{$stats['total']}</td>
 </tr>
 <tr>
	<td class="pformleft" width="50%">{$ibforums->lang['misc_totalworth']}</td>
	<td class="pformleft" width="25%">{$stats['item']}</td>
 </tr>
 <tr>
	<td class="pformleft" width="50%">{$ibforums->lang['misc_totalstock']}</td>
	<td class="pformleft" width="25%">{$stats['stock']}</td>
 </tr>


EOF;
}


function make_url($address,$text,$prefix="<b>",$suffix="</b><br>") {
global $ibforums;
return <<<EOF

{$prefix}<a href='{$ibforums->base_url}{$address}'>{$text}</a>{$suffix}

EOF;
}


function member_points() {
global $ibforums;
return <<<EOF

<div class="row4" align="center"><b>{$ibforums->lang['yourpoints']} {$ibforums->vars['currency_name']}: {$ibforums->member['points']}</b></div>

EOF;
}


function post_info() {
global $ibforums;
return <<<EOF

  <tr>
	<td class="titlemedium" width="90%">{$ibforums->lang['post_info']}</td>
	<td class="titlemedium" width="10%">{$ibforums->lang['edit_userspoints']}</td>
  </tr>


  <tr>
	<td class="pformleft">Добавление нового смайла (для каждого скина)</td>
	<td class="pformleft">1</td>
  </tr>
  
  <tr>
	<td class="pformleft">Проработка и графическое оформление нового набора смайлов</td>
	<td class="pformleft">4 - 5</td>
  </tr>
  <tr>
	<td class="pformleft">Разработка логотипа сайта</td>
	<td class="pformleft">5 - 10</td>
  </tr>

  <tr>
	<td class="pformleft">Оформление одного чужого вопроса/ответа для FAQ с исходником и подробными комментариями</td>
	<td class="pformleft">1</td>
  </tr>

  
  <tr>
	<td class="pformleft">Написание и оформление 1 вопроса/ответа в FAQ с исходником и подробными комментариями</td>
	<td class="pformleft">2 - 10</td>
  </tr>
  <tr>
	<td class="pformleft">Написание и оформление новой статьи для сайта/журнала/форума</td>
	<td class="pformleft">2 - 10</td>
  </tr>
  <tr>
	<td class="pformleft">Графическое оформление нового скина для Форума (без программной реализации)</td>
	<td class="pformleft">5 - 10</td>
  </tr>
  <tr>
	<td class="pformleft">Оформление нового скина для Форума с программной и графической реализацией, с адаптацией к существующей версии кода</td>
	<td class="pformleft">10 - 20</td>
  </tr>
  <tr>
	<td class="pformleft">Готовое решение по дизайну сайта</td>
	<td class="pformleft">10 - 20</td>
  </tr>
  <tr>
	<td class="pformleft">Победитель конкурса, соревнования в разделе</td>
	<td class="pformleft">5 - 10</td>
  </tr>
  <tr>
	<td class="pformleft">Обнаружение и закрытие дыры в безопасности сайта и форума</td>
	<td class="pformleft">10</td>
  </tr>
  <tr>
	<td class="pformleft">Программная реализация новой возможности для сайта или форума</td>
	<td class="pformleft">10 - 100</td>
  </tr>
  <tr>
	<td class="pformleft">Премия активным модераторам</td>
	<td class="pformleft">0 - 10</td>
  </tr>
  <tr>
	<td class="pformleft">Премия администраторам</td>
	<td class="pformleft">0 - 10</td>
  </tr>
  <tr>
	<td class="pformleft">&nbsp;</td>
	<td class="pformleft">&nbsp;</td>
  </tr>
EOF;
}


//vot  <tr>
//vot	<td class="pformleft" width="25%">{$ibforums->lang['points_pertopic']}</td>
//vot	<td class="pformleft" width="25%">{$ibforums->vars['pointsper_topic']}</td>
//vot  </tr>
//vot  <tr>
//vot	<td class="pformleft" width="25%">{$ibforums->lang['points_perreply']}</td>
//vot	<td class="pformleft" width="25%">{$ibforums->vars['pointsper_reply']}</td>
//vot  </tr>
//vot  <tr>
//vot	<td class="pformleft" width="25%">{$ibforums->lang['pointsper_poll']}</td>
//vot	<td class="pformleft" width="25%">{$ibforums->vars['pointsper_poll']}</td>
//vot  </tr>
//vot  <tr>    
//vot	<td class="pformleft" width="25%">{$ibforums->lang['what_else']}</td>
//vot	<td><textarea cols='80' rows='15' readonly="readonly" name='Post' class='textinput'>{$ibforums->vars['what_else']}</textarea></td>
//vot  </tr> 

//vot: new html added:


function convert_points($member) {
global $ibforums;
return <<<EOF

<form action="{$ibforums->base_url}act=quiz&code=doconvertpoint" method="post">
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

function ShowTitle($i) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
 <div class="maintitle" align='center'>
 {$ibforums->lang['rep_name']} {$ibforums->lang['user']} <b>{$i['name']}</b>: {$i['rep']} [ +{$i['ups']} | -{$i['downs']} ]
 </div>
 <table width='100%' cellpadding='4' cellspacing='1' border='0'>
  <tr>
EOF;
}

function ShowHeader() {
global $ibforums;
return <<<EOF
	<th align='center' class='pformstrip' width='15%'>{$ibforums->lang['who']}</td>
	<th align='center' class='pformstrip'>{$ibforums->lang['where']}</td>
	<th align='center' class='pformstrip'>{$ibforums->lang['why']}</td>
	<th align='center' class='pformstrip' width='5%'>{$ibforums->lang['code']}</td>
	<th align='center' class='pformstrip' width='15%'>{$ibforums->lang['when']}</td>
	</tr>
EOF;
}

function ShowFooter($link) {
global $ibforums;
return <<<EOF
                <tr>
			<td align='center' colspan='6' class='darkrow1'><a href='$link'>{$ibforums->lang['back']}</a></td>
                </tr>
	</table>
     </div>
EOF;
}

function ShowRow($i) { 
global $ibforums;
return <<<EOF
		<tr>
			<td class='row2' width='15%' align='center'>{$i['name']}</td>
			<td class='row2' width='25%'><a href={$i['url']}>{$i['title']}</a></td>
			<td class='row4'>{$i['message']}</td>
			<td align='center' class='row2' width='5%'><img src='{$i['img']}' border='0'></td>
			<td align='center' class='row4' width='15%'>{$i['date']}{$i['admin_undo']}</td>
		</tr>
EOF;
}


*/

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
  <table width="100%" border="0" cellspacing="1" cellpadding="4">
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





}
?>