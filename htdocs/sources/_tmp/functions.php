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
|   > Multi function library
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/



class FUNC {

	var $time_formats  = array();
	var $time_options  = array();
	var $offset        = "";
	var $offset_set    = 0;
	var $num_format    = "";
	var $allow_unicode = 0;
	var $get_magic_quotes = 0;


	//----------------------------------------
	// Set up some standards to save CPU later
	
	function FUNC() {
	global $INFO;
		
	$this->time_options = array(
					'JOINED' => $INFO['clock_joined'],
					'SHORT'  => $INFO['clock_short'],
					'LONG'   => $INFO['clock_long'],
					'MERGE'  => 'H:i'
				   );

	$this->num_format = ($INFO['number_format'] == 'space') ? ' ' : $INFO['number_format'];
	
	$this->get_magic_quotes = get_magic_quotes_gpc();
		
	}





	// Shaman * get/set forums open state

	function get_board_visibility($board_id, $is_forum) {
	global $DB, $sess;

	  if ( !$sess->member['id'] ) return 1;

	  $visible = $visible ? 1 : 0;
	  $is_forum = $is_forum ? 1 : 0;

	  $query = "SELECT is_visible
		    FROM ibf_boards_visibility
		    WHERE 
		    	id={$board_id} AND
		    	is_forum={$is_forum} AND
	   	    	user_id={$sess->member['id']}";
	  $DB->query($query);
	  $result = $DB->fetch_row();
	  if ( !$result ) 
	   {
	     $result = $is_forum ? 0 : 1;
	     $query =  "INSERT INTO ibf_boards_visibility
			VALUES ({$board_id},{$is_forum},{$result},{$sess->member['id']})";
	     $DB->query($query);
	   } else $result = $result['is_visible'];
	  return $result;
	 }

	function set_board_visibility($board_id, $is_forum, $visible) {
	 global $DB, $sess;


	  $visible = $visible ? 1 : 0;
	  $is_forum = $is_forum ? 1 : 0;

	  if ( !$sess->member['id'] ) return;

	  $vis = $this->get_board_visibility($board_id, $is_forum);
	  if ($vis == $visible) return;


	  $query = "UPDATE ibf_boards_visibility
		    SET is_visible={$visible}
		    WHERE 
			id={$board_id} AND 
			is_forum={$is_forum} AND 
			user_id={$sess->member['id']}";
	  $DB->query($query);
	 }




//---------------------------------------------------------------
// POST functions
//---------------------------------------------------------------

        function mod_tag_exists($post, $gm_on = 0, $mm_on = 1) {

	return ( ( $gm_on and preg_match("#(?!\\[CODE.*?\\])\\[GM\\](.*?)\\[/GM\\](?!\\[/CODE\\])#i", $post) ) or
		 ( $mm_on and preg_match("#(?!\\[CODE.*?\\])\\[MM\\](.*?)\\[/MM\\](?!\\[/CODE\\])#i", $post) ) or
                 preg_match("#(?!\\[CODE.*?\\])\\[EX\\](.*?)\\[/EX\\](?!\\[/CODE\\])#i", $post) or
                 preg_match("#(?!\\[CODE.*?\\])\\[MOD\\](.*?)\\[/MOD\\](?!\\[/CODE\\])#i", $post)
	       );

	}


	//-------------------------------------
	// Song * delayed time, 03.05.05
	function delayed_time($post, $days_off, $to_do = 0, $moderator = array()) {
	global $ibforums;

	if ( !$days_off and !$ibforums->vars['default_days_off'] )
	{
		return 0;
	}

	$result = ( $to_do or 
		    $this->mod_tag_exists($post) or
		    ( $ibforums->input['offtop'] and 
		      $days_off and 
		      ( $moderator['delete_post'] or 
		        $ibforums->member['g_is_supmod'] or 
		        $ibforums->member['g_delay_delete_posts'] 
		      ) 
		    ) 
		  ) ? time() + ( ( !$days_off ) ? $ibforums->vars['default_days_off'] : $days_off)*60*60*24 : 0;

	return $result;

	}




	// Song * hide parts of post, 27.03.05

        function regex_moderator_message($message) {
	global $ibforums;
	
	if ( !$message or !$ibforums->member['id'] ) return "";
	if ( !( $ibforums->member['is_mod'] or $ibforums->member['g_is_supmod'] ) ) return "";
                                                        
	return "[MM]{$message}[/MM]";

	}


        function regex_global_moderator_message($message) {
	global $ibforums;
	
	if ( !$ibforums->member['g_is_supmod'] or !$message ) return "";
                                                        
	return "[GM]{$message}[/GM]";

	}


	function do_post($post = "") {

	if ( !$post ) return "";
	
	$post = preg_replace( "#\[mm\](.+?)\[/mm\]#ies", "\$this->regex_moderator_message('\\1')", $post);
	$post = preg_replace( "#\[gm\](.+?)\[/gm\]#ies", "\$this->regex_global_moderator_message('\\1')", $post);

	return $post;

	}



	// Song * premoderation, 16.03.05

	function premod_rights($mid, $queued = 0, $resolve = 0) {
	global $ibforums;

	$resolve = 0;
		
	if ( !$ibforums->member['g_is_supmod'] )
	{
		if ( !$ibforums->member['is_mod'] and
		      $mid != $ibforums->member['id'] )
		{
			return FALSE;
		}

		if ( $ibforums->member['is_mod'] )
		 if ( $queued )
		 {
			$resolve = 1;
			
	 	 } elseif ( $mid != $ibforums->member['id'] ) 
		 {
			return FALSE; 
		 }
	} else
	{
		$resolve = 1;
	}

	return TRUE;

	}
	



	// Song * Old Topics Flood, 15.03.05

	function user_reply_flood($start_date = 0) {
	global $ibforums;

	if ( $ibforums->member['g_days_ago'] and
	     $start_date and
	     $start_date < (time() - intval($ibforums->member['g_days_ago'])*60*60*24) )
	{
		return TRUE;
	}

	return FALSE;

	}




	// Song * user ban function

	function user_ban_check( $forum = array() ) {
	global $DB, $ibforums;

	if ( !$forum['id'] or $ibforums->member['is_mod'] ) return;

	$DB->query("SELECT temp_ban
		    FROM ibf_preview_user
		    WHERE
			mid='".$ibforums->member['id']."' and
			(fid='".$forum['id']."' or fid='".$forum['parent_id']."')");

	if ( $DB->get_num_rows() )
	{
		$ban = $DB->fetch_row();

		if ( $ban['temp_ban'] ) 			
		{
			if ( $ban['temp_ban'] == 1 ) $this->Error( array(
						LEVEL => 1,
						MSG => 'no_view_forum_always') ); else
			{
				// Game over man! :))
				$ban_arr = $this->hdl_ban_line($ban['temp_ban']);

				if ( time() >= $ban_arr['date_end'] )
				{
					// You're free :((
					$DB->query("UPDATE ibf_preview_user
						    SET temp_ban=NULL
						    WHERE
							mid='".$ibforums->member['id']."' and
							(fid='".$forum['id']."' or
							 fid='".$forum['parent_id']."')");

					// delete row of user if there is nothing remaining for him
					$DB->query("DELETE
						    FROM ibf_preview_user
						    WHERE
							IsNULL(mod_posts) and 
							IsNULL(restrict_posts) and 
							IsNULL(temp_ban)");

					  // Gi-gi-gi! Hui tebe! :)
				} else $this->Error( array(
					LEVEL => 1,
					MSG => 'no_view_forum',
					'EXTRA' => $this->get_date($ban_arr['date_end'], 'LONG')) );
			}
		}
	}

	}



	// Song * ip control

	function ip_control($forum, $ip) {
	global $ibforums, $DB;

	if ( !$forum['id'] or
	     !$ip or
	     $ibforums->member['g_avoid_q'] ) return 0;

	$ip = explode(".", $ip);

	if ( !count($ip) ) return 0;

	$DB->query("SELECT id
		    FROM ibf_ip_table
		    WHERE 
			(ok1='".$ip[0]."' or ok1='*') and 
			(ok2='".$ip[1]."' or ok2='*') and 
			(ok3='".$ip[2]."' or ok3='*') and 
			(ok4='".$ip[3]."' or ok4='*') and 
			(fid='0' or fid='".$forum['id']."' or
			 fid='".$forum['parent_id']."')
		    LIMIT 1");

	return $DB->get_num_rows();

	}



	// Song * recount member reputation, 15.01.05

	function rep_recount($mid = 0) {
	global $DB;

	if ( !$mid ) return;

	// first ratting
	$DB->query("SELECT
			COUNT(r.msg_id) AS raise
		    FROM
			ibf_reputation r,
			ibf_forums f 
		    WHERE
			r.forum_id=f.id and
			f.inc_postcount=1 and
			r.member_id='".$mid."' AND
			r.CODE='01'");
	$row = $DB->fetch_row();
	$plus = $row['raise'];

	$DB->query("SELECT
			COUNT(r.msg_id) AS lower
		    FROM
			ibf_reputation r,
			ibf_forums f 
		    WHERE
			r.forum_id=f.id and
			f.inc_postcount=1 and
			r.member_id='".$mid."' AND
			r.CODE='02'");
	$row = $DB->fetch_row();
	$minus = $row['lower'];

	$rep = $plus - $minus;
	$DB->query("UPDATE ibf_members
		    SET rep='".$rep."'
		    WHERE id ='".$mid."'");


	// second ratting
	$DB->query("SELECT
			COUNT(r.msg_id) AS raise
		    FROM
			ibf_reputation r,
			ibf_forums f 
		    WHERE
			r.forum_id=f.id and
			f.inc_postcount=0 and
			r.member_id='".$mid."' AND
			r.CODE='01'");
	$row = $DB->fetch_row();
	$plus = $row['raise'];

	$DB->query("SELECT
			COUNT(r.msg_id) AS lower
		    FROM
			ibf_reputation r,
			ibf_forums f 
		    WHERE
			r.forum_id=f.id and
			f.inc_postcount=0 and
			r.member_id='".$mid."' AND
			r.CODE='02'");
	$row = $DB->fetch_row();
	$minus = $row['lower'];

	$rep = $plus - $minus;
	$DB->query("UPDATE ibf_members
		    SET ratting='".$rep."'
		    WHERE id='".$mid."'");


	}



	// Song * update forums order cache, 19.12.04

        function select_parent($id) {
        global $DB;

	$DB->query("SELECT
			IF (parent_id = -1, 0, parent_id) as parent_id
		    FROM ibf_forums
		    WHERE id='".$id."'");

	return $DB->fetch_row();

	}
	
	function do_update( $id,$pid ) {
	global $DB;

	$DB->query("INSERT INTO ibf_forums_order
		    VALUES ($id,$pid)");

	}

	function update_forum_order_cache( $id,$pid )  {

		if ( !$id ) return;

		$this->do_update($id,$pid);

		while ( $row = $this->select_parent($pid) )
		{
			$pid = $row['parent_id'];
			$this->do_update($id,$pid);
		}

	}




	// Song * forum filter, 19.11.2004, 19.12.2004 (with endless depth)

	function subforums_addtorow($result, $children, $id, $level) {

	  if ( isset($children[ $id ]) and count($children[ $id ] ) > 0 ) 
	  {
		foreach( $children[ $id ] as $idx => $child )
		{
			if ( !isset($result[ $child['id'] ]) )
			{
				$prefix = "";

				// visuality depth
				for ($i = 0; $i < $level; $i++) $prefix .= "---";

				$child['name'] = $prefix." ".$child['name'];

				$result[ $child['id'] ] = $child;

				$result = $this->subforums_addtorow($result, $children, $child['id'], $level + 1);
			}
		}
	  }

	  return $result;

	}

	function fill_array($row, $forums, $children, $total_list) {

	if ( $row['parent_id'] > 0 ) 
	{
		$children[ $row['parent_id'] ][ $row['id'] ] = $row;
	} else
	{
		$forums[ $row['id'] ] = $row;
	}

	$total_list[ $row['id'] ] = $row['name'];

	}

	function check_forum( $row = array() ) {
	global $ibforums;

	// check rights
	if ( $this->check_perms( $row['read_perms'] ) != TRUE ) return FALSE;

	// check "passworded" forums
	if ( $row['password'] and $_COOKIE[ $ibforums->vars['cookie_id']."iBForum".$row['id'] ] != $row['password'] )
	{
		return FALSE;
	}

	return TRUE;

	}

	function forums_array( $id, $current, $forums, $children, $forums_list = array() ) {
	global $DB;

	$result = array();

	if ( $id )
	{	
		// querying upper forum
		if ( $id != $current['id'] )
		{
			$DB->query("SELECT *
				    FROM ibf_forums
				    WHERE id='".$id."'");

			$main = $DB->fetch_row();
		} else
		{
			$main = $current;
		}

		// have we access to it ?
		if ( $main and $this->check_forum($main) )
		{
			// querying parent forums
			$DB->query("SELECT f.*
				    FROM
					ibf_forums f,
					ibf_forums_order fo 
				    WHERE
					fo.pid='".$id."' and
					f.id=fo.id 
				    ORDER BY f.position");

			if ( $DB->get_num_rows() > 0 )
			{
				// at first include upper forum
				$this->fill_array($main, &$forums, &$children, &$forums_list);

				// collect parent forums
				while ( $row = $DB->fetch_row() ) if ( $this->check_forum($row) )
				{
					// check rights
					$this->fill_array($row, &$forums, &$children, &$forums_list);
				}
			}

			// combine data to one array
			foreach( $forums as $row )
			{	
				if ( !isset($result[ $row['id'] ]) ) 
				{
					$result[ $row['id'] ] = $row;

					$result = $this->subforums_addtorow($result, $children, $row['id'], $main['sub_can_post']);
				}
			}
		}
	}

	return $result;

	}

	function menu_row($value, $current, $label, $mode = "") {

	if ( $value == $current and ( $mode == "" or $mode == 0 ) )
	{
		$selected = " selected='selected'";
	}

	return "<option value='$value'$selected>{$label}</option>\n";

	}

	function forum_filter( $forum = array(), $forums_id = array(), $mode = 0, $pid ) {
	global $ibforums, $skin_universal;

	$k = count($forums_id);

	if ( $k > 1 or ( $k == 1 and $forums_id[ 0 ]['parent_id'] != -1 ) ) 
	{
		$forums = "<form name='forummenu' method='get'>\n";

		$forums .= "<select name='f' class='forminput'>\n";

		$forums .= $this->menu_row(-1, $mode, $ibforums->lang['see_forums']);

		$forums .= $this->menu_row(-2, $mode, $ibforums->lang['all_forums']);

		foreach( $forums_id as $row )
		{
			if ( $row['parent_id'] == -1 and !$row['sub_can_post'] ) continue;

			$rtime = $row['last_post'];

			$ftime = ( $ibforums->member['board_read'] > $ibforums->forum_read[ $row['id'] ] ) 
					? $ibforums->member['board_read'] 
					: $ibforums->forum_read[ $row['id'] ];

			$new = ( $ftime < $rtime ) ? $ibforums->lang['is_new'] : "&nbsp;&nbsp;&nbsp;";

			$forums .= $this->menu_row($row['id'], $forum['id'], $new.$row['name'], $mode);
		}

		$forums .= "</select>&nbsp;<input type='button' value='{$ibforums->lang['jmp_go']}' class='forminput' onClick='do_url({$pid});'></form>";

		$forums = $skin_universal->forum_filter($forums);

		return $forums;
	}

	return "";

	}




	// Song * get syntax highlight id

	function get_highlight_id($id) {
	global $DB;

	$DB->query("SELECT forum_highlight,highlight_fid,parent_id FROM ibf_forums WHERE id='".$id."'");

	if ( !$row = $DB->fetch_row() or !$row['forum_highlight'] ) return -1;

	if ( $row['highlight_fid'] == -1 and $row['parent_id'] != -1 )
	{
		$DB->query("SELECT forum_highlight,highlight_fid FROM ibf_forums WHERE id='".$row['parent_id']."'");

		if ( !$row = $DB->fetch_row() or !$row['forum_highlight'] or !$row['highlight_fid'] or $row['highlight_fid'] == -1 )
		{
			return -1;
		}
	} 

	return $row['highlight_fid'];

	}




	// Song * who was today online

	function current_day()
	{ 

		$arr = getdate( time() );
		return $arr['mday'];
	
	}

	function current_month()
	{ 

		$arr = getdate( time() );
		return $arr['mon'];
	
	}

	function yesterday_day($days_down = 1) {

		$arr = getdate( time() - 3600 * 24 * $days_down );
		return $arr['mday'];

	}

	function yesterday_month($days_down = 1) {

		$arr = getdate( time() - 3600 * 24 * $days_down );
		return $arr['mon'];

	}

	function inc_user_count($field, $day, $month) {
	global $DB;

	if ( !$field or !$day or !$month) return;

	$DB->query("UPDATE ibf_users_stat
		    SET ".$field."=".$field."+1
		    WHERE
			day='".$day."' and
			month='".$month."'");

	// if no changed
	if ( !$DB->get_affected_rows() )
	{
		// add new day
		$DB->query("INSERT
			    INTO ibf_users_stat
				(day,month)
			    VALUES (".$day.",".$month.")");

		// and try again
		$this->inc_user_count($field, $day, $month);
	}

	}

	function who_was_member( $mid ) {
	global $DB;

	if ( !$mid ) return;

	// current day
	$cur_day = $this->current_day();

	// current month
	$cur_mon = $this->current_month();

	// pass future error
	$DB->return_die = 1;

	$DB->query("INSERT
		    INTO ibf_m_visitors
		    VALUES (".$mid.",".$cur_day.",".$cur_mon.")");

	if ( !$DB->error ) $this->inc_user_count('members', $cur_day, $cur_mon);

	// return
	$DB->return_die = 0;

	}

	function who_was_guest_or_bot( $table, $field, $session_id, $ip_address) {
	global $DB;

	// current day
	$cur_day = $this->current_day();

	// current month
	$cur_mon = $this->current_month();

	// pass future error
	$DB->return_die = 1;

	$DB->query("INSERT
		    INTO ".$table."
		    VALUES ('".$session_id."','".$ip_address."',".$cur_day.",".$cur_mon.")");

	if ( !$DB->error ) $this->inc_user_count($field, $cur_day, $cur_mon);

	// return
	$DB->return_die = 0;

	}




	// Song * code tag button

	function code_tag_button($hid = 0) {
	global $DB;
	                        
	$syntax_html = "<IBF_SONG_BUTTON>";
	$syntax_html .= "<select name='syntax' class='codebuttons' onchange=\"alterfont(this.options[this.selectedIndex].value, 'CODE')\">";
	$syntax_html .= "<option value='-1'>CODE</option>";

	$DB->query("SELECT
			id,
			syntax,
			syntax_description
		    FROM ibf_syntax_list");
	if ( !$DB->get_num_rows() ) return "";

	$code = "";

	while ( $syntax = $DB->fetch_row() )
	{
		if ( $hid == $syntax['id'] ) $code = $syntax['syntax'];

		$syntax_html .= "<option value='{$syntax['syntax']}'>{$syntax['syntax_description']}</option>";
	}

// vot: BAD Message!!!

	$syntax_html .= "<option value='no'>Без подсветки</option>";

	$syntax_html .= "</select>";

	if ( $code ) 
	{
		$code = "<input type='button' value='CODE={$code}' onclick=\"alterfont('{$code}','CODE')\" class='codebuttons'> ";
	}

	$syntax_html = str_replace("<IBF_SONG_BUTTON>", $code, $syntax_html);

	return $syntax_html;

	}




	// Song * delete members

	function delete_members($ids) {
	global $ibforums, $DB;

                $DB->query("UPDATE ibf_posts
			    SET author_id='0'
			    WHERE author_id".$ids);

                $DB->query("UPDATE ibf_topics
			    SET starter_id='0'
			    WHERE starter_id".$ids);

		$DB->query("DELETE
			    FROM ibf_members
			    WHERE id".$ids);

		$DB->query("DELETE FROM ibf_reputation
			    WHERE member_id".$ids);

        	$DB->query("DELETE FROM ibf_pfields_content
			    WHERE member_id".$ids);

            	$DB->query("DELETE FROM ibf_member_extra
			    WHERE id".$ids);

                $DB->query("DELETE FROM ibf_messages
			    WHERE member_id".$ids);

                $DB->query("DELETE FROM ibf_contacts
			    WHERE member_id".$ids);

		$DB->query("DELETE FROM ibf_contacts
			    WHERE contact_id".$ids);

 	        $DB->query("DELETE FROM ibf_tracker
			    WHERE member_id".$ids);

                $DB->query("DELETE FROM ibf_forum_tracker
			    WHERE member_id".$ids);

                $DB->query("DELETE FROM ibf_warn_logs
			    WHERE wlog_mid".$ids);

		$DB->query("DELETE FROM ibf_validating
			    WHERE member_id".$ids);

		$DB->query("DELETE FROM ibf_boards_visibility
			    WHERE user_id".$ids);

		$DB->query("DELETE FROM ibf_log_forums
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_log_topics
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_ip_table
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_moderators
			    WHERE member_id".$ids);

		$DB->query("DELETE FROM ibf_preview_user
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_syntax_access
			    WHERE member_id".$ids);

		$DB->query("DELETE FROM ibf_warnings
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_voters
			    WHERE member_id".$ids);

		$DB->query("DELETE FROM ibf_check_members
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_search_forums
			    WHERE mid".$ids);

		$DB->query("DELETE FROM ibf_search_results
			    WHERE member_id".$ids);

		// Set the stats DB straight.
		$DB->query("SELECT
				id,
				name
			    FROM ibf_members
			    WHERE mgroup<>'".$ibforums->vars['auth_group']."' 
			    ORDER BY joined DESC
			    LIMIT 1");

		$memb = $DB->fetch_row();
		
		$DB->query("SELECT COUNT(id) as members
			    FROM ibf_members
			    WHERE mgroup<>'".$ibforums->vars['auth_group']."'");

		$r = $DB->fetch_row();

		// Remove "guest" account...
		$r['members']--;
		$r['members'] < 1 ? 0 : $r['members'];
		
		$DB->query("UPDATE ibf_stats SET 
				MEM_COUNT='".$r['members']."',
				LAST_MEM_NAME='".$memb['name']."',
				LAST_MEM_ID='".$memb['id']."'");
	}




	// Song * send pm func, updated 24.12.04

	function sendpm($sendto, $message, $title, $sender_id="", $popup=0, $do_send=0, $fatal = 1) {
	global $DB, $ibforums;

	$sendto = intval($sendto);

	$sender_id = intval($sender_id);
	if ( !$sender_id ) $sender_id = $ibforums->member['id'];

	if ( !$sendto or !$sender_id ) return 0;

	$db_string = $this->compile_db_string( array( 

			 'member_id'      => $sendto,
			 'msg_date'       => time(),
			 'read_state'     => '0',
			 'title'          => addslashes($this->clean_value($title)),
			 'message'        => addslashes($this->remove_tags($this->clean_value($message))),
			 'from_id'        => $sender_id,
			 'vid'            => 'in',
			 'recipient_id'   => $sendto,
			 'tracking'       => 0,

				)      );

	$DB->query("INSERT INTO ibf_messages
			(".$db_string['FIELD_NAMES'].")
		    VALUES
			(".$db_string['FIELD_VALUES'].")", 0, $fatal);
	$message_id = $DB->get_insert_id();

	$db_string = array();

	if ( $popup ) $extra = ",show_popup=1";

	$DB->query("UPDATE ibf_members
		    SET
			msg_total = msg_total + 1,
			new_msg = new_msg + 1,
			msg_from_id='".$sender_id."',
			msg_msg_id='".$message_id."'
			{$extra} 
	            WHERE id='".$sendto."'
		    LIMIT 1", 0, $fatal);


	$to_member = array();

	$DB->query("SELECT
			name,
			email_pm,
			language,
			email,
			disable_mail,
			mgroup
		    FROM ibf_members
		    WHERE id='".$sendto."'", 0, $fatal);

	if ( 
	     $to_member = $DB->fetch_row() and 
	     $to_member['mgroup'] != $ibforums->vars['auth_group'] and 
             !$to_member['disable_mail'] and 
	     ($to_member['email_pm'] or $do_send)

           ) 
	{
		require_once $ibforums->vars['base_dir']."sources/lib/emailer.php";
		$email = new emailer();

		$to_member['language'] = $to_member['language'] == "" ? 'en' : $to_member['language'];
	
		$email->get_template("pm_notify", $to_member['language']);

		if ( $sender_id != $ibforums->member['id'] )
		{
			$DB->query("SELECT name
				    FROM ibf_members
				    WHERE id='".$sender_id."'", 0, $fatal);

			if ( $member = $DB->fetch_row() ) $name = $member['name']; else return $message_id;
		} else 
		{
			$name = $ibforums->member['name'];
		}

		$email->build_message( array(
				'NAME'      => 	$to_member['name'],
				'POSTER'    => 	$name,
				'TITLE'     => 	$title,
				'LINK'      => 	"?act=Msg&amp;CODE=03&amp;VID=in&amp;MSID=$message_id",
				'MSG_BODY'  => 	$this->remove_tags($message)
				  )
				 );
								
		$email->build_subject(array(
					'TEMPLATE'  => 	"pm_email_subject",
					'POSTER'    => 	$name,
					  )
				);
	
		$email->to = $to_member['email'];
		$email->send_mail();
	}

	return $message_id;

	}




    // -----------------------------------------------------
    // Leprecon. Some functions for syntax highlight
    // Begin -----------------------------------------------
    function str_to_sql ($str)
	{
		$str = preg_replace("/\\\/", "\\\\\\", $str);
		$str = preg_replace("/'/", "\\'", $str);
		return $str;
	}

	function sql_to_html ($str)
	{
		$str = preg_replace("/\"/", "\"", $str);
		$str = preg_replace("/'/", "&#39;", $str);
		return $str;
	}
    // End -------------------------------------------------
    // Leprecon
    // -----------------------------------------------------


	/*-------------------------------------------------------------------------*/
	// txt_stripslashes
	// ------------------
	// Make Big5 safe - only strip if not already...
	/*-------------------------------------------------------------------------*/
	
	function txt_stripslashes($t)
	{
		if ( $this->get_magic_quotes )
		{
    		$t = stripslashes($t);
    	}
    	
    	return $t;
    }
	
	/*-------------------------------------------------------------------------*/
	// txt_raw2form
	// ------------------
	// makes _POST text safe for text areas
	/*-------------------------------------------------------------------------*/
	
	function txt_raw2form($t="")
	{
		$t = str_replace( '$', "&#036;", $t);
			
		if ( get_magic_quotes_gpc() )
		{
			$t = stripslashes($t);
		}
		
		$t = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $t );
		
		return $t;
	}
	
	/*-------------------------------------------------------------------------*/
	// Safe Slashes - ensures slashes are saved correctly
	// ------------------
	// 
	/*-------------------------------------------------------------------------*/
	
	function txt_safeslashes($t="")
	{
		return str_replace( '\\', "\\\\", $this->txt_stripslashes($t));
	}
	
	/*-------------------------------------------------------------------------*/
	// txt_htmlspecialchars
	// ------------------
	// Custom version of htmlspecialchars to take into account mb chars
	/*-------------------------------------------------------------------------*/
	
	function txt_htmlspecialchars($t="")
	{
		// Use forward look up to only convert & not &#123;
		$t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t );
		$t = str_replace( "<", "&lt;"  , $t );
		$t = str_replace( ">", "&gt;"  , $t );
		$t = str_replace( '"', "&quot;", $t );
		
		return $t; // A nice cup of?
	}
	
	/*-------------------------------------------------------------------------*/
	// txt_UNhtmlspecialchars
	// ------------------
	// Undoes what the above function does. Yes.
	/*-------------------------------------------------------------------------*/
	
	function txt_UNhtmlspecialchars($t="")
	{
		$t = str_replace( "&amp;" , "&", $t );
		$t = str_replace( "&lt;"  , "<", $t );
		$t = str_replace( "&gt;"  , ">", $t );
		$t = str_replace( "&quot;", '"', $t );
		
		return $t;
	}
	
	/*-------------------------------------------------------------------------*/
	// return_md5_check
	// ------------------
	// md5 hash for server side validation of form / link stuff
	/*-------------------------------------------------------------------------*/
	
	function return_md5_check()
	{
		global $ibforums;
		
		if ( $ibforums->member['id'] )
		{
			return md5($ibforums->member['email'].'&'.$ibforums->member['password'].'&'.$ibforums->member['joined']);
		}
		else
		{
			return md5("this is only here to prevent it breaking on guests");
		}
	}
	

	/*-------------------------------------------------------------------------*/
	// C.O.C.S (clean old comma-delimeted strings)
	// ------------------
	// <>
	/*-------------------------------------------------------------------------*/
	
	function trim_leading_comma($t)
	{
		return preg_replace( "/^,/", "", $t );
	}
	
	function trim_trailing_comma($t)
	{
		return preg_replace( "/,$/", "", $t );
	}
	
	
	function clean_comma($t)
	{
		return preg_replace( "/,{2,}/", ",", $t );
	}
	
	function clean_perm_string($t)
	{
		$t = $this->clean_comma($t);
		$t = $this->trim_leading_comma($t);
		$t = $this->trim_trailing_comma($t);
		
		return $t;
	}
	
	/*-------------------------------------------------------------------------*/
	// size_format
	// ------------------
	// Give it a byte to eat and it'll return nice stuff!
	/*-------------------------------------------------------------------------*/
	
	function size_format($bytes="")
	{
		global $ibforums;
		
		$retval = "";
		
		if ($bytes >= 1048576)
		{
			$retval = round($bytes / 1048576 * 100 ) / 100 . $ibforums->lang['sf_mb'];
		}
		else if ($bytes  >= 1024)
		{
			$retval = round($bytes / 1024 * 100 ) / 100 . $ibforums->lang['sf_k'];
		}
		else
		{
			$retval = $bytes . $ibforums->lang['sf_bytes'];
		}
		
		return $retval;
	}
	
	/*-------------------------------------------------------------------------*/
	// print_forum_rules
	// ------------------
	// Checks and prints forum rules (if required)
	/*-------------------------------------------------------------------------*/
	
	// Song * new IBF forum rules

	function print_forum_rules($forum)
	{
		global $ibforums, $skin_universal;
		
		$ruleshtml = "";
		
		if ($forum['show_rules'])
		{
			if ( $forum['rules_title'] )
			{
				$rules['title'] = $forum['rules_title'];
				$rules['body']  = $forum['rules_text'];

				if ( $forum['red_border'] and $forum['show_rules'] == 2 )
				{
					$rules['body'] .= "</td></tr></table>";
					$rules['body'] = "<table {$ibforums->skin['white_background']} style='border:2px solid red;'><tr><td>".$rules['body'];
				}

				$rules['fid']   = $forum['id'];
				$ruleshtml = $forum['show_rules'] == 2 ? $skin_universal->forum_show_rules_full($rules) : $skin_universal->forum_show_rules_link($rules);
			}
		}
		
		return $ruleshtml;
	}
	


	/*-------------------------------------------------------------------------*/
	//
	// hdl_ban_line() : Get / set ban info
	// Returns array on get and string on "set"
	//
	/*-------------------------------------------------------------------------*/
	
	function hdl_ban_line($bline)
	{
		global $ibforums;
		
		if ( is_array( $bline ) )
		{
			// Set ( 'timespan' 'unit' )
			
			$factor = $bline['unit'] == 'd' ? 86400 : 3600;
			
			$date_end = time() + ( $bline['timespan'] * $factor );
			
			return time().':'.$date_end.':'.$bline['timespan'].':'.$bline['unit'];
		} else
		{
			$arr = array();
			
			list( $arr['date_start'], $arr['date_end'], $arr['timespan'], $arr['unit'] ) = explode( ":", $bline );
			
			return $arr;
		}
		
	}
	
	/*-------------------------------------------------------------------------*/
	//
	// check_perms() : Nice little sub to check perms
	// Returns TRUE if access is allowed, FALSE if not.
	//
	/*-------------------------------------------------------------------------*/
	
	function check_perms($forum_perm="")
	{
		global $ibforums;
		
		if ( $forum_perm == "" )
		{
			return FALSE;
		}
		else if ( $forum_perm == '*' )
		{
			return TRUE;
		}
		else
		{
			// Make permission array for this forum
			$forum_perm_array = explode( ",", $forum_perm );
			
			foreach( $ibforums->perm_id_array as $u_id )
			{
				if ( in_array( $u_id, $forum_perm_array ) )
				{
					return TRUE;
				}
			}
			
			// Still here? Not a match then.
			
			return FALSE;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	//
	// do_number_format() : Nice little sub to handle common stuff
	//
	/*-------------------------------------------------------------------------*/
	
	function do_number_format($number)
	{
		global $ibforums;
		
		if ($ibforums->vars['number_format'] != 'none')
		{
			return number_format($number , 0, '', $this->num_format);
		}
		else
		{
			return $number;
		}
	}
	
	
	/*-------------------------------------------------------------------------*/
	//
	// Return scaled down image
	//
	/*-------------------------------------------------------------------------*/
	
	function scale_image($arg)
	{
		// max_width, max_height, cur_width, cur_height
		
		$ret = array(
				  'img_width'  => $arg['cur_width'],
				  'img_height' => $arg['cur_height']
			    );
		
		if ( $arg['cur_width'] > $arg['max_width'] )
		{
			$ret['img_width']  = $arg['max_width'];
			$ret['img_height'] = ceil( ( $arg['cur_height'] * ( ( $arg['max_width'] * 100 ) / $arg['cur_width'] ) ) / 100 );
			$arg['cur_height'] = $ret['img_height'];
			$arg['cur_width']  = $ret['img_width'];
		}
		
		if ( $arg['cur_height'] > $arg['max_height'] )
		{
			$ret['img_height']  = $arg['max_height'];
			$ret['img_width']   = ceil( ( $arg['cur_width'] * ( ( $arg['max_height'] * 100 ) / $arg['cur_height'] ) ) / 100 );
		}
		
	
		return $ret;
	
	}
	
	
	/*-------------------------------------------------------------------------*/
	//
	// Show NORMAL created security image(s)...
	//
	/*-------------------------------------------------------------------------*/
	
	function show_gif_img($this_number="")
	{
		global $ibforums, $DB;
		
		$numbers = array(
				0 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKsOnmqSPjtT1ZdnnjCUqBQAOw==',
				1 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUjAEWyMqoXIprRkjxtZJWrz3iCBQAOw==',
				2 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKubnpPzRQvoVbvyrDHiWAAAOw==',
				3 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKbaHgRyUZtmlPtlfnnMiGUFADs=',
				4 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjAN5mLDtjFJMRjpj1Rv6v1SHN0IFADs=',
				5 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhA+Bpxn/DITL1SRjnps63l1M9RQAOw==',
				6 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjIEYyWwH3lNyrQTbnVh2Tl3N5wQFADs=',
				7 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhI9pwbztAAwP1napnFnzbYEYWAAAOw==',
				8 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKubHgSPWXoxVUxC33FZZCkFADs=',
				9 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDA6hyJabnnISnsnybXdS73hcZlUFADs=',
				);
		
		flush();
		header("Content-type: image/gif");
		echo base64_decode($numbers[ $this_number ]);
		exit();
		
		
	}
	
	/*-------------------------------------------------------------------------*/
	//
	// Show GD created security image...
	//
	/*-------------------------------------------------------------------------*/
	
	function show_gd_img($content="")
	{
		global $ibforums, $DB;

		flush();
		
		@header("Content-Type: image/jpeg");
		
		if ( $ibforums->vars['use_ttf'] != 1 )
		{
			$font_style = 5;
			$no_chars   = strlen($content);
			
			$charheight = ImageFontHeight($font_style);
			$charwidth  = ImageFontWidth($font_style);
			$strwidth   = $charwidth * intval($no_chars);
			$strheight  = $charheight;
			
			$imgwidth   = $strwidth  + 15;
			$imgheight  = $strheight + 15;
			$img_c_x    = $imgwidth  / 2;
			$img_c_y    = $imgheight / 2;
			
			$im       = ImageCreate($imgwidth, $imgheight);
			$text_col = ImageColorAllocate($im, 0, 0, 0);
			$back_col = ImageColorAllocate($im, 200,200,200);
			
			ImageFilledRectangle($im, 0, 0, $imgwidth, $imgheight, $text_col);
			ImageFilledRectangle($im, 3, 3, $imgwidth - 4, $imgheight - 4, $back_col);
			
			$draw_pos_x = $img_c_x - ($strwidth  / 2) + 1;
			$draw_pos_y = $img_c_y - ($strheight / 2) + 1;
			
			ImageString($im, $font_style, $draw_pos_x, $draw_pos_y, $content, $text_col);
		
		}
		else
		{
			$image_x = isset($ibforums->vars['gd_width'])  ? $ibforums->vars['gd_width'] : 250;
			$image_y = isset($ibforums->vars['gd_height']) ? $ibforums->vars['gd_height'] : 70;
			
			$im = imagecreate($image_x,$image_y);
			
			$white    = ImageColorAllocate($im, 255, 255, 255);
			$black    = ImageColorAllocate($im, 0, 0, 0);
			$grey     = ImageColorAllocate($im, 200, 200, 200 );
			
			$no_x_lines = ($image_x - 1) / 5;
			
			for ( $i = 0; $i <= $no_x_lines; $i++ )
			{
				// X lines
				
				ImageLine( $im, $i * $no_x_lines, 0, $i * $no_x_lines, $image_y, $grey );
				
				// Diag lines
				
				ImageLine( $im, $i * $no_x_lines, 0, ($i * $no_x_lines)+$no_x_lines, $image_y, $grey );
			}
			
			$no_y_lines = ($image_y - 1) / 5;
			
			for ( $i = 0; $i <= $no_y_lines; $i++ )
			{
				ImageLine( $im, 0, $i * $no_y_lines, $image_x, $i * $no_y_lines, $grey );
			}
			
			$font = isset($ibforums->vars['gd_font']) ? $ibforums->vars['gd_font'] : getcwd().'/fonts/progbot.ttf';
		
			$text_bbox = ImageTTFBBox(20, 0, $font, $content);
			
			$sx = ($image_x - ($text_bbox[2] - $text_bbox[0])) / 2; 
			$sy = ($image_y - ($text_bbox[1] - $text_bbox[7])) / 2; 
			$sy -= $text_bbox[7];
			
			imageTTFtext($im, 20, 0, $sx, $sy, $black, $font, $content);
		}
		
		
		ImageJPEG($im);
		ImageDestroy($im);
		
		exit();
		
	}
	
	/*-------------------------------------------------------------------------*/
	//
	// Convert newlines to <br> nl2br is buggy with <br> on early PHP builds
	//
	/*-------------------------------------------------------------------------*/
	
	function my_nl2br($t="")
	{
		return str_replace( "\n", "<br>", $t );
	}
	
	/*-------------------------------------------------------------------------*/
	//
	// Convert <br> to newlines
	//
	/*-------------------------------------------------------------------------*/
	
	function my_br2nl($t="")
	{
		$t = str_replace( "<br>", "\n", $t );
		$t = str_replace( "<br>"  , "\n", $t );
		
		return $t;
	}
	
	
	/*-------------------------------------------------------------------------*/
	//
	// Load a template file from DB or from PHP file
	//
	/*-------------------------------------------------------------------------*/
	
	function load_template( $name, $id='' )
	{
		global $ibforums, $DB;
		
		$tags      = 1;
		
		if ( !$ibforums->vars['safe_mode_skins'] )
		{
			// Simply require and return
// vot DEBUG:
//echo "load_template: ".$name." ".$id."<br>";			
//echo "load_template: base_dir=".$ibforums->vars['base_dir']."<br>";			
//echo "load_template: skin_id=".$ibforums->skin_id."<br>";
//echo "load_template: skin=".$ibforums->vars['base_dir']."Skin/".$ibforums->skin_id."/$name.php<br>";
			require $ibforums->vars['base_dir']."Skin/".$ibforums->skin_id."/$name.php";

			return new $name();
		} else
		{
			// We're using safe mode skins, yippee
			// Load the data from the DB
			
			$DB->query("SELECT
					func_name,
					func_data,
					section_content
				    FROM ibf_skin_templates
				    WHERE
					set_id='".$ibforums->skin_rid."' AND
					group_name='$name'");
			
			if ( ! $DB->get_num_rows() )
			{
				fatal_error("Could not fetch the templates from the database. Template $name, ID {$ibforums->skin_rid}");
			}
			else
			{
				$new_class = "class $name {\n";
				
				while( $row = $DB->fetch_row() )
				{
					if ($tags == 1)
					{
						$comment = "<!--TEMPLATE: $name, Template Part: ".$row['func_name']."-->\n";
					}
					
					$new_class .= 'function '.$row['func_name'].'('.$row['func_data'].") {\n";
					$new_class .= "global \$ibforums;\n";
					$new_class .= 'return <<<EOF'."\n".$comment.$row['section_content']."\nEOF;\n}\n";
				}
				
				$new_class .= "}\n";
				
				eval($new_class);
				
				return new $name();
			}
		}
	}
		
		
	/*-------------------------------------------------------------------------*/
	//
	// Creates a profile link if member is a reg. member, else just show name
	//
	/*-------------------------------------------------------------------------*/
	
	function make_profile_link($name, $id="", $info = "")
	{
		global $ibforums;
		
		if ($id > 0)
		{
			return "<a href='{$ibforums->base_url}showuser=$id'>$name</a>{$info}";
		}
		else
		{
			return $name;
		}
	}
	
	/*-------------------------------------------------------------------------*/
	//
	// Redirect using HTTP commands, not a page meta tag.
	//
	/*-------------------------------------------------------------------------*/
	
	function boink_it($url, $type = "" ) {
	global $ibforums;

		// Ensure &amp;s are taken care of
		if (!$type) $type = $ibforums->vars['header_redirect'];

		$url = str_replace( "&amp;", "&", $url );

		if ( $type == 'refresh')
		{
			@header("Refresh: 0;url=".$url);

		} elseif ($type == 'html')
		{
			@flush();
//			echo("<html><head><meta http-equiv='refresh' content='0; url=$url'></head><body></body></html>");
			echo("<html><head><meta http-equiv='refresh' content='0; url=".htmlspecialchars($url)."'></head><body></body></html>");
			exit();
		} else
		{
			@header("Location: ".$url);
		}

		exit();
	}

	/*-------------------------------------------------------------------------*/
	//
	// Create a random 8 character password
	//
	/*-------------------------------------------------------------------------*/
	
	function make_password()
	{
		$pass = "";
		$chars = array(
			"1","2","3","4","5","6","7","8","9","0",
			"a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J",
			"k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T",
			"u","U","v","V","w","W","x","X","y","Y","z","Z");
	
		$count = count($chars) - 1;

		srand((double)microtime()*1000000);

		for($i = 0; $i < 8; $i++)
		{
			$pass .= $chars[rand(0, $count)];
		}

		return($pass);
	}

	/*-------------------------------------------------------------------------*/
	//
	// Generate the appropriate folder icon for a forum
	//
	/*-------------------------------------------------------------------------*/

	function forum_new_posts($forum_data, $sub=0, $state=0, $moder_rights = "") {
        global $ibforums, $std, $DB;

	$rtime = $forum_data['last_post'];

	$fid = $forum_data['fid'] == "" ? $forum_data['id'] : $forum_data['fid'];

	if ( $ibforums->member['id'] && $forum_data['topics'] )
	{
		 $ftime = ( $ibforums->member['board_read'] > $ibforums->forum_read[ $fid ] ) 
			? $ibforums->member['board_read'] 
			: $ibforums->forum_read[$fid];

	} else $ftime = $rtime;

	if ( $state ) $ftime = 0;

        if ( !$sub )
        {
		if ( !$forum_data['status'] ) return "<{C_LOCKED}>";
		$sub_cat_img = '';
        } else 
	{
		$sub_cat_img = '_CAT';
	}


        if ( $ibforums->member['id'])
        {

	  $mid = $ibforums->member['id'];

/*
if ( $ibforums->member['id'] == 11454 ) //LuckLess
{
echo "\$mid={$ibforums->member['id']}<br>\n";
echo "\$fid=$fid<br>\n";
echo "\$forum_data['has_mod_posts']={$forum_data['has_mod_posts']}<br>\n";
echo "\$ibforums->member['g_is_supmod']={$ibforums->member['g_is_supmod']}<br>\n";
echo "\$moder_rights={$moder_rights}<br>\n";
echo "\$moder_rights[\$fid]={$moder_rights[$fid]}<br>\n";

if(is_array($moder_rights[$fid]))
{
  echo "\$moder_rights[\$fid][\$mid]={$moder_rights[$fid][$mid]}<br>\n";
  if(is_array($moder_rights[$fid][$mid]))
  {
    echo "\$moder_rights[ \$fid ][ \$mid]['topic_q']={$moder_rights[ $fid ][ $mid ]['topic_q']}<br>\n";
    echo "\$moder_rights[ \$fid ][ \$mid]['post_q']={$moder_rights[ $fid ][ $mid ]['post_q']}<br>\n";
  }
}
//echo "\$moder_rights[ \$fid ][ \$mid]={$moder_rights[ $fid ][ $mid ]}<br>\n";
//echo "\<br>\n";
echo "-----------------<br>\n";
}
*/
	  if ( $forum_data['has_mod_posts'] )
	  {
	    $rights_topicqueue = 0;
	    $rights_postqueue  = 0;
	    if(is_array($moder_rights[ $fid ]))
	    {
	      $rights_topicqueue = $moder_rights[ $fid ][ $mid ]['topic_q'];
	      $rights_postqueue  = $moder_rights[ $fid ][ $mid ]['post_q'];
		if ( $ibforums->member['g_is_supmod'] or 
		     ( $moder_rights[ $fid ][ $mid ] and 
		       ( $rights_topicqueue or $rights_postqueue )
		     ) 
 		   ) return "<{C_LOCKED".$sub_cat_img."}>";
	    }
	  }
	}

	if ( $forum_data['password'] and $sub == 0 )
	{
		return $ftime < $rtime	? "<{C_ON_RES}>"
					: "<{C_OFF_RES}>";
	}

	return $ftime < $rtime	? "<{C_ON".$sub_cat_img."}>"
				: "<{C_OFF".$sub_cat_img."}>";
	}

	/*-------------------------------------------------------------------------*/
	//
	// Generate the appropriate folder icon for a topic
	//
	/*-------------------------------------------------------------------------*/

	function folder_icon( $topic, $dot="", $last_time = -1, $mark = -1) {
	global $ibforums, $std;
	
	if ( !$ibforums->member['id'] ) $last_time = ''; else
	{
		if ($mark > $last_time)
		{
			$last_time = $mark ? $mark : -1;

		} elseif ( !$last_time )
		{
			$last_time = -1;
		}
	}

	if ( $dot ) $dot = "_DOT"; else $dot = "";

	if ( $topic['state'] == 'closed' ) return "<{B_LOCKED}>";

	if ( $topic['poll_state'] )
	{
		if ( !$ibforums->member['id'] ) 
		{
			return "<{B_POLL_NN".$dot."}>";
		}

		if ( $topic['last_post'] > $topic['last_vote'] ) 
		{
			$topic['last_vote'] = $topic['last_post'];
		}

		if ( $topic['last_vote'] > $last_time ) 
		{
			return "<{B_POLL".$dot."}>"; 
		} else
		{
			return "<{B_POLL_NN".$dot."}>";
		}

		return "<{B_POLL".$dot."}>";
	}

	if ( $topic['state'] == 'moved' or $topic['state'] == 'link' ) 
	{
		return "<{B_MOVED}>";
	}

	if ( ! $ibforums->member['id'] ) return "<{B_NORM".$dot."}>";

	if ( $topic['posts'] + 1 >= $ibforums->vars['hot_topic'] )
	 if ( $topic['last_post'] > $last_time ) 
	 {
		return "<{B_HOT".$dot."}>";
	 } else 
	 {
		return "<{B_HOT_NN".$dot."}>";
	 }

	if ( $topic['last_post'] > $last_time ) return "<{B_NEW".$dot."}>";

	return "<{B_NORM".$dot."}>";

	}
	
	
    /*-------------------------------------------------------------------------*/
    // text_tidy:
    // Takes raw text from the DB and makes it all nice and pretty - which also
    // parses un-HTML'd characters. Use this with caution!         
    /*-------------------------------------------------------------------------*/
    
	function text_tidy($txt = "") {
    
    	$trans = get_html_translation_table(HTML_ENTITIES);
    	$trans = array_flip($trans);
    	
    	$txt = strtr( $txt, $trans );
    	
    	$txt = preg_replace( "/\s{2}/" , "&nbsp; "      , $txt );
    	$txt = preg_replace( "/\r/"    , "\n"           , $txt );
    	$txt = preg_replace( "/\t/"    , "&nbsp;&nbsp;" , $txt );
    	//$txt = preg_replace( "/\\n/"   , "&#92;n"       , $txt );
    	
    	return $txt;
    	
    }

    /*-------------------------------------------------------------------------*/
    // compile_db_string:
    // Takes an array of keys and values and formats them into a string the DB
    // can use.
    // $array = ( 'THIS' => 'this', 'THAT' => 'that' );
    // will be returned as THIS, THAT  'this', 'that'                
    /*-------------------------------------------------------------------------*/
    
    function compile_db_string($data) {
    
    	$field_names  = "";
		$field_values = "";
		
		foreach ($data as $k => $v) {
			$v = preg_replace( "/'/", "\\'", $v );
			$field_names  .= "$k,";
			$field_values .= "'$v',";
		}
		
		$field_names  = preg_replace( "/,$/" , "" , $field_names  );
		$field_values = preg_replace( "/,$/" , "" , $field_values );
		
		return array( 'FIELD_NAMES'  => $field_names,
					  'FIELD_VALUES' => $field_values,
					);
	}



    /*-------------------------------------------------------------------------*/
    // Build up page span links                
    /*-------------------------------------------------------------------------*/
    
	function build_pagelinks($data)
	{
		global $ibforums, $skin_universal;

		$work = array();
		
		$section = ($data['leave_out'] == "") ? 2 : $data['leave_out'];  // Number of pages to show per section( either side of current), IE: 1 ... 4 5 [6] 7 8 ... 10
	
		$work['pages']  = 1;
		
		if ( ($data['TOTAL_POSS'] % $data['PER_PAGE']) == 0 )
		{
			$work['pages'] = $data['TOTAL_POSS'] / $data['PER_PAGE'];
		} else
		{
			$number = ($data['TOTAL_POSS'] / $data['PER_PAGE']);
			$work['pages'] = ceil( $number);
		}
		
		
		$work['total_page']   = $work['pages'];
		$work['current_page'] = $data['CUR_ST_VAL'] > 0 ? ($data['CUR_ST_VAL'] / $data['PER_PAGE']) + 1 : 1;
		
		if ( $work['pages'] > 1 )
		{
			$work['first_page'] = $skin_universal->make_page_jump($data['TOTAL_POSS'],$data['PER_PAGE'], $data['BASE_URL'])." (".$work['pages'].")";
			
			for( $i = 0; $i <= $work['pages'] - 1; ++$i )
			{
				$RealNo = $i * $data['PER_PAGE'];
				$PageNo = $i+1;
				
				if ($RealNo == $data['CUR_ST_VAL'])
				{
					$work['page_span'] .= "&nbsp;<b>[{$PageNo}]</b>";
				} else
				{
					if ($PageNo < ($work['current_page'] - $section))
					{
						$work['st_dots'] = "&nbsp;<a href='{$data['BASE_URL']}&amp;st=0' title='{$ibforums->lang['ps_page']} 1'>&laquo; {$ibforums->lang['ps_first']}</a>&nbsp;...";
						continue;
					}
					
					// If the next page is out of our section range, add some dotty dots!
					
					if ( $PageNo > ($work['current_page'] + $section) )
					{
// Song * new pages, 14.02.05
						$work['end_dots'] = "...&nbsp;";

						if ( $work['pages'] - $work['current_page'] > 2 and 
						     $work['pages'] - $PageNo > 1) 

						 for ($i = $work['pages'] - 2; $i < $work['pages']; ++$i ) 
						 {
							$RealNo = $i * $data['PER_PAGE'];
							$PageNo = $i+1;

							$work['end_dots'] .= "&nbsp;<a href='{$data['BASE_URL']}&amp;st={$RealNo}'>{$PageNo}</a>";
						 } else
						 {
							$work['end_dots'] .= "<a href='{$data['BASE_URL']}&amp;st=".($work['pages']-1) * $data['PER_PAGE']."' title='{$ibforums->lang['ps_page']} {$work['pages']}'>{$ibforums->lang['ps_last']} &raquo;</a>";
						 }
// /Song * new pages, 14.02.05
						break;
					}
					
					$work['page_span'] .= "&nbsp;<a href='{$data['BASE_URL']}&amp;st={$RealNo}'>{$PageNo}</a>";
				}
			}
			
			$work['return'] = $work['first_page'].$work['st_dots'].$work['page_span'].'&nbsp;'.$work['end_dots'];

// Song * show all posts in the topic

			if ( strpos( $data['BASE_URL'], "showtopic" ) !== FALSE and 
			     $data['TOTAL_POSS'] < $ibforums->vars['max_show_all_posts'] )
			{
				$work['return']	.= " <a href='{$data['BASE_URL']}&amp;view=showall'>".$ibforums->lang['all_posts']."</a>";
			}

// /Song * show all posts in the topic

		} else
		{
			$work['return']    = $data['L_SINGLE'];
		}
	
		return $work['return'];
	}
    

	    /*-------------------------------------------------------------------------*/
	    // Build the forum jump menu               
	    /*-------------------------------------------------------------------------*/ 
    
	function build_forum_jump($html=1, $override=0, $remove_redirects=0)
	{
		global $INFO, $DB, $ibforums;
		// $html = 0 means don't return the select html stuff
		// $html = 1 means return the jump menu with select and option stuff

		if ( $html == 1 and !$ibforums->member['cb_forumlist'] ) return "";

		$last_cat_id = -1;
		
		if ( $remove_redirects )
		{
			$qe = 'AND f.redirect_on <> 1';
		} else
		{
			$qe = '';
		}
		
		$DB->query("SELECT f.id as forum_id, f.parent_id, f.subwrap, f.sub_can_post, f.name as forum_name, 
				   f.position, f.redirect_on, f.read_perms, c.id as cat_id, c.name 
			    FROM ibf_forums f
			    LEFT JOIN ibf_categories c ON (c.id=f.category)
			    WHERE c.state IN (1,2) $qe
			    ORDER BY c.position, f.position");
		
		if ( $html == 1 ) 
		{
		
			$the_html = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$ibforums->base_url}act=SF' method='get' name='jumpmenu'>
			             <input type='hidden' name='act' value='SF'>\n<input type='hidden' name='s' value='{$ibforums->session_id}'>
			             <select name='f' class='forminput'>
			             <optgroup label=\"{$ibforums->lang['sj_title']}\">
			              <option value='sj_home'>{$ibforums->lang['sj_home']}</option>
			              <option value='sj_search'>{$ibforums->lang['sj_search']}</option>
			              <option value='sj_help'>{$ibforums->lang['sj_help']}</option>
			             </optgroup>
			             <optgroup label=\"{$ibforums->lang['forum_jump']}\">";
		}
		
		$forum_keys = array();
		$cat_keys   = array();
		$children   = array();
		$subs       = array();
		$subwrap    = array();
		
		// disable short mode if we're compiling a mod form
		
		if ($html == 0 or $override == 1)
		{
			$ibforums->vars['short_forum_jump'] = 0;
		}
			
		while ( $i = $DB->fetch_row() )
		{
			$selected = '';
			$redirect = "";
		
			if ($html == 1 or $override == 1)
			{
				if ($ibforums->input['f'] and $ibforums->input['f'] == $i['forum_id'])
				{
					$selected = ' selected="selected"';
				}
			}
			
			if ( $i['redirect_on'] )
			{
				$redirect = $ibforums->lang['fj_redirect'];
			}
			
			if ($i['subwrap'] == 1)
			{
				$subwrap[ $i['forum_id'] ] = 1;
			}
			
			if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
			{
				$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;- {$i['forum_name']}</option>\n";
			} else
			{
				if ( $this->check_perms($i['read_perms']) == TRUE )
				{
					if ($i['parent_id'] > 0)
					{
//song						$children[ $i['parent_id'] ][] = "<option value=\"{$i['forum_id']}\"$selected>&nbsp;&nbsp;<IBF_SONG_DEPTH>&nbsp;{$i['forum_name']}</option>\n");
// Song * endless forums, 20.12.04
						$children[ $i['parent_id'] ][] = array($i['forum_id'], "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;<IBF_SONG_DEPTH>---- {$i['forum_name']} $redirect</option>\n");
// Song * endless forums, 20.12.04
					} else
					{
						$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;- {$i['forum_name']} $redirect</option><!--fx:{$i['forum_id']}-->\n";
					}

				} else continue;
			}
			
			if ( $last_cat_id != $i['cat_id'] )
			{
				
				// Make sure cats with hidden forums are not shown in forum jump
				
				$cat_keys[ $i['cat_id'] ] = "<option value='-1'>{$i['name']}</option>\n";
							              
				$last_cat_id = $i['cat_id'];
				
			}
		}
		
		foreach($cat_keys as $cat_id => $cat_text)
		{
			if ( is_array( $forum_keys[$cat_id] ) && count( $forum_keys[$cat_id] ) > 0 )
			{
				$the_html .= $cat_text;
				
				foreach($forum_keys[$cat_id] as $idx => $forum_text)
				{
					if ( $subwrap[$idx] != 1 )
					{
						$the_html .= $forum_text;
					} else
					{
						if ( count($children[$idx]) > 0 )
						{
							$the_html .= $forum_text;
							
							if ( $ibforums->vars['short_forum_jump'] != 1 )
							{
// Song * endless forums, 20.12.04
								$the_html .= $this->subforums_addtoform($idx, $children);
// Song * endless forums, 20.12.04
							} else
							{
								$the_html = str_replace('<IBF_SONG_DEPTH>', "", $the_html);

								$the_html = str_replace( "</option><!--fx:$idx-->", " (+".count($children[$idx])." {$ibforums->lang['fj_subforums']})</option>", $the_html );
							}
						}
					}
				}
			}
		}
			
		
		if ($html == 1)
		{
			$the_html .= "</optgroup>\n</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>";
		}
		
		return $the_html;
		
	}



	// Song * endless forums, 20.12.04

	function subforums_addtoform($id, &$children, $level='') {

	$html = '';

	if ( count($children[$id]) > 0 ) foreach($children[$id] as $ii => $tt)
	{
		$prefix = "";

		// visuality depth
		for ($i = 0; $i < $level; $i++) $prefix .= "---";

		$tt[1] = str_replace('<IBF_SONG_DEPTH>', $prefix, $tt[1]);

		$html .= $prefix.$tt[1].$this->subforums_addtoform($tt[0], $children, $level + 1);
	}

	return $html;

	}


	
	function build_forum_jump_topics($html=1, $override=0, $remove_redirects=0)
	{
		global $INFO, $DB, $ibforums;
		// $html = 0 means don't return the select html stuff
		// $html = 1 means return the jump menu with select and option stuff
		
		if ( !$ibforums->member['cb_forumlist'] ) return "";

		$last_cat_id = -1;
		
		if ( $remove_redirects )
		{
			$qe = 'AND f.redirect_on <> 1';
		}
		else
		{
			$qe = '';
		}
		
		$DB->query("SELECT
				f.id as forum_id,
				f.parent_id,
				f.subwrap,
				f.sub_can_post,
				f.name as forum_name,
				f.position,
				f.redirect_on,
				f.read_perms,
				c.id as cat_id,
				c.name
			    FROM ibf_forums f
		     	    LEFT JOIN ibf_categories c
				ON (c.id=f.category)
			    WHERE
				c.state IN (1,2)
				$qe
			    ORDER BY c.position, f.position");
		
		if ($html == 1) 
		{
		
			$the_html = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$ibforums->base_url}act=SF' method='get' name='jumpmenu'>
			             <input type='hidden' name='act' value='SF'>\n<input type='hidden' name='s' value='{$ibforums->session_id}'>
			             <select name='f' class='forminput'>
			             <optgroup label=\"{$ibforums->lang['sj_title']}\">
			              <option value='sj_home'>{$ibforums->lang['sj_home']}</option>
			              <option value='sj_search'>{$ibforums->lang['sj_search']}</option>
			              <option value='sj_help'>{$ibforums->lang['sj_help']}</option>
			             </optgroup>
			             <optgroup label=\"{$ibforums->lang['forum_jump']}\">";
		}
		
		$forum_keys = array();
		$cat_keys   = array();
		$children   = array();
		$subs       = array();
		$subwrap    = array();
		
		// disable short mode if we're compiling a mod form
		
		if ($html == 0 or $override == 1)
		{
			$ibforums->vars['short_forum_jump'] = 0;
		}
			
		while ( $i = $DB->fetch_row() )
		{
			$selected = '';
			$redirect = "";
		
			if ($html == 1 or $override == 1)
			{
				if ($ibforums->input['f'] and $ibforums->input['f'] == $i['forum_id'])
				{
					$selected = ' selected="selected"';
				}
			}
			
			if ( $i['redirect_on'] )
			{
				$redirect = $ibforums->lang['fj_redirect'];
			}
			
			if ($i['subwrap'] == 1)
			{
				$subwrap[ $i['forum_id'] ] = 1;
			}
			
			if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
			{
				$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;- {$i['forum_name']}</option>\n";
			} else
			{
				if ( $this->check_perms($i['read_perms']) == TRUE )
				{
					if ($i['parent_id'] > 0)
					{
//song						$children[ $i['parent_id'] ][] = "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;---- {$i['forum_name']} $redirect</option>\n";
// Song * endless forums, 20.12.04
						$children[ $i['parent_id'] ][] = array($i['forum_id'], "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;<IBF_SONG_DEPTH>---- {$i['forum_name']} $redirect</option>\n");
// Song * endless forums, 20.12.04
					} else
					{
						$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"".$selected.">&nbsp;&nbsp;- {$i['forum_name']} $redirect</option><!--fx:{$i['forum_id']}-->\n";
					}
				}
				else
				{
					continue;
				}
			}
			
			if ($last_cat_id != $i['cat_id'])
			{
				
				// Make sure cats with hidden forums are not shown in forum jump
				
				$cat_keys[ $i['cat_id'] ] = "<option value='-1'>{$i['name']}</option>\n";
							              
				$last_cat_id = $i['cat_id'];
				
			}
		}
		
		foreach($cat_keys as $cat_id => $cat_text)
		{
			if ( is_array( $forum_keys[$cat_id] ) && count( $forum_keys[$cat_id] ) > 0 )
			{
				$the_html .= $cat_text;
				
				foreach($forum_keys[$cat_id] as $idx => $forum_text)
				{
					if ( $subwrap[$idx] != 1 )
					{
						$the_html .= $forum_text;
					}
					else
					{
						if (count($children[$idx]) > 0)
						{
							$the_html .= $forum_text;
							
							if ($ibforums->vars['short_forum_jump'] != 1)
							{
// Song * endless forums, 20.12.04
								$the_html .= $this->subforums_addtoform($idx, $children);
// Song * endless forums, 20.12.04
							} else
							{
								$the_html = str_replace('<IBF_SONG_DEPTH>', "", $the_html);

								$the_html = str_replace( "</option><!--fx:$idx-->", " (+".count($children[$idx])." {$ibforums->lang['fj_subforums']})</option>", $the_html );
							}
						}
					}
				}
			}
		}
			
		if ($html == 1)
		{
			$the_html .= "</optgroup>\n</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>";
		}
		
		return $the_html;
		
	}

	function clean_email($email = "") {

		$email = trim($email);
		
		$email = str_replace( " ", "", $email );
		
    	$email = preg_replace( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/]#", "", $email );
    	
    	if ( preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email) )
    	{
    		return $email;
    	}
    	else
    	{
    		return FALSE;
    	}
	}
    
    
    /*-------------------------------------------------------------------------*/
    // SKIN, sort out the skin stuff                 
    /*-------------------------------------------------------------------------*/

    function load_skin() {
    global $ibforums, $INFO, $DB;

    	$id       = -1;
    	$skin_set = 0;

    	if ( ( $ibforums->is_bot == 1 ) and ($ibforums->vars['spider_suit'] != "") )
    	{
    		$skin_set = 1;
    		$id       = $ibforums->vars['spider_suit'];
    	} else
    	{
		//------------------------------------------------
		// Do we have a skin for a particular forum?
		//------------------------------------------------

		if ($ibforums->input['f'] and $ibforums->input['act'] != 'UserCP')
		{
			if ( $ibforums->vars[ 'forum_skin_'.$ibforums->input['f'] ] != "" )
			{
				$id = $ibforums->vars[ 'forum_skin_'.$ibforums->input['f'] ];
				
				$skin_set = 1;
			}
		}
		
		//------------------------------------------------
		// Are we allowing user chooseable skins?
		//------------------------------------------------
		
		$extra = "";
		
		if ($skin_set != 1 and $ibforums->vars['allow_skins'] == 1)
		{
			if (isset($ibforums->input['skinid']))
			{
				$id    = intval($ibforums->input['skinid']);
				$extra = " AND s.hidden=0";
				$skin_set = 1;

			} elseif ( $ibforums->member['skin'] != "" and intval($ibforums->member['skin']) >= 0 )
			{
				$id = $ibforums->member['skin'];
				
				if ($id == 'Default') $id = -1;
				
				$skin_set = 1;
			}
		}
    	}

    	//------------------------------------------------
    	// Load the info from the database.
    	//------------------------------------------------

    	if ( $id >= 0 and $skin_set == 1)
    	{
/*
    		$DB->query("SELECT
				s.*,
				t.template,
				Concat(c.css_text,'\n',cc.css_text) as css_text 
    			    FROM
				ibf_skins s,
				ibf_css cc 
    			    LEFT JOIN ibf_templates t
				ON (t.tmid=s.tmpl_id) 
    			    LEFT JOIN ibf_css c
				ON (c.cssid=s.css_id) 
    	           	    WHERE
				cc.cssid=13 and
				s.sid=$id".$extra);
*/
    		$DB->query("SELECT
				s.*,
				t.template,
				Concat(c.css_text,'\n',cc.css_text) as css_text 
    			    FROM (
				ibf_skins s,
				ibf_css cc 
				)
    			    LEFT JOIN ibf_templates t
				ON (s.tmpl_id=t.tmid) 
    			    LEFT JOIN ibf_css c
				ON (c.cssid=s.css_id) 
    	           	    WHERE
				cc.cssid=13 and
				s.sid=$id".$extra);
    	           	   
		// Didn't get a row?
		if ( !$DB->get_num_rows() )
		{
			// Update this members profile
    	    	
	    	    	if ( $ibforums->member['id'] )
	    	    	{
	    	    		$DB->query("UPDATE ibf_members
					    SET skin='-1'
					    WHERE id='".$ibforums->member['id']."'");
	    	    	}
    	    	
	  	    	$DB->query("SELECT
					s.*,
					t.template,
					Concat(c.css_text,'\n',cc.css_text) as css_text 
	 			    FROM (
					ibf_skins s,
					ibf_css cc 
					)
	    			    LEFT JOIN ibf_templates t
					ON (s.tmpl_id = t.tmid) 
	    			    LEFT JOIN ibf_css c
					ON (s.css_id=c.cssid) 
	    	           	    WHERE
					cc.cssid=13 and
					s.default_set=1");
		}
    	    
    	} else $DB->query("SELECT
				s.*,
				t.template,
				Concat(c.css_text,'\n',cc.css_text) as css_text 
    			   FROM (
				ibf_skins s,
				ibf_css cc 
				)
    			   LEFT JOIN ibf_templates t
				ON (s.tmpl_id=t.tmid) 
   			   LEFT JOIN ibf_css c
				ON (s.css_id=c.cssid) 
    	           	   WHERE
				cc.cssid=13 and
				s.default_set=1");
    	
    	if ( !$row = $DB->fetch_row() )
    	{
    		echo("Could not query the skin information!");
    		exit();
    	}
    	
    	//-------------------------------------------
    	// Setting the skin?
    	//-------------------------------------------
    	
    	if ( ($ibforums->input['setskin']) and ($ibforums->member['id']) )
    	{
    		$DB->query( "UPDATE ibf_members
			     SET skin='".intval($row['sid'])."' 
			     WHERE id='".intval($ibforums->member['id'])."'");
    		
    		$ibforums->member['skin'] = $row['sid'];
    	}
    	
	$row['white_background'] = str_replace( "&#39;", "'", $row['white_background'] );

    	return $row;
    	
    }


    /*-------------------------------------------------------------------------*/
    // Require, parse and return an array containing the language stuff                 
    /*-------------------------------------------------------------------------*/ 
    
    function load_words($current_lang_array, $area, $lang_type) {
        global $ibforums;
        require $ibforums->vars['base_dir']."lang/".$lang_type."/".$area.".php";
        
        foreach ($lang as $k => $v)
        {
        	$current_lang_array[$k] = stripslashes($v);
        }
        
        unset($lang);
        
        return $current_lang_array;

    }

    
    /*-------------------------------------------------------------------------*/
    // Return a date or '--' if the date is undef.
    // We use the rather nice gmdate function in PHP to synchronise our times
    // with GMT. This gives us the following choices:
    //
    // If the user has specified a time offset, we use that. If they haven't set
    // a time zone, we use the default board time offset (which should automagically
    // be adjusted to match gmdate.             
    /*-------------------------------------------------------------------------*/    
    
// Song * today/yesterday

    function old_get_date($date, $method) {
        global $ibforums;
        
        if ( !$date ) return '--';
        
        if ( empty($method) ) $method = 'LONG';
        
        if ( $this->offset_set == 0 )
        {
        	// Save redoing this code for each call, only do once per page load
        	
		$this->offset = $this->get_time_offset();
		$this->offset_set = 1;
        }
        
        return gmdate($this->time_options[$method], ($date + $this->offset) );
    }

   function get_date($date, $method, $html = 1) {
   global $ibforums;

        if ( !$date ) return '--';

        if (empty($method)) $method = 'LONG';

        if ($this->offset_set == 0) 
	{
		$this->offset = (($ibforums->member['time_offset'] != "") ? $ibforums->member['time_offset'] : $ibforums->vars['time_offset']) * 3600;

		if ($ibforums->vars['time_adjust'] != "" and $ibforums->vars['time_adjust'] != 0) 
		{
			$this->offset += ($ibforums->vars['time_adjust'] * 60);
		}

// exodus		if ($ibforums->member['dst_in_use']) $this->offset += 3600; else $this->offset_set = 1;
// exodus:
		if ($ibforums->member['dst_in_use']) $this->offset += date('I') * 3600; else $this->offset_set = 1;
        }

	$todaystamp = mktime();
	$todaydate = gmdate("F j Y", ($todaystamp + $this->offset));

	$yestdate = gmdate("F j Y", (($todaystamp-86400) + $this->offset));
    	$postdate = gmdate("F j Y", ($date + $this->offset));

        $tydate = "";

	if ( $postdate == $todaydate) 
	{
		if ( $html != -1 ) $tydate = ( $html ) ? "<b>" : "[b]";

		if ( $ibforums->member['today'] ) $tydate .= $ibforums->member['today']; else $tydate .= "Сегодня";

		if ( $html != -1 ) $tydate .= ( $html ) ? "</b>" : "[/b]";

		$tydate .= ", ";
	} else 
	{
		if ( $postdate == $yestdate) 
		 if ( $ibforums->member['yesterday'] ) $tydate .= $ibforums->member['yesterday'].", "; else $tydate .= "Вчера, ";
	}
		
	if ( $tydate ) return $tydate.gmdate("H:i", ($date + $this->offset) ); else 
	{
		return gmdate($this->time_options[$method], ($date + $this->offset) );
	}

    }

// Song * today/yesterday
    
    /*-------------------------------------------------------------------------*/
    // Returns the offset needed and stuff - quite groovy.              
    /*-------------------------------------------------------------------------*/    
    
    function get_time_offset()
    {
    	global $ibforums;
    	
    	$r = 0;
    	
    	$r = (($ibforums->member['time_offset'] != "") ? $ibforums->member['time_offset'] : $ibforums->vars['time_offset']) * 3600;
			
		if ( $ibforums->vars['time_adjust'] )
		{
			$r += ($ibforums->vars['time_adjust'] * 60);
		}
		
		if ($ibforums->member['dst_in_use'])
		{
			$r += 3600;
		}
    	
    	return $r;
    	
    }
    
    /*-------------------------------------------------------------------------*/
    // Sets a cookie, abstract layer allows us to do some checking, etc                
    /*-------------------------------------------------------------------------*/    
    
    function my_setcookie($name, $value = "", $sticky = 1) {
        global $INFO;
        
        //$expires = "";
        
        if ($sticky == 1)
        {
        	$expires = time() + 60*60*24*365;
        }

        $INFO['cookie_domain'] = $INFO['cookie_domain'] == "" ? ""  : $INFO['cookie_domain'];
        $INFO['cookie_path']   = $INFO['cookie_path']   == "" ? "/" : $INFO['cookie_path'];
        
        $name = $INFO['cookie_id'].$name;
      
        @setcookie($name, $value, $expires, $INFO['cookie_path'], $INFO['cookie_domain']);
    }
    
    /*-------------------------------------------------------------------------*/
    // Cookies, cookies everywhere and not a byte to eat.                
    /*-------------------------------------------------------------------------*/  
    
    function my_getcookie($name)
    {
    	global $INFO;
    	
    	if ( isset($_COOKIE[$INFO['cookie_id'].$name]) )
    	{
    		return $this->clean_value(urldecode($_COOKIE[$INFO['cookie_id'].$name]));
    	} else
    	{
    		return FALSE;
    	}
    	
    }
    
    /*-------------------------------------------------------------------------*/
    // Makes incoming info "safe"              
    /*-------------------------------------------------------------------------*/

    function parse_incoming()
    {
//    	global $HTTP_X_FORWARDED_FOR;

    	$return = array();

// Song * secure patch

	if ( is_array($_GET) )
	{
		while( list($k, $v) = each($_GET) )
		{
			if ( $k == 'INFO' ) continue;
// Song * secure patch
			if( is_array($_GET[$k]) )
			{
				while( list($k2, $v2) = each($_GET[$k]) )
				{
					$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);
				}

			} else $return[$k] = $this->clean_value($v);
		}
	}
	
	// Overwrite GET data with post data
	
	if ( is_array($_POST) )
	{
		while( list($k, $v) = each($_POST) )
		{
			if ( is_array($_POST[$k]) )
			{
				while( list($k2, $v2) = each($_POST[$k]) )
				{
					$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);
				}

			} else $return[$k] = $this->clean_value($v);
		}
	}
	
	//----------------------------------------
	// Sort out the accessing IP
	// (Thanks to Cosmos and schickb)
	//----------------------------------------
	
	$addrs = array();
	
	$addrs[] = $_SERVER['REMOTE_ADDR'];
	if(isset($_SERVER['HTTP_PROXY_USER'])) $addrs[] = $_SERVER['HTTP_PROXY_USER'];
//	$addrs[] = $_SERVER['REMOTE_ADDR'];
	
	//header("Content-type: text/plain"); print_r($addrs); print $_SERVER['HTTP_X_FORWARDED_FOR']; exit();
	
	$return['IP_ADDRESS'] = $this->select_var( $addrs );
											 
	// Make sure we take a valid IP address
	
	$return['IP_ADDRESS'] = preg_replace( "/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3.\\4", $return['IP_ADDRESS'] );
	
	$return['request_method'] = ( $_SERVER['REQUEST_METHOD'] != "" ) ? strtolower($_SERVER['REQUEST_METHOD']) : strtolower($REQUEST_METHOD);
	
	return $return;

	}
	
    /*-------------------------------------------------------------------------*/
    // Key Cleaner - ensures no funny business with form elements             
    /*-------------------------------------------------------------------------*/
    
    function clean_key($key) {
    
    	if ( $key == "" ) return "";

    	$key = preg_replace( "/\.\./"           , ""  , $key );
    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );

    	return $key;
    }
    
    function clean_value($val) {
    global $ibforums;
    	
    	if ( $val == "" ) return "";
    	
    	$val = str_replace( "&#032;", " ", $val );
    	
    	if ( $ibforums->vars['strip_space_chr'] )
    	{
    		$val = str_replace( chr(0xCA), "", $val );  //Remove sneaky spaces
    	}
    	
    	$val = str_replace( "&"            , "&amp;"         , $val );
    	$val = str_replace( "<!--"         , "&#60;&#33;--"  , $val );
    	$val = str_replace( "-->"          , "--&#62;"       , $val );
	$val = preg_replace( "/<(script)/i", "&#60;\\1"      , $val );
    	$val = str_replace( ">"            , "&gt;"          , $val );
    	$val = str_replace( "<"            , "&lt;"          , $val );
    	$val = str_replace( "\""           , "&quot;"        , $val );
    	$val = preg_replace( "/\n/"        , "<br>"          , $val ); // Convert literal newlines
    	$val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
    	$val = preg_replace( "/\r/"        , ""              , $val ); // Remove literal carriage returns
    	$val = str_replace( "!"            , "&#33;"         , $val );
    	$val = str_replace( "'"            , "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.
    	
    	// Ensure unicode chars are OK
    	
    	if ( $this->allow_unicode )
	{
		$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );
	}
		
	// Strip slashes if not already done so.
    	if ( $this->get_magic_quotes ) $val = stripslashes($val);
    	
    	// Swop user inputted backslashes
    	$val = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $val ); 
    	
    	return $val;
    }
    
    
    function remove_tags($text="")
    {
    	// Removes < BOARD TAGS > from posted forms
    	
    	$text = preg_replace( "/(<|&lt;)% (BOARD HEADER|CSS|JAVASCRIPT|TITLE|BOARD|STATS|GENERATOR|COPYRIGHT|NAVIGATION) %(>|&gt;)/i", "&#60;% \\2 %&#62;", $text );
    	
    	//$text = str_replace( "<%", "&#60;%", $text );
    	
    	return $text;
    }
    
    function is_number($number="")
    {
    
    	if ($number == "") return -1;
    	
    	if ( preg_match( "/^([0-9]+)$/", $number ) )
    	{
    		return $number;
    	}
    	else
    	{
    		return "";
    	}
    }
    



    /*-------------------------------------------------------------------------*/
    // MEMBER FUNCTIONS             
    /*-------------------------------------------------------------------------*/
    
    
    function set_up_guest( $name = 'Guest' ) {
    global $INFO;
    
    	return array( 
			'name'     => $name,
 			'id'       => 0,
			'password' => "",
			'email'    => "",
			'title'    => "Unregistered",
			'mgroup'    => $INFO['guest_group'],
			'view_sigs' => $INFO['guests_sig'],
			'view_img'  => $INFO['guests_img'],
			'view_avs'  => $INFO['guests_ava'],

  		    );
    }
    
    /*-------------------------------------------------------------------------*/
    // GET USER AVATAR         
    /*-------------------------------------------------------------------------*/
    
    function get_avatar($member_avatar="", $member_view_avatars=0, $avatar_dims="x")
    {
    	global $ibforums;
    	
    	if ( !$member_avatar or $member_view_avatars == 0 or !$ibforums->vars['avatars_on'] )
    	{
    		return "";
    	}
    	
    	if ( preg_match ( "/^noavatar/", $member_avatar ) )
    	{
    		return "";
    	}
    	
    	if ( (preg_match ( "/\.swf/", $member_avatar)) and ($ibforums->vars['allow_flash'] != 1) )
    	{
    		return "";
    	}
    	
    	$davatar_dims    = explode( "x", $ibforums->vars['avatar_dims'] );
	$default_a_dims  = explode( "x", $ibforums->vars['avatar_def'] );
    	
    	//---------------------------------------
	// Have we enabled URL / Upload avatars?
	//---------------------------------------
	 
	$this_dims = explode( "x", $avatar_dims );
	if (!$this_dims[0]) $this_dims[0] = $davatar_dims[0];
	if (!$this_dims[1]) $this_dims[1] = $davatar_dims[1];
		
	if ( preg_match( "/^http:\/\//", $member_avatar ) )
	{
		// Ok, it's a URL..
		
		if (preg_match ( "/\.swf/", $member_avatar))
		{
			return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
		}
		else
		{
			return "<img src='{$member_avatar}' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}' alt=''>";
		}
		
		//---------------------------------------
		// Not a URL? Is it an uploaded avatar?
		//---------------------------------------

	} else if ( ($ibforums->vars['avup_size_max'] > 1) and ( preg_match( "/^upload:av-(?:\d+)\.(?:\S+)/", $member_avatar ) ) )
	{
		$member_avatar = preg_replace( "/^upload:/", "", $member_avatar );
		
		if ( preg_match ( "/\.swf/", $member_avatar) )
		{
			return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE=\"{$ibforums->vars['upload_url']}/$member_avatar\"><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC=\"{$ibforums->vars['upload_url']}/$member_avatar\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
		}
		else
		{
			return "<img src='{$ibforums->vars['upload_url']}/$member_avatar' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}' alt=''>";
		}
	}
	
	//---------------------------------------
	// No, it's not a URL or an upload, must
	// be a normal avatar then
	//---------------------------------------
	
	else if ( $member_avatar != "" )
	{
		//---------------------------------------
		// Do we have an avatar still ?
	   	//---------------------------------------
	   	
		return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' alt=''>";
		//---------------------------------------
		// No, ok - return blank
		//---------------------------------------
	} else return "";
    }
 
 





    /*-------------------------------------------------------------------------*/
    // ERROR FUNCTIONS             
    /*-------------------------------------------------------------------------*/
    
    function JsError($error = array()) {
    global $ibforums, $print;

	$url = "location.href='{$ibforums->base_url}act=Error&type={$error['MSG']}'";

	$print->redirect_js_screen("", "", "", $url);
    }

    function Error($error) {
    	global $DB, $ibforums, $skin_universal;
    	
    	//INIT is passed to the array if we've not yet loaded a skin and stuff
    	
	if ( isset($ibforums->input['js']) && $ibforums->input['js'] && $ibforums->input['linkID'] )
	{
		$this->JsError($error);

		exit();
	}

    	if ( $error['INIT'] == 1)
    	{
    		$DB->query("SELECT
				s.*,
				t.template,
				c.css_text
  			    FROM ibf_skins s
  			    LEFT JOIN ibf_templates t
				ON (t.tmid=s.tmpl_id)
    			    LEFT JOIN ibf_css c
				ON (s.css_id=c.cssid)
    	           	    WHERE s.default_set=1");
    	           	   
		$ibforums->skin = $DB->fetch_row();
    	           	   
    		$ibforums->session_id = $this->my_getcookie('session_id');

		$ibforums->base_url = $ibforums->vars['board_url'].'/index.'.$ibforums->vars['php_ext'].'?s='.$ibforums->session_id;
		$ibforums->skin_rid = $ibforums->skin['set_id'];
		$ibforums->skin_id = 's'.$ibforums->skin['set_id'];
			
		if ($ibforums->vars['default_language'] == "")
		{
			$ibforums->vars['default_language'] = 'en';
		}
			
		$ibforums->lang_id = (isset($ibforums->member['language']) && $ibforums->member['language'])? $ibforums->member['language'] : $ibforums->vars['default_language'];
			
		if ( ($ibforums->lang_id != $ibforums->vars['default_language']) and
		     (! is_dir( $ibforums->vars['base_dir']."lang/".$ibforums->lang_id ) ) )
		{
			$ibforums->lang_id = $ibforums->vars['default_language'];
		}
			
		$ibforums->lang = $this->load_words($ibforums->lang, "lang_global", $ibforums->lang_id);


		//------------------------------------------
		// Let's load the full path for the images, including the board url
		// Date: 06/07/2005 16:17 (c) Anton
		//-----------------------------------------
		// vot $ibforums->vars['img_url'] = "/style_images/".$ibforums->skin['img_dir'];
		$ibforums->vars['img_url']   = $ibforums->vars['board_url'] . '/style_images/' . $ibforums->skin['img_dir'];

		$skin_universal = $this->load_template('skin_global');
	}

    	$ibforums->lang = $this->load_words($ibforums->lang, "lang_error", $ibforums->lang_id);

    	list($em_1, $em_2) = explode( '@', $ibforums->vars['email_in'] );
    	
    	$msg = $ibforums->lang[ $error['MSG'] ];

	if ( !$msg ) $msg = $ibforums->lang['missing_files'];
    	
    	if ( !$error['EXTRA'] ) $error['EXTRA'] = ""; // vot
    	$msg = preg_replace( "/<#EXTRA#>/", $error['EXTRA'], $msg );

    	if ( !isset($error['EXTRA2']) || !$error['EXTRA2']) $error['EXTRA2']=""; // vot
    	$msg = preg_replace( "/<#EXTRA2#>/", $error['EXTRA2'], $msg ); // vot
    	
//sh: This returns to user just only error string
//Such behavior is needed for:
//a) offline clients
//b) searching bots engines
//c) plugins

	if ( $ibforums->vars['plg_catch_err'] ) 
	{
		$ibforums->vars['plg_catch_err']->Error($msg);
	}

    	$html = $skin_universal->Error( $msg, $em_1, $em_2);
    	
    	//-----------------------------------------
    	// If we're a guest, show the log in box..
    	//-----------------------------------------
    	
    	if ( !$ibforums->member['id'] and $error['MSG'] != 'server_too_busy' and $error['MSG'] != 'account_susp' )
    	{
    		$html = str_replace( "<!--IBF.LOG_IN_TABLE-->", $skin_universal->error_log_in($_SERVER['QUERY_STRING']), $html);
    	}
    	
    	//-----------------------------------------
    	// Do we have any post data to keepy?
    	//-----------------------------------------
    	
    	if ( $ibforums->input['act'] == 'Post' OR $ibforums->input['act'] == 'Msg' OR $ibforums->input['act'] == 'calendar' )
    	{
    		if ( $_POST['Post'] )
    		{
    			$post_thing = $skin_universal->error_post_textarea($this->txt_htmlspecialchars($this->txt_stripslashes($_POST['Post'])) );
    			
    			$html = str_replace( "<!--IBF.POST_TEXTAREA-->", $post_thing, $html );
    		}
    	}

    	$print = new display();

    	$print->add_output($html);

    	$print->do_output( array(
  				OVERRIDE   => 1,
    				TITLE      => $ibforums->lang['error_title'],
    				 )
  			);
    }
    

// Song * NEW system

 function song_get_forumsread() {
 global $ibforums, $DB;
	
	$info = $DB->query("SELECT
				fid,
				logTime
			    FROM ibf_log_forums
			    WHERE mid='".$ibforums->member['id']."'");
	
	if ($DB->get_num_rows($info))
	{
		while($read = $DB->fetch_row($info))
		{
			$ibforums->forum_read[ $read['fid'] ] = $read['logTime'];
		}

	} else $ibforums->forum_read = array();
	
	$ibforums->forums_read = unserialize($ibforums->member['forums_read']);

	return TRUE;
 }

 function song_set_forumread( $fid ) {
 global $ibforums, $DB;

	if ( $ibforums->member['id'] )
	{
		// do safe query
		$DB->return_die = 1;

		if ( isset($ibforums->forum_read[ $fid ]) )
		{			
			$DB->query("UPDATE ibf_log_forums
				    SET logTime='".time()."'
				    WHERE
					fid='".$fid."' AND
					mid='".$ibforums->member['id']."'");
		} else
		{
			$DB->query("INSERT INTO ibf_log_forums
				    VALUES ('".$ibforums->member['id']."', '".$fid."','".time()."')");
		}

		// return mode
		$DB->return_die = 0;
	}

 }

 function song_set_topicread( $topic_log, $fid, $tid ) {
 global $ibforums, $DB;

	if ( $ibforums->member['id'] )
	{
		// do safe query
		$DB->return_die = 1;

		if ( $topic_log )
		{
			$DB->query("UPDATE ibf_log_topics
				    SET logTime='".time()."'
				    WHERE
					tid='".$tid."' AND
					mid='".$ibforums->member['id']."'");
		} else
		{
			$DB->query("INSERT INTO ibf_log_topics
				    VALUES('".$ibforums->member['id']."', '".$tid."', '".$fid."','".time()."')");
		}

		// return mode
		$DB->return_die = 0;
	}

 }

// Song * new NEW system


    function board_offline()
    {
    	global $DB, $ibforums, $skin_universal;
    	
    	$ibforums->lang = $this->load_words($ibforums->lang, "lang_error", $ibforums->lang_id);
    	
    	$msg = preg_replace( "/\n/", "<br>", stripslashes($ibforums->vars['offline_msg']) );
    	
    	$html = $skin_universal->board_offline( $msg );

    	$print = new display();
    	
    	$print->add_output($html);
    		
    	$print->do_output( array(
	 			OVERRIDE   => 1,
	  			TITLE      => $ibforums->lang['offline_title'],
				 )
  			);
    }
    								
    /*-------------------------------------------------------------------------*/
    // Variable chooser             
    /*-------------------------------------------------------------------------*/
    
    function select_var($array) {
    	
    	if ( !is_array($array) ) return -1;
    	
    	ksort($array);
    	
    	
    	$chosen = -1;  // Ensure that we return zero if nothing else is available
    	
    	foreach ($array as $k => $v)
    	{
    		if (isset($v))
    		{
    			$chosen = $v;
    			break;
    		}
    	}
    	
    	return $chosen;
    }
      


// Song * enchased flood control, 23.02.05

    function flood_begin() {
    global $ibforums, $DB, $sess;

	if ( $ibforums->vars['flood_control'] > 0 )
	 if ( $ibforums->member['id'] ) 
	 {
        	// Flood check..
		if ( $ibforums->member['g_avoid_flood'] != 1 )
		{
			if ( time() - $ibforums->member['last_post'] < $ibforums->member['g_search_flood'] )
			{
				$this->Error( array( 'LEVEL' => 1,
						     'MSG' => 'flood_control',
						     'EXTRA' => $ibforums->member['g_search_flood'] ) );
			}
		}

	 } else
	 {
		// Additional flood check
		$DB->query("SELECT last_post
			    FROM ibf_sessions
			    WHERE id='".$sess->session_id."'");

		$last_post = $DB->fetch_row();

		if ( $last_post['last_post'] )
		{
			if ( ( time() - $last_post['last_post'] ) < $ibforums->vars['flood_control'] )
			{
				$this->Error( array( 'LEVEL' => 1,
						     'MSG' => 'flood_control',
						     'EXTRA' => $ibforums->vars['flood_control'] ) );
			}
		}
	 }
    }
    

    function flood_end() {
    global $ibforums, $DB, $sess;

	$time = time();

	if ( $ibforums->member['id'] )
	{
		$DB->query("UPDATE ibf_members
			    SET last_post='".$time."'
			    WHERE id='".$ibforums->member['id']."'");
	} else
	{
		$DB->query("UPDATE ibf_sessions
			    SET last_post='".$time."'
			    WHERE id='".$sess->session_id."'");
	}
    }


//###########################################################################
//===========================================================================
// INDEXED SEARCH FUNCTIONS by vot
//
// for conf_global.php settings:
// $INFO['search_sql_method'] =	'index';
//===========================================================================
// Prototypes:
//
// function index_del_title($tid=0)
// function index_del_post($pid=0)
// function index_del_posts($pidlist="")
// function index_del_topic($tid=0)
// function index_del_topics($tidlist="")
// function index_reindex_post($pid=0, $tid=0, $fid=0, $post)
// function index_reindex_title( $tid=0, $fid=0, $title)
// function index_move_topics($tids,$movetoforum);
// function index_wordlist ( $post = "")
// function index_make_index($pid=0, $tid=0, $fid=0, $words=array())
//===========================================================================
//###########################################################################


//-----------------------------------------------
// vot:
// Reindex the Post body
//-----------------------------------------------
function index_reindex_post ($pid=0, $tid=0, $fid=0, $post="")
{
  global $ibforums;

  if($ibforums->vars['search_sql_method'] == 'index') {

    $this->index_del_post($pid);

    $wordlist = $this->index_wordlist($post);

    $this->index_make_index($pid,$tid,$fid,$wordlist);
  }
}


//-----------------------------------------------
// vot:
// Reindex the Topic Title
//-----------------------------------------------
function index_reindex_title ( $tid=0, $fid=0, $title)
{
  global $ibforums;

  if($ibforums->vars['search_sql_method'] == 'index') {

    $this->index_del_title($tid);

    $wordlist = $this->index_wordlist($title);

    $this->index_make_index(0,$tid,$fid,$wordlist); // Post_ID = 0 for the title indexing !
  }
}


//------------------------------------------------------
// vot:
// delete indexed earlier words for the topic title only
//------------------------------------------------------

function index_del_title($tid=0)
{
global $ibforums, $DB;

  if($ibforums->vars['search_sql_method'] == 'index') {
	$DB->query("DELETE
		    FROM ibf_search
		    WHERE tid=$tid
			AND pid=0");
  }
}



//--------------------------------------------------
// vot:
// delete indexed earlier words for this post
//--------------------------------------------------

function index_del_post($pid=0)
{
global $ibforums, $DB;

  if($ibforums->vars['search_sql_method'] == 'index') {
	$DB->query("DELETE
		    FROM ibf_search
		    WHERE pid=$pid");
  }
}



//--------------------------------------------------
// vot:
// delete indexed earlier words for the postlist
//--------------------------------------------------

function index_del_posts($pidlist="")
{
global $ibforums, $DB;

  $pidlist = trim($pidlist);
  if($pidlist)
  {
    if($ibforums->vars['search_sql_method'] == 'index') {

	$DB->query("DELETE
		    FROM ibf_search
		    WHERE pid IN ($pidlist)");
    }
  }
}



//--------------------------------------------------
// vot:
// delete indexed earlier words for all this topic
//--------------------------------------------------

function index_del_topic($tid=0)
{
global $ibforums, $DB;

  if($ibforums->vars['search_sql_method'] == 'index') {

	$DB->query("DELETE
		    FROM ibf_search
		    WHERE tid=$tid");
  }
}



//--------------------------------------------------
// vot:
// delete indexed earlier words for the topic list
//--------------------------------------------------

function index_del_topics($tidlist="")
{
  global $ibforums, $DB;

  $tidlist = trim($tidlist);
  if($tidlist)
  {
    if($ibforums->vars['search_sql_method'] == 'index')
    {

	$DB->query("DELETE
		    FROM ibf_search
		    WHERE tid ".$tidlist);
    }
  }
}

//-------------------------------------------------
// vot:
// Update the search words - MOVE to another forum
//-------------------------------------------------
function index_move_topics ($tids,$movetoforum)
{
	global $ibforums, $DB;
	$tidlist = trim($tidlist);
	if($tids)
	{
	  if($ibforums->vars['search_sql_method'] == 'index')
	  {
  		$DB->query("UPDATE ibf_search
			    SET fid=$movetoforum
			    WHERE
				tid ".$tids);
				// tid ='nnn'  or  tid IN(nnn,nnn,nnn)
	  }
	}
}



//--------------------------------------------
// vot:
// Build New Search Index for a Title or Post
//--------------------------------------------

function index_make_index($pid=0, $tid=0, $fid=0, $words=array())
{
global $ibforums, $DB;

  if($ibforums->vars['search_sql_method'] == 'index') {
	$i = 1;
	foreach($words as $word) {		

		// get id of existing word
		$id = 0;
		$DB->query("SELECT id
			    FROM ibf_search_words
			    WHERE word='$word'");
//			    WHERE word='".addslashes($word)."'");

		if ( $row = $DB->fetch_row() )
		{
			$id = $row['id'];
//echo $i.": ".$word." ($id)<br>\n";
		}

		// insert the word
		if(!$id)
		{
			$DB->query("INSERT INTO ibf_search_words
					(word)
				    VALUES
					('$word')");
			$id = $DB->get_insert_id();
//echo $i.": ".$word." ($id) NEW!<br>\n";
		}

		// add the post/topic record for this word
		if ( $id )
		{
			$DB->query("INSERT INTO ibf_search
				    VALUES ($pid,
					    $tid,
					    $fid,
					    $id)");
		}
		$i++;
	}


	// Mark the Topic/Post as INDEXED allready

	if($pid) {
		$dsql = "UPDATE ibf_posts
			 SET indexed=1
			 WHERE pid='$pid'";
	} else {
		$dsql = "UPDATE ibf_topics
			 SET indexed=1
			 WHERE tid='$tid'";
	}

	$DB->query($dsql);

  }
  return;
}

//-----------------------------------------------
// vot:
// Parse the content, strip & return unique words
//-----------------------------------------------
function index_wordlist ( $post = "")
{
global $ibforums;

  $post = strtolower($post);


  // Replace line endings by a space
  $post = preg_replace("/[\n\r]/is", " ", $post); 


  $post = preg_replace("/<script.*?<\/script>/i", " ", $post); 
  $post = preg_replace("/<style.*?<\/style>/i", " ", $post); 

  // Clean UBB quote tags
  $post = preg_replace("/<\!--quotebegin-([^\-\-\>]+)-->/is", "<\!--quotebegin-->", $post); 
  $post = preg_replace("/<\!--quoteebegin-->/is", "<\!--quotebegin-->", $post); 
  $post = preg_replace("/<\!--quoteeend-->/is", "<\!--quoteend-->", $post); 

  while ( preg_match( "/<\!--quotebegin-->(?!<\!--quotebegin-->).+<\!--quoteend-->/is", $post ) )
  {
    $post = preg_replace("/<\!--quotebegin-->(?!<\!--quotebegin-->).+<\!--quoteend-->/is", " ", $post); 
  }

  $post = preg_replace("/\[quote[^\]]*\](.+?)\[\/quote\]/is", " ", $post); 
  

  // Clean smiles
  $post = preg_replace("/<\!--emo\&(.+?)-->(.+?)<\!--endemo-->/is", "\\1", $post); 
  $post = preg_replace("/:[\w\d]*:/is", " ", $post); 


  // Clean UBB tags
  $post = preg_replace("/\[[^\]]*\]/is", " ", $post); 


  // Clean HTTP:// prefixes
  $post = preg_replace("/(https|http|news|ftp):\/\//is", " ", $post); 
  $post = preg_replace("/mailto:/is", " ", $post); 


  // Clean all other HTML tags
  $post = preg_replace("/<[^>]*>/is", " ", $post); 


  // Clean Escape characters
  $post = preg_replace("/\&amp;/is", "\&", $post); 
  $post = preg_replace("/\&nbsp;/is", " ", $post); 
//  $post = preg_replace("/\&lt;/is", "<", $post); 
//  $post = preg_replace("/\&gt;/is", ">", $post); 

  $post = preg_replace("/(&.*?;)/is", " ", $post); 


  // Clean not numbers & not letters
  $post = preg_replace("/[^\d\w_]/is", " ", $post); 


  // Clean multiple spaces
  $post = trim($post); 
  $post = preg_replace("/\s+/is", " ", $post); 
//  $post = preg_replace("/\s{2,}/is", " ", $post); 
//  $post = preg_replace("/(^|\s)\S{1,2}(\s|$)/is", " ", $post); 


  $results = explode(" ", $post);

  $word_count = count($results);


  $i = 1;
  $uniq = array();
  $seen = array();


  foreach ($results as $item) {

    $length = strlen($item);

    if ( ($length >= $ibforums->vars[min_search_word]) &&
	 ($length <= $ibforums->vars[max_search_word]) )
    {
       if(!isset($seen[$item])) {
            $seen[$item] = 1;
            $uniq[] = $item;
        };
    }
    $i++;
  }

  return $uniq;
}




//##############################################################
// End of INDEXED SEARCH Routines
//##############################################################






    function update_favorites() {
      global $ibforums, $DB;

      $favlist = explode(",", $ibforums->member['favorites']);
      if(count($favlist)) {
        $mid = $ibforums->member['id'];
        foreach($favlist as $tid) {
          // Check for correct Topic ID
          $tid = preg_replace("/[^\d]/",'',$tid);
          if(strlen($tid) <= 9 && $tid > 0) {
            $DB->query("INSERT INTO ibf_favorites
                            (mid,tid) VALUES
                            ('$mid','$tid')");
          }
        }
        $DB->query("UPDATE ibf_members
                            SET favorites=''
                            WHERE id='$mid'");
      }
    }

    function get_favorites() {
      global $ibforums, $DB;

      $f = array();

      // Move Favs from ibf_members to ibf_favorites
      $this->update_favorites();

      // Get Favs from ibf_favorites
      $DB->query("SELECT tid FROM ibf_favorites
                  WHERE mid='".$ibforums->member['id']."'");
      while ( $row = $DB->fetch_row()) {
        $f[]=$row['tid'];
      }
      return $f;
    }




//+-------------------------------------------------
// vot: Check if the user is banned by IP ?
//+-------------------------------------------------
function is_ip_banned($ipaddr)
{
  global $ibforums;

  $res = 0;
  if ( $ibforums->vars['ban_ip'] )
  {
    $ips = explode( "|", $ibforums->vars['ban_ip'] );
    foreach ($ips as $ip)
    {

// New IP ban algorithm by archimed7592
//      $ip = preg_replace( "/\*/", '.*' , preg_quote($ip, "/") );
//      if ( preg_match( "/$ip/", $ipaddr ) )
      $ip = preg_quote($ip, "/");
      $ip = preg_replace( "/\\\\\\*/", '[0-9]{1,3}' , $ip );
      if ( preg_match( '/'.$ip.'/', $ipaddr ) )
      {
	$res = 1;
	break;
      }
    }
  }
  return $res;
}








} // end FUNC class $std


//==============================================================
//==============================================================
//==============================================================


//######################################################
// Our "print" class
//######################################################


class display {

    var $syntax = array();
    var $to_print = "";



    function is_new_fav_exists() {
        global $ibforums, $DB, $std;

//        $std->update_favorites();

        $DB->query("SELECT f.tid FROM ibf_favorites f
	    	    LEFT JOIN ibf_topics t
	    	         ON f.tid=t.tid
                    LEFT JOIN ibf_log_topics tr 
	    	         ON (f.mid=tr.mid AND f.tid=tr.tid)
		    WHERE
			  f.mid='".$ibforums->member['id']."' AND
			  t.last_post>tr.logTime
		    LIMIT 1");

        return $DB->get_num_rows();

/*

        $favs = $ibforums->member['favorites'];

        if ( $favs )  
	{
	    $DB->query("SELECT t.tid
	    	    FROM ibf_topics t, ibf_log_topics tr 
		    WHERE
			  tr.mid='".$ibforums->member['id']."' AND
			  tr.tid=t.tid AND 
			  t.tid IN ({$favs}) AND 
			  t.last_post>tr.logTime
		    LIMIT 1");

	    return $DB->get_num_rows();

	} else return 0;
*/

    }

    //-------------------------------------------
    // Appends the parsed HTML to our class var
    //-------------------------------------------
    
	function add_output($to_add) {
        $this->to_print .= $to_add;

        //return 'true' on success
        return true;

	}

	function nav($output_array) {
   	global $skin_universal, $ibforums, $std;

	$nav  = $skin_universal->start_nav( "_NEW" );

	$nav .= "<a href='{$ibforums->base_url}'>{$ibforums->vars['board_name']}</a>";

        if ( empty($output_array['OVERRIDE']) )
	{
	    if (is_array( $output_array['NAV'] ) )
	    {
		$mysep = "";
		foreach ($output_array['NAV'] as $n)
		{
		    if ( $n )
		    {
			$mysep .= "&nbsp;&nbsp;";
			$nav .= "<br>&nbsp;&nbsp;&nbsp;".$mysep."<{F_NAV_SEP}>".$n;
		    }
		}
	    }
	}
																							            
        $nav .= $skin_universal->end_nav();

	return $nav;

	}

	function nav_at_line($output_array) {

   	global $skin_universal, $ibforums, $std;

        $nav = "<{F_NAV}> <a href='{$ibforums->base_url}'>{$ibforums->vars['board_name']}</a>";
        
        if ( empty($output_array['OVERRIDE']) )
	{
		$level = 0;		

		if (is_array( $output_array['NAV'] ) ) foreach ($output_array['NAV'] as $n) 
		{
			if ( $n and $level != 0 ) $nav .= " · ".$n;

			$level++;
		}
	}

	return $nav;

    }




    //-------------------------------------------
    // vot: Rotate banners from array.
    //-------------------------------------------

    function rotate_banner($banners="")
    {
        global $ibforums, $std;

	$my_banners = array();
	$my_banners = explode("|",$banners);
	$content = "";

//if ($std->check_perms( $ibforums->member['club_perms'] ) or
if (
    $ibforums->member['is_mod'] or
    $ibforums->member['g_is_supmod']
   ) return $content;

	$num_of_banners = count($my_banners);

	if($num_of_banners)
	{
	  $i = rand(0,$num_of_banners-1);
//debug:	  $content = "$num_of_banners:$i:".$my_banners[$i];
	  $content = $my_banners[$i];
	}

        return $content;
    }





    //-------------------------------------------
    // vot: Output the XAP network banner.
    //-------------------------------------------
/*
    function xap_code($login)	// modified by vot
    {
     $path = ''; $file = ''; $site = str_replace('www.', '', $_SERVER["HTTP_HOST"]);
     if (strlen($_SERVER["REQUEST_URI"]) > 180) return; if ($_SERVER["REQUEST_URI"] == '') $_SERVER["REQUEST_URI"] = '/';
     $file = base64_encode("$_SERVER[REQUEST_URI]");
     $path_code = md5($file); $user_pref = substr($login, 0, 2);
     $path = substr($path_code, 0, 1).'/'.substr($path_code, 1, 2).'/';
     $domain = "$login.tnx.net";
     $path = "/users/$user_pref/$login/$site/$path$file.txt";
     $str=''; // vot
     
     if ($fp = @fsockopen ("$domain", 80, $errno, $errstr, 7))
     {
       fputs ($fp, "GET $path HTTP/1.0\r\nhost: $domain\r\n\r\n");
       $fl = 0;
       while (!feof($fp)) {
           $str = trim(fgets($fp,4096));
           if ($str == 'HTTP/1.1 404 Not Found')
	   {		// vot
	     $str='';	// vot
	     break;	// vot
	   }
           if ($fl == 1) break; // vot echo $str;
           if ($str == "") $fl = 1;
       }
       fclose ($fp);
     }
     return $str;
    }
// vot    xap_code(strtolower("vot"));
*/

    function xap_banner()
    {
        global $ibforums, $std;
/*
    //if ($std->check_perms( $ibforums->member['club_perms'] ) or
    if (
        $ibforums->member['is_mod'] or
        $ibforums->member['g_is_supmod']
       ) return $content;

//	$content = xap_code("vot");
        return "<!-- XAP -->\n".$this->xap_code("vot");
// vot test        return "<a href='#'>Test XAP code return</a>";
*/
$tnx = new TNX_l();
$content = "<!-- XAP banner -->\n";
$content .= $tnx->show_link(1); // выводим первую ссылку
$content .= $tnx->show_link(1); // выводим вторую ссылку, желательно в другом месте страницы, ниже
$content .= $tnx->show_link(1); // выводим третью ссылку, желательно в другом месте страницы, ниже
$content .= $tnx->show_link(); // выводим оставшиеся, желательно в другом месте страницы, ниже

       return $content;
       
    }



    //-------------------------------------------
    // Parses all the information and prints it.
    //-------------------------------------------

    function do_output($output_array) {
    global $DB, $Debug, $skin_universal, $ibforums, $std;

        if ( $ibforums->input['show_cp_order_number'] == 1 )
        {
        	// Show the IPS Copyright Removal order number.
        	// Note, this is designed to allow IPS validate boards
		// who've purchased copyright removal. The order number
        	// is the only thing shown and the order number is unique
		// to the person who paid and is no good to anyone else.
        	// Showing the order number poses no risk at all -
		//  the information is useless to anyone outside of IPS.
        	flush();
        	print ($ibforums->vars['ips_cp_purchase'] != "") ? $ibforums->vars['ips_cp_purchase'] : '0';
        	exit();
        }
        
	// Load the Macro Set

        $TAGS = $DB->query("SELECT
				macro_value,
				macro_replace
			    FROM ibf_macro
			    WHERE macro_set={$ibforums->skin['macro_id']}");
        
	// Song * Adjust the Favorites Icon

	$image = ( $ibforums->member['id'] && $this->is_new_fav_exists() )
		   ? "<{atb_favs_new}>" : "<{atb_favs}>";



	//---------------------------------------------
	// Check for DEBUG Mode
	//---------------------------------------------

	if ( $ibforums->member['g_access_cp'] )
	{
                if ( $DB->obj['debug'] )
                {
                	flush();
                	print "<html><head><title>mySQL Debugger</title><body bgcolor='white'><style type='text/css'> TABLE, TD, TR, BODY { font-family: verdana,arial, sans-serif;color:black;font-size:11px }</style>";
                	print $ibforums->debug_html;
                	print "</body></html>";
                	exit();
                }
                
                $input   = "";
                $queries = "";
                $sload   = "";
                
                if ( $ibforums->server_load > 0 )
                {
                	$sload = "&nbsp; [ Server Load: ".$ibforums->server_load." ]";
                }
                
		//+----------------------------------------------
                		  
		if ( $ibforums->vars['debug_level'] >= 2 )
		{
       	       		$stats .= "<br>\n<div class='tableborder'>\n<div class='pformstrip'>FORM and GET Input</div><div class='row1' style='padding:6px'>\n";
                
				while( list($k, $v) = each($ibforums->input) )
				{
					$stats .= "<strong>$k</strong> = $v<br>\n";
				}
				
				$stats .= "</div>\n</div>";
                
                }
                
                //+----------------------------------------------
                
                if ( $ibforums->vars['debug_level'] >= 3 )
                {
                   	$stats .= "<br>\n<div class='tableborder'>\n<div class='pformstrip'>Queries Used</div><div class='row1' style='padding:6px'>";
       	       					
                	foreach($DB->obj['cached_queries'] as $q)
                	{
                		$q = htmlspecialchars($q);
                		$q = preg_replace( "/^SELECT/i" , "<span class='red'>SELECT</span>"   , $q );
                		$q = preg_replace( "/^UPDATE/i" , "<span class='blue'>UPDATE</span>"  , $q );
                		$q = preg_replace( "/^DELETE/i" , "<span class='orange'>DELETE</span>", $q );
                		$q = preg_replace( "/^INSERT/i" , "<span class='green'>INSERT</span>" , $q );
                		$q = str_replace( "LEFT JOIN"   , "<span class='red'>LEFT JOIN</span>" , $q );
                		
                		$q = preg_replace( "/(".$ibforums->vars['sql_tbl_prefix'].")(\S+?)([\s\.,]|$)/", "<span class='purple'>\\1\\2</span>\\3", $q );
                		
                		$stats .= "$q<hr>\n";
                	}
                	
                	$stats .= "</div>\n</div>";
                }
	}

	//+----------------------------------------------

	if ($ibforums->vars['debug_level'] > 0)
	{
		$ex_time = sprintf( "%.4f",$Debug->endTimer() );

	        $query_cnt = $DB->get_query_cnt();

	        $gzip_status = $ibforums->vars['disable_gzip'] == 1 ? $ibforums->lang['gzip_off'] : $ibforums->lang['gzip_on'];

		// timestamp by barazuk
		$timestamp = $std->old_get_date(time(), 'LONG');

//		$stats = "<br>\n<br>\n<div align='center'>[ Script Execution time: $ex_time ] &nbsp; [ $query_cnt queries used ] &nbsp; [ $gzip_status ] $sload</div>\n<br>";
// barazuk		$stats = "<br>\n<br>\n<div align='center'>[ Script Execution time: $ex_time ] &nbsp; [ $query_cnt queries used ] &nbsp; $sload</div>\n<br>";
		// timestamp by barazuk
		$stats = "<br>\n<br>\n<div align='center'>[ Script Execution time: $ex_time ] &nbsp; [ $query_cnt queries used ] &nbsp; [ Generated: $timestamp ] &nbsp; $sload</div>\n<br>";

	}
		  


        /********************************************************/
        // NAVIGATION
        /********************************************************/

//	$nav = $this->nav($output_array);
     
        //----------------------------------------------------------------------
        // Different Navigation bar views for forum and D-Site
        // (we hane another navigation bar templates)
        // --
        // Date: 2006/02/19 (c) chainick
        //----------------------------------------------------------------------

        if ( !class_exists("csite") ) {

                $nav = $this->nav($output_array);

        } else {

//		$skin_universal = $std->load_template('skin_global');  //vot

                global $DSITE;
                $nav = $DSITE->site_bits['nav'];
        }



        //---------------------------------------------------------
        // CSS
        //---------------------------------------------------------

	// Song * CSS based on User CP + common CSS, 29.12.04

        if ( $ibforums->member['id'] and
	     $ibforums->member['css_method'] == 'external' )
        {
        	$css =  $skin_universal->css_external($ibforums->skin['css_id'], $ibforums->skin['img_dir'])."\n";

		// 13 - id of common CSS
		$css .= $skin_universal->css_external(13, $ibforums->skin['img_dir']);
        } else
        {
// vot        	$css = $skin_universal->css_inline( str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $ibforums->skin['css_text'] ) );
        	$css = $skin_universal->css_inline( str_replace( "<#IMG_DIR#>", $ibforums->vars['img_url'], $ibforums->skin['css_text'] ) );
        }
        
        
        //---------------------------------------------------------
        
        $extra = "";
        $ur    = '(U)';
        
        if ( $ibforums->vars['ipb_reg_number'] )
        {
        	$ur = '(R)';
        	
        	if ( $ibforums->vars['ipb_reg_show'] and $ibforums->vars['ipb_reg_name'] )
        	{
        		$extra = "<div align='center' class='copyright'>Registered to: ". $ibforums->vars['ipb_reg_name']."</div>";
        	}
        }


	//-------------------------------------------------------
	// Song + Mixxx * included js, client highlight, 23.12.04

	$js = "";

	if ( $ibforums->member['syntax'] == "client" )
	{
		$count = 0;

		foreach ($this->syntax as $row => $highlight)
		{
			$js .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/highlight/h_{$row}_{$highlight}.js'></script>\n";
			$count++;
		}
	
		if ( $count )
		{
			$js .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/h_core_{$ibforums->vars['client_highlight_core_version']}.js'></script>\n";
		}
	}

	if ( $output_array['JS'] ) 
	{
		$js .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/{$output_array['JS']}'></script>";
	}

	// End of Song + Mixxx * included js, 23.12.04

	// Copyrights
	
	// Yes, I realise that this is silly and easy to remove the copyright, but
	// as it's not concealed source, there's no point having a 1337 fancy hashing
	// algorithm if all you have to do is delete a few lines, so..
	// However, be warned: If you remove the copyright and you have not purchased
	// copyright removal, you WILL be spotted and your licence to use Invision Power Board
	// will be terminated, requiring you to remove your board immediately.
	// So, have a nice day.

        $copyright = "<!-- Copyright Information -->\n\n<div align='center' class='copyright'>Powered by <a rel='nofollow' href=\"http://www.invisionboard.com\" target='_blank'>Invision Power Board</a>{$ur} {$ibforums->version} &copy; 2003 &nbsp;<a rel='nofollow' href='http://www.invisionpower.com' target='_blank'>IPS, Inc.</a></div>\n";

        if ( $ibforums->vars['ips_cp_purchase'] ) $copyright = "";

        $copyright .= $extra;

        // Awww, cmon, don't be mean! Literally thousands of hours have gone into
        // coding Invision Power Board and all we ask in return is one measly little line
        // at the bottom. That's fair isn't it?
        // No? Hmmm...
        // Have you seen how much it costs to remove the copyright from UBB? o_O
                       



        /********************************************************/
        // Build the board header
        
        $this_header  = $skin_universal->BoardHeader("", $image);
        
        // Show rules link?
        
        if ( $ibforums->vars['gl_show'] and $ibforums->vars['gl_title'] )
        {
        	if ( !$ibforums->vars['gl_link'] )
        	{
        		$ibforums->vars['gl_link'] = $ibforums->base_url."act=boardrules";
        	}
        	
        	$this_header = str_replace( "<!--IBF.RULES-->", $skin_universal->rules_link($ibforums->vars['gl_link'], $ibforums->vars['gl_title']), $this_header );
        }
        


        //---------------------------------------
        // Build the members bar
	//---------------------------------------
		
        if ( !$ibforums->member['id'] )
        {
        	$output_array['MEMBER_BAR'] = $skin_universal->Guest_bar();
        } else
        {
		$pm_js = "";

		if ( ($ibforums->member['g_max_messages'] > 0) and ($ibforums->member['msg_total'] >= $ibforums->member['g_max_messages']) )
		{
			$msg_data['TEXT'] = $ibforums->lang['msg_full'];
		} else
		{
			$ibforums->member['new_msg'] = $ibforums->member['new_msg'] == "" ? 0 : $ibforums->member['new_msg'];

			$msg_data['TEXT'] = sprintf( $ibforums->lang['msg_new'], $ibforums->member['new_msg']);

			// CBP & vot: Check for NEW PM 

			if ($ibforums->member['new_msg']) 
			{
			    $msg_data['TEXT'] .= " <img border=0 src='{$ibforums->vars['board_url']}/html/sys-img/bat.gif'>";
			}


		}

		//---------------------------------------
		// Do we have a pop up to show?
		//---------------------------------------
		
		if ( $ibforums->member['show_popup'] )
		{
			$DB->query("UPDATE ibf_members
				    SET show_popup=0
				    WHERE id='".$ibforums->member['id']."'");
			
			if ( $ibforums->input['act'] != 'Msg' )
			{
				$pm_js = $skin_universal->PM_popup();
			}
		}
		
		$mod_link = "";

		$admin_link = $ibforums->member['g_access_cp'] ? $skin_universal->admin_link() : '';

		$valid_link = $ibforums->member['mgroup'] == $ibforums->vars['auth_group'] ? $skin_universal->validating_link() : '';
		
		if ( !$ibforums->member['g_use_pm'] )
        	{
        		$output_array['MEMBER_BAR'] = $skin_universal->Member_no_usepm_bar($admin_link, $mod_link, $valid_link);
        	} else
		{
			$output_array['MEMBER_BAR'] = $pm_js.$skin_universal->Member_bar($msg_data, $admin_link, $mod_link, $valid_link);
		}
	}
 		
	// vot:
	// Adjust the page title for russian search bots

	$output_array['TITLE'] = str_replace( ".RU", ".Ру", $output_array['TITLE']);


	// Check for OFFLINE BOARD

	if ( $ibforums->vars['board_offline'] )
	{
		$output_array['TITLE'] = $ibforums->lang['warn_offline']." ".$output_array['TITLE'];
	}
        
	$replace = array();
	$change  = array();



        //---------------------------------------
        // Get the template
        //---------------------------------------

	$replace[] = "<% CSS %>";
	$change[]  = $css;

	$replace[] = "<% JAVASCRIPT %>";
	$change[]  = $js;


	// Song * RSS, 29.01.05

	$replace[] = "<% RSS %>";

	if ( !$output_array['RSS'] ) $output_array['RSS'] = $skin_universal->rss();

	$change[]  = $output_array['RSS'];


	// Replace Blocks in the template

	$replace[] = "<% TITLE %>" ;
	$change[]  = $output_array['TITLE'];

	$replace[] = "<% BOARD %>";
	$change[]  = $this->to_print;

	$replace[] = "<% STATS %>";
	$change[]  = $stats;

	$replace[] = "<% GENERATOR %>";
	$change[]  = "";

	$replace[] = "<% COPYRIGHT %>";
	$change[]  = $copyright;

	$replace[] = "<% BOARD HEADER %>";
	$change[]  = $this_header;

	$replace[] = "<% NAVIGATION %>";
	$change[]  = $nav;




	//-----------------------------------
	// vot: header banner
	$replace[] = "<!-- HEADER_BANNER -->";
	$change[]  = $this->rotate_banner($ibforums->vars['banner_header']);

	//-----------------------------------
	// vot: top banner
	$replace[] = "<% TOP NAV BANNER %>";
	$change[]  = $this->rotate_banner($ibforums->vars['banner_top_nav']);

	//-----------------------------------
	// vot: middle banner
	$replace[] = "<% MIDDLE BANNER %>";
	$change[]  = $this->rotate_banner($ibforums->vars['banner_middle']);

	//-----------------------------------
	// vot: bottom banner
	$replace[] = "<% BOTTOM BANNER %>";
	$change[]  = $this->rotate_banner($ibforums->vars['banner_bottom']);


	//-----------------------------------
	// vot: bottom XAP banner
	$replace[] = "<% XAP BANNER %>";
	$change[]  = $this->xap_banner();






	// Song * secondary navigation

	$replace[] = "<!--IBF.NAVIGATION-->";
	$change[]  = $this->nav_at_line($output_array);



	$replace[] = "<% MEMBER BAR %>";

	if ( empty($output_array['OVERRIDE']) )
	{
		$change[] = $output_array['MEMBER_BAR'];
	} else
	{
		$change[] = $skin_universal->member_bar_disabled();
	}
      	


      	//+--------------------------------------------
	// Stick in banner?
	//+--------------------------------------------
	
	if ( $ibforums->vars['ipshosting_credit'] )
	{
		$replace[] = "<!--IBF.BANNER-->";
		$change[]  = $skin_universal->ibf_banner();
	}
	

	//+--------------------------------------------
	// Stick in chat link?
	//+--------------------------------------------
	
	if ( $ibforums->vars['chat_account_no'] )
	{
		$ibforums->vars['chat_height'] += 50;
		$ibforums->vars['chat_width']  += 50;
		
		$chat_link = ( $ibforums->vars['chat_display'] == 'self' ) 
			   ? $skin_universal->show_chat_link_inline()
			   : $skin_universal->show_chat_link_popup();
		
		$replace[] = "<!--IBF.CHATLINK-->";
		$change[]  = $chat_link;
	}


      	//+--------------------------------------------
      	//| Get the macros and replace them
      	//+--------------------------------------------
      	
      	while ( $row = $DB->fetch_row($TAGS) )
      	{
		if ( $row['macro_value'] )
		{
			$replace[] = "<{".$row['macro_value']."}>";
			$change[]  = $row['macro_replace'];
		}
	}
		
	$replace[] = "<#IMG_DIR#>";
//vot	$change[]  = $ibforums->skin['img_dir'];
	$change[]  = $ibforums->vars['img_url'];	// vot

	$replace[] = "<#BASE_URL#>";     	// vot
	$change[]  = $ibforums->base_url;	// vot

// vot: removed the Song d-site patch
// Song * d-site patch, 12.05.05
//
//	if ( $ibforums->vars['pre_board_url'] )
//	{
//		$replace[] = "img src='style_images/";
////		$replace[] = "img src='smiles/";
//
//		$change[]  = "img src='".$ibforums->vars['pre_board_url']."/style_images/";
////		$change[]  = "img src='".$ibforums->vars['pre_board_url']."/smiles/";
//	}
//
// Song * d-site patch, 12.05.05


	//---------------------------------------
	// Do replace in template
	//---------------------------------------

	$ibforums->skin['template'] = str_replace($replace, $change, $ibforums->skin['template'] );

	//---------------------------------------
	// Close this DB connection
	//---------------------------------------

	$DB->close_db();

	//---------------------------------------
	// Start GZIP compression
        //---------------------------------------

        if ($ibforums->vars['disable_gzip'] != 1)
        {
        	$buffer = ob_get_contents();
        	ob_end_clean();
        	ob_start('ob_gzhandler');
        	print $buffer;
        }

        $this->do_headers();

        print $ibforums->skin['template'];
        
// Debug query list for Chainick (id=487)
//	if ( $ibforums->member['id'] == 487 ) {
//                print "\n<hr><div align = 'left'>";
//                foreach ($DB->obj['cached_queries'] as $query) {
//                        print "\n$query";
//                        print "<hr>";
//                        print "\n</div>";
//                }
//	}
// end of debug query list

        exit;
    }
    
    //-------------------------------------------
    // print the headers
    //-------------------------------------------
        
    function do_headers() {
    	global $ibforums;
    	
    	if ($ibforums->vars['print_headers'])
    	{
		@header("HTTP/1.0 200 OK");
		@header("HTTP/1.1 200 OK");
		@header("Content-type: text/html");
			
		if ($ibforums->vars['nocache'])
		{
			@header("Cache-Control: no-cache, must-revalidate, max-age=0");
			@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			@header("Pragma: no-cache");
		}
        }
    }
    
    function redirect_js_screen($do, $action, $params, $do_echo = "") {

	@header("conten-type: text/javascript");

	$to_echo = "";

	if ( $do )
	 if ( !is_array($do) )
	 {
		$to_echo = "toChangeLink('{$do}',{$action},'{$params}');";

	 } elseif ( count($do) )
	 {
		foreach($do as $to_do) 
		{
			$to_echo .= "toChangeLink('{$to_do}',{$action},'{$params}');";
		}
	 }

	$to_echo = $to_echo.$do_echo;	
	
	$to_echo = str_replace("[nn]", "<br>", $to_echo);

	echo $to_echo;

	exit();
    }

    //-------------------------------------------
    // print a pure redirect screen
    //-------------------------------------------

    function redirect_screen($text="", $url="", $type="") {
    global $std, $ibforums;

	if ( $ibforums->input['debug'] ) 
	{
		flush();
		exit();
	}

	$std->boink_it($ibforums->base_url.$url, $type);

	exit();
     }

    //-------------------------------------------
    // print a minimalist screen suitable for small
    // pop up windows
    //-------------------------------------------
    
    function pop_up_window($title = 'Invision Power Board', $text = "" ) {
    global $ibforums, $skin_universal, $DB;
    	
    	//---------------------------------------------------------
        // CSS
        //---------------------------------------------------------
        
	// CSS based on User CP + common CSS, Song * 29.12.04

        if ( $ibforums->member['id'] and $ibforums->member['css_method'] == 'external' )
        {
        	$css =  $skin_universal->css_external($ibforums->skin['css_id'], $ibforums->skin['img_dir'])."\n";

		// 13 - id of common CSS
		$css .= $skin_universal->css_external(13, $ibforums->skin['img_dir'])."\n";
        } else
        {
        	$css = $skin_universal->css_inline( str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $ibforums->skin['css_text'] ) )."\n";
        }
        


	// Song + Mixxx * included js, client highlight, 23.12.04

	if ( $ibforums->member['syntax'] == "client" )
	{
		$count = 0;

		foreach ($this->syntax as $row => $highlight)
		{
			$css .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/highlight/h_{$row}_{$highlight}.js'></script>\n";
			$count++;
		}

		if ( $count )
		{
			$css .= "<script type='text/javascript' src='{$ibforums->vars['board_url']}/html/h_core_{$ibforums->vars['client_highlight_core_version']}.js'></script>\n";
		}
	}



    	$html = $skin_universal->pop_up_window($title, $css, $text);

	// Load Macro Values

    	$TAGS = $DB->query("SELECT
				macro_value,
				macro_replace
			    FROM ibf_macro
			    WHERE macro_set='{$ibforums->skin['macro_id']}'");

    	while ( $row = $DB->fetch_row($TAGS) )
      	{
		if ($row['macro_value'] != "")
		{
			$html = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $html );
		}
	}

	$html = str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $html );

    	$DB->close_db();

    	if ( $ibforums->vars['disable_gzip'] != 1 )
        {
        	$buffer = ob_get_contents();
        	ob_end_clean();
        	ob_start('ob_gzhandler');
        	print $buffer;
        }

        $this->do_headers();
        
    	echo ($html);
    	exit;
    } 
    


    
} // END class
    




//######################################################
// Our "session" class
//######################################################

class session {

    var $ip_address 	   = 0;
    var $user_agent 	   = "";
    var $time_now   	   = 0;
    var $session_id 	   = 0;
    var $session_dead_id   = 0;
    var $session_user_id   = 0;
    var $session_user_pass = "";
    var $last_click        = 0;
    var $member            = array();
    var $location          = "";
    var $r_location        = "";
    var $in_forum          = "";
    var $r_in_forum        = "";
    var $in_topic          = "";
    var $r_in_topic        = "";

    // No need for a constructor
   

    function authorise()
    {
        global $DB, $INFO, $ibforums, $std;
        
        //-------------------------------------------------
        // Before we go any lets check the load settings..
        //-------------------------------------------------
        
        if ( $ibforums->vars['load_limit'] > 0 )
        {
        	if ( file_exists('/proc/loadavg') )
        	{
        		if ( $fh = @fopen( '/proc/loadavg', 'r' ) )
        		{
        			$data = @fread( $fh, 6 );
        			@fclose( $fh );
        			
        			$load_avg = explode( " ", $data );
        			
        			$ibforums->server_load = trim($load_avg[0]);
        			
        			if ( $ibforums->server_load > $ibforums->vars['load_limit'] )
        			{
        				$std->Error( array(
						'LEVEL' => 1,
						'MSG' => 'server_too_busy',
						'INIT' => 1 ) );
        			}
        		}
        	}
        }
       
        //--------------------------------------------
	// Are they banned?
	//--------------------------------------------
		
	if($std->is_ip_banned($ibforums->input['IP_ADDRESS']))
	{
        	$std->Error( array( 'LEVEL' => 1,
				    'MSG' => 'you_are_banned',
				    'EXTRA' => '['.$ibforums->input['IP_ADDRESS'].']',
				    'INIT' => 1 ) );
	}
        
        //--------------------------------------------
        
        $this->member = array(
				'id' => 0,
				'password' => "",
				'name' => "",
				'mgroup' => $INFO['guest_group'] );
        
        //--------------------------------------------
        // no new headers if we're simply viewing an attachment..
        //--------------------------------------------

// vot        if ( $ibforums->input['act'] == 'Attach' )
// vot        {
// vot        	return $this->member;
// vot        }
        
        $_SERVER['HTTP_USER_AGENT'] = $std->clean_value($_SERVER['HTTP_USER_AGENT']);
        
        $this->ip_address = $ibforums->input['IP_ADDRESS'];

        $this->user_agent = substr($_SERVER['HTTP_USER_AGENT'],0,50);

        $this->time_now   = time();
        
        //-------------------------------------------------
        // Manage bots? (tee-hee)
        //-------------------------------------------------
        
        if ( $ibforums->vars['spider_sense'] == 1 )
        {
		$remap_agents = array(

	                              'googlebot'       => 'google',
	                              'slurp' 	     => 'inktomi',
	                              'ask jeeves'      => 'jeeves',
	                              'lycos'           => 'lycos',
	                              'whatuseek'       => 'wuseek',
	                              'ia_archiver'     => 'Archive_org',
	                              'aport'           => 'Aport',
	                              'yandex'          => 'Yandex',
	                              'stackrambler'    => 'Rambler',
	                              'turtlescanner'   => 'Turtle',
	                              'scooter'         =>  'Altavista',
	                              'gigabot'         =>  'Gigablast',
	                              'zyborg'          =>  'ZyBorg',
	                              'fast-webcrawler' => 'FAST',
	                              'openbot'         =>  'Openfind',
	                              'libwww'          =>  'libwww-FM',
	                              'yahoo'           =>  'Yahoo',
	                              'msnbot'          =>   'MSN',
                                 );

		if ( preg_match( '/(googlebot|slurp|yahoo|msnbot|ask jeeves|lycos|whatuseek|ia_archiver|aport|yandex|stackrambler|scooter|
				   gigabot|turtlescanner|zyborg|fast-webcrawler|openbot|libwww)/i', 
				   $_SERVER['HTTP_USER_AGENT'], $match ) )
		{
        		
        		$DB->query("SELECT *
				    FROM ibf_groups
				    WHERE g_id='".$ibforums->vars['spider_group']."'");

        		$group = $DB->fetch_row();
        
			foreach ($group as $k => $v) $this->member[ $k ] = $v;
				
			$this->member['restrict_post']    = 1;
			$this->member['g_use_search']     = 0;
			$this->member['g_email_friend']   = 0;
			$this->member['g_edit_profile']   = 0;
			$this->member['g_use_pm']         = 0;
			$this->member['g_is_supmod']      = 0;
			$this->member['g_access_cp']      = 0;
			$this->member['g_access_offline'] = 0;
			$this->member['g_avoid_flood']    = 0;
			$this->member['id']      	  = 0;
				
			$ibforums->perm_id       	  = $this->member['g_perm_id'];
       			$ibforums->perm_id_array 	  = explode( ",", $ibforums->perm_id );
       			$ibforums->session_type  	  = 'cookie';
       			$ibforums->is_bot        	  = 1;
       			$this->session_id        	  = "";
       			
			$match = strtolower($match[1]);

		        if ( !$agent = $remap_agents[$match] ) $agent = 'google';
       			
       			if ( $ibforums->vars['spider_visit'] )
       			{
       				$dba = $DB->compile_db_insert_string( array (

					'bot'          => $agent,
					'query_string' => str_replace( "'", "", $_SERVER['QUERY_STRING']),
					'ip_address'   => $_SERVER['REMOTE_ADDR'],
					'entry_date'   => time(),
				)        );
       													
       				$DB->query("INSERT INTO ibf_spider_logs
						({$dba['FIELD_NAMES']})
					    VALUES
						({$dba['FIELD_VALUES']})");
       			}
       			
       			if ( $ibforums->vars['spider_active'] )
       			{
       				$DB->query("DELETE FROM ibf_sessions
					    WHERE id='".$agent."_session'");

       				$this->create_bot_session($agent);
       			}
       			
       			return $this->member;
		}
	}
        
        //-------------------------------------------------
        // Continue!
        //-------------------------------------------------
        
        $cookie = array();
        $cookie['session_id']   = $std->my_getcookie('session_id');
        $cookie['member_id']    = $std->my_getcookie('member_id');
        $cookie['pass_hash']    = $std->my_getcookie('pass_hash');

	if ( isset($ibforums->vars['plg_custom_login']) && $ibforums->vars['plg_custom_login'] )
        {
	        $plg_sess = $this->plg_get_session();
                if ( $plg_sess ) $this->get_session($plg_sess); else $this->session_id = 0;

        } elseif ( $cookie['session_id'] )
        {
        	$this->get_session($cookie['session_id']);
        	$ibforums->session_type = 'cookie';

        } elseif ( isset($ibforums->input['s']) )
        {
        	$this->get_session($ibforums->input['s']);
        	$ibforums->session_type = 'url';

        } else $this->session_id = 0;
        
        //-------------------------------------------------
        // Finalise the incoming data..
        //-------------------------------------------------

	//Schooler invisible login patch
        if(!isset($ibforums->input['Privacy'])) {
        	$ibforums->input['Privacy'] = $std->my_getcookie('anonlogin');
	}
												      
	//-------------------------------------------------								  
	// Do we have a valid session ID?
	//-------------------------------------------------
	
	if ( $this->session_id )
	{
		// We've checked the IP addy and browser, so we can assume that this is
		// a valid session.
		
		if ( $this->session_user_id != 0 and !empty($this->session_user_id) )
		{
			// It's a member session, so load the member.
			$this->load_member($this->session_user_id);
			
			// Did we get a member?
			if ( !$this->member['id'] or $this->member['id'] == 0 )
			{
				$this->unload_member();
				$this->update_guest_session();

			} else $this->update_member_session();

		} else $this->update_guest_session();
	
	} else
		// We didn't have a session, or the session didn't validate
	{
		// Forumizer auth
		if ( isset($ibforums->vars['plg_custom_login']) && $ibforums->vars['plg_custom_login'] ) 
		{
			$this->load_member($ibforums->vars['plg_user_id']);
			
			// such member exists ?
			if ( !$this->member['id'] or $this->member['id'] == 0 )
			{
				$this->unload_member();

				if ( !$ibforums->info['plg_allow_guests'] ) 
				{
					$std->Error(array('LEVEL' => 1,
							  'MSG' => 'no_guests',
							  'INIT' => 1));
				}

				$this->create_guest_session();
			} else
			{
				// check a password
				if ( $this->member['password'] == $ibforums->vars['plg_user_pass'] )
				{
					$this->create_member_session();
				} else
				{
					$this->unload_member();

					if( !$ibforums->info['plg_allow_guests'] ) 
					{
						$std->Error(array(
							'LEVEL' => 1,
							'MSG' => 'no_guests',
							'INIT' => 1));
					}

					$this->create_guest_session();
				}
			}

		// Do we have cookies stored?

		} elseif ( $cookie['member_id'] != "" and $cookie['pass_hash'] != "" )
		{
			$this->load_member($cookie['member_id']);
			
			// such member exists ?
			if ( !$this->member['id'] or $this->member['id'] == 0 )
			{
				$this->unload_member();
				$this->create_guest_session();
			} else
			{
				// check a password
				if ( $this->member['password'] == $cookie['pass_hash'] )
				{
					$this->create_member_session();
				} else
				{
					$this->unload_member();
					$this->create_guest_session();
				}
			}

		} else $this->create_guest_session();
	}
		
        //-------------------------------------------------
        // Set up a guest if we get here and we don't have a member ID
        //-------------------------------------------------
        
        if ( !$this->member['id'] )
        {
        	$this->member = $std->set_up_guest();

        	$DB->query("SELECT *
			    FROM ibf_groups
			    WHERE g_id='".$ibforums->vars['guest_group']."'");

        	$group = $DB->fetch_row();

        	if(is_array($group))
  		  foreach ($group as $k => $v) $this->member[ $k ] = $v;
	} else
        {
	        //------------------------------------------------
	        // Synchronise the last visit and activity times if
	        // we have some in the member profile
	        //-------------------------------------------------

        	if ( !$ibforums->input['last_activity'] )
        	{
			if ( $this->member['last_activity'] )
			{
				$ibforums->input['last_activity'] = $this->member['last_activity'];
			} else
			{
				$ibforums->input['last_activity'] = $this->time_now;
			}
        	}
        	
        	if ( !$ibforums->input['last_visit'] )
        	{
			if ( $this->member['last_visit'] )
			{
				$ibforums->input['last_visit'] = $this->member['last_visit'];
			} else
			{
				$ibforums->input['last_visit'] = $this->time_now;
			}
        	}
        
		//-------------------------------------------------
		// If there hasn't been a cookie update in 2 hours,
		// we assume that they've gone and come back
		//-------------------------------------------------

		if ( !$this->member['last_visit'] ) 
		{
			// No last visit set, do so now!
			$DB->query("UPDATE ibf_members
				    SET
					last_visit='".$this->time_now."',
					last_activity='".$this->time_now."' 
				    WHERE id='".$this->member['id']."'");


			// Song * look at died accounts

			if ( $this->member['check_id'] )
			{
				$DB->query("UPDATE ibf_check_members
					    SET last_visit='".$this->time_now."' 
					    WHERE mid='".$this->member['check_id']."'");
			}



		} elseif ( (time() - $ibforums->input['last_activity']) > 300 )
		{
			// If the last click was longer than 5 mins ago and this is a member
			// Update their profile.
			$DB->query("UPDATE ibf_members
				    SET last_activity='".$this->time_now."'
				    WHERE id='".$this->member['id']."'");

			// Song * look at died accounts

			if ( $this->member['check_id'] )
			{
				$DB->query("UPDATE ibf_check_members
					    SET last_visit='".$this->time_now."' 
					    WHERE mid='".$this->member['check_id']."'");
			}


		}
		
		//-------------------------------------------------
		// Check ban status
		//-------------------------------------------------
		
		if ( $this->member['temp_ban'] )
		{
			if ( $this->member['temp_ban'] == 1 ) 
			{
				$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'you_are_banned',
//				    		    'EXTRA' => '['.$ibforums->input['IP_ADDRESS'].']',
						    'INIT' => 1 ) );
			} else
			{
				$ban_arr = $std->hdl_ban_line($this->member['temp_ban']);

				if ( time() >= $ban_arr['date_end'] )
				{
					// Update this member's profile
					$DB->query("UPDATE ibf_members
						    SET temp_ban=''
						    WHERE id='".$this->member['id']."'");
				} else
				{
					$ibforums->member = $this->member; // Set time right

					$std->Error( array(
						'LEVEL' => 1,
						'MSG' => 'account_susp',
						'INIT' => 1,
						'EXTRA' => $std->get_date($ban_arr['date_end'],
						'LONG') ) );
				}
			}
		}
	}

	//-------------------------------------------------
        // Set a session ID cookie
        //-------------------------------------------------

        $std->my_setcookie("session_id", $this->session_id, -1);

	// permissions based on a group
        $ibforums->perm_id = $this->member['g_perm_id'];

	// Song * enchaced rights, 16.12.04

	// additional permissions basen on a member
	$org_perm_id = isset($this->member['org_perm_id']) ? $this->member['org_perm_id'] : '';
	if ( $org_perm_id )
	{
		$ibforums->perm_id = ( $ibforums->perm_id )
					? $ibforums->perm_id.",".$org_perm_id
					: $org_perm_id;
	}



        $ibforums->perm_id_array = explode( ",", $ibforums->perm_id );
        
        return $this->member;
        
    }
    

    //+-------------------------------------------------
    // Attempt to load a member
    //+-------------------------------------------------

    function load_member($member_id=0) {
    global $DB, $std, $ibforums;
    	
    	$member_id = intval($member_id);

	if ( $member_id != 0 )
	{
		$DB->query("SELECT
				m.*,
				g.*,
				IFNULL(es.name,'Main') as sskin_name,
				md.mid as is_mod,
				md.allow_warn,
				md.time_deleted_link,
				cm.mid as check_id,
				cm.sent 
			FROM ibf_members m 
            		LEFT JOIN ibf_groups g
				ON (g.g_id=m.mgroup) 
			LEFT JOIN ibf_emoticons_skins es
				ON (es.id=m.sskin_id) 
			LEFT JOIN ibf_check_members cm
				ON (cm.mid=m.id) 
            		LEFT JOIN ibf_moderators md
				ON (md.member_id=m.id) 
            		WHERE m.id='".$member_id."'
			LIMIT 1");
            
		if ( $DB->get_num_rows() ) 
		{
			$this->member = $DB->fetch_row();

			if ( $this->member['id'] )
			{
				// Song * club tool
				$DB->query("SELECT read_perms
					    FROM ibf_forums
					    WHERE id='".$ibforums->vars['club']."'");

				if ( $club = $DB->fetch_row() ) $this->member['club_perms'] = $club['read_perms'];



// vot: BAD MESSAGE! yesterday, today

				// Song * today/yesterday

				if ( $this->member['language'] == 'en' )
				{
					$this->member['yesterday'] = 'Yesterday';
					$this->member['today'] = 'Today';

					if ( $this->member['disable_mail'] and !$this->member['disable_mail_reason'] )
					{
						$this->member['disable_mail_reason'] = "(no information)";
					}				
				} else
				{
					$this->member['yesterday'] = 'Вчера';
					$this->member['today'] = 'Сегодня';

					if ( $this->member['disable_mail'] and !$this->member['disable_mail_reason'] )
					{
						$this->member['disable_mail_reason'] = "(нет информации)";
					}				
				}

				$time = time();

				// if member has more than 50 posts,
				// leave him, abandon see him

				if ( $this->member['check_id'] )
				{                   
					if ( $this->member['posts'] > $ibforums->vars['check_before'] )
					{
					  $DB->query("DELETE
						      FROM ibf_check_members
						      WHERE mid='".$this->member['check_id']."'");
					  $this->member['check_id'] = "";

					} elseif ( $this->member['sent'] )
					{
					  $DB->query("UPDATE ibf_check_members
						      SET
							sent=0,
							last_visit='".$time."' 
						      WHERE mid='".$this->member['check_id']."'");

					  $this->member['sent'] = 0;
					}
				}

/* <--- Jureth --- 
 * Publish the moderated forums list
 * Hint: Result of query include forums, moderated by groups, rather than prev.
 * 
**/

				$this->member['modforums']=false;
				$mod = array();

				$DB->query("SELECT forum_id
					    FROM ibf_moderators
					    WHERE member_id='".$this->member['id']."' or group_id='".$this->member['mgroup']."'");
        
				while ( $row = $DB->fetch_row($res) ) $mod[] = $row['forum_id'];
				if (count($mod)){
					$this->member['modforums']=implode(',', $mod);
				}
				unset($mod);

/* >--- Jureth --- */

				// if this is a user
				if ( !$this->member['is_mod'] )
				{


// ********* REMOVE WARNINGS of current member *****************************************

					// if user group is not banned
					if ( $this->member['mgroup'] != $ibforums->vars['ban_group'] )
					{
					       $DB->query("SELECT id
							   FROM ibf_warnings
							   WHERE
								mid='".$this->member['id']."' and
								RestrictDate<".	$time."
							   ORDER BY RestrictDate DESC
							   LIMIT 1");
                        
						if ( $DB->get_num_rows() )
						{
							$row = $DB->fetch_row();
        
							$level = intval($this->member['warn_level']);
        
							// decrease level
							$level--;
        
							// define a new group of user
							if ( $level > 0 )
							{					
								$group = 15 + $level;
							} else
							{
								$group = $this->member['old_group'];
							}
                        
							 // update group and warn level of user
							$DB->query("UPDATE ibf_members
								    SET
									mgroup='".$group."',
									warn_level='".$level."' 
								    WHERE id='".$this->member['id']."'");
        
							// accept new group and warn level for already fetched row
							$this->member['mgroup'] = $group;
							$this->member['warn_level'] = $level;
        
							// delete old record about violation
							$DB->query("DELETE FROM ibf_warnings
								    WHERE id='".$row['id']."'");
// vot: BAD MESSAGE!        
							$mes = "Уменьшен уровень Ваших предупреждений в связи с истечением срока действия одного из них.\n\n";
        
							if ( $group == $ibforums->vars['member_group'] or $group == $ibforums->vars['club_group'] or $group == 26 or $group == 9 )
							{
// vot: BAD MESSAGE!        
								$mes .= "[color=green]Вы обратно переведены в группу участников.[/color]";
							} else
							{
// vot: BAD MESSAGE!        
								$mes .= "[color=red]Вы переведены в группу нарушивших правила уровня ".$level.".[/color]";
							}
        
							$save = array();
        
							$save['wlog_type'] 	= "pos";
							$save['wlog_date'] 	= $time;
							$save['wlog_notes']  	= "<content>{$mes}</content>";
							$save['wlog_notes']    .= "<mod>,,</mod>";
							$save['wlog_notes']    .= "<post>,,</post>";
							$save['wlog_notes']    .= "<susp>,</susp>";
							$save['wlog_mid']     	= $this->member['id'];
							$save['wlog_addedby'] 	= 8617;
        
							$dbs = $DB->compile_db_insert_string( $save );
        
							$DB->query("INSERT INTO ibf_warn_logs
									({$dbs['FIELD_NAMES']})
								    VALUES ({$dbs['FIELD_VALUES']})");
						}
					}

// ************** END OF REMOVE WARNINGS *****************************************

				// if this is moderator
				} elseif ( !$this->member['time_deleted_link'] or 
                                           ( $time - intval($this->member['time_deleted_link']) > 60*60*24 ) 
                                         ) 
				{







// *****************************************************************************
// ************* BEGIN MODERATORS DAILY TASKS **********************************
// *****************************************************************************

					// a day within two days ago
					$old_day = $std->yesterday_day(2);
        
					// a month within two days ago
					$old_month = $std->yesterday_month(2);
				
// *************** DELETE VISITORS LOGS ******************************************************

					// delete old logs of visits
					$DB->query("DELETE FROM ibf_m_visitors
						    WHERE
							day<='".$old_day."' and
							month='".$old_month."'");
					$DB->query("DELETE FROM ibf_g_visitors
						    WHERE
							day<='".$old_day."' and
							month='".$old_month."'");
					$DB->query("DELETE FROM ibf_b_visitors
						    WHERE
							day<='".$old_day."' and
							month='".$old_month."'");
					$DB->query("DELETE FROM ibf_users_stat
						    WHERE
							day<='".$old_day."' and
							month='".$old_month."'");



					if ( $this->member['modforums'] ) //Jureth
					{
//Jureth				  // ids of forums
//Jureth				  $mod = implode(",", $mod);

// ************* DELETE LINKS ( 7 days ) ******************************************************
// *********** !!! MOVED TO CRON by vot !!! ***********
//					  // delete links for a week
//					  $DB->query("DELETE
//						      FROM ibf_topics
//						      WHERE
//							state='link' and 
//							link_time<".$time."-60*60*24*7 and 
//							forum_id IN ({$this->member['modforums']})");

// ************* DELETE logs visiting of topics ( 30 days ) ***********************************
// *********** !!! MOVED TO CRON by vot !!! ***********
//
//					  // do not delete logs for pinned topics.
//					  // Collect ids of pinned topics
//
//					  $DB->query("SELECT tid
//						    FROM ibf_topics
//						    WHERE
//							pinned=1 and
//							approved=1 and 
//							forum_id IN ({$this->member['modforums']})");
//  
//					  $ids = array();
//
//					  while ( $topic = $DB->fetch_row() ) $ids[] = $topic['tid'];
//
//					  if ( count($ids) )
//					  {
//					    $ids = implode(",", $ids);
//
//					    $DB->query("DELETE
//						    FROM ibf_log_topics
//						    WHERE
//							not (tid IN ({$ids})) and 
//							fid IN ({$this->member['modforums']}) and 
//							logTime<".$time."-60*60*24*30");
//
//					    unset($ids);
//					  }

// ***************** DELETE moderatorial posts ( 7 days ) ******************************************

					  $topics = array();                 
					  $forums = array();

					  // querying data for topics and forums recount
					  $DB->query("SELECT attach_id, topic_id, forum_id FROM ibf_posts 
						    WHERE
							forum_id IN ({$this->member['modforums']}) and 
							use_sig=1 and
							post_date<".$time."-60*60*24*7");

					  // clear attachments
					  $this->clear_posts(&$topics, &$forums);

					  // delete moderatorial posts
					  $DB->query("DELETE
						FROM ibf_posts
						WHERE
							forum_id IN ({$this->member['modforums']}) and 
							use_sig=1 and post_date<".$time."-60*60*24*7");

// ***** DELETE delayed posts ( various days, depends from forums settings ) *************************

					  $DB->query("SELECT
							attach_id,
							topic_id,
							forum_id
						      FROM ibf_posts 
						      WHERE
							forum_id IN ({$this->member['modforums']}) and
							delete_after != 0 and 
							delete_after<".$time);

					  // clear attachments
					  $this->clear_posts(&$topics, &$forums);

					  // delete delayed posts
					  $DB->query("DELETE FROM ibf_posts
						      WHERE
							forum_id IN ({$this->member['modforums']}) and 
							delete_after != 0 and
							delete_after<".$time);

// ************************************** RECOUNT forums and topics after deleting posts *******************************

					  // update statistics
					  $this->topic_recount($topics);
					  $this->forum_recount($forums);

					  if ( count($topics) )
					  {
					    $this->stats_recount();

					    unset($topics);
					    unset($forums);
					  }

//Jureth				  unset($mod);
					}

// **************** CHECK USERS that do not visit forum ( 150 days ) *************************************

// vot: BAD MESSAGE - use language !
					$title = "Уведомление об предполагаемом удалении Вашего аккаунта";

					$mes = "Здравствуйте, %s!\n";
					$mes .= "Вы не посещали Форум на Исходниках.RU (http://forum.sources.ru) уже в течение 150-ти дней.\n";
					$mes .= "По правилам нашего форума аккаунт удаляется, если участник ни разу не посещает форум в течение 6 месяцев, ";
					$mes .= "авторизуясь под своим аккаунтом. Поэтому, если Вы не зайдёте на наш форум ещё в течение месяца, ";
					$mes .= "Ваш аккаунт будет удалён без дальнейших предупреждений.\n";
					$mes .= "Если Вы просто редко посещаете наш форум и намереваетесь продолжать его посещать дальше, просто зайдите ";
					$mes .= "на форум, авторизуясь под своим ником.\n";
					$mes .= "Если Вы уверены, что это ошибка, зайдите на наш форум и сообщите об этом администратору или ";
					$mes .= "напишите сейчас об ошибке на адрес admin@sources.ru\n\n";
					$mes .= "С уважением, администрация Форума на Исходниках.RU";
                        
					// send warnings letters
					$DB->query("SELECT
							cm.mid,
							m.name,
							m.mgroup,
							m.temp_ban 
						    FROM
							ibf_check_members cm,
							ibf_members m 
						    WHERE
							m.id=cm.mid and
							cm.sent=0 and
							cm.last_visit<".$time."-60*60*24*150");
        
					while ( $row = $DB->fetch_row() ) 
					{
					  if ( $row['mgroup'] == $ibforums->vars['ban_group'] or $row['temp_ban'] )
					  {
						continue;
					  }

					  $std->sendpm($row['mid'], sprintf($mes, $row['name']), $title, 8617, 1, 1);
					}
                        
					// mark members
					$DB->query("UPDATE ibf_check_members
						    SET sent=1
						    WHERE
							sent=0 and 
							last_visit<".$time."-60*60*24*150");
                        
// ******* DELETE USERS that do not visit forum ( 180 days )  *****************************

// vot: NEED TO have preserved members!!!

					$ids = array();
                        
					// delete non active users that were sent letters
					$DB->query("SELECT mid
						    FROM ibf_check_members
						    WHERE
							sent=1 and 
							last_visit<".$time."-60*60*24*180");

					while ( $row = $DB->fetch_row() ) $ids[] = $row['mid'];

					if ( count($ids) )
					{
					  $std->delete_members(" IN (".implode(",", $ids).")");

					  unset($ids);
					}

// *************************************************************************************************************************
// ************************** END OF MODERATORS TASKS ************************************************						
// *************************************************************************************************************************

// ***********************************************************************************************************************
// ************************** BEGIN ADMINS TASKS *****************************************************
// ************************* for admins-moderators ***************************************************
// ***********************************************************************************************************************

					// Delete users that marked their accounts to deleting
					if ( $this->member['mgroup'] == $ibforums->vars['admin_group'] )
					{
					  $ids = array();

					  $DB->query("SELECT id
						      FROM ibf_members
						      WHERE
							profile_delete_time != 0 and
							profile_delete_time<'".$time."'");

						while ( $member = $DB->fetch_row() ) $ids[] = $member['id'];

						if ( count($ids) )
						{
						  $std->delete_members(" IN (".implode(",", $ids).")");
						  unset($ids);
						}
					}

// *************************************************************************************************************************
// ************************************************ END OF ADMINS TASKS ****************************************************						
// *************************************************************************************************************************

					// mark time when moderator work has been made
					$DB->query("UPDATE ibf_moderators
						    SET time_deleted_link='".$time."' 
						    WHERE mid='".$this->member['is_mod']."'");

				} // end of moderator tasks

			} // end of user tasks
		}

		//-------------------------------------------------
		// Unless they have a member id, log 'em in as a guest
		//-------------------------------------------------
            
		if ( $this->member['id'] == 0 or empty($this->member['id']) ) $this->unload_member();
	}

	unset($member_id);

    }



	//+-------------------------------------------------
	// Remove the users cookies
	//+-------------------------------------------------
	
	function unload_member() {
	global $std;
		
	// Boink the cookies
		
	$std->my_setcookie( "member_id" , "0", -1  );
	$std->my_setcookie( "pass_hash" , "0", -1  );
		
	$this->member['id']       = 0;
	$this->member['name']     = "";
	$this->member['password'] = "";
		
	}
    


	
	// Song * topic and forum recount within deleting of moderatorial posts, 15.02.05

	//---------------------------------------------------
	function clear_posts($topics, $forums) {
	global $ibforums, $DB;

	while ( $row = $DB->fetch_row() )
	{
		$forums[ $row['forum_id'] ] = $row['forum_id'];
		$topics[ $row['topic_id'] ] = $row['topic_id'];

		// delete attached files
		if ( $row['attach_id'] and is_file($ibforums->vars['upload_dir']."/".$row['attach_id']) )
		{
			@unlink($ibforums->vars['upload_dir']."/".$row['attach_id']);
		}
	}

	}

	//---------------------------------------------------
	function topic_recount( $tids = array() ) {
	global $ibforums, $DB, $std;
		
	if ( !count($tids) ) return;
	
	foreach($tids as $tid)
	{
		$DB->query("SELECT COUNT(pid) AS posts
			    FROM ibf_posts
			    WHERE
				topic_id='".$tid."' and
				queued != 1");

		if ( $posts = $DB->fetch_row() )
		{
			$posts = $posts['posts'] - 1;
		
			$DB->query("SELECT
					post_date,
					author_id,
					author_name
				    FROM ibf_posts 
				    WHERE
					topic_id='".$tid."' and
					queued != 1
				    ORDER BY pid DESC
				    LIMIT 1");

			$last_post = $DB->fetch_row();
		
			$DB->query("UPDATE ibf_topics
				    SET
					last_post='".$last_post['post_date']."',
					last_poster_id='".$last_post['author_id']."',
					last_poster_name='".$last_post['author_name']."',
					posts='".$posts."' 
				    WHERE tid='".$tid."'");
		}
	}

	}

	//----------------------------------------------------
	function forum_recount( $fids = array() ) {
	global $DB;
		
	if ( !count($fids) ) return;
	
	foreach($fids as $fid)
	{
		// Get the topics..
		$DB->query("SELECT COUNT(tid) as count
			    FROM ibf_topics
			    WHERE
				approved=1 and
				forum_id='".$fid."'");
		$topics = $DB->fetch_row();
	
		// Get the posts..
		$DB->query("SELECT COUNT(pid) as count
			    FROM ibf_posts
			    WHERE
				queued != 1 and
				forum_id='".$fid."'");
		$posts = $DB->fetch_row();
	
		// Get the forum last poster..
		$DB->query("SELECT
				tid,
				title,
				last_poster_id,
				last_poster_name,
				last_post 
			    FROM ibf_topics
			    WHERE
				approved=1 and
				forum_id='".$fid."' and
				club=0 
			    ORDER BY last_post DESC
			    LIMIT 1");

		$last_post = $DB->fetch_row();
	
		// Get real post count by removing topic starting posts from the count
		$real_posts = $posts['count'] - $topics['count'];
	
		// Reset this forums stats
		$db_string = $DB->compile_db_update_string( array (
				'last_poster_id'   => $last_post['last_poster_id'],
				'last_poster_name' => $last_post['last_poster_name'],
				'last_post'        => $last_post['last_post'],
				'last_title'       => $last_post['title'],
				'last_id'          => $last_post['tid'],
				'topics'           => $topics['count'],
				'posts'            => $real_posts
				 )        );
											 
		$DB->query("UPDATE ibf_forums
			    SET $db_string
			    WHERE id='".$fid."'");
	}

	}

	//------------------------------------------------------
	// @stats_recount: Recount all topics & posts
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------
	
	function stats_recount()
	{
		global $ibforums, $DB, $std;
	
		$DB->query("SELECT COUNT(tid) as tcount from ibf_topics WHERE approved=1");
		$topics = $DB->fetch_row();
		
		$DB->query("SELECT COUNT(pid) as pcount from ibf_posts WHERE queued != 1");
		$posts  = $DB->fetch_row();
		
		$posts = $posts['pcount'] - $topics['tcount'];
		
		$DB->query("UPDATE ibf_stats SET TOTAL_TOPICS=".$topics['tcount'].", TOTAL_REPLIES=".$posts);
	}

	// /Song * topic and forum recount within deleting of posts, 15.02.05



    //-------------------------------------------
    // Get a session based on the current session ID
    //-------------------------------------------
    
    function get_session($session_id="") {
      global $DB, $INFO, $std;
        
        $result = array();
        
        $query = "";
        
        $session_id = preg_replace("/([^a-zA-Z0-9])/", "", $session_id);
        
        if ( $session_id )
        {
		if ( $INFO['match_browser'] == 1 ) $query = " AND browser='".$this->user_agent."'";
			
		$DB->query("SELECT
				id,
				member_id,
				running_time,
				location,
				in_forum,
				in_topic,
				r_location,
				r_in_forum,
				r_in_topic 
			    FROM ibf_sessions 
			    WHERE
				id='".$session_id."' and
				ip_address='".$this->ip_address."'".$query);
		
		if ( $DB->get_num_rows() != 1 )
		{
			// Either there is no session, or we have more than one session..
			$this->session_dead_id   = $session_id;
			$this->session_id        = 0;
        		$this->session_user_id   = 0;

        		return;

		} else
		{
			$result = $DB->fetch_row();
		
			if ( $result['id'] == "" )
			{
				$this->session_dead_id   = $session_id;
				$this->session_id        = 0;
				$this->session_user_id   = 0;
				unset($result);
				return;

			} else
			{
				$this->session_id        = $result['id'];
				$this->session_user_id   = $result['member_id'];
				$this->last_click        = $result['running_time'];
			
        			$this->location          = $result['location'];
        			$this->r_location        = $result['r_location'];
				
        			$this->in_forum          = $result['in_forum'];
        			$this->r_in_forum        = $result['r_in_forum'];

        			$this->in_topic          = $result['in_topic'];
        			$this->r_in_topic        = $result['r_in_topic'];

        			unset($result);
				return;
			}
		}
	}
    }
    
    //-----------------------------------------------------------
    function plg_get_session() {
    global $ibforums, $DB;
        
	if ( $ibforums->vars['plg_custom_login'] ) 
	{
		$ibforums->vars['plg_user_id'] = intval($ibforums->vars['plg_user_id']);
		if ( !$ibforums->vars['plg_user_id'] ) return NULL;
		
		$DB->query("SELECT password
			    FROM ibf_members
			    WHERE id='".$ibforums->vars['plg_user_id']."'");
		if ( $DB->get_num_rows() != 1 ) return NULL;

		$pass = $DB->fetch_row();
		if ( $pass['password'] != $ibforums->vars['plg_user_pass'] ) return NULL;

		$DB->query("SELECT id
			    FROM ibf_sessions
			    WHERE member_id='".$ibforums->vars['plg_user_id']."'");
		if ( $DB->get_num_rows() != 1 ) return NULL;

		$sess = $DB->fetch_row();
		return $sess['id'];
	}

	return NULL;        
    }


    //-------------------------------------------
    // Creates a member session.
    //-------------------------------------------
    
    function create_member_session()
    {
        global $DB, $INFO, $std, $ibforums;
        
        if ( $this->member['id'] )
        {
        	//---------------------------------
        	// Remove the defunct sessions
        	//---------------------------------
        	
		$INFO['session_expiration'] = $INFO['session_expiration'] ? (time() - $INFO['session_expiration']) : (time() - 3600);
		
		$DB->query("DELETE FROM ibf_sessions
			    WHERE running_time < {$INFO['session_expiration']}");

                $DB->query("DELETE FROM ibf_sessions
			    WHERE member_id='".$this->member['id']."'");

		$this->session_id  = md5( uniqid(microtime()) );

		//---------------------------------
        	// Insert the new session
        	//---------------------------------
        	
	        $db_str = $DB->compile_db_insert_string( array(
				'id'           => $this->session_id,
				'member_name'  => $this->member['name'],
				'member_id'    => $this->member['id'],
				'ip_address'   => $this->ip_address,
				'browser'      => $this->user_agent,
				'running_time' => $this->time_now,
				'login_type'   => $ibforums->input['Privacy'],
				'member_group' => $this->member['mgroup'],
				'org_perm_id'  => $this->member['org_perm_id'],
			     )
			);

		$DB->query("INSERT INTO ibf_sessions ({$db_str['FIELD_NAMES']}) VALUES({$db_str['FIELD_VALUES']})");

		// If this is a member, update their last visit times, etc.
		if ( time() - $this->member['last_activity'] > 300 )
		{
			//---------------------------------
			// Reset the topics read cookie..
			//---------------------------------

			$DB->query("UPDATE ibf_members
				    SET
					last_visit=last_activity,
					last_activity='".$this->time_now."' 
				    WHERE id='".$this->member['id']."'");
			
			// Update the Last Visit time
			// (used at lookking for died accounts

			if ( $this->member['check_id'] )
			{
				$DB->query("UPDATE ibf_check_members
					    SET last_visit='".$this->time_now."' 
					    WHERE mid='".$this->member['check_id']."'");
			}



			//---------------------------------
			// Fix up the last visit/activity times.
			//---------------------------------
			
			$ibforums->input['last_visit']    = $this->member['last_activity'];
			$ibforums->input['last_activity'] = $this->time_now;
		}


		// Song * who was today online (members)

		$std->who_was_member($this->member['id']);


	} else $this->create_guest_session();

    }

    //--------------------------------------------------------------------
    
    function create_guest_session() {
    global $DB, $INFO, $std, $ibforums;
        
	//---------------------------------
	// Remove the defunct sessions
	//---------------------------------
	
	if ( ($this->session_dead_id != 0) and ( ! empty($this->session_dead_id) ) )
	{
		$extra = " or id='".$this->session_dead_id."'";
	} else
	{
		$extra = "";
	}
	
	$INFO['session_expiration'] = $INFO['session_expiration'] ? (time() - $INFO['session_expiration']) : (time() - 3600);
	
	$DB->query( "DELETE FROM ibf_sessions WHERE running_time < {$INFO['session_expiration']} or ip_address='".$this->ip_address."'".$extra);

	$this->session_id  = md5( uniqid(microtime()) );
	
	//---------------------------------
	// Insert the new session
	//---------------------------------
	
        $db_str = $DB->compile_db_insert_string( array(
			'id'           => $this->session_id,
			'member_name'  => '',
			'member_id'    => 0,
			'ip_address'   => $this->ip_address,
			'browser'      => $this->user_agent,
			'running_time' => $this->time_now,
			'login_type'   => 0,
			'member_group' => $INFO['guest_group'],
				)
			);

	$DB->query("INSERT INTO ibf_sessions ({$db_str['FIELD_NAMES']}) VALUES({$db_str['FIELD_VALUES']})");


	// Song * who was today online (guest)

	$std->who_was_guest_or_bot(	'ibf_g_visitors',
					'guests',
					$this->session_id,
					$this->ip_address);



    }
    
    //-------------------------------------------
    // Creates a BOT session
    //-------------------------------------------
    
    function create_bot_session($bot) {
        global $DB, $INFO, $std, $ibforums;

	$session_id = $bot."_session";

	if(!isset($ibforums->input['f'])) $ibforums->input['f'] = 0;
	if(!isset($ibforums->input['t'])) $ibforums->input['t'] = 0;
	if(!isset($ibforums->input['act'])) $ibforums->input['act'] = '';
	if(!isset($ibforums->input['p'])) $ibforums->input['p'] = '';
	if(!isset($ibforums->input['CODE'])) $ibforums->input['CODE'] = '';

        $db_str = $DB->compile_db_insert_string( array(
			'id'           => $session_id,
			'member_name'  => $ibforums->vars['sp_'.$bot],
			'member_id'    => 0,
			'member_group' => $ibforums->vars['spider_group'],
			'in_forum'     => intval($ibforums->input['f']),
			'in_topic'     => intval($ibforums->input['t']),
			'login_type'   => $ibforums->vars['spider_anon'],
			'running_time' => $this->time_now,
			'location'     => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'],
			'ip_address'   => $this->ip_address,
			'browser'      => $this->user_agent,
		  )
					  );
											  
	$DB->query("INSERT DELAYED INTO ibf_sessions ({$db_str['FIELD_NAMES']}) VALUES({$db_str['FIELD_VALUES']})");


	// Song * who was today online (bot)

	$std->who_was_guest_or_bot(	'ibf_b_visitors',
					'bots',
					$session_id,
					$this->ip_address);

					   
    }
    
    //-------------------------------------------
    // Updates a current session.
    //-------------------------------------------
    
    function update_member_session() {
        global $DB, $ibforums;
        
        // Make sure we have a session id.
        
        if ( !$this->session_id )
        {
        	$this->create_member_session();
        	return;
        }
        
	if ( (substr($this->r_location, 0, strpos($this->r_location, '|')) == 
	      $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE']) AND 
	     (substr($this->r_in_forum, 0, strpos($this->r_in_forum, ',')) == 
              intval($ibforums->input['f']) ) AND 
	     (substr($this->r_in_topic, 0, strpos($this->r_in_topic, ',')) == intval($ibforums->input['t'])) 
	   ) 
        {

          return;

	} else
        {

		if ( empty($this->member['id']) )
		{
	        	$this->unload_member();
	        	$this->create_guest_session();
	        	return;
		}
	
		$locasion = $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'].'|'.$this->location;

		if ( !$this->in_forum ) $this->in_forum = '0';
		if ( !$this->in_topic ) $this->in_topic = '0';

		$in_forum = intval($ibforums->input['f']).','.$this->in_forum;
		$in_topic = intval($ibforums->input['t']).','.$this->in_topic;
       	
		$temp_loc = explode('|', $locasion);

		if ( count($temp_loc) > 10 )
		{
			$locasion = substr($locasion, 0, strrpos($locasion, '|'));
			$in_forum = substr($in_forum, 0, strrpos($in_forum, ','));
			$in_topic = substr($in_topic, 0, strrpos($in_topic, ','));
		}
       	
		$db_str = $DB->compile_db_update_string( array(
				'member_name'  => $this->member['name'],
				'member_id'    => intval($this->member['id']),
				'member_group' => $this->member['mgroup'],
				'in_forum'     => $in_forum,
				'r_in_forum'   => intval($ibforums->input['f']),
				'in_topic'     => $in_topic,
				'r_in_topic'   => intval($ibforums->input['t']),
				'login_type'   => $ibforums->input['Privacy'],
				'running_time' => $this->time_now,
				'location'     => $locasion,
				'r_location'   => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'],
				  )
			  );
											  
		$DB->query("UPDATE ibf_sessions
			    SET $db_str
			    WHERE id='{$this->session_id}'");
	}

     }        
    
    //--------------------------------------------------------------------
    
    function update_guest_session()
    {
        global $DB, $ibforums, $INFO;
        
        // Make sure we have a session id.
        if ( !$this->session_id )
        {
        	$this->create_guest_session();
        	return;
        }
        
	if ( (substr($this->r_location, 0, strpos($this->r_location, '|')) == 
	      $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE']) AND 
	     (substr($this->r_in_forum, 0, strpos($this->r_in_forum, ',')) == 
	      intval($ibforums->input['f']) ) AND 
	     (substr($this->r_in_topic, 0, strpos($this->r_in_topic, ',')) == intval($ibforums->input['t'])) ) 
        {
           return;

	} else
        {

       	 $locasion = $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'].'|'.$this->location;
       	 if ($this->in_forum == '') $this->in_forum = '0';
       	 if ($this->in_topic == '') $this->in_topic = '0';
       	 $in_forum = intval($ibforums->input['f']).','.$this->in_forum;
       	 $in_topic = intval($ibforums->input['t']).','.$this->in_topic;
       	
       	 $temp_loc = explode('|', $locasion);
       	 if (count($temp_loc) > 10)
       	  {
       		$locasion = substr($locasion, 0, strrpos($locasion, '|'));
       		$in_forum = substr($in_forum, 0, strrpos($in_forum, ','));
       		$in_topic = substr($in_topic, 0, strrpos($in_topic, ','));
       	  }
        	
         $db_str = $DB->compile_db_update_string( array(
			'member_name'  => '',
			'member_id'    => 0,
			'member_group' => $INFO['guest_group'],
			'login_type'   => '0',
			'running_time' => $this->time_now,
			'in_forum'     => $in_forum,
			'r_in_forum'   => intval($ibforums->input['f']),
			'in_topic'     => $in_topic,
			'r_in_topic'   => intval($ibforums->input['t']),
			'location'     => $locasion,
			'r_location'   => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'],
				  )
			  );

         // Update the database
         $DB->query("UPDATE ibf_sessions
		     SET $db_str
		     WHERE id='{$this->session_id}'");
        }

     } 

    //-------------------------------------------
    // Updates BOT's current session.
    //-------------------------------------------
    
    function update_bot_session($bot)
    {
        global $DB, $ibforums, $INFO;
        
	$session_id = $bot.'='.str_replace('.','',$this->ip_address ).'_session';
	
	$db_str = $DB->compile_db_update_string(
				 array(
				 		'member_name'  => $ibforums->vars['sp_'.$bot],
						'member_id'    => 0,
						'member_group' => $ibforums->vars['spider_group'],
						'in_forum'     => intval($ibforums->input['f']),
						'in_topic'     => intval($ibforums->input['t']),
						'login_type'   => $ibforums->vars['spider_anon'],
						'running_time' => $this->time_now,
						'location'     => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE']
					  )
			  );
				  
        $DB->query("UPDATE ibf_sessions SET $db_str WHERE id='".$session_id."'");
        
    }        
    
        
}

//---------------------------------------------------------------
class TNX_l
{
        var $_login = 'vot'; // логин в системе
        var $_timeout_connect = 5; // таймаут - максимальное время ожидания загрузки ссылок, секунд
        var $_connect_using = 'fsock'; // curl или fsock - можно выбрать способ соединения
        var $_html_delimiter = '<br>'; // разделитель между ссылками, можно изменить на любой
        var $_encoding = ''; // выбор кодировки вашего сайта. Пусто - win-1251 (по умолчанию). Также возможны: KOI8-U, UTF-8 (необходим модуль iconv на хостинге)
        var $_exceptions = 'PHPSESSID'; // здесь можно написать через пробел части урлов для запрещения их индексации системой, в т.ч. из robots.txt. Это урлы, не доступные поисковикам, или не существующие страницы. После индексации не менять.
        /*******************************/
        var $_return_point = 0;
        var $_content = '';

        function TNX_l()
        {
                if($this->_connect_using == 'fsock' AND !function_exists('fsockopen')){echo 'Ошибка, внешние коннекты на хостинге отключены, обратитесь к хостеру или попробуйте CURL.'; return false;}
                if($this->_connect_using == 'curl' AND !function_exists('curl_init')){echo 'Ошибка, CURL не поддерживается, попробуйте fsock.'; return false;}
                if(!empty($this->_encoding) AND !function_exists("iconv")){echo 'Ошибка, iconv не поддерживается.'; return false;}

                if ($_SERVER['REQUEST_URI'] == '') $_SERVER['REQUEST_URI'] = '/';
                if (strlen($_SERVER['REQUEST_URI']) > 180) return false;

                if(!empty($this->_exceptions))
                {
                        $exceptions = explode(' ', $this->_exceptions);
                        for ($i=0; $i<sizeof($exceptions); $i++)
                        {
                                if($_SERVER['REQUEST_URI'] == $exceptions[$i]) return false;
                                if($exceptions[$i] == '/' AND preg_match("#^\/index\.\w{1,5}$#", $_SERVER['REQUEST_URI'])) return false;
                                if(strpos($_SERVER['REQUEST_URI'], $exceptions[$i]) !== false) return false;
                        }
                }

                $this->_login = strtolower($this->_login); $this->_host = $this->_login . '.tnx.net'; $file = base64_encode($_SERVER['REQUEST_URI']);
                $user_pref = substr($this->_login, 0, 2); $md5 = md5($file); $index = substr($md5, 0, 2);
                $site = str_replace('www.', '', $_SERVER['HTTP_HOST']);
                $this->_path = '/users/' . $user_pref . '/' . $this->_login . '/' . $site. '/' . substr($md5, 0, 1) . '/' . substr($md5, 1, 2) . '/' . $file . '.txt';
                $this->_url = 'http://' . $this->_host . $this->_path;
                $this->_content = $this->get_content();
                if($this->_content !== false)
                {
                        $this->_content_array = explode('<br>', $this->_content);
                        for ($i=0; $i<sizeof($this->_content_array); $i++)
                        {
                                $this->_content_array[$i] = trim($this->_content_array[$i]);
                        }
                }
        }
        /*!!!*/
        function show_link($num = false)
        {
                if(!isset($this->_content_array)) return false;
                $links = '';
                if(!isset($this->_content_array_count)){$this->_content_array_count = sizeof($this->_content_array);}
                if($this->_return_point >= $this->_content_array_count) return false;

                if($num === false OR $num >= $this->_content_array_count)
                {
                        for ($i = $this->_return_point; $i < $this->_content_array_count; $i++)
                        {
                                $links .= $this->_content_array[$i] . $this->_html_delimiter;
                        }
                        $this->_return_point += $this->_content_array_count;
                }
                else
                {
                        if($this->_return_point + $num > $this->_content_array_count) return false;
                        for ($i = $this->_return_point; $i < $num + $this->_return_point; $i++)
                        {
                                $links .= $this->_content_array[$i] . $this->_html_delimiter;
                        }
                        $this->_return_point += $num;
                }
                return (!empty($this->_encoding)) ? iconv("windows-1251", $this->_encoding, $links) : $links;
        }
        function get_content()
        {
                $user_agent = 'TNX_l ip: ' . $_SERVER['REMOTE_ADDR'];
                $page = '';
                if ($this->_connect_using == 'curl' OR ($this->_connect_using == '' AND function_exists('curl_init')))
                {
                        $c = curl_init($this->_url);
                        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $this->_timeout_connect);
                        curl_setopt($c, CURLOPT_HEADER, false);
                        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($c, CURLOPT_TIMEOUT, $this->_timeout_connect);
                        curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
                        $page = curl_exec($c);
                        if(curl_error($c) OR (curl_getinfo($c, CURLINFO_HTTP_CODE) != '200' AND curl_getinfo($c, CURLINFO_HTTP_CODE) != '404') OR strpos($page, 'fsockopen') !== false)
                        {
                                curl_close($c);
                                return false;
                        }
                        curl_close($c);
                }
                elseif($this->_connect_using == 'fsock')
                {
                        $buff = '';
                        $fp = @fsockopen($this->_host, 80, $errno, $errstr, $this->_timeout_connect);
                        if ($fp)
                        {
                                fputs($fp, "GET " . $this->_path . " HTTP/1.0\r\n");
                                fputs($fp, "Host: " . $this->_host . "\r\n");
                                fputs($fp, "User-Agent: " . $user_agent . "\r\n");
                                fputs($fp, "Connection: Close\r\n\r\n");

                                stream_set_blocking($fp, true);
                                stream_set_timeout($fp, $this->_timeout_connect);
                                $info = stream_get_meta_data($fp);

                                while ((!feof($fp)) AND (!$info['timed_out']))
                                {
                                        $buff .= fgets($fp, 4096);
                                        $info = stream_get_meta_data($fp);
                                }
                                fclose($fp);

                                if ($info['timed_out']) return false;

                                $page = explode("\r\n\r\n", $buff);
                                $page = $page[1];
                                if((!preg_match("#^HTTP/1\.\d 200$#", substr($buff, 0, 12)) AND !preg_match("#^HTTP/1\.\d 404$#", substr($buff, 0, 12))) OR $errno!=0 OR strpos($page, 'fsockopen') !== false) return false;
                        }
                }
                if(strpos($page, '404 Not Found')) return '';
                return $page;
        }
}



