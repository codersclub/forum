<?php

/**
 * Класс для работы с таблицей ibf_post_edit_history
 *  
 * @author a
 *
 */
class PostEditHistory {
	
	const tablename = 'ibf_post_edit_history';
	
	static function addItem($post_id, $old_text) {
		global $DB, $ibforums;
		
		$fields = array(
				'post_id'	=> $post_id,
				'old_text'	=> $old_text,
				'edit_time'	=> time(),
				'editor_id'	=> $ibforums->member['id'],
				'editor_name'=>$ibforums->member['name'],
			);
			
		$DB->do_insert_query($fields, self::tablename);
	}
	
	/**
	 * 
	 * Возвращает список записей об изменении поста, отсортированный по времени редактирования.
	 * Сначала последнее редактирование, в конце -- первое
	 * 
	 * @param int $post_id
	 */
	static function getItems($post_id) {
		global $DB;
		$result = array();
		settype($post_id, 'integer');
		$DB->query('SELECT * FROM '.self::tablename.' WHERE post_id = '.$post_id.' ORDER BY edit_time DESC');
		while($row = $DB->fetch_row()) {
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
	static function getOneItem($post_id, $item_id) {
		global $DB;
		$result = array();
		settype($post_id, 'integer');
		settype($item_id, 'integer');
		$DB->query('SELECT * FROM '.self::tablename." WHERE post_id = {$post_id} AND id = {$item_id} ");
		while($row = $DB->fetch_row()) {
			return $row;
		}
		return NULL;
	}
	
}



