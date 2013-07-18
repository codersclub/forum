<?php

$fav = new fav;

/**
 * Favorites controller
 * @class fav
 */
class fav
{
	var $output = "";
	var $html = "";
	var $nav = "";

	function __construct()
	{
		if (Ibf::app()->input['show'] or Ibf::app()->input['topic'])
		{
			$this->html = Ibf::app()->functions->load_template('skin_fav');

			$this->nav[] = Ibf::app()->lang['favorites'];

			if (!Ibf::app()->member['id'])
			{
				Ibf::app()->functions->Error(array('LEVEL' => 1, 'MSG' => 'fav_guest'));
			}

			//topic actions
			if (Ibf::app()->input['topic'])
			{
				$this->doTopic(Ibf::app()->input['topic']);
			} else
			{
				$this->show();
			}
		} else
		{
			global $print;
			$print->redirect_screen();

		}
	}

	private function doTopic($topic_id)
	{
		$topic = $this->getTopicInfo($topic_id);

		//add or delete topic
		if ($topic === NULL)
		{
			if (in_array($topic_id, Ibf::app()->member['favorites']->getTopicIds()))
			{ //delete from favorites?
				Ibf::app()->member['favorites']->deleteTopic($topic_id);
				$topic = [
					'topic_id' => (int)$topic_id,
					'result'   => 'deleted',
					'track'    => FALSE, //missing topic can't be tracked
				];

			} else
			{
				Ibf::app()->functions->Error(array(LEVEL => 1, MSG => 'mt_no_topic'));
			}
		} elseif ($topic['is_favorite'])
		{
			//delete it
			Ibf::app()->member['favorites']->deleteTopic($topic['topic_id']);
			$topic['result'] = 'deleted';
			$topic['track']  = (bool)Ibf::app()->input['track'];
		} else
		{
			//add it
			Ibf::app()->member['favorites']->addTopic($topic['topic_id']);
			$topic['result'] = 'added';
		}
		$this->redirect($topic);
	}

	//End function fav()

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
				'is_favorite' => in_array($topic_id, Ibf::app()->member['favorites']->getTopicIds())
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

		if (Ibf::app()->input['js'] and Ibf::app()->input['linkID'])
		{
			$fav = $topic['result'] === 'deleted'
				? "fav1"
				: "fav2";
			$print->redirect_js_screen(
				Ibf::app()->input['linkID'],
				$fav,
				Ibf::app()->base_url . "act=fav&topic={$topic['topic_id']}&js=1"
			);
		} else
		{
			$refer = $_SERVER['HTTP_REFERER'];

			if (!preg_match("#" . Ibf::app()->base_url . "\?#", $refer))
			{
				$refer = "";
			}

			$refer = preg_replace("#" . Ibf::app()->base_url . "\?#", "", $refer);

			if ($topic['track'] && isset($topic['forum_id']))
			{
				$refer = "act=Track&f={$topic['forum_id']}&t={$topic['topic_id']}";
			}
			$print->redirect_screen("", $refer);
		}
	}

	/**
	 * Displays favorite topics
	 */
	function show()
	{
		global $print;

		$data = Ibf::app()->member['favorites']->findAll();
		if (empty($data))
		{
			Ibf::app()->lang = Ibf::app()->functions->load_words(Ibf::app()->lang, 'lang_global', Ibf::app()->lang_id);
			$e               = Ibf::app()->lang['fav_nolinks'];

			$this->output .= $this->html->error($e);
		} else
		{
			foreach ($data as $topic)
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
					$topic['last_post'] = Ibf::app()->functions->get_date($topic['last_post']);
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
					$topic['last_post'] = Ibf::app()->functions->get_date($topic['last_post']);
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
				Ibf::app()->member['favorites']->purgeTopics($remove);
			}

			$this->output .= $this->html->main($html);
		}

		$print->add_output($this->output);

		$print->do_output(array('TITLE' => Ibf::app()->vars['board_name'], 'JS' => 0, 'NAV' => $this->nav));

	} //End of function show_favs()

}//End class fav
