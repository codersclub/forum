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
|   > Post core module
|   > Module written by Matt Mecham
|   > Date started: 14th February 2002
|
|   > Module Version 1.0.0
+--------------------------------------------------------------------------
*/

require_once "mimecrutch.php"; // Barazuk

$idx = new Post;

class Post {

    var $output    = "";
    var $base_url  = "";
    var $html      = "";
    var $parser    = "";
    var $moderator = array();
    var $forum     = array();
    var $topic     = array();
    var $category  = array();
    var $mem_groups = array();
    var $mem_titles = array();
    var $obj        = array();
    var $email      = "";
    var $can_upload = 0;
    var $md5_check  = "";
    var $module     = "";
    var $act	    = array();
    var	$nav_extra  = array();
	public $upload_errors = array();


    //-------------------------------------------------------
    // Post class initialization for D-Site
    // (because we can't redeclare class objects)
    // --
    // Date: 24/08/2005 (c) Anton
    //-------------------------------------------------------

    function csite_init() {
    global $ibforums, $std, $skin_universal;

            //--------------------------------------
            // Load skin'n'language sets
            //--------------------------------------

            $this->html = $std->load_template('skin_post');
//            $skin_universal = $std->load_template('skin_global'); //эту строку ”ƒјЋ»“№!!!
            $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

            //--------------------------------------
            // finished!
            //--------------------------------------

            return true;
    }


    //-------------------------------------------------------
    // Return JavaScript skin set from skin_post.php
    //-------------------------------------------------------

    function csite_get_javascript() {
    global $ibforums;

            //--------------------------------------
            // finished!
            //--------------------------------------

            return $this->html->get_javascript();
    }


    /***********************************************************************************/
    //
    // Our constructor, load words, load skin, print the topic listing
    //
    /***********************************************************************************/
    
    function Post()
    {
        global $ibforums, $DB, $std, $print, $skin_universal, $sess;
        
        //--------------------------------------
        // Do not load stuff when the class
        // functions're called from D-Site
        // --
        // Date: 24/08/2005 (c) Anton
        //--------------------------------------

        if ( class_exists('mod_nav') ) {

                return false;
        }

        require ROOT_PATH."sources/lib/post_parser.php";
        
        $this->parser = new post_parser(1);
        
        //--------------------------------------
	// Compile the language file
	//--------------------------------------
		
        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_post',  $ibforums->lang_id);
        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);
        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_error', $ibforums->lang_id);
        
        $this->html     = $std->load_template('skin_post');
        
        //--------------------------------------------
    	// Get the sync module
	//--------------------------------------------
		
	if ( USE_MODULES == 1 )
	{
		require ROOT_PATH."modules/ipb_member_sync.php";
			
		$this->modules = new ipb_member_sync();
	}
        
        //--------------------------------------
        // Check the input
        //--------------------------------------
        
        $this->md5_check = $std->return_md5_check();
        
        if ( $ibforums->input['t'] )
        {
        	$ibforums->input['t'] = intval($ibforums->input['t']);

        	if ( !$ibforums->input['t'] )
        	{
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
        	}
        }
        
        if ( $ibforums->input['p'] )
        {
        	$ibforums->input['p'] = intval($ibforums->input['p']);

        	if ( !$ibforums->input['p'] )
        	{
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
        	}
        }
        
        $ibforums->input['f'] = intval($ibforums->input['f']);

        if ( !$ibforums->input['f'] )
        {
        	$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
        }
        
        $ibforums->input['st'] = $ibforums->input['st'] ?intval($ibforums->input['st']) : 0;
        
        // Did the user press the "preview" button?
        
        $this->obj['preview_post'] = $ibforums->input['preview'];
        
        //--------------------------------------
        // Get the forum info based on the forum ID,
	// get the category name, ID, and get the topic details
        //--------------------------------------
        
        $DB->query("SELECT
			f.*,
			c.id as cat_id,
			c.name as cat_name
		    FROM
			ibf_forums f,
			ibf_categories c 
		    WHERE
			f.id='".$ibforums->input['f']."'
			AND c.id=f.category");
        
        $this->forum = $DB->fetch_row();
        
        if ( $std->check_perms($this->forum['read_perms']) != TRUE )
        {
		$std->Error( array( LEVEL => 1, MSG => 'no_view_topic') );
        }
        
        // Can we upload stuff?
        
        if ( $std->check_perms($this->forum['upload_perms']) == TRUE )
        {
        	$this->can_upload = 1;
        }
        
        // Is this forum switched off?
        
        if ( !$this->forum['status'] )
        {
        	$std->Error( array( LEVEL => 1, MSG => 'forum_read_only') );
        }
        
        //--------------------------------------
        // Is this a password protected forum?
        //--------------------------------------
        
        $pass = 0;
		
	if ($this->forum['password'] != "")
	{
		if ( !$c_pass = $std->my_getcookie('iBForum'.$this->forum['id']) )
		{
			$pass = 0;
		}
	
		if ( $c_pass == $this->forum['password'] )
		{
			$pass = 1;
		} else
		{
		    $pass = 0;
		}

	} else $pass = 1;
	
	if ( !$pass ) $std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_view_topic') );
	
        //--------------------------------------
        // Error out if we can not find the forum
        //--------------------------------------
        
        if ( !$this->forum['id'] ) $std->Error( array( 'LEVEL' => 1,
							'MSG' => 'missing_files') );
        
        $this->base_url = $ibforums->base_url;
        
	//--------------------------------------

        if ( $this->forum['parent_id'] > 0 )
        {
        	$DB->query("SELECT
				f.id as forum_id,
				f.name as forum_name,
				f.parent_id,
				f.read_perms,
				c.id,
				c.name 
			    FROM
				ibf_forums_order fo,
				ibf_forums f,
				ibf_categories c 
			    WHERE
				fo.id='".$this->forum['id']."'
				AND f.id=fo.pid
				AND c.id=f.category");

		while ( $row = $DB->fetch_row() )
		{
			if ( $row['forum_id'] == $this->forum['parent_id'] )
			{       
		        	$this->forum['cat_id']   = $row['id'];
		        	$this->forum['cat_name'] = $row['name'];
			}

			$this->nav_extra[] = "<a href='".$this->base_url."showforum={$row['forum_id']}'>{$row['forum_name']}</a>";
		}

		$this->nav_extra = array_reverse($this->nav_extra);
        }

	// add category and current forum
	array_unshift($this->nav_extra, "<a href='".$this->base_url."act=SC&amp;c={$this->forum['cat_id']}'>{$this->forum['cat_name']}</a>");

	array_push($this->nav_extra, "<a href='".$this->base_url."showforum={$this->forum['id']}'>{$this->forum['name']}</a>");

        //--------------------------------------
        // Is this forum moderated?
        //--------------------------------------
        
        $this->obj['moderate'] = intval($this->forum['preview_posts']);
        
        // Can we bypass it?
        
        if ( $ibforums->member['g_avoid_q'] ) $this->obj['moderate'] = 0;
        
	//--------------------------------------
        // Does this member have mod_posts enabled?
	//--------------------------------------
         
	// Song * new ban control

        if ( $ibforums->member['mod_posts'] )
	{
		if ( $ibforums->member['mod_posts'] == 1 )
		{
			$this->obj['moderate'] = 1;
		} else
		{
			$mod_arr = $std->hdl_ban_line( $ibforums->member['mod_posts'] );
			
			if ( time() >= $mod_arr['date_end'] )
			{
				$DB->query("UPDATE ibf_members
					    SET mod_posts=0
					    WHERE id='".intval($ibforums->member['id'])."'" );
				
				$this->obj['moderate'] = intval($this->forum['preview_posts']);
			} else
			{
				$this->obj['moderate'] = 1;
			}
		}
	}

		$DB->query("SELECT
				mod_posts,
				restrict_posts
			    FROM ibf_preview_user
			    WHERE mid='".$ibforums->member['id']."'
				AND (fid='".$this->forum['id']."'
				     OR fid='".$this->forum['parent_id']."')");
        
                $row = $DB->fetch_row();
        
                if ( $row['mod_posts'] )
		{
			if ( $row['mod_posts'] == 1 ) $this->obj['moderate'] = 1; else
			{
				$mod_arr = $std->hdl_ban_line( $row['mod_posts'] );
				
				if ( time() >= $mod_arr['date_end'] )
				{
					$DB->query("UPDATE ibf_preview_user
						    SET mod_posts=NULL
						    WHERE
							mid='".$ibforums->member['id']."'
							AND (fid='".$this->forum['id']."'
							OR fid='".$this->forum['parent_id']."')");
        
					 // delete row of user if there is nothing remaining for him
        
					$DB->query("DELETE
						    FROM ibf_preview_user
						    WHERE 
						     IsNULL(mod_posts) AND
						     IsNULL(restrict_posts) AND
						     IsNULL(temp_ban)");
        
					 // may be there is preview of posts on forum ?
					
					$this->obj['moderate'] = intval($this->forum['preview_posts']);
        
				 // not still yest
				} else $this->obj['moderate'] = 1;
			}
		}

	// /Song * new ban control
	

	// Song * ip blocked
	// check ip
	if ( !$this->obj['moderate'] ) 
	{
		$this->obj['moderate'] = $std->ip_control($this->forum, $ibforums->input['IP_ADDRESS']);
	}



	// Song * Additional flood check

       	if ( $ibforums->input['CODE'] != "08"
	 and $ibforums->input['CODE'] != "09"
	 and $ibforums->input['CODE'] != "14"
	 and $ibforums->input['CODE'] != "15" )

	 if ( $ibforums->vars['flood_control'] > 0 )

          if ( $ibforums->member['id'] ) 
	   {
        	// Flood check..
		if ( $ibforums->member['g_avoid_flood'] != 1 )
		{
// Der_Meister			if ( time() - $ibforums->member['last_post'] < $ibforums->member['g_post_flood'] )
// Jureth			$remain=$ibforums->member['last_post'] + $ibforums->vars['flood_control'] - time(); // Der_Meister
			$remain=$ibforums->member['last_post'] + $ibforums->member['g_post_flood'] - time(); // Jureth
			if ( $remain > 0 ) // Der_Meister
			{
				$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'flood_control',
						    'EXTRA' => $ibforums->member['g_post_flood'],
						    'EXTRA2' => $remain )  // vot
						  );
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
				$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'flood_control',
						    'EXTRA' => $ibforums->vars['flood_control'] ) );
			}
		}
	   }



        //--------------------------------------
        // Are we allowed to post at all?
        //--------------------------------------

        if ( $ibforums->member['id'] )
        {
        	if ( $ibforums->member['restrict_post'] )
        	{
        		if ( $ibforums->member['restrict_post'] == 1 )
        		{
        			$std->Error( array( LEVEL => 1,
						    MSG => 'posting_off') );
        		}
        		
        		$post_arr = $std->hdl_ban_line( $ibforums->member['restrict_post'] );
        		
        		if ( time() >= $post_arr['date_end'] )
        		{
        			// Update this member's profile
        			
        			$DB->query("UPDATE ibf_members
					    SET restrict_post=0
					    WHERE id='".intval($ibforums->member['id'])."'");
        		} else
        		{
        			$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'posting_off_susp',
						    'EXTRA' => $std->get_date($post_arr['date_end'], 'LONG') ) );
        		}
        		
        	}

        	if ( $row['restrict_posts'] )
        	{
        		if ( $row['restrict_posts'] == 1 ) $std->Error( array( 
							'LEVEL' => 1, 
							'MSG' => 'posting_off') ); else
			{
				$post_arr = $std->hdl_ban_line( $row['restrict_posts'] );
                 		
				if ( time() >= $post_arr['date_end'] )
				{
					$DB->query(
					"UPDATE ibf_preview_user
					 SET restrict_posts=NULL
					 WHERE
						mid='".$ibforums->member['id']."'
						AND (fid='".$this->forum['id']."'
						OR fid='".$this->forum['parent_id']."')");

					 // delete row of user if there is nothing remaining for him
					$DB->query("DELETE FROM ibf_preview_user
						    WHERE 
							IsNULL(mod_posts) AND
							IsNULL(restrict_posts) AND
							IsNULL(temp_ban)");

				} else $std->Error( array( 'LEVEL' => 1,
							   'MSG' => 'posting_off_susp',
							   'EXTRA' => $std->get_date($post_arr['date_end'], 'LONG') ) );
			}
		}
        	
        } elseif ( $ibforums->is_bot == 1 ) $std->Error( array(
						LEVEL => 1,
						MSG => 'posting_off') );
        
        if ( $ibforums->member['id'] != 0 and $ibforums->member['g_is_supmod'] == 0 )
        {
        	$DB->query("SELECT *
			    FROM ibf_moderators
			    WHERE
				forum_id='".$this->forum['id']."' AND 
				(member_id='".$ibforums->member['id']."'
				OR (is_group=1
				AND group_id='".$ibforums->member['mgroup']."'))");

        	$this->moderator = $DB->fetch_row();
        }
        





        //--------------------------------------
        // Convert the code ID's into something
        // use mere mortals can understand....
        //--------------------------------------
        



        $this->obj['action_codes'] = array (
			'00'  => array( '0'  , 'new_post'     ),
   			'01'  => array( '1'  , 'new_post'     ),
  			'02'  => array( '0'  , 'reply_post'   ),
			'03'  => array( '1'  , 'reply_post'   ),
			'06'  => array( '0'  , 'q_reply_post' ),
			'07'  => array( '1'  , 'q_reply_post' ),
			'08'  => array( '0'  , 'edit_post'    ),
			'09'  => array( '1'  , 'edit_post'    ),
			'10'  => array( '0'  , 'poll'         ),
			'11'  => array( '1'  , 'poll'         ),
			'14'  => array( '0'  , 'poll_after'   ),
			'15'  => array( '1'  , 'poll_after'   ),
			'16'  => array( '0'  , 'edit_post_history'),
			'17'  => array( '1'  , 'edit_post_history'),
			   );
							   
        // Make sure our input CODE element is legal.

	$this->act = $this->obj['action_codes'][ $ibforums->input['CODE'] ];
        
        if ( !isset($this->act) ) {
        	$std->Error( array( 'LEVEL' => 1,
						      'MSG' => 'missing_files') );
        }
        
        // Require and run our associated library file for this action.
        // this imports an extended class for this Post class.
        
        require ROOT_PATH."/sources/lib/post_".$this->act[1].".php";
        
        $post_functions = new post_functions($this);
        
        // If the first CODE array bit is set to "0" - show the relevant form.
        // If it's set to "1" process the input.
        
        // We pass a reference to this classes object so we can manipulate
	// this classes data from our sub class.
        
        if ( $this->act[0] )
        {
        	// Make sure we have a valid auth key
        	
        	if ( $ibforums->input['auth_key'] != $this->md5_check )
		{
			$std->Error( array( 'LEVEL' => 1,
					    'MSG' => 'del_post') );
		}
        	
        	// Make sure we have a "Guest" Name..
        	
        	if ( !$ibforums->member['id'] )
        	{
        		$ibforums->input['UserName'] = trim($ibforums->input['UserName']);
        		$ibforums->input['UserName'] = str_replace( "<br>", "", $ibforums->input['UserName']);
        		$ibforums->input['UserName'] = $ibforums->input['UserName'] ? $ibforums->input['UserName'] : 'Guest';
    			$ibforums->input['UserName'] = $ibforums->vars['guest_name_pre'].$ibforums->input['UserName'].$ibforums->vars['guest_name_suf'];
        	}
        	
        	//-------------------------------------------------------------------------
        	// Stop the user hitting the submit button in the hope
		// that multiple topics or replies will be added.
        	// Or if the user accidently hits the button
        	// twice.
        	//-------------------------------------------------------------------------
        	
        	if ( $this->obj['preview_post'] == "" )
        	{
			if ( preg_match( "/Post,.*,(01|03|07|11)$/", $ibforums->location ) )
			{
				if ( time() - $ibforums->lastclick < 2 )
				{
					if ( $ibforums->input['CODE'] == '01' or $ibforums->input['CODE'] == '11' )
					{
						// Redirect to the newest topic in the forum
						
						$DB->query("SELECT tid
							    FROM ibf_topics
							    WHERE
								forum_id='".$this->forum['id']."'
								AND approved=1 
							    ORDER BY last_post DESC
							    LIMIT 0,1");
								  
						$topic = $DB->fetch_row();
				
						$std->boink_it($ibforums->base_url."act=ST&f=".$this->forum['id']."&t=".$topic['tid']);

						exit();
					} else {
						// It's a reply, so simply show the topic...
						
						$std->boink_it($ibforums->base_url."act=ST&amp;f=".$this->forum['id']."&amp;t=".$ibforums->input['t']."&amp;view=getlastpost");
						exit();
					}
				}
			}
	
        	}
        	
        	//----------------------------------
       
        	$post_functions->process($this);

	} else {
		$post_functions->show_form($this);
	}

	}
	
	
/*****************************************************/
// Remove moderator's tags from mail for NOT a moderator
// by vot & FullArcticFox 
function parse_post_mail($post='', $poster=0, $mgroup=0)
{
  // $post = post body
  // $poster = the post author ID
  // $mgroup = email recipient group

  global $ibforums, $in;

  if ( !$post ) return "";
  if ( !in_array($mgroup, $ibforums->vars['mm_groups']) and !$in['MOD_FLAG'])
  {
    $post = preg_replace( "#\[mm\](.+?)\[/mm\]#is", "", $post);
  }
  if($ibforums->member['id'] != $poster) $post = preg_replace( "#\[gm\](.+?)\[/gm\]#is", "", $post);

  return trim($post);
}	


	/*****************************************************/
	// topic tracker
	// ------------------
	// Checks and sends out the emails as needed.
	/*****************************************************/
	
	function topic_tracker($tid="", $post="", $poster="", $last_post="" ) {
	global $ibforums, $DB, $std;
		
		if ( !$tid ) return TRUE;

		require_once ROOT_PATH."/sources/lib/emailer.php";
		
		$this->email = new emailer();
		
		// cut moderators tag if any
		$post = $std->do_post($post);

		// Get the email addy's, topic ids and email_full stuff - oh yeah.
		// We only return rows that have a member last_activity of greater than the post itself
		
		$DB->query("SELECT
				tr.trid,
				tr.topic_id,
				m.name,
				m.email,
				m.id,
				m.mgroup,
				m.email_full,
				m.language,
				m.last_activity,
				t.title,
				t.forum_id 
			    FROM
				ibf_tracker tr,
				ibf_topics t,
				ibf_members m 
			    WHERE
				tr.topic_id='$tid' 
			    AND m.disable_mail=0 
			    AND tr.member_id=m.id 
			    AND m.id <> '{$ibforums->member['id']}' 
			    AND t.tid=tr.topic_id 
			   ");
//vot			    AND m.last_activity > '$last_post'
		
		if ( $DB->get_num_rows() ) {
			$trids = array();

			$post_original = $post;

			while ( $r = $DB->fetch_row() ) {
				$r['language'] = $r['language'] ? $r['language'] : 'en';

				// vot & FullArcticFox
				$post = $this->parse_post_mail($post_original, $poster, $r['mgroup']);
			   if($post) {
				if ($r['email_full'] == 1) {
					$this->email->get_template("subs_with_post", $r['language']);

					$this->email->build_message( array(
						'TOPIC_ID'=> $r['topic_id'],
						'FORUM_ID'=> $r['forum_id'],
						'TITLE'   => $r['title'],
						'NAME'    => $r['name'],
						'POSTER'  => $poster,
						'POST'    => $post,
						  )
						);

					$this->email->build_subject(array(
						'TEMPLATE'=> "tt_subject",
						'TITLE'   => $r['title'],
						'POSTER'  => $poster
						  )
						);

					$this->email->to = $r['email'];
					$this->email->send_mail();

				} else {

					$this->email->get_template("subs_no_post", $r['language']);

					$this->email->build_message( array(
						'TOPIC_ID' => $r['topic_id'],
						'FORUM_ID' => $r['forum_id'],
						'TITLE'    => $r['title'],
						'NAME'     => $r['name'],
						'POSTER'   => $poster,
							)
						);

					$this->email->build_subject(array(
						'TEMPLATE' => "tt_subject",
						'TITLE'    => $r['title'],
						'POSTER'   => $poster
							)
						);

					$this->email->to = $r['email'];
					
					$this->email->send_mail();
					
				}
				
				$trids[] = $r['trid'];
			      }
			}
		}
		
		//return TRUE;
	}
	
	
	
	/*****************************************************/
	// Forum tracker
	// ------------------
	// Checks and sends out the new topic notification if
	// needed
	/*****************************************************/
	
	function forum_tracker($fid="", $this_tid="", $title="", $forum_name="")
	{
		global $ibforums, $DB, $std;
		
		if ( !$this_tid or !$fid ) return TRUE;

		require_once ROOT_PATH."/sources/lib/emailer.php";
		
		$this->email = new emailer();
		
		//-------------------------
		
		// Work out the time stamp needed to "guess" if the user
		// is still active on the board
		// We will base this guess on a period of non activity of
		// time_now - 30 minutes.
		
		$time_limit = time() - (30*60);
		
		// Get the email addy's, topic ids and email_full stuff -
		// oh yeah.
		// We only return rows that have a member last_activity
		// of greater than the post itself
		
		$DB->query("SELECT
				tr.frid,
				m.name,
				m.email,
				m.id,
				m.language,
				m.last_activity,
				m.org_perm_id,
				g.g_perm_id 
			    FROM
				ibf_forum_tracker tr,
				ibf_members m,
				ibf_groups g 
			    WHERE tr.forum_id='$fid' 
			    AND m.disable_mail=0 
			    AND tr.member_id=m.id 
			    AND m.mgroup=g.g_id 
			    AND m.id <> '{$ibforums->member['id']}' 
			    AND m.last_activity < '$time_limit'");
	
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
				$perm_id = ( $r['org_perm_id'] ) ? $r['org_perm_id'] : $r['g_perm_id'];
				
				if ( $this->forum['read_perms'] != '*' )
				{
					if ( !preg_match("/(^|,)".str_replace( ",", '|', $perm_id )."(,|$)/", $this->forum['read_perms'] ) )
	        			{
	        				continue;
	       				}
				}
        
				$r['language'] = $r['language'] ? $r['language'] : 'en';
				
				$this->email->get_template("subs_new_topic", $r['language']);
		
				$this->email->build_message( array(
						'TOPIC_ID' => $this_tid,
						'FORUM_ID' => $fid,
						'TITLE'    => $title,
						'NAME'     => $r['name'],
						'POSTER'   => $ibforums->member['name'],
						'FORUM'    => $forum_name,
						  )
						);
											
				$this->email->subject = $ibforums->lang['ft_subject'];
				$this->email->to      = $r['email'];
				
				$this->email->send_mail();
			}
		}

		return TRUE;
	}
	
	/*****************************************************/
	// compile post
	// ------------------
	// Compiles all the incoming information into an array
	// which is returned to the accessor
	/*****************************************************/
	
	function compile_post()
	{
		global $ibforums, $std;
		
		$ibforums->vars['max_post_length'] = $ibforums->vars['max_post_length'] ? $ibforums->vars['max_post_length'] : 2140000;
		
                if (($this->moderator['mid'] != "" &&
		     $ibforums->member['id'] != 0) ||
		     $ibforums->member['g_is_supmod'] == 1)
                {
                      $modflag = TRUE;
                } else 
		{
                      $modflag = FALSE;
                }

		//----------------------------------------------------------------
		// Sort out some of the form data, check for posting length, etc.
		// THIS MUST BE CALLED BEFORE CHECKING ATTACHMENTS
		//----------------------------------------------------------------
		
		$ibforums->input['enablesig']   = $ibforums->input['enablesig']   == 'yes' ? 1 : 0;
		$ibforums->input['enableemo']   = $ibforums->input['enableemo']   == 'yes' ? 1 : 0;
		$ibforums->input['enabletrack'] = $ibforums->input['enabletrack'] ==   1   ? 1 : 0;

		if ( $ibforums->input['enabletrack'] and
		     $ibforums->member['disable_mail'] )
		{
			$this->parser->error = "no_mail";
		}
		
		//------------------------------------------------------------
		// Do we have a valid post?
		//------------------------------------------------------------
		
		if (strlen( trim($_POST['Post']) ) < 1)
		{
			if ( !$_POST['preview'] )
			{
				$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'no_post') );
			}
		}
		
		if ( strlen( $_POST['Post'] ) > ($ibforums->vars['max_post_length']*1024) )
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'post_too_long') );
		}
		
		$convert = $this->parser->convert( 
				 array(
					'TEXT'     => $ibforums->input['Post'],
					'SMILIES'  => $ibforums->input['enableemo'],
					'CODE'     => $this->forum['use_ibc'],
					'HTML'     => $this->forum['use_html'],
					'MOD_FLAG' => $modflag,
					), 
				$this->forum['id'] );

		$is_new_post = in_array(
				$this->act[1], 
				array('new_post', 'reply_post', 'q_reply_post')
			);
				$post = array(
				'author_id'   => $ibforums->member['id'] ? $ibforums->member['id'] : 0,
				'use_emo'     => $ibforums->input['enableemo'],
				'ip_address'  => $ibforums->input['IP_ADDRESS'],
				'post_date'   => time(),
				//'edit_time'   => time(), negram: setting edit time only on  edit, not on creation 
				'icon_id'     => $ibforums->input['iconid'],
				'post'        => $convert,
				'author_name' => $ibforums->member['id'] ? $ibforums->member['name'] : $ibforums->input['UserName'],
				'forum_id'    => $this->forum['id'],
				'topic_id'    => 0,
				'queued'      => ( $this->obj['moderate'] == 1 || $this->obj['moderate'] == 3 ) ? 1 : 0,
				// negram: autodelete only on new post, not when edit
				'delete_after'=> $std->delayed_time($convert, $this->forum['days_off'], 0, $this->moderator, $is_new_post),
				 );
					 
	    // If we had any errors, parse them back to this class
	    // so we can track them later.
	    
	    $this->obj['post_errors'] = $this->parser->error;
					 
	    return $post;
	}
	
	/*****************************************************/
	// process upload
	// ------------------
	// checks for an entry in the upload field, and uploads
	// the file if it meets our criteria. This also inserts
	// a new row into the attachments database if successful
	/*****************************************************/
	
	function process_upload() {
	
		global $ibforums, $std, $DB;
		
		//-------------------------------------------------
		// Set up some variables to stop carpals developing
		//-------------------------------------------------
		$attachments = array();
		if (!isset($_FILES['FILE_UPLOAD']['name'])) {
			// nothing is uploaded 
			return false;
		}
		foreach($_FILES['FILE_UPLOAD']['name'] as $i => $name ) {
			$error = '';
			$a = Attachment::createFromPOST('FILE_UPLOAD', $i, $error);
			
			if ($a) {
				$attachments[$i] = $a;
			} elseif ($error) {
				$this->upload_errors[] = $error;
			}
		}
		
		$attach_data = array(
				'attach_id'   => 0,
				'attach_hits' => 0,
				'attach_type' => "",
				'attach_file' => "",
				);
							
		//-------------------------------------------------					
		// Return if we don't have a file to upload
		//-------------------------------------------------
		
		// Naughty Mozilla likes to use "none" to indicate an empty
		// upload field.
		// I love universal languages that aren't universal.
		
		if (count($attachments) == 0) {
			return false;
		}
		//-------------------------------------------------
		// Return empty handed if we don't have permission to use
		// uploads
		//-------------------------------------------------
		
		if ( ($this->can_upload != 1)
		 and ($ibforums->member['g_attach_max'] < 1) ) {
			$this->upload_errors[] = 'You can not use uploads';
		 	return false;
		 }
		
		//-------------------------------------------------
		// Load our mime types config file.
		//-------------------------------------------------
		foreach($attachments as $i => $a) {
			
			($a instanceof Attachment); // for code completer :)
			
			//-------------------------------------------------
			// Are we allowing this type of file?
			//-------------------------------------------------
			if (!$a->haveAllowedType()) {
				$a->setType($a->detectType());
			}
			if (!$a->haveAllowedType()) {
				$this->upload_errors[] = $ibforums->lang['invalid_mime_type'];
				unset($attachments[$i]);
				continue;
			}
			
			//-------------------------------------------------
			// Check the file size
			//-------------------------------------------------
			
			if ($a->size() > ($ibforums->member['g_attach_max']*1024)) {
				$this->upload_errors[] = $ibforums->lang['upload_to_big'];
				unset($attachments[$i]);
				continue;
			}
			
			$name = trim($a->filename());
			$name = preg_replace( '/[^\w\.]/', '_', $name );
			$a->setFilename($name);
			
			$real_file_name =
				sprintf("post-%d-%d-%d%s",
					$this->forum['id'],
					time(),
					$i,
					Attachment::createExtensionByType($a->type())
				); 
			$a->setRealFilename($real_file_name);
			
			if (preg_match( '/\.(cgi|pl|js|asp|php|html|htm|jsp|jar)/', $name ))
			{
				$a->setType('text/plain');
			}
		}
		
		//-------------------------------------------------
		// Copy the upload to the uploads directory
		//-------------------------------------------------
		foreach($attachments as $a) {
			
			($a instanceof Attachment); // for code completer :)
			
			if (!$a->moveToUploadDirectory($ibforums->vars['upload_dir'])) {
				$this->upload_errors[] = $ibforums->lang['upload_failed'];
				continue;
			}
			chmod( $ibforums->vars['upload_dir']."/".$a->realFilename(), 0666 );
		}
		return $attachments;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param text &$post
	 * 			текст поста, в котором производить замены.
	 * 
	 * @param array $attachments
	 * 			массив аттачей, будут сохранены в Ѕƒ, этой функцией
	 * 
	 * @param int $save_id
	 * 			$save_id id элемента где аттачи должны хранитьс€
	 * 
	 * @param string $save_to
	 * 			тип элемента, где будет хранитьс€: topic_draft, post
	 */
	function replace_attachments_tags(&$post, array $attachments, $save_id, $save_to = 'post') {
		
		foreach($attachments as $i => $attach) {
			
			($attach instanceof Attachment);
		
			$attach->setPostId( $save_id );
		
			$array = $attach->saveToDB($save_to);
		
			if (strpos($post, "[attach=#{$i}]") !== false) {
				$post = str_replace("[attach=#{$i}]", "[attach=".$attach->attachId()."]", $post);
			} else {
				$attach_append .= "\n[attach={$attach->attachId()}][/attach]";
			}
		
		}
		$post .= $attach_append;
	}

	
	function process_edituploads(array &$attachments, $delete_from = 'post') {
		global $ibforums;
		
		$attachments = Attachment::reindexArray($attachments);
		
		$new_attachment_index = 0;
		
                !$ibforums->input['editupload'] && $ibforums->input['editupload'] = array();
                
		foreach($ibforums->input['editupload'] as $filename => $editupload) {
		
			$filename = intval($filename);
		
			if ($editupload == 'keep') {
				; // nothing to do
			} else {
				if (isset($attachments[$filename])) {
		
					$attach = &$attachments[$filename];
		
					if ($editupload == 'new' && isset($new_attachments[$new_attachment_index])) {
							
						@unlink($ibforums->vars['upload_dir']."/".$attach->realFilename());
							
						$new_attach = $new_attachments[$new_attachment_index];
						$new_attach->setPostId($this->orig_post['pid']);
						$attach->acceptAttach($new_attach);
						$attach->saveToDB();
							
						$attach_append .= "\n[attach={$attach->attachId()}][/attach]";
							
						unset($new_attachments[$new_attachment_index]);
							
						$new_attachment_index++;
							
					} elseif ($editupload == 'delete') {
						
						@unlink($ibforums->vars['upload_dir']."/".$attach->realFilename());
							
						$attach->delteFromDB( $delete_from );
						; // file is alredy deleted
						unset($attachments[$filename]);
							
					}
				}
			}
		}
		
	}

	/**
	 * пишет в лог информацию о типе файла, который не удалось
	 * схоранить, ввиду недопустимого типа
	 * 
	 */
	function upload_log_typefails($real_file_name)
	{
		global $ibforums, $std;
		
		if (!$ibforums->vars['bad_upload_log_path']) {
			return;
		} else {
			@mkdir($ibforums->vars['bad_upload_log_path'],0777);
		}
		
		$user_name = $ibforums->member['name'];
		$user_file_type = $_FILES['FILE_UPLOAD']['type'];
		$user_file_name = $_FILES['FILE_UPLOAD']['name'];
		$detected_file_type = detect_mime_type($_FILES['FILE_UPLOAD']['tmp_name']);
		
		$line = sprintf(
			"date:'%s' user:'%s' filename:'%s' userfilename:'%s' usertype:'%s' detected:'%s'\n",
			date('r'),
			$user_name,
			$real_file_name,
			$user_file_name,
			$user_file_type,
			$detected_file_type
		);
		
		if ($ibforums->vars['bad_upload_store_files']) {
			
			@move_uploaded_file( $_FILES['FILE_UPLOAD']['tmp_name'], $ibforums->vars['bad_upload_log_path']."/".$real_file_name);
			chmod( $ibforums->vars['bad_upload_log_path']."/".$real_file_name, 0666 );
			
		}
		try{
			$logfile = new SplFileObject("{$ibforums->vars['bad_upload_log_path']}/log", 'a');
			$block = 1;
			$i = 0;
			// если требуетс€ блокировка, пробуем 5 раза с интервалом 50мсек
			// если не получаетс€, забиваем это дело
			while (!$logfile->flock(LOCK_EX | LOCK_NB, $block)){
				if ($block) {
					if (++$i <= 5) {
						usleep(50000);
					} else {
						throw new Exception('fails to open log');
					}
				}
			}
			$logfile->fwrite($line);
			$logfile->flock(LOCK_UN);
			unset($logfile);
		} catch(Exception $e) {
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'cant_open_bad_upload_log') );
		}
		
	}
	
	
	/*****************************************************/
	// check_upload_ability
	// ------------------
	// checks to make sure the requesting browser can accept
	// file uploads, also checks if the member group can
	// accept uploads and returns accordingly.
	/*****************************************************/
	
	function check_upload_ability() {
		global $ibforums;
		
		if ( ($this->can_upload == 1) and $ibforums->member['g_attach_max'] > 0)
		{
			$this->obj['can_upload']   = 1;
			$this->obj['form_extra']   = " enctype='multipart/form-data'";
			$this->obj['hidden_field'] = "<input type='hidden' name='MAX_FILE_SIZE' value='".($ibforums->member['g_attach_max']*1024)."' />";
		}
		
	}
	
	/*****************************************************/
	// HTML: mod_options.
	// ------------------
	// Returns the HTML for the mod options drop down box
	/*****************************************************/
	
	function mod_options($is_reply=0) {
		global $ibforums, $DB;
		
		$can_close 	= 0;
		$can_pin   	= 0;
		$can_move  	= 0;
		$can_decline	= 0;
		$can_hide	= 0;
		
		$html = "<select id='forminput' name='mod_options' class='forminput'>\n<option value='nowt'>".$ibforums->lang['mod_nowt']."</option>\n";
		
		if ( $ibforums->member['g_is_supmod'] )
		{
			$can_close 	= 1;
			$can_pin   	= 1;
			$can_move  	= 1;
			$can_delete 	= 1;
			$can_decline	= 1;
			$can_hide	= 1;

		} elseif ( $ibforums->member['id'] )
		{
			if ($this->moderator['mid'] != "" )
			{
				if ( $this->moderator['close_topic'] )
				{
					$can_close = 1;
				}
				if ( $this->moderator['pin_topic'] )
				{
					$can_pin   = 1;
				}
				if ( $this->moderator['move_topic'] )
				{
					$can_move  = 1;
				}
				if ( $this->moderator['delete_post'] )
				{
					$can_delete  = 1;
				}
				if ( $this->moderator['topic_q'] )
				{
					$can_decline  = 1;
				}
				if ( $this->moderator['hide_topic'] )
				{
					$can_hide  = 1;
				}
			}

		} else return "";
		
		if ( !$can_pin and !$can_close and !$can_move and !$can_delete and !$can_decline and !$can_hide )
		{
			return "";
		}
		
		if ( $can_pin )
		{
			$html .= "<option value='pin'>".$ibforums->lang['mod_pin']."</option>";
		}
		if ( $can_close )
		{
			$html .= "<option value='close'>".$ibforums->lang['mod_close']."</option>";
		}
		
		if ( $can_close and $can_pin )
		{
			$html .= "<option value='pinclose'>".$ibforums->lang['mod_pinclose']."</option>";
		}
		
		if ( $can_move and $is_reply )
		{
			$html .= "<option value='move'>".$ibforums->lang['mod_move']."</option>";
		}
		
		if ( $can_delete )
		{
			$html .= "<option value='delete'>".$ibforums->lang['mod_delete']."</option>";
		}

		if ( $can_decline and 
		     (  $this->act[1] == 'new_post' or
			$this->act[1] == 'edit_post' ) )
		{
			$html .= "<option value='decline'>".$ibforums->lang['mod_decline']."</option>";
		}

		if ( $can_hide )
		{
			$html .= "<option value='hide'>".$ibforums->lang['mod_hide']."</option>";
		}

		return $this->html->mod_options($html);
	
	}
	
	
	/*****************************************************/
	// HTML: start form.
	// ------------------
	// Returns the HTML for the <FORM> opening tag
	/*****************************************************/
	
	function html_start_form($additional_tags=array()) {
		global $ibforums;
		
		$form = $this->html->get_javascript();
		
		$form .="<form name='REPLIER' action='{$this->base_url}' method='post' onsubmit='return ValidateForm()'".$this->obj['form_extra'].">".
			"<input type='hidden' name='st' value='".$ibforums->input['st']."'>\n".
			"<input type='hidden' name='act' value='Post' />\n".
			"<input type='hidden' name='s' value='".$ibforums->session_id."'>\n".
			"<input type='hidden' name='f' value='".$this->forum['id']."'>\n".
			"<input type='hidden' name='auth_key' value='".$this->md5_check."'>\n".
			$this->obj['hidden_field'];
				
		// Any other tags to add?
		
		if ( isset($additional_tags) ) 
		{
			foreach($additional_tags as $k => $v) 
			{
				$form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}' />";
			}
		}
		
		return $form;
    }
		
	/*****************************************************/
	// HTML: name fields.
	// ------------------
	// Returns the HTML for either text inputs or membername
	// depending if the member is a guest.
	/*****************************************************/
	
	function html_name_field() {
	global $ibforums;
		
		return $ibforums->member['id'] ? $this->html->nameField_reg() : $this->html->nameField_unreg( $ibforums->input[UserName] );
	}
	
	/*****************************************************/
	// HTML: Post body.
	// ------------------
	// Returns the HTML for post area, code buttons and
	// post icons
	/*****************************************************/
	
	function html_post_body($raw_post = "", $topic = array() ) {
	global $ibforums, $std, $skin_universal;
		                        
		$ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

		// Song * mod buttons, 08.11.2004

		$mod_buttons = "";

		if ( $ibforums->member['is_mod'] )
		{
			if ( $this->moderator['mid'] or $ibforums->member['g_is_supmod'] )
			{
				$mod_buttons .= $skin_universal->mod_buttons();
			}

			if ( $ibforums->member['g_is_supmod'] )
			{
				$mod_buttons .= $skin_universal->global_mod_buttons();
			}

			$mod_buttons .= $skin_universal->common_mod_buttons();

			if ( $mod_buttons ) $mod_buttons = $skin_universal->mod_buttons_label().$mod_buttons;
		}



		// Song * decided topics, 20.04.05

		$topic_decided = "";

		if ( $topic['tid'] and !$topic['decided'] and $this->forum['decided_button'] and $ibforums->member['id'] )
		{
			$topic_decided = ( $ibforums->member['g_use_decided'] and $ibforums->member['id'] == $topic['starter_id'] ) 
					? $skin_universal->topic_decided() 
					: "";
		}



		return $this->html->postbox_buttons($raw_post, $std->code_tag_button( $std->get_highlight_id($this->forum['id']) ), $mod_buttons, $topic_decided);
	}
	
	/*****************************************************/
	// HTML: Post Icons
	// ------------------
	// Returns the HTML for post area, code buttons and
	// post icons
	/*****************************************************/
	
	function html_post_icons($post_icon = "") {
		global $ibforums;
		
		if ($ibforums->input['iconid'])
		{
			$post_icon = $ibforums->input['iconid'];
		}
		
		$ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;
		
		$html = $this->html->PostIcons();
		
		if ( $post_icon )
		{
			$html = preg_replace( "/name=[\"']iconid[\"']\s*value=[\"']$post_icon\s?[\"']/", "name='iconid' value='$post_icon' checked", $html );

			$html = preg_replace( "/name=[\"']iconid[\"']\s*value=[\"']0[\"']\s*checked=['\"]checked['\"]/i"  , "name='iconid' value='0'", $html );
		}

		return $html;
	}
	
	/*****************************************************/
	// HTML: checkboxes
	// ------------------
	// Returns the HTML for sig/emo/track boxes
	/*****************************************************/
	
	function html_checkboxes($type="", $tid="", $use_emo = "", $offtop = "") {
	global $ibforums, $DB;
		
		$default_checked = array(
			'emo'    => 'checked="checked"',
			'tra'    => $ibforums->member['auto_track'] ? 'checked="checked"' : '',
			'edit'    => 'checked="checked"',
			'merge'    => 'checked="checked"',
		        );
						        
		// Make sure we're not previewing them and they've been unchecked!

		// Song * emoticon checkbox correct

		if ( isset($ibforums->input['enableemo']) ) 
		{
			if ( !$ibforums->input['enableemo'] ) $default_checked['emo'] = "";

		} elseif ( $use_emo != "" and !$use_emo ) $default_checked['emo'] = "";

		$this->output = str_replace( '<!--IBF.EMO-->', $this->html->get_box_enableemo( $default_checked['emo'] ), $this->output );



		if ( $ibforums->member['id'] )
		{
			if($type == "reply")
			{
				// Sunny: галочка "склеивание сообщений"
				if(isset($ibforums->input['add_merge_edit']) and !$ibforums->input['add_merge_edit']) $default_checked['merge'] = "";
				$this->output = str_replace('<!--IBF.MERGE_POST_LABEL-->', $this->html->add_merge_edit_box($default_checked['merge']), $this->output);
			}
			elseif($type == "edit" && $ibforums->member['g_edit_posts'] == 1)
			{
				// Sunny: галочка "надпись отредактировано"
				if(isset($ibforums->input['add_edit']) and !$ibforums->input['add_edit']) $default_checked['edit'] = "";
				$this->output = str_replace('<!--IBF.MOD_ADD_EDIT_LABEL-->', $this->html->add_edit_box($default_checked['edit']), $this->output);
			}

			if ( $type != "edit" )
			{
				// track topic
				if ( isset($ibforums->input['enabletrack']) AND !$ibforums->input['enabletrack'] )
				{
					$default_checked['tra'] = "";
                
				} elseif ( isset( $ibforums->input['enabletrack']) AND $ibforums->input['enabletrack'] )
				{
					$default_checked['tra'] = 'checked="checked"';
				}
        
				if ( $tid )
				{
					$DB->query("SELECT trid
						    FROM ibf_tracker
						    WHERE
							topic_id='".$tid."'
							AND member_id='".$ibforums->member['id']."'");
				}

				if ( $tid and $DB->get_num_rows() )
				{
					$DB->free_result();

					$this->output = str_replace( '<!--IBF.TRACK-->',$this->html->get_box_alreadytrack(), $this->output );
				} else 
				{
					$this->output = str_replace( '<!--IBF.TRACK-->', $this->html->get_box_enabletrack( $default_checked['tra'] ), $this->output );
				}
			}
        

			// Song * favorite checkbox in the new topic, 18.03.05

			if ( !$type )
			{
				// add to favorites
				if ( isset($ibforums->input['fav']) AND !$ibforums->input['fav'] )
				{
					$default_checked['fav'] = "";

				} elseif ( isset( $ibforums->input['fav']) AND $ibforums->input['fav'] )
				{
					$default_checked['fav'] = 'checked="checked"';
				}

				$this->output = str_replace( '<!--IBF.FAV-->', $this->html->get_box_enablefav( $default_checked['fav'] ), $this->output );



			// Song * offtopic checkbox, 19.04.05
			// Song * don't bump topic, 03.05.05

			} elseif ( $type == "reply" or $type == "edit" )
			{
				// offtop checkbox
				if ( $this->forum['days_off'] and 
				     ( $this->moderator['delete_post'] or 
				       $ibforums->member['g_is_supmod'] or 
				       $ibforums->member['g_delay_delete_posts'] 
				     ) 
				   ) 
				{
					if ( $offtop ) $default_checked['offtop'] = 'checked="checked"';

					if ( isset($ibforums->input['offtop']) AND !$ibforums->input['offtop'] )
					{
						$default_checked['offtop'] = "";
	
					} elseif ( isset( $ibforums->input['offtop']) AND $ibforums->input['offtop'] )
					{
						$default_checked['offtop'] = 'checked="checked"';
					}

					$this->output = str_replace( '<!--IBF.OFFTOP-->', $this->html->get_box_enable_offtop( $default_checked['offtop'] ), $this->output );
				}

				// don't bump checkbox
				if ( $type == "edit" and ( $this->moderator['edit_post'] or $ibforums->member['g_is_supmod'] ) )
				{
					if ( isset($ibforums->input['bump']) AND !$ibforums->input['bump'] )
					{
						$default_checked['bump'] = "";
	
					} elseif ( isset( $ibforums->input['bump']) AND $ibforums->input['bump'] )
					{
						$default_checked['bump'] = 'checked="checked"';
					}

					$this->output = str_replace( '<!--IBF.BUMP-->', $this->html->get_box_bump( $default_checked['bump'] ), $this->output );
				}

			}

		}
	}
	
	/*****************************************************/
	// HTML: add smilie box.
	// ------------------
	// Inserts the clickable smilies box
	/*****************************************************/
	
	function html_add_smilie_box() {
		global $ibforums, $DB;
		
		$show_table = 0;
		$count      = 0;
		$smilies    = "<tr align='center'>\n";
		
		// Get the smilies from the DB
		
		// Song * smile skin

		if ( !$ibforums->member['id'] ) $id = 1; else $id = $ibforums->member['sskin_id'];
		if ( !$id ) $id = 1;

		$DB->query("SELECT
				typed,
				image
			    FROM ibf_emoticons
			    WHERE
				clickable='1'
				AND skid='".$id."'");



		while ($elmo = $DB->fetch_row() ) {
		
			$show_table++;
			$count++;
			
			// Make single quotes as URL's with html entites in them
			// are parsed by the browser, so ' causes JS error :o
			
			if (strstr( $elmo['typed'], "&#39;" ) )
			{
				$in_delim  = '"';
				$out_delim = "'";
			}
			else
			{
				$in_delim  = "'";
				$out_delim = '"';
			}
			
			if ( !$ibforums->member['id'] ) $sskin = 'Main'; else
			{
				$sskin = $ibforums->member['sskin_name'];
				if ( !$ibforums->member['view_img'] or $ibforums->member['sskin_id'] == 0 ) $sskin = 0;
			}

			if ( $sskin ) 
			{
				$smile = "<img src='{$ibforums->vars['board_url']}/smiles/$sskin/".$elmo['image']."' alt='{$elmo['typed']}' border='0'>";

			} else $smile = $elmo['typed'];

			$smilies .= "<td><a href={$out_delim}javascript:emoticon($in_delim".$elmo['typed']."$in_delim){$out_delim}>{$smile}</a>&nbsp;</td>\n";

			if ( $count == $ibforums->vars['emo_per_row'] ) 
			{
				$smilies .= "</tr>\n\n<tr align='center'>";
				$count = 0;
			}
		}
		
		if ($count != $ibforums->vars['emo_per_row']) {
			for ($i = $count ; $i < $ibforums->vars['emo_per_row'] ; ++$i) {
				$smilies .= "<td>&nbsp;</td>\n";
			}
			$smilies .= "</tr>";
		}
		
		$table = $this->html->smilie_table();
		
		if ($show_table != 0) {
			$table = preg_replace( "/<!--THE SMILIES-->/", $smilies, $table );
			$this->output = preg_replace( "/<!--SMILIE TABLE-->/", $table, $this->output );
		}
                //----------------------------------
                // Return smilie table for D-Site
                // --
                // Date: 24/08/2005 (c) Anton
                //----------------------------------

                return $table;
	
	}
		
	/*****************************************************/
	// HTML: topic summary.
	// ------------------
	// displays the last 10 replies to the topic we're
	// replying in.
	/*****************************************************/
	
	function html_topic_summary($topic_id) {
		global $ibforums, $std, $DB;
		
		if ( !$topic_id ) return;
		if ( !$ibforums->member['show_history'] ) return;
		
		$cached_members = array();
		
		$this->output .= $this->html->TopicSummary_top();
		
		//--------------------------------------------------------------
		// Get the posts
		// This section will probably change at some point
		//--------------------------------------------------------------

                $this->parser->prepareIcons();
		
                $post_query = $DB->query(
			"SELECT
				post,
				pid,
				post_date,
				author_id,
				author_name,
				use_emo
			FROM ibf_posts 
			WHERE
				topic_id=$topic_id and
				queued != 1 and
				use_sig=0
			ORDER BY pid DESC
			LIMIT 0,10");
		
		while ( $row = $DB->fetch_row($post_query) )
		{
			
			$row['author'] = $row['author_name'];
	
			$row['date']   = $std->get_date( $row['post_date'], 'LONG' );
	
			$data = array(  TEXT          => $row['post'],
                                        SMILIES       => $row['use_emo'],
                                        CODE          => 1,
                                        SIGNATURE     => 0,
                                        HTML          => 1

                                       );

			$row['post'] = $this->parser->prepare($data);

			if ( !trim($row['post']) ) continue;
	
			$row['post'] = $this->parser->post_db_parse($row['post'], $this->forum['use_html'] AND $ibforums->member['g_dohtml'] ? 1 : 0);
	
			//--------------------------------------------------------------
			// Do word wrap?
			//--------------------------------------------------------------
	
			if ( $ibforums->vars['post_wordwrap'] > 0 )
			{
				$row['post'] = $this->parser->my_wordwrap( $row['post'], $ibforums->vars['post_wordwrap']) ;
			}
			
// vot			$row['post']   = str_replace( "<br>", "<br>", $row['post'] );
			
		    $this->output .= $this->html->TopicSummary_body( $row );
		}
		
		$this->output .= $this->html->TopicSummary_bottom();
		
	}
	
	/*****************************************************/
	// Moderators log
	// ------------------
	// Simply adds the last action to the mod logs
	/*****************************************************/
	
	function moderate_log($title = 'unknown', $topic_title) {
		global $std, $ibforums, $DB;
		
		$db_string = $std->compile_db_string( array (
					'forum_id'    => $ibforums->input['f'],
					'topic_id'    => intval($ibforums->input['t']),
					'post_id'     => intval($ibforums->input['p']),
					'member_id'   => $ibforums->member['id'],
					'member_name' => $ibforums->member['name'],
					'ip_address'  => $ibforums->input['IP_ADDRESS'],
					'http_referer'=> $_SERVER['HTTP_REFERER'],
					'ctime'       => time(),
					'topic_title' => $topic_title,
					'action'      => $title,
					'query_string'=> $_SERVER['QUERY_STRING'],
						)
					);
		
		$DB->query("INSERT INTO ibf_moderator_logs
				(" .$db_string['FIELD_NAMES']. ")
			    VALUES (". $db_string['FIELD_VALUES'] .")");
		
	}
        
}

?>