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
|   > Attachment Handler module
|   > Module written by Matt Mecham
|   > Date started: 10th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new attach;

class attach {

    
    function __construct()
    {
        global $ibforums, $DB, $std, $print, $skin_universal;
        
        $ibforums->input['id'] = preg_replace( "/^(\d+)$/", "\\1", $ibforums->input['id'] );
                
        if ($ibforums->input['type'] == 'post')
        {
        	// Handle post attachments.
        	if (!$ibforums->input['attach_id']) {
	        	$DB->query("SELECT
					pid,
					attach_id,
					attach_type,
					attach_file,
					attach_exists
				    FROM ibf_posts
				    WHERE pid='".$ibforums->input['id']."'");
	
	        	if ( !$DB->get_num_rows() ) {
	        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files' ) );
	        	}
	        	
	        	$post = $DB->fetch_row();
	        	$attach = Attachment::createFromPostRow($post);
        	} else {
        		$attach = Attachment::getById( $ibforums->input['attach_id'] );
        		if (!($attach instanceof Attachment)) {
        			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'is_broken_link') );
        		}
        	}

        	/**
        	 * check forum permissions
        	 */
        	/*
        	if ( !$ibforums->topic_cache['tid'] )
        	{
        		$post_id = $attach->postId() ?: $attach->itemId();
        		$DB->query("SELECT
        			f.id as fid,
        			f.parent_id as parent_id,
        			f.password as password,
        			f.read_perms,
        			
        			t.club as club,
        			t.approved as approved,
        			t.state as state,
        			t.starter_id as starter_id,
        			t.pinned as pinned,
        			t.starter_id as starter_id
        			
				    FROM
						ibf_forums f,
						ibf_topics t,
						ibf_posts p
						
				    WHERE p.forum_id = f.id 
				    	AND p.pid = {$post_id}
				    	AND p.topic_id = t.tid
					");

        		$this->topic = $DB->fetch_row();

        	} else {
        		$this->topic = $ibforums->topic_cache;
        	}
        	$this->forum = array (
        		'id' => $this->topic['fid'],
        		'parent_id' => $this->topic['parent_id'],
        		'password' => $this->topic['password'],
        		'read_perms' => $this->topic['read_perms'],
        	);
        	//-------------------------------------
        	// Error out if we can not find the forum or the topic
        	//-------------------------------------

        	if ( !$this->forum['id'] )
        	{
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'is_broken_link') );
        	}

        	if ( $this->topic['club'] and $std->check_perms( $ibforums->member['club_perms'] ) == FALSE )
        	{
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'is_broken_link') );
        	}

        	// Song * user ban patch

        	$std->user_ban_check($this->forum);

        	if ( !$this->topic['approved'] )
        	{

        		if ( !$std->premod_rights($this->topic['starter_id'],
	        		$this->mod[ $ibforums->member['id'] ]['topic_q'], // TODO: find where mod[]
	        		$this->topic['app']) )
        		{
        			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
        		}
        	}

        	if ( $ibforums->member['id'] and !$ibforums->member['g_is_supmod'] )
        	{
        		$DB->query("SELECT *
			    FROM ibf_moderators
			    WHERE
				forum_id=".$this->forum['id']."
				AND (member_id=".$ibforums->member['id']."
				     OR (is_group=1
					 AND group_id='".$ibforums->member['mgroup']."'))");

        		$this->moderator = $DB->fetch_row();
        	}
        	
        	//-------------------------------------
        	// Check viewing permissions, private forums,
        	// password forums, etc
        	//-------------------------------------

        	if ( (!$this->topic['pinned']) and ( ( ! $ibforums->member['g_other_topics'] ) AND ( $this->topic['starter_id'] != $ibforums->member['id'] ) ) )
        	{
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_view_topic') );
        	}

        	$bad_entry = $this->check_access();

        	if ( $bad_entry ) {
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_view_topic') );
        	}
			*/
        	if ( !($attach instanceof Attachment) ) {
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files' ) );
        	}
        	
        	if ( ! $attach->accessIsAllowed($ibforums->member) ) {
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files' ) );
        	}
        	
        	$file = $ibforums->vars['upload_dir']."/".$attach->realFilename();
        	
        	if ( file_exists( $file ) and $attach->type() )
        	{
        		// Update the "hits"..
        		if (!$ibforums->input['attach_id']) {
	        		$DB->query("UPDATE ibf_posts
					    SET attach_hits=attach_hits+1
					    WHERE pid='".$attach->postId()."'");
        		} else {
        			$attach->incHits();
        		}
        		
                  header("Content-type: {$attach->type()}");
                  header("Content-Disposition: inline; filename=\"{$attach->filename()}\"");
                  header("Content-Length: ". (string)(filesize($file)));

                  readfile($file);

        	  exit();

        	} else {
     			// File does not exist..
        		$std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files' ) );
        	}
        }
        
    }
    /*
	function check_access()
	{
		global $ibforums, $std;
		
		$return = 1;
		
		if ( $std->check_perms($this->forum['read_perms']) == TRUE )
		{
			$return = 0;
		}
		
		if ($this->forum['password'] != "")
		{
		
			if ( ! $c_pass = $std->my_getcookie('iBForum'.$this->forum['id']) )
			{
				return 1;
			}
		
			if ( $c_pass == $this->forum['password'] )
			{
				return 0;
			}
			else
			{
			    return 1;
			}
		}
		
		return $return;
	
	}
    */
}
