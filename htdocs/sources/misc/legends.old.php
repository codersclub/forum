<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Show all emo's / BB Tags module
|   > Module written by Matt Mecham
|   > Date started: 18th April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


$idx = new legends;

class legends {

    var $output    = "";
    var $base_url  = "";
    var $html      = "";

    function legends() {
    
    	//------------------------------------------------------
    	// $is_sub is a boolean operator.
    	// If set to 1, we don't show the "topic subscribed" page
    	// we simply end the subroutine and let the caller finish
    	// up for us.
    	//------------------------------------------------------
    
        global $ibforums, $DB, $std, $print, $skin_universal;
        
        $ibforums->lang    = $std->load_words($ibforums->lang, 'lang_legends', $ibforums->lang_id );

    	$this->html = $std->load_template('skin_legends');
    	
    	$this->base_url        = $ibforums->base_url;
    	
    	
    	
    	//--------------------------------------------
    	// What to do?
    	//--------------------------------------------
    	
    	switch($ibforums->input['CODE'])
    	{

case 'keyb':
    $this->show_keyb();
    break;

    		case 'emoticons':
    			$this->show_emoticons();
    			break;
    			
    		case 'finduser_one':
    			$this->find_user_one();
    			break;
    			
    		case 'finduser_two':
    			$this->find_user_two();
    			break;
    			
    		case 'bbcode':
    			$this->show_bbcode();
    			break;
    			
    		default:
    			$this->show_emoticons();
    			break;
    	}
    	
    	// If we have any HTML to print, do so...
    	
        $print->pop_up_window( $this->page_title, $this->output );
    		
 	}
 	
 	//--------------------------------------------------------------
 	
 	function find_user_one()
 	{
 		global $ibforums, $DB, $std;
 		
 		// entry=textarea&name=carbon_copy&sep=comma
 		
 		$entry = (isset($ibforums->input['entry'])) ? $ibforums->input['entry'] : 'textarea';
 		$name  = (isset($ibforums->input['name']))  ? $ibforums->input['name']  : 'carbon_copy';
 		$sep   = (isset($ibforums->input['sep']))   ? $ibforums->input['sep']   : 'line';
 		
 		$this->output .= $this->html->find_user_one($entry, $name, $sep);
 		
 		$this->page_title = $ibforums->lang['fu_title'];
 		
 	}
 	
 	//--------------------------------------------------------------
 	
 	function find_user_two()
 	{
 		global $ibforums, $DB, $std;
 		
 		$entry = (isset($ibforums->input['entry'])) ? $ibforums->input['entry'] : 'textarea';
 		$name  = (isset($ibforums->input['name']))  ? $ibforums->input['name']  : 'carbon_copy';
 		$sep   = (isset($ibforums->input['sep']))   ? $ibforums->input['sep']   : 'line';
 		
 		//-----------------------------------------
 		// Check for input, etc
 		//-----------------------------------------
 		
 		$ibforums->input['username'] = strtolower(trim($ibforums->input['username']));
 		
 		if ($ibforums->input['username'] == "")
 		{
 			$this->find_user_error('fu_no_data');
 			return;
 		}
 		
 		//-----------------------------------------
 		// Attempt a match
 		//-----------------------------------------
 		
 		$DB->query("SELECT id, name FROM ibf_members WHERE LOWER(name) LIKE '".$ibforums->input['username']."%' LIMIT 0,101");
 		
 		if ( ! $DB->get_num_rows() )
 		{
 			$this->find_user_error('fu_no_match');
 			return;
 		}
 		else if ( $DB->get_num_rows() > 99 )
 		{
 			$this->find_user_error('fu_kc_loads');
 			return;
 		}
 		else
 		{
 			$select_box = "";
 			
 			while ( $row = $DB->fetch_row() )
 			{
 				if ($row['id'] > 0)
 				{
 					$select_box .= "<option value='{$row['name']}'>{$row['name']}</option>\n";
 				}
 			}
 		
 		
 			$this->output .= $this->html->find_user_final($select_box, $entry, $name, $sep);
 		
 			$this->page_title = $ibforums->lang['fu_title'];
 		}
 		
 	}
 	
 	
 	//--------------------------------------------------------------
 	
 	function find_user_error($error)
 	{
 		global $ibforums, $DB, $std;
 		
 		$this->page_title = $ibforums->lang['fu_title'];
 		
 		$this->output = $this->html->find_user_error($ibforums->lang[$error]);
 		
 		return;
 		
 	}
 	


function show_keyb() {
  global $ibforums, $DB, $std;
      
  $this->page_title = 'Русская клавиатура';
       
//  $this->output .= $this->html->page_header('Русская клавиатура' ,'', '<FONT size=1>by SergeS</FONT>');
         
  $this->output .= $this->html->keyb_javascript();
	   
  $this->output .= '</table><DIV id="keys1" style="display:none"><table width = 100% cellspacing = 1 bgcolor=#000000><tr height = 30>';
  
  $keys1 = array('Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','tbr','cap','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','tab','tbr','tab','tab','Я','Ч','С','М','И','Т','Ь','Б','Ю','tab','tab','tbr','tab','spc','tab');

  foreach ( $keys1 as $ckey ) {
    if ($ckey == 'lbr') {
      $this->output .= '</tr><tr height = 30>';
    } elseif ($ckey == 'cap') {
      $this->output .= '<td align=center bgcolor=#FFFFFF width = \'90\'><font size=4><a href="javascript:ShowHide(\'keys1\',\'keys2\')">Caps Lock</A></font></td>';
    } elseif ($ckey == 'spc') {
      $this->output .= '<td align=center bgcolor=#FFFFFF><font size=4><a href=javascript:add_smilie("&nbsp;")>( большой красивый пробел )</A></font></td>';
    } elseif ($ckey == 'tbr') {
      $this->output .= '</tr></table><table width = 100% cellspacing = 1 bgcolor=#000000><tr height = 30>';
    } elseif ($ckey == 'tab') {
      $this->output .= '<td align=center width=30>&nbsp;</td>';
    } else {
      $this->output .= '<td align=center bgcolor=#FFFFFF><font size=4><a href=javascript:add_smilie("' . $ckey . '")>' . $ckey . '</A></font></td>';
    }
  }
					     
  $this->output .= '</tr></table></DIV><table>';
					     
  $this->output .= '</table><DIV id="keys2" style="display:show"><table width = 100% cellspacing = 1 bgcolor=#000000><tr height = 30>';
					       
  $keys2 = array('й','ц','у','к','е','н','г','ш','щ','з','х','ъ','tbr','cap','ф','ы','в','а','п','р','о','л','д','ж','э','tab','tbr','tab','tab','я','ч','с','м','и','т','ь','б','ю','tab','tab','tbr','tab','spc','tab');

  foreach ( $keys2 as $ckey ) {
    if ($ckey == 'lbr') {
      $this->output .= '</tr><tr height = 30>';
    } elseif ($ckey == 'cap') {
      $this->output .= '<td align=center bgcolor=#FFFFFF width = \'90\'><font size=4><a href=javascript:ShowHide("keys1","keys2")>Caps Lock</A></font></td>';
    } elseif ($ckey == 'spc') {
      $this->output .= '<td align=center bgcolor=#FFFFFF><font size=4><a href=javascript:add_smilie("&nbsp;")>( большой красивый пробел )</A></font></td>';
    } elseif ($ckey == 'tbr') {
      $this->output .= '</tr></table><table width = 100% cellspacing = 1 bgcolor=#000000><tr height = 30>';
    } elseif ($ckey == 'tab') {
      $this->output .= '<td align=center width=30>&nbsp;</td>';
    } else {
      $this->output .= '<td align=center bgcolor=#FFFFFF><font size=4><a href=javascript:add_smilie("' . $ckey . '")>' . $ckey . '</A></font></td>';
    }
  }

  $this->output .= '</tr></table></DIV><table>';

  $this->output .= $this->html->page_footer();
}
										    
 	
 	//--------------------------------------------------------------
 	
 	function show_emoticons()
 	{
 		global $ibforums, $DB, $std;
 		
 		$this->page_title = $ibforums->lang['emo_title'];
 		
 		$this->output .= $this->html->emoticon_javascript();
 		
 		$this->output .= $this->html->page_header( $ibforums->lang['emo_title'], $ibforums->lang['emo_type'], $ibforums->lang['emo_img'] );
 		
// Song * smile skin

		if ( !$ibforums->member['id'] ) $id = 1; else
		 if ( $ibforums->input['sskin'] == "" ) $id = $ibforums->member['sskin_id']; else $id = $ibforums->input['sskin'];

		if ( !$id ) $id = 1;

// Song * smile skin

 		$res = $DB->query("SELECT typed,image FROM ibf_emoticons WHERE skid='".$id."'");
			
		if ( $DB->get_num_rows( $res ) )
		{
// SergeS + Song * smile skin

			if ( !$ibforums->member['id'] ) $sskin = 'Main'; else
			 if ( $ibforums->input['sskin'] == "" ) $sskin = $ibforums->member['sskin_name']; else
			  if ( $ibforums->input['sskin'] == 0 ) $sskin = "0"; else
			   {
				$DB->query("SELECT name FROM ibf_emoticons_skins WHERE id='".$ibforums->input['sskin']."'"); 
				if ( $sname = $DB->fetch_row() ) $sskin = $sname['name'];
			   }

			if ( $ibforums->input['sskin'] == "" and $ibforums->member['sskin_id'] == 0 ) $sskin = $ibforums->member['sskin_id'];
			if ( $sskin == "" ) $sskin = "Main";

// SergeS + Song * smile skin

			while ( $r = $DB->fetch_row( $res ) )
			{
				if (strstr( $r['typed'], "&quot;" ) )
				{
					$in_delim  = "'";
					$out_delim = '"';
				} else
				{
					$in_delim  = '"';
					$out_delim = "'";
				}
			
				if ( !$sskin ) $this->output .= $this->html->text_emoticons_row( stripslashes($r['typed']), stripslashes($r['image']), $in_delim, $out_delim ); else
				 $this->output .= $this->html->emoticons_row( stripslashes($r['typed']), stripslashes($r['image']), $sskin, $in_delim, $out_delim );
			}
		}
		
		$this->output .= $this->html->page_footer();
    	
 	}
 	
 	//--------------------------------------------------------------
 	// Show BBCode Helpy file
 	//--------------------------------------------------------------
 	
 	function show_bbcode()
 	{
 		global $ibforums, $DB, $std;
 		
	        require ROOT_PATH."sources/lib/post_parser.php";
 		
 		$this->parser = new post_parser();
 		
 		//-------------------------------------------
 		// Array out or stuff here
 		//-------------------------------------------
 		
 		$bbcode = array(
 		
 		0  => array('[b]', '[/b]', $ibforums->lang['bbc_ex1'] ),
 		1  => array('[s]', '[/s]', $ibforums->lang['bbc_ex1'] ),
 		2  => array('[i]', '[/i]', $ibforums->lang['bbc_ex1'] ),
 		3  => array('[u]', '[/u]', $ibforums->lang['bbc_ex1'] ),
                4  => array('[r]', '[/r]', $ibforums->lang['bbc_ex1'] ),
                5  => array('[c]', '[/c]', $ibforums->lang['bbc_ex1'] ),
                6  => array('[l]', '[/l]', $ibforums->lang['bbc_ex1'] ),
// 		8  => array('[email]', '[/email]', 'user@domain.com' ),
// 		9  => array('[email=user@domain.com]', '[/email]', $ibforums->lang['bbc_ex2'] ),
 		7  => array('[sup]', '[/sup]', 'superscript', 'normal text ' ),
 		8  => array('[sub]', '[/sub]', 'subscript', 'normal text ' ),
                9  => array('[hr]','',''), 		
 		10 => array('[url]', '[/url]', 'http://www.domain.com' ),
 		11 => array('[url=http://www.domain.com]', '[/url]', $ibforums->lang['bbc_ex2'] ),
 		12 => array('[font=times]', '[/font]', $ibforums->lang['bbc_ex1'] ),
 		13 => array('[color=red]', '[/color]', $ibforums->lang['bbc_ex1'] ),
 		14 => array('[size=7]', '[/size]'    , $ibforums->lang['bbc_ex1'] ),
 		15 => array('[img]', '[/img]', $ibforums->vars['board_url'].'/'.$ibforums->vars['img_url'].'/icon11.gif' ),
 		16 => array('[list]', '[/list]', '[*]List Item [*]List Item' ),
 		17 => array('[list=1]', '[/list]', '[*]List Item [*]List Item' ),
 		18 => array('[list=a]', '[/list]', '[*]List Item [*]List Item' ),
 		19 => array('[list=i]', '[/list]', '[*]List Item [*]List Item' ),
 		20 => array('[quote]', '[/quote]', $ibforums->lang['bbc_ex1'] ),
 		21 => array('[user]', '[/user]', 'vot' ),
		22 => array('[pre]','[/pre]','Тег pre позволяет использовать<br>  заранее отформатированный текст<br>    не удаляя лишних пробелов и<br>      знаков табуляций, что, несомненно,<br>        является очень удобной функцией.' ),
 		23 => array('[code]', '[/code]', '$this_var = "Код с подсветкой по умолчанию (может быть не задана)";' ),
 		24 => array('[code=no]', '[/code]', '$this_var = "Код без подсветки";' ),
 		25 => array('[code=no]', '[/code]', '$this_var = 123; // Подсветка части кода
				// {b}красным{/b} цветом."' ),
 		);
 		
		$n = sizeof($bbcode);
                $DB->query("select syntax, description, example from ibf_syntax_list");
                while ($row = $DB->fetch_row())
                {
                        $syntax = $row['syntax'];
                        $example = $row['example'];
                        
                        $bbcode [$n++] = array ('[code='.$syntax.']', '[/code]', $example);
                }

 		$this->page_title = $ibforums->lang['bbc_title'];
 		
 		$this->output .= $this->html->bbcode_header();
 		
 		$this->output .= $this->html->page_header( $ibforums->lang['bbc_title'], $ibforums->lang['bbc_before'], $ibforums->lang['bbc_after'] );
 		
		foreach( $bbcode as $bbc )
		{
			$open    = $bbc[0];
			$content = $bbc[2];
			$close   = $bbc[1];
			$preface    = $bbc[3];

			$before = $this->html->wrap_tag($open).$content.$this->html->wrap_tag($close);
			
			$after = $this->parser->convert( array( 'TEXT' => $open.$content.$close, 'CODE' => 1 ) );

			$after = $this->parser->prepare( array( 'TEXT' => $after, 'CODE' => 1 ) );

			$after = $preface . $after;
			$after = str_replace("&#60;br&#62;", "<br>", $after);

			$before = $preface . $before;
			$before = str_replace("&lt;br&gt;", "<br>", $before);

			$this->output .= $this->html->bbcode_row( $before, stripslashes($after) );
										
		}
		
		$this->output .= $this->html->page_footer();
    	
 	}
 	
 	
        
}

?>





