<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yuri
 * Date: 09.03.13
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 */
class ForumApplication extends CoreApplication
{

	/**
	 *
	 * @var IBPDO
	 */
	public $db;
	/**
	 * @var Member
	 */
	var $member;
	var $is_bot = 0;
	var $input = array();
	var $session_id = "";
	var $session_type = "";
	var $base_url = "";
	var $vars = "";
	var $skin = "";
	var $skin_id = "0"; // Skin Dir name
	var $skin_rid = ""; // Real skin id (numerical only)
	var $server_load = 0;
	var $lastclick = "";
	var $location = "";
	var $debug_html = "";
	var $perm_id = "";
	var $forum_read = array();
	var $topic_cache = array();
	var $version = "v1.2";

	public function __construct()
	{
		parent::__construct();
		$this->session = new session();
	}

	public function init()
	{
		$this->vars['TEAM_ICON_URL'] = $this->vars['html_url'] . '/team_icons';
		$this->vars['AVATARS_URL']   = $this->vars['html_url'] . '/avatars';
		$this->vars['mime_img']      = $this->vars['html_url'] . '/mime_types';
		parent::init();
		$this->lastclick  = $this->session->last_click;
		$this->location   = $this->session->location;
		$this->session_id = $this->session->session_id;

	}

	/**
	 * Input data loader
	 * @return array
	 */
	protected function loadInputData()
	{
		$data = parent::loadInputData();
		//--------------------------------
		//	Short tags...
		//--------------------------------
		// If Show Topic selected
		if ($data['showtopic'])
		{
			$data['act'] = "ST";
			$data['t']   = intval($data['showtopic']);

			// Grab and cache the topic now as we need the 'f' attr for
			// the skins...
			$cmd               = Ibf::app()->db
				->prepare("SELECT
					t.*,
					f.topic_mm_id,
					f.name as forum_name,
					f.quick_reply,
					f.id as forum_id,
					f.read_perms,
					f.reply_perms,
					f.parent_id,
					f.use_html,
					f.forum_highlight,
					f.highlight_fid,
					f.start_perms,
					f.allow_poll,
					f.password,
					f.posts as forum_posts,
					f.topics as forum_topics,
					f.upload_perms,
					f.show_rules,
					f.rules_text,
					f.rules_title,
					f.red_border,
					f.siu_thumb,
					f.inc_postcount,
					f.days_off,
					f.decided_button,
					f.faq_id,
					c.id as cat_id,
					c.name as cat_name
				FROM
					ibf_topics t,
					ibf_forums f,
					ibf_categories c
				WHERE
					t.tid = :topic
				AND f.id=t.forum_id
				AND f.category=c.id")
				->bindParam(':topic', $data['t'])
				->execute();
			$this->topic_cache = $cmd->fetch();
			$data['f']         = $this->topic_cache['forum_id'];
		} else
		{
			if ($data['showforum'])
			{
				$data['act'] = "SF";
				$data['f']   = intval($data['showforum']);
			} elseif ($data['showuser'])
			{
				$data['act'] = "Profile";
				$data['MID'] = intval($data['showuser']);
			} elseif (empty($data['act']))
			{
				$data['act'] = "idx";
			}
		}
		return $data;
	}

	protected function loadMember()
	{
		$data              = parent::loadMember();
		$data['show_wp']   = intval($data['show_wp']);
		$data['favorites'] = $data->getFavorites();
		return $data;
	}

	protected function loadSkin()
	{
		$data                  = parent::loadSkin();
		$this->skin_rid        = $data['set_id'];
		$this->skin_id         = 's' . $data['set_id'];
		$this->vars['img_url'] = 'style_images/' . $data['img_dir'];
		return $data;
	}

}
