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
 |   > MySQL Indexed Search Library
 |   > Module written by Valery Votintsev
 |   > Date started: 31st December 2004
 |
 |	> Module Version Number: 1.0.0
 +--------------------------------------------------------------------------
 */

class search_lib extends Search
{

	var $parser = "";
	var $is = "";
	var $num_of_words = 0;
	public $realtime = true;

	/**
	 * Количество найденых постов/топиков
	 * @var integer
	 */
	protected $documents_found = 0;

	//--------------------------------------------
	// Constructor
	//--------------------------------------------

	function search_lib($that)
	{
		global $ibforums, $std, $print;

		$this->is = $that; // hahaha!
	}

	function results_count()
	{
		return $this->documents_found;
	}

	//--------------------------------------------
	// Main Board Search-e-me-doo-daa
	//--------------------------------------------

	function do_main_search()
	{
		global $ibforums, $std, $print;

		//------------------------------------
		// Do we have any input?
		//------------------------------------

		if ($ibforums->input['namesearch'])
		{
			$name_filter = $this->is->filter_ftext_keywords($ibforums->input['namesearch'], 1);
		}

		if ($ibforums->input['useridsearch'])
		{
			$keywords              = $ibforums->input['useridsearch']; // $this->is->filter_ftext_keywords($ibforums->input['useridsearch']);
			$this->is->search_type = 'userid';
		} else
		{
			$keywords              = $ibforums->input['keywords']; // $this->is->filter_ftext_keywords($ibforums->input['keywords']);
			$this->is->search_type = 'posts';
		}

		if ($name_filter AND $ibforums->input['keywords'])
		{
			$type = 'joined';
		} else
		{
			if ($name_filter == "" AND $ibforums->input['keywords'] != "")
			{
				$type = 'postonly';
			} else
			{
				if ($name_filter != "" AND $ibforums->input['keywords'] == "")
				{
					$type = 'nameonly';
				}
			}
		}

		//------------------------------------
		// SEARCH_IN parameter

		if ($ibforums->input['search_in'] == 'titles')
		{
			// @ - служебный символ для сфинкса в режиме match=extend
			// $keywords = str_replace('@', '', $keywords);
		}

		//------------------------------------

		$forums = $this->is->get_searchable_forums();

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

		$forums = ";filter=forum_id,$forums";

		//------------------------------------
		// Make sort key parameter

		$sort_attr = '';
		foreach (array(
			         'last_post',
			         'posts',
			         'starter_name',
			         'forum_id',
			         'relevancy'
		         ) as $v)
		{
			if ($ibforums->input['sort_key'] == $v)
			{
				$this->is->sort_key = $v;
				$sort_attr          = $v;
			}
		}
		if ($sort_attr)
		{
			if ($sort_attr == 'last_post')
			{
				$sort_attr = 'post_date';
			} elseif ($sort_attr == 'starter_name' && $this->is->search_in == 'posts')
			{
				$sort_attr = 'author_name';
			} elseif ($sort_attr == 'forum_id')
			{
				$sort_attr = 'forum_title';
			} elseif ($sort_attr == 'relevancy')
			{
				$sort_attr = '@weight ';
			}

			if ($ibforums->input['result_type'] == 'topics' && $this->is->search_in == 'posts')
			{
				if ($ibforums->input['sort_order'] == 'asc')
				{
					$sort_attr = ";groupsort=$sort_attr ASC";
				} else
				{
					$sort_attr = ";groupsort=$sort_attr DESC";
				}
			} else
			{
				if ($ibforums->input['sort_order'] == 'asc')
				{
					$sort_attr = ";sort=extended:$sort_attr ASC";
				} else
				{
					$sort_attr = ";sort=extended:$sort_attr DESC";
				}
			}
		}

		//------------------------------------
		// Make days old parameter

		foreach (array(1, 7, 30, 60, 90, 180, 365, 0) as $v)
		{
			if ($ibforums->input['prune'] == $v)
			{
				$this->is->prune = $v;
			}
		}

		//------------------------------------
		// Make sort order parameter

		if ($ibforums->input['sort_order'] == 'asc')
		{
			$this->is->sort_order = 'asc';
		}

		//------------------------------------
		// Make "SHOW_RESULT_AS" parameter

		if ($ibforums->input['result_type'] == 'posts')
		{
			$this->is->result_type = 'posts';
		}

		//------------------------------------
		// Add on the prune days
		//------------------------------------

		if ($this->is->prune > 0)
		{

			$time = time() - ($ibforums->input['prune'] * 86400);
			if ($ibforums->input['prune_type'] == 'older')
			{
				// <
				$posts_datecut  = ";range=post_date,$time,9990040286";
				$topics_datecut = ";range=last_post,$time,9990040286";
			} else
			{
				// >
				$posts_datecut  = ";range=post_date,0,$time";
				$topics_datecut = ";range=last_post,0,$time";
			}

		}

		//---------------------------------
		// Is this a membername search?

		$name_filter   = trim($name_filter);
		$member_string = "";

		if ($name_filter != "")
		{
			// Look for members IDs
			$member_string = $this->get_members_id($name_filter);

			// Error out of we matched no members

			if ($member_string == "")
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'no_name_search_results'
				            ));
			}

			$posts_name  = ";filter=author_id,$member_string"; // " AND p.author_id IN ($member_string)";
			$topics_name = ";filter=starter_id,$member_string"; // " AND t.starter_id IN ($member_string)";

		}

		$unique_id = md5(uniqid(microtime(), 1));

		// это разделитель полей в запросе к сфинксу
		$keywords = str_replace(';', '', $keywords);
		if ($type != 'nameonly')
		{

			// sphinx_snippets(p.post, 'test1', 'Пупкин'),
			$keywords     = mb_substr($ibforums->db->quote($keywords), 1, -1);
			$sphinx_query = mb_substr($ibforums->db->quote("{$posts_datecut}{$forums}{$topics_name}"), 1, -1);

			if ($ibforums->input['st'])
			{
				$start = intval($ibforums->input['st']);
				$sphinx_query .= ";offset=$start";
			}

			$mode = 'all';
			if ($ibforums->input['space_determine'])
			{
				$mode = ($ibforums->input['space_determine'] == 'phrase')
					? 'phrase'
					: 'any';
			}
			/*
			 * SQL_NO_CACHE нужен для того, чтобы обновилось/установилось значение переменной состояния
			 * sphinx_total_found (см. ниже, где присваивается значение $this->documents_found)
			 */
			if ($this->is->search_in == 'posts')
			{
				$topics_query = "SELECT SQL_NO_CACHE
						t.*,
						f.id as forum_id,
						f.name as forum_name

					FROM ibf_sph_search_posts t1
						INNER JOIN ibf_topics t ON (t1.topic_id = t.tid)
						INNER JOIN ibf_forums f ON (t.forum_id = f.id)
						INNER JOIN ibf_posts p ON (t1.id = p.pid)
					WHERE
						t1.query='{$keywords}{$sphinx_query};groupby=attr:topic_id{$sort_attr};limit=25;mode=$mode'
						AND t.approved=1
					";

				$sphinx_query = mb_substr($ibforums->db->quote("{$posts_datecut}{$forums}{$posts_name}"), 1, -1);

				if ($ibforums->input['st'])
				{
					$sphinx_query .= ";offset=$start";
				}

				$posts_query = "SELECT SQL_NO_CACHE
					t.*,
					p.pid,
					p.author_id,
					p.author_name,
					p.post_date,
					p.post,
					f.id as forum_id,
					f.name as forum_name
					-- , t1.*
				FROM ibf_sph_search_posts t1
						INNER JOIN ibf_topics t ON (t1.topic_id = t.tid)
						INNER JOIN ibf_forums f ON (t.forum_id = f.id)
						INNER JOIN ibf_posts p ON (t1.id = p.pid)
				WHERE
					t1.query='$keywords{$sphinx_query}{$sort_attr};limit=25;mode=$mode'
					AND p.use_sig=0
					AND p.queued <> 1";

			} else
			{ // $this->is->search_in == 'titles'
				$topics_query = "SELECT SQL_NO_CACHE
						t.*,
						f.id as forum_id,
						f.name as forum_name

					FROM ibf_sph_search_topics t1
						INNER JOIN ibf_topics t ON (t1.id = t.tid)
						INNER JOIN ibf_forums f ON (t.forum_id = f.id)
						INNER JOIN ibf_posts p ON (t1.post_id = p.pid)
					WHERE
						t1.query='{$keywords}{$sphinx_query};{$sort_attr};limit=25;mode=$mode'
						AND t.approved=1
					";

				$sphinx_query = mb_substr($ibforums->db->quote("{$posts_datecut}{$forums}{$posts_name}"), 1, -1);

				if ($ibforums->input['st'])
				{
					$sphinx_query .= ";offset=$start";
				}

				$posts_query = "SELECT SQL_NO_CACHE
					t.*,
					p.pid,
					p.author_id,
					p.author_name,
					p.post_date,
					p.post,
					f.id as forum_id,
					f.name as forum_name
					-- , t1.*
				FROM ibf_sph_search_topics t1
						INNER JOIN ibf_topics t ON (t1.id = t.tid)
						INNER JOIN ibf_forums f ON (t.forum_id = f.id)
						INNER JOIN ibf_posts p ON (t1.post_id = p.pid)
				WHERE
					t1.query='$keywords{$sphinx_query}{$sort_attr};limit=25;mode=$mode'
					AND p.use_sig=0
					AND p.queued <> 1";
			}

		} else
		{
			$posts_query = $topics_query = "SELECT t.tid
					FROM ibf_topics t
					WHERE
					$topics_datecut
						t.forum_id IN ($forums)
						$topics_name";

			$posts_query = "SELECT p.pid
					FROM ibf_posts p
					WHERE
					$posts_datecut
						p.forum_id IN ($forums) AND
						p.use_sig=0 AND
						p.queued <> 1
						$posts_name";
		}

		if ($ibforums->input['result_type'] == 'topics')
		{
			$sql = $topics_query;
			// var_dump($sql);
		} else
		{
			$sql = $posts_query;
		}

		// var_dump($sql);

		$stmt = $ibforums->db->query($sql);

		$this->documents_found = $ibforums->db->query('SHOW STATUS LIKE  \'sphinx_total_found\'')->fetch();
		$this->documents_found = $this->documents_found['Value'];

		if ($ibforums->input['search_in'] == 'titles')
		{
			$topic_max_hits = $this->documents_found;
		} else
		{
			$post_max_hits = $this->documents_found;
		}

		$topics = "";
		$posts  = "";

		//------------------------------------

		$topics = preg_replace("/,$/", "", $topics);
		$posts  = preg_replace("/,$/", "", $posts);

		//------------------------------------------------
		// Do we have any results?
		//------------------------------------------------

		//------------------------------------------------
		// If we are still here, return data like a good
		// boy (or girl). Yes Reg; or girl.
		// What have the Romans ever done for us?
		//------------------------------------------------

		return array(
			'topic_id'     => $topics,
			'post_id'      => $posts,
			'topic_max'    => $topic_max_hits,
			'post_max'     => $post_max_hits,
			'keywords'     => $keywords,
			// debug:

			't_query'      => $tsql,
			'p_query'      => $psql,
			'search_count' => $this->documents_found,
			'topics_query' => $topics_query,
			'posts_query'  => $posts_query,
			'wordlist'     => $wordlist,
			'wordidlist'   => $wordidlist,
			'result'       => $stmt

		);

	}

	//------------------------------------------------------------------
	// Get all the possible matches for the supplied name from the DB
	//------------------------------------------------------------------
	function get_members_id($name_filter = "")
	{
		global $ibforums;

		$name_filter   = str_replace('|', "&#124;", $name_filter);
		$member_string = "";

		if ($ibforums->input['exactname'] == 1)
		{
			$sql_query = "SELECT id
				      FROM ibf_members
				      WHERE lower(name)='" . $name_filter . "'";
		} else
		{
			$sql_query = "SELECT id
				      FROM ibf_members
				      WHERE name like '%" . $name_filter . "%'";
		}

		$stmt = $ibforums->db->query($sql_query);

		while ($row = $stmt->fetch())
		{
			$member_string .= "'" . $row['id'] . "',";
		}

		return preg_replace("/,$/", "", $member_string);

	}

}

