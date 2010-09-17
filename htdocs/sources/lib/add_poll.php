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
|   > Add POLL module
|   > Module written by Matt Mecham
|
+--------------------------------------------------------------------------
|
|   QUOTE OF THE MODULE: (Taken from BtVS)
|   --------------------
|	Drusilla: I'm naming all the stars...
|   Spike: You can't see the stars love, That's the ceiling. Also, it's day.
|
+-------------------------------------------------------------------------- 
*/


$idx = new Poll;

class Poll {


	var $topic = array();
	var $poll  = array();
	var $upload = array();
	var $poll_count = 0;
	var $poll_choices = "";

	function Poll() {
	
		global $ibforums, $std, $DB, $print;
		
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);
		
		// Lets do some tests to make sure that we are allowed to start a new topic
		
		if ( !$ibforums->member['g_vote_polls'] )
		{
			$std->Error( array( LEVEL => 1, MSG => 'no_reply_polls') );
		}
		
		// Did we choose a choice?
		
		if ( !$ibforums->input['nullvote'] )
		{
			if ( !isset($ibforums->input['poll_vote']) )
			{
				$std->Error( array( LEVEL => 1, MSG => 'no_vote') );
			}
		}

		// Make sure we have a valid poll id
		
	       	$ibforums->input[t] = $std->is_number($ibforums->input[t]);

		if ( !$ibforums->input[t] ) 
		{
			$std->Error( array( LEVEL => 1, MSG => 'missing_files') );
		}
   
		// Song * multiple choices

   		// Load the topic and poll
		$DB->query("SELECT
				f.allow_pollbump,
				t.*,
				p.pid as poll_id,
				f.reply_perms,
				p.choices,
				p.starter_id,
				p.votes,
				p.state as poll_state,
		         	p.is_multi_poll,
				p.multi_poll_min,
				p.multi_poll_max, 
				p.is_weighted_poll,
				p.weighted_poll_places
			    FROM
				ibf_polls p,
				ibf_topics t,
				ibf_forums f
			    WHERE
				t.tid='".$ibforums->input['t']."'
				AND p.tid=t.tid and t.forum_id=f.id");

   		$this->topic = $DB->fetch_row();
   		
   		if ( !$this->topic['tid'] ) $std->Error( array(
						LEVEL => 1,
						MSG => 'poll_none_found') );

   		if ( $this->topic['state'] != 'open' or $this->topic['poll_state'] != 'open' ) 
		{
			$std->Error( array( LEVEL => 1,
					    MSG => 'locked_topic') );
		}

		if ( $std->check_perms($this->topic['reply_perms']) == FALSE )
		{
			$std->Error( array( LEVEL => 1,
					    MSG => 'no_permission') );
		}

		// Have we voted before?
		$DB->query("SELECT member_id
			    FROM ibf_voters
			    WHERE
				tid='".$this->topic['tid']."'
				AND member_id='".$ibforums->member['id']."'");
		
		if ( $DB->get_num_rows() ) $std->Error( array(
						LEVEL => 1,
						MSG => 'poll_you_voted') );

		// /Song * multiple choices
		

		if ( !$ibforums->input['nullvote'] )
		{
			if ( $this->topic['is_multi_poll'] ) 
			{
				$vote_count = 0;

				while (list($key, $value) = each($ibforums->input['poll_vote'])) 
				{ 
					if ( $value ) $vote_count++;
					$key = intval($key); //vot
					$ibforums->input['poll_vote'][$key] = $key; //vot
				} 

				if ( $vote_count < $this->topic['multi_poll_min'] ) 
				{
					$std->Error( array( LEVEL => 1,
							    MSG => 'poll_error_least',
							    EXTRA => $this->topic['multi_poll_min'] ));

				} elseif ( $vote_count > $this->topic['multi_poll_max'] ) 
				{
					$std->Error( array( LEVEL => 1,
							    MSG => 'poll_error_most',
							    EXTRA => $this->topic['multi_poll_max'] ));

				}
			 }

			if ( $this->topic['is_weighted_poll'] ) 
			{
				$vote_count = 0;

				while (list($key, $value) = each($ibforums->input['poll_vote'])) 
				{
					if ( $value ) $vote_count++; 
				}

				if ( $vote_count <> $this->topic['weighted_poll_places'] ) 
				{
					$std->Error( array(
							LEVEL => 1,
							MSG => 'poll_error_exactly',
							EXTRA => $this->topic['weighted_poll_places'] ));
				}
			}
		}

		// /Song * multiple choices


		// If we're here, lets add the vote
		$db_string = $std->compile_db_string(
			  array (
	  			'member_id'  => $ibforums->member['id'],
	  			'ip_address' => $ibforums->input['IP_ADDRESS'],
	  			'tid'        => $this->topic['tid'],
	  			'forum_id'   => $this->topic['forum_id'],
	  			'vote_date'  => time(),
				'votes'      => addslashes(serialize($ibforums->input['poll_vote'])),
				  		)
				  );
											  
		$DB->query("INSERT INTO ibf_voters
				(" .$db_string['FIELD_NAMES']. ")
			    VALUES (". $db_string['FIELD_VALUES'] .")");


		// If this isn't a null vote...
		   
		$i = 1;

		if ( !$ibforums->input['nullvote'] )
		{
			$poll_answers = unserialize(stripslashes($this->topic['choices']));
			reset($poll_answers);
                	$new_poll_array = array();

                	foreach ($poll_answers as $ind=>$entry)
                	{
//vot                		$id     = $entry[0];
                		$id     = $ind +1;
                		$choice = $entry[1];
                		$votes  = $entry[2];

				// Song * multiple choices
				if ( $this->topic['is_multi_poll'] )
				{
//vot					$id++;

//vot					if ( $id == $ibforums->input['poll_vote'][$i] && isset( $ibforums->input['poll_vote'][$i] ) ) 
					if ( isset( $ibforums->input['poll_vote'][$i] ) && $id == $ibforums->input['poll_vote'][$i] ) 
					{
						 $votes++;
					}

				} elseif ( $this->topic['is_weighted_poll']) 
				{ 
					if ($ibforums->input['poll_vote'][$i] != '') 
					{
						 $votes = $votes + (($this->topic['weighted_poll_places']+1) - $ibforums->input['poll_vote'][$i]);
					}
				} else 
				{
					if ( $id == $ibforums->input['poll_vote'] ) 
					{
						 $votes++;
					}
				}
                
				$i++;
				// /Song * multiple choices

	        		$new_poll_array[] = array( $id, $choice, $votes);
	        	}
        	
	        	$this->topic['choices'] = addslashes(serialize($new_poll_array));
        	
	        	$DB->query("UPDATE ibf_polls
				    SET
					votes=votes+1,
					choices='".$this->topic['choices']."' 
				    WHERE pid='".$this->topic['poll_id']."'");
        				 
	        	if ( $this->topic['allow_pollbump'] )
	        	{
				$this->topic['last_vote'] = time();
	        		$this->topic['last_post'] = time();

				$DB->query("UPDATE ibf_topics
					   SET
						last_vote='".$this->topic['last_vote']."',
						last_post='".$this->topic['last_post']."' 
					   WHERE tid='".$this->topic['tid']."'");
        				 	
	        	} else
	        	{
	        		$this->topic['last_vote'] = time();
        		
				$DB->query("UPDATE ibf_topics
					    SET
						last_vote='".$this->topic['last_vote']."',
						 last_post='".$this->topic['last_post']."' 
					    WHERE tid='".$this->topic['tid']."'");
	        	}
	        }

		$lang = $ibforums->input['nullvote'] ? $ibforums->lang['poll_viewing_results'] : $ibforums->lang['poll_vote_added'];
		
		$print->redirect_screen( $lang,"act=ST&f={$this->topic['forum_id']}&t={$this->topic['tid']}" );

	}

}

?>