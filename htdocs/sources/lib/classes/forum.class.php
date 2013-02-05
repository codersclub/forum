<?php

class forum
{

	public $id;

	public function update_last_topic_time()
	{

		$last_topic = Ibf::instance()->db->get_row("SELECT
				t.last_post,
				t.last_poster_id,
				t.last_poster_name,
				t.tid as topic_id,
				t.title
			    FROM ibf_topics t
			    WHERE t.forum_id='{$this->id}'
			    ORDER BY t.last_post DESC
			    LIMIT 1")->fetch();

		if ($last_topic)
		{
			Ibf::instance()->db->exec("UPDATE ibf_forums f
				    SET
						f.last_post		= '{$last_topic['last_post']}',
						f.last_poster_id= '{$last_topic['last_poster_id']}',
						f.last_poster_name='{$last_topic['last_poster_name']}',
						f.last_id		= '{$last_topic['topic_id']}',
						f.last_title	= '{$last_topic['title']}'

				    WHERE (f.id='{$this->id}')");
		}

	}

}
