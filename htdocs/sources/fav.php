<?php

$fav = new fav;

class fav
{

	var $max_fav = 2000;
	var $output = "";
	var $html = "";
	var $base_url = "";
	var $nav = "";

	function __construct()
	{
		$ibforums = Ibf::app();
		if ($ibforums->input['show'] or $ibforums->input['topic'])
		{
			$this->base_url = $ibforums->base_url;

			$this->html = $ibforums->functions->load_template('skin_fav');

			$this->nav[] = $ibforums->lang['favorites'];

			if (!$ibforums->member['id'])
			{
				$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'fav_guest'));
			}

			//topic actions
			if ($ibforums->input['topic'])
			{
				$this->doTopic($ibforums->input['topic']);
			} else
			{
				$this->show();
			}
		}
		else{
			global $print;
			$print->redirect_screen();

		}
	}

	private function doTopic($topic_id)
	{
		$ibforums = Ibf::app();

		$topic = $this->getTopicInfo($topic_id);

		//add or delete topic
		if ($topic === NULL)
		{
			if (in_array($topic_id, Ibf::app()->member['favorites']))
			{ //delete from favorites?
				$this->deleteTopic($topic_id);
				$topic = [
					'topic_id' => (int)$topic_id,
					'result'   => 'deleted',
					'track'    => FALSE, //missing topic can't be tracked
				];

			} else
			{
				$ibforums->functions->Error(array(LEVEL => 1, MSG => 'mt_no_topic'));
			}
		} elseif ($topic['is_favorite'])
		{
			//delete it
			$this->deleteTopic($topic['topic_id']);
			$topic['result'] = 'deleted';
			$topic['track']  = (bool)$ibforums->input['track'];
		} else
		{
			//add it
			$this->addTopic($topic['topic_id']);
			$topic['result'] = 'added';
		}
		$this->redirect($topic);
	}

	//End function fav()

	/**
	 * @param $topic_id int
	 */
	public function addTopic($topic_id)
	{
		settype($topic_id, 'int');
		if ($this->canTopicBeAdded($topic_id))
		{
			$this->add($topic_id);
		} else
		{
			Ibf::app()->functions->Error(['LEVEL' => 1, 'MSG' => 'too_many_favs', 'EXTRA' => $this->max_fav]);
		}
	}

	/**
	 * Simply adds topic to favorites
	 * @param $topic_id int Topic id
	 */
	protected function add($topic_id)
	{
		$mid = (int)Ibf::app()->member['id'];
		Ibf::app()->db
			->prepare("INSERT INTO ibf_favorites (mid,tid) VALUES (:mid, :tid)")
			->bindParam(':mid', $mid, PDO::PARAM_INT)
			->bindParam(':tid', $topic_id, PDO::PARAM_INT)
			->execute();
		Ibf::app()->member['favorites'][] = $topic_id;
	}

	/**
	 * Deletes the topic
	 * @param $topic_id int Topic id
	 */
	public function deleteTopic($topic_id)
	{
		settype($topic_id, 'int');
		$this->delete($topic_id);
	}

	/**
	 * Simply deletes the topic from favorites
	 * @param $topic_id int Topic id
	 */
	protected function delete($topic_id)
	{
		$mid = (int)Ibf::app()->member['id'];
		Ibf::app()->db
			->prepare("DELETE FROM ibf_favorites WHERE mid= :mid AND tid = :tid")
			->bindParam(':mid', $mid, PDO::PARAM_INT)
			->bindParam(':tid', $topic_id, PDO::PARAM_INT)
			->execute();
		$key = array_search($topic_id, Ibf::app()->member['favorites']);
		unset(Ibf::app()->member['favorites'][$key]);
	}

	/**
	 * @param $topic_id int Topic id
	 * @return array|null
	 */
	protected function getTopicInfo($topic_id)
	{
		settype($topic_id, 'int');
		$info = Ibf::app()->db
			->prepare("SELECT tid, forum_id FROM ibf_topics WHERE tid=:tid")
			->bindParam(':tid', $topic_id, PDO::PARAM_INT)
			->execute()
			->fetch();
		if (!$info)
		{
			return NULL;
		} else
		{
			return [
				'topic_id'    => (int)$info['tid'],
				'forum_id'    => (int)$info['forum_id'],
				'is_favorite' => in_array($topic_id, Ibf::app()->member['favorites'])
			];
		}
	}

	/**
	 * Redirects somewhere
	 * @param $topic array Topic info
	 */
	protected function redirect($topic)
	{
		global $print;
		$ibforums = Ibf::app();

		if ($ibforums->input['js'] and $ibforums->input['linkID'])
		{
			$fav = $topic['result'] === 'deleted'
				? "fav1"
				: "fav2";
			$print->redirect_js_screen("{$ibforums->input['linkID']}", $fav, $ibforums->base_url . "act=fav&topic={$topic['topic_id']}&js=1");
		} else
		{
			$refer = $_SERVER['HTTP_REFERER'];

			if (!preg_match("#" . $ibforums->base_url . "\?#", $refer))
			{
				$refer = "";
			}

			$refer = preg_replace("#" . $ibforums->base_url . "\?#", "", $refer);

			if ($topic['track'] && isset($topic['forum_id']))
			{
				$refer = "act=Track&f={$topic['forum_id']}&t={$topic['topic_id']}";
			}
			$print->redirect_screen("", $refer);
		}
	}

	/**
	 * Checks, whether can topic be added or not
	 * @param $topic_id int topic_id is not used for now
	 * @return bool
	 */
	public function canTopicBeAdded($topic_id)
	{
		return count(Ibf::app()->member['favorites']) < $this->max_fav;
	}

	/**
	 * Displays favorite topics
	 */
	function show()
	{
		global $print;

		$ibforums = Ibf::app();

		$query = "
			SELECT f.tid, t.tid AS topic_id, t.title, t.starter_id, t.last_poster_id, t.last_post,
			   t.starter_name, t.last_poster_name, tr.logTime
            FROM ibf_favorites f
            LEFT JOIN ibf_topics t ON f.tid=t.tid
            LEFT JOIN ibf_log_topics tr ON (f.mid=tr.mid AND f.tid=tr.tid)
		    WHERE
			  f.mid=:mid
		    GROUP BY f.tid
            ORDER BY t.last_post DESC";
		$stmt  = $ibforums->db->prepare($query)
			->bindParam(':mid', $ibforums->member['id'], PDO::PARAM_INT)
			->execute();

		if ($stmt->rowCount() == 0)
		{
			$ibforums->lang = $ibforums->functions->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);

			$e = $ibforums->lang['fav_nolinks'];

			$this->output .= $this->html->error($e);

		} else
		{
			foreach($stmt as $topic)
			{
				$last_time = $topic['logTime'];

				if (intval($topic['topic_id']))
				{
					if ($last_time && ($topic['last_post'] > $last_time))
					{
						$new[] = $topic;
					} else
					{
						$nonew[] = $topic;
					}
				} else
				{
					$remove[] = $topic['topic_id'];
				}
			}

			if (!empty($new))
			{
				$html['new'] = '';
				foreach ($new as $topic)
				{
					$topic['last_post'] = $ibforums->functions->get_date($topic['last_post']);
					$html['new'] .= $this->html->topic_row($topic);
				}

			} else
			{
				$html['new'] = $this->html->none();
			}

			if (isset($nonew))
			{
				$html['nonew'] = '';
				foreach ($nonew as $topic)
				{
					$topic['last_post'] = $ibforums->functions->get_date($topic['last_post']);
					$html['nonew'] .= $this->html->topic_row($topic);
				}

			} else
			{
				$html['nonew'] = $this->html->none();
			}

			//-------------------------------------
			// Remove Deleted Topics from Favorites

			if (!empty($remove))
			{
				$this->purgeTopics($remove);
			}

			$this->output .= $this->html->main($html);
		}

		$print->add_output($this->output);

		$print->do_output(array('TITLE' => $ibforums->vars['board_name'], 'JS' => 0, 'NAV' => $this->nav));

	} //End of function show_favs()

	/**
	 * Delete topics from all memebers favorites
	 * @param $topic_ids array topic's ids
	 */
	protected function purgeTopics($topic_ids)
	{
		if(!empty($topic_ids)){
			$ibforums->db->prepare("DELETE FROM ibf_favorites WHERE tid IN(" . IBPDO::placeholders($topic_ids) . ")")
				->execute($topic_ids);
		}

	}

}//End class fav
