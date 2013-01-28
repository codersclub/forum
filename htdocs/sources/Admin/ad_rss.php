<?php

/*
+---------------------------------------------------------------------------
|
|   Admin RSS Stuff
|   for Invision Power Board v1.2
|   Module written by Valery Votintsev
|   Date started: 01.07.2006
|
|   Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

// Ensure we've not accessed this script directly:

$idx = new ad_rss();

class ad_rss
{

	var $base_url;
	var $colours = array();

	//---------------------------------------
	function ad_rss()
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

		// Make sure we're a root admin, or else!

		if ($MEMBER['mgroup'] != $INFO['admin_group'])
		{
			$ADMIN->error("Sorry, these functions are for the root admin group only");
		}

		$this->colours = array(
			"cat"    => "green",
			"forum"  => "darkgreen",
			"mem"    => "red",
			'group'  => "purple",
			'mod'    => 'orange',
			'op'     => 'darkred',
			'help'   => 'darkorange',
			'modlog' => 'steelblue',
		);

		switch ($IN['act'])
		{

			case 'rss_sources':
				$this->show_sources();
				break;

			case 'rss_add_source':
				$this->edit_source(1);
				break;

			case 'rss_edit_source':
				$this->edit_source();
				break;

			case 'rss_save_source':
				$this->save_source();
				break;

			case 'rss_del_source':
				$this->del_source();
				break;
			//-----------------------------
			case 'rss_channels':
				$this->show_channels();
				break;

			case 'rss_add_channel':
				$this->edit_channel(1);
				break;

			case 'rss_edit_channel':
				$this->edit_channel();
				break;

			case 'rss_save_channel':
				$this->save_channel();
				break;

			case 'rss_del_channel':
				$this->del_channel();
				break;
			//-----------------------------
			case 'rss_logs':
				$this->show_logs();
				break;

			case 'rss_del_log':
				$this->del_log();
				break;

			//-------------------------
			default:
				//				$this->list_current();
				break;
		}

	}

	//---------------------------------------------
	// Show RSS Sources
	//---------------------------------------------

	function show_sources()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$start = $IN['st']
			? $IN['st']
			: 0;

		$ADMIN->page_detail = "Managing the RSS Sources";
		$ADMIN->page_title  = "RSS Manager";

		$stmt = $ibforums->db->query("SELECT COUNT(id) as count
		FROM ibf_rss_sources");
		$row  = $stmt->fetch();

		$row_count = $row['count'];

		$query = "&act=rss_sources";

		$stmt = $ibforums->db->query("SELECT *
		FROM ibf_rss_sources
		ORDER BY id
		LIMIT $start, 20");

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $row_count,
		                                    'PER_PAGE'   => 20,
		                                    'CUR_ST_VAL' => $start,
		                                    'L_SINGLE'   => "Single Page",
		                                    'L_MULTI'    => "Pages: ",
		                                    'BASE_URL'   => $ADMIN->base_url . $query,
		                               ));

		//		$ADMIN->page_detail = "You may view and remove actions performed by your administrators";
		//		$ADMIN->page_title  = "Administrator Logs Manager";

		//+-------------------------------

		$SKIN->td_header[] = array("Id", "10%");
		$SKIN->td_header[] = array("Name", "20%");
		$SKIN->td_header[] = array("URL", "40%");
		$SKIN->td_header[] = array("Action", "30%");

		$ADMIN->html .= $SKIN->start_table("RSS Sources");
		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'pformstrip');

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{

				//	$row['ctime'] = $ADMIN->get_date( $row['ctime'], 'LONG' );

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "{$row['id']}",
				                                       "{$row['name']}",
				                                       "{$row['url']}",
				                                       "<center><b><a href='{$ADMIN->base_url}&act=rss_edit_source&id={$row['id']}'>Edit</a></b>" . " | <b><a href='{$ADMIN->base_url}&act=rss_del_source&id={$row['id']}&st={$start}'>Delete</a></b>",
				                                  ));

			}
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
		}

		$ADMIN->html .= $SKIN->add_td_basic("<center><b><a href='{$ADMIN->base_url}&act=rss_add_source'>Add New Source</a></b>");

		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'pformstrip');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	// EDIT RSS SOURCE FORM
	//-------------------------------------------------------------

	function edit_source($new = 0)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$form_array = array();

		$ADMIN->page_detail = "You may edit your RSS sources.";
		$ADMIN->page_title  = "RSS Manager";

		if ($new)
		{
			$IN['id']    = '';
			$row['name'] = '';
			$row['url']  = '';
		} else
		{
			$stmt = $ibforums->db->query("SELECT *
  		FROM ibf_rss_sources
  		WHERE id='{$IN['id']}'
  		");

			$row = $stmt->fetch();
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('id', $IN['id']),
		                                       2 => array('act', 'rss_save_source'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "50%");
		$SKIN->td_header[] = array("&nbsp;", "50%");

		$ADMIN->html .= $SKIN->start_table("Edit the RSS Source");

		$form_array = array(
			0 => array('note', 'Action Performed'),
			1 => array('ip_address', 'IP Address'),
			2 => array('member_id', 'Member ID'),
			3 => array('act', 'ACT Setting'),
			4 => array('code', 'CODE Setting'),
		);

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Source Name</b>",
		                                       $SKIN->form_input("source_name", $row['name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Source URL</b>",
		                                       $SKIN->form_input("source_url", $row['url'])
		                                  ));
		/*    $ADMIN->html .= $SKIN->add_td_row( array(
						"<b>Search in...</b>" ,
						$SKIN->form_dropdown( "search_type", $form_array)
						 )      );
		*/
		$ADMIN->html .= $SKIN->end_form("Save");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//---------------------------------------------
	// Save Edited/New Source
	//---------------------------------------------

	function save_source()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		/*
			foreach($IN as $k => $v)
			{
			echo $k."=>".$v."<br>\n";
			}
		*/
		$IN['id'] = intval($IN['id']);

		//echo "IN['id']=".$IN['id']."<br>\n";

		if ($IN['id'])
		{
			$query = "UPDATE ibf_rss_sources
		SET name='{$IN['source_name']}',
		    url='{$IN['source_url']}'
		WHERE id='{$IN['id']}'";
		} else
		{
			/*
				  if ($IN['id'] == "")
				  {
					  $ADMIN->error("You did not select a RSS Source ID to edit!");
				  }
			*/
			//todo does it work?
			$query = "INSERT INTO ibf_rss_sources
		SET name='{$IN['source_name']}',
		    url='{$IN['source_url']}'";
		}

		$stmt = $ibforums->db->query($query);

		$std->boink_it($ADMIN->base_url . "&act=rss_sources");
		exit();

	}

	//---------------------------------------------
	// Remove Source Record
	//---------------------------------------------

	function del_source()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$start = $IN['st']
			? $IN['st']
			: 0;

		if ($IN['id'] == "")
		{
			$ADMIN->error("You did not select a source ID to remove!");
		}

		$ibforums->db->exec("DELETE FROM ibf_rss_sources WHERE id='" . $IN['id'] . "'");

		$redir_url = $ADMIN->base_url . "&act=rss_sources";
		if ($IN['st'] > 0)
		{
			$redir_url .= "&st=" . $start;
		}

		$std->boink_it($redir_url);

		exit();

	}

	//=================================================

	//---------------------------------------------
	// Show RSS Channels
	//---------------------------------------------

	function show_channels()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$start = $IN['st']
			? $IN['st']
			: 0;

		$ADMIN->page_detail = "Managing the RSS Channels";
		$ADMIN->page_title  = "RSS Manager";

		$stmt = $ibforums->db->query("SELECT COUNT(id) as count
		FROM ibf_rss_channels");
		$row  = $stmt->fetch();

		$row_count = $row['count'];

		$query = "&act=rss_channels";

		$stmt = $ibforums->db->query("SELECT c.*, s.name as source_name
		FROM ibf_rss_channels c
		LEFT JOIN ibf_rss_sources s
			ON (c.source_id=s.id)
		ORDER BY c.id
		LIMIT $start, 20");

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $row_count,
		                                    'PER_PAGE'   => 20,
		                                    'CUR_ST_VAL' => $start,
		                                    'L_SINGLE'   => "Single Page",
		                                    'L_MULTI'    => "Pages: ",
		                                    'BASE_URL'   => $ADMIN->base_url . $query,
		                               ));

		//+-------------------------------

		$SKIN->td_header[] = array("Id", "5%");
		$SKIN->td_header[] = array("Source", "10%");
		$SKIN->td_header[] = array("Description", "20%");
		$SKIN->td_header[] = array("Channel URL", "25%");
		$SKIN->td_header[] = array("Type", "10%");
		$SKIN->td_header[] = array("State", "10%");
		$SKIN->td_header[] = array("Action", "20%");

		$ADMIN->html .= $SKIN->start_table("RSS Channels");
		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'pformstrip');

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{

				//	$row['ctime'] = $ADMIN->get_date( $row['ctime'], 'LONG' );

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "{$row['id']}",
				                                       "{$row['source_name']}",
				                                       "{$row['descr']}",
				                                       "{$row['channel_url']}",
				                                       "{$row['posting_type']}",
				                                       "{$row['is_active']}",
				                                       "<center><b><a href='{$ADMIN->base_url}&act=rss_edit_channel&id={$row['id']}'>Edit</a></b>" . " | <b><a href='{$ADMIN->base_url}&act=rss_del_channel&id={$row['id']}&st={$start}'>Delete</a></b>",
				                                  ));

			}
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
		}

		$ADMIN->html .= $SKIN->add_td_basic("<center><b><a href='{$ADMIN->base_url}&act=rss_add_channel'>Add New Channel</a></b>");
		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'pformstrip');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	// EDIT RSS CHANNEL FORM
	//-------------------------------------------------------------

	function edit_channel($new = 0)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$form_array = array();

		$ADMIN->page_detail = "You may edit your RSS Channels.";
		$ADMIN->page_title  = "RSS Manager";

		//+----------------------------
		// Get the Source List
		//+----------------------------

		$sources_array = array();
		$stmt          = $ibforums->db->query("SELECT *
		FROM ibf_rss_sources
		ORDER BY id
		");
		while ($row = $stmt->fetch())
		{
			$sources_array[] = array("{$row['id']}", "{$row['name']}");
		}

		if ($new)
		{
			$IN['id'] = '';
			//      $row['name']='';
			//      $row['url']='';
		} else
		{
			$stmt = $ibforums->db->query("SELECT *
  		FROM ibf_rss_channels
  		WHERE id='{$IN['id']}'
  		");

			$row = $stmt->fetch();
		}

		$type_array = array(
			0 => array('SHORT', 'SHORT'),
			1 => array('FULL', 'FULL')
		);

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('id', $IN['id']),
		                                       2 => array('act', 'rss_save_channel'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "50%");
		$SKIN->td_header[] = array("&nbsp;", "50%");

		$ADMIN->html .= $SKIN->start_table("Edit the RSS Channel");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Description</b>",
		                                       $SKIN->form_input("channel_descr", $row['descr'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Channel URL</b>",
		                                       $SKIN->form_input("channel_url", $row['channel_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Channel Source</b>",
		                                       $SKIN->form_dropdown("source_id", $sources_array, $row['source_id'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum ID</b>",
		                                       $SKIN->form_input("forum_id", $row['forum_id'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Posting Type</b>",
		                                       $SKIN->form_dropdown('posting_type', $type_array, $row['posting_type'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>User ID</b>",
		                                       $SKIN->form_input("user_id", $row['user_id'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>User Name</b>",
		                                       $SKIN->form_input("user_name", $row['user_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>URL Handler (function name)</b>",
		                                       $SKIN->form_input("url_handler", $row['url_handler'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Body Handler (function name)</b>",
		                                       $SKIN->form_input("body_handler", $row['body_handler'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Is Active</b>  (0=No, 1=Yes)",
		                                       $SKIN->form_input("is_active", $row['is_active'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Save");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//---------------------------------------------
	// Save Edited/New CHANNEL
	//---------------------------------------------

	function save_channel()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		/*
			foreach($IN as $k => $v)
			{
			echo $k."=>".$v."<br>\n";
			}
			exit;
		*/

		$IN['id'] = intval($IN['id']);

		//echo "IN['id']=".$IN['id']."<br>\n";

		if ($IN['id'])
		{
			/* Channels
				   id   		int(11)  		Нет   	0
				 descr  	varchar(32) 	  	Нет
				 channel_url  	varchar(255) 	  	Нет
				 source_id  	int(11) 		Нет  	0
				 forum_id  	int(11) 		Нет  	0
				 posting_type  	set('SHORT', 'FULL') 	Нет  	SHORT
				 user_id  	int(11) 		Нет  	0
				 user_name  	varchar(32) 	 	Нет
				 url_handler  	varchar(32) 	 	Нет
				 body_handler  	varchar(32) 	  	Нет
				 is_active  	tinyint(1) 		Нет	0
			*/

			$query = "UPDATE ibf_rss_channels
		SET	descr       ='{$IN['channel_descr']}',
			channel_url ='{$IN['channel_url']}',
			source_id   ='{$IN['source_id']}',
			forum_id    ='{$IN['forum_id']}',
			posting_type='{$IN['posting_type']}',
			user_id     ='{$IN['user_id']}',
			user_name   ='{$IN['user_name']}',
			url_handler ='{$IN['url_handler']}',
			body_handler='{$IN['body_handler']}',
			is_active   ='{$IN['is_active']}'
		WHERE id='{$IN['id']}'";
		} else
		{
			/*
				  if ($IN['id'] == "")
				  {
					  $ADMIN->error("You did not select a RSS Source ID to edit!");
				  }
			*/
			$query = "INSERT INTO ibf_rss_channels
		SET	descr       ='{$IN['channel_descr']}',
			channel_url ='{$IN['channel_url']}',
			source_id   ='{$IN['source_id']}',
			forum_id    ='{$IN['forum_id']}',
			posting_type='{$IN['posting_type']}',
			user_id     ='{$IN['user_id']}',
			user_name   ='{$IN['user_name']}',
			url_handler ='{$IN['url_handler']}',
			body_handler='{$IN['body_handler']}',
			is_active   ='{$IN['is_active']}'
		    ";
		}

		$stmt = $ibforums->db->query($query);

		$std->boink_it($ADMIN->base_url . "&act=rss_channels");
		exit();

	}

	//---------------------------------------------
	// Remove Channel Record
	//---------------------------------------------

	function del_channel()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$start = $IN['st']
			? $IN['st']
			: 0;

		if ($IN['id'] == "")
		{
			$ADMIN->error("You did not select a channel ID to remove!");
		}

		$ibforums->db->exec("DELETE FROM ibf_rss_channels WHERE id='" . $IN['id'] . "'");

		$redir_url = $ADMIN->base_url . "&act=rss_channels";
		if ($IN['st'] > 0)
		{
			$redir_url .= "&st=" . $start;
		}

		$std->boink_it($redir_url);

		exit();

	}

	//==============================================

	//---------------------------------------------
	// Show RSS Logs
	//---------------------------------------------

	function show_logs()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$start = $IN['st']
			? $IN['st']
			: 0;

		$ADMIN->page_detail = "Managing the RSS Logs";
		$ADMIN->page_title  = "RSS Manager";

		$stmt = $ibforums->db->query("SELECT COUNT(id) as count
		FROM ibf_rss_logs");
		$row  = $stmt->fetch();

		$row_count = $row['count'];

		$query = "&act=rss_logs";

		$stmt = $ibforums->db->query("SELECT l.*, s.name as source_name
		FROM ibf_rss_logs l
		LEFT JOIN ibf_rss_sources s
			ON (l.source_id=s.id)
		ORDER BY l.id
		LIMIT $start, 20");

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $row_count,
		                                    'PER_PAGE'   => 20,
		                                    'CUR_ST_VAL' => $start,
		                                    'L_SINGLE'   => "Single Page",
		                                    'L_MULTI'    => "Pages: ",
		                                    'BASE_URL'   => $ADMIN->base_url . $query,
		                               ));

		//+-------------------------------

		$SKIN->td_header[] = array("Id", "5%");
		$SKIN->td_header[] = array("Source", "5%");
		$SKIN->td_header[] = array("News ID", "5%");
		$SKIN->td_header[] = array("News Date", "10%");
		$SKIN->td_header[] = array("News URL", "50%");
		$SKIN->td_header[] = array("State", "5%");
		$SKIN->td_header[] = array("Action", "20%");

		$ADMIN->html .= $SKIN->start_table("RSS Channels");
		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'pformstrip');

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{

				$row['news_date'] = $ADMIN->get_date($row['news_date'], 'LONG');

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "{$row['id']}",
				                                       "{$row['source_name']}",
				                                       "{$row['news_id']}",
				                                       "{$row['news_date']}",
				                                       "{$row['news_url']}",
				                                       "{$row['state']}",
				                                       "<center><b><a href='{$ADMIN->base_url}&act=rss_del_log&id={$row['id']}&st={$start}'>Delete</a></b>",
				                                  ));

			}
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
		}

		//    $ADMIN->html .= $SKIN->add_td_basic("<center><b><a href='{$ADMIN->base_url}&act=rss_add_channel'>Add New Channel</a></b>");
		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'pformstrip');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//---------------------------------------------
	// Remove Log Record
	//---------------------------------------------

	function del_log()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$start = $IN['st']
			? $IN['st']
			: 0;

		if ($IN['id'] == "")
		{
			$ADMIN->error("You did not select a log ID to remove!");
		}

		$ibforums->db->exec("DELETE FROM ibf_rss_logs WHERE id='" . $IN['id'] . "'");

		$redir_url = $ADMIN->base_url . "&act=rss_logs";
		if ($IN['st'] > 0)
		{
			$redir_url .= "&st=" . $start;
		}

		$std->boink_it($redir_url);

		exit();

	}

}

?>
