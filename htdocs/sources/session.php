<?php


//######################################################
// Our "session" class
//######################################################

require_once dirname(__FILE__).'/Attach.php';
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
        global $DB, $ibforums, $std;
        
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
				'mgroup' => $ibforums->vars['guest_group'] );
        
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
	                              'slurp' 	        => 'inktomi',
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
		Attach2::deleteAllPostAttachments($row);
		/*
		if ( $row['attach_id'] and is_file($ibforums->vars['upload_dir']."/".$row['attach_id']) )
		{
			@unlink($ibforums->vars['upload_dir']."/".$row['attach_id']);
		}
		*/
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
      global $DB, $ibforums, $std;
        
        $result = array();
        
        $query = "";
        
        $session_id = preg_replace("/([^a-zA-Z0-9])/", "", $session_id);
        
        if ( $session_id )
        {
		if ( $ibforums->vars['match_browser'] == 1 ) $query = " AND browser='".$this->user_agent."'";
			
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
        global $DB, $std, $ibforums;
        
        if ( $this->member['id'] )
        {
        	//---------------------------------
        	// Remove the defunct sessions
        	//---------------------------------
        	
		$ibforums->vars['session_expiration'] = $ibforums->vars['session_expiration'] ? (time() - $ibforums->vars['session_expiration']) : (time() - 3600);
		
		$DB->query("DELETE FROM ibf_sessions
			    WHERE running_time < {$ibforums->vars['session_expiration']}");

                $DB->query("DELETE FROM ibf_sessions
			    WHERE member_id='".$this->member['id']."'");

		$this->session_id  = md5( uniqid(microtime()) );

		//---------------------------------
        	// Insert the new session
        	//---------------------------------
        	
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
				'r_location'   => $ibforums->input['act'].",".$ibforums->input['p'].",".$ibforums->input['CODE'],
				'location'     => $locasion,
				'r_in_topic'   => intval($ibforums->input['t']),
				'in_topic'     => $in_topic,
				'in_forum'     => $in_forum,
	        	'r_in_forum'   => intval($ibforums->input['f']),
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
    global $DB, $std, $ibforums;
        
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
	
	$ibforums->vars['session_expiration'] = $ibforums->vars['session_expiration'] ? (time() - $ibforums->vars['session_expiration']) : (time() - 3600);
	
	$DB->query( "DELETE FROM ibf_sessions WHERE running_time < {$ibforums->vars['session_expiration']} or ip_address='".$this->ip_address."'".$extra);

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
			'member_group' => $ibforums->vars['guest_group'],
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
        global $DB, $std, $ibforums;

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
        global $DB, $ibforums;
        
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
			'member_group' => $ibforums->vars['guest_group'],
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
        global $DB, $ibforums;
        
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





} // End of class Session




