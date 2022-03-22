<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Song
|   (c) 2004 Sources.RU
|   http://www.sources.ru
|   ========================================
|   Web: http://www.sources.ru
|   Email: song@sources.ru
|   Licence Info: http://www.sources.ru
+---------------------------------------------------------------------------
|
|   > Toolbar display module
|   > Module written by Song
|   > Date started: 25th November 2004
|
|   > Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new SongFunc;

class SongFunc
{

	var $output = "";

	function __construct()
	{
		global $ibforums, $std, $print;

		$ibforums->input['t'] = intval($ibforums->input['t']);

		switch ($ibforums->input['CODE'])
		{
			case 'forums_recount':
				$this->output = $this->all_forums_order_recount();
				break;

			case 'last_post_id':
				$this->output = $this->check_new();
				break;

			case 'index_posts':
				$this->output = $this->index_data_posts($ibforums->input['f']);
				break;

			case 'index_topics':
				$this->output = $this->index_data_topics($ibforums->input['f']);
				break;

			case 'club_enable':
				$this->club_member_enable($ibforums->input['mid']);
				break;
			default:
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
				break;
		}

		if ($ibforums->input['CODE'] == 'last_post_id')
		{
			//---------------------------------------
			// Close this DB connection
			//---------------------------------------

			//---------------------------------------
			// Start GZIP compression
			//---------------------------------------

			if ($ibforums->vars['disable_gzip'] != 1)
			{
				$buffer = ob_get_contents();
				ob_end_clean();
				ob_start('ob_gzhandler');
				print $buffer;
			}

			$print->do_headers();
			print $this->output;

			exit;
		} else
		{
			$print->add_output("$this->output");
			$print->do_output(array());
		}

	}

	function club_member_enable($mid = 0)
	{
		global $ibforums, $std;

		$mid = intval($mid);

		if (!$ibforums->member['id'] or
		    !$mid or
		    !$ibforums->vars['club_boss'] or
		    !$ibforums->vars['club_group']
		)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		if ($ibforums->member['g_is_supmod'] or
		    $ibforums->member['id'] == $ibforums->vars['club_boss']
		)
		{
			$stmt = $ibforums->db->query(
			    "SELECT name,mgroup
			    FROM ibf_members
                WHERE id='" . $mid . "'"
            );

			if ($user = $stmt->fetch())
			{
				if ($user['mgroup'] != $ibforums->vars['member_group'])
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_permission'
					            ));
				}

				$ibforums->db->exec(
				    "UPDATE ibf_members
				    SET
					    mgroup='" . $ibforums->vars['club_group'] . "',
					    disable_group=0
				    WHERE id='" . $mid . "'"
                );

				$message = "Уважаемый(ая), %s!\nМы имеем честь пригласить Вас в закрытый клуб Sources.Ru ";

				$message .= "([URL=https://forum.sources.ru/index.php?c=9]Клуб на Исходниках.RU[/URL]). ";

				$message .= "Надеемся, что Вы станете завсегдатаем этого ";

				$message .= "приятного во всех отношениях заведения, и мы не раз сможем услышать ваш голос в его ";

				$message .= "виртуальных стенах.\n\n С уважением, члены клуба Sources.Ru";

				$title = "Приглашение в Клуб на Исходниках.RU";

				$std->sendpm($mid, sprintf($message, $user['name']), $title, $ibforums->vars['club_boss']);

				$std->boink_it($ibforums->base_url . "showuser=" . $mid);
			} else
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

	}

	function check_new()
	{
		global $ibforums, $std;

		if (!$ibforums->input['t'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		$stmt = $ibforums->db->query(
		    "SELECT max(pid) as pid
            FROM ibf_posts
            WHERE queued != 1
              AND topic_id='" . $ibforums->input['t'] . "'"
        );

		if (!$post = $stmt->fetch() or !$post['pid'])
		{
			return "Error";
		} else
		{
			$stmt = $ibforums->db->query(
			    "SELECT Concat(posts,';',last_poster_name) as info
                FROM ibf_topics
                WHERE tid='" . $ibforums->input['t'] . "'"
            );

			if ($info = $stmt->fetch())
			{
				$post['pid'] .= ";" . $info['info'];
			}

			return $post['pid'];
		}

	}

	function all_forums_order_recount()
	{
		global $std;
		$ibforums = Ibf::app();

		$ibforums->db->query(
		    "TRUNCATE ibf_forums_order"
        );

		$forums = array();

		$stmt = $ibforums->db->query(
		    "SELECT id, parent_id
            FROM ibf_forums"
        );

		while ($row = $stmt->fetch())
		{
			$forums[$row['id']] = $row['parent_id'];
		}

		foreach ($forums as $id => $row)
		{
			$std->update_forum_order_cache($id, $row);
		}

		return "Done!";

	}

	function clean_words(&$entry, &$stopword, &$synonym)
	{

		static $drop_char_match = array(
			'&quot;',
			'^',
			'$',
			'&',
			'(',
			')',
			'<',
			'>',
			'`',
			'\'',
			'"',
			'|',
			',',
			'@',
			'_',
			'?',
			'%',
			'-',
			'~',
			'+',
			'.',
			'[',
			']',
			'{',
			'}',
			':',
			'\\',
			'/',
			'=',
			'#',
			'\'',
			';',
			'!'
		);
		static $drop_char_replace = array(
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			'',
			'',
			' ',
			' ',
			' ',
			' ',
			'',
			' ',
			' ',
			'',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' '
		);

		$entry = " " . strip_tags($entry) . " ";

		// Remove time tag
		$entry = preg_replace("#\[mergetime\](\d+)\[/mergetime\]#is", "", $entry);

		// Replace line endings by a space
		$entry = preg_replace("/[\n\r]/is", " ", $entry);

		// Quickly remove BBcode.
		$entry = preg_replace("/\[\/?[a-zA-Z*]+[^\]]*\]/", " ", $entry);
		$entry = preg_replace("#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]]+)#is", " ", $entry);

		// HTML entities like &nbsp;
		$entry = preg_replace("/\b&[a-z]+;\b/", " ", $entry);

		// Filter out strange characters like ^, $, &, change "it's" to "its"
		$entry = str_replace($drop_char_match, $drop_char_replace, $entry);

		if (!empty($stopword))
		{
			for ($j = 0; $j < count($stopword); $j++)
			{
				$stopword = trim($stopword[$j]);

				if ($stopword != "not" && $stopword != "and" && $stopword != "or")
				{
					$entry = str_replace(" " . trim($stopword) . " ", " ", $entry);
				}
			}
		}

		if (!empty($synonym))
		{
			for ($j = 0; $j < count($synonym); $j++)
			{
				list($replace_synonym, $match_synonym) = preg_split("/ /", trim($synonym[$j]));

				if ($match_synonym != "not" && $match_synonym != "and" && $match_synonym != "or")
				{
					$entry = str_replace(" " . trim($match_synonym) . " ", " " . trim($replace_synonym) . " ", $entry);
				}
			}
		}

		return $entry;

	}

	function split_words(&$entry)
	{
		return explode(" ", trim(preg_replace("#\s+#", " ", $entry)));
	}

	function analyze_post($post_text)
	{

		if (!$post_text)
		{
			return;
		}

		$stopword = array();
		$synonym  = array();

		// loading stop and synonym words to its array

		$words = $this->split_words($this->clean_words($post_text, $stopword, $synonym));

		if (count($words))
		{
			sort($words);

			$temp_words = array();

			$prev_word = "";

			for ($i = 0; $i < count($words); $i++)
			{
				$words[$i] = trim($words[$i]);

				if ($words[$i] != $prev_word)
				{
					$temp_words[] = $words[$i];
				}

				$prev_word = $words[$i];
			}

			unset($words);

			for ($i = 0; $i < count($temp_words); $i++)
			{
				$length = mb_strlen($temp_words[$i]);

				if ($length > 2 and $length < 50)
				{
					$words[] = $temp_words[$i];
				}
			}

			unset($temp_words);
		}

		return $words;

	}

	function index_data_posts($fid = 0)
	{
		global $ibforums, $std;

		@set_time_limit(0);

		$fid = intval($fid);

		if (!$fid or $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		$stmt = $ibforums->db->query(
		    "SELECT pid, LOWER(post) as post, topic_id, forum_id
            FROM ibf_posts
			WHERE forum_id='" . $fid . "'
			  AND indexed=0"
        );

		while ($post = $stmt->fetch())
		{
			// at first delete indexed earlier words
			$ibforums->db->exec(
			    "DELETE FROM ibf_search_post_words
				WHERE pid='" . $post['pid'] . "'"
            );

			// get words array
			$words = $this->analyze_post($post['post']);

			if (count($words))
			{
				$insert = $ibforums->db->prepare(
				    "INSERT IGNORE INTO ibf_search_words
                    (word) VALUES (:word)"
                );
				$select = $ibforums->db->prepare(
				    "SELECT id
                    FROM ibf_search_words
                    WHERE word=:word"
                );
				foreach ($words as $word)
				{
					$select->execute([$word]);
					if ($select->rowCount()){
					}else{
						$insert->execute([$word]);
					}
				}
			}

			$ibforums->db->exec(
			    "UPDATE ibf_posts
				SET indexed=1
				WHERE pid='" . $post['pid'] . "'"
            );
		}

		return "Done!";

	}

	function index_data_topics($fid = 0)
	{
		global $ibforums, $std;

		@set_time_limit(0);

		$fid = intval($fid);

		if (!$fid or $ibforums->member['mgroup'] != $ibforums->vars['admin_group'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		$stmt = $ibforums->db->query(
		    "SELECT tid, forum_id,
                LOWER(title) as title, LOWER(description) as description
			FROM ibf_topics
            WHERE forum_id='" . $fid . "'
              AND indexed=0"
        );

		while ($topic = $stmt->fetch())
		{
			// at first delete indexed earlier words
			$ibforums->db->exec(
			    "DELETE FROM ibf_search_post_words
				WHERE tid='" . $topic['tid'] . "'
				  AND pid=0"
            );

			// get words array
			$words       = $this->analyze_post($topic['title']);
			$description = $this->analyze_post($topic['description']);

			// join both arrays to one
			$words = array_merge($words, $description);
			unset($description);

			if (count($words))
			{
				foreach ($words as $word)
				{
					// insert word
					$cnt = $ibforums->db->exec(
					    "INSERT INTO ibf_search_words
							(word)
						VALUES ('" . addslashes($word) . "')"
                    );

					$id = 0;

					// if word has been in a database
					if ($cnt == 0)
					{
						// get id of existing word
						$stmt = $ibforums->db->query(
						    "SELECT id
                            FROM ibf_search_words
                            WHERE word='" . addslashes($word) . "'"
                        );

						if ($row = $stmt->fetch())
						{
							$id = $row['id'];
						}
					} else
					{
						// else get id of inserted word
						$id = $ibforums->db->lastInsertId();
					}

					// add word record
					if ($id)
					{
						$ibforums->db->exec(
						    "INSERT INTO ibf_search_post_words
						    VALUES (0,  "
                                . $topic['tid'] . ","
							    . $topic['forum_id'] . ","
                                . $id . ")"
                        );
					}
				}
			}

			$ibforums->db->exec(
			    "UPDATE ibf_topics
				SET indexed=1
				WHERE tid='" . $topic['tid'] . "'"
            );
		}

		return "Done!";

	}

}
