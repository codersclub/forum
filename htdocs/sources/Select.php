<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Searching procedures
|   > Module written by Matt Mecham
|   > Date started: 24th February 2002
|
|	> Module Version Number: 1.1.0
+--------------------------------------------------------------------------
*/
use Views\View;

require "att.php";

$idx = new Search;

class Search
{

	var $output = "";
	var $page_title = "";
	var $nav = array();

	var $first = 0;

	var $search_type = 'posts';
	var $sort_order = 'desc';
	var $sort_key = 'last_post';
	var $search_in = 'posts';
	var $prune = '30';
	var $st_time = array();
	var $end_time = array();
	var $st_stamp = "";
	var $end_stamp = "";
	var $result_type = "topics";
	var $parser = "";
	var $load_lib = 'search_mysql_man';
	var $lib = "";
	var $read_array = array();
	var $read_mark = array();
	//show modpanel in search
	var $modfunctions = false;

	var $mysql_version = "";
	var $true_version = "";

	function __construct()
	{
		global $ibforums, $std, $print;

		//--------------------------------------------
		// Require the HTML and language modules
		//--------------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_search', $ibforums->lang_id);
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_forum', $ibforums->lang_id);

		$this->base_url = $ibforums->base_url;

		//    	if ( !$ibforums->vars['allow_search']) $std->Error( array( 'LEVEL' => 1, 'MSG' => 'search_off') );
		//    	if ( $ibforums->member['g_use_search'] != 1 ) $std->Error( array( 'LEVEL' => 1, 'MSG' => 'no_permission' ) );

		//    	if ( (!$ibforums->vars['allow_search']) ||
		//    	   ( $ibforums->member['g_use_search'] != 1 )) {
		//	  $this->page_title = $ibforums->lang['search_title'];
		//	  $this->nav        = array( $ibforums->lang['search_form'] );
		//	  $this->output = View::Make("search.alien_form", ['message' => 'search_off']);
		//    	  $print->add_output("$this->output");
		//          $print->do_output( array( 'TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav ) );
		//        }

        //--------------------------------------------
        // Check the user may use Search
        //--------------------------------------------

        if (empty($ibforums->member['g_use_search']))
        {
            $std->Error(array('LEVEL' => 1, 'MSG' => 'cant_use_feature'));
        }

        //---------------------------------------
		// Get the mySQL version.
		// Adapted from phpMyAdmin
		//---------------------------------------

		$stmt = $ibforums->db->query(
		    "SELECT VERSION() AS version"
        );

		if (!$row = $stmt->fetch())
		{
			$stmt = $ibforums->db->query(
			    "SHOW VARIABLES LIKE 'version'"
            );
			$row  = $stmt->fetch();
		}

		$this->true_version = $row['version'];

		$no_array = explode('.', preg_replace("/^(.+?)[-_]?/", "\\1", $row['version']));

		$one   = (!isset($no_array) || !isset($no_array[0]))
			? 3
			: $no_array[0];
		$two   = (!isset($no_array[1]))
			? 21
			: $no_array[1];
		$three = (!isset($no_array[2]))
			? 0
			: $no_array[2];

		$this->mysql_version = (int)sprintf('%d%02d%02d', $one, $two, intval($three));

		if (!$ibforums->input['CODE'])
		{
			$ibforums->input['CODE'] = '00';
		}

		//--------------------------------------------
		// Sort out the required search library
		//--------------------------------------------

		$method = isset($ibforums->vars['search_sql_method'])
			? $ibforums->vars['search_sql_method']
			: 'man';
		$sql    = isset($ibforums->vars['sql_driver'])
			? $ibforums->vars['sql_driver']
			: 'mysql';

		$this->load_lib = 'search_' . mb_strtolower($sql) . '_' . $method . '.php';

		require (ROOT_PATH . "/sources/lib/" . $this->load_lib);

		//--------------------------------------------
		// Suck in libby
		//--------------------------------------------

		$this->lib = new search_lib($this);

		if (isset($ibforums->input['st']))
		{
			$this->first = intval($ibforums->input['st']);
		}

		$this->modfunctions = $ibforums->member['g_is_supmod'] || $ibforums->member['modforums'];

		//--------------------------------------------
		// What to do?
		//--------------------------------------------

		if (!isset($ibforums->member['g_use_search']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'cant_use_feature'));
		}

		switch ($ibforums->input['CODE'])
		{
			case '01':
				$this->do_search();
				break;
			case '02':
				$this->forums_list();
				break;
			case '03':
				$this->accept_forums_list();
				break;
			case 'word':
				$this->do_word_search('posts');
				break;
			case 'title':
				$this->do_word_search('titles');
				break;

			case 'mygetnew':
				$this->get_new_posts(1);
				break;
			case 'getnew':
				$this->get_new_posts();
				break;
			case 'change_days':
				$this->change_days();
				break;

			case 'getactive':
				$this->get_active();
				break;
			case 'show':
				$this->show_results();
				break;
			case 'getreplied':
				$this->get_replies();
				break;
			case 'lastten':
				$this->get_last_ten();
				break;
			case 'getalluser':
				$this->get_all_user();
				break;
			case 'getallusertopics':
				$this->get_all_user('topics');
				break;
			case 'getalluseratt':
				$this->get_all_user_att();
				break;
			case 'simpleresults':
				$this->show_simple_results();
				break;
			case 'explain':
				$this->show_boolean_explain();
				break;
			default:
				$this->show_form();
				break;
		}

		// If we have any HTML to print, do so...

		$print->add_output("$this->output");
		$print->do_output(array(
		                       'TITLE' => $this->page_title,
		                       'JS'    => ($this->modfunctions)
			                       ? "rows_js.js?{$ibforums->vars['client_script_version']}"
			                       : 0,
		                       'NAV'     => $this->nav
		                  ));

	}

	//-----------------------------------------------------
	// Do simple search
	//-----------------------------------------------------

	function show_simple_results()
	{
		$result = $this->lib->do_simple_search();
	}

	//-----------------------------------------------------
	// Get all posts by a member
	//-----------------------------------------------------

	function get_all_user($result = 'posts')
	{
		global $ibforums, $std, $print;

		//------------------------------------
		// Do we have flood control enabled?
		//------------------------------------

		if ($ibforums->member['g_search_flood'] > 0)
		{
			$flood_time = time() - $ibforums->member['g_search_flood'];

			// Get any old search results..

			$stmt = $ibforums->db->query(
			    "SELECT id
				FROM ibf_search_results
				WHERE (member_id='" . $ibforums->member['id'] . "'
				       OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "')
				  AND search_date > '$flood_time'"
            );

			if ($stmt->rowCount())
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'search_flood',
				                 'EXTRA' => $ibforums->member['g_search_flood']
				            ));
			}
		}

		// Check for valid forum ID

		$fid = intval($ibforums->input['fid']);

		if ($fid)
		{

			$stmt = $ibforums->db->query(
			    "SELECT id, read_perms
				FROM ibf_forums
				WHERE id='" . $fid . "'"
            );

			if ($row = $stmt->fetch())
			{
				if (!$std->check_perms($row['read_perms']))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_search_forum'
					            ));
				}
			} else
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'no_search_forum'
				            ));
			}

			//			if ( $std->check_perms($row['read_perms']) == FALSE ) continue;
			//			if ( $std->check_perms($row['read_perms']) == FALSE ) continue;
			//			if ( $this->check_access($fid) ) {
			$forums = $fid;
			//			}
		} else
		{
			$ibforums->input['forums'] = 'all';

			$forums = $this->get_searchable_forums();
		}

		//		$member_perms = $ibforums->perm_id;
		//		$member_perms = str_replace( ",", "|", $member_perms);
		//
		//		$stmt = $ibforums->db->query(
        //          "SELECT
		//					f.id as forum_id,
		//					f.parent_id,
		//					f.subwrap,
		//					f.sub_can_post,
		//					f.name as forum_name,
		//					f.position,
		//					f.read_perms,
		//					c.id as cat_id,
		//					c.name as cat_name
		//			FROM ibf_forums f
		//			LEFT JOIN ibf_categories c
		//				ON (c.id=f.category)
		//			WHERE read_perms RLIKE \"(^|,)(" . $member_perms.")(,|$)\"
		//			ORDER BY c.position, f.position"
        //      );

		//------------------------------------
		// Do we have any forums to search in?
		//------------------------------------

		if (!$forums)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_forum'
			            ));
		}

		$mid = intval($ibforums->input['mid']);

		if (!$mid)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		//------------------------------------------------
		// Get the topic ID's to serialize and store into
		// the database
		//------------------------------------------------

		$posts = "";

		if ($result == 'posts')
		{
			$stmt = $ibforums->db->query(
			    "SELECT pid
				FROM ibf_posts
				WHERE queued != 1
				  AND forum_id IN($forums)
				  AND author_id=$mid"
            );

			$max_hits = $stmt->rowCount();

			while ($row = $stmt->fetch())
			{
				$posts .= $row['pid'] . ",";
			}
		} else
		{
			$stmt = $ibforums->db->query(
			    "SELECT tid
                FROM ibf_topics
                WHERE forum_id IN($forums)
                  AND approved=1
                  AND starter_id=$mid"
            );

			$max_hits = $stmt->rowCount();

			while ($row = $stmt->fetch())
			{
				$posts .= $row['tid'] . ",";
			}
		}

		$stmt->closeCursor();

		$posts = preg_replace("/,$/", "", $posts);

		//------------------------------------------------
		// Do we have any results?
		//------------------------------------------------

		if (!$posts)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		//------------------------------------------------
		// If we are still here, store the data into the database...
		//------------------------------------------------

		$unique_id = md5(uniqid(microtime(), 1));

		if ($result == 'posts')
		{
			$data = [
				'id'          => $unique_id,
				'search_date' => time(),
				'post_id'     => $posts,
				'post_max'    => $max_hits,
				'sort_key'    => $this->sort_key,
				'sort_order'  => $this->sort_order,
				'member_id'   => $ibforums->member['id'],
				'ip_address'  => $ibforums->input['IP_ADDRESS'],
			];
		} else
		{
			$data = [
				'id'          => $unique_id,
				'search_date' => time(),
				'topic_id'    => $posts,
				'topic_max'   => $max_hits,
				'sort_key'    => $this->sort_key,
				'sort_order'  => $this->sort_order,
				'member_id'   => $ibforums->member['id'],
				'ip_address'  => $ibforums->input['IP_ADDRESS'],
			];
		}

		$ibforums->db->insertRow('ibf_search_results', $data);

		$print->redirect_screen($ibforums->lang['search_redirect'], "act=Select&nav=au&CODE=show&searchid=$unique_id&search_in={$result}&result_type={$result}");

		exit();
	}

	//--------------------------------------------------------

	function get_all_user_att()
	{
		$a                = new Att;
		$this->output     = $a->html;
		$this->page_title = $a->pt;
	}

	//--------------------------------------------------------

	function get_new_posts($mine = 0)
	{
		global $ibforums, $std, $print;

		if (!$ibforums->member['id'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		//------------------------------------
		// Do we have flood control enabled?
		//------------------------------------

		if ($ibforums->member['g_search_flood'] > 0)
		{
			$flood_time = time() - $ibforums->member['g_search_flood'];

			// Get any old search results..

			$stmt = $ibforums->db->query(
			    "SELECT id
				FROM ibf_search_results
				WHERE (member_id='" . $ibforums->member['id'] . "'
					  OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "')
				  AND search_date > '$flood_time'"
            );

			if ($stmt->rowCount())
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'search_flood',
				                 'EXTRA' => $ibforums->member['g_search_flood']
				            ));
			}
		}

		$ibforums->input['forums'] = 'all';

		$ibforums->input['nav'] = ($mine)
			? 'my_lv'
			: 'lv';

		$forums = $this->get_searchable_forums();

		//------------------------------------
		// Do we have any forums to search in?
		//------------------------------------

		if (!$forums)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_forum'
			            ));
		}

		//------------------------------------------------
		// Get the topic ID's to serialize and store into
		// the database
		//------------------------------------------------

		$stmt = $ibforums->db->query(
		    "SELECT
				sf.fid,
				f.read_perms
			FROM
				ibf_search_forums sf,
				ibf_forums f
			WHERE sf.fid=f.id
			  AND sf.mid='" . $ibforums->member['id'] . "'"
        );

		$all = "";

		while ($row = $stmt->fetch())
		{
			if ($std->check_perms($row['read_perms']) == FALSE)
			{
				continue;
			}

			$all .= $row['fid'] . ",";
		}

		if ($ibforums->member['search_days'] == -1)
		{
			$time = $ibforums->input['last_visit'];
		} else
		{
			$time = time();

			// 5 days ago
			$time = $time - 60 * 60 * 24 * intval($ibforums->member['search_days']);
		}

		// read topic changes
		$this->get_read_topics($time);

		if ($mine)
		{
			$query = "SELECT t.tid as topic_id, t.last_post, t.forum_id FROM ibf_topics t, ibf_posts p
			          WHERE p.queued != 1 and p.author_id='" . $ibforums->member['id'] . "' and p.topic_id=t.tid and
					p.post NOT LIKE '%[MOD]%' and p.post NOT LIKE '%[EX]%' and t.state != 'link' and t.approved=1 and
					t.forum_id IN ({$forums}) and t.last_post > '" . $time . "'";
		} else
		{
			$query = "SELECT t.tid as topic_id, t.last_post, t.forum_id FROM ibf_topics t
				  WHERE t.state != 'link' AND t.approved=1 AND t.forum_id IN($forums) AND t.last_post > '" . $time . "'";
		}

		if ($all)
		{
			$query .= " and t.forum_id IN (" . $all . "0)";
		}

		$query .= " GROUP BY t.tid";

		// selected search

		$stmt = $ibforums->db->query($query);

		$max_hits = $stmt->rowCount();

		$posts = "";

		while ($topic = $stmt->fetch())
		{
			$last_time = '';

			$tid = $topic['topic_id'];
			$fid = $topic['forum_id'];
			if ($ibforums->member['id'])
			{
				if (!isset($this->read_mark[$fid]))
				{
					$this->read_mark[$fid] = $ibforums->forums_read[$topic['forum_id']];

					$this->read_mark[$fid] = ($ibforums->member['board_read'] > $this->read_mark[$fid])
						? $ibforums->member['board_read']
						: $this->read_mark[$fid];

					$this->read_mark[$fid] = ($this->read_mark[$fid] < (time() - 60 * 60 * 24 * 30))
						? (time() - 60 * 60 * 24 * 30)
						: $this->read_mark[$fid];
				}

				if ($this->read_array[$tid])
				{
					$last_time = $this->read_array[$tid];
				} else
				{
					$last_time = -1;

					if ($topic['last_post'] < $this->read_mark[$fid])
					{
						$last_time = $this->read_mark[$fid];
					}
				}
			}

			if ($last_time && ($topic['last_post'] > $last_time))
			{
				$posts .= $tid . ",";
			}
		}

		$stmt->closeCursor();

		$posts = preg_replace("/,$/", "", $posts);

		//------------------------------------------------
		// Do we have any results?
		//------------------------------------------------

		if (!$posts)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		//------------------------------------------------
		// If we are still here, store the data into the database...
		//------------------------------------------------

		$unique_id = md5(uniqid(microtime(), 1));

		$data = [
			'id'          => $unique_id,
			'search_date' => time(),
			'topic_id'    => $posts,
			'topic_max'   => $max_hits,
			'sort_key'    => $this->sort_key,
			'sort_order'  => $this->sort_order,
			'member_id'   => $ibforums->member['id'],
			'ip_address'  => $ibforums->input['IP_ADDRESS'],
		];

		$ibforums->db->insertRow('ibf_search_results', $data);

		$print->redirect_screen($ibforums->lang['search_redirect'], "act=Select&nav={$ibforums->input['nav']}&CODE=show&CODE_MODE={$ibforums->input['CODE']}&searchid=$unique_id&search_in=topics&result_type=topics&new=1");
		exit();
	}

	//--------------------------------------------------------

	function get_last_ten()
	{
		global $ibforums, $std, $print;

		$ibforums->input['forums'] = 'all';

		$forums = $this->get_searchable_forums();

		//------------------------------------
		// Do we have any forums to search in?
		//------------------------------------

		if (!$forums)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_forum'
			            ));
		}

		//------------------------------------------------
		// Get the topic ID's to serialize and store into
		// the database
		//------------------------------------------------

		$stmt = $ibforums->db->query(
		    "SELECT p.*, t.*,
                f.id as forum_id, f.name as forum_name
			FROM ibf_topics t, ibf_posts p, ibf_forums f
			WHERE p.use_sig=0
			  AND p.queued != 1
			  AND p.forum_id IN($forums)
			  AND p.author_id='" . $ibforums->member['id'] . "'
			  AND t.tid=p.topic_id
			  AND f.id=p.forum_id
			  AND t.approved=1
            ORDER BY p.post_date DESC
            LIMIT 10"
        );

		$count = 0;

		if ($stmt->rowCount())
		{
			$topic_list = array();
			$tids       = [];

			while ($row = $stmt->fetch())
			{
				if ($row['club'] and $std->check_perms($ibforums->member['club_perms']) == FALSE)
				{
					continue;
				}
				$row['ip_address'] = "";

				$topic_list[$count] = $row;

				$count++;
				$tids[] = $row['tid'];
			}

			if (!empty($tids))
			{
				$this->fill_read_arrays($tids);
			}

			$count = count($topic_list);

			$this->parser = new PostParser();

			$this->output .= View::make("search.start_as_post", ['Data' => array('SHOW_PAGES' => $links)]);

			foreach ($topic_list as $row)
			{
				$data = array(
					'TEXT'      => $row['post'],
					'SMILIES'   => $row['use_emo'],
					'CODE'      => 1,
					'SIGNATURE' => 0,
					'HTML'      => 1,
					'HID'       => -1,
					'TID'       => $row['topic_id'],
					'MID'       => $row['author_id'],
				);

				$row['post'] = $this->parser->prepare($data);

				if (!trim($row['post']))
				{
					$count--;
					continue;
				}

				$row['keywords']  = $url_words;
				$row['std_post_date'] = date('c', $row['post_date']);
				$row['post_date'] = $std->get_date($row['post_date']);

				if ($ibforums->vars['post_wordwrap'] > 0)
				{
					$row['post'] = $this->parser->my_wordwrap($row['post'], $ibforums->vars['post_wordwrap']);
				}

				$this->output .= View::make("search.RenderPostRow", ['Data' => $this->parse_entry($row, 1)]);
			}

			$this->output .= View::make("search.end_as_post", ['Data' => array('SHOW_PAGES' => $links)]);

		} else
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		if ($count <= 0)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		$stmt->closeCursor();

		$this->page_title = $ibforums->lang['nav_lt'];

		$this->nav = array($ibforums->lang['nav_lt']);

	}

	//--------------------------------------------------------

	function get_replies()
	{
		global $ibforums, $std, $print;

		//------------------------------------
		// Do we have flood control enabled?
		//------------------------------------

		if ($ibforums->member['g_search_flood'] > 0)
		{
			$flood_time = time() - $ibforums->member['g_search_flood'];

			// Get any old search results..

			$stmt = $ibforums->db->query(
			    "SELECT id
                FROM ibf_search_results
                WHERE (member_id='" . $ibforums->member['id'] . "'
                      OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "')
                  AND search_date > '$flood_time'"
            );

			if ($stmt->rowCount())
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'search_flood',
				                 'EXTRA' => $ibforums->member['g_search_flood']
				            ));
			}
		}

		$ibforums->input['forums'] = 'all';
		$ibforums->input['nav']    = 'lv';

		$forums = $this->get_searchable_forums();

		//------------------------------------
		// Do we have any forums to search in?
		//------------------------------------

		if (!$forums)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_search_forum'));
		}

		//------------------------------------------------
		// Get the topic ID's to serialize and store into
		// the database
		//------------------------------------------------

		$stmt = $ibforums->db->query(
		    "SELECT tid
            FROM ibf_topics
            WHERE starter_id='" . $ibforums->member['id'] . "'
		      AND last_post > " . $ibforums->member['last_visit'] . "
		      AND forum_id IN($forums)
		      AND approved=1"
        );

		$max_hits = $stmt->rowCount();

		$topics = "";

		while ($row = $stmt->fetch())
		{
			$topics .= $row['tid'] . ",";
		}

		$stmt->closeCursor();

		$topics = preg_replace("/,$/", "", $topics);

		//------------------------------------------------
		// Do we have any results?
		//------------------------------------------------

		if (!$topics)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_search_results'));
		}

		//------------------------------------------------
		// If we are still here, store the data into the database...
		//------------------------------------------------

		$unique_id = md5(uniqid(microtime(), 1));

		$data = [
			'id'          => $unique_id,
			'search_date' => time(),
			'topic_id'    => $topics,
			'topic_max'   => $max_hits,
			'sort_key'    => $this->sort_key,
			'sort_order'  => $this->sort_order,
			'member_id'   => $ibforums->member['id'],
			'ip_address'  => $ibforums->input['IP_ADDRESS'],
		];

		$ibforums->db->insertRow('ibf_search_results', $data);

		$print->redirect_screen($ibforums->lang['search_redirect'], "act=Select&nav=gr&CODE=show&searchid=$unique_id&search_in=posts&result_type=topics");
		exit();

	}

	//------------------------------------------------
	// Show pop-up window
	//------------------------------------------------

	function show_boolean_explain()
	{
		global $std, $ibforums, $print;

		$print->pop_up_window($ibforums->lang['be_link'], View::make("search.boolean_explain_page"));

	}

	//------------------------------------------------
	// Show main form
	//------------------------------------------------

	function do_word_search($search_in)
	{
		global $ibforums;

		$ibforums->input['searchsubs'] = 1;

		$fid = $ibforums->input['f'];

		$_POST['forums'] = Array('' => 'all');

		if ($fid)
		{
			$stmt = $ibforums->db->query(
			    "SELECT parent_id
                FROM ibf_forums
                WHERE id='" . $fid . "'"
            );

			if ($row = $stmt->fetch())
			{
				if ($row['parent_id'] != -1)
				{
					$_POST['forums'] = Array('' => $row['parent_id']);
				} else
				{
					$_POST['forums'] = Array('' => $fid);
				}
			}
		}

		$ibforums->input['forums']      = $this->get_searchable_forums();
		$ibforums->input['prune']       = 0;
		$ibforums->input['prune_type']  = 'newer';
		$ibforums->input['sort_key']    = 'last_post';
		$ibforums->input['sort_order']  = 'desc';
		$ibforums->input['search_in']   = $search_in;
		$ibforums->input['result_type'] = 'topics';

		$this->do_search();
	}

	function show_form()
	{
		global $std, $ibforums;

		$last_cat_id = -1;

		$the_hiddens = "";

		$stmt = $ibforums->db->query(
		    "SELECT f.id as forum_id, f.parent_id, f.subwrap, f.sub_can_post, f.name as forum_name,
                    f.position, f.read_perms, c.id as cat_id, c.name as cat_name
		    FROM ibf_forums f
		    LEFT JOIN ibf_categories c
		      ON (c.id=f.category)
		    ORDER BY c.position, f.position"
        );

		$forum_keys = array();
		$cat_keys   = array();
		$children   = array();
		$subs       = array();
		$subwrap    = array();

		while ($i = $stmt->fetch())
		{
			$selected = '';

			if ($ibforums->input['f'] and $ibforums->input['f'] == $i['forum_id'])
			{
				$selected = ' selected="selected"';
			}

			if ($i['subwrap'] == 1)
			{
				$is_sub                  = $ibforums->lang['is_sub'];
				$sub_css                 = " class='sub' ";
				$subwrap[$i['forum_id']] = 1;

			}

			if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
			{
				$forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $sub_css . "$selected>&middot;&middot;&nbsp;{$i['forum_name']}$is_sub</option>\n";
			} else
			{
				if ($std->check_perms($i['read_perms']) == TRUE)
				{
					if ($i['parent_id'] > 0)
					{
						$children[$i['parent_id']][] = array(
							$i['forum_id'],
							"<option value=\"{$i['forum_id']}\"$selected>&middot;&middot;&middot;&middot;<IBF_SONG_DEPTH>&nbsp;{$i['forum_name']}</option>\n"
						);
					} else
					{
						$forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $sub_css . "$selected>&middot;&middot;&nbsp;{$i['forum_name']}$is_sub</option>\n";
					}
				} else
				{
					continue;
				}
			}

			if ($last_cat_id != $i['cat_id'])
			{
				$cat_keys[$i['cat_id']] = "<option value=\"c_{$i['cat_id']}\" class='cat'>{$i['cat_name']}</option>\n";
				$last_cat_id            = $i['cat_id'];
			}

			unset($is_sub);
			unset($sub_css);
		}

		foreach ($cat_keys as $cat_id => $cat_text)
		{
			if (is_array($forum_keys[$cat_id]) && count($forum_keys[$cat_id]) > 0)
			{
				$the_html .= $cat_text;

				foreach ($forum_keys[$cat_id] as $idx => $forum_text)
				{
					if ($subwrap[$idx] != 1)
					{
						$the_html .= $forum_text;
					} else
					{
                        if (empty($children[$idx])) {
                            $children[$idx] = [];
                        }
						if (count($children[$idx]) > 0)
						{
							$the_html .= $forum_text;
							$the_html .= $this->subforums_addtoform($idx, $children);
						}
					}
				}
			}
		}

		$init_sel = "";

		if ($ibforums->input['f'] == "")
		{
			$init_sel = ' selected="selected"';
		}

		$forums = "<select name='forums[]' class='forminput' size='10' multiple='multiple'>\n" . "<option value='all'" . $init_sel . ">" . $ibforums->lang['all_forums'] . "</option>" . $the_html . "</select>";

		$where = '';
		if ($ibforums->vars['search_sql_method'] == 'ftext')
		{
			$label = $ibforums->lang['keysearch_text_simple'];
		} else
		{
			$label = $ibforums->lang['keysearch_text'];
			$where = View::make("search.checkbox_where");
		}

		if ($ibforums->input['mode'] == 'simple')
		{
			if ($ibforums->vars['search_sql_method'] == 'ftext')
			{
				$this->output = View::make(
					"search.simple_form",
					['forums' => $forums, 'search_txt' => $label, 'where' => $where]
				);
			} else
			{
				$this->output = View::make(
					"search.Form",
					['forums' => $forums, 'search_txt' => $label, 'where' => $where]
				);
			}

		} elseif ($ibforums->input['mode'] == 'adv')
		{
			$this->output = View::make("search.Form", ['forums' => $forums, 'search_txt' => $label, 'where' => $where]);

			if ($ibforums->vars['search_sql_method'] == 'ftext')
			{
				$this->output = str_replace("<!--IBF.SIMPLE_BUTTON-->",
					View::make("search.form_simple_button"), $this->output);
			}
		} else
		{
			// No mode specified..

			if ($ibforums->vars['search_default_method'] == 'simple')
			{
				$this->output = View::make(
					"search.Form",
					['forums' => $forums, 'search_txt' => $label, 'where' => $where]
				);
			} else
			{
				// Default..

				$this->output = View::make(
					"search.Form",
					['forums' => $forums, 'search_txt' => $label, 'where' => $where]
				);

				if ($ibforums->vars['search_sql_method'] == 'ftext')
				{
					$this->output = str_replace("<!--IBF.SIMPLE_BUTTON-->",
						View::make("search.form_simple_button"), $this->output);
				}
			}
		}

		if ($this->mysql_version >= 40010 AND $ibforums->vars['search_sql_method'] == 'ftext')
		{
			$this->output = str_replace("<!--IBF.BOOLEAN_EXPLAIN-->",
				View::make("search.boolean_explain_link"), $this->output);
		}

		$this->page_title = $ibforums->lang['search_title'];
		$this->nav        = array($ibforums->lang['search_form']);

	}

	function do_search()
	{
		global $ibforums, $std, $print;

		//------------------------------------
		// Do we have flood control enabled?
		//------------------------------------

		if ($ibforums->member['g_search_flood'] > 0)
		{
			$flood_time = time() - $ibforums->member['g_search_flood'];

			// Get any old search results..

			$stmt = $ibforums->db->query(
			    "SELECT id
                FROM ibf_search_results
                WHERE (member_id='" . $ibforums->member['id'] . "'
                      OR ip_address='" . $ibforums->input['IP_ADDRESS'] . "')
                  AND search_date > '$flood_time'"
            );

			if ($stmt->rowCount())
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'search_flood',
				                 'EXTRA' => $ibforums->member['g_search_flood']
				            ));
			}
		}

		//------------------------------------------------
		// init main search
		//------------------------------------------------

		$result = $this->lib->do_main_search();

		//------------------------------------------------
		// Do we have any results?
		//------------------------------------------------

		if (!$result['topic_id'] and !$result['post_id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_search_results'));
		}

		//------------------------------------------------
		// If we are still here, store the data into the database...
		//------------------------------------------------

		$unique_id = md5(uniqid(microtime(), 1));

		$data = [
			'id'          => $unique_id,
			'search_date' => time(),
			'topic_id'    => $result['topic_id'],
			'topic_max'   => $result['topic_max'],
			'sort_key'    => $this->sort_key,
			'sort_order'  => $this->sort_order,
			'member_id'   => $ibforums->member['id'],
			'ip_address'  => $ibforums->input['IP_ADDRESS'],
			'post_id'     => $result['post_id'],
			'post_max'    => $result['post_max'],
		];

		$ibforums->db->insertRow('ibf_search_results', $data);

		$print->redirect_screen($ibforums->lang['search_redirect'], "act=Select&CODE=show&searchid=$unique_id&search_in=" . $this->search_in . "&result_type=" . $this->result_type . "&highlite=" . urlencode(trim($result['keywords'])));
	}

	/**
	 * Обновление времени прочтённости топиков
	 * @param $topics array topic ids to update
	 */
	function fill_read_arrays($topics)
	{
		$ibforums = Ibf::app();

		if ($ibforums->member['id'])
		{
			// at first, collect data within visited topics
			$stmt = $ibforums->db->prepare(
			    "SELECT tid, fid, logTime
                FROM ibf_log_topics
                WHERE mid=?
                  AND tid IN (" . IBPDO::placeholders($topics) . ")"
            )->execute(
                array_merge((array)$ibforums->member['id'], $topics)
            );

			while ($read = $stmt->fetch())
			{
				$this->fillReadArrayItem($read['fid'], $read['tid'], $read['logTime']);
			}
		}
	}

	/**
	 * Обновление времени прочтённости одного топика
	 * @param $fid int forum id
	 * @param $tid int topic id
	 * @param $log_time int Текущее время обновления
	 */
	protected function fillReadArrayItem($fid, $tid, $log_time) {

		if (!$this->read_mark[$fid])
		{
			$this->read_mark[$fid] = Ibf::app()->forums_read[$fid];

			$this->read_mark[$fid] = (Ibf::app()->member['board_read'] > $this->read_mark[$fid])
				? Ibf::app()->member['board_read']
				: $this->read_mark[$fid];

			$this->read_mark[$fid] = ($this->read_mark[$fid] < (time() - 60 * 60 * 24 * 30))
				? (time() - 60 * 60 * 24 * 30)
				: $this->read_mark[$fid];
		}

		$this->read_array[$tid] = ($this->read_mark[$fid] > $log_time)
			? $this->read_mark[$fid]
			: $log_time;
	}

	/**
	 * Фильтрация доступов и выдача результата
	 */
	function show_results()
	{
		$ibforums = Ibf::app();

		$this->result_type = $ibforums->input['result_type'];
		$this->search_in   = $ibforums->input['search_in'];

		//------------------------------------------------
		// We have a search ID, so lets get the parsed results.
		// Delete old search queries (older than 24 hours)
		//------------------------------------------------

		$this->unique_id = $ibforums->input['searchid'];

		if (!$this->unique_id)
		{
			$ibforums->functions->Error(
				[
					'LEVEL' => 1,
					'MSG'   => 'no_search_results'
				]
			);
		}

		$stmt = $ibforums->db
			->prepare("SELECT * FROM ibf_search_results WHERE id=:id")
			->bindParam(':id', $this->unique_id)
			->execute();
		if($stmt->rowCount() === 0)
		{
			$ibforums->functions->Error(
				[
					'LEVEL' => 1,
					'MSG'   => 'no_search_results'
				]
			);
		}
		$sr   = $stmt->fetch();

		$this->sort_order = $sr['sort_order'];
		$this->sort_key   = $sr['sort_key'];

		$topics = explode(',', $sr['topic_id']);
		$posts = explode(',', $sr['post_id']);

		//-------------------------------------

		if (empty($topics) && empty($posts))
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'no_search_results'));
		}

		$url_words = $this->convert_highlite_words($ibforums->input['highlite']);

		$count = 0;

		if ($this->result_type == 'topics')
		{
			if ($this->search_in == 'titles')
			{
				$query = "SELECT
						t.*,
						f.id as forum_id,
						f.name as forum_name
				      FROM
						ibf_topics t,
						ibf_forums f
					  WHERE
						t.tid IN(" . IBPDO::placeholders($topics) . ")
					  AND
						f.id=t.forum_id
					  AND
						t.approved = 1
					  ORDER BY t.pinned DESC,";

				if ($ibforums->input['new'])
				{
					$query .= "f.id ASC,";
				}
				$query .= $this->sort_key . " " . $this->sort_order . " LIMIT " . $this->first . ",25";

				$stmt = $ibforums
					->db
					->prepare($query)
					->execute($topics);
			} else
			{
				//--------------------------------------------
				// we have tid and pid to sort out, woohoo NOT
				//--------------------------------------------

				if (!empty($posts))
				{
					$stmt = $ibforums->db->prepare(
						"SELECT
							topic_id
						FROM ibf_posts
						WHERE
							pid IN(" . IBPDO::placeholders($posts) . ")
						AND
							queued != 1"
                    )->execute($posts);

					while(FALSE !== $topic_id = $stmt->fetchColumn()){
						$topics[] = $topic_id;
					}
				}

				$query = "SELECT
						t.*,
						f.id as forum_id,
						f.name as forum_name,
						l.logTime
				  	  FROM ibf_topics t
					  LEFT JOIN ibf_forums f ON (f.id=t.forum_id)
					  LEFT JOIN ibf_log_topics l ON t.tid = l.tid AND l.fid = t.forum_id AND l.mid = ?
					  WHERE
						t.tid IN(" . IBPDO::placeholders($topics) . ")
				      AND
						t.approved=1
					  ORDER BY t.pinned DESC,";

				if ($ibforums->input['new'])
				{
					$query .= "f.id ASC,";
				}

				$query .= "t." . $this->sort_key . " " . $this->sort_order . " LIMIT " . $this->first . ",25";

				$stmt = $ibforums->db
					->prepare($query)
					->execute(array_merge((array)$ibforums->member['id'], $topics));
			}

			//--------------------------------------------

			if ($stmt->rowCount() > 0)
			{
				$topics = array_filter(array_unique($topics));
				$this->output .= $this->start_page(count($topics));

				while ($row = $stmt->fetch())
				{
					if ($row['club'] and $ibforums->functions->check_perms($ibforums->member['club_perms']) == FALSE)
					{
						continue;
					}
					//обновляем информацию по прочитанности топика
					$this->fillReadArrayItem($row['forum_id'], $row['tid'], $row['logTime'] );

					$row['keywords'] = $url_words;

					if ($row['pinned'])
					{
						$this->output .= View::make("search.RenderPinnedRow", ['Data' => $this->parse_entry($row)]);
					} else
					{
						$this->output .= View::make("search.RenderRow", ['Data' => $this->parse_entry($row)]);
					}
					$count++;
				}

			} else
			{
				$ibforums->functions->Error([
				                 'LEVEL' => 1,
				                 'MSG'   => 'no_search_results'
				            ]);
			}

			//--------------------------------------------

			$this->output .= View::make(
				"search.end",
				[
					'Data' => array(
						'SHOW_PAGES' => $this->links,
						'modform_close' => ($this->modfunctions)
							? View::make("search.modform_close")
							: ""
					)
				]
			);

		} else
		{
			//Результат в виде постов

			$this->parser = new PostParser();

			if ($this->search_in == 'titles')
			{
				//Результат в виде постов при поиске по заголовкам топиков? WTF?
				$stmt = $ibforums->db->prepare(
				    "SELECT
						t.*,
						p.pid,
						p.author_id,
						p.author_name,
						p.post_date,
						p.post,
						f.id as forum_id,
						f.name as forum_name
					FROM ibf_topics t
					LEFT JOIN ibf_posts p
					    ON (t.tid=p.topic_id AND p.new_topic=1 AND p.use_sig=0)
			        LEFT JOIN ibf_forums f
			            ON (f.id=t.forum_id)
			        WHERE t.tid IN(" . IBPDO::placeholders($topics) . ")
					  AND p.queued != 1
					  AND t.approved=1
			        ORDER BY p.post_date DESC
			        LIMIT " . $this->first . ",25"
                )->execute($topics);
			} else
			{
				$this->parser->prepareIcons();

				if (!empty($topics))
				{
					$stmt = $ibforums->db->prepare(
					    "SELECT pid
					    FROM ibf_posts
					    WHERE topic_id IN(" . IBPDO::placeholders($topics) . ")
					      AND new_topic=1
					      AND queued != 1"
                    )->execute($topics);

					while(FALSE !== $pid = $stmt->fetchColumn()){
						$posts[] = $pid;
					}
				}

				$stmt = $ibforums->db->prepare(
				    "SELECT
						t.*,
						p.pid,
						p.author_id,
						p.author_name,
						p.post_date,
						p.post,
						p.use_emo,
						f.id as forum_id,
						f.forum_highlight,
						f.highlight_fid as hid,
						f.name as forum_name,
						f.use_html,
						g.g_dohtml,
						p.ip_address
					FROM
						ibf_posts p
					LEFT JOIN ibf_topics t
					    ON (t.tid=p.topic_id)
					LEFT JOIN ibf_forums f
					    ON (f.id=p.forum_id)
					LEFT JOIN ibf_members m
					    ON (m.id=p.author_id)
					LEFT JOIN ibf_groups g
					    ON (m.mgroup=g.g_id)
					WHERE p.pid IN(" . IBPDO::placeholders($posts) . ")
					  AND p.use_sig = 0
					  AND p.queued != 1
					  AND t.approved = 1
					ORDER BY p.post_date DESC
					LIMIT " . $this->first . ", 25"
                )->execute($posts);
			}

			$posts = array_filter(array_unique($posts));//todo может всё-таки SQL COUNT()?
			$this->output .= $this->start_page(count($posts), 1);

			while ($row = $stmt->fetch())
			{
				// Ip address in a search
				if ($ibforums->member['g_is_supmod'])
				{
					$row['ip_address'] = "( <a href='{$ibforums->base_url}&act=modcp&CODE=ip&incoming={$row['ip_address']}' target='_blank'>{$row['ip_address']}</a> )";
				} else
				{
					$row['ip_address'] = "";
				}

				// Club tool
				if ($row['club'] and $ibforums->functions->check_perms($ibforums->member['club_perms']) == FALSE)
				{
					continue;
				}

				$data = array(
					'TEXT'      => $row['post'],
					'SMILIES'   => $row['use_emo'],
					'CODE'      => 1,
					'SIGNATURE' => 0,
					'HTML'      => 1,
					'HID'       => ($row['forum_highlight'])
						? $row['hid']
						: -1,
					'TID'       => $row['topic_id'],
					'MID'       => $row['author_id'],
				);

				$row['post'] = $this->parser->prepare($data);

				if (!trim($row['post']))
				{
					continue;
				}

				$row['keywords']  = $url_words;
				$row['std_post_date'] = date('c', $row['post_date']);
				$row['post_date'] = $ibforums->functions->get_date($row['post_date']);

				//--------------------------------------------------------------
				// Parse HTML tag on the fly
				//--------------------------------------------------------------

				if ($row['use_html'] == 1)
				{
					// So far, so good..

					if (stristr($row['post'], '[dohtml]'))
					{
						// [doHTML] tag found..

						$parse = ($row['use_html'] AND $row['g_dohtml'])
							? 1
							: 0;

						$row['post'] = $this->parser->post_db_parse($row['post'], $parse);
					}
				}

				//--------------------------------------------------------------
				// Do word wrap?
				//--------------------------------------------------------------

				if ($ibforums->vars['post_wordwrap'] > 0)
				{
					$row['post'] = $this->parser->my_wordwrap($row['post'], $ibforums->vars['post_wordwrap']);
				}

				$this->output .= View::make("search.RenderPostRow", ['Data' => $this->parse_entry($row, 1)]);
				$count++;
			}

			$this->output .= View::make("search.end_as_post", ['Data' => array('SHOW_PAGES' => $this->links)]);
		}

		if ($count <= 0)
		{
			$ibforums->functions->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_search_results'
			            ));
		}

		$this->page_title = $ibforums->lang['search_results'];

		if ($ibforums->input['nav'] == 'lv')
		{
			$this->nav = array($ibforums->lang['nav_since_lv']);

		} elseif ($ibforums->input['nav'] == 'my_lv')
		{
			$this->nav = array($ibforums->lang['my_nav_since_lv']);

		} elseif ($ibforums->input['nav'] == 'lt')
		{
			$this->nav = array($ibforums->lang['nav_lt']);

		} elseif ($ibforums->input['nav'] == 'au')
		{
			$this->nav = array($ibforums->lang['nav_au']);
		} else
		{
			$this->nav = array(
				"<a href='{$this->base_url}&act=Select'>{$ibforums->lang['search_form']}</a>",
				$ibforums->lang['search_title']
			);
		}
	}

	function start_page($amount, $is_post = 0)
	{
		global $ibforums, $std;

		$url_words = $this->convert_highlite_words($ibforums->input['highlite']);

		$this->links = $std->build_pagelinks(array(

		                                          'TOTAL_POSS' => $amount,
		                                          'PER_PAGE'   => 25,
		                                          'CUR_ST_VAL' => $this->first,
		                                          'L_SINGLE'   => "",
		                                          'L_MULTI'    => $ibforums->lang['search_pages'],
		                                          'BASE_URL'   => $this->base_url . "act=Select&nav={$ibforums->input['nav']}&CODE=show&CODE_MODE={$ibforums->input['CODE_MODE']}&searchid=" . $this->unique_id . "&search_in=" . $this->search_in . "&result_type=" . $this->result_type . "&new={$ibforums->input['new']}&hl=" . $url_words,

		                                     ));

		if ($ibforums->input['new'] and $ibforums->member['id'])
		{
			$button = View::make("search.button");

			$selector = View::make("search.start_search_days");

			$selected = ($ibforums->member['search_days'] == -1)
				? " selected='selected'"
				: "";

			$selector .= View::make(
				"search.search_days",
				['days' => -1, 'title' => $ibforums->lang['search_last_visit'], 'check' => $selected]
			);

			for ($i = 1; $i < 15; ++$i)
			{
				$selected = ($i == $ibforums->member['search_days'])
					? " selected='selected'"
					: "";

				$selector .= View::make(
					"search.search_days",
					['days' => $i, 'title' => sprintf($ibforums->lang['search_days'], $i), 'check' => $selected]
				);
			}

			$selector .= View::make("search.end_search_days");
		} else
		{
			$button = "";

			$selector = "";
		}

		if ($this->modfunctions)
		{
			$searchdata                 = array(
				'search_in'   => $this->search_in,
				'result_type' => $this->result_type,
				'search_id'   => $this->unique_id,
				'hl'          => $url_words,
			);
			$modcontrol['modform_open'] = View::make("search.modform_open", ['data' => $searchdata]);
			$modcontrol['mod_column']   = View::make("search.mod_column_head");
		}

		$out = array(
			'SHOW_PAGES'  => $this->links,
			'BUTTON'      => $button,
			'SEARCH_DAYS' => $selector,
			'MOD_CONTROL' => $modcontrol,
		);

		if (!$is_post)
		{
			return View::make("search.start", ['Data' => $out]);
		} else
		{
			return View::make("search.start_as_post", ['Data' => $out]);
		}
	}

	/******************************************************/
	// Get active
	// Show all topics posted in / created between a user
	// definable amount of days..
	/******************************************************/

	function get_active()
	{
		global $ibforums, $std;

		//------------------------------------
		// If we don't have a search ID (searchid)
		// then it's a fresh query.
		//
		//------------------------------------

		if (!isset($ibforums->input['searchid']))
		{

			//------------------------------------
			// Do we have any start date input?
			//------------------------------------

			if ($ibforums->input['st_day'] == "")
			{
				// No? Lets work out the start date as 24hrs ago
				$ibforums->input['st_day'] = 1;
				$this->st_stamp            = time() - (60 * 60 * 24);

			} else
			{
				$ibforums->input['st_day'] = preg_replace("/s/", "", $ibforums->input['st_day']);
				$this->st_stamp            = time() - (60 * 60 * 24 * $ibforums->input['st_day']);
			}

			//------------------------------------
			// Do we have any END date input?
			//------------------------------------

			if ($ibforums->input['end_day'] == "")
			{
				// No? Lets work out the end date as now

				$this->end_stamp            = time();
				$ibforums->input['end_day'] = 0;

			} else
			{
				$ibforums->input['end_day'] = preg_replace("/e/", "", $ibforums->input['end_day']);
				$this->end_stamp            = time() - (60 * 60 * 24 * $ibforums->input['end_day']);
			}

			//------------------------------------
			// Synchronise our input data
			//------------------------------------

			$ibforums->input['forums'] = 'all';

			$forums = $this->get_searchable_forums();

			//------------------------------------
			// Do we have any forums to search in?
			//------------------------------------

			if ($forums == "")
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_search_forum'));
			}

			$query = "SELECT DISTINCT(t.tid)
					  FROM ibf_posts p
					    LEFT JOIN ibf_topics t ON (p.topic_id=t.tid)
					  WHERE p.post_date BETWEEN " . $this->st_stamp . " AND " . $this->end_stamp . "
					    AND p.forum_id IN($forums)
					    AND p.queued != 1
					  ORDER BY t.last_post DESC
					  LIMIT 0,200";

			//------------------------------------------------
			// Get the topic ID's to serialize and store into
			// the database
			//------------------------------------------------

			$stmt = $ibforums->db->query($query);

			$max_hits = $stmt->rowCount();

			$topics = "";

			while ($row = $stmt->fetch())
			{
				$topics .= $row['tid'] . ",";
			}

			$stmt->closeCursor();

			$topics = preg_replace("/,$/", "", $topics);

			//------------------------------------------------
			// Do we have any results?
			//------------------------------------------------

			if ($topics == "")
			{
				$this->output .= View::make("search.active_start", ['data' => array('SHOW_PAGES' => "")]);
				$this->output .= View::make("search.active_none");
				$this->output .= View::make("search.end", []);
				$this->page_title = $ibforums->lang['search_results'];
				$this->nav        = array(
					"<a href='{$this->base_url}&act=Select'>{$ibforums->lang['search_form']}</a>",
					$ibforums->lang['search_title']
				);
				return ""; // return empty handed
			}

			//------------------------------------------------
			// If we are still here, store the data into the database...
			//------------------------------------------------

			$unique_id = md5(uniqid(microtime(), 1));

			$data = [
				'id'          => $unique_id,
				'search_date' => time(),
				'topic_id'    => $topics,
				'topic_max'   => $max_hits,
				'sort_key'    => $this->sort_key,
				'sort_order'  => $this->sort_order,
				'member_id'   => $ibforums->member['id'],
				'ip_address'  => $ibforums->input['IP_ADDRESS'],
			];

			$ibforums->db->insertRow('ibf_search_results', $data);

		} else
		{
			//------------------------------------------------
			// We have a search ID, so lets get the parsed results.
			// Delete old search queries (older than 24 hours)
			//------------------------------------------------

			$t_time = time() - (60 * 60 * 24);

			$this->first = intval($ibforums->input['st']) != ""
				? intval($ibforums->input['st'])
				: 0;

			$ibforums->db->exec(
			    "DELETE FROM ibf_search_results
				WHERE search_date < '$t_time'"
            );

			$unique_id = $ibforums->input['searchid'];

			$stmt = $ibforums->db->query(
			    "SELECT *
                FROM ibf_search_results
                WHERE id='$unique_id'"
            );
			$sr   = $stmt->fetch();

			$topics   = $sr['topic_id'];
			$max_hits = $sr['topic_max'];

			$this->sort_order = $sr['sort_order'];
			$this->sort_key   = $sr['sort_key'];

			if ($topics == "")
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_search_results'));
			}
		}

		// Our variables are centralised, lets get the array slice depending on our $this->first
		// position.

		$topic_string = implode(",", array_slice(explode(",", $topics), $this->first, 25));

		$topic_string = str_replace(" ", "", $topic_string);
		$topic_string = preg_replace("/,$/", "", $topic_string);

		$url_words = urlencode(trim($keywords));

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $max_hits,
		                                    'PER_PAGE'   => 25,
		                                    'CUR_ST_VAL' => $this->first,
		                                    'L_SINGLE'   => "",
		                                    'L_MULTI'    => $ibforums->lang['search_pages'],
		                                    'BASE_URL'   => $this->base_url . "act=Select&CODE=getactive&searchid=$unique_id",
		                               ));

		$this->output .= View::make("search.active_start", ['data' => array('SHOW_PAGES' => $links)]);

		// Regex in our selected values.

		$this->output = preg_replace("/(<option value='s" . $ibforums->input['st_day'] . "')/", "\\1 selected", $this->output);
		$this->output = preg_replace("/(<option value='e" . $ibforums->input['end_day'] . "')/", "\\1 selected", $this->output);

		$stmt = $ibforums->db->query(
		    "SELECT t.*, f.id as forum_id, f.name as forum_name
		    FROM ibf_topics t, ibf_forums f
		    WHERE t.tid IN($topic_string)
		      AND f.id=t.forum_id
		    ORDER BY " . $this->sort_key . " " . $this->sort_order . "
		    LIMIT 0,25"
        );

		$count = 0;

		while ($row = $stmt->fetch())
		{
			if ($row['club'] and $std->check_perms($ibforums->member['club_perms']) == FALSE)
			{
				continue;
			}

			$count++;
			$row['keywords'] = $url_words;
			$this->output .= View::make("search.RenderRow", ['Data' => $this->parse_entry($row)]);

		}

		if ($count <= 0)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_search_results'));
		}

		$this->page_title = $ibforums->lang['search_results'];
		$this->nav        = array(
			"<a href='{$this->base_url}act=Select'>{$ibforums->lang['search_form']}</a>",
			$ibforums->lang['search_title']
		);

		$this->output .= View::make("search.end", ['Data' => array('SHOW_PAGES' => $links)]);

	}

	function parse_entry($topic, $view_as_post = 0)
	{
		global $std, $ibforums;

		$topic['last_text'] = $ibforums->lang['last_post_by'];

		$topic['last_poster'] = ($topic['last_poster_id'] != 0)
			? "<a href='{$this->base_url}showuser={$topic['last_poster_id']}'>{$topic['last_poster_name']}</b>"
			: "-" . $topic['last_poster_name'] . "-";

		$topic['starter'] = ($topic['starter_id'] != 0)
			? "<a href='{$this->base_url}showuser={$topic['starter_id']}'>{$topic['starter_name']}</a>"
			: "-" . $topic['starter_name'] . "-";

		if ($topic['poll_state'])
		{
			$topic['prefix'] = $ibforums->vars['pre_polls'] . ' ';
		}

		$topic['topic_icon'] = $topic['icon_id']
			? '<img src="' . $ibforums->skin->getImagesPath() . '/icon' . $topic['icon_id'] . '.gif" alt="">'
			: '&nbsp;';

		if ($topic['decided'])
		{
			$topic['topic_icon'] = "<{B_DECIDED}>";
		}

		if ($topic['pinned'])
		{
			$topic['topic_icon'] = "<{B_PIN}>";
			$topic['prefix']     = $ibforums->vars['pre_pinned'];
		}

		$topic['topic_start_date'] = $std->get_date($topic['start_date']);

		$pages = 1;

		if ($this->modfunctions)
		{
			if ((mb_strpos(',' . $ibforums->member['modforums'] . ',', ',' . $topic['forum_id'] . ',') !== false) or ($ibforums->member['g_is_supmod']))
			{
				$topic['mod_checkbox'] = View::make(
					"search.mod_checkbox",
					[
						'class' => $topic['pinned']
							? 'pinned_topic'
							: 'row4',
						'tid'   => $topic['tid']
					]
				);
			} else
			{
				$topic['mod_checkbox'] = View::make(
					"search.mod_nocheckbox",
					[
						'class' => $topic['pinned']
							? 'pinned_topic'
							: 'row4'
					]
				);
			}
		}

		if ($topic['posts'])
		{
			if ((($topic['posts'] + 1) % $ibforums->vars['display_max_posts']) == 0)
			{
				$pages = ($topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
			} else
			{
				$number = (($topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
				$pages  = ceil($number);
			}

		}

		if ($pages > 1)
		{
			$topic['PAGES'] = "<span class='small'>({$ibforums->lang['topic_sp_pages']} ";
			for ($i = 0; $i < $pages; ++$i)
			{
				$real_no = $i * $ibforums->vars['display_max_posts'];
				$page_no = $i + 1;

				if ($page_no == 4)
				{
					$topic['PAGES'] .= " ... <a href='{$this->base_url}showtopic={$topic['tid']}&amp;st=" . ($pages - 1) * $ibforums->vars['display_max_posts'] . "&hl={$topic['keywords']}'>$pages</a> ";
					break;
				} else
				{
					$topic['PAGES'] .= "<a href='{$this->base_url}showtopic={$topic['tid']}&amp;st=$real_no&amp;hl={$topic['keywords']}'>$page_no</a> ";
				}
			}

			$topic['PAGES'] = mb_substr($topic['PAGES'], 0, mb_strlen($topic['PAGES']) - 1);

			if ($topic['posts'] < $ibforums->vars['max_show_all_posts'])
			{
				$topic['PAGES'] .= " <a href='{$this->base_url}showtopic={$topic['tid']}&amp;view=showall&amp;hl={$topic['keywords']}'>" . $ibforums->lang['all_posts'] . "</a>";
			}

			$topic['PAGES'] .= ")</span>";
		}

		if ($topic['posts'] < 0)
		{
			$topic['posts'] = 0;
		}

		if (!$ibforums->member['id'])
		{
			$last_time = '';
		} else
		{
			if (!isset($this->read_mark[$topic['forum_id']]))
			{
				$this->read_mark[$topic['forum_id']] = $ibforums->forums_read[$topic['forum_id']];

				$this->read_mark[$topic['forum_id']] = ($ibforums->member['board_read'] > $this->read_mark[$topic['forum_id']])
					? $ibforums->member['board_read']
					: $this->read_mark[$topic['forum_id']];

				$this->read_mark[$topic['forum_id']] = ($this->read_mark[$topic['forum_id']] < (time() - 60 * 60 * 24 * 30))
					? (time() - 60 * 60 * 24 * 30)
					: $this->read_mark[$topic['forum_id']];
			}

			if ($this->read_array[$topic['tid']])
			{
				$last_time = $this->read_array[$topic['tid']];
			} else
			{
				$last_time = -1;

				if ($topic['last_post'] < $this->read_mark[$topic['forum_id']])
				{
					$last_time = $this->read_mark[$topic['forum_id']];

				}
			}
		}

		// icon of topic
		$topic['folder_img'] = $std->folder_icon($topic, "", $this->read_array[$topic['tid']], $this->read_mark[$topic['forum_id']]);
		if ($last_time && ($topic['last_post'] > $last_time))
		{
			$topic['go_last_page'] = "<a class='e-view_last_post-button' href='{$this->base_url}showtopic={$topic['tid']}&amp;view=getlastpost'><{GO_LAST_ON}></a>";
			$topic['go_new_post']  = "<a class='e-go_new_post-link' href='{$this->base_url}showtopic={$topic['tid']}&amp;view=getnewpost'><{NEW_POST}></a>";

		} else
		{
			$topic['go_last_page'] = "<a class='e-view_last_post-button' href='{$this->base_url}showtopic={$topic['tid']}&amp;view=getlastpost'><{GO_LAST_OFF}></a>";
			$topic['go_new_post']  = "";
		}

		// Do the quick goto last page icon stuff
		$maxpages = ($pages - 1) * $ibforums->vars['display_max_posts'];
		if ($maxpages < 0)
		{
			$maxpages = 0;
		}

		$topic['last_post_std'] = date('c', $topic['last_post']);
		$topic['last_post'] = $std->get_date($topic['last_post']);

		if ($topic['state'] == 'link')
		{
			$t_array              = explode("&", $topic['moved_to']);
			$topic['tid']         = $t_array[0];
			$topic['forum_id']    = $t_array[1];
			$topic['views']       = '--';
			$topic['posts']       = '--';
			$topic['prefix']      = $ibforums->vars['pre_moved'] . " ";
			$topic['go_new_post'] = "";
		}

		if ($topic['pinned'] == 1)
		{
			$topic['prefix']     = $ibforums->vars['pre_pinned'];
			$topic['topic_icon'] = "<{B_PIN}>";

		}

		if ($view_as_post == 1)
		{
			if ($ibforums->vars['search_post_cut'])
			{
				$topic['post'] = mb_substr($this->parser->unconvert($topic['post']), 0, $ibforums->vars['search_post_cut']) . '...';
				$topic['post'] = str_replace("\n", "<br />", $topic['post']);
			}

			if ($topic['author_id'])
			{
				$topic['author_name'] = "<a href='{$this->base_url}showuser={$topic['author_id']}'>{$topic['author_name']}</a>";
			}

			// Highlighting?

			if ($topic['keywords'])
			{

				$keywords = str_replace("+", " ", $topic['keywords']);

				if (preg_match("/,(and|or),/i", $keywords))
				{
					while (preg_match("/,(and|or),/i", $keywords, $match))
					{
						$word_array = explode("," . $match[1] . ",", $keywords);

						if (is_array($word_array))
						{
							foreach ($word_array as $keywords)
							{
								$topic['post'] = preg_replace("/(^|\s)(" . preg_quote($keywords, "/") . ")(\s|$)/i", "\\1<span class='searchlite'>\\2</span>\\3", $topic['post']);
							}
						}
					}
				} else
				{
					$topic['post'] = preg_replace("/(^|\s)(" . preg_quote($keywords, "/") . ")(\s|,|$)/i", "\\1<span class='searchlite'>\\2</span>\\3", $topic['post']);
				}
			}
		}

		$topic['posts'] = $std->do_number_format($topic['posts']);
		$topic['views'] = $std->do_number_format($topic['views']);

		return $topic;
	}

	function filter_keywords($words = "", $name = 0)
	{

		// force to lowercase and swop % into a safer version

		$words = trim(mb_strtolower(str_replace("%", "\\%", $words)));

		// Remove trailing boolean operators

		$words = preg_replace("/\s+(and|or)$/", "", $words);

		// Swop wildcard into *SQL percent

		//$words = str_replace( "*", "%", $words );

		// Make safe underscores

		$words = str_replace("_", "\\_", $words);

		$words = str_replace('|', "&#124;", $words);

		// Remove crap

		if ($name == 0)
		{
			$words = preg_replace("/[\|\[\]\{\}\(\)\,:\\\\\/\"']|&quot;/", "", $words);
		}

		// Remove common words..

		$words = preg_replace("/^(?:img|quote|code|html|javascript|a href|color|span|div)$/", "", $words);

		return " " . preg_quote($words) . " ";
	}

	function filter_ftext_keywords($words = "")
	{

		// force to lowercase and swop % into a safer version

		$words = trim($words);
		$words = str_replace('|', "&#124;", $words);

		// Remove crap

		$words = str_replace("&quot;", '"', $words);
		//$words = str_replace( "&lt;"  , "<", $words );
		$words = str_replace("&gt;", ">", $words);
		$words = str_replace("%", "", $words);

		// Remove common words..

		$words = preg_replace("/^(?:img|quote|code|html|javascript|a href|color|span|div)$/", "", $words);

		return $words;

	}

	//------------------------------------------------------
	// Make the hl words nice and stuff
	//------------------------------------------------------

	function convert_highlite_words($words = "")
	{
		global $std;
		$ibforums = Ibf::app();

		$words = $std->clean_value(trim(urldecode($words)));

		// Convert booleans to something easy to match next time around

		$words = preg_replace("/\s+(and|or)(\s+|$)/i", ",\\1,", $words);

		// Convert spaces to plus signs

		$words = preg_replace("/\s/", "+", $words);

		return $words;
	}

	//------------------------------------------------------
	// Get the searchable forums
	//------------------------------------------------------

	function get_searchable_forums()
	{
		global $ibforums, $std;

		$forum_array  = array();
		$forum_string = "";
		$sql_query    = "";
		$check_sub    = 0;

		$cats   = array();
		$forums = array();

		// If we have an array of "forums", loop
		// through and build our *SQL IN( ) statement.

		//------------------------------------------------
		// Check for an array
		//------------------------------------------------

		if (is_array($_POST['forums']))
		{

			if (in_array('all', $_POST['forums']))
			{
				//--------------------------------------------
				// Searching all forums..
				//--------------------------------------------

				$sql_query = "SELECT id, read_perms, password FROM ibf_forums";

			} else
			{
				//--------------------------------------------
				// Go loopy loo
				//--------------------------------------------

				foreach ($_POST['forums'] as $l)
				{
					if (preg_match("/^c_/", $l))
					{
						$cats[] = intval(str_replace("c_", "", $l));
					} else
					{
						$forums[] = intval($l);
					}
				}

				//--------------------------------------------
				// Do we have cats? Give 'em to Charles!
				//--------------------------------------------

				if (count($cats))
				{
					$sql_query = "SELECT
							id,
							read_perms,
							password,
							subwrap
						      FROM ibf_forums
						      WHERE
							category IN(" . implode(",", $cats) . ")";
					$boolean   = "OR";
				} else
				{
					$sql_query = "SELECT
							id,
							read_perms,
							password,
							subwrap
						      FROM ibf_forums";
					$boolean   = "WHERE";
				}

				if (count($forums))
				{
					if ($ibforums->input['searchsubs'] == 1)
					{
						$sql_query .= " $boolean (id IN(" . implode(",", $forums) . ") or
						  parent_id IN(" . implode(",", $forums) . ") )";
					} else
					{
						$sql_query .= " $boolean id IN(" . implode(",", $forums) . ")";
					}
				}

				if ($sql_query == "")
				{
					// Return empty..

					return;
				}
			}

			//--------------------------------------------
			// Run query and finish up..
			//--------------------------------------------

			$stmt = $ibforums->db->query($sql_query);

			while ($i = $stmt->fetch())
			{
				if ($this->check_access($i))
				{
					$forum_array[] = $i['id'];
				}
			}
		} else
		{
			//--------------------------------------------
			// Not an array...
			//--------------------------------------------

			if ($ibforums->input['forums'] == 'all')
			{
				$stmt = $ibforums->db->query(
				    "SELECT
							id,
							read_perms,
							password
					FROM ibf_forums"
                );

				while ($i = $stmt->fetch())
				{
					if ($this->check_access($i))
					{
						$forum_array[] = $i['id'];
					}
				}
			} else
			{
				if ($ibforums->input['forums'] != "")
				{
					$l = $ibforums->input['forums'];

					//--------------------------------------------
					// Single  Cat
					//--------------------------------------------

					if (preg_match("/^c_/", $l))
					{
						$c = intval(str_replace("c_", "", $l));

						if ($c)
						{
							$stmt = $ibforums->db->query(
							    "SELECT id, read_perms, password
                                FROM ibf_forums
                                WHERE category=$c"
                            );

							while ($i = $stmt->fetch())
							{
								if ($this->check_access($i))
								{
									$forum_array[] = $i['id'];
								}
							}
						}
					} else
					{
						//--------------------------------------------
						// Single forum
						//--------------------------------------------

						$f = intval($l);

						if ($f)
						{
							$qe = ($ibforums->input['searchsubs'] == 1)
								? " OR parent_id=$f "
								: "";

							$stmt = $ibforums->db->query(
							    "SELECT id, read_perms, password
                                FROM ibf_forums
                                WHERE id=$f" . $qe
                            );

							while ($i = $stmt->fetch())
							{
								if ($this->check_access($i))
								{
									$forum_array[] = $i['id'];
								}
							}
						}
					}
				}
			}
		}

		$forum_string = implode(",", $forum_array);

		return $forum_string;

	}

	function check_access($i)
	{
		global $std, $ibforums;

		$can_read = TRUE;

		if ($i['password'] != "")
		{
			if (!$c_pass = $std->my_getcookie('iBForum' . $i['id']))
			{
				$can_read = FALSE;
			}

			if ($c_pass == $i['password'])
			{
				$can_read = TRUE;
			} else
			{
				$can_read = FALSE;
			}
		}

		if ($can_read == TRUE)
		{
			if ($std->check_perms($i['read_perms']) == TRUE)
			{
				$can_read = TRUE;
			} else
			{
				$can_read = FALSE;
			}
		}

		return $can_read;
	}

	function get_read_topics($date = 0)
	{
		global $ibforums;

		$stmt = $ibforums->db->query(
		    "SELECT tid, fid, logTime
            FROM ibf_log_topics
            WHERE mid='" . $ibforums->member['id'] . "'
              AND logTime > '" . $date . "'"
        );

		if ($stmt->rowCount() > 0)
		{
			while ($read = $stmt->fetch())
			{
				$fid = $read['fid'];
				$tid = $read['tid'];

				if (!$this->read_mark[$fid])
				{
					$this->read_mark[$fid] = $ibforums->forums_read[$fid];

					$this->read_mark[$fid] = ($ibforums->member['board_read'] > $this->read_mark[$fid])
						? $ibforums->member['board_read']
						: $this->read_mark[$fid];

					$this->read_mark[$fid] = ($this->read_mark[$fid] < (time() - 60 * 60 * 24 * 30))
						? (time() - 60 * 60 * 24 * 30)
						: $this->read_mark[$fid];
				}

				$this->read_array[$tid] = ($this->read_mark[$fid] > $read['logTime'])
					? $this->read_mark[$fid]
					: $read['logTime'];
			}

		}

	}

	function subforums_search_list($children, $id, $level, &$temp_html, $all_checkboxes)
	{
		global $std;
		$ibforums = Ibf::app();

		if (isset($children[$id]) and count($children[$id]) > 0)
		{
			foreach ($children[$id] as $r)
			{
				if ($std->check_perms($r['read_perms']) != TRUE)
				{
					continue;
				}

				$r['qid'] = "f_";

				$r['sh'] = ($r['checked'] or !$all_checkboxes)
					? "checked='checked'"
					: "";

				$checkbox = (!$r['status'] or $r['redirect_on'] or !$r['sub_can_post'])
					? ""
					: View::make("search.checkbox", ['data' => $r]);

				$r['css'] = 'row1';

				$prefix = "";

				for ($i = 0; $i < $level; $i++)
				{
					$prefix .= "---";
				}

				$r['name'] = "&nbsp;" . $prefix . " " . $r['name'];

				$temp_html .= View::make("search.boardlay_between", ['data' => $r, 'checkbox' => $checkbox]);

				$this->subforums_search_list($children, $r['id'], $level + 1, $temp_html, $all_checkboxes);
			}
		}

	}

	function forums_list()
	{
		global $ibforums, $std, $print;

		if (!$ibforums->member['id'])
		{
			return;
		}

		$stmt           = $ibforums->db->query(
		    "SELECT fid
            FROM ibf_search_forums
            WHERE mid='" . $ibforums->member['id'] . "'
            LIMIT 1"
        );
		$all_checkboxes = $stmt->rowCount();

		$cats     = array();
		$forums   = array();
		$children = array();

		$stmt = $ibforums->db->query(
		    "SELECT *
            FROM ibf_categories
            ORDER BY position"
        );

		while ($r = $stmt->fetch())
		{
			$cats[$r['id']] = $r;
		}

		$stmt = $ibforums->db->query(
		    "SELECT f.*, sf.mid as checked
		    FROM ibf_forums f
            LEFT JOIN ibf_search_forums sf
                ON (sf.mid='" . $ibforums->member['id'] . "' AND sf.fid=f.id)
		    ORDER BY f.position"
        );

		while ($r = $stmt->fetch())
		{
			if ($r['parent_id'] > 0)
			{
				$children[$r['parent_id']][] = $r;
			} else
			{
				$forums[] = $r;
			}
		}

		$last_cat_id = -1;

		$this->output .= View::make("search.boardlay_start");

		foreach ($cats as $c)
		{
			$c['sub'] = "";
			$c['css'] = 'row4';

			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{
				if ($r['category'] == $last_cat_id)
				{
					if ($std->check_perms($r['read_perms']) != TRUE)
					{
						continue;
					}

					$r['qid'] = "f_";

					$r['sh'] = ($r['checked'] or !$all_checkboxes)
						? "checked='checked'"
						: "";

					$checkbox = (!$r['status'] or $r['redirect_on'] or !$r['sub_can_post'])
						? ""
						: View::make("search.checkbox", ['data' => $r]);

					$r['css'] = 'row1';

					$temp_html .= View::make("search.boardlay_between", ['data' => $r, 'checkbox' => $checkbox]);

					$this->subforums_search_list($children, $r['id'], 1, $temp_html, $all_checkboxes);
				}
			}

			if ($temp_html)
			{
				$this->output .= View::make("search.boardlay_between", ['data' => $c]);
				$this->output .= $temp_html;

				unset($temp_html);
			}

		}

		$this->output .= View::make("search.boardlay_end");

		$this->page_title = $ibforums->lang['search_title'];
		$this->nav        = array($ibforums->lang['search_form']);

		$print->add_output("$this->output");

		$print->do_output(array('TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav));

	}

	function accept_forums_list()
	{
		global $ibforums, $print;

		if (!$ibforums->member['id'])
		{
			return;
		}

		// delete old settings
		$ibforums->db->exec(
		    "DELETE FROM ibf_search_forums
			WHERE mid='" . $ibforums->member['id'] . "'"
        );

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^f_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$ibforums->db->exec(
					    "INSERT INTO ibf_search_forums
						VALUES (" . $ibforums->member['id'] . ","
							  . $match[1] . ")"
                    );
				}
			}
		}

		$this->output .= View::make("search.boardlay_successful");

		$this->page_title = $ibforums->lang['search_title'];
		$this->nav        = array($ibforums->lang['search_form']);

		$print->add_output("$this->output");
		$print->do_output(array('TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav));

	}

	function subforums_addtoform($id, &$children, $level = 0)
	{

		$html = '';

        if (empty($children[$id]))
        {
            $children[$id] = [];
        }
		if (count($children[$id]) > 0)
		{
			foreach ($children[$id] as $ii => $tt)
			{
				$prefix = "";

				// visuality depth
				for ($i = 0; $i < $level; $i++)
				{
					$prefix .= "&middot;&middot;";
				}

				$tt[1] = str_replace('<IBF_SONG_DEPTH>', $prefix, $tt[1]);

				$html .= $prefix . $tt[1] . $this->subforums_addtoform($tt[0], $children, $level + 1);
			}
		}

		return $html;

	}

	function change_days()
	{
		global $ibforums, $print;

		$days = intval($ibforums->input['search_days']);

		if ($days)
		{
			$ibforums->db->exec(
			    "UPDATE ibf_members
				SET search_days='" . $days . "'
				WHERE id='" . $ibforums->member['id'] . "'"
            );
		}

		$print->redirect_screen($ibforums->lang['search_redirect'], "act=Select&CODE=" . $ibforums->input['CODE_MODE']);

	}

}
