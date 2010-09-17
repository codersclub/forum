<?php


require_once dirname(__FILE__).'/mimecrutch.php';

if (!class_exists('Attach2')) {
class Attach2 {
	
	protected $attach_id;
	protected $type;
	protected $filename;
	protected $size;
	protected $tmp_name;
	protected $post_id;
	protected $hits = 0;
	
	protected $realFilename;
	
	/**
	 * old attach system compatibility
	 * @var array 
	 */
	protected $post_row;
	protected $from_post_row = false;
	
	public function setType($arg)
	{
		$this->type = $arg;
	}
	
	public function type()
	{
		return $this->type;
	}
	
	public function size()
	{
		return $this->size;
	}
	
	public function setSize($arg) {
		$this->size = $arg;
	}
	
	/**
	 * returns size of attachment as 
	 * human readable string
	 * @return string
	 */
	public function sizeAsString()
	{
		global $ibforums;
		
		if ($this->from_post_row) {
			return sprintf($ibforums->lang['attachment_size_kb'], $this->size);
		}
		$sizes_strings = array(
		'attachment_size_b',
		'attachment_size_kb',
		'attachment_size_mb',
		'attachment_size_gb',
		);
		$size = $this->size;
		$i = 0;
		for($i = 0; $i < 4; ++$i) {
			if ($size < 1024) {
				break;
			}
			$size /= 1024.0;
		}
		return sprintf($ibforums->lang[ $sizes_strings[$i] ], round($size, 2));
		
	}
	
	public function hits()
	{
		return (int)$this->hits;
	}
	public function setHits($arg) {
		$this->hits = $arg;
	}
	public function incHits() {
		$this->hits++;
		
		global $DB;
		if (!$this->attach_id) {
			return;
		}		
		$query = "UPDATE ibf_post_attachments SET `hits` = `hits` + 1 
			WHERE post_id={$this->post_id} and attach_id={$this->attach_id}";
		$qid = $DB->query($query);
		
	}
	
	public function filename()
	{
		return $this->filename;
	}
	public function setFilename($arg)
	{
		$this->filename = $arg;
	}
	
	public function setRealFilename($arg)
	{
		$this->realFilename = $arg;
	}
	
	public function realFilename()
	{
		return $this->realFilename;
	}
	
	public function postId()
	{
		return $this->post_id;
	}

	public function setPostId($arg)
	{
		$this->post_id = $arg;
	}
	
	public function attachId()
	{
		return $this->attach_id;
	}
	
	public function setAttachId($arg)
	{
		$this->attach_id = $arg;
	}
	
	public static function createFromPOST($field_name, $index)
	{
		if (!isset($_FILES[$field_name]['name'][$index])) {
			return NULL;
		}
		if ($_FILES[$field_name]['name'][$index] == '' || $_FILES['FILE_UPLOAD']['name'][$index] == 'none') {
			return NULL;
		}
		if ($_FILES[$field_name]['error'][$index] != UPLOAD_ERR_OK) {
			return NULL;
		}
		
		$a = new self;
		
		$a->type 	= $_FILES[$field_name]['type'][$index];
		$a->type	= preg_replace( '/^(.+?);.*$/', '$1', $a->type );
		
		$a->filename= trim($_FILES[$field_name]['name'][$index]);
		$a->size	= $_FILES[$field_name]['size'][$index];
		$a->tmp_name= $_FILES[$field_name]['tmp_name'][$index];
		
		return $a;
	}
	
	
	public function haveAllowedType()
	{
		return mime_type_is_allowed($this->type);
	}
	
	public function detectType()
	{
		return detect_mime_type($this->tmp_name);
	}
	
	public static function createExtensionByType($type)
	{
		$ext = '.ibf';
		switch($type)
		{
			case 'image/gif':
				$ext = '.gif';
				break;
			case 'image/jpeg':
				$ext = '.jpg';
				break;
			case 'image/pjpeg':
				$ext = '.jpg';
				break;
			case 'image/png':
			case 'image/x-png':
				$ext = '.png';
				break;
			default:
				$ext = '.ibf';
				break;
		}
		return $ext;
	}
	
	public function moveToUploadDirectory($dir)
	{
		(!$this->realFilename) && $this->realFilename = $this->filename;
		return move_uploaded_file($this->tmp_name, "$dir/{$this->realFilename}");
	}
	
	
	public function toArray()
	{
		return array (
			'type' => $this->type,
			'attach_id' => $this->attach_id,
			'post_id' => $this->post_id,
			'real_filename' => $this->realFilename,
			'filename' => $this->filename,
			'size' => $this->size,
		);
	}
	
	public static function isImageType($type)
	{
		$image_types = array (
				'image/gif',
				'image/jpeg',
				'image/pjpeg',
				'image/x-png',
				'image/png'
			);
		return in_array($type, $image_types);
	}
	
	protected static function createFromRow(array &$row)
	{
		$a = new self;
		
		$a->type	= $row['type'];
		$a->filename= $row['filename'];
		$a->size	= $row['size'];
		$a->tmp_name= $row['tmp_name'];
		$a->post_id	= $row['post_id'];
		$a->hits	= $row['hits'];
		
		$a->attach_id	= $row['attach_id'];
		$a->realFilename= $row['real_filename'];
		
		
		return $a;
	}
	
	public static function createFromPostRow(array &$row)
	{
		$array = array (
			'real_filename' => $row['attach_id'],
			'type' => $row['attach_type'],
			'filename' => $row['attach_file'],
			'size' => $row['attach_size'],
			'hits' => $row['attach_hits'],
			'post_id' => $row['pid'],
		);
		
		$result = self::createFromRow($array);
		
		$result->from_post_row = true;
		$result->post_row = &$row;
		
		return $result;
	}
	
	public static function getPostAttachmentsList($post_id, $attach_id = NULL)
	{
		global $DB;
		
		settype($post_id, 'integer');
		
		$query = "SELECT *
		    FROM ibf_post_attachments
		    WHERE post_id=$post_id";
		if ($attach_id !== NULL) {
			settype($attach_id, 'integer');
			$query .= "\nAND attach_id=$attach_id";
		}
		
		$qid = $DB->query($query);
		
		$result = array();
		while(($row = $DB->fetch_row($qid)) !== false) {
			$result[] = self::createFromRow($row);
		}
		
		return $result;
	}
	
	public static function getById($attach_id)
	{
		global $DB;
		settype($attach_id, 'integer');
		$query = "SELECT *
		    FROM ibf_post_attachments
		    WHERE attach_id=$attach_id";
		
		$qid = $DB->query($query);
		
		if($row = $DB->fetch_row($qid)) {
			return self::createFromRow($row);
		}
		
		return $false;
		
	}
	
	public function getImageOfType()
	{
		global $ibforums;
		require dirname(dirname(__FILE__)).'/conf_mime_types.php';
		
		return ( $ibforums->member['view_img'] )
			? 
				"<img src='{$ibforums->vars['mime_img']}/{$mime_types[ $this->type() ][1]}' border='0' alt='{$ibforums->lang['attached_file']}'>"
			: "";

	}
	
	public static function getPostAttachmentsFromRow(array &$row)
	{
		$result = array();
		if ($row['attach_exists']) {
			$result = self::getPostAttachmentsList($row['pid']);
		}
		if ($row['attach_id']) {
			$result[] = self::createFromPostRow($row);
		}
		return $result;
	}
	
	public static function reindexArray(array $array)
	{
		$result = array();
		foreach($array as $attach) {
			$result[(int)$attach->attachId()] = $attach;
		}
		return $result;
	}
	
	public function saveToDB()
	{
		global $DB;
		
		$array = $this->toArray();
		if (!$this->from_post_row) {
			
			if (!$this->attach_id) {
				unset($array['attach_id']); // because it is not exists yet, see below
				
				$db_string = $DB->compile_db_insert_string( $array );
				
				$DB->query("INSERT INTO ibf_post_attachments
						(" .$db_string['FIELD_NAMES']. ")
					    VALUES
						(". $db_string['FIELD_VALUES'] .")");
				
				$this->setAttachId($DB->get_insert_id());
			} else {
				$db_string = $DB->compile_db_update_string( $array );
				
				$DB->query("UPDATE ibf_post_attachments 
							SET $db_string
							WHERE attach_id = {$this->attach_id}");			
			}
		} else {
			$this->post_row['attach_id']	= $this->realFilename;
			$this->post_row['attach_type']	= $this->type;
			$this->post_row['attach_file']	= $this->filename;
			$this->post_row['attach_size']	= $this->size;
			$this->post_row['attach_hits']	= $this->hits;
		}
	}
	
	public function delteFromDB()
	{
		global $DB;
		
		if (!$this->from_post_row) {
			$DB->query("DELETE FROM `ibf_post_attachments`  
					WHERE `attach_id` = {$this->attach_id}");			
		} else {
			$this->post_row['attach_id']	= NULL;
			$this->post_row['attach_type']	= NULL;
			$this->post_row['attach_file']	= NULL;
			$this->post_row['attach_size']	= NULL;
			$this->post_row['attach_hits']	= NULL;
		} 
		
	}
	
	public function acceptAttach(self $a)
	{
		$this->filename	= $a->filename;
		$this->hits		= $a->hits;
		$this->realFilename = $a->realFilename;
		$this->size		= $a->size;
		$this->type		= $a->type;
		$this->tmp_name = $a->tmp_name;
		$this->post_id	= $a->post_id;
	}
	
	
	public static function deleteAllPostAttachments(array &$row)
	{
		global $ibforums;
		if ($attachments = Attach2::getPostAttachmentsFromRow($row)) {
			foreach($attachments as $attach) {
				$attach->delteFromDB();
				if (is_file($ibforums->vars['upload_dir']."/".$attach->realFilename())) {
					@unlink($ibforums->vars['upload_dir']."/".$row['attach_id']);
					
				}
			}
		}
	} 
	
}

}