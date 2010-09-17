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
|   > Show all the members
|   > Module written by Matt Mecham
|   > Date started: 20th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


$idx = new Memberlist;

class Memberlist {

    var $output     = "";
    var $page_title = "";
    var $nav        = array();
    var $html       = "";
    var $base_url   = "";
    var $first       = 0;

#Testudo
    var $max_results = 20;
    var $sort_key    = 'posts';
    var $sort_order  = 'desc';
#/Testudo
    
    var $filter      = 'ALL';
    
    var $mem_titles = array();
    var $mem_groups = array();
    
    
    function Memberlist()
    {
    	global $ibforums, $DB, $std, $print;
    	
    	if ( !$ibforums->input['CODE'] ) $ibforums->input['CODE'] = 'listall';
    	
    	//--------------------------------------------
    	// Require the HTML and language modules
    	//--------------------------------------------
    	
	$ibforums->lang = $std->load_words($ibforums->lang, 'lang_mlist', $ibforums->lang_id );
    	
    	$this->html = $std->load_template('skin_mlist');
    	
    	$this->base_url = $ibforums->base_url;
    	
    	if ( $ibforums->member['g_mem_info'] != 1 )
	{
		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_permission' ) );
    	}
    	
    	$see_groups = array();
    	
    	//--------------------------------------------
    	// Get the member groups, member titles stuff
    	//--------------------------------------------
    	
    	$DB->query("SELECT g_title, g_id, g_icon from ibf_groups WHERE g_hide_from_list <> 1 ORDER BY g_title");
    	
    	while ( $row = $DB->fetch_row() )
    	{
    		$see_groups[] = $row['g_id'];
    		
    		$this->mem_groups[ $row['g_id'] ] = array( 'TITLE'  => $row['g_title'],
 							   'ICON'   => $row['g_icon'],
  							 );
    	}
    	
    	unset($row);
    	
    	$group_string = implode( ",", $see_groups );
    	
    	$DB->free_result();
    	
    	$DB->query("SELECT title, id, posts, pips from ibf_titles ORDER BY posts DESC");
    	
    	while ($row = $DB->fetch_row() )
    	{
    		$this->mem_titles[ $row['id'] ] = array( 'TITLE'   => $row['title'],
    							 'POSTS'   => $row['posts'],
    							 'PIPS'    => $row['pips'],
 												 
    						       );
    	}
    	
    	unset($row);
    	
    	$DB->free_result();
    	
    	$the_filter  = array( 'ALL' => $ibforums->lang['show_all'] );
    	
    	foreach($this->mem_groups as $id => $data)
    	{
    		if ( $id == $ibforums->vars['guest_group'] ) continue;

    		$the_filter[$id] = $data['TITLE'];
    	}
    	
    	//------------------------------------------
    	// Test for input
    	//------------------------------------------
    	
    	if ( isset($ibforums->input['st']) )          
	{
		$this->first = intval($ibforums->input['st']);
	}

    	if ( isset($ibforums->input['max_results']) ) 
	{
		$this->max_results = intval($ibforums->input['max_results']);
	}

    	if ( isset($ibforums->input['sort_key']) )    
	{
		$this->sort_key = $ibforums->input['sort_key'];
	}

    	if ( isset($ibforums->input['sort_order']) )  
	{
		$this->sort_order = $ibforums->input['sort_order'];
	}

    	if ( isset($ibforums->input['filter']) )
	{
		$this->filter = $ibforums->input['filter'];
	}
    	
    	//------------------------------------------
    	// Fix up the search box
    	//------------------------------------------
    	
    	$ibforums->input['name'] = $std->clean_value(trim(urldecode(stripslashes($ibforums->input['name']))));
    	
    	if ( !$ibforums->input['name'] ) $ibforums->input['name_box'] = 'all';
    	
    	//------------------------------------------
    	// Init some arrays
    	//------------------------------------------
    	
    	$the_sort_key = array( 'name'    => 'sort_by_name',
   			       'posts'   => 'sort_by_posts',
    			       'joined'  => 'sort_by_joined',
			       'rep'     => 'sort_by_rep',
			       'points'  => 'sort_by_dgm',
			       'location' => 'sort_by_location',
				'icq_number' => 'sort_by_icq',	// (c) barazuk
  			     );
    						 
    	$the_max_results = array( 10  => '10',
  				  20  => '20',
    				  30  => '30',
    				  40  => '40',
    				  50  => '50',
			    );
    						    
    	$the_sort_order = array(  'desc' => 'descending_order',
 				  'asc'  => 'ascending_order',
    				);
   						   
    	//------------------------------------------
    	// Start the form stuff
    	//------------------------------------------
    						   
    	$filter_html      = "<select name='filter' class='forminput'>\n";
    	$sort_key_html    = "<select name='sort_key' class='forminput'>\n";
    	$max_results_html = "<select name='max_results' class='forminput'>\n";
    	$sort_order_html  = "<select name='sort_order' class='forminput'>\n";
    	
    	foreach ($the_sort_order as $k => $v) {
			$sort_order_html .= $k == $this->sort_order ? "<option value='$k' selected>" . $ibforums->lang[ $the_sort_order[ $k ] ] . "</option>\n"
											            : "<option value='$k'>"          . $ibforums->lang[ $the_sort_order[ $k ] ] . "</option>\n";
		}
     	foreach ($the_filter as $k => $v) {
			$filter_html .= $k == $this->filter  ? "<option value='$k' selected>"         . $the_filter[ $k ] . "</option>\n"
											            : "<option value='$k'>"          . $the_filter[ $k ] . "</option>\n";
		}   	
    	foreach ($the_sort_key as $k => $v) {
			$sort_key_html .= $k == $this->sort_key ? "<option value='$k' selected>"     . $ibforums->lang[ $the_sort_key[ $k ] ] . "</option>\n"
											            : "<option value='$k'>"          . $ibforums->lang[ $the_sort_key[ $k ] ] . "</option>\n";
		}    	
    	foreach ($the_max_results as $k => $v) {
			$max_results_html .= $k == $this->max_results ? "<option value='$k' selected>". $the_max_results[ $k ] . "</option>\n"
											            : "<option value='$k'>"          . $the_max_results[ $k ] . "</option>\n";
		}
		
	$ibforums->lang['sorting_text'] = preg_replace( "/<#FILTER#>/"      , $filter_html."</select>"     , $ibforums->lang['sorting_text'] );
    	$ibforums->lang['sorting_text'] = preg_replace( "/<#SORT_KEY#>/"    , $sort_key_html."</select>"   , $ibforums->lang['sorting_text'] );
    	$ibforums->lang['sorting_text'] = preg_replace( "/<#SORT_ORDER#>/"  , $sort_order_html."</select>" , $ibforums->lang['sorting_text'] );
    	$ibforums->lang['sorting_text'] = preg_replace( "/<#MAX_RESULTS#>/" , $max_results_html."</select>", $ibforums->lang['sorting_text'] );
    	
    	$error = 0;
    	
    	if ( !isset($the_sort_key[ $this->sort_key ]) )       $error = 1;
    	if ( !isset($the_sort_order[ $this->sort_order ]) )   $error = 1;
    	if ( !isset($the_filter[ $this->filter ]) )           $error = 1;
    	if ( !isset($the_max_results[ $this->max_results ]) ) $error = 1;
    	
    	if ( $error )
    	{
    		if ( $ibforums->input['b'] ) 
    		{
    			$std->Error( array( LEVEL=> 1, MSG =>'ml_error') );
    		} else
    		{
    			$std->Error( array( LEVEL=> 5, MSG =>'incorrect_use') );
    		}
    	}
    	
    	//---------------------------------------------
    	// Find out how many members match our criteria
    	//---------------------------------------------
    	
    	$q_extra = "";
    	
    	if ( $this->filter != 'ALL' )
    	{
    		// Are we allowed to see this group?
    		
    		if ( !preg_match( "/(^|,)".$this->filter."(,|$)/", $group_string ) )
    		{
    			$q_extra = " AND m.mgroup IN($group_string)";
    		} else
    		{
    			$q_extra = " AND m.mgroup='".$this->filter."' ";
    		}
    	}
    	
/* commented & modified by barazuk
    	if ( $ibforums->input['name_box'] != 'all' )
    	{
		if ($this->sort_key == 'location')
		{
	    		if ( $ibforums->input['name_box'] == 'begins' )
	    		{
	    			$q_extra .= " AND m.location LIKE '".$ibforums->input['name']."%'";
	    		} else
	    		{
	    			$q_extra .= " AND m.location LIKE '%".$ibforums->input['name']."%'";
	    		}
		} else
		{
	    		if ( $ibforums->input['name_box'] == 'begins' )
	    		{
	    			$q_extra .= " AND m.name LIKE '".$ibforums->input['name']."%'";
	    		} else
	    		{
	    			$q_extra .= " AND m.name LIKE '%".$ibforums->input['name']."%'";
	    		}
		}
    	}
*/
    	
	if ( $ibforums->input['name_box'] != 'all' )
	{
	  if ($this->sort_key == 'location') 
	    $q_extra .= " AND m.location LIKE ";
	  else if ($this->sort_key == 'icq_number') 
	    $q_extra .= " AND m.icq_number LIKE ";
	  else
	   $q_extra .= " AND m.name LIKE ";

	  if ( $ibforums->input['name_box'] == 'begins' ) 
	    $q_extra .= "'".$ibforums->input['name']."%'";

	  if ( $ibforums->input['name_box'] == 'contains' ) 
	    $q_extra .= "'%".$ibforums->input['name']."%'";
	}




    	if ( $ibforums->input['photoonly'] )
    	{
    		$DB->query("SELECT COUNT(m.id) as total_members
			    FROM ibf_members m
			    LEFT JOIN ibf_member_extra me
				ON me.id=m.id
			    WHERE me.photo_location <> '' AND m.id > 0".$q_extra);
    		
    		$q_extra .= " AND me.photo_location <> ''";
    	} else
    	{
	    	$DB->query("SELECT COUNT(m.id) as total_members FROM ibf_members m WHERE m.id > 0".$q_extra);
	}
	    
	$max = $DB->fetch_row();
	
	$DB->free_result();
	
	$links = $std->build_pagelinks(  array( 'TOTAL_POSS'  => $max['total_members'],
						'PER_PAGE'    => $this->max_results,
						'CUR_ST_VAL'  => $this->first,
						'L_SINGLE'     => "",
						'L_MULTI'      => $ibforums->lang['pages'],
						'BASE_URL'     => $this->base_url."&amp;act=Members&amp;photoonly={$ibforums->input['photoonly']}&amp;name=".urlencode($ibforums->input['name'])."&amp;name_box={$ibforums->input['name_box']}&amp;max_results={$this->max_results}&amp;filter={$this->filter}&amp;sort_order={$this->sort_order}&amp;sort_key={$this->sort_key}"
					  )
				   );
								   
	$this->output = $this->html->start();
								   
	$this->output .= $this->html->Page_header( array( 'SHOW_PAGES' => $links) );  
	
	//-----------------------------
	// START THE LISTING
	//-----------------------------
        if ($this->sort_key == 'points') {
		$this->sort_key = 'fined';
	}
	$DB->query("SELECT m.name, m.id, m.posts, m.rep, m.points, m.fined,
			   m.joined, m.mgroup, m.email,m.title, m.hide_email,
			   m.location, m.aim_name, m.icq_number,
	                   me.photo_location, me.photo_type,
			   me.photo_dimensions, m.location, p.field_1 
		    FROM ibf_members m
			LEFT JOIN ibf_member_extra me ON (me.id=m.id)
			LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id)
			LEFT JOIN ibf_pfields_content p ON (m.id=p.member_id) 
		    WHERE m.id > 0".$q_extra." AND g.g_hide_from_list <> 1
			ORDER BY m.".$this->sort_key." ".$this->sort_order."
			LIMIT ".$this->first.",".$this->max_results);
	
	while( $member = $DB->fetch_row() ) {
		$pips = 0;
		
		foreach($this->mem_titles as $k => $v) {
			if ( $member['posts'] >= $v['POSTS'] ) {
				$member['title'] = $this->mem_titles[ $k ]['TITLE'];

				$pips = $v['PIPS'];
				break;
			}
		}
		
		if ( $this->mem_groups[ $member['mgroup'] ]['ICON'] ) {
			$member['gicon'] = "<img src='{$ibforums->vars[TEAM_ICON_URL]}/{$this->mem_groups[ $member['mgroup'] ][ICON]}' border='0'> ";
		}

		if ( $pips ) {
			if ( preg_match( "/^\d+$/", $pips ) ) {
				for ($i = 1; $i <= $pips; ++$i)	$member['pips'] .= "<{A_STAR}>";

			} else $member['pips'] = "<img src='{$ibforums->vars[TEAM_ICON_URL]}/$pips' border='0'>";
		}

		// Song * sex
		if ( $member['field_1'] == 'f' ) {
			$member['sex'] = "<img src='{$ibforums->vars[TEAM_ICON_URL]}/fem.gif' border='0'> ";

		} else $member['sex'] = "";

		$member['joined'] = $std->get_date( $member['joined'], 'JOINED' );
		
		$member['group']  = $this->mem_groups[ $member['mgroup'] ]['TITLE'];
		
		
		if ( !$member['hide_email'] ) {
			$member['member_email'] = "<a href='{$this->base_url}act=Mail&amp;CODE=00&amp;MID={$member['id']}'><{P_EMAIL}></a>";
		} else {
			$member['member_email'] = '&nbsp;';
		}
		
		if ( $member['icq_number'] ) {
			$member['icq_status'] = "<a href=http://wwp.icq.com/{$member['icq_number']}#pager target='_blank' title='{$member['icq_number']}' alt='{$member['icq_number']}'><img src=http://online.mirabilis.com/scripts/online.dll?icq={$member['icq_number']}&amp;img=5 width=18 height=18 border=0 align=top></a>"; 
			$member['icq_icon'] = "<a href='http://wwp.icq.com/scripts/search.dll?to={$member['icq_number']}'><{P_ICQ}></a>";
		} else {
			$member['icq_status'] = "";
			$member['icq_icon'] = "";
			$member['icq_number'] = "";
		}

		if ( $member['aim_name'] ) {
			$member['aim_name'] = "<a href=\"javascript:PopUp('{$this->base_url}act=AOL&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>";
		} else {
			$member['aim_name'] = '&nbsp;';
		}
		
		if ( $member['photo_type'] and $member['photo_location'] ) {
			$member['camera'] = "<a href=\"javascript:PopUp('{$this->base_url}act=Profile&amp;CODE=showphoto&amp;MID={$member['id']}','Photo','200','250','0','1','1','1')\"><{CAMERA}></a>";
		}
		
		// do safe
		$member['password'] = "";
		
		$member['posts'] = $std->do_number_format($member['posts']);

		if ( intval($member['rep']) > 0 ) {
			$member['rep'] = "<a href='{$ibforums->vars['board_url']}/index.php?act=rep&amp;CODE=03&amp;type=t&amp;mid={$member['id']}'>".$member['rep']."</a>";
		}
		
		if ( intval($member['fined']) > 0 ) {
			$member['fined'] = "<a href='{$ibforums->vars['board_url']}/index.php?act=store&amp;code=showfine&amp;id={$member['id']}'>".$member['fined']."</a>";
		}
		

		$this->output .= $this->html->show_row($member);
	}
	
	$checked = $ibforums->input['photoonly'] == 1 ? 'checked="checked"' : "";
		
	$this->output .= $this->html->Page_end( $checked );
		
	$this->output .= $this->html->end( array( 'SHOW_PAGES' => $links) );
    	
    	$print->add_output("$this->output");
        $print->do_output( array( 'TITLE' => $ibforums->lang['page_title'], 'JS' => 0, NAV => array( $ibforums->lang['page_title'] ) ) );

 	}
 	
}

?>
