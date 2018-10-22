<?php
/*
+--------------------------------------------------------------------------
|   Invision Power Board v2.0.0
|   =============================================
|   by Matthew Mecham
|   (c) 2001 - 2004 Invision Power Services, Inc.
|   http://www.invisionpower.com
|   =============================================
|   Web: http://www.invisionboard.com
|   Time: Tue, 21 Sep 2004 16:34:28 GMT
|   Release: 150aa7a702c3c8b6f6eb90ad49305d2f
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Task Manager
|   > Module written by Matt Mecham
|   > Date started: 27th January 2004
|
|	> Module Version Number: 1.0.0
|   > DBA Checked: Tue 25th May 2004
+--------------------------------------------------------------------------
*/

if (!defined('IN_ACP'))
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ad_task_manager
{
	var $functions = "";

	function auto_run()
	{
		global $ibforums, $std;

		//-----------------------------------------
		// Require and RUN !! THERES A BOMB
		//-----------------------------------------

		require_once(ROOT_PATH . 'sources/lib/task_functions.php');

		$this->functions = new task_functions();

		$ibforums->admin->page_detail = "The task manager contains all your scheduled tasks.<br />Please note that as these tasks are run when the board is accessed, the next run time is to be used as a guide only and depends on the traffic your board gets.";
		$ibforums->admin->page_title  = "Task Manager";

		//-----------------------------------------
		// Kill globals - globals bad, Homer good.
		//-----------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		$ibforums->admin->nav[] = array('act=task', 'Task Manager Home');

		//-----------------------------------------
		// What to do...
		//-----------------------------------------

		switch ($ibforums->input['code'])
		{

			case 'edit':
				$this->task_form('edit');
				break;

			case 'doedittask':
				$this->do_save('edit');
				break;

			case 'addtask':
				$this->task_form('add');
				break;

			case 'doaddtask':
				$this->do_save('add');
				break;

			case 'delete':
				$this->delete_task();
				break;

			case 'run':
				$this->run_task();
				break;

			case 'log':
				$this->task_log_setup();
				break;

			case 'showlog':
				$this->task_log_show();
				break;

			case 'deletelog':
				$this->task_log_delete();
				break;

			default:
				$this->show_tasks();
				break;
		}
	}

	//-----------------------------------------
	// TASK LOG DELETE
	//-----------------------------------------

	function task_log_delete()
	{
		global $ibforums, $std;

		//-----------------------------------------
		// SHOW 'EM
		//-----------------------------------------

		$prune = $ibforums->input['task_prune']
			? $ibforums->input['task_prune']
			: 30;
		$prune = time() - ($prune * 86400);

		if ($ibforums->input['task_id'] != -1)
		{
			$where = "task_title='" . $ibforums->input['task_id'] . "' AND log_date < $prune";
		} else
		{
			$where = "log_date < $prune";
		}

		$ibforums->db->exec('delete FROM task_logs where ' . $where);

		$ibforums->main_msg = 'Selected Task Logs Removed';
		$this->task_log_setup();

	}

	//-----------------------------------------
	// TASK LOG SHOW
	//-----------------------------------------

	function task_log_show()
	{
		global $ibforums, $std;

		//-----------------------------------------
		// SHOW 'EM
		//-----------------------------------------

		$limit = $ibforums->input['task_count']
			? $ibforums->input['task_count']
			: 30;
		$limit = $limit > 150
			? 150
			: $limit;

		if ($ibforums->input['task_id'] != -1)
		{
			$stmt = $ibforums->db->query('select * from task_logs where log_title=' . $ibforums->input['task_id'] . ' order log_date DESC limit 0,' . $limit);
		} else
		{
			$stmt = $ibforums->db->query('select * from task_logs order log_date DESC limit 0,' . $limit);
		}

		$ibforums->adskin->td_header[] = array("Task Run", "20%");
		$ibforums->adskin->td_header[] = array("Date Run", "35%");
		$ibforums->adskin->td_header[] = array("Log Info", "45%");

		$ibforums->html .= $ibforums->adskin->start_table("Selected Task Logs");

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{
				$ibforums->html .= $ibforums->adskin->add_td_row(array(
				                                                      "<b>{$row['log_title']}</b>",
				                                                      $ibforums->admin->get_date($row['log_date'], 'SHORT'),
				                                                      "{$row['log_desc']}",
				                                                 ));
			}
		} else
		{
			$ibforums->html .= $ibforums->adskin->add_td_basic("<center>No results</center>");
		}

		$ibforums->html .= $ibforums->adskin->end_table();

		$ibforums->admin->output();

	}

	//-----------------------------------------
	// TASK LOG START
	//-----------------------------------------

	function task_log_setup()
	{
		global $ibforums, $std;

		//-----------------------------------------
		// Some set up
		//-----------------------------------------

		$tasks = array(0 => array(-1, 'All tasks'));

		$stmt = $ibforums->db->query('select * from task_manager order task_title');

		while ($pee = $stmt->fetch())
		{
			$tasks[] = array($pee['task_title'], $pee['task_title']);
		}

		//-----------------------------------------
		// LAST FIVE ACTIONS
		//-----------------------------------------

		$stmt = $ibforums->db->query('select * from task_logs order log_date DESC limit 0, 5');

		$ibforums->adskin->td_header[] = array("Task Run", "20%");
		$ibforums->adskin->td_header[] = array("Date Run", "35%");
		$ibforums->adskin->td_header[] = array("Log Info", "45%");

		$ibforums->html .= $ibforums->adskin->start_table("Last 5 Tasks Run");

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{
				$ibforums->html .= $ibforums->adskin->add_td_row(array(
				                                                      "<b>{$row['log_title']}</b>",
				                                                      $ibforums->admin->get_date($row['log_date'], 'SHORT'),
				                                                      "{$row['log_desc']}",
				                                                 ));
			}
		} else
		{
			$ibforums->html .= $ibforums->adskin->add_td_basic("<center>No results</center>");
		}

		$ibforums->html .= $ibforums->adskin->end_table();

		//-----------------------------------------
		// Show more...
		//-----------------------------------------

		$ibforums->html .= $ibforums->adskin->start_form(array(
		                                                      1 => array('act', 'task'),
		                                                      2 => array('code', 'showlog'),
		                                                 ));

		$ibforums->adskin->td_header[] = array("&nbsp;", "60%");
		$ibforums->adskin->td_header[] = array("&nbsp;", "40%");

		$ibforums->html .= $ibforums->adskin->start_table("View Task Logs");

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<b>View logs for task:</b>",
		                                                      $ibforums->adskin->form_dropdown('task_id', $tasks)
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<b>Show how many log entries?</b>",
		                                                      $ibforums->adskin->form_input('task_count', '30')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->end_form('View Logs');

		$ibforums->html .= $ibforums->adskin->end_table();

		//-----------------------------------------
		// Delete...
		//-----------------------------------------

		$ibforums->html .= $ibforums->adskin->start_form(array(
		                                                      1 => array('act', 'task'),
		                                                      2 => array('code', 'deletelog'),
		                                                 ));

		$ibforums->adskin->td_header[] = array("&nbsp;", "60%");
		$ibforums->adskin->td_header[] = array("&nbsp;", "40%");

		$ibforums->html .= $ibforums->adskin->start_table("DELETE Task Logs");

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<b>Delete logs for task:</b>",
		                                                      $ibforums->adskin->form_dropdown('task_id', $tasks)
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<b>Delete logs older than (in days)?</b>",
		                                                      $ibforums->adskin->form_input('task_prune', '30')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->end_form('DELETE Logs');

		$ibforums->html .= $ibforums->adskin->end_table();

		$ibforums->admin->output();

	}

	//-----------------------------------------
	// RUN TASK
	//-----------------------------------------

	function run_task()
	{
		global $ibforums, $std;

		if (!$ibforums->input['id'])
		{
			$ibforums->main_msg = 'No ID was passed, cannot save';
			$this->show_tasks();
		}

		$this_task = $ibforums->db->query('select * from task_manager where task_id=' . $ibforums->input['id'])
			->fetch();

		if (!$this_task['task_id'])
		{
			$ibforums->main_msg = 'No task to run.';
			$this->show_tasks();
		}

		if (!$this_task['task_enabled'])
		{
			$ibforums->main_msg = "This task has been disabled. Please enable the task before running it.";
			$this->show_tasks();
		}

		//-----------------------------------------
		// Get new instance of functions
		//-----------------------------------------

		$func = new task_functions();

		$newdate = $func->generate_next_run($this_task);

		$ibforums->db->updateRow('task_manager', ['task_next_run' => $ibforums->db->quote($newdate)], "task_id=" . $this_task['task_id']);

		$func->save_next_run_stamp();

		$func->root_path = ROOT_PATH;

		if (file_exists($func->root_path . 'sources/tasks/' . $this_task['task_file']))
		{
			require_once($func->root_path . 'sources/tasks/' . $this_task['task_file']);
			$myobj = new task_item();
			$myobj->register_class($func);
			$myobj->pass_task($this_task);
			$myobj->run_task();

			$ibforums->main_msg = 'Task run successfully';
			$this->show_tasks();
		} else
		{
			$ibforums->main_msg = 'Cannot locate: ' . $func->root_path . 'sources/tasks/' . $this_task['task_file'];
			$this->show_tasks();
		}

	}

	//-----------------------------------------
	// DELETE TASK
	//-----------------------------------------

	function delete_task()
	{
		global $ibforums, $std;

		if (!$ibforums->input['id'])
		{
			$ibforums->main_msg = 'No ID was passed, cannot save';
			$this->show_tasks();
		}

		$ibforums->db->exec('delete task_manager where task_id=' . $ibforums->input['id']);

		$this->functions->save_next_run_stamp();

		$ibforums->main_msg = 'Task deleted';

		$this->show_tasks();
	}

	//-----------------------------------------
	// DO SAVE
	//-----------------------------------------

	function do_save($type = 'edit')
	{
		global $ibforums, $std;

		if ($type == 'edit')
		{
			if (!$ibforums->input['id'])
			{
				$ibforums->main_msg = 'No ID was passed, cannot save';
				$this->task_form();
			}
		}

		if (!$ibforums->input['task_title'])
		{
			$ibforums->main_msg = 'You must enter a task title.';
			$this->task_form();
		}

		if (!$ibforums->input['task_file'])
		{
			$ibforums->main_msg = 'You must enter a filename for this task to run';
			$this->task_form();
		}

		//-----------------------------------------
		// Compile task
		//-----------------------------------------

		$save = array(
			'task_title'       => $ibforums->input['task_title'],
			'task_description' => $ibforums->input['task_description'],
			'task_file'        => $ibforums->input['task_file'],
			'task_week_day'    => $ibforums->input['task_week_day'],
			'task_month_day'   => $ibforums->input['task_month_day'],
			'task_hour'        => $ibforums->input['task_hour'],
			'task_minute'      => $ibforums->input['task_minute'],
			'task_log'         => $ibforums->input['task_log'],
			'task_cronkey'     => $ibforums->input['task_cronkey']
				? $ibforums->input['task_cronkey']
				: md5(microtime()),
			'task_enabled'     => $ibforums->input['task_enabled'],
			'task_key'         => $ibforums->input['task_key'],
			'task_safemode'    => $ibforums->input['task_safemode'],
		);

		//-----------------------------------------
		// Get next run date...
		//-----------------------------------------

		$save['task_next_run'] = $this->functions->generate_next_run($save);

		if ($type == 'edit')
		{
			$ibforums->db->updateRow('task_manager', array_map([
			                                                   $ibforums->db,
			                                                   'quote'
			                                                   ], $save), 'task_id=' . $ibforums->input['id']);
			$ibforums->main_msg = 'Task Edited Successfully';
		} else
		{
			$ibforums->db->insertRow('task_manager', $save);
			$ibforums->main_msg = 'Task Saved Successfully';
		}

		$this->functions->save_next_run_stamp();

		$this->show_tasks();
	}

	//-----------------------------------------
	// EDIT TASK
	//-----------------------------------------

	function task_form($type = 'edit')
	{
		global $ibforums, $std;

		if ($type == 'edit')
		{
			//-----------------------------------------
			// Get task
			//-----------------------------------------

			$id = intval($ibforums->input['id']);

			$this_task = $ibforums->db->query('select * from task_manager where task_id=' . $id)->fetch();

			$button = 'Edit this task';
			$code   = 'doedittask';
			$title  = 'Editing task: ' . $this_task['task_title'];
		} else
		{
			$this_task = array();
			$button    = 'Add this task';
			$code      = 'doaddtask';
			$title     = 'Adding a new task';
		}

		//-----------------------------------------
		// Create drop downs
		//-----------------------------------------

		$dd_minute = array(0 => array('-1', 'Every Minute'));
		$dd_hour   = array(0 => array('-1', 'Every Hour'), 1 => array('0', '0 - Midnight'));
		$dd_wday   = array(0 => array('-1', 'Every Week Day'));
		$dd_mday   = array(0 => array('-1', 'Every Day of the Month'));
		$dd_month  = array(0 => array('-1', 'Every Month'));

		for ($i = 0; $i < 60; $i++)
		{
			$dd_minute[] = array($i, $i);
		}

		for ($i = 1; $i < 24; $i++)
		{
			if ($i < 12)
			{
				$ampm = $i . ' am';
			} else
			{
				if ($i == 12)
				{
					$ampm = 'Midday';
				} else
				{
					$ampm = $i - 12 . ' pm';
				}
			}

			$dd_hour[] = array($i, $i . ' - (' . $ampm . ')');
		}

		for ($i = 1; $i < 32; $i++)
		{
			$dd_mday[] = array($i, $i);
		}

		$dd_wday[] = array('0', 'Sunday');
		$dd_wday[] = array('1', 'Monday');
		$dd_wday[] = array('2', 'Tuesday');
		$dd_wday[] = array('3', 'Wednesday');
		$dd_wday[] = array('4', 'Thursday');
		$dd_wday[] = array('5', 'Friday');
		$dd_wday[] = array('6', 'Saturday');

		//-----------------------------------------
		// START FORM
		//-----------------------------------------

		$ibforums->html .= $ibforums->adskin->start_form(array(
		                                                      1 => array('act', 'task'),
		                                                      2 => array('code', $code),
		                                                      3 => array('id', $id),
		                                                      4 => array('task_cronkey', $this_task['task_cronkey']),
		                                                 ));

		$ibforums->html .= "<script type='text/javascript' language='javascript'>
							function updatepreview()
							{
								var formobj  = document.theAdminForm;
								var dd_wday  = new Array();

								dd_wday[0]   = 'Sunday';
								dd_wday[1]   = 'Monday';
								dd_wday[2]   = 'Tuesday';
								dd_wday[3]   = 'Wednesday';
								dd_wday[4]   = 'Thursday';
								dd_wday[5]   = 'Friday';
								dd_wday[6]   = 'Saturday';

								var output       = '';

								chosen_min   = formobj.task_minute.options[formobj.task_minute.selectedIndex].value;
								chosen_hour  = formobj.task_hour.options[formobj.task_hour.selectedIndex].value;
								chosen_wday  = formobj.task_week_day.options[formobj.task_week_day.selectedIndex].value;
								chosen_mday  = formobj.task_month_day.options[formobj.task_month_day.selectedIndex].value;

								var output_min   = '';
								var output_hour  = '';
								var output_day   = '';
								var timeset      = 0;

								if ( chosen_mday == -1 && chosen_wday == -1 )
								{
									output_day = '';
								}

								if ( chosen_mday != -1 )
								{
									output_day = 'On day '+chosen_mday+'.';
								}

								if ( chosen_mday == -1 && chosen_wday != -1 )
								{
									output_day = 'On ' + dd_wday[ chosen_wday ]+'.';
								}

								if ( chosen_hour != -1 && chosen_min != -1 )
								{
									output_hour = 'At '+chosen_hour+':'+formatnumber(chosen_min)+'.';
								}
								else
								{
									if ( chosen_hour == -1 )
									{
										if ( chosen_min == 0 )
										{
											output_hour = 'On every hour';
										}
										else
										{
											if ( output_day == '' )
											{
												if ( chosen_min == -1 )
												{
													output_min = 'Every minute';
												}
												else
												{
													output_min = 'Every '+chosen_min+' minutes.';
												}
											}
											else
											{
												output_min = 'At '+formatnumber(chosen_min)+' minutes past the first available hour';
											}
										}
									}
									else
									{
										if ( output_day != '' )
										{
											output_hour = 'At ' + chosen_hour + ':00';
										}
										else
										{
											output_hour = 'Every ' + chosen_hour + ' hours';
										}
									}
								}

								output = output_day + ' ' + output_hour + ' ' + output_min;

								formobj.showtask.value = output;
							}

							function formatnumber(num)
							{
								if ( num == -1 )
								{
									return '00';
								}
								if ( num < 10 )
								{
									return '0'+num;
								}
								else
								{
									return num;
								}
							}
							</script>";

		$input = "<input type='text' name='showtask' class='realbutton' size='50' style='font-size:10px;width:auto;font-weight:normal'/>";

		$ibforums->adskin->td_header[] = array("&nbsp;", "60%");
		$ibforums->adskin->td_header[] = array("&nbsp;", "40%");

		$ibforums->html .= $ibforums->adskin->start_table("<table width='100%' border='0'><tr><td width='100%' style='color:white;font-size:11px'><b>$title</b></td><td width='1%' nowrap='nowrap'>$input</td></tr></table>");

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task Title</strong>",
		                                                      $ibforums->adskin->form_input('task_title', $_POST['task_title']
			                                                      ? $_POST['task_title']
			                                                      : $this_task['task_title'])
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task Short Description</strong>",
		                                                      $ibforums->adskin->form_input('task_description', $_POST['task_description']
			                                                      ? $_POST['task_description']
			                                                      : $this_task['task_description'])
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task PHP File To Run</strong><div style='color:gray'>This is the PHP file that is run when the task is run.</div>",
		                                                      "./sources/tasks/ " . $ibforums->adskin->form_simple_input('task_file', $_POST['task_file']
			                                                      ? $_POST['task_file']
			                                                      : $this_task['task_file'], '20')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task Time: Minutes</strong><div style='color:gray'>Choose 'Every Minute' to run each minute or a number for a specific minute of an hour</div>",
		                                                      $ibforums->adskin->form_dropdown('task_minute', $dd_minute, $_POST['task_minute']
			                                                      ? $_POST['task_minute']
			                                                      : $this_task['task_minute'], 'onchange="updatepreview()"')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task Time: Hours</strong><div style='color:gray'>Choose 'Every Hour' to run each hour or a number for a specific hour of a day</div>",
		                                                      $ibforums->adskin->form_dropdown('task_hour', $dd_hour, $_POST['task_hour']
			                                                      ? $_POST['task_hour']
			                                                      : $this_task['task_hour'], 'onchange="updatepreview()"')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task Time: Week Day</strong><div style='color:gray'>Choose 'Every Day' to run each day or a week day for a specific week day of a month</div>",
		                                                      $ibforums->adskin->form_dropdown('task_week_day', $dd_wday, $_POST['task_week_day']
			                                                      ? $_POST['task_week_day']
			                                                      : $this_task['task_week_day'], 'onchange="updatepreview()"')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Task Time: Month Day</strong><div style='color:gray'>Choose 'Every Day' to run each day or a month day for a specific month day of a month</div>",
		                                                      $ibforums->adskin->form_dropdown('task_month_day', $dd_mday, $_POST['task_month_day']
			                                                      ? $_POST['task_month_day']
			                                                      : $this_task['task_month_day'], 'onchange="updatepreview()"')
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Enable Task Logging</strong><div style='color:gray'>Will write to the task log each time the task is run, not recommended for regular tasks run every few minutes.</div>",
		                                                      $ibforums->adskin->form_yes_no('task_log', $_POST['task_log']
			                                                      ? $_POST['task_log']
			                                                      : $this_task['task_log'])
		                                                 ));

		$ibforums->html .= $ibforums->adskin->add_td_row(array(
		                                                      "<strong>Enable Task?</strong><div style='color:gray'>If you are using CRON, you might wish to disable this task from the internal manager.</div>",
		                                                      $ibforums->adskin->form_yes_no('task_enabled', $_POST['task_enabled']
			                                                      ? $_POST['task_enabled']
			                                                      : $this_task['task_enabled'])
		                                                 ));

		if (IN_DEV)
		{
			$ibforums->html .= $ibforums->adskin->add_td_row(array(
			                                                      "<strong>Task Key</strong><div style='color:gray'>This is used to call a task where the ID of the task might change</div>",
			                                                      $ibforums->adskin->form_input('task_key', $_POST['task_key']
				                                                      ? $_POST['task_key']
				                                                      : $this_task['task_key'])
			                                                 ));

			$ibforums->html .= $ibforums->adskin->add_td_row(array(
			                                                      "<strong>Task Safe Mode</strong><div style='color:gray'>If set to 'yes', this will not be editable by admins</div>",
			                                                      $ibforums->adskin->form_yes_no('task_safemode', $_POST['task_safemode']
				                                                      ? $_POST['task_safemode']
				                                                      : $this_task['task_safemode'])
			                                                 ));
		}

		$ibforums->html .= $ibforums->adskin->end_table();

		$ibforums->html .= "<div style='tableborder'><div align='center' class='pformstrip'><input type='submit' value='$button' class='realdarkbutton' /></div>
						  </form>";

		//-----------------------------------------

		$ibforums->admin->output();

	}

	//-----------------------------------------
	// SHOW TASKS
	//-----------------------------------------

	function show_tasks()
	{
		global $ibforums, $std;

		//-----------------------------------------
		// REBUILD CACHES
		//-----------------------------------------

		$ibforums->html .= $ibforums->adskin->js_checkdelete("Are you sure you wish to remove this task?");

		$ibforums->html .= $ibforums->adskin->start_form(array(
		                                                      1 => array('act', 'task'),
		                                                      2 => array('code', 'addtask'),
		                                                 ));

		$ibforums->adskin->td_header[] = array("Title", "40%");
		$ibforums->adskin->td_header[] = array("Next Run", "25%");
		$ibforums->adskin->td_header[] = array("Min", "5%");
		$ibforums->adskin->td_header[] = array("Hour", "5%");
		$ibforums->adskin->td_header[] = array("MDay", "5%");
		$ibforums->adskin->td_header[] = array("WDay", "5%");
		$ibforums->adskin->td_header[] = array("Options", "25%");

		$ibforums->html .= $ibforums->adskin->start_table("Your scheduled tasks");

		$stmt = $ibforums->db->query('select * from task_manager order task_safemode, task_next_run');

		while ($row = $stmt->fetch())
		{
			$row['task_minute']    = $row['task_minute'] != '-1'
				? $row['task_minute']
				: '-';
			$row['task_hour']      = $row['task_hour'] != '-1'
				? $row['task_hour']
				: '-';
			$row['task_month_day'] = $row['task_month_day'] != '-1'
				? $row['task_month_day']
				: '-';
			$row['task_week_day']  = $row['task_week_day'] != '-1'
				? $row['task_week_day']
				: '-';

			if (time() > $row['task_next_run'])
			{
				$image = 'task_run_now.gif';
			} else
			{
				$image = 'task_run.gif';
			}

			$class    = "";
			$title    = "";
			$next_run = gmdate('jS F Y - h:i A', $row['task_next_run']);

			if ($row['task_enabled'] != 1)
			{
				$class    = " style='color:gray'";
				$title    = " (Disabled)";
				$next_run = "<span style='color:gray'><s>$next_run</s></span>";
			}

			if ($row['task_safemode'] and !IN_DEV)
			{
				$deletebutton = '--';
				$editbutton   = '--';
			} else
			{
				$deletebutton = "<input type='button' class='realdarkbutton' value='Delete' onclick='checkdelete(\"act=task&code=delete&id={$row['task_id']}\")' />";
				$editbutton   = $ibforums->adskin->js_make_button('Edit', $ibforums->base_url . '&act=task&code=edit&id=' . $row['task_id'], 'realbutton');
			}

			$ibforums->html .= $ibforums->adskin->add_td_row(array(
			                                                      "<table cellpadding='0' cellspacing='0' border='0' width='100%'>
																	<tr>
																	 <td width='99%'>
																	  <strong{$class}>{$row['task_title']}{$title}</strong><div style='color:gray'><em>{$row['task_description']}</em></div>
																	  <div align='center' style='position:absolute;width:auto;display:none;text-align:center;background:#EEE;border:2px outset #555;padding:4px' id='pop{$row['task_id']}'>
																	    curl -s -o /dev/null {$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=task&amp;ck={$row['task_cronkey']}
																	  </div>
																	 </td>
																	 <td width='1%' nowrap='nowrap'>
																	   <a href='#' onclick=\"toggleview('pop{$row['task_id']}')\" title='Show CURL to use in a cron'><img src='{$ibforums->skin_url}/task_cron.gif' border='0' alt='Cron' /></a>
																	   <a href='{$ibforums->base_url}&act=task&code=run&id={$row['task_id']}' title='Run task now (id: {$row['task_id']})'><img src='{$ibforums->skin_url}/$image'  border='0' alt='Run' /></a>
																	 </td>
																	</tr>
																	</table>",
			                                                      "<center>" . $next_run . "</center>",
			                                                      "<center>" . $row['task_minute'] . "</center>",
			                                                      "<center>" . $row['task_hour'] . "</center>",
			                                                      "<center>" . $row['task_month_day'] . "</center>",
			                                                      "<center>" . $row['task_week_day'] . "</center>",
			                                                      "<center>{$editbutton} {$deletebutton}</center>"
			                                                 ));
		}

		$ibforums->html .= $ibforums->adskin->end_form("Add a new task");

		$ibforums->html .= $ibforums->adskin->end_table();

		$std->offset_set = 0;

		$ibforums->html .= "<div align='center'><em>All times GMT. GMT time now is: " . gmdate('jS F Y - h:i A') . "</em></div>";

		//-----------------------------------------
		//-------------------------------

		$ibforums->admin->output();

	}

}
