<?php

require_once ROOT_PATH.'/sources/lib/classes/forum.class.php';

class topic {
	
	public $tid;
	public $forum_id;
	
	/**
	 * 
	 * требуемые поля: tid, forum_id
	 */
	public function update_last_post_time() {
		global $DB;
		
		$last_post = $DB->get_row("SELECT
				post_date,
				author_id,
				author_name
			    FROM ibf_posts 
			    WHERE topic_id='{$this->tid}'
				AND queued != 1 AND use_sig = 0
			    ORDER BY pid DESC
			    LIMIT 1");
		
		if ($last_post) {
			// если вообще есть не отклонённые ответы...
			$DB->query("UPDATE ibf_topics t 
				    SET
						t.last_post='".$last_post['post_date']."',
						t.last_poster_id='"   .$last_post['author_id']."',
						t.last_poster_name='" .$last_post['author_name']."'
						
				    WHERE (t.tid='{$this->tid}' OR mirrored_topic_id='{$this->tid}')");
		}
		
		// обновить все зеркала
		$topics = $this->get_mirrors();
		array_push($topics, $this);
		foreach ($topics as $t) {
			$forum = new forum;
			$forum->id = $t->forum_id;
			$forum->update_last_topic_time();
		}
		
	}
	
	public function accept_array(array $a) {
		foreach ($a as $name => $value) {
			$this->$name = $value;
		}
	}
	
	public static function create_from_array(array $a) {
		$o = new self;
		$o->accept_array($a);
		return $o;
	}
	
	/**
	 * 
	 * returns mirror-topics of this topic
	 * @return topic[]
	 */
	public function get_mirrors() {
		global $DB;
		
		$result = array();
		$q = $DB->query("SELECT * FROM ibf_topics WHERE mirrored_topic_id='{$this->tid}'");
		while (($o = $DB->fetch_object($q, 'topic')) != false) {
			$result[] = $o;
		}
		$DB->free_result($q);
		return $result;
		
	}
}
