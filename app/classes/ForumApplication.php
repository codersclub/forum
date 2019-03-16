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
        //  Short tags...
        //--------------------------------
        // If Show Topic selected
        if ($data['showtopic']) {
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
        } else {
            if ($data['showforum']) {
                $data['act'] = "SF";
                $data['f']   = intval($data['showforum']);
            } elseif ($data['showuser']) {
                $data['act'] = "Profile";
                $data['MID'] = intval($data['showuser']);
            } elseif (empty($data['act'])) {
                $data['act'] = "idx";
            }
        }
        return $data;
    }

    protected function loadMember()
    {
        $data              = parent::loadMember();
        $data['show_wp']   = intval($data['show_wp']);
        return $data;
    }

    protected function loadSkin()
    {
        $id       = -1;
        $skin_set = 0;

        if (($this->is_bot == 1) and ($this->vars['spider_suit'] != "")) {
            $skin_set = 'spider';
            $id       = $this->vars['spider_suit'];
        } else {
            //------------------------------------------------
            // Do we have a skin for a particular forum?
            //------------------------------------------------

            if ($this->input['f'] and $this->input['act'] != 'UserCP') {
                if ($this->vars['forum_skin_' . $this->input['f']] != "") {
                    $id = $this->vars['forum_skin_' . $this->input['f']];
                    $skin_set = 'forum';
                    Logs::debug('Ibf', 'Use forum-specific skin', ['forum' => $this->input['f'], 'skin' => $id]);
                }
            }

            //------------------------------------------------
            // Are we allowing user choosable skins?
            //------------------------------------------------

            if ($skin_set === 0 and $this->vars['allow_skins'] == 1) {
                if (isset($this->input['skinid'])) {
                    $id       = intval($this->input['skinid']);
                    $skip_hidden = true;
                    $skin_set = 'request';
                    Logs::debug('Ibf', 'Use skin id from request', ['skin' => $id]);
                } elseif ($this->member['skin'] != "" and intval($this->member['skin']) >= 0) {
                    $id = $this->member['skin'];

                    if ($id == 'Default') {
                        $id = -1;
                    }

                    $skin_set = 'profile';
                    Logs::debug('Ibf', 'Use member preferred skin', ['skin' => $id]);
                }
            }
        }

        //------------------------------------------------
        // Load the info from the database.
        //------------------------------------------------
        try {
            if ($id >= 0 and $skin_set !== 0) {
                try {
                    $skin = \Skins\Factory::create(intval($id));
                    if ($skin->isHidden() && isset($skip_hidden)) {
                        throw new Exception('Attempt to use hidden skin');
                    }
                } catch (\Exception $e) {
                    // Update this members profile
                    Logs::notice('Ibf', 'Skin not found. Fallback to default', [ 'searched id' => $id ]);
                    if ($skin_set === 'profile') {
                        $this->db->prepare("UPDATE ibf_members SET skin=:skinid WHERE id=:id")
                            ->execute(
                                [
                                    ':skinid' => -1,
                                    ':id'     => $this->member['id'],
                                ]
                            );
                    }
                    $skin =  \Skins\Factory::createDefaultSkin();//no catch. It's not our problem if skin doesn't exist
                }
            } else {
                Logs::debug('Ibf', 'No skin selected. Use default');
                $skin = \Skins\Factory::createDefaultSkin();
            }
        } catch (\Exception $e) {
            Logs::critical('Ibf', 'Neither selected nor default skin was found', ['selected skin id' => $id]);
            echo("Could not query the skin information!");
            exit();
        }

        //-------------------------------------------
        // Setting the skin?
        //-------------------------------------------

        if (($this->input['setskin']) and ($this->member['id'])) {
            $this->db->prepare("UPDATE ibf_members SET skin=:sid WHERE id=:id")
                ->execute(
                    [
                        ':sid' => $skin->getId(),
                        ':id' => intval($this->member['id'])
                    ]
                );

            $this->member['skin'] = $skin->getId();
        }
        return $skin;
    }
}
