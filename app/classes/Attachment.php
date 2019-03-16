<?php


require_once public_path('sources') . '/mimecrutch.php';

if (!class_exists('Attachment')) {
    class Attachment
    {

        const ITEM_TYPE_POST        = 'post';
        const ITEM_TYPE_TOPIC_DRAFT = 'topic_draft';

        protected $attach_id;
        protected $type;
        protected $filename;
        protected $size;
        protected $tmp_name;
        protected $post_id;
        protected $hits = 0;

        private $item_id;
        private $item_type = self::ITEM_TYPE_POST;
        /**
         * Additional options
         * @var array
         */
        private $options = [];

        protected $realFilename;

        static $error_types = array(
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
        );
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

        public function setSize($arg)
        {
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

            $out_size = $this->size;
            $i        = 0;
            while (($i < 4) && ($out_size >= 1024)) {
                $i++;
                $out_size /= 1024.0;
            }

            return (sprintf($ibforums->lang[$sizes_strings[$i]], round($out_size, 2)));
        }

        public function hits()
        {
            return (int)$this->hits;
        }

        public function setHits($arg)
        {
            $this->hits = $arg;
        }

        public function incHits()
        {
            $this->hits++;

            $ibforums = Ibf::app();
            if (!$this->attach_id) {
                return;
            }
            $query = "UPDATE ibf_post_attachments SET `hits` = `hits` + 1
			WHERE attach_id={$this->attach_id}";
            $ibforums->db->query($query);
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

        public function itemId()
        {
            return $this->item_id;
        }

        public function itemType()
        {
            return $this->item_type;
        }

        public static function createFromPOST($field_name, $index, &$error_message = -1)
        {
            if (!isset($_FILES[$field_name]['name'][$index])) {
                return null;
            }
            if ($_FILES[$field_name]['name'][$index] == '' || $_FILES['FILE_UPLOAD']['name'][$index] == 'none') {
                return null;
            }
            if ($_FILES[$field_name]['error'][$index] != UPLOAD_ERR_OK) {
                if ($error_message !== -1) {
                    $error_message = self::$error_types[$_FILES[$field_name]['error'][$index]];
                }
                return null;
            }

            $a = new self();

            $a->type = $_FILES[$field_name]['type'][$index];
            $a->type = preg_replace('/^(.+?);.*$/', '$1', $a->type);

            $a->filename = trim($_FILES[$field_name]['name'][$index]);
            $a->size     = $_FILES[$field_name]['size'][$index];
            $a->tmp_name = $_FILES[$field_name]['tmp_name'][$index];

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
            switch ($type) {
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
            return array(
                'type'          => $this->type,
                'attach_id'     => $this->attach_id,
                'post_id'       => $this->post_id,
                'real_filename' => $this->realFilename,
                'filename'      => $this->filename,
                'size'          => $this->size,
            );
        }

        public static function isImageType($type)
        {
            $image_types = array(
                'image/gif',
                'image/jpeg',
                'image/pjpeg',
                'image/x-png',
                'image/png'
            );
            return in_array($type, $image_types);
        }

        public static function createFromRow(array &$row)
        {
            if (self::isImageType($row['type'])) {
                //todo move to Factory class
                $a = new AttachImage();
            } else {
                $a = new self();
            }

            $a->type     = isset($row['type'])
                ? $row['type']
                : "";
            $a->filename = isset($row['filename'])
                ? $row['filename']
                : "";
            $a->size     = isset($row['size'])
                ? $row['size']
                : 0;
            $a->tmp_name = isset($row['tmp_name'])
                ? $row['tmp_name']
                : "";
            $a->post_id  = isset($row['post_id'])
                ? $row['post_id']
                : 0;
            $a->hits     = isset($row['hits'])
                ? $row['hits']
                : 0;

            $a->attach_id    = isset($row['attach_id'])
                ? $row['attach_id']
                : 0;
            $a->realFilename = isset($row['real_filename'])
                ? $row['real_filename']
                : "";

            $a->item_id   = $row['item_id']
                ? : $a->post_id; // bakward compatibility
            $a->item_type = $row['item_type']
                ? : self::ITEM_TYPE_POST;

            return $a;
        }

        public static function createFromPostRow(array &$row)
        {
            $array = array(
                'real_filename' => $row['attach_id'],
                'type'          => $row['attach_type'],
                'filename'      => $row['attach_file'],
                'size'          => $row['attach_size'],
                'hits'          => $row['attach_hits'],
                'post_id'       => $row['pid'],
            );

            $result = self::createFromRow($array);

            $result->from_post_row = true;
            $result->post_row      = & $row;

            return $result;
        }

        private static function getAttachmentsList($id, $attach_type = self::ITEM_TYPE_POST, $attach_id = null)
        {

            $ibforums = Ibf::app();

            settype($id, 'integer');

            $query = "SELECT *
		    FROM ibf_post_attachments a
			INNER JOIN ibf_attachments_link al USING (attach_id)
		    WHERE al.item_id=$id AND al.item_type = " . $ibforums->db->quote($attach_type);

            if ($attach_id !== null) {
                settype($attach_id, 'integer');
                $query .= " AND attach_id=$attach_id";
            }

            $stmt = $ibforums->db->query($query);

            $result = array();
            while (($row = $stmt->fetch()) !== false) {
                $result[] = self::createFromRow($row);
            }

            $stmt->closeCursor();

            return $result;
        }

        public static function getPostAttachmentsList($post_id, $attach_id = null)
        {
            return self::getAttachmentsList($post_id, self::ITEM_TYPE_POST, $attach_id);
        }

        public static function getTopicDraftAttachmentsList($draft_id)
        {
            return self::getAttachmentsList($draft_id, self::ITEM_TYPE_TOPIC_DRAFT);
        }

        public static function getById($attach_id)
        {
            $ibforums = Ibf::app();
            settype($attach_id, 'integer');
            $query = "SELECT *
		    FROM ibf_post_attachments
		    WHERE attach_id=$attach_id";

            if ($row = $ibforums->db->query($query)->fetch()) {
                return self::createFromRow($row);
            }

            return false;
        }

        public function getImageOfType()
        {
            global $ibforums;
            require public_path() . '/conf_mime_types.php';

            return ($ibforums->member['view_img'])
                ? "<img src='{$ibforums->vars['mime_img']}/{$mime_types[ $this->type() ][1]}' border='0' alt='{$ibforums->lang['attached_file']}'>"
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
            foreach ($array as $attach) {
                $result[(int)$attach->attachId()] = $attach;
            }
            return $result;
        }

        public function saveToDB($save_to = 'post')
        {
            $ibforums = Ibf::app();

            $array = $this->toArray();
            if (!$this->from_post_row) {
                if (!$this->attach_id) {
                    unset($array['attach_id']); // because it is not exists yet, see below

                    if ($save_to != 'post') {
                        unset($array['post_id']);
                    }

                    $ibforums->db->insertRow("ibf_post_attachments", $array);

                    $this->setAttachId($ibforums->db->lastInsertId());

                    if ($this->post_id || $save_to != 'post') { // post - default value, for backward compatibility
                        $array = array(
                            'attach_id' => $this->attach_id,
                            'item_type' => $save_to /*'post'*/,
                            'item_id'   => $this->post_id
                        );

                        $ibforums->db->replaceRow('ibf_attachments_link', $array);
                    }
                } else {
                    $ibforums->db->updateRow('ibf_post_attachments', array_map([
                                                                               $ibforums->db,
                                                                               'quote'
                                                                               ], $array), "attach_id = {$this->attach_id}");
                }
            } else {
                $this->post_row['attach_id']   = $this->realFilename;
                $this->post_row['attach_type'] = $this->type;
                $this->post_row['attach_file'] = $this->filename;
                $this->post_row['attach_size'] = $this->size;
                $this->post_row['attach_hits'] = $this->hits;
            }
        }

        public function delteFromDB()
        {
            // TODO: сделать удаление только нужного объекта, а из ibf_attachments_link удалять только если не осталось ссылок в ibf_attachments_link
            $ibforums = Ibf::app();

            if (!$this->from_post_row) {
                $ibforums->db->exec("DELETE FROM `ibf_post_attachments` WHERE `attach_id` = {$this->attach_id}");
                $ibforums->db->exec("DELETE FROM `ibf_attachments_link` WHERE `attach_id` = {$this->attach_id}");
            } else {
                $this->post_row['attach_id']   = null;
                $this->post_row['attach_type'] = null;
                $this->post_row['attach_file'] = null;
                $this->post_row['attach_size'] = null;
                $this->post_row['attach_hits'] = null;
            }
        }

        public function isImage()
        {
            return false;
        }

        public function getHref()
        {
            global $ibforums;
            return ($ibforums->base_url) . "act=Attach&amp;type=" . $this->itemType() . "&amp;id=" . ($this->postId()) . "&amp;attach_id=" . ($this->attachId());
        }

        public function acceptAttach(self $a)
        {
            $this->filename     = $a->filename;
            $this->hits         = $a->hits;
            $this->realFilename = $a->realFilename;
            $this->size         = $a->size;
            $this->type         = $a->type;
            $this->tmp_name     = $a->tmp_name;
            $this->post_id      = $a->post_id;
        }

        public static function deleteAllPostAttachments(array &$row)
        {
            global $ibforums;
            if ($attachments = Attachment::getPostAttachmentsFromRow($row)) {
                foreach ($attachments as $attach) {
                    $attach->delteFromDB();
                    if (is_file($ibforums->vars['upload_dir'] . "/" . $attach->realFilename())) {
                        @unlink($ibforums->vars['upload_dir'] . "/" . $row['attach_id']);
                    }
                }
            }
        }

        function moveTo($id, $item_type)
        {
            $id        = intval($id);
            Ibf::app()->db->prepare("UPDATE ibf_attachments_link
			SET item_type = :new_type, item_id = :new_id
			WHERE attach_id = :attach
				AND item_type = :type
				AND item_id = :id")
            ->bindParam(':new_type', $item_type, PDO::PARAM_STR)
            ->bindParam(':new_id', $id, PDO::PARAM_INT)
            ->bindParam(':attach', $this->attach_id, PDO::PARAM_INT)
            ->bindParam(':type', $this->item_type, PDO::PARAM_STR)
            ->bindParam(':id', $this->item_id, PDO::PARAM_INT)
            ->execute();

            $this->item_id   = $id;
            $this->item_type = $item_type;
        }

        public function getLink()
        {
            global $ibforums;
            return $this->getImageOfType() . "<a class='b-attach-link' href='{$this->getHref()}' title='Скачать файл' target='_blank'>" . ($this->filename()) . "</a>";
        }

        public function accessIsAllowed($member)
        {
            global $ibforums;

            $result = false;

            if ($this->from_post_row) {
                if ($this->checkPostAccess($this->item_id, $member)) {
                    return true;
                }
            } else {
                $stmt = $ibforums->db->query("SELECT * FROM ibf_attachments_link WHERE attach_id = {$this->attach_id}");

                while ($item = $stmt->fetch()) {
                    if ($item['item_type'] = self::ITEM_TYPE_POST) {
                        if ($this->checkPostAccess($item['item_id'], $member)) {
                            $result = true;
                            break;
                        }
                    }
                }
                $stmt->closeCursor();
            }

            return $result;
        }

        private function checkPostAccess($post_id, $member)
        {
            global $ibforums, $std;
            $topic = $ibforums->db->query("SELECT
        			f.id as fid,
        			f.parent_id as parent_id,
        			f.password as password,
        			f.read_perms,

        			t.club as club,
        			t.approved as approved,
        			t.state as state,
        			t.starter_id as starter_id,
        			t.pinned as pinned,
        			t.starter_id as starter_id

				    FROM
						ibf_forums f,
						ibf_topics t,
						ibf_posts p

				    WHERE p.forum_id = f.id
				    	AND p.pid = {$post_id}
				    	AND p.topic_id = t.tid
					")->fetch();
            $forum = array(
                'id'         => $topic['fid'],
                'parent_id'  => $topic['parent_id'],
                'password'   => $topic['password'],
                'read_perms' => $topic['read_perms'],
            );

            if (!$forum['id']) {
                return false;
            }

            if ($topic['club'] and $std->check_perms($member['club_perms']) == false) {
                return false;
            }

            $std->user_ban_check($forum);

            if (!$topic['approved']) {
                if (!$std->premod_rights(
                    $topic['starter_id'],
                    $mod[$member['id']]['topic_q'], // TODO: find where mod[]
                    $topic['app']
                )
                ) {
                    // $std->Error( array( 'LEVEL' => 1, 'MSG' => 'missing_files') );
                    return false;
                }
            }

            if ($member['id'] and !$member['g_is_supmod']) {
                $stmt = $ibforums->db->query("SELECT *
					    FROM ibf_moderators
					    WHERE
						forum_id=" . $forum['id'] . "
						AND (member_id=" . $member['id'] . "
						     OR (is_group=1
							 AND group_id='" . $member['mgroup'] . "'))");

                $moderator = $stmt->fetch();
            }

            //-------------------------------------
            // Check viewing permissions, private forums,
            // password forums, etc
            //-------------------------------------
            if ((!$topic['pinned']) and ((!$member['g_other_topics']) and ($topic['starter_id'] != $member['id']))) {
                //$std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_view_topic') );
                return false;
            }

            return !$this->check_access($forum);
        }

        private function check_access($forum) // TODO: get rid of this copypast
        {
            global $ibforums, $std;

            $return = 1;

            if ($std->check_perms($forum['read_perms']) == true) {
                $return = 0;
            }

            if ($forum['password'] != "") {
                if (!$c_pass = $std->my_getcookie('iBForum' . $forum['id'])) {
                    return 1;
                }

                if ($c_pass == $forum['password']) {
                    return 0;
                } else {
                    return 1;
                }
            }

            return $return;
        }

        public function setOptions($options = [])
        {
            $this->options = array_fill_keys(array_map('mb_strtolower', array_filter($options)), 1);
        }

        public function getOptions()
        {
            return array_keys($this->options);
        }

        public function hasOption($option)
        {
            return isset($this->options[$option]);
        }
    }

}
