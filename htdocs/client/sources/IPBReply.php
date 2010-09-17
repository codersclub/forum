<? 

// Stub class. Needed for the IBForums engine.
class Post {
}

class Html {
	function errors($error) {

		echo "MESSAGE: " . $error;
		exit();
	}
}

if($B_Parent) {

	require "../sources/lib/post_reply_post.php";
} else {

	require "../sources/lib/post_new_post.php";
}

require "../sources/lib/post_parser.php";
require "sources/Reply.php";

$Log = new Log();

class IPBReply extends Reply {

var $poster;
var $forum;
var $offline;
var $parser;
var $html;
var $email;
	function IPBReply() {

	global $std, $DB, $B_Board, $B_Parent, $B_Main, $ibforums, $B_Body, $B_Icon, $HTTP_POST_VARS, $B_Subject, $Log, $B_Descr;

		$this->offline = 1;
		$this->parser = new post_parser();
		$this->html = new Html();

		if(!empty($ibforums->vars['client_copyright_msg'])) {

			$B_Body .= "\n\n" . $ibforums->vars['client_copyright_msg'];
		}

		$ibforums->input['Post'] = $std->clean_value($B_Body);

		$ibforums->input['iconid'] = $B_Icon;

		if(empty($B_Board))
			return;

	        $DB->query("
			SELECT
				f.*,
				c.id as cat_id,
				c.name as cat_name
			FROM
				ibf_forums f,
				ibf_categories c
			WHERE
				f.id = '" . intval($B_Board) . "' AND
				c.id = f.category
		");

		$this->forum = $DB->fetch_row();

		if(0 != $B_Parent) {

			$DB->query("
				SELECT
					t.tid
				FROM
					ibf_topics t,
					ibf_posts p
				WHERE
					p.pid = '" . intval($B_Parent) . "' AND
					t.tid=p.topic_id
			");
		        $B_Parent = $DB->fetch_row();
			$B_Parent = $B_Parent['tid'];

			$ibforums->input['t'] = $B_Parent;
		} else {

			$HTTP_POST_VARS['TopicDesc'] = $B_Descr;
			$ibforums->input['TopicDesc'] = $std->clean_value($B_Descr);
			$HTTP_POST_VARS['TopicTitle'] = $B_Subject;
			$ibforums->input['TopicTitle'] = $std->clean_value($B_Subject);
		}

		$Log->addForumList("(" . $B_Board . ")");
		$Log->addInterval($B_Parent, 0, "reply");		

		$this->poster = new post_functions($this);

		echo "~OK~";
		// And do logging
		$Log->doLog();		

		$this->poster->process($this);

		echo "~OK~";
	}

	// Need to fill this, when Forumizer will be ready to do upload.
	function process_upload() {
	}

	function topic_tracker($tid="", $post="", $poster="", $last_post="" )
	{
		global $ibforums, $DB, $std;
		
		require "../sources/lib/emailer.php";
		
		$this->email = new emailer();
		
		//-------------------------
	
		if ($tid == "")
		{
			return TRUE;
		}
	
// Song * moderators messages

		$post = preg_replace( "#\[mm\](.+?)\[/mm\]#ies", "\$this->parser->regex_moderator_message('\\1')", $post);

// Song * moderators messages

		// Get the email addy's, topic ids and email_full stuff - oh yeah.
		// We only return rows that have a member last_activity of greater than the post itself		

		$DB->query("SELECT tr.trid, tr.topic_id, m.name, m.email, m.id, m.email_full, m.language, m.last_activity, t.title, t.forum_id
				    FROM ibf_tracker tr, ibf_topics t,ibf_members m
				    WHERE tr.topic_id='$tid'
				    AND tr.member_id=m.id
				    AND m.disable_mail=0 
				    AND m.id <> '{$ibforums->member['id']}'
				    AND t.tid=tr.topic_id
				    AND m.last_activity > '$last_post'");
		
		if ( $DB->get_num_rows() )
		{
			$trids = array();
			
			while ( $r = $DB->fetch_row() )
			{
			
				$r['language'] = $r['language'] ? $r['language'] : 'en';
				
				if ($r['email_full'] == 1)
				{
					$this->email->get_template("subs_with_post", $r['language']);
			
					$this->email->build_message( array(
														'TOPIC_ID'        => $r['topic_id'],
														'FORUM_ID'        => $r['forum_id'],
														'TITLE'           => $r['title'],
														'NAME'            => $r['name'],
														'POSTER'          => $poster,
														'POST'            => $post,
													  )
												);
												
					$this->email->subject = $ibforums->lang['tt_subject'];
					$this->email->to      = $r['email'];
					$this->email->send_mail();
					
				}
				else
				{
				
					$this->email->get_template("subs_no_post", $r['language']);
			
					$this->email->build_message( array(
														'TOPIC_ID'        => $r['topic_id'],
														'FORUM_ID'        => $r['forum_id'],
														'TITLE'           => $r['title'],
														'NAME'            => $r['name'],
														'POSTER'          => $poster,
													  )
												);
												
					$this->email->subject = $ibforums->lang['tt_subject'];
					$this->email->to      = $r['email'];
					
					$this->email->send_mail();
					
				}
				
				$trids[] = $r['trid'];
			}
		}
	}

	function forum_tracker($fid="", $this_tid="", $title="", $forum_name="")
	{
		global $ibforums, $DB, $std;
		
		require "../sources/lib/emailer.php";
		
		$this->email = new emailer();
		
		//-------------------------
	
		if ($this_tid == "")
		{
			return TRUE;
		}
		
		if ($fid == "")
		{
			return TRUE;
		}
		
		// Work out the time stamp needed to "guess" if the user is still active on the board
		// We will base this guess on a period of non activity of time_now - 30 minutes.
		
		$time_limit = time() - (30*60);
		
		// Get the email addy's, topic ids and email_full stuff - oh yeah.
		// We only return rows that have a member last_activity of greater than the post itself
		
		$DB->query("SELECT tr.frid, m.name, m.email, m.id, m.language, m.last_activity, m.org_perm_id, g.g_perm_id
				    FROM ibf_forum_tracker tr,ibf_members m, ibf_groups g
				    WHERE tr.forum_id='$fid'
				    AND tr.member_id=m.id
				    AND m.disable_mail=0 
				    AND m.mgroup=g.g_id
				    AND m.id <> '{$ibforums->member['id']}'
				    AND m.last_activity < '$time_limit'");
		
		if (  $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
			
				$perm_id = ( $r['org_perm_id'] ) ? $r['org_perm_id'] : $r['g_perm_id'];
				
				if ($this->forum['read_perms'] != '*')
				{
					if ( ! preg_match("/(^|,)".str_replace( ",", '|', $perm_id )."(,|$)/", $this->forum['read_perms'] ) )
        			{
        				continue;
       				}
				}
        
				$r['language'] = $r['language'] ? $r['language'] : 'en';
				
				$this->email->get_template("subs_new_topic", $r['language']);
		
				$this->email->build_message( array(
													'TOPIC_ID'        => $this_tid,
													'FORUM_ID'        => $fid,
													'TITLE'           => $title,
													'NAME'            => $r['name'],
													'POSTER'          => $ibforums->member['name'],
													'FORUM'           => $forum_name,
												  )
											);
											
				$this->email->subject = $ibforums->lang['ft_subject'];
				$this->email->to      = $r['email'];
				
				$this->email->send_mail();
			}
		}
		return TRUE;
	}

	function compile_post()
	{
		global $ibforums, $std, $REQUEST_METHOD;
		
		$ibforums->vars['max_post_length'] = $ibforums->vars['max_post_length'] ? $ibforums->vars['max_post_length'] : 2140000;
		
                if (($this->moderator['mid'] != "" &&  $ibforums->member['id'] != 0)|| $ibforums->member['g_is_supmod'] == 1)
                {
                      $modflag = TRUE;
                } else {
                      $modflag = FALSE;
                }

		//----------------------------------------------------------------
		// Sort out some of the form data, check for posting length, etc.
		// THIS MUST BE CALLED BEFORE CHECKING ATTACHMENTS
		//----------------------------------------------------------------
		
		$ibforums->input['enablesig']   = $ibforums->input['enablesig']   == 'yes' ? 1 : 0;
		$ibforums->input['enableemo']   = $ibforums->input['enableemo']   == 'yes' ? 1 : 0;
		$ibforums->input['enabletrack'] = $ibforums->input['enabletrack'] ==   1   ? 1 : 0;
		
		//----------------------------------------------------------------
		// Do we have a valid post?
		//----------------------------------------------------------------
		
		if (strlen( trim($ibforums->input['Post']) ) < 1)
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_post') );
		}
		
		if (strlen( $ibforums->input['Post'] ) > ($ibforums->vars['max_post_length']*1024))
		{
			$std->Error( array( LEVEL => 1, MSG => 'post_too_long') );
		}

		$post = array(
						'author_id'   => $ibforums->member['id'] ? $ibforums->member['id'] : 0,
						'use_emo'     => 1,
						'ip_address'  => $ibforums->input['IP_ADDRESS'],
						'post_date'   => time(),
						'edit_time'   => time(),
						'icon_id'     => $ibforums->input['iconid'],
						'post'        => $this->parser->convert( array( TEXT    => $ibforums->input['Post'],
							SMILIES => 1,
							CODE    => $this->forum['use_ibc'],
							HTML    => $this->forum['use_html'],
                                                        MOD_FLAG => $modflag,
								  )
							),
						'author_name' => $ibforums->member['id'] ? $ibforums->member['name'] : $ibforums->input['UserName'],
						'forum_id'    => $this->forum['id'],
						'topic_id'    => "",
						'queued'      => ( $this->obj['moderate'] == 1 || $this->obj['moderate'] == 3 ) ? 1 : 0,
						'attach_id'   => "",
						'attach_hits' => "",
						'attach_type' => "",
					 );
					 
		// If we had any errors, parse them back to this class
		// so we can track them later.
	    
		$this->obj['post_errors'] = $this->parser->error;

		return $post;
	}
}

?>
