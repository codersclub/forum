<?php

$fav = new fav;

class fav {

    var $max_fav = 2000;
    var $output = "";
    var $html = "";
    var $base_url = "";
    var $nav = "";
                 
    function fav() {
    global $ibforums, $print, $std;
                             
	if ( $ibforums->input['show'] or $ibforums->input['topic'] ) 
	{
	        $this->base_url = $ibforums->base_url;

	    	$this->html = $std->load_template('skin_fav');

		$this->nav[] = $ibforums->lang['favorites']; //vot
//			$this->nav[] = "Избранное";

	        if ( !$ibforums->member['id'] ) 
		{
			$std->Error( array( 'LEVEL' => 1, 'MSG' => 'fav_guest') );
		}

		$refer = $_SERVER['HTTP_REFERER'];

	        if ( !preg_match("#".$ibforums->base_url."\?#",$refer) ) $refer = "";

	        $refer = preg_replace("#".$ibforums->base_url."\?#","",$refer);

		if ( $ibforums->input['topic'] ) 
		{
			$this->add_topic(intval($ibforums->input['topic']), $state);

		        if ( $ibforums->input['track'] and $count and $delete )
			{
				$refer = "act=Track&f={$row['forum_id']}&t={$topic}";
			}

			if ( $ibforums->input['js'] and $ibforums->input['linkID'] )
			{
				$print->redirect_js_screen("{$ibforums->input['linkID']}", ( ( $state ) ? "fav1" : "fav2"), $ibforums->base_url."act=fav&topic={$ibforums->input['topic']}&js=1");
			} else
			{
				$print->redirect_screen("", $refer);
			}

		} else $this->show_favs();
	}

    }//End function fav()



    function add_topic($topic = 0, &$delete = 0) {
    global $ibforums, $DB, $std;

	if ( !$topic ) return;

//    	$favlist = explode(",", $ibforums->member['favorites']);
//    	$favlist = $std->get_favorites();
	$favlist = $ibforums->member['favorites'];

	$DB->query("SELECT forum_id FROM ibf_topics WHERE tid='".$topic."'");
	$count = $DB->get_num_rows();
	if ( $count ) $row = $DB->fetch_row();


	$delete = in_array($topic, $favlist);


	if ( $count or ( !$count and $delete ) ) 
	{
//            	$favlist = explode(",", $ibforums->member['favorites']);

		if ( !$delete )
		{
//	            	if ( !is_numeric($favlist[0]) ) 
//			{
//				$favlist[0] = 0;
//
//				$favlist = array_slice($favlist,1);
//			}

			if ( count($favlist) > $this->max_fav ) 
			{
				$std->Error( array( LEVEL => 1, MSG => 'too_many_favs', EXTRA => $this->max_fav) );
			} else
			{
//				$favlist[] = $topic;
                                $mid = $ibforums->member['id'];
                                $DB->query("INSERT INTO ibf_favorites
                                            (mid,tid) VALUES
                                            ('$mid','$topic')");

			}

//			$favlist = implode(",", $favlist);
		} else 
		{
//			$prefix = ( $favlist[0] != $topic ) ? "," : "";

//			$favlist = implode(",", $favlist);

//			$favlist = str_replace($prefix.$topic, "", $favlist);

//			if ( substr($favlist,0,1) == "," )
//			{
//				$favlist = substr($favlist,1,strlen($favlist));
//			}
                                $mid = $ibforums->member['id'];
                                $DB->query("DELETE FROM ibf_favorites
                                            WHERE
                                             mid='$mid' AND tid='$topic'");
		}
		
//            	$DB->query("UPDATE ibf_members
//                            SET favorites='".$favlist."'
//                            WHERE id='".$ibforums->member['id']."'");
	} else 
	{
		$std->Error( array( LEVEL => 1, MSG => 'mt_no_topic') );
	}
    }	



    function show_favs() {
    global $ibforums, $print, $std, $DB;

	$std->update_favorites();

        $query = "SELECT f.tid, t.tid AS topic_id, t.title, t.starter_id,
                           t.last_poster_id, t.last_post,
			   t.starter_name, t.last_poster_name,
                           tr.logTime
                    FROM ibf_favorites f
	    	    LEFT JOIN ibf_topics t
	    	         ON f.tid=t.tid
                    LEFT JOIN ibf_log_topics tr 
	    	         ON (f.mid=tr.mid AND f.tid=tr.tid)
		    WHERE
			  f.mid='".$ibforums->member['id']."'
		    GROUP BY f.tid
                    ORDER BY t.last_post DESC";
        $DB->query($query);
        $count = $DB->get_num_rows();

        if ( !$count) 
	{ 
                $ibforums->lang = $std->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);

	        $e = $ibforums->lang['fav_nolinks'];

	        $this->output .= $this->html->error($e);

        } else 
	{
        	while( $topic = $DB->fetch_row() ) {

//$this->output .= "tid=".$topic['tid']." topic_id=".intval($topic['topic_id'])."<br>";

			$last_time = $topic['logTime'];

 		    if ( intval($topic['topic_id'])) {
			if ( $last_time && ($topic['last_post'] > $last_time) ) {
                          $new[] = $topic;
                        } else {
                          $nonew[] = $topic;
                        }
                    } else {
                          $remove[] = $topic;
                    }
        	}

		if ( isset($new) ) {
	            	foreach($new as $topic) {
	                	$topic['last_post'] = $std->get_date($topic['last_post']);
	                	$html['new'] .= $this->html->topic_row($topic);
	                }

		} else $html['new'] = $this->html->none();

		if ( isset($nonew) ) {
	            	foreach($nonew as $topic) {
				$topic['last_post'] = $std->get_date($topic['last_post']);
				$html['nonew'] .= $this->html->topic_row($topic);
			}

		} else $html['nonew'] = $this->html->none();

		//-------------------------------------
		// Remove Deleted Topics from Favorites

		if(isset($remove)) {
		  $r = array();
  	          foreach($remove as $topic) {
  			$r[] = $topic['tid'];
  		  }
  		  if ( count($r) ) {
  		    $r = implode(",",$r);
                    $DB->query("DELETE FROM ibf_favorites
                                WHERE
                                tid IN($r)");
  		  }
  		}

		$this->output .= $this->html->main($html);
	}

        $print->add_output($this->output);

    	$print->do_output(array( 'TITLE' => $ibforums->vars['board_name'].$cp, 'JS' => 0, 'NAV' => $this->nav) );

    } //End of function show_favs()

}//End class fav

