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
|   > Edit post library
|   > Module written by Matt Mecham
|   > Date started: 19th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


require_once dirname(__FILE__).'/PostEditHistory.php';

class post_functions extends Post {

	var $nav               = array();
	var $title             = "";
	var $post              = array();
	var $topic             = array();
	var $upload            = array();
	var $moderator         = array( 'member_id' => 0,
					'member_name' => "",
					'edit_post' => 0 );
	var $orig_post         = array();
	var $edit_title        = 0;

	//-----------------------------------
	function post_functions($class) {
	
		global $ibforums, $std, $DB;
		
		//-------------------------------------------------
		// Lets load the topic from the database before
		// we do anything else.
		//-------------------------------------------------
		
		$DB->query("SELECT *
			    FROM ibf_topics
			    WHERE tid='".intval($ibforums->input['t'])."'");

		$this->topic = $DB->fetch_row();
		
		//-------------------------------------------------
		// Is it legitimate?
		//-------------------------------------------------
		
		if ( !$this->topic['tid'] ) 
		{
			$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
		}
		
		//-------------------------------------------------
		// Load the old post
		//-------------------------------------------------
		
		$DB->query("SELECT *
			    FROM ibf_posts
			    WHERE pid='".intval($ibforums->input['p'])."'");

		$this->orig_post = $DB->fetch_row();
		
		if ( !$this->orig_post['pid'] ) 
		{
			$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
		}

		//-------------------------------------------------
		// Load the moderator IF the user is moderator
		//-------------------------------------------------
		
		if ( $ibforums->member['id'] )
		{
			$DB->query("SELECT
					member_id,
					member_name,
					mid,
					edit_post,
					edit_topic 
				    FROM ibf_moderators 
				    WHERE
					forum_id='".$class->forum['id']."'
					AND (member_id='".$ibforums->member['id']."'
					  OR (is_group=1
						AND group_id='".$ibforums->member['mgroup']."'))");

			$this->moderator = $DB->fetch_row();
		}
		
		//-------------------------------------------------
		// Lets do some tests to make sure that we are
		// allowed to edit this topic
		//-------------------------------------------------
		
		$can_edit = 0;
		
		if ( $ibforums->member['g_is_supmod'] )
		{
			$can_edit = 1;
		}

		if ( $this->moderator['edit_post'] )
		{
			$can_edit = 1;
		}

		// Song * post has modcomment

		if ( $this->orig_post['author_id'] == $ibforums->member['id'] and $ibforums->member['g_edit_posts'] )
		{
			$can_edit = 1;
		}
		
		if ( !$can_edit ) $std->Error( array(
						LEVEL => 1,
						MSG => 'not_op') );
		
		// Is the topic locked?

		if ( $this->topic['state'] != 'open' )
		{
		        if ( !$ibforums->member['id'] or
			     !( $ibforums->member['g_post_closed'] and
			      ( $ibforums->member['g_is_supmod'] or
				$class->moderator['mid'] ) ) )
			{
				$std->Error( array( LEVEL => 1,
						    MSG => 'locked_topic') );
			}
		}
		
		//-----------------------------
		// // Do we have edit topic abilities?
		//-----------------------------
		
		if ( $this->orig_post['new_topic'] == 1 )
		{
			if ( $ibforums->member['g_is_supmod'] == 1 )
			{
				$this->edit_title = 1;

			} elseif ( $this->moderator['edit_topic'] == 1 )
			{
				$this->edit_title = 1;

			} elseif ( $ibforums->member['g_edit_topic'] == 1 and
				   $ibforums->member['id'] == $this->topic['starter_id'] )
			{
				$this->edit_title = 1;
			}
		}
	}
	
	//-------------------------------------
	function process($class) {
	
		global $ibforums, $std, $DB, $print;
		
		//-------------------------------------------------
		// Parse the post, and check for any errors.
		// overwrites saved post intentionally
		//-------------------------------------------------
		
		$this->post   = $class->compile_post();
		
		// Show the form again
		$this->show_form($class);
	}
	
	
	//-----------------------------------------------------
	function show_form($class) {
	
		global $ibforums, $std, $DB, $print;
		
		$items = PostEditHistory::getItems($this->orig_post['pid']);
		
		$prevoius_text = $class->parser->post_db_parse(
		
			$class->parser->prepare( array(
						'TEXT'    => $this->orig_post['post'],
		     			'CODE'    => $class->forum['use_ibc'],
		     			'SMILIES' => 1,
		     			'HTML'    => $class->forum['use_html']
			)      ) ,
			$class->forum['use_html'] AND $ibforums->member['g_dohtml'] ? 1 : 0
		);
		foreach ($items as $i => $history_item) {
			$items[$i]['new_text']	= $prevoius_text;
			
			$items[$i]['time']		= $std->get_date($history_item['edit_time'], $method);
			
			$items[$i]['member']	= $history_item['editor_name'];
			
			$prevoius_text =
			$items[$i]['old_text'] =  $class->parser->post_db_parse(
				$class->parser->prepare( array(
					'TEXT'    => $history_item['old_text'],
		     			'CODE'    => $class->forum['use_ibc'],
		     			'SMILIES' => 1,
		     			'HTML'    => $class->forum['use_html']
			)      ) ,

			$class->forum['use_html'] AND $ibforums->member['g_dohtml'] ? 1 : 0);
				
		}
		
		$class->output .= $class->html->edit_history( $items, $this->orig_post['forum_id'], $this->orig_post['topic_id'], $this->orig_post['pid'] );
		
		$this->nav = array( "<a href='{$class->base_url}&act=SC&c={$class->forum['cat_id']}'>{$class->forum['cat_name']}</a>",
					"<a href='{$class->base_url}&act=SF&f={$class->forum['id']}'>{$class->forum['name']}</a>",
					"<a href='{$class->base_url}&act=ST&f={$class->forum['id']}&t={$this->topic['tid']}'>{$this->topic['title']}</a>",
				  );
						  
		$this->title = $ibforums->lang['editing_post'].' '.$this->topic['title'];
		
		$print->add_output($class->output);
		
		$class->nav_extra[] = "<a href='".$this->base_url."?showtopic={$this->topic['tid']}'>{$this->topic['title']}</a>";
	        $print->do_output( array( 'TITLE'    => $this->title." -> ".$ibforums->vars['board_name'],
        			 	  'NAV'      => $class->nav_extra,
        			  ) );
		
	}
	

}

