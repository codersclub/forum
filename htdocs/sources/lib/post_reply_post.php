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
|   > Reply post module
|   > Module written by Matt Mecham
|
+--------------------------------------------------------------------------
*/
require_once dirname(__FILE__).'/PostEditHistory.php';


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
		
		// Lets load the topic from the database
		// before we do anything else.

		$DB->query("SELECT *
			    FROM ibf_topics
			    WHERE
				forum_id='".$class->forum['id']."'
				AND tid='".$ibforums->input['t']."'");

		$this->topic = $DB->fetch_row();
		
		// Is it legitimate?
		if ( !$this->topic['tid'] ) 
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
		}

		// Song * Old Topics Flood, 15.03.05

		if ( $std->user_reply_flood($this->topic['start_date']) )
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
		}		



		//-------------------------------------------------
		// Lets do some tests to make sure that we are
		// allowed to reply to this topic
		//-------------------------------------------------
				
		if ( !$this->topic['approved'] or
		      $this->topic['poll_state'] == 'closed' )
		{
			$std->Error( array( LEVEL => 1,
					    MSG => 'no_replies') );
		}

		if ( $this->topic['starter_id'] == $ibforums->member['id'] )
		{
			if ( !$ibforums->member['g_reply_own_topics'])
			{
				$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'no_replies') );
			}
		}
		
		if ( $this->topic['starter_id'] != $ibforums->member['id'] )
		{
			if ( !$ibforums->member['g_reply_other_topics'])
			{
				$std->Error( array( 'LEVEL' => 1,
						    'MSG' => 'no_replies') );
			}
		}

		if ( $std->check_perms($class->forum['reply_perms']) == FALSE )
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_replies') );
		}
		
		// Is the topic locked?

		if ( $this->topic['state'] != 'open' )
		{
		        if ( !$ibforums->member['id'] or
			     !( $ibforums->member['g_post_closed'] and
				( $ibforums->member['g_is_supmod'] or
				  $class->moderator['mid'] ) ) )
			{
				$std->Error( array( LEVEL => 1,
						    'MSG' => 'locked_topic') );
			}
		}
	}
	

	//------------------------------
	function process(Post $class) {
	
		global $ibforums, $std, $DB, $print;
		
		//-------------------------------------------------
		// Parse the post, and check for any errors.
		//-------------------------------------------------
		
		$this->post = $class->compile_post();
		
		if ( !$class->obj['post_errors'] ) $this->upload = $class->process_upload();
		
		if ( $class->obj['post_errors'] or $class->obj['preview_post'] or $class->upload_errors )
		{
			// Show the form again
			$this->show_form($class);
		} else
		{
			$this->add_reply($class);
		}
	}
	
	
	//------------------------------
	function add_reply(Post $class) {
		
		global $ibforums, $std, $DB, $print, $sess;
		
		//-------------------------------------------------
		// Update the post info with the upload array info
		//-------------------------------------------------
		
		$draft = TopicDraft::getDraft( $this->topic['tid'] );
		if ($draft) {
			$attachments = $draft->getAttachments();
		} else {
			$attachments = array();
		}
		$this->post['attach_exists'] = is_array($this->upload) ? (bool)(count($this->upload) + count($attachments)) : false;
		//-------------------------------------------------
		// Insert the post into the database to get the
		// last inserted value of the auto_increment field
		//-------------------------------------------------
		
		$this->post['topic_id'] = $this->topic['tid'];
		


		//-------------------------------------------------
		// Are we a mod, and can we change the topic state?
		//-------------------------------------------------
		
		$return_to_move = 0;
		
		if ( $ibforums->input['mod_options'] and
		     $ibforums->input['mod_options'] != 'nowt' )
		{
			// PIN

			if ($ibforums->input['mod_options'] == 'pin')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['pin_topic'] == 1)
				{
					$this->topic['pinned'] = 1;
					
					$class->moderate_log('Pinned topic from post form', $this->topic['title']);
				}

			// CLOSE

			} elseif ($ibforums->input['mod_options'] == 'close')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['close_topic'] == 1)
				{
					$this->topic['state'] = 'closed';
					
					$class->moderate_log('Closed topic from post form', $this->topic['title']);
				}

			// MOVE

			} elseif ($ibforums->input['mod_options'] == 'move')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    $class->moderator['move_topic'] == 1)
				{
					$return_to_move = 1;
				}

			// PIN & CLOSE

			} elseif ($ibforums->input['mod_options'] == 'pinclose')
			{
				if ($ibforums->member['g_is_supmod'] == 1 or
				    ( $class->moderator['pin_topic'] == 1 AND
				      $class->moderator['close_topic'] == 1 ) )
				{
					$this->topic['pinned'] = 1;
					$this->topic['state']  = 'closed';
					
					$class->moderate_log('Pinned & closed topic from post form', $this->topic['title']);
				}

			// DELETE

			} elseif ( $ibforums->input['mod_options'] == 'delete' )
			{
				if ( $ibforums->member['g_is_supmod'] == 1 or
				     $class->moderator['delete_post'] == 1 )
				{
					$this->post['use_sig'] = 1;
					$this->post['append_edit'] = 1;
					$this->post['has_modcomment'] = 1;
					$this->post['edit_name'] = $ibforums->member['name'];

					$class->moderate_log('Decline post within answer', $ibforums->input['TopicTitle']);
				}

			// HIDE

			} elseif ( $ibforums->input['mod_options'] == 'hide' )
			{
				if ( $ibforums->member['g_is_supmod'] == 1 or
				     $class->moderator['hide_topic'] == 1 )
				{
					$this->topic['hidden'] = 1;
					$class->moderate_log('Hide topic within answer', $ibforums->input['TopicTitle']);
				}
			}
		}
		
		// Song * decided topics, 20.04.05

		if ( $class->forum['decided_button'] and
		     $ibforums->member['id'] and
		     $ibforums->member['g_use_decided'] and
		     !$this->topic['decided'] )
		{
			$this->topic['decided'] = ( $ibforums->input['topic_decided'] and $ibforums->member['id'] == $this->topic['starter_id'] )
						? 1
						: $this->topic['decided'];
		}



		// Song * merge mod

		
		if ( !$this->post['delete_after'] and
		     !$this->post['attach_exists'] and
		     !$std->mod_tag_exists($this->post['post'], 1) and 
		     !$attachments and 
		     !$draft)
		{
			$DB->query("SELECT
					pid,
					post_date,
					author_id,
					post,
					use_sig,
					queued,
					delete_after 
				    FROM ibf_posts
				    WHERE topic_id='".$this->post['topic_id']."' 
				    ORDER BY post_date DESC
				    LIMIT 1");

			$lastpost = $DB->fetch_row();
        
			if ( $lastpost['author_id'] == $ibforums->member['id'] and
			     !$lastpost['use_sig'] and 
			     !$lastpost['delete_after'] and
			     !$lastpost['queued'] )
			{
				if ( $lastpost['post'] == $this->post['post'] )
				{
					$std->boink_it($ibforums->base_url."showtopic=".$this->topic['tid']."&view=getlastpost", "html");

					exit();
				}

				$timedeff = time() - $lastpost['post_date'];
        
// vot: WHAT THE HELL? 3500
				if ( $ibforums->member['id'] and
				     $timedeff < 3500 and
				     !$std->mod_tag_exists($lastpost['post'], 1) )
				{
//Спящий                                            )
					if ( $ibforums->input['add_merge_edit']  ) //Спящий
					{
						if ($ibforums->member['g_avoid_flood'] != 1)
						{
						  if ( time() - $ibforums->member['last_post'] < $ibforums->member['g_search_flood'] )
						  {
						    $std->Error( array(
							'LEVEL' => 1,
							'MSG' => 'flood_control',
							'EXTRA' => $ibforums->member['g_search_flood'] ) );
						  }
						}
        
//Jureth
						$lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);
		                                $this->post['post'] =
						  $lastpost['post'].
						  " \n\n[color=gray][size=0]".$lang['added_post']." [mergetime]".time()."[/mergetime][/size][/color]\n".
						  $this->post['post'];
						unset($lang);
						
        				PostEditHistory::addItem($lastpost['pid'], $lastpost['post']);
        				
						// update forum properties
						$class->forum['last_title']       = str_replace( "'", "&#39;", $this->topic['title'] );
						$class->forum['last_id']          = $this->topic['tid'];
						$class->forum['last_post']        = time();
						$class->forum['last_poster_name'] = $ibforums->member['id'] ? $ibforums->member['name'] : $ibforums->input['UserName'];
						$class->forum['last_poster_id']   = $ibforums->member['id'];
        
						// update old post
						$DB->query(
						"UPDATE ibf_posts
						SET
						  post='".addslashes($this->post['post'])."',
						  edit_time='".$class->forum['last_post']."' 
						WHERE
						  pid='".$lastpost['pid']."'");


						//----------------------------------------------------
						// vot:
						// Index the Post body for the Indexed Search Engine
						//----------------------------------------------------

						$std->index_reindex_post(
							$lastpost['pid'],
							$this->post['topic_id'],
							$class->forum['id'],
							$this->post['post']);
	


						// Song * club tool
						if ( !$this->topic['club'] )
						{
						  // change forum
						  $DB->query(
						  "UPDATE ibf_forums
						  SET 
						    last_title='".$class->forum['last_title']."',
						    last_id='".$class->forum['last_id']."',
						    last_post='".$class->forum['last_post']."',
						    last_poster_name='".$class->forum['last_poster_name']."',
						    last_poster_id='".$class->forum['last_poster_id']."' 
						  WHERE id='".$class->forum['id']."'");
						} else
						{
						  $DB->query(
						  "UPDATE ibf_forums
						  SET
						    last_post='".$class->forum['last_post']."' 
						  WHERE id='".$class->forum['id']."'");
						}


						// change topic
						$DB->query(
						"UPDATE ibf_topics
						SET 
						   decided='".$this->topic['decided']."',
						   last_poster_id='".$class->forum['last_poster_id']."',
						   last_poster_name='".$class->forum['last_poster_name']."',
						   last_post='".$class->forum['last_post']."' 
						WHERE tid='".$this->topic['tid']."'");
        
						// update member last post time
						$DB->query(
						"UPDATE ibf_members
						SET last_post='".time()."'
						WHERE id='".$ibforums->member['id']."'");
                        
						if( !$ibforums->vars['plg_disable_redirect'] )
						 if ( $class->obj['moderate'] == 1 or $class->obj['moderate'] == 3 )
						 {
						   $DB->query(
						  "UPDATE ibf_forums
						   SET has_mod_posts=1
						   WHERE id='".$class->forum['id']."'");

                        
						   $page = floor( ($this->topic['posts'] ) / $ibforums->vars['display_max_posts']);
						   $page = $page * $ibforums->vars['display_max_posts'];

						   $print->redirect_screen( $ibforums->lang['moderate_post'], "showtopic={$this->topic['tid']}&st=$page" );
						 }
        
						//-------------------------------------------------
						// Redirect them back to the topic
						//-------------------------------------------------
                        
						if ($return_to_move == 1)
						{
							$std->boink_it($class->base_url."act=Mod&amp;CODE=02&amp;f={$class->forum['id']}&amp;t={$this->topic['tid']}","html");
						} else
						{
							$page = floor( ($this->topic['posts'] ) / $ibforums->vars['display_max_posts']);
							$page = $page * $ibforums->vars['display_max_posts'];
							$std->boink_it($class->base_url."showtopic={$this->topic['tid']}&amp;st=$page&amp;#entry{$lastpost['pid']}","html");
						}
                        
						exit();
				        }
				}
			}
		}



		$DB->do_insert_query( $this->post, 'ibf_posts' );
		
		$this->post['pid'] = $DB->get_insert_id();
		
		
		if ($draft) {
			if ($attachments) {
				foreach($attachments as $a) {
					$a->moveTo( $this->post['pid'], Attachment::ITEM_TYPE_POST );
				}
				
				$db_string = $DB->compile_db_update_string(array('attach_exists' => 1));
				$DB->query("UPDATE ibf_posts
					    SET
						$db_string
					    WHERE pid='{$this->post['pid']}'");
				
			}
			$draft->delete();
		}
		
		if ( $this->post['attach_exists'] ) {

			$class->replace_attachments_tags( $this->post['post'], $this->upload, $this->post['pid'] );

			$db_string = $DB->compile_db_update_string(array('post' => $this->post['post']));
			$DB->query("UPDATE ibf_posts
				    SET
					$db_string
				    WHERE pid='{$this->post['pid']}'");
		}
		




		//----------------------------------------------------
		// vot:
		// Index the Post body for the Indexed Search Engine
		//----------------------------------------------------

		$std->index_reindex_post(
				$this->post['pid'],
				$this->post['topic_id'],
				$class->forum['id'],
				$this->post['post']);
	


		if ( $class->obj['moderate'] == 1 or $class->obj['moderate'] == 3 )
		{
			$DB->query("UPDATE ibf_forums
				    SET has_mod_posts=1
				    WHERE id='".$class->forum['id']."'");
			
			$page = floor( ($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
			$page = $page * $ibforums->vars['display_max_posts'];
			
			$print->redirect_screen( $ibforums->lang['moderate_post'], "showtopic={$this->topic['tid']}&st=$page" );
		}
		
		//-------------------------------------------------
		// If we are still here, lets update the
		// board/forum/topic stats
		//-------------------------------------------------
		
		// update forum properties
		$class->forum['posts']++;
		$class->forum['last_title']       = str_replace( "'", "&#39;", $this->topic['title'] );
		$class->forum['last_id']          = $this->topic['tid'];
		$class->forum['last_post']        = time();
		$class->forum['last_poster_name'] = $ibforums->member['id'] ?  $ibforums->member['name'] : $ibforums->input['UserName'];
		$class->forum['last_poster_id']   = $ibforums->member['id'];


		// Song * club tool

		if ( !$this->topic['club'] )
		{
			// change forum
			$DB->query("UPDATE ibf_forums
				    SET 
					last_title='".$class->forum['last_title']."',
					last_id='".$class->forum['last_id']."',
					last_post='".$class->forum['last_post']."',
					last_poster_name='".$class->forum['last_poster_name']."',
					last_poster_id='".$class->forum['last_poster_id']."',
					posts='".$class->forum['posts']."' 
				    WHERE id='".$class->forum['id']."'");
		} else 
		{
			$DB->query("UPDATE ibf_forums
				    SET
					posts='".$class->forum['posts']."',
					last_post='".$class->forum['last_post']."' 
				    WHERE id='".$class->forum['id']."'");
		}



		//-------------------------------------------------
		// Get the correct number of replies the topic has
		//-------------------------------------------------
		
		$DB->query("SELECT COUNT(pid) as posts
			    FROM ibf_posts
			    WHERE
				topic_id='".$this->topic['tid']."'
				AND queued != 1");
		
		$posts = $DB->fetch_row();
		
		$pcount = intval( $posts['posts'] - 1 );
		
		//+------------------------------------------------------------------------------------------------------

		// change topic
		$DB->query("UPDATE ibf_topics
			    SET 
				decided='".$this->topic['decided']."',
				last_poster_id='".$class->forum['last_poster_id']."',
				last_poster_name='".$class->forum['last_poster_name']."',
				last_post='".$class->forum['last_post']."',
				pinned='".$this->topic['pinned']."',
				hidden='".$this->topic['hidden']."',
				state='".$this->topic['state']."',
				posts=$pcount 
			    WHERE tid='".$this->topic['tid']."'");
		
		if ($this->topic['has_mirror']) {
			
			$DB->query("UPDATE ibf_topics
			    SET 
				decided='{$this->topic['decided']}',
				last_poster_id='{$class->forum['last_poster_id']}',
				last_poster_name='{$class->forum['last_poster_name']}',
				last_post='{$class->forum['last_post']}',
				posts=$pcount
			    WHERE mirrored_topic_id='{$this->topic['tid']}'");
			// update forums
			$q = "SELECT forum_id, tid, title FROM ibf_topics WHERE mirrored_topic_id='{$this->topic['tid']}' ";
			
			$quid = $DB->query($q);
			while ($row = $DB->fetch_row($quid)) {
				$DB->query($q = "UPDATE ibf_forums
				    SET 
					last_title='{$row['title']}',
					last_id='{$row['tid']}',
					last_post='".time()."',
					last_poster_name='".$class->forum['last_poster_name']."',
					last_poster_id='".$class->forum['last_poster_id']."',
					posts=posts + 1 
				    WHERE id='{$row['forum_id']}'");
			}
		}
		//+------------------------------------------------------------------------------------------------------
		
		$DB->query("UPDATE ibf_stats
			    SET TOTAL_REPLIES=TOTAL_REPLIES+1");
		
		//-------------------------------------------------
		// If we are a member, lets update thier last post
		// date and increment their post count.
		//-------------------------------------------------
		
		if ( $ibforums->member['id'] )
		{
			$pcount = "";
			$mgroup = "";

			// Increment the users post count
			if ( $class->forum['inc_postcount'] ) $pcount = "posts=posts+1, ";
			
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
							$class->modules->register_class($class);
							$class->modules->on_group_change($ibforums->member['id'], $gid);
						}
					}
				}
			}

			
			$ibforums->member['last_post'] = time();
			
			require(ROOT_PATH."/sources/store/store_module.php");
			$module = new module();
			$module->topic_overcheck(
					array( 	'posts' 	=> intval($posts['posts']-1),
						'amount_over' 	=> $ibforums->vars['topic_over'],
						'amount_give' 	=> $ibforums->vars['topic_pointsover'],
						'give_to' 	=> $this->topic['starter_id']));

			$DB->query("UPDATE ibf_members
				    SET
					$pcount
					$mgroup ".
					$module->post_points($ibforums->vars['pointsper_reply'])."
					last_post='{$ibforums->member['last_post']}'
				    WHERE id='{$ibforums->member['id']}'");

		// Song * additional flood control

            	} else $DB->query("UPDATE ibf_sessions
				   SET last_post='".time()."'
				   WHERE id='".$sess->session_id."'");


		
		//-------------------------------------------------
		// Are we tracking topics we reply in 'auto_track'?
		//-------------------------------------------------
		
		if ( $ibforums->member['id'] AND
		     $ibforums->input['enabletrack'] == 1 )
		{
			$DB->query("SELECT trid
				    FROM ibf_tracker
				    WHERE
					topic_id='".$this->topic['tid']."'
					AND member_id='".$ibforums->member['id']."'");
			
			if ( ! $DB->get_num_rows() )
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
		}
		


		//-------------------------------------------------
		// Check for subscribed topics
		// Pass on the previous last post time of the topic
		// to see if we need to send emails out
		//-------------------------------------------------
		
		$class->topic_tracker( 	$this->topic['tid'],
					$this->post['post'],
					$class->forum['last_poster_name'],
					$this->topic['last_post'] );
		
		//-------------------------------------------------
		// Redirect them back to the topic
		//-------------------------------------------------
		
		if ( $return_to_move == 1 )
		{
			$std->boink_it($class->base_url."act=Mod&amp;CODE=02&amp;f={$class->forum['id']}&amp;t={$this->topic['tid']}","html");
		} else
		{
			$page = floor( ($this->topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
			$page = $page * $ibforums->vars['display_max_posts'];

			$std->boink_it($class->base_url."showtopic={$this->topic['tid']}&amp;st=$page&amp;#entry{$this->post['pid']}","html");
		}
	}

	//-------------------------------
	function show_form(Post $class) {
	
		global $ibforums, $std, $DB, $print;
		
		// Sort out the "raw" textarea input and make it safe incase
		// we have a <textarea> tag in the raw post var.
		
		$raw_post = isset($_POST['Post']) ? $std->txt_htmlspecialchars($_POST['Post']) : "";

		if ( isset($raw_post) )
		{
			$raw_post = $std->txt_raw2form($raw_post);

			$raw_post = str_replace( array ("&#091;", 	"&#093;"     ), 
						 array ("&amp;#091;", 	"&amp;#093;" ),
						 $raw_post);
		}
		
		// Do we have any posting errors?
		if ( $class->obj['post_errors'] )
		{
			if ( $ibforums->member['id'] and $class->obj['post_errors'] == "no_mail" )
			{
				$ibforums->lang[ $class->obj['post_errors'] ] = sprintf($ibforums->lang[ $class->obj['post_errors'] ], $ibforums->member['disable_mail_reason']);
			}

			$class->output .= $class->html->errors( $ibforums->lang[ $class->obj['post_errors'] ]);
		}
		if ($class->upload_errors) {
			foreach ($class->upload_errors as $error_message) {
				$class->output .= $class->html->errors( $error_message );
			}
		}
		if ( $class->obj['preview_post'] )
		{
			$attach_exists = is_array($this->upload) ? (bool)count($this->upload) : false;

			$draft = TopicDraft::createDraft( $this->topic['tid'], $this->post['post'] );
			
			if ( $attach_exists ) {
				$class->replace_attachments_tags( $raw_post, $this->upload, $draft->id(), 'topic_draft' );
				$draft->setText( $raw_post );
				$draft->save();
			}
			
			$this->upload = array_merge($this->upload ?: array(), $draft->getAttachments());
			
			if ( $this->upload ) {
				$class->process_edituploads( $this->upload, Attachment::ITEM_TYPE_TOPIC_DRAFT );
			}
			
		} else {
			
			$draft = TopicDraft::getDraft( $this->topic['tid'] );
			
			if ($draft) {
				$raw_post = $draft->text();
				$this->upload = array_merge($this->upload, $draft->getAttachments());
			}
			
		}
		
		if ( $draft ) {
			$this->post['post'] = $raw_post;
			
			$this->post['post'] = $class->parser->post_db_parse(
					     $class->parser->prepare( array(
						 'TEXT'    => $this->post['post'],
						 'CODE'    => $class->forum['use_ibc'],
						 'SMILIES' => $ibforums->input['enableemo'],
						 'HTML'    => $class->forum['use_html']
     						)      ) ,
					     $class->forum['use_html'] AND $ibforums->member['g_dohtml'] ? 1 : 0);
			
			$class->output .= $class->html->preview( $this->post['post'], $class->upload_errors );
		}
		
		$class->check_upload_ability();
		
		$class->output .= $class->html_start_form( array( 
					  1 => array( 'CODE', '03' ),
					  2 => array( 't'   , $this->topic['tid'])
					) 	);
														
		//---------------------------------------
		// START TABLE
		//---------------------------------------
		

                $warning = "";

                if ( $class->obj['moderate'] == 1 or $class->obj['moderate'] == 3 or $ibforums->member['mod_posts'] )
		{
			$warning = $ibforums->lang['mod_posts_warning'];
		}

		$class->output .= $warning;

		$class->output .= $class->html->table_structure();
		
		//---------------------------------------
		
		$start_table = $class->html->table_top( "{$ibforums->lang['top_txt_reply']} {$this->topic['title']}");
		
		$name_fields = $class->html_name_field();
		
		$post_box    = $class->html_post_body($raw_post, $this->topic);
		
		$mod_options = $class->mod_options(1);
		
		$end_form    = $class->html->EndForm( $ibforums->lang['submit_reply'] );
		
		$post_icons  = $class->html_post_icons();
		
		
		if ( $class->obj['can_upload'] ) {
			
			if ( $this->upload ) {
				$upload_field .= $class->html->edit_upload_field( 
					$std->size_format( $ibforums->member['g_attach_max'] * 1024 ),
					$this->upload );
				
			} else {
				$upload_field = $class->html->Upload_field( $std->size_format( $ibforums->member['g_attach_max'] * 1024 ) );
			}
		}
		
		//---------------------------------------
		
		$class->output = preg_replace( "/<!--START TABLE-->/" , "$start_table"  , $class->output );
		$class->output = preg_replace( "/<!--NAME FIELDS-->/" , "$name_fields"  , $class->output );
		$class->output = preg_replace( "/<!--POST BOX-->/"    , "$post_box"     , $class->output );
		$class->output = preg_replace( "/<!--POST ICONS-->/"  , "$post_icons"   , $class->output );
		$class->output = preg_replace( "/<!--UPLOAD FIELD-->/", "$upload_field" , $class->output );
		$class->output = preg_replace( "/<!--MOD OPTIONS-->/" , "$mod_options"  , $class->output );
		$class->output = preg_replace( "/<!--END TABLE-->/"   , "$end_form"     , $class->output );

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
		
		$class->html_checkboxes('reply', $this->topic['tid']);
		
		//---------------------------------------
		
		$class->html_topic_summary($this->topic['tid']);
		
		$this->nav = array( "<a href='{$class->base_url}act=SC&amp;c={$class->forum[cat_id]}'>{$class->forum['cat_name']}</a>",
				    "<a href='{$class->base_url}showforum={$class->forum['id']}'>{$class->forum['name']}</a>",
				    "<a href='{$class->base_url}showtopic={$this->topic['tid']}'>{$this->topic['title']}</a>",
				  );
						  
		$this->title = $ibforums->lang['replying_in'].' '.$this->topic['title'];
		
		$print->add_output("$class->output");
		
	        $print->do_output( array( 'TITLE'    => $this->title." -> ".$ibforums->vars['board_name'],
        			 	  'NAV'      => $class->nav_extra,
   				) 	);
		
	}
	

}

