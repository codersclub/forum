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
  |   > Access the help files
  |   > Module written by Matt Mecham
  |   > Date started: 24th February 2002
  |
  |	> Module Version Number: 1.0.0
  +--------------------------------------------------------------------------
 */
use Skins\Skin;
use Views\View;

$idx = new Help;

class Help
{

	var $output = "";
	var $page_title = "";
	var $nav = array();

	function __construct()
	{
		global $ibforums, $std, $print;

		if ($ibforums->input['CODE'] == "")
		{
			$ibforums->input['CODE'] = '00';
		}

		//--------------------------------------------
		// Require the HTML and language modules
		//--------------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_help', $ibforums->lang_id);

		$this->base_url = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}";

		//--------------------------------------------
		// What to do?
		//--------------------------------------------

		switch ($ibforums->input['CODE'])
		{
			case '01':
				$this->show_section();
				break;
			case '02':
				$this->do_search();
				break;
			default:
				$this->show_titles();
				break;
		}

		// If we have any HTML to print, do so...

		$print->add_output("$this->output");
		$print->do_output(array('TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav));
	}

	function show_titles()
	{
		global $ibforums;

		$seen = array();

		$this->output = View::make("help.start",
			[
				'one_text'   => $ibforums->lang['page_title'],
				'two_text'   => $ibforums->lang['help_txt'],
				'three_text' => $ibforums->lang['choose_file']
			]
		);

		$stmt = $ibforums->db->query("SELECT id, title, description from ibf_faq ORDER BY title ASC");

		$cnt = 0;

		while ($row = $stmt->fetch())
		{

			if (isset($seen[$row['title']]))
			{
				continue;
			} else
			{
				$seen[$row['title']] = 1;
			}

			$row['CELL_COLOUR'] = $cnt % 2
				? 'row1'
				: 'row2';

			$cnt++;

			$this->output .= View::make("help.row", ['entry' => $row]);
		}

		$this->output .= View::make("help.end");

		$this->page_title = $ibforums->lang['page_title'];
		$this->nav        = array($ibforums->lang['page_title']);
	}

	function show_section()
	{
		global $ibforums, $std;

		$id = $ibforums->input['HID'];

		if (!preg_match("/^(\d+)$/", $id))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_help_file'));
		}

		$topic = $ibforums->db->query("SELECT id, title, text from ibf_faq WHERE ID='$id'")->fetch();

		$this->output = View::make("help.start",
			[
				'one_text'   => $ibforums->lang['help_topic'],
				'two_text'   => $ibforums->lang['topic_text'],
				'three_text' => $topic['title']
			]
		);
		$this->output .= View::make("help.display", ['text' => $std->text_tidy($topic['text'])]);

		$this->output .= View::make("help.end");

		$this->page_title = $ibforums->lang['help_topic'];
		$this->nav        = array(
			"<a href='{$this->base_url}&amp;act=Help'>{$ibforums->lang['help_topics']}</a>",
			$ibforums->lang['help_topic']
		);
	}

	function do_search()
	{
		global $ibforums, $std;

		if (empty($ibforums->input['search_q']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_help_file'));
		}

		$search_string = mb_strtolower(str_replace("*", "%", $ibforums->input['search_q']));
		$search_string = preg_replace("/[<>\!\@£\$\^&\+\=\=\[\]\{\}\(\)\"':;\.,\/]/", "", $search_string);

		$seen = array();

		$this->output = View::make("help.start",
			[
				'one_text'   => $ibforums->lang['page_title'],
				'two_text'   => $ibforums->lang['results_txt'],
				'three_text' => $ibforums->lang['search_results']
			]
		);

		$stmt = $ibforums->db->query("SELECT id, title, description from ibf_faq WHERE LOWER(title) LIKE '%$search_string%' or LOWER(text) LIKE '%$search_string%' ORDER BY title ASC");

		$cnt = 0;

		while ($row = $stmt->fetch())
		{

			if (isset($seen[$row['title']]))
			{
				continue;
			} else
			{
				$seen[$row['title']] = 1;
			}

			$row['CELL_COLOUR'] = $cnt % 2
				? 'row1'
				: 'row2';

			$cnt++;

			$this->output .= View::make("help.row", ['entry' => $row]);
		}

		if ($cnt == 0)
		{
			$this->output .= View::make("help.no_results");
		}

		$this->output .= View::make("help.end");

		$this->page_title = $ibforums->lang['page_title'];
		$this->nav        = array(
			"<a href='{$this->base_url}&amp;act=Help'>{$ibforums->lang['help_topics']}</a>",
			$ibforums->lang['results_title']
		);
	}

}
