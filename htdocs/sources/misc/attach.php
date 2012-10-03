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
        
        $ibforums->input['id'] = intval( $ibforums->input['id'] );
                
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
}
