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
|   > New Post module
|   > Module written by Matt Mecham
|   > Date started: 17th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/




class post_functions extends Post {

	var $nav = array();
	var $title = "";
	var $post  = array();
	var $topic = array();
	var $upload = array();
	var $mod_topic = array();
	
	var $m_group = "";

	function post_functions($class) {
	
		global $ibforums, $std, $DB;
		
		// Lets do some tests to make sure that we are allowed to start a new topic
		
		if ( !$ibforums->member['g_post_new_topics'])
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_starting') );
		}
		
		if ( $std->check_perms($class->forum['start_perms']) == FALSE )
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_starting') );
		}

	}
	
	function process($class) {
	
		global $ibforums, $std, $DB, $print;
		
		//-------------------------------------------------
		// Parse the post, and check for any errors.
		//-------------------------------------------------
		
		$this->post   = $class->compile_post();
		
		//-------------------------------------------------
		// check to make sure we have a valid topic title
		//-------------------------------------------------
		
		$ibforums->input['TopicTitle'] = str_replace( "<br>", "", $ibforums->input['TopicTitle'] );
		
		$ibforums->input['TopicTitle'] = trim($ibforums->input['TopicTitle']);
		
		if ( (strlen($ibforums->input['TopicTitle']) < 2) or (!$ibforums->input['TopicTitle'])  )
		{
			$class->obj['post_errors'] = 'no_topic_title';
		}
		
		//-------------------------------------------------
		// More unicode..
		//-------------------------------------------------
		
		$temp = $std->txt_stripslashes($_POST['TopicTitle']);
		
		$temp = preg_replace("/&#([0-9]+);/", "-", $temp );
		
		if ( strlen($temp) > $ibforums->vars['max_title_length'] )
		{
			$class->obj['post_errors'] = 'topic_title_long';
		}
		

		//-------------------------------------------------
		// If we don't have any errors yet, parse the upload
		//-------------------------------------------------
		
		if ($class->obj['post_errors'] == "")
		{
			$this->upload = $class->process_upload();
		}
		
		if ( $class->obj['post_errors'] or
		     $class->obj['preview_post'] ) 
		{
			// Show the form again
			$this->show_form($class);
		} else 
		{
			$this->add_new_topic($class);
		}
	}
	

	//---------------------------------
	function add_new_topic($class) {
		
		global $ibforums, $std, $DB, $print, $sess;
		
		//-------------------------------------------------
		// Fix up the topic title
		//-------------------------------------------------
		
		if ($ibforums->vars['etfilter_punct'])
		{
			$ibforums->input['TopicTitle']	= preg_replace( "/\?{1,}/"      , "?"    , $ibforums->input['TopicTitle'] );		
			$ibforums->input['TopicTitle']	= preg_replace( "/(&#33;){1,}/" , "&#33;", $ibforums->input['TopicTitle'] );
		}
		
		if ($ibforums->vars['etfilter_shout'])
		{
			$ibforums->input['TopicTitle'] = ucwords(strtolower($ibforums->input['TopicTitle']));
		}
		
		$ibforums->input['TopicTitle'] = $class->parser->bad_words( $ibforums->input['TopicTitle'] );
		$ibforums->input['TopicDesc']  = $class->parser->bad_words( $ibforums->input['TopicDesc']  );
		
		$pinned = 0;
		$state  = 'open';
		$hidden = 0;
		
		if ( ($ibforums->input['mod_options'] != "") or
		     ($ibforums->input['mod_options'] != 'nowt') )
		{
			if ($ibforums->input['mod_options'] == 'pin')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['pin_topic'] == 1)
				{
					$pinned = 1;
					
					$class->moderate_log('Pinned topic from post form', $ibforums->input['TopicTitle']);
				}

			} elseif ($ibforums->input['mod_options'] == 'close')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['close_topic'] == 1)
				{
					$state = 'closed';
					
					$class->moderate_log('Closed topic from post form', $ibforums->input['TopicTitle']);
				}
			} elseif ($ibforums->input['mod_options'] == 'pinclose')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				      ( $class->moderator['pin_topic'] == 1 AND
					$class->moderator['close_topic'] == 1 ) )
				{
					$pinned = 1;
					$state = 'closed';
					
					$class->moderate_log('Pinned & closed topic from post form', $ibforums->input['TopicTitle']);
				}
			} elseif ( $ibforums->input['mod_options'] == 'delete' )
			{
				if ( $ibforums->member['g_is_supmod'] == 1 or
				     $class->moderator['delete_post'] == 1 )
				{
					$this->post['use_sig'] = 1;
					$this->post['append_edit'] = 1;
					$this->post['has_modcomment'] = 1;
					$this->post['edit_time'] = time();
					$this->post['edit_name'] = $ibforums->member['name'];

					$class->moderate_log('Decline post within answer', $ibforums->input['TopicTitle']);
				}
			} elseif ( $ibforums->input['mod_options'] == 'decline' )
			{
				if ( $ibforums->member['g_is_supmod'] == 1 or
				     $class->moderator['topic_q'] == 1 )
				{
					$this->topic['approved'] = 0;
					$class->obj['moderate'] = 3;
					$class->moderate_log('Queued topic within answer', $ibforums->input['TopicTitle']);
				}
			} elseif ( $ibforums->input['mod_options'] == 'hide' )
			{
				if ( $ibforums->member['g_is_supmod'] == 1 or
				     $class->moderator['hide_topic'] == 1 )
				{
					$hidden = 1;
					$class->moderate_log('Hide topic within answer', $ibforums->input['TopicTitle']);
				}
			}
		}
		
		//-------------------------------------------------
		// Build the master array
		//-------------------------------------------------
		
		// Song * club tool

		if ( $ibforums->input['club_only'] and
		     $std->check_perms($ibforums->member['club_perms']) == FALSE )
		{
			$ibforums->input['club_only'] = "0";
		}



		$this->topic = array(
				  'title'            => $ibforums->input['TopicTitle'],
				  'description'      => $ibforums->input['TopicDesc'] ,
				  'state'            => $state,
				  'posts'            => 0,
				  'starter_id'       => $ibforums->member['id'],
				  'starter_name'     => $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'],
				  'start_date'       => time(),
				  'last_poster_id'   => $ibforums->member['id'],
				  'last_poster_name' => $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'],
				  'last_post'        => time(),
				  'icon_id'          => $ibforums->input['iconid'],
				  'author_mode'      => $ibforums->member['id'] ? 1 : 0,
				  'poll_state'       => 0,
				  'last_vote'        => 0,
				  'views'            => 0,
				  'forum_id'         => $class->forum['id'],
				  'approved'         => ( $class->obj['moderate'] == 1 || $class->obj['moderate'] == 2 ) ? 0 : 1,
				  'pinned'           => $pinned,
				  'club'	     => intval($ibforums->input['club_only']),
				  'hidden'	     => $hidden,
				 );

		// Song * new pinned 

		if ( $pinned ) $this->topic['pinned_date'] = time();
					


		//-------------------------------------------------
		// Insert the topic into the database to get the
		// last inserted value of the auto_increment field
		// follow suit with the post
		//-------------------------------------------------
		
		$db_string = $DB->compile_db_insert_string( $this->topic );
		
		$DB->query("INSERT INTO ibf_topics
				(" .$db_string['FIELD_NAMES']. ")
			    VALUES
				(". $db_string['FIELD_VALUES'] .")");

		$this->post['topic_id']  = $DB->get_insert_id();

		$this->topic['tid']      = $this->post['topic_id'];
		


		//----------------------------------------------------
		// vot:
		// Index the Topic Title for the Indexed Search Engine
		//----------------------------------------------------

		$std->index_reindex_title( $this->topic['tid'],
					   $class->forum['id'],
					   $ibforums->input['TopicTitle']);




		//-------------------------------------------------
		// Update the post info with the upload array info
		//-------------------------------------------------
		
		/*
		$this->post['attach_id']   = $this->upload['attach_id'];
		$this->post['attach_type'] = $this->upload['attach_type'];
		$this->post['attach_hits'] = $this->upload['attach_hits'];
		$this->post['attach_file'] = $this->upload['attach_file'];
		*/
		$this->post['attach_exists'] = is_array($this->upload) ? (bool)count($this->upload) : false;
   		$this->post['new_topic']   = 1;
		
		//-------------------------------------------------
		// Unqueue the post if we're starting a new topic
		//-------------------------------------------------
		
		if ( $class->obj['moderate'] == 3 ) $this->post['queued'] = 0;
		


		// Song * delayed post deleting, 19.04.05

		$this->post['delete_after'] = 0;



		// Compile the post

		$db_string = $DB->compile_db_insert_string( $this->post );
		
		$DB->query("INSERT INTO ibf_posts
				(" .$db_string['FIELD_NAMES']. ")
			    VALUES
				(". $db_string['FIELD_VALUES'] .")");
		
		$this->post['pid'] = $DB->get_insert_id();
		
		$attach_append = "\n";
		
		if ($this->post['attach_exists']) {
			foreach($this->upload as $i => $attach) {
				($attach instanceof Attach2);
				
				$attach->setPostId($this->post['pid']);
				
				$attach->saveToDB();
				
				unset($array['attach_id']); // because it is not exists yet, see below
				
				if (strpos($this->post['post'], "[attach=#{$i}]") !== false) {
					$this->post['post'] = str_replace("[attach=#{$i}]", "[attach=".$attach->attachId()."]", $this->post['post']);
				} else {
					$attach_append .= "\n[attach={$attach->attachId()}][/attach]";
				}
			}
			
			/*
			 * append post with [attach] tags. One tag per attachment 
			 */
			$db_string = $DB->compile_db_update_string(array('post' => $this->post['post'].$attach_append));
			$DB->query("UPDATE ibf_posts
				    SET
					$db_string
				    WHERE pid='{$this->post['pid']}'");
			
		}
		





		//----------------------------------------------------
		// vot:
		// Index the Post body for the Indexed Search Engine
		//----------------------------------------------------

		$std->index_reindex_post($this->post['pid'],
					 $this->topic['tid'],
					 $class->forum['id'],
					 $this->post['post']);
	





		// Check for premoderated mode

		if ( $class->obj['moderate'] == 1 OR
		     $class->obj['moderate'] == 2 )
		{
			$DB->query("UPDATE ibf_forums
				    SET has_mod_posts=1
				    WHERE id='".$class->forum['id']."'");


			// Song * favorite checkbox in the new topic, 18.03.05
			$this->add_to_fav();

			
			$print->redirect_screen( $ibforums->lang['moderate_topic'], "act=SF&f={$class->forum['id']}" );
		}
		

		//-------------------------------------------------
		// If we are still here, lets update the
		// board/forum stats
		//------------------------------------------------- 
		
		$class->forum['topics']++;

		// Song * club tool

		if ( !$ibforums->input['club_only'] )
		{
			$class->forum['last_title']       = $this->topic['title'];
			$class->forum['last_id']          = $this->topic['tid'];
			$class->forum['last_post']        = time();
			$class->forum['last_poster_name'] = $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'];
			$class->forum['last_poster_id']   = $ibforums->member['id'];

			// Update the database
			$DB->query("UPDATE ibf_forums
				    SET
					last_title='".$class->forum['last_title']."',
					last_id='".$class->forum['last_id']."',
					last_post='".$class->forum['last_post']."',
					last_poster_name='".$class->forum['last_poster_name'] ."',
					last_poster_id='".$class->forum['last_poster_id']."',
					topics='".$class->forum['topics']."' 
				    WHERE id='".$class->forum['id']."'");
		} else 
		{
			$DB->query("UPDATE ibf_forums
				    SET
					topics='".$class->forum['topics']."',
					last_post='".$class->forum['last_post']."' 
				    WHERE id='".$class->forum['id']."'");
		}
		


		$DB->query("UPDATE ibf_stats
			    SET TOTAL_TOPICS=TOTAL_TOPICS+1");
		
		//-------------------------------------------------
		// Are we tracking new topics we start 'auto_track'?
		//-------------------------------------------------
		
		if ( $ibforums->member['id'] AND
		     $ibforums->input['enabletrack'] == 1 )
		{
			$db_string = $DB->compile_db_insert_string( array (
					'member_id'  => $ibforums->member['id'],
					'topic_id'   => $this->topic['tid'],
					'start_date' => time(),
					  )       );
			$DB->query("INSERT INTO ibf_tracker
					({$db_string['FIELD_NAMES']})
				    VALUES
					({$db_string['FIELD_VALUES']})");
		}

		//---------------------------------------------------------------
		// Are we tracking this forum? If so generate some mailies - yay!
		//---------------------------------------------------------------
		
		$class->forum_tracker($class->forum['id'],
				      $this->topic['tid'],
				      $this->topic['title'],
				      $class->forum['name'] );
		
		//-------------------------------------------------
		// If we are a member, lets update thier last post
		// date and increment their post count.
		//-------------------------------------------------
		
		if ( $ibforums->member['id'] )
		{
			$pcount = "";
			$mgroup = "";

			if ( $class->forum['inc_postcount'] )
			{
				// Increment the users post count
				
				$pcount = "posts=posts+1, ";
				
			}
			
			// Are we checking for auto promotion?
			
			if ( $ibforums->member['g_promotion'] != '-1&-1' and
			    !$ibforums->member['disable_group'] )
			{
				list($gid, $gposts) = explode( '&', $ibforums->member['g_promotion'] );
				
				if ( $gid > 0 and $gposts > 0 )
				{
					if ( $ibforums->member['posts'] + 1 >= $gposts and !$ibforums->member['warn_level'] )
					{
						$mgroup = "mgroup='$gid',old_group='$gid',";
						
						if ( USE_MODULES == 1 )
						{
							$class->modules->register_class(&$class);
							$class->modules->on_group_change($ibforums->member['id'], $gid);
						}
					}
				}
			}
			
			$ibforums->member['last_post'] = time();
			

			// IbStore: 
			// Increase the member punkts for this post

			require(ROOT_PATH."/sources/store/store_module.php");
			$module = new module();
			$module->inc_postcount = $class->forum['inc_postcount'];

			$DB->query("UPDATE ibf_members
				    SET
					$pcount
					$mgroup ".
					$module->post_points($ibforums->vars['pointsper_topic'])."
					last_post='{$ibforums->member['last_post']}' 
				    WHERE id='{$ibforums->member['id']}'");



			// Song * favorite checkbox in the new topic, 18.03.05

			$this->add_to_fav();



		// Song * additional flood control

            	} else $DB->query("UPDATE ibf_sessions
				   SET last_post='".time()."'
				   WHERE id='".$sess->session_id."'");



		//-------------------------------------------------
		// Redirect them back to the topic
		//-------------------------------------------------

		$std->boink_it($class->base_url."act=ST&amp;f={$class->forum['id']}&amp;t={$this->topic['tid']}");
		
	}


	//-----------------------------------------------------
	// Song * favorite checkbox in the new topic, 18.03.05

	function add_to_fav() {
	global $ibforums;

	if ( $ibforums->member['id'] and $ibforums->input['fav'] )
	{
		require ROOT_PATH."sources/fav.php";

		$fav = new fav();

		$fav->add_topic($this->topic['tid']);
	}

	}



	//----------------------------
	function show_form(&$class) {
	
		global $ibforums, $std, $DB, $print;
		
		// Sort out the "raw" textarea input and make it safe incase
		// we have a <textarea> tag in the raw post var.
		
		$raw_post    = isset($_POST['Post'])       ? $std->txt_htmlspecialchars($_POST['Post'])        : "";
		$topic_title = isset($_POST['TopicTitle']) ? $ibforums->input['TopicTitle'] : "";
		$topic_desc  = isset($_POST['TopicDesc'])  ? $ibforums->input['TopicDesc']  : "";
		
		if ( isset($raw_post) ) $raw_post = $std->txt_raw2form($raw_post);

		// Do we have any posting errors?
		
		if ( $class->obj['post_errors'] )
		{
			if ( $ibforums->member['id'] and $class->obj['post_errors'] == "no_mail" )
			{
				$ibforums->lang[ $class->obj['post_errors'] ] = sprintf($ibforums->lang[ $class->obj['post_errors'] ], $ibforums->member['disable_mail_reason']);
			}

			$class->output .= $class->html->errors( $ibforums->lang[ $class->obj['post_errors'] ] );
		}
		
		if ( $class->obj['preview_post'] )
		{
			$this->post['post'] = $class->parser->post_db_parse(
						     $class->parser->prepare( array(
						'TEXT'    => $this->post['post'],
			     			'CODE'    => $class->forum['use_ibc'],
			     			'SMILIES' => $ibforums->input['enableemo'],
			     			'HTML'    => $class->forum['use_html']
     						)      ) ,
					      $class->forum['use_html'] AND $ibforums->member['g_dohtml'] ? 1 : 0 );

			$class->output .= $class->html->preview( $this->post['post'] );
		}
		
		$class->check_upload_ability();
		
		$class->output .= $class->html_start_form( array(
					1 => array( 'CODE', '01' ) ) );
		
		//---------------------------------------
		// START TABLE
		//---------------------------------------
		
                $warning = "";

                if ( $class->obj['moderate'] == 1 or $class->obj['moderate'] == 2 or $ibforums->member['mod_posts'] ) 
		{
			$warning = $ibforums->lang['mod_posts_warning'];
		}

		$class->output .= $warning;

		$class->output .= $class->html->table_structure();
		
		//---------------------------------------
		
		$topic_title = $class->html->topictitle_fields(
						array(
						'TITLE' => $topic_title,
						'DESC' => $topic_desc
						) );
		
		$start_table = $class->html->table_top( "{$ibforums->lang['top_txt_new']} {$class->forum['name']}");
		
		$name_fields = $class->html_name_field();
		
		$post_box    = $class->html_post_body( $raw_post );
		
		$mod_options = $class->mod_options();


		// Song * club tool

		if ( $std->check_perms($ibforums->member['club_perms']) == TRUE and 
		     $ibforums->member['club_perms'] != $class->forum['read_perms'] ) 
		{
			if ( $class->obj['preview_post'] and $ibforums->input['club_only'] ) 
			{
				$checked = 'checked';
			}

			$rights_options = $class->html->rights_options($checked);
		}



		$end_form    = $class->html->EndForm( $ibforums->lang['submit_new'] );
		
		$post_icons  = $class->html_post_icons();
		
		if ( $class->obj['can_upload'] )
		{
			$upload_field = $class->html->Upload_field( $std->size_format( $ibforums->member['g_attach_max'] * 1024 ) );
		}
		
		//---------------------------------------
		
		$class->output = preg_replace( "/<!--START TABLE-->/" ,  "$start_table"  , $class->output );
		$class->output = preg_replace( "/<!--NAME FIELDS-->/" ,  "$name_fields"  , $class->output );
		$class->output = preg_replace( "/<!--POST BOX-->/"    ,  "$post_box"     , $class->output );
		$class->output = preg_replace( "/<!--POST ICONS-->/"  ,  "$post_icons"   , $class->output );
		$class->output = preg_replace( "/<!--UPLOAD FIELD-->/",  "$upload_field" , $class->output );
		$class->output = preg_replace( "/<!--RIGHTS OPTIONS-->/","$rights_options",$class->output );
		$class->output = preg_replace( "/<!--MOD OPTIONS-->/" ,  "$mod_options"  , $class->output );
		$class->output = preg_replace( "/<!--END TABLE-->/"   ,  "$end_form"     , $class->output );
		$class->output = preg_replace( "/<!--TOPIC TITLE-->/" ,  "$topic_title"  , $class->output );


		// Song * IBF forum rules		

		if ( $class->forum['show_rules'] )
		{
			if ( $class->forum['rules_title'] )
			{
                        	$class->forum['rules_title'] = trim( $class->parser->prepare( array (
                                          'TEXT'          => $class->forum['rules_title'],
                                          'SMILIES'       => 1,
                                          'CODE'          => 1,
                                          'SIGNATURE'     => 0,
                                          'HTML'          => 0,
					 )	)  );
                
                        	$class->forum['rules_text']  = trim( $class->parser->prepare( array (
                                          'TEXT'          => $class->forum['rules_text'],
                                          'SMILIES'       => 1,
                                          'CODE'          => 1,
                                          'SIGNATURE'     => 0,
                                          'HTML'          => 0,
					 )	)  );
                
				$class->forum['rules_text'] = str_replace( ";&lt;br&gt;", "<br>", $class->forum['rules_text'] );
			}
		}
		


		$class->output = str_replace("<!--FORUM RULES-->", $std->print_forum_rules($class->forum), $class->output );
		
		//---------------------------------------
		
		$class->html_add_smilie_box();
		
		//---------------------------------------
		// Add in siggy buttons and such
		//---------------------------------------
		
		$class->html_checkboxes();
		
		//---------------------------------------
		
		$this->nav = array( "<a href='{$class->base_url}act=SC&amp;c={$class->forum['cat_id']}'>{$class->forum['cat_name']}</a>",
					"<a href='{$class->base_url}act=SF&amp;f={$class->forum['id']}'>{$class->forum['name']}</a>",
						  );
		$this->title = $ibforums->lang['posting_new_topic'];
		
		$print->add_output("$class->output");

	        $print->do_output( array( 'TITLE'    => $this->title." -> ".$ibforums->vars['board_name'],
        				  'NAV'      => $class->nav_extra,
     				) 	);
		
	}
	

}

