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
	public $realtime = false;

	//--------------------------------------------
	// Constructor
	//--------------------------------------------

	function search_lib($that)
	{
		global $ibforums, $std, $print;

		$this->is = $that; // hahaha!
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
			$name_filter = $this->is->filter_keywords($ibforums->input['namesearch'], 1);
		}

		if ($ibforums->input['useridsearch'])
		{
			$keywords              = $this->is->filter_keywords($ibforums->input['useridsearch']);
			$this->is->search_type = 'userid';
		} else
		{
			$keywords              = $this->is->filter_keywords($ibforums->input['keywords']);
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
			$this->is->search_in = 'titles';
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

		//------------------------------------
		// Make sort key parameter

		foreach (array(
			         'last_post',
			         'posts',
			         'starter_name',
			         'forum_id'
		         ) as $v)
		{
			if ($ibforums->input['sort_key'] == $v)
			{
				$this->is->sort_key = $v;
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
		// Correct the min search length

		if ($ibforums->vars['min_search_word'] < 1)
		{
			$ibforums->vars['min_search_word'] = 3;
		}

		//------------------------------------
		// Add on the prune days
		//------------------------------------

		if ($this->is->prune > 0)
		{
			$gt_lt = $ibforums->input['prune_type'] == 'older'
				? "<"
				: ">";
			$time  = time() - ($ibforums->input['prune'] * 86400);

			$topics_datecut = "t.last_post $gt_lt $time AND";
			$posts_datecut  = "p.post_date $gt_lt $time AND";
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

			$posts_name  = " AND p.author_id IN ($member_string)";
			$topics_name = " AND t.starter_id IN ($member_string)";

		}

		//echo "ibforums->input['namesearch']=".$ibforums->input['namesearch']."<br>";
		//echo "ibforums->input['useridsearch']=".$ibforums->input['useridsearch']."<br>";
		//echo "type=".$type."<br>";

		//-----------------------------------
		// Parse the keywords

		if ($type != 'nameonly')
		{

			//------------------------------------

			$check_keywords = trim($keywords);

			$check_keywords = str_replace("%", "", $check_keywords);

			if ((!$check_keywords)
			    or ($check_keywords == "")
			    or (!isset($check_keywords))
			)
			{
				if ($type != 'nameonly')
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_search_words'
					            ));
				}
			}

			//--------------------------
			// look for keywords

			$keywords = trim($keywords);
			//echo "keywords: $keywords<br>";

			if ($ibforums->input['space_determine'])
			{
				$keywords = str_replace(" ", " or ", $keywords);
			} else
			{
				$keywords = str_replace("\\+", " ", $keywords);
				$keywords = str_replace("+", " ", $keywords);
				$keywords = preg_replace("/\s+/", " ", $keywords);
				$keywords = str_replace(" ", " and ", $keywords);
			}
			$keywords = " " . $keywords . " ";

			//echo "keywords: $keywords<br>";

			$wordlist = "";

			if (preg_match("/ and|or /", $keywords))
			{
				preg_match_all("/(^|and|or)\s{1,}(\S+?)\s{1,}/", $keywords, $matches);

				//				$title_like = "(";
				//				$post_like  = "(";
				$title_like = "";
				$post_like  = "";

				for ($i = 0; $i < count($matches[0]); $i++)
				{
					$boolean = $matches[1][$i];
					$word    = trim($matches[2][$i]);

					if (mb_strlen($word) < $ibforums->vars['min_search_word'])
					{
						$std->Error(array(
						                 'LEVEL' => 1,
						                 'MSG'   => 'search_word_short',
						                 'EXTRA' => $ibforums->vars['min_search_word']
						            ));
					}

					if ($boolean)
					{
						$boolean = " $boolean";
					}

					$wordlist .= "'" . $word . "',";

					$title_like .= "$boolean t.title LIKE '%$word%' ";
					$post_like .= "$boolean p.post LIKE '%$word%' ";

					////					$title_like .= "$boolean LOWER(t.title) LIKE '%$word%' ";
					////					$post_like  .= "$boolean LOWER(p.post) LIKE '%$word%' ";
				}

				//				$title_like .= ")";
				//				$post_like  .= ")";

			} else // NOT (preg_match( "/ and|or /", $keywords) )
			{

				if (mb_strlen(trim($keywords)) < $ibforums->vars['min_search_word'])
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'search_word_short',
					                 'EXTRA' => $ibforums->vars['min_search_word']
					            ));
				}

				$wordlist .= "'" . trim($keywords) . "'";

				$title_like = " t.title LIKE '%" . trim($keywords) . "%' ";
				$post_like  = " p.post LIKE '%" . trim($keywords) . "%' ";

				////				$title_like = " LOWER(t.title) LIKE '%".trim($keywords)."%' ";
				////				$post_like  = " LOWER(p.post) LIKE '%".trim($keywords)."%' ";
			}

			//------------------------------------
			// Get IDs for the keywords

			$wordlist = preg_replace("/,$/", "", $wordlist);

			$wordidlist = $this->get_words_ids($wordlist);

			if ($wordidlist == "")
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'no_search_results'
				            ));
			}

		}

		$unique_id = md5(uniqid(microtime(), 1));

		if ($type != 'nameonly')
		{

			//----------------------------------------
			// Search type AND/OR parameter

			$and_or = "";
			if (!$ibforums->input['space_determine'])
			{
				$and_or = "HAVING COUNT(s.word_id)=" . $this->num_of_words . " ";
			}

			//---------------------------------------
			// search for posts where WORD_ID presents

			$topics_query = "SELECT
				s.pid,
				s.tid,
				s.fid
			FROM
				ibf_search s,
				ibf_topics t
			WHERE
				$topics_datecut
				s.pid=0
				AND s.fid IN ($forums)
				$topics_name
				AND t.approved=1
				AND s.word_id IN ($wordidlist)
				AND s.tid=t.tid
				GROUP BY s.tid
				$and_or ";

			$posts_query = "SELECT
				s.pid,
				s.tid,
				s.fid
			FROM
				ibf_search s,
				ibf_posts p
			WHERE
				$posts_datecut
				s.pid>0
				AND s.fid IN ($forums)
				$posts_name
				AND p.use_sig=0
				AND p.queued <> 1
				AND s.word_id IN ($wordidlist)
				AND s.pid=p.pid
				GROUP BY s.pid
				$and_or ";

		} else
		{
			$topics_query = "SELECT t.tid
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

		//			$tsql .= "LIMIT $stpos,$res_num";

		if ($ibforums->input['search_in'] == 'titles')
		{
			$sql = $topics_query;
		} else
		{
			$sql = $posts_query;
		}

		$stmt = $ibforums->db->query($sql);

		if ($ibforums->input['search_in'] == 'titles')
		{
			$topic_max_hits = $stmt->rowCount();
		} else
		{
			$post_max_hits = $stmt->rowCount();
		}

		$search_count = $stmt->rowCount();

		$topics = "";
		$posts  = "";

		while ($row = $stmt->fetch())
		{
			if ($ibforums->input['search_in'] == 'titles')
			{
				$topics .= $row['tid'] . ",";
			} else
			{
				$posts .= $row['pid'] . ",";
			}
		}

		//------------------------------------------------
		// Get the topic ID's to serialize and store into
		// the database
		//------------------------------------------------

		//------------------------------------
		//		$topics = "";

		//		$stmt = $ibforums->db->query($topics_query);

		//		$topic_max_hits = $stmt->rowCount();

		//		while ($row = $stmt->fetch() )
		//		{
		//			$topics .= $row['tid'].",";
		//		}

		//		$stmt->closeCursor();

		//------------------------------------

		//		$posts  = "";

		//		$stmt = $ibforums->db->query($posts_query);

		//		$post_max_hits = $stmt->rowCount();

		//		while ($row = $stmt->fetch() )
		//		{
		//			$posts .= $row['pid'].",";
		//		}

		$stmt->closeCursor();

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
			'search_count' => $search_count,
			'topics_query' => $topics_query,
			'posts_query'  => $posts_query,
			'wordlist'     => $wordlist,
			'wordidlist'   => $wordidlist

		);

	}

	//------------------------------
	// Get the keywords IDs from DB
	//------------------------------

	function get_words_ids($words = "")
	{
		global $ibforums;

		$idlist = "";

		$sql  = "SELECT id, word
		        FROM ibf_search_words
			WHERE word IN ($words) ";
		$stmt = $ibforums->db->query($sql);

		while ($row = $stmt->fetch())
		{
			$idlist .= "'" . $row['id'] . "',";
		}

		$idlist = preg_replace("/,$/", "", $idlist);

		$this->num_of_words = $stmt->rowCount();

		return $idlist;
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
