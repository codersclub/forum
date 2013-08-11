<?php

class TopicDraft
{

	const table_name = 'ibf_topic_draft';

	private $id;
	private $text;
	private $created;
	private $topic_id;
	private $topic_title;
	private $forum_id;
	private $topic_description;

	function topic_id()
	{
		return $this->topic_id;
	}

	function id()
	{
		return $this->id;
	}

	function text()
	{
		return $this->text;
	}

	function setText($arg)
	{
		$this->text = $arg;
	}

	function topic_title()
	{
		return $this->topic_title;
	}

	function forum_id()
	{
		return $this->forum_id;
	}

	function topic_description()
	{
		return $this->topic_description;
	}

	static function createDraft($topic_id, $text)
	{

		if ($o = self::getDraft($topic_id))
		{
			$o->text = $text;
			$o->save();
			return $o;
		}

		$o           = new self();
		$o->text     = $text;
		$o->topic_id = $topic_id;

		$o->save();

		return $o;

	}

	static function createNewTopicDraft($forum_id, $title, $description, $text)
	{

		if ($o = self::getNewTopicDraft($forum_id))
		{
			$o->text = $text;
			$o->save();
			return $o;
		}

		$o                    = new self();
		$o->text              = $text;
		$o->topic_title       = $title;
		$o->forum_id          = $forum_id;
		$o->topic_description = $description;
		$o->save();

		return $o;

	}

	function save()
	{
		global $ibforums;

		$fields = array(
			'text'      => $this->text,
			'created'   => time(),
			'member_id' => $ibforums->member['id']
		);
		$this->id && $fields['id'] = $this->id;
		$this->topic_id && $fields['topic_id'] = $this->topic_id;
		$this->topic_title && $fields['topic_title'] = $this->topic_title;
		$this->forum_id && $fields['forum_id'] = $this->forum_id;
		$this->topic_description && $fields['topic_description'] = $this->topic_description;

		$ibforums->db->replaceRow(self::table_name, $fields);
		$this->id = $this->id
			? : $ibforums->db->lastInsertId();

	}

	function delete()
	{
		$ibforums = Ibf::app();
		$query    = 'DELETE FROM ' . self::table_name . ' WHERE id = ' . $ibforums->db->quote($this->id);
		$ibforums->db->query($query);
	}

	static function getDraft($topic_id)
	{
		global $ibforums;
		$array = $ibforums->db
			->query('SELECT * FROM ' . self::table_name . ' WHERE topic_id = ' . $ibforums->db->quote($topic_id) . ' AND member_id = ' . $ibforums->db->quote($ibforums->member['id']))
			->fetch();
		if (!$array)
		{
			return NULL;
		}
		$o = new self;

		$o->id       = $array['id'];
		$o->text     = $array['text'];
		$o->created  = $array['created'];
		$o->topic_id = $array['topic_id'];

		$o->topic_title       = $array['topic_title'];
		$o->forum_id          = $array['forum_id'];
		$o->topic_description = $array['topic_description'];

		return $o;
	}

	/**
	 * Возвращает черновик нового топика (не поста), если таковой уже есть в базе
	 *
	 * @param int $forum_id
	 * @return NULL|TopicDraft
	 */
	static function getNewTopicDraft($forum_id)
	{
		global $ibforums;
		$array = $ibforums->db
			->query('SELECT * FROM ' . self::table_name . ' WHERE forum_id = ' . $ibforums->db->quote($forum_id) . ' AND member_id = ' . $ibforums->db->quote($ibforums->member['id']))
			->fetch();
		if (!$array)
		{
			return NULL;
		}
		$o = new self;

		$o->id       = $array['id'];
		$o->text     = $array['text'];
		$o->created  = $array['created'];
		$o->topic_id = $array['topic_id'];

		$o->topic_title       = $array['topic_title'];
		$o->forum_id          = $array['forum_id'];
		$o->topic_description = $array['topic_description'];

		return $o;
	}

	static function draftExists($topic_id)
	{
		global $ibforums;
		$id = $ibforums->db
			->query('SELECT id FROM ' . self::table_name . ' WHERE topic_id = ' . $ibforums->db->quote($topic_id) . ' AND member_id = ' . $ibforums->db->quote($ibforums->member['id'] . ' LIMIT 1'))
			->fetchColumn();
		return (bool)$id;
	}

	function getAttachments()
	{
		return Attachment::getTopicDraftAttachmentsList($this->id);
	}

}
