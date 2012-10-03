<? 

require "sources/GetPosts.php";
require "client_timer.php";

$Log = new Log();
$Timer = new PerfTimer();

class IPBGetPosts extends GetPosts {

var $Topics;
var $Forums;
var $parser;

	function IPBGetPosts() {

	global $GetThread, $DB, $std, $ibforums, $B_Main;
		require("../sources/lib/post_parser.php");
		$B_Main = intval($B_Main);
		$this->parser = new post_parser();
		$this->parser->prepareIcons();

		
		if(!$GetThread) {

			$this->TimeSettings();
		}
		$this->FetchPosts();
	}

	function FetchPostsPortion($from, $count) {

	global $DB, $Boards, $Log, $ibforums, $GetThread, $B_Main, $Timer, $greater;

		// Go to sleep, if needed
		// At first time we will sleep only a bit,
		// but on another loop this time will be increased
		$Timer->sleep();
		$greater = intval($greater);

		$query = "SELECT
				forum_id,
				pid,
				topic_id,
				author_name,
				author_id,
				post,
				use_emo,
				use_sig,
				post_date,
				icon_id,
				attach_file,
				new_topic,
				edit_time,
				edit_name,
				append_edit
			FROM
				ibf_posts";

		if($GetThread) {

			$query .= " WHERE
					topic_id='" . intval($B_Main) . "'";
		} else if(!$ibforums->vars['client_force_load_all_db']) {

			$query .= " WHERE edit_time >= " . $this->timeMin . "
				AND edit_time < " . $this->timeMax . " AND";
		} else if(!empty($Boards) && $Boards != "" ) {

			$query .= " WHERE";
		}

		if(!$GetThread ) {

			$Boards = str_replace( array("(", ")", "{", "}", "\\'"), array("", "", "", "", ""), $Boards);
			$Boards = explode(",", $Boards);
			$BoardsArr = array();
			foreach ( $Boards as $idx => $board) {
				$val = intval($board);
				if($val) $BoardsArr[] = $val;
			}

			if(!count($BoardsArr)) {
				
				echo "MESSAGE: Вы не выбрали разделы для считывания с форума. Это можно сделать, вызвав диалог: Меню->Сервис->Настройка...";
				exit();
			} else {
				$Boards = implode(",", $BoardsArr);
				$query .= " forum_id in (" . $Boards . ")";
			}
       		}

		$query .= " ORDER BY post_date ASC LIMIT $from, $count";

		$qid = $DB->query($query);

		$mcount = $DB->get_num_rows($qid);
		$continue = ($count == $mcount);

		$Log->addMsgsCount($mcount);

		while( $row = $DB->fetch_row($qid) ) {

			if(	$row['pid'] == $greater &&
				$row['edit_time'] == $this->timeMin) {

				continue;
			}

			$this->AddTopic($row);
			$perm = $this->CheckPermissions($row);
			if($perm) {

				$this->ProcessPost($row);
			}
		}

		return $continue;
	}

	function CheckPermissions($row) {

	global $DB, $std, $ibforums;

		$fid = intval($row['forum_id']);
		$tid = intval($row['topic_id']);

		if($std->check_perms( $ibforums->member['club_perms'] ) == FALSE ) {

			if($this->Topics[$tid]['club']) {

				return 0;
			}
		}

		if(empty($this->Forums[$fid])) {

			$query = "
				SELECT
					read_perms,
					use_html,
					forum_highlight,
					highlight_fid,
					parent_id,
					password
				FROM
					ibf_forums
				WHERE
					id=" . $fid;

			$DB->query($query);
			$ar = $DB->fetch_row();
			$this->Forums[$fid] = $ar;

			if(!$this->Forums[$fid]['password'])
			{
			if($this->Forums[$fid])
				$this->Forums[$fid]['perm'] = $std->check_perms($ar['read_perms']);
			}
			else
			{
				$this->Forums[$fid] = array();
			}

			if(!$ar['highlight_fid'])
				$this->Forums[$fid]['highlight_fid'] = -1;

			if ( $ar['forum_highlight'] and $ar['highlight_fid'] == -1 and $ar['parent_id'] != -1 )
			{
				$DB->query("
					SELECT
						forum_highlight,
						highlight_fid
					FROM
						ibf_forums
					WHERE
						id='".$ar['parent_id']."'
					");
				$ar=$DB->fetch_row();

			}
			if($ar['forum_highlight'] and $ar['highlight_fid'] != -1)
				$this->Forums[$fid]['highlight_fid'] = $ar['highlight_fid'];
		}
		return $this->Forums[$fid]['perm'];
	}


	function AddTopic(&$row) {

	global $DB;

		$tid = intval($row['topic_id']);

		if(empty($this->Topics[$tid])) {

			if($row['new_topic']) {

				$query = "
					SELECT
						title,
						description,
						club
					FROM
						ibf_topics
					WHERE
						tid = $tid";

				$DB->query($query);

				$this->Topics[$tid] = $DB->fetch_row();
				$this->Topics[$tid]['pid'] = $row['pid'];
			} else {

				$query = "
					SELECT
						t.title,
						t.description,
						t.club,
						p.pid
					FROM
						ibf_topics t,
						ibf_posts p
					WHERE
						t.tid = $tid AND
						p.topic_id = t.tid AND
						p.new_topic = 1";

				$DB->query($query);

				$this->Topics[$tid] = $DB->fetch_row();
			}
		}

		$row['title'] = $this->Topics[$tid]['title'];

		if($row['new_topic']) {

			// Add description to the top of message
			$row['post'] = "<b>" . $this->Topics[$tid]['description']. "</b><hr>" . $row['post'];
		}
	}


	function ProcessPost($row) {

	global $Timer, $ibforums, $std;

		$post_out = "";

		if($row['new_topic'] && $this->Topics[$row['topic_id']]['club']) {

			$post_out .= "<p><small><i>Топик с ограниченной видимостью</i></small></p>";
		}

		if(0 == $row['use_sig'])
		{
				$row['post'] = $this->parser->prepare(
	
				array(
						'TEXT'          => $row['post'],
						'SMILIES'       => $row['use_emo'],
						'CODE'          => 1,
						'SIGNATURE'     => 0,
						'HTML'          => 1,
						'HID'		=> $this->Forums[$row['forum_id']]['highlight_fid'],
						'TID'		=> $row['topic_id']
				     )
									);

				if ( !trim($row['post']) ) return;

				// process DOHTML tag
				$row['post'] = $this->parser->post_db_parse($row['post'], $this->Forums[$row['forum_id']]['use_html']);
			
				//--------------------------------------------------------------
				// Do word wrap?
				//--------------------------------------------------------------
			
				if ( $ibforums->vars['post_wordwrap'] > 0 ) {

					$row['post'] = $this->parser->my_wordwrap( $row['post'], $ibforums->vars['post_wordwrap']) ;
				}
			
				$post_out = str_replace( "<br>", "<br />", $row['post'] );

			if (!empty($row['attach_file'])) {

				// Add attachment
				$post_out .=
					  "<br><br><b>Attached file:</b> <a href='"
					. $ibforums->vars['board_url']
					. "?act=Attach&amp;type=post&amp;id="
					. $row['pid']
					. "' title='Download' target='_blank'>"
					. $row['attach_file']
					. "</a>";
			}
		}
		else
		{
			$post_out .= "<span style='color:red'><b>Сообщение удалено модератором.</b></span>";
		}

		$etime = "";
		if($row['edit_time'] > $row['post_date']) {
			$etime = $std->get_date($row['edit_time']);
			$row['title'] .= " - (Ред. " . $etime . ")";
		}

		if($row['append_edit'] == 1 && !empty($row['edit_name']) && !empty($row['edit_time'])) {
			$post_out .=
				"<br><br><span class='edit'>Сообщение отредактировано: "
				."<b>" . $row['edit_name'] . ", ". $etime . "</span>";
		}

		// Prepare post array
		$this->Posts[$row['pid']] = array(
			'FORUM'    => $row['forum_id'],
			'TOPIC1'   => $this->Topics[$row['topic_id']]['pid'],
			'TOPIC2'   => $row['topic_id'],
			'NAME'     => $row['author_name'],
			'AUTID'    => $row['author_id'],
			'TITLE'    => $row['title'],
			'POST'     => $post_out,
			'DATE'     => $row['post_date'],
			'ICON'     => $row['icon_id']
		);

	}

	function FetchPosts() {

	global $Log, $Boards, $period, $Timer, $GetThread, $B_Main;

		// Fill some of log fields
		if($GetThread) {

			$Log->addInterval($B_Main, 0, "get thread");
		} else {

			$Log->addInterval($this->timeMin, $this->timeMax, $period);
		}
		$Log->addForumList($Boards);

		$from = 0;
		$count = 30;

		$portionTime = $Timer->endTimer();

		while($this->FetchPostsPortion($from,$count)) {

			$from += $count;
			$nTime = $Timer->endTimer();

			// Portions uploading each 0.5 seconds
			if(0.5 <= $nTime - $portionTime) {

				$this->Process();
				$portionTime = $Timer->endTimer();
			}
		}

		// Upload the rest
		$this->Process();

		// And do logging
		$Log->doLog();
	}
}


