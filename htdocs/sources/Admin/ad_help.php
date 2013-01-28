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
|   > Help Control functions
|   > Module written by Matt Mecham
|   > Date started: 2nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_settings();

class ad_settings
{

	var $base_url;

	function ad_settings()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		switch ($IN['code'])
		{
			case 'edit':
				$this->show_form('edit');
				break;
			case 'new':
				$this->show_form('new');
				break;

			case 'doedit':
				$this->doedit();
				break;

			case 'donew':
				$this->doadd();
				break;

			case 'remove':
				$this->remove();
				break;

			//-------------------------
			default:
				$this->list_files();
				break;
		}

	}

	//-------------------------------------------------------------
	// HELP FILE FUNCTIONS
	//-------------------------------------------------------------

	function doedit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid emoticon id, silly!");
		}

		$text  = preg_replace("/\n/", "<br>", stripslashes($_POST['text']));
		$title = preg_replace("/\n/", "<br>", stripslashes($_POST['title']));
		$desc  = preg_replace("/\n/", "<br>", stripslashes($_POST['description']));

		$text = preg_replace("/\\\/", "&#092;", $text);

		$data = [
			'title'       => $title,
			'text'        => $text,
			'description' => $desc,
		];

		$ibforums->db->updateRow("ibf_faq", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['id'] . "'");

		$ADMIN->save_log("Edited help files");

		$std->boink_it($SKIN->base_url . "&act=help");
		exit();

	}

	//=====================================================

	function show_form($type = 'new')
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_detail = "You may add/edit and remove help files below.";
		$ADMIN->page_title  = "Help File Management";

		//+-------------------------------

		if ($type != 'new')
		{

			if ($IN['id'] == "")
			{
				$ADMIN->error("You must pass a valid help file id, silly!");
			}

			//+-------------------------------

			$stmt = $ibforums->db->query("SELECT * FROM ibf_faq WHERE id='" . $IN['id'] . "'");

			if (!$r = $stmt->fetch())
			{
				$ADMIN->error("We could not find that help file in the database");
			}

			//+-------------------------------

			$button = 'Edit this Help File';
			$code   = 'doedit';
		} else
		{
			$r      = array();
			$button = 'Add this Help File';
			$code   = 'donew';
		}

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $code),
		                                       2 => array('act', 'help'),
		                                       3 => array('id', $IN['id']),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "20%");
		$SKIN->td_header[] = array("&nbsp;", "80%");

		$r['text'] = preg_replace("/<br>/i", "\n", stripslashes($r['text']));

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table($button);

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Help File Title",
		                                       $SKIN->form_input('title', stripslashes($r['title'])),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Help File Description",
		                                       $SKIN->form_textarea('description', stripslashes($r['description'])),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Help File Text",
		                                       $SKIN->form_textarea('text', $r['text'], "60", "10"),
		                                  ));

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//=====================================================

	function remove()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid help file id, silly!");
		}

		$ibforums->db->exec("DELETE FROM ibf_faq WHERE id='" . $IN['id'] . "'");

		$ADMIN->save_log("Removed a help file");

		$std->boink_it($SKIN->base_url . "&act=help");
		exit();

	}

	//=====================================================

	function doadd()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['title'] == "")
		{
			$ADMIN->error("You must enter a title, silly!");
		}

		$text  = preg_replace("/\n/", "<br>", stripslashes($_POST['text']));
		$title = preg_replace("/\n/", "<br>", stripslashes($_POST['title']));
		$desc  = preg_replace("/\n/", "<br>", stripslashes($_POST['description']));

		$text = preg_replace("/\\\/", "&#092;", $text);

		$data = [
			'title'       => $title,
			'text'        => $text,
			'description' => $desc,
		];

		$ibforums->db->insertRow("ibf_faq", $data);

		$ADMIN->save_log("Added a help file");

		$std->boink_it($SKIN->base_url . "&act=help");
		exit();

	}

	//=====================================================

	function list_files()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_detail = "You may add/edit and remove help files below.";
		$ADMIN->page_title  = "Help File Management";

		//+-------------------------------

		$SKIN->td_header[] = array("Title", "50%");
		$SKIN->td_header[] = array("Edit", "30%");
		$SKIN->td_header[] = array("Remove", "20%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Current Help Files");

		$stmt = $ibforums->db->query("SELECT * from ibf_faq ORDER BY id ASC");

		if ($stmt->rowCount())
		{
			while ($r = $stmt->fetch())
			{

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>" . stripslashes($r['title']) . "</b><br>" . stripslashes($r['description']),
				                                       "<center><a href='" . $SKIN->base_url . "&act=help&code=edit&id={$r['id']}'>Edit</a></center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=help&code=remove&id={$r['id']}'>Remove</a></center>",
				                                  ));

			}
		}

		$ADMIN->html .= $SKIN->add_td_basic("<a href='" . $SKIN->base_url . "&act=help&code=new'>Add New Help File</a>", "center", "title");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$ADMIN->output();

	}

}

?>
