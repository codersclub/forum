<?php

/**
 * Класс для работы с таблицей ibf_post_edit_history
 *
 * @author a
 *
 */
class PostEditHistory
{

	const tablename = 'ibf_post_edit_history';

	static function addItem($post_id, $old_text)
	{
		global $ibforums;

		$fields = array(
			'post_id'     => $post_id,
			'old_text'    => $old_text,
			'edit_time'   => time(),
			'editor_id'   => $ibforums->member['id'],
			'editor_name' => $ibforums->member['name'],
		);

		$ibforums->db->insertRow(self::tablename, $fields);
	}

	/**
	 *
	 * Возвращает список записей об изменении поста, отсортированный по времени редактирования.
	 * Сначала последнее редактирование, в конце -- первое
	 *
	 * @param int $post_id
	 */
	static function getItems($post_id)
	{
		$ibforums = Ibf::instance();
		$result   = array();
		settype($post_id, 'integer');
		$stmt = $ibforums->db->query('SELECT * FROM ' . self::tablename . ' WHERE post_id = ' . $post_id . ' ORDER BY edit_time DESC');
		while ($row = $stmt->fetch())
		{
			$result[] = $row;
		}
		return $result;
	}

	/**
	 *
	 * Возвращает одну запись об изменении поста
	 *
	 * @param int $post_id
	 */
	static function getOneItem($post_id, $item_id)
	{
		$ibforums = Ibf::instance();
		$result   = array();
		settype($post_id, 'integer');
		settype($item_id, 'integer');
		$stmt = $ibforums->db->query('SELECT * FROM ' . self::tablename . " WHERE post_id = {$post_id} AND id = {$item_id} ");
		while ($row = $stmt->fetch())
		{
			return $row;
		}
		return NULL;
	}

}



