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
|
+--------------------------------------------------------------------------
*/




class post_functions extends Post {

	var $nav = array();
	var $title = "";
	var $post  = array();
	var $topic = array();
	var $upload = array();
	var $mod_topic = array();
	var $poll_count = 0;
	var $poll_choices = "";

	var $m_group = "";
	
	function post_functions($class)
	{
	
		global $ibforums, $std, $DB;
		
		//-------------------------------------------------------------------------
		// Sort out maximum number of poll choices allowed
		//-------------------------------------------------------------------------
		
		$ibforums->vars['max_poll_choices'] = $ibforums->vars['max_poll_choices'] ? $ibforums->vars['max_poll_choices'] : 10;
		
		$ibforums->lang['poll_choices']      = sprintf( $ibforums->lang['poll_choices'], $ibforums->vars['max_poll_choices'] );
		
		//-------------------------------------------------------------------------
		// Lets do some tests to make sure that we are allowed to start a new topic
		//-------------------------------------------------------------------------
		
		if (! $ibforums->member['g_post_polls'])
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_start_polls') );
		}
		
		if ( ! $class->forum['allow_poll'] )
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_start_polls') );
		}
			
		$this->m_group = $ibforums->perm_id;
		
		if ( $std->check_perms($class->forum['start_perms']) != TRUE )
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_start_polls') );
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
		
		if ( (strlen($ibforums->input['TopicTitle']) < 2) or (!$ibforums->input['TopicTitle'])  ) {
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
		// check to make sure we have a correct # of choices
		//-------------------------------------------------
		
		$this->poll_choices = $ibforums->input['PollAnswers'];

		$this->poll_choices .= "<br>";

		$this->poll_choices = preg_replace( "/<br><br>/" , "<br>", $this->poll_choices ); // Jureth: added <br> to the result text
		
		$this->poll_choices = preg_replace( "/<br>/e"    , "\$this->regex_count_choices()" , $this->poll_choices );
		
		if ($this->poll_count > $ibforums->vars['max_poll_choices'])
		{
			$class->obj['post_errors'] = 'poll_to_many';
		}
		
		if ($this->poll_count < 1)
		{
			$class->obj['post_errors'] = 'poll_not_enough';
		}

// Song * multiple chices

		if ( $ibforums->input['multi_poll'] ) 
		{
			if( $this->poll_count < $ibforums->input['multi_poll_min'] ) 
			{
				 $class->obj['post_errors'] = 'multi_poll_min_error';
			}

			if( $this->poll_count < $ibforums->input['multi_poll_max'] ) 
			{
				 $class->obj['post_errors'] = 'multi_poll_max_error';
			}
		}        

		if ( $ibforums->input['weighted_poll'] && $this->poll_count < 3 ) 
		{
			$class->obj['post_errors'] = 'weighted_poll_min_error';
		}

// Song * multiple chices

		//-------------------------------------------------
		// If we don't have any errors yet, parse the upload
		//-------------------------------------------------
		
		if ($class->obj['post_errors'] == "")
		{
			$this->upload = $class->process_upload();
		}
		
		
		if ( ($class->obj['post_errors'] != "") or ($class->obj['preview_post'] != "") ) 
		{
			// Show the form again
			$this->show_form($class);

		} else $this->add_new_poll($class);
	}
	
	function add_new_poll($class) {
		
		global $ibforums, $std, $DB, $print, $sess;
		
		//-------------------------------------------------
		// Sort out the poll stuff
		// This is somewhat contrived, but it has to be
		// compatible with the current perl version.
		//-------------------------------------------------
		
		$poll_array  = array();
// Jureth	$count       = 0;
		$count       = 1; // Jureth
		
		$polls       = explode( "<br>", $this->poll_choices );
		
		foreach ($polls as $polling)
		{
			if ( trim($polling) == '' ) continue;

			$poll_array[] = array( $count , $class->parser->bad_words($polling), 0 );

			$count++;
		}
		
		//-------------------------------------------------
		// Fix up the topic title
		//-------------------------------------------------
		
		if ($ibforums->vars['etfilter_punct'])
		{
			$ibforums->input['TopicTitle']	= preg_replace( "/\?{1,}/"      , "?"    , $ibforums->input['TopicTitle'] );		
			$ibforums->input['TopicTitle']	= preg_replace( "/(&#33;){1,}/", "&#33;" , $ibforums->input['TopicTitle'] );
		}
		
		if ($ibforums->vars['etfilter_shout'])
		{
			$ibforums->input['TopicTitle'] = ucwords($ibforums->input['TopicTitle']);
		}
		
		$ibforums->input['TopicTitle'] = $class->parser->bad_words( $ibforums->input['TopicTitle'] );
		$ibforums->input['TopicDesc']  = $class->parser->bad_words( $ibforums->input['TopicDesc']  );
		
		if ( $ibforums->vars['poll_disable_noreply'] != 1 )
		{
			$poll_state = $ibforums->input['allow_disc'] == 0 ? 'open' : 'closed';
		}
		else
		{
			$poll_state = 'open';
		}
		
		//-------------------------------------------------
		// Build the master array
		//-------------------------------------------------
		$this->topic = array(
							  'title'            => $ibforums->input['TopicTitle'],
							  'description'      => $ibforums->input['TopicDesc'] ,
							  'state'            => 'open',
							  'posts'            => 0,
							  'starter_id'       => $ibforums->member['id'],
							  'starter_name'     => $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'],
							  'start_date'       => time(),
							  'last_poster_id'   => $ibforums->member['id'],
							  'last_poster_name' => $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'],
							  'last_post'        => time(),
							  'icon_id'          => $ibforums->input['iconid'],
							  'author_mode'      => $ibforums->member['id'] ? 1 : 0,
							  'poll_state'       => $poll_state,
							  'last_vote'        => 0,
							  'views'            => 0,
							  'forum_id'         => $class->forum['id'],
							  'approved'         => $class->obj['moderate'] ? 0 : 1,
							  'pinned'           => 0,
				   );


		
		//-------------------------------------------------
		// Insert the topic into the database to get the
		// last inserted value of the auto_increment field
		// follow suit with the post
		//-------------------------------------------------
		
		$db_string = $DB->compile_db_insert_string( $this->topic );
		
		$DB->query("INSERT INTO ibf_topics (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
		$this->post['topic_id']  = $DB->get_insert_id();
		$this->topic['tid']      = $this->post['topic_id'];
		/*---------------------------------------------------*/
		
		// Update the post info with the upload array info
		
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
		
		if ( $class->obj['moderate'] == 3 )
		{
			$this->post['queued'] = 0;
		}
		
		//  delayed post deleting, 21.07.21

		$this->post['delete_after'] = 0;
		
		$db_string = $DB->compile_db_insert_string( $this->post );
		
		$DB->query("INSERT INTO ibf_posts (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");

		$this->post['pid'] = $DB->get_insert_id();
		if ($this->post['attach_exists']) {
			foreach($this->upload as $attach) {
				($attach instanceof Attach2);
				
				$attach->setPostId($this->post['pid']);
				
				$attach->saveToDB();
				
				unset($array['attach_id']); // because it is not exists yet, see below
				
			}
		}
		
		//-------------------------------------------------
		// Add the poll to the forum_polls table
		// if we are moderating this post
		//-------------------------------------------------
		
		$poll = array (
	  			'tid'           	=> $this->topic['tid'],
	  			'forum_id'      	=> $class->forum['id'],
	  			'start_date'    	=> time(),
	  			'choices'       	=> addslashes(serialize($poll_array)),
	  			'starter_id'    	=> $ibforums->member['id'],
	  			'votes'         	=> 0,
	  			'poll_question' 	=> $class->parser->bad_words($ibforums->input['pollq']),
// Song * multiple choices
				'is_multi_poll'    	=> ( $ibforums->input['multi_poll'] ) ? 1 : 0,
				'multi_poll_min'   	=> ( $ibforums->input['multi_poll'] ) ? $ibforums->input['multi_poll_min'] : 0,
				'multi_poll_max'   	=> ( $ibforums->input['multi_poll'] ) ? $ibforums->input['multi_poll_max'] : 0,                                                                                      
				'is_weighted_poll' 	=> ( $ibforums->input['weighted_poll'] ) ? 1 : 0,
				'weighted_poll_places'  => ( $ibforums->input['weighted_poll'] ) ? $ibforums->input['weighted_poll_places'] : 0,
// Song * multiple choices

			);

// Song * poll life, 25.03.05

		$life = intval($ibforums->input['life']);

		if ( $life and $life < 366 )
		{
			$life = time() + 60*60*24*$life;

			$poll['live_before'] = $life;
		}

// Song * poll life, 25.03.05

		$db_string = $std->compile_db_string( $poll );
											  
		$DB->query("INSERT INTO ibf_polls (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
		
		if ( $class->obj['moderate'] == 1 OR $class->obj['moderate'] == 2 )
		{
			// Redirect them with a message telling them the post has to be previewed first
			
			$DB->query("UPDATE ibf_forums SET has_mod_posts=1 WHERE id=".$class->forum['id']);
			
			$print->redirect_screen( $ibforums->lang['moderate_topic'], "act=SF&f={$class->forum['id']}" );
		}
		
		//-------------------------------------------------
		// If we are still here, lets update the
		// board/forum stats
		//-------------------------------------------------
		
		$class->forum['last_title']       = $this->topic['title'];
		$class->forum['last_id']          = $this->topic['tid'];
		$class->forum['last_post']        = time();
		$class->forum['last_poster_name'] = $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'];
		$class->forum['last_poster_id']   = $ibforums->member['id'];
		$class->forum['topics']++;
		
		// Update the database
		
		$DB->query("UPDATE ibf_forums SET last_title='".$class->forum['last_title']."',
						  last_id='".$class->forum['last_id']."',
						  last_post='".$class->forum['last_post']."',
						  last_poster_name='".$class->forum['last_poster_name']."',
						  last_poster_id='".$class->forum['last_poster_id']."',
						  topics='".$class->forum['topics']."' 
			    WHERE id='".$class->forum['id']."'");
		
		$DB->query("UPDATE ibf_stats SET TOTAL_TOPICS=TOTAL_TOPICS+1");

		//-------------------------------------------------
		// If we are a member, lets update thier last post
		// date and increment their post count.
		//-------------------------------------------------
		
		if ( $ibforums->member['id'] )
		{
			$pcount = "";
			$mgroup = "";

			if ($class->forum['inc_postcount'])
			{
				// Increment the users post count
				
				$pcount = "posts=posts+1, ";
				
			}
			
			// Are we checking for auto promotion?
			
			if ( $ibforums->member['g_promotion'] != '-1&-1' and !$ibforums->member['disable_group'] )
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
			
			require(ROOT_PATH."/sources/store/store_module.php");
			$module = new module();
			$DB->query("UPDATE ibf_members SET ".$pcount.$mgroup.$module->post_points($ibforums->vars['pointsper_poll']).
						  "last_post='"    .$ibforums->member['last_post']   ."'".
			 "WHERE id='"     .$ibforums->member['id']."'");

// Song * additional flood control

            	} else $DB->query("UPDATE ibf_sessions SET last_post='".time()."' WHERE id='".$sess->session_id."'");

// Song * additional flood control
		
		//-------------------------------------------------
		// Set a last post time cookie
		//-------------------------------------------------
		
		$std->my_setcookie( "LPid", time() );
		
		//-------------------------------------------------
		// Redirect them back to the topic
		//-------------------------------------------------
		

		$std->boink_it($class->base_url."act=ST&amp;f={$class->forum['id']}&amp;t={$this->topic['tid']}");
	}


	function show_form($class) {
	
		global $ibforums, $std, $DB, $print;
		
		// Sort out the "raw" textarea input and make it safe incase
		// we have a <textarea> tag in the raw post var.
		
		$raw_post    = isset($_POST['Post'])        ? $std->txt_htmlspecialchars($_POST['Post'])       : "";
		$topic_title = isset($_POST['TopicTitle'])  ? str_replace("'", "&#39;", stripslashes($_POST['TopicTitle']) ) : "";
		$topic_desc  = isset($_POST['TopicDesc'])   ? str_replace("'", "&#39;", stripslashes($_POST['TopicDesc']) ) : "";
		$poll        = isset($_POST['PollAnswers']) ? $std->txt_htmlspecialchars($_POST['PollAnswers']) : "";
		
		if (isset($raw_post))
		{
			$raw_post = $std->txt_raw2form($raw_post);
			$raw_post = str_replace( '<%', "&lt;%"  , $raw_post );
			
		}
		
		// Do we have any posting errors?
		
		if ($class->obj['post_errors'])
		{
			if ( $ibforums->member['id'] and $class->obj['post_errors'] == "no_mail" )
			{
				$ibforums->lang[ $class->obj['post_errors'] ] = sprintf($ibforums->lang[ $class->obj['post_errors'] ], $ibforums->member['disable_mail_reason']);
			}

			$class->output .= $class->html->errors( $ibforums->lang[ $class->obj['post_errors'] ]);
		}
		
		if ($class->obj['preview_post'])
		{
			$this->post['post'] = $class->parser->post_db_parse(
					     $class->parser->prepare( array(
					     								 'TEXT'    => $this->post['post'],
					     								 'CODE'    => $class->forum['use_ibc'],
					     								 'SMILIES' => $ibforums->input['enableemo'],
					     								 'HTML'    => $class->forum['use_html']
					     						)      ) ,
										     $class->forum['use_html']);
			$class->output .= $class->html->preview( $this->post['post'] );
		}
		
		$extra = "";
		
		if ($ibforums->vars['poll_tags'])
		{
			$extra = $ibforums->lang['poll_tag_allowed'];
		}
		
		$class->output .= $class->html_start_form( array( 1 => array( 'CODE', '11' ), 2 => array( 'f', $class->forum['id']) ) );
		
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
		
		$topic_title = $class->html->topictitle_fields( array( TITLE => $topic_title, DESC => $topic_desc ) );
		
		$start_table = $class->html->table_top( "{$ibforums->lang['top_txt_poll']}: {$class->forum['name']}");
		
		$name_fields = $class->html_name_field();
		
		$post_box    = $class->html_post_body( $raw_post );
		
		$mod_options = $class->mod_options();

		$poll_box    = $class->html->poll_box( $poll, $extra);
		
		$end_form    = $class->html->EndForm( $ibforums->lang['submit_poll'] );
		
		$post_icons  = $class->html_post_icons();
		
		if ($class->obj['can_upload'])
		{
			$upload_field = $class->html->Upload_field( $ibforums->member['g_attach_max'] * 1024 );
		}
		
		//---------------------------------------
		
		$class->output = preg_replace( "/<!--START TABLE-->/" , "$start_table"  , $class->output );
		$class->output = preg_replace( "/<!--NAME FIELDS-->/" , "$name_fields"  , $class->output );
		$class->output = preg_replace( "/<!--POST BOX-->/"    , "$post_box"     , $class->output );
		$class->output = preg_replace( "/<!--POST ICONS-->/"  , "$post_icons"   , $class->output );
		$class->output = preg_replace( "/<!--UPLOAD FIELD-->/", "$upload_field" , $class->output );
		//$class->output = preg_replace( "/<!--MOD OPTIONS-->/" , "$mod_options"  , $class->output );
		$class->output = preg_replace( "/<!--END TABLE-->/"   , "$end_form"     , $class->output );
		$class->output = preg_replace( "/<!--TOPIC TITLE-->/" , "$topic_title"  , $class->output );
		$class->output = preg_replace( "/<!--POLL BOX-->/"    , "$poll_box"     , $class->output );

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
		
// Song * IBF forum rules		

		$class->output = str_replace("<!--FORUM RULES-->", $std->print_forum_rules($class->forum), $class->output );
		
		if ( $ibforums->vars['poll_disable_noreply'] != 1 )
		{
			$class->output = str_replace("<!--IBF.POLL_OPTIONS-->", $class->html->poll_options(), $class->output );
		}
		
		//---------------------------------------
		// Add in siggy buttons and such
		//---------------------------------------
		
		$class->html_checkboxes();
		
		
		$class->html_add_smilie_box();
		
		$this->nav = array( "<a href='{$class->base_url}act=SC&amp;c={$class->forum['cat_id']}'>{$class->forum['cat_name']}</a>",
							"<a href='{$class->base_url}act=SF&amp;f={$class->forum['id']}'>{$class->forum['name']}</a>",
						  );
		$this->title = $ibforums->lang['posting_new_topic'];
		
		$print->add_output("$class->output");

	        $print->do_output( array( 'TITLE'    => $ibforums->vars['board_name']." -> ".$this->title,
	       				  'NAV'      => $class->nav_extra,
        			) );

	}
	
	function regex_count_choices() {
		
		++$this->poll_count;
		
		return "<br>";
		
	}
	

}

