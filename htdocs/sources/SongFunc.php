<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Song
|   (c) 2004 Sources.RU
|   http://www.sources.ru
|   ========================================
|   Web: http://www.sources.ru
|   Email: song@sources.ru
|   Licence Info: http://www.sources.ru
+---------------------------------------------------------------------------
|
|   > Toolbar display module
|   > Module written by Song
|   > Date started: 25th November 2004
|
|   > Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new SongFunc;

class SongFunc {

var $output    = "";


	function SongFunc() {
	global $ibforums, $std, $print, $DB;

	$ibforums->input['t'] = intval($ibforums->input['t']);

	switch ( $ibforums->input['CODE'] ) 
	{
		case 'forums_recount':
			$this->output = $this->all_forums_order_recount();
			break;

		case 'last_post_id':
			$this->output = $this->check_new();
			break;

		case 'index_posts':
			$this->output = $this->index_data_posts($ibforums->input['f']);
			break;

		case 'index_topics':
			$this->output = $this->index_data_topics($ibforums->input['f']);
			break;

		case 'club_enable':
			$this->club_member_enable($ibforums->input['mid']);
			break;
		case 'my_func':
			$this->output = $this->my_func();
			break;

		default:
		     	$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
		        break;
	}

	if ( $ibforums->input['CODE'] == 'last_post_id' )
	{
		//---------------------------------------
		// Close this DB connection
		//---------------------------------------

		$DB->close_db();

		//---------------------------------------
		// Start GZIP compression
	        //---------------------------------------

	        if ( $ibforums->vars['disable_gzip'] != 1 )
	        {
	        	$buffer = ob_get_contents();
	        	ob_end_clean();
	        	ob_start('ob_gzhandler');
	        	print $buffer;
	        }

	        $print->do_headers();
	        print $this->output;

	        exit;
	} else
	{
	    	$print->add_output("$this->output");
	        $print->do_output( array() );
	}

	}

	function my_func() {
	global $ibforums, $DB;

	if ( $ibforums->member['id'] != 2 )
	{
		$std->Error( array( LEVEL => 1, MSG => 'no_permission') );
	}

	$main = $DB->query("SELECT m.id,m.last_activity 
		    FROM ibf_check_members cm, ibf_members m 
		    WHERE cm.mid=m.id and m.last_activity != cm.last_visit and m.last_activity > 0");

	while ( $member = $DB->fetch_row($main) )
	{
		$DB->query("UPDATE ibf_check_members SET last_visit='".$member['last_activity']."' WHERE mid='".$member['id']."'");
	}

	return "Done!";

	}


	function club_member_enable($mid = 0) {
	global $ibforums, $DB, $std;

	$mid = intval($mid);
// vot: debug
//echo "mid=$mid<br>\n";
//echo "member[id]=".$ibforums->member['id']."<br>\n";
//echo "member[g_is_supmod]=".$ibforums->member['g_is_supmod']."<br>\n";
//echo "vars[member_group]=".$ibforums->vars['member_group']."<br>\n";
//echo "vars[club_boss]=".$ibforums->vars['club_boss']."<br>\n";
//echo "vars[club_group]=".$ibforums->vars['club_group']."<br>\n";

	if ( !$ibforums->member['id'] or
	     !$mid or
	     !$ibforums->vars['club_boss'] or
	     !$ibforums->vars['club_group'] )
	{
		$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
	}

	if ( $ibforums->member['g_is_supmod'] or
	     $ibforums->member['id'] == $ibforums->vars['club_boss'] )
	{
		$DB->query("SELECT name,mgroup
			    FROM ibf_members WHERE id='".$mid."'");

		if ( $user = $DB->fetch_row() )
		{
			if ( $user['mgroup'] != $ibforums->vars['member_group'] )
			{
				$std->Error( array( LEVEL => 1,
						    MSG => 'no_permission') );
			}

			$DB->query("UPDATE ibf_members
				    SET
					mgroup='".$ibforums->vars['club_group']."',
					disable_group=0
				    WHERE id='".$mid."'");

			$message = "Уважаемый(ая), %s!\nМы имеем честь пригласить вас в закрытый клуб Sources.Ru ";

			$message .= "([URL=http://forum.sources.ru/index.php?c=9]Клуб на Исходниках.RU[/URL]). ";

			$message .= "Надеемся, что вы станете завсегдатаем этого ";

			$message .= "приятного во всех отношениях заведения, и мы не раз сможем услышать ваш голос в его ";

			$message .= "виртуальых стенах.\n\n С уважением, члены клуба Sources.Ru";

			$title = "Приглашение в Клуб на Исходниках.RU";

			$std->sendpm($mid, sprintf($message, $user['name']), $title, $ibforums->vars['club_boss']);

			$std->boink_it($ibforums->base_url."showuser=".$mid);
		} else
		{
			$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
		}
	} else
	{
		$std->Error( array( LEVEL => 1, MSG => 'no_permission') );
	}

	}


	function check_new() {
	global $ibforums, $DB;

	if ( !$ibforums->input['t'] ) $std->Error( array( LEVEL => 1, MSG => 'missing_files') );

	$DB->query("SELECT max(pid) as pid FROM ibf_posts WHERE queued != 1 AND topic_id='".$ibforums->input['t']."'");

	if ( !$post = $DB->fetch_row() or !$post['pid'] ) return "Error"; else 
	{
		$DB->query("SELECT Concat(posts,';',last_poster_name) as info FROM ibf_topics WHERE tid='".
				$ibforums->input['t']."'");

		if ( $info = $DB->fetch_row() ) $post['pid'] .= ";".$info['info'];

		return $post['pid'];
	}

	}

	function all_forums_order_recount() {
	global $DB, $std;

	$DB->query("TRUNCATE ibf_forums_order");

	$forums = array();

	$DB->query("SELECT id, parent_id FROM ibf_forums");
	
	while ( $row = $DB->fetch_row() )
	{
		$forums[ $row['id'] ] = $row['parent_id'];
	}

	foreach ( $forums as $id => $row ) $std->update_forum_order_cache($id,$row);

	return "Done!";

	}

	function clean_words(&$entry, &$stopword, &$synonym) {	

	static $drop_char_match   = array('&quot;','^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '-', '~', '+', '.', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!');
	static $drop_char_replace = array(' ',     ' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' , ' ', ' ', ' ', ' ',  ' ', ' ');

	$entry = " ".strip_tags($entry)." ";

	// Remove time tag
	$entry = preg_replace("#\[mergetime\](\d+)\[/mergetime\]#is", "", $entry);

	// Replace line endings by a space
	$entry = preg_replace("/[\n\r]/is", " ", $entry); 

	// Quickly remove BBcode.
	$entry = preg_replace("/\[\/?[a-zA-Z*]+[^\]]*\]/", " ", $entry);
	$entry = preg_replace("#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]]+)#is", " ", $entry);

	// HTML entities like &nbsp;
	$entry = preg_replace("/\b&[a-z]+;\b/", " ", $entry); 

	// Filter out strange characters like ^, $, &, change "it's" to "its"
	$entry =  str_replace($drop_char_match, $drop_char_replace, $entry);

	if ( !empty($stopword) )
	{
		for ($j = 0; $j < count($stopword); $j++)
		{
			$stopword = trim($stopword[ $j ]);

			if ( $stopword != "not" && $stopword != "and" && $stopword != "or" )
			{
				$entry = str_replace(" ".trim($stopword)." ", " ", $entry);
			}
		}
	}

	if ( !empty($synonym) )
	{
		for ($j = 0; $j < count($synonym); $j++)
		{
			list($replace_synonym, $match_synonym) = split(" ", trim($synonym[ $j ]));

			if ( $match_synonym != "not" && $match_synonym != "and" && $match_synonym != "or" )
			{
				$entry = str_replace(" ".trim($match_synonym)." ", " ".trim($replace_synonym)." ", $entry);
			}
		}
	}

	return $entry;

	}

	function split_words(&$entry) { return explode(" ", trim(preg_replace("#\s+#", " ", $entry))); }

	function analyze_post($post_text) {

	if ( !$post_text ) return;

	$stopword 	 = array();
	$synonym  	 = array();

	// loading stop and synonym words to its array

	$words = $this->split_words($this->clean_words($post_text, $stopword, $synonym));

	if ( count($words) )
	{
		sort($words);

		$temp_words = array();

		$prev_word = "";

		for ($i = 0; $i < count($words); $i++)
		{
			$words[ $i ] = trim($words[ $i ]);

			if ( $words[ $i ] != $prev_word ) $temp_words[] = $words[ $i ];

			$prev_word = $words[ $i ];
		}

		unset($words);

		for ($i = 0; $i < count($temp_words); $i++)
		{ 
			$length = strlen($temp_words[ $i ]);

			if ( $length > 2 and $length < 50 )
			{
				$words[] = $temp_words[ $i ];
			}
		}

		unset($temp_words);
	}

	return $words;

	}
	
	function index_data_posts( $fid = 0 ) {
	global $ibforums, $DB, $std;

	@set_time_limit(0);

	$fid = intval($fid);

	if ( !$fid or $ibforums->member['mgroup'] != $ibforums->vars['admin_group'] )
	{
		$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
	}

	$posts = $DB->query("SELECT pid, LOWER(post) as post, topic_id, forum_id FROM ibf_posts 
			     WHERE forum_id='".$fid."' and indexed=0");

	while ( $post = $DB->fetch_row($posts) )
	{
		// at first delete indexed earlier words
		$DB->query("DELETE FROM ibf_search_post_words WHERE pid='".$post['pid']."'");

		// get words array
		$words = $this->analyze_post($post['post']);

		// do safe queries
		$DB->return_die = 1;
		
		if ( count($words) ) foreach($words as $word)
		{		
			// insert word
			$DB->query("INSERT INTO ibf_search_words (word) VALUES ('".addslashes($word)."')");

			$id = 0;

			// if word has been in a database
			if ( $DB->error )
			{
				// get id of existing word
				$DB->query("SELECT id FROM ibf_search_words WHERE word='".addslashes($word)."'");

				if ( $row = $DB->fetch_row() ) $id = $row['id'];
			} else
			{
				// else get id of inserted word
				$id = $DB->get_insert_id();
			}

			// add word record
			if ( $id )
			{
				$DB->query("INSERT INTO ibf_search_post_words VALUES (".$post['pid'].",
					   ".$post['topic_id'].",".$post['forum_id'].",
					    ".$id.")");
			}
		}

		// return mode
		$DB->return_die = 0;

		$DB->query("UPDATE ibf_posts SET indexed=1 WHERE pid='".$post['pid']."'");
	}

	return "Done!";

	}

	function index_data_topics( $fid = 0 ) {
	global $ibforums, $DB, $std;

	@set_time_limit(0);

	$fid = intval($fid);

	if ( !$fid or $ibforums->member['mgroup'] != $ibforums->vars['admin_group'] )
	{
		$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
	}

	$topics = $DB->query("SELECT tid, forum_id, LOWER(title) as title, LOWER(description) as description 
			      FROM ibf_topics WHERE forum_id='".$fid."' and indexed=0");

	while ( $topic = $DB->fetch_row($topics) )
	{
		// at first delete indexed earlier words
		$DB->query("DELETE FROM ibf_search_post_words WHERE tid='".$topic['tid']."' and pid=0");

		// get words array
		$words       = $this->analyze_post($topic['title']);
		$description = $this->analyze_post($topic['description']);

		// join both arrays to one
		$words = array_merge($words, $description);
		unset($description);

		// do safe queries
		$DB->return_die = 1;
		
		if ( count($words) ) foreach($words as $word)
		{		
			// insert word
			$DB->query("INSERT INTO ibf_search_words (word) VALUES ('".addslashes($word)."')");

			$id = 0;

			// if word has been in a database
			if ( $DB->error )
			{
				// get id of existing word
				$DB->query("SELECT id FROM ibf_search_words WHERE word='".addslashes($word)."'");

				if ( $row = $DB->fetch_row() ) $id = $row['id'];
			} else
			{
				// else get id of inserted word
				$id = $DB->get_insert_id();
			}

			// add word record
			if ( $id )
			{
				$DB->query("INSERT INTO ibf_search_post_words VALUES (0,
					   ".$topic['tid'].",".$topic['forum_id'].",
					    ".$id.")");
			}
		}

		// return mode
		$DB->return_die = 0;

		$DB->query("UPDATE ibf_topics SET indexed=1 WHERE tid='".$topic['tid']."'");
	}

	return "Done!";

	}


}


?>