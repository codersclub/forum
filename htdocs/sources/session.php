<?php

//######################################################
// Our "session" class
//######################################################

class session
{

	var $ip_address = 0;
	var $user_agent = "";
	var $time_now = 0;
	var $session_id = 0;
	var $session_dead_id = 0;
	var $session_user_id = 0;
	var $session_user_pass = "";
	var $last_click = 0;
	var $member = array();
	var $location = "";
	var $r_location = "";
	var $in_forum = "";
	var $r_in_forum = "";
	var $in_topic = "";
	var $r_in_topic = "";

	// No need for a constructor

	function authorise()
	{
		global $std;
		$ibforums = Ibf::instance();

		//-------------------------------------------------
		// Before we go any lets check the load settings..
		//-------------------------------------------------

		if ($ibforums->vars['load_limit'] > 0)
		{
			if (file_exists('/proc/loadavg'))
			{
				if ($fh = @fopen('/proc/loadavg', 'r'))
				{
					$data = @fread($fh, 6);
					@fclose($fh);

					$load_avg = explode(" ", $data);

					$ibforums->server_load = trim($load_avg[0]);

					if ($ibforums->server_load > $ibforums->vars['load_limit'])
					{
						$std->Error(array(
						                 'LEVEL' => 1,
						                 'MSG'   => 'server_too_busy',
						                 'INIT'  => 1
						            ));
					}
				}
			}
		}

		//--------------------------------------------
		// Are they banned?
		//--------------------------------------------

		if ($std->is_ip_banned($ibforums->input['IP_ADDRESS']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'you_are_banned',
			                 'EXTRA' => '[' . $ibforums->input['IP_ADDRESS'] . ']',
			                 'INIT'  => 1
			            ));
		}

		//--------------------------------------------

		$this->member = array(
			'id'       => 0,
			'password' => "",
			'name'     => "",
			'mgroup'   => $ibforums->vars['guest_group']
		);

		//--------------------------------------------
		// no new headers if we're simply viewing an attachment..
		//--------------------------------------------
		// vot        if ( $ibforums->input['act'] == 'Attach' )
		// vot        {
		// vot        	return $this->member;
		// vot        }

		$_SERVER['HTTP_USER_AGENT'] = $std->clean_value($_SERVER['HTTP_USER_AGENT']);

		$this->ip_address = $ibforums->input['IP_ADDRESS'];

		$this->user_agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

		$this->time_now = time();

		//-------------------------------------------------
		// Manage bots? (tee-hee)
		//-------------------------------------------------

		if ($ibforums->vars['spider_sense'] == 1)
		{
			$remap_agents = array(
				'googlebot'       => 'google',
				'slurp'           => 'inktomi',
				'ask jeeves'      => 'jeeves',
				'lycos'           => 'lycos',
				'whatuseek'       => 'wuseek',
				'ia_archiver'     => 'Archive_org',
				'aport'           => 'Aport',
				'yandex'          => 'Yandex',
				'stackrambler'    => 'Rambler',
				'turtlescanner'   => 'Turtle',
				'scooter'         => 'Altavista',
				'gigabot'         => 'Gigablast',
				'zyborg'          => 'ZyBorg',
				'fast-webcrawler' => 'FAST',
				'openbot'         => 'Openfind',
				'libwww'          => 'libwww-FM',
				'yahoo'           => 'Yahoo',
				'msnbot'          => 'MSN',
			);

			if (preg_match('/(googlebot|slurp|yahoo|msnbot|ask jeeves|lycos|whatuseek|ia_archiver|aport|yandex|stackrambler|scooter|
				   gigabot|turtlescanner|zyborg|fast-webcrawler|openbot|libwww)/i', $_SERVER['HTTP_USER_AGENT'], $match)
			)
			{

				$stmt = $ibforums->db->prepare("SELECT * FROM ibf_groups WHERE g_id=?");
				$stmt->execute([$ibforums->vars['spider_group']]);
				$group = $stmt->fetch_row();

				foreach ($group as $k => $v)
				{
					$this->member[$k] = $v;
				}

				$this->member['restrict_post']    = 1;
				$this->member['g_use_search']     = 0;
				$this->member['g_email_friend']   = 0;
				$this->member['g_edit_profile']   = 0;
				$this->member['g_use_pm']         = 0;
				$this->member['g_is_supmod']      = 0;
				$this->member['g_access_cp']      = 0;
				$this->member['g_access_offline'] = 0;
				$this->member['g_avoid_flood']    = 0;
				$this->member['id']               = 0;

				$ibforums->perm_id       = $this->member['g_perm_id'];
				$ibforums->perm_id_array = explode(",", $ibforums->perm_id);
				$ibforums->session_type  = 'cookie';
				$ibforums->is_bot        = 1;
				$this->session_id        = "";

				$match = strtolower($match[1]);

				if (!$agent = $remap_agents[$match])
				{
					$agent = 'google';
				}

				if ($ibforums->vars['spider_visit'])
				{
					$dba = [
						'bot'          => $agent,
						'query_string' => str_replace("'", "", $_SERVER['QUERY_STRING']),
						'ip_address'   => $_SERVER['REMOTE_ADDR'],
						'entry_date'   => time(),
					];

					$ibforums->db->insertRow('ibf_spider_logs', $dba);

				}

				if ($ibforums->vars['spider_active'])
				{
					$ibforums->db->exec("DELETE FROM ibf_sessions WHERE id='" . $agent . "_session'");
					$this->create_bot_session($agent);
				}

				return $this->member;
			}
		}

		//-------------------------------------------------
		// Continue!
		//-------------------------------------------------

		$cookie               = array();
		$cookie['session_id'] = $std->my_getcookie('session_id');
		$cookie['member_id']  = $std->my_getcookie('member_id');

		if (isset($ibforums->vars['plg_custom_login']) && $ibforums->vars['plg_custom_login'])
		{
			$plg_sess = $this->plg_get_session();
			if ($plg_sess)
			{
				$this->get_session($plg_sess);
			} else
			{
				$this->session_id = 0;
			}
		} elseif ($cookie['session_id'])
		{
			$this->get_session($cookie['session_id']);
			$ibforums->session_type = 'cookie';
		} elseif (isset($ibforums->input['s']))
		{
			$this->get_session($ibforums->input['s']);
			$ibforums->session_type = 'url';
		} else
		{
			$this->session_id = 0;
		}

		//-------------------------------------------------
		// Finalise the incoming data..
		//-------------------------------------------------
		//Schooler invisible login patch
		if (!isset($ibforums->input['Privacy']))
		{
			$ibforums->input['Privacy'] = $std->my_getcookie('anonlogin');
		}

		//-------------------------------------------------
		// Do we have a valid session ID?
		//-------------------------------------------------

		if ($this->session_id)
		{
			// We've checked the IP addy and browser, so we can assume that this is
			// a valid session.

			if ($this->session_user_id != 0 and !empty($this->session_user_id))
			{
				// It's a member session, so load the member.
				$this->load_member($this->session_user_id);

				// Did we get a member?
				if (!$this->member['id'] or $this->member['id'] == 0)
				{
					$this->unload_member();
					$this->update_guest_session();
				} else
				{
					$this->update_member_session();
				}
			} else
			{
				$this->update_guest_session();
			}
		} else
			// We didn't have a session, or the session didn't validate
		{
			// Forumizer auth
			if (isset($ibforums->vars['plg_custom_login']) && $ibforums->vars['plg_custom_login'])
			{
				$this->load_member($ibforums->vars['plg_user_id']);

				// such member exists ?
				if (!$this->member['id'] or $this->member['id'] == 0)
				{
					$this->unload_member();

					if (!$ibforums->info['plg_allow_guests'])
					{
						$std->Error(array(
						                 'LEVEL' => 1,
						                 'MSG'   => 'no_guests',
						                 'INIT'  => 1
						            ));
					}

					$this->create_guest_session();
				} else
				{
					// check a password
					if ($this->member['password'] == $ibforums->vars['plg_user_pass'])
					{
						$this->create_member_session();
					} else
					{
						$this->unload_member();

						if (!$ibforums->info['plg_allow_guests'])
						{
							$std->Error(array(
							                 'LEVEL' => 1,
							                 'MSG'   => 'no_guests',
							                 'INIT'  => 1
							            ));
						}

						$this->create_guest_session();
					}
				}

				// Do we have cookies stored?
			} elseif ($cookie['member_id'] != "")
			{
				$this->load_member($cookie['member_id']);

				// such member exists ?
				if (!$this->member['id'] or $this->member['id'] == 0)
				{
					$this->unload_member();
					$this->create_guest_session();
				} else
				{
					// check a password
					if (AuthBasic::checkSessionDataIsValid($this->member))
					{
						$this->create_member_session();
					} else
					{
						$this->unload_member();
						$this->create_guest_session();
					}
				}
			} else
			{
				$this->create_guest_session();
			}
		}

		//-------------------------------------------------
		// Set up a guest if we get here and we don't have a member ID
		//-------------------------------------------------

		if (!$this->member['id'])
		{
			$this->member = $std->set_up_guest();

			$stmt = $ibforums->db->prepare("SELECT * FROM ibf_groups WHERE g_id=?");
			$stmt->execute([$ibforums->vars['guest_group']]);

			$group = $stmt->fetch();

			if (is_array($group))
			{
				foreach ($group as $k => $v)
				{
					$this->member[$k] = $v;
				}
			}
		} else
		{
			//------------------------------------------------
			// Synchronise the last visit and activity times if
			// we have some in the member profile
			//-------------------------------------------------

			if (!$ibforums->input['last_activity'])
			{
				if ($this->member['last_activity'])
				{
					$ibforums->input['last_activity'] = $this->member['last_activity'];
				} else
				{
					$ibforums->input['last_activity'] = $this->time_now;
				}
			}

			if (!$ibforums->input['last_visit'])
			{
				if ($this->member['last_visit'])
				{
					$ibforums->input['last_visit'] = $this->member['last_visit'];
				} else
				{
					$ibforums->input['last_visit'] = $this->time_now;
				}
			}

			//-------------------------------------------------
			// If there hasn't been a cookie update in 2 hours,
			// we assume that they've gone and come back
			//-------------------------------------------------

			if (!$this->member['last_visit'])
			{
				// No last visit set, do so now!
				$stmt = $ibforums->db->prepare("UPDATE ibf_members
				    SET
					last_visit=?,
					last_activity=?
				    WHERE id=?");
				$stmt->execute([$this->time_now, $this->time_now, $this->memeber['id']]);

				// Song * look at died accounts

				if ($this->member['check_id'])
				{
					$stmt = $ibforums->db->prepare("UPDATE ibf_check_members
					    SET last_visit=?
					    WHERE mid=?");
					$stmt->execute([$this->time_now, $this->memeber['check_id']]);
				}
			} elseif ((time() - $ibforums->input['last_activity']) > 300)
			{
				// If the last click was longer than 5 mins ago and this is a member
				// Update their profile.
				$stmt = $ibforums->db->prepare("UPDATE ibf_members
				    SET last_activity=?
				    WHERE id=?");
				$stmt->execute([$this->time_now, $this->memeber['id']]);

				// Song * look at died accounts

				if ($this->member['check_id'])
				{
					$stmt = $ibforums->db->prepare("UPDATE ibf_check_members
					    SET last_visit=?
					    WHERE mid=?");
					$stmt->execute([$this->time_now, $this->memeber['check_id']]);
				}
			}

			//-------------------------------------------------
			// Check ban status
			//-------------------------------------------------

			if ($this->member['temp_ban'])
			{
				if ($this->member['temp_ban'] == 1)
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'you_are_banned',
					                 //				    		    'EXTRA' => '['.$ibforums->input['IP_ADDRESS'].']',
					                 'INIT'  => 1
					            ));
				} else
				{
					$ban_arr = $std->hdl_ban_line($this->member['temp_ban']);

					if (time() >= $ban_arr['date_end'])
					{
						// Update this member's profile
						$stmt = $ibforums->db->prepare("UPDATE ibf_members
						    SET temp_ban=''
						    WHERE id=?");
						$stmt->execute([$this->member['id']]);
					} else
					{
						$ibforums->member = $this->member; // Set time right

						$std->Error(array(
						                 'LEVEL' => 1,
						                 'MSG'   => 'account_susp',
						                 'INIT'  => 1,
						                 'EXTRA' => $std->get_date($ban_arr['date_end'])
						            ));
					}
				}
			}
		}

		//-------------------------------------------------
		// Set a session ID cookie
		//-------------------------------------------------

		$std->my_setcookie("session_id", $this->session_id, -1);

		// permissions based on a group
		$ibforums->perm_id = $this->member['g_perm_id'];

		// Song * enchaced rights, 16.12.04
		// additional permissions basen on a member
		$org_perm_id = isset($this->member['org_perm_id'])
			? $this->member['org_perm_id']
			: '';
		if ($org_perm_id)
		{
			$ibforums->perm_id = ($ibforums->perm_id)
				? $ibforums->perm_id . "," . $org_perm_id
				: $org_perm_id;
		}

		$ibforums->perm_id_array = explode(",", $ibforums->perm_id);

		return $this->member;
	}

	//+-------------------------------------------------
	// Attempt to load a member
	//+-------------------------------------------------

	function load_member($member_id = 0)
	{
		global $std;
		$ibforums = Ibf::instance();

		$member_id = intval($member_id);

		if ($member_id != 0)
		{
			$stmt = $ibforums->db->prepare("SELECT
				m.*,
				g.*,
				IFNULL(es.name,'Main') as sskin_name,
				md.mid as is_mod,
				md.allow_warn,
				md.time_deleted_link,
				cm.mid as check_id,
				cm.sent
			FROM ibf_members m
            		LEFT JOIN ibf_groups g
				ON (g.g_id=m.mgroup)
			LEFT JOIN ibf_emoticons_skins es
				ON (es.id=m.sskin_id)
			LEFT JOIN ibf_check_members cm
				ON (cm.mid=m.id)
            		LEFT JOIN ibf_moderators md
				ON (md.member_id=m.id)
            		WHERE m.id=?
			LIMIT 1");
			$stmt->execute([$member_id]);

			if ($stmt->rowCount())
			{
				$this->member = $stmt->fetch();

				if ($this->member['id'])
				{
					// Song * club tool
					$stmt = $ibforums->db->prepare("SELECT read_perms
					FROM ibf_forums
					WHERE id=?");
					$stmt->execute([$ibforums->vars['club']]);

					if ($club = $stmt->fetch())
					{
						$this->member['club_perms'] = $club['read_perms'];
					}

					// vot: BAD MESSAGE! yesterday, today
					// Song * today/yesterday

					if ($this->member['language'] == 'en')
					{
						$this->member['yesterday'] = 'Yesterday';
						$this->member['today']     = 'Today';

						if ($this->member['disable_mail'] and !$this->member['disable_mail_reason'])
						{
							$this->member['disable_mail_reason'] = "(no information)";
						}
					} else
					{
						$this->member['yesterday'] = 'Вчера';
						$this->member['today']     = 'Сегодня';

						if ($this->member['disable_mail'] and !$this->member['disable_mail_reason'])
						{
							$this->member['disable_mail_reason'] = "(нет информации)";
						}
					}

					$time = time();

					// if member has more than 50 posts,
					// leave him, abandon see him

					if ($this->member['check_id'])
					{
						if ($this->member['posts'] > $ibforums->vars['check_before'])
						{
							$ibforums->db->exec("DELETE FROM ibf_check_members
						      WHERE mid='" . $this->member['check_id'] . "'");
							$this->member['check_id'] = "";
						} elseif ($this->member['sent'])
						{
							$ibforums->db->exec("UPDATE ibf_check_members
						      SET sent=0, last_visit='" . $time . "'
						      WHERE mid='" . $this->member['check_id'] . "'");
							$this->member['sent'] = 0;
						}
					}

					/* <--- Jureth ---
					 * Publish the moderated forums list
					 * Hint: Result of query include forums, moderated by groups, rather than prev.
					 *
					 * */

					$this->member['modforums'] = false;
					$mod                       = array();

					$stmt = $ibforums->db->query("SELECT forum_id
					    FROM ibf_moderators
					    WHERE member_id='" . $this->member['id'] . "' or group_id='" . $this->member['mgroup'] . "'");

					foreach ($stmt as $row)
					{
						$mod[] = $row['forum_id'];
					}
					if (count($mod))
					{
						$this->member['modforums'] = implode(',', $mod);
					}
					unset($mod);

					/* >--- Jureth --- */

					// if this is a user
					if (!$this->member['is_mod'])
					{

						// ********* REMOVE WARNINGS of current member *****************************************
						// if user group is not banned
						if ($this->member['mgroup'] != $ibforums->vars['ban_group'])
						{
							$stmt = $ibforums->db->query("SELECT id
							   FROM ibf_warnings
							   WHERE
								mid='" . $this->member['id'] . "' and
								RestrictDate<" . $time . "
							   ORDER BY RestrictDate DESC
							   LIMIT 1");

							if ($stmt->rowCount())
							{
								$row = $stmt->fetch();

								$level = intval($this->member['warn_level']);

								// decrease level
								$level--;

								// define a new group of user
								if ($level > 0)
								{
									$group = 15 + $level;
								} else
								{
									$group = $this->member['old_group'];
								}

								// update group and warn level of user
								$ibforums->db->exec("UPDATE ibf_members
								    SET
									mgroup='" . $group . "',
									warn_level='" . $level . "'
								    WHERE id='" . $this->member['id'] . "'");

								// accept new group and warn level for already fetched row
								$this->member['mgroup']     = $group;
								$this->member['warn_level'] = $level;

								// delete old record about violation
								$ibforums->db->exec("DELETE FROM ibf_warnings
								    WHERE id='" . $row['id'] . "'");
								// vot: BAD MESSAGE!
								$mes = "Уменьшен уровень Ваших предупреждений в связи с истечением срока действия одного из них.\n\n";

								if ($group == $ibforums->vars['member_group'] or $group == $ibforums->vars['club_group'] or $group == 26 or $group == 9)
								{
									// vot: BAD MESSAGE!
									$mes .= "[color=green]Вы обратно переведены в группу участников.[/color]";
								} else
								{
									// vot: BAD MESSAGE!
									$mes .= "[color=red]Вы переведены в группу нарушивших правила уровня " . $level . ".[/color]";
								}

								$save = array();

								$save['wlog_type']  = "pos";
								$save['wlog_date']  = $time;
								$save['wlog_notes'] = "<content>{$mes}</content>";
								$save['wlog_notes'] .= "<mod>,,</mod>";
								$save['wlog_notes'] .= "<post>,,</post>";
								$save['wlog_notes'] .= "<susp>,</susp>";
								$save['wlog_mid']     = $this->member['id'];
								$save['wlog_addedby'] = 8617;

								$ibforums->db->insertRow('ibf_warn_logs', $save);
							}
						}

						// ************** END OF REMOVE WARNINGS *****************************************
						// if this is moderator
					} elseif (!$this->member['time_deleted_link'] or
					          ($time - intval($this->member['time_deleted_link']) > 60 * 60 * 24)
					)
					{

						// *****************************************************************************
						// ************* BEGIN MODERATORS DAILY TASKS **********************************
						// *****************************************************************************

						if ($this->member['modforums']) //Jureth
						{
							$topics = array();
							$forums = array();

							// querying data for topics and forums recount
							$stmt = $ibforums->db->query("SELECT attach_id, topic_id, forum_id FROM ibf_posts
						    WHERE
							forum_id IN ({$this->member['modforums']})
							and (
								(use_sig=1 and post_date<" . $time . "-60*60*24*7)
								OR
								(use_sig=2 and edit_time<" . $time . "-60*60*24*180)
							)");
							// clear attachments
							$this->clear_posts($stmt, $topics, $forums);

							// delete moderatorial posts
							$ibforums->db->exec("DELETE
						FROM ibf_posts
						WHERE
							forum_id IN ({$this->member['modforums']})
							and (
								(use_sig=1 and post_date<" . $time . "-60*60*24*7)
								OR
								(use_sig=2 and edit_time<" . $time . "-60*60*24*180)
						)");

							// ***** DELETE delayed posts ( various days, depends from forums settings ) *************************

							$stmt = $ibforums->db->query("SELECT
							attach_id,
							topic_id,
							forum_id
						      FROM ibf_posts
						      WHERE
							forum_id IN ({$this->member['modforums']}) and
							delete_after != 0 and
							delete_after<" . $time);

							// clear attachments
							$this->clear_posts($stmt, $topics, $forums);

							// delete delayed posts
							$ibforums->db->exec("DELETE FROM ibf_posts
						      WHERE
							forum_id IN ({$this->member['modforums']}) and
							delete_after != 0 and
							delete_after<" . $time);

							// ************************************** RECOUNT forums and topics after deleting posts *******************************
							// update statistics
							$this->topic_recount($topics);
							$this->forum_recount($forums);

							if (count($topics))
							{
								$this->stats_recount();

								unset($topics);
								unset($forums);
							}

							//Jureth				  unset($mod);
						}

						// **************** CHECK USERS that do not visit forum ( 150 days ) *************************************
						// vot: BAD MESSAGE - use language !
						$title = "Уведомление об предполагаемом удалении Вашего аккаунта";

						$mes = "Здравствуйте, %s!\n";
						$mes .= "Вы не посещали Форум на Исходниках.RU (http://forum.sources.ru) уже в течение 150-ти дней.\n";
						$mes .= "По правилам нашего форума аккаунт удаляется, если участник ни разу не посещает форум в течение 6 месяцев, ";
						$mes .= "авторизуясь под своим аккаунтом. Поэтому, если Вы не зайдёте на наш форум ещё в течение месяца, ";
						$mes .= "Ваш аккаунт будет удалён без дальнейших предупреждений.\n";
						$mes .= "Если Вы просто редко посещаете наш форум и намереваетесь продолжать его посещать дальше, просто зайдите ";
						$mes .= "на форум, авторизуясь под своим ником.\n";
						$mes .= "Если Вы уверены, что это ошибка, зайдите на наш форум и сообщите об этом администратору или ";
						$mes .= "напишите сейчас об ошибке на адрес admin@sources.ru\n\n";
						$mes .= "С уважением, администрация Форума на Исходниках.RU";

						// send warnings letters
						$stmt = $ibforums->db->query("SELECT
							cm.mid,
							m.name,
							m.mgroup,
							m.temp_ban
						    FROM
							ibf_check_members cm,
							ibf_members m
						    WHERE
							m.id=cm.mid and
							cm.sent=0 and
							cm.last_visit<" . $time . "-60*60*24*150");

						while ($row = $stmt->fetch())
						{
							if ($row['mgroup'] == $ibforums->vars['ban_group'] or $row['temp_ban'])
							{
								continue;
							}

							$std->sendpm($row['mid'], sprintf($mes, $row['name']), $title, 8617, 1, 1);
						}

						// mark members
						$ibforums->db->exec("UPDATE ibf_check_members
						    SET sent=1
						    WHERE
							sent=0 and
							last_visit<" . $time . "-60*60*24*150");

						// ******* DELETE USERS that do not visit forum ( 180 days )  *****************************
						// vot: NEED TO have preserved members!!!

						$ids = array();

						// delete non active users that were sent letters
						$stmt = $ibforums->db->query("SELECT mid
						    FROM ibf_check_members
						    WHERE
							sent=1 and
							last_visit<" . $time . "-60*60*24*180");

						while ($row = $stmt->fetch())
						{
							$ids[] = $row['mid'];
						}

						if (count($ids))
						{
							$std->delete_members(" IN (" . implode(",", $ids) . ")");

							unset($ids);
						}

						// *************************************************************************************************************************
						// ************************** END OF MODERATORS TASKS ************************************************
						// *************************************************************************************************************************
						// ***********************************************************************************************************************
						// ************************** BEGIN ADMINS TASKS *****************************************************
						// ************************* for admins-moderators ***************************************************
						// ***********************************************************************************************************************
						// Delete users that marked their accounts to deleting
						if ($this->member['mgroup'] == $ibforums->vars['admin_group'])
						{
							$ids = array();

							$stmt = $ibforums->db->query("SELECT id
						      FROM ibf_members
						      WHERE
							profile_delete_time != 0 and
							profile_delete_time<'" . $time . "'");

							while ($member = $stmt->fetch())
							{
								$ids[] = $member['id'];
							}

							if (count($ids))
							{
								$std->delete_members(" IN (" . implode(",", $ids) . ")");
								unset($ids);
							}
						}

						// *************************************************************************************************************************
						// ************************************************ END OF ADMINS TASKS ****************************************************
						// *************************************************************************************************************************
						// mark time when moderator work has been made
						$ibforums->db->exec("UPDATE ibf_moderators
						    SET time_deleted_link='" . $time . "'
						    WHERE mid='" . $this->member['is_mod'] . "'");
					} // end of moderator tasks
				} // end of user tasks
			}

			//-------------------------------------------------
			// Unless they have a member id, log 'em in as a guest
			//-------------------------------------------------

			if ($this->member['id'] == 0 or empty($this->member['id']))
			{
				$this->unload_member();
			}
		}

		unset($member_id);
	}

	//+-------------------------------------------------
	// Remove the users cookies
	//+-------------------------------------------------

	function unload_member()
	{
		global $std;

		// Boink the cookies

		$std->my_setcookie("member_id", "0", -1);
		$std->my_setcookie("pass_hash", "0", -1);

		$this->member['id']       = 0;
		$this->member['name']     = "";
		$this->member['password'] = "";
	}

	// Song * topic and forum recount within deleting of moderatorial posts, 15.02.05
	//---------------------------------------------------
	function clear_posts(PDOStatementWrapper $stmt, &$topics, &$forums)
	{
		global $ibforums;

		foreach ($stmt as $row)
		{
			$forums[$row['forum_id']] = $row['forum_id'];
			$topics[$row['topic_id']] = $row['topic_id'];

			// delete attached files
			Attachment::deleteAllPostAttachments($row);
		}
	}

	//---------------------------------------------------
	function topic_recount($tids = array())
	{
		global $ibforums, $std;

		if (!count($tids))
		{
			return;
		}

		foreach ($tids as $tid)
		{
			$stmt = $ibforums->db->query("SELECT COUNT(pid) AS posts
			    FROM ibf_posts
			    WHERE
				topic_id='" . $tid . "' and
				queued != 1");

			if ($posts = $stmt->fetch())
			{
				$posts = $posts['posts'] - 1;

				$stmt = $ibforums->db->query("SELECT
					post_date,
					author_id,
					author_name
				    FROM ibf_posts
				    WHERE
					topic_id='" . $tid . "' and
					queued != 1
				    ORDER BY pid DESC
				    LIMIT 1");

				$last_post = $stmt->fetch();

				$ibforums->db->exec("UPDATE ibf_topics
				    SET
					last_post='" . $last_post['post_date'] . "',
					last_poster_id='" . $last_post['author_id'] . "',
					last_poster_name='" . $last_post['author_name'] . "',
					posts='" . $posts . "'
				    WHERE tid='" . $tid . "'");
			}
		}
	}

	//----------------------------------------------------
	function forum_recount($fids = array())
	{

		if (!count($fids))
		{
			return;
		}

		foreach ($fids as $fid)
		{
			// Get the topics..
			$topics = $ibforums->db->query("SELECT COUNT(tid) as count
			    FROM ibf_topics
			    WHERE
				approved=1 and
				forum_id='" . $fid . "'")->fetch();

			// Get the posts..
			$posts = $ibforums->db->query("SELECT COUNT(pid) as count
			    FROM ibf_posts
			    WHERE
				queued != 1 and
				forum_id='" . $fid . "'")->fetch();

			// Get the forum last poster..
			$last_post = $ibforums->db->query("SELECT
				tid,
				title,
				last_poster_id,
				last_poster_name,
				last_post
			    FROM ibf_topics
			    WHERE
				approved=1 and
				forum_id='" . $fid . "' and
				club=0
			    ORDER BY last_post DESC
			    LIMIT 1")->fetch();

			// Get real post count by removing topic starting posts from the count
			$real_posts = $posts['count'] - $topics['count'];

			// Reset this forums stats
			$params = array(
				'last_poster_id'   => $last_post['last_poster_id'],
				'last_poster_name' => $last_post['last_poster_name'],
				'last_post'        => $last_post['last_post'],
				'last_title'       => $last_post['title'],
				'last_id'          => $last_post['tid'],
				'topics'           => $topics['count'],
				'posts'            => $real_posts
			);

			$stmt = $ibforums->db->prepare("UPDATE ibf_forums
			    SET " . IBPDO::compileKeyPairsString($params) . "
			    WHERE id='" . $fid . "'");
			$stmt->execute($params);
		}
	}

	//------------------------------------------------------
	// @stats_recount: Recount all topics & posts
	// -----------
	// Accepts: NOTHING
	// Returns: NOTHING (TRUE/FALSE)
	//------------------------------------------------------

	function stats_recount()
	{
		global $ibforums, $std;

		$topic = $ibforums->db->query("SELECT COUNT(tid) as tcount from ibf_topics WHERE approved=1")->fetch();

		$posts = $ibforums->db->query("SELECT COUNT(pid) as pcount from ibf_posts WHERE queued != 1")->fetch();

		$posts = $posts['pcount'] - $topics['tcount'];

		$ibforums->db->exec("UPDATE ibf_stats SET TOTAL_TOPICS=" . $topics['tcount'] . ", TOTAL_REPLIES=" . $posts);
	}

	// /Song * topic and forum recount within deleting of posts, 15.02.05
	//-------------------------------------------
	// Get a session based on the current session ID
	//-------------------------------------------

	function get_session($session_id = "")
	{
		global $ibforums, $std;

		$result = array();

		$query = "";

		$session_id = preg_replace("/([^a-zA-Z0-9])/", "", $session_id);

		if ($session_id)
		{
			if ($ibforums->vars['match_browser'] == 1)
			{
				$query = " AND browser='" . $this->user_agent . "'";
			}

			$stmt = $ibforums->db->query("SELECT
				id,
				member_id,
				running_time,
				location,
				in_forum,
				in_topic,
				r_location,
				r_in_forum,
				r_in_topic
			    FROM ibf_sessions
			    WHERE
				id='" . $session_id . "' and
				ip_address='" . $this->ip_address . "'" . $query);

			if ($stmt->rowCount() != 1)
			{
				// Either there is no session, or we have more than one session..
				$this->session_dead_id = $session_id;
				$this->session_id      = 0;
				$this->session_user_id = 0;

				return;
			} else
			{
				$result = $stmt->fetch();

				if ($result['id'] == "")
				{
					$this->session_dead_id = $session_id;
					$this->session_id      = 0;
					$this->session_user_id = 0;
					unset($result);
					return;
				} else
				{
					$this->session_id      = $result['id'];
					$this->session_user_id = $result['member_id'];
					$this->last_click      = $result['running_time'];

					$this->location   = $result['location'];
					$this->r_location = $result['r_location'];

					$this->in_forum   = $result['in_forum'];
					$this->r_in_forum = $result['r_in_forum'];

					$this->in_topic   = $result['in_topic'];
					$this->r_in_topic = $result['r_in_topic'];

					unset($result);
					return;
				}
			}
		}
	}

	//-----------------------------------------------------------
	function plg_get_session()
	{
		global $ibforums;

		if ($ibforums->vars['plg_custom_login'])
		{
			$ibforums->vars['plg_user_id'] = intval($ibforums->vars['plg_user_id']);
			if (!$ibforums->vars['plg_user_id'])
			{
				return NULL;
			}

			$stmt = $ibforums->db->query("SELECT password
			    FROM ibf_members
			    WHERE id='" . $ibforums->vars['plg_user_id'] . "'");
			if ($stmt->rowCount() != 1)
			{
				return NULL;
			}

			$pass = $stmt->fetch();
			if ($pass['password'] != $ibforums->vars['plg_user_pass'])
			{
				return NULL;
			}

			$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_sessions
			    WHERE member_id='" . $ibforums->vars['plg_user_id'] . "'");
			if ($stmt->rowCount() != 1)
			{
				return NULL;
			}

			$sess = $stmt->fetch();
			return $sess['id'];
		}

		return NULL;
	}

	//-------------------------------------------
	// Creates a member session.
	//-------------------------------------------

	function create_member_session()
	{
		global $std;
		$ibforums = Ibf::instance();

		if ($this->member['id'])
		{
			//---------------------------------
			// Remove the defunct sessions
			//---------------------------------

			$ibforums->vars['session_expiration'] = $ibforums->vars['session_expiration']
				? (time() - $ibforums->vars['session_expiration'])
				: (time() - 3600);

			$ibforums->db->exec("DELETE FROM ibf_sessions
			    WHERE running_time < {$ibforums->vars['session_expiration']}");

			$ibforums->db->exec("DELETE FROM ibf_sessions
			    WHERE member_id='" . $this->member['id'] . "'");

			$this->session_id = md5(uniqid(microtime()));

			//---------------------------------
			// Insert the new session
			//---------------------------------
			if (!isset($ibforums->input['act']))
			{
				$ibforums->input['act'] = '';
			}
			if (!isset($ibforums->input['f']))
			{
				$ibforums->input['f'] = '';
			}
			if (!isset($ibforums->input['t']))
			{
				$ibforums->input['t'] = '';
			}
			if (!isset($ibforums->input['p']))
			{
				$ibforums->input['p'] = '';
			}
			if (!isset($ibforums->input['CODE']))
			{
				$ibforums->input['CODE'] = '';
			}
			$locasion = $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'] . '|' . $this->location;

			if (!$this->in_forum)
			{
				$this->in_forum = '0';
			}
			if (!$this->in_topic)
			{
				$this->in_topic = '0';
			}

			$in_forum = intval($ibforums->input['f']) . ',' . $this->in_forum;
			$in_topic = intval($ibforums->input['t']) . ',' . $this->in_topic;

			$temp_loc = explode('|', $locasion);

			if (count($temp_loc) > 10)
			{
				$locasion = substr($locasion, 0, strrpos($locasion, '|'));
				$in_forum = substr($in_forum, 0, strrpos($in_forum, ','));
				$in_topic = substr($in_topic, 0, strrpos($in_topic, ','));
			}

			$data = array(
				'id'           => $this->session_id,
				'member_name'  => $this->member['name'],
				'member_id'    => $this->member['id'],
				'ip_address'   => $this->ip_address,
				'browser'      => $this->user_agent,
				'running_time' => $this->time_now,
				'login_type'   => $ibforums->input['Privacy'],
				'member_group' => $this->member['mgroup'],
				'org_perm_id'  => $this->member['org_perm_id'],
				'r_location'   => $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'],
				'location'     => $locasion,
				'r_in_topic'   => intval($ibforums->input['t']),
				'in_topic'     => $in_topic,
				'in_forum'     => $in_forum,
				'r_in_forum'   => intval($ibforums->input['f']),
			);
			$ibforums->db->insertRow('ibf_sessions', $data);

			// If this is a member, update their last visit times, etc.
			if (time() - $this->member['last_activity'] > 300)
			{
				//---------------------------------
				// Reset the topics read cookie..
				//---------------------------------

				$ibforums->db->exec("UPDATE ibf_members
				    SET
					last_visit=last_activity,
					last_activity='" . $this->time_now . "'
				    WHERE id='" . $this->member['id'] . "'");

				// Update the Last Visit time
				// (used at lookking for died accounts

				if ($this->member['check_id'])
				{
					$ibforums->db->exec("UPDATE ibf_check_members
					    SET last_visit='" . $this->time_now . "'
					    WHERE mid='" . $this->member['check_id'] . "'");
				}

				//---------------------------------
				// Fix up the last visit/activity times.
				//---------------------------------

				$ibforums->input['last_visit']    = $this->member['last_activity'];
				$ibforums->input['last_activity'] = $this->time_now;
			}

			// Song * who was today online (members)

			$std->who_was_member($this->member['id']);
		} else
		{
			$this->create_guest_session();
		}
	}

	//--------------------------------------------------------------------

	function create_guest_session()
	{
		global $std, $ibforums;
		$ibforums = Ibf::instance();

		//---------------------------------
		// Remove the defunct sessions
		//---------------------------------

		if (($this->session_dead_id != 0) and (!empty($this->session_dead_id)))
		{
			$extra = " or id='" . $this->session_dead_id . "'";
		} else
		{
			$extra = "";
		}

		$ibforums->vars['session_expiration'] = $ibforums->vars['session_expiration']
			? (time() - $ibforums->vars['session_expiration'])
			: (time() - 3600);

		$ibforums->db->exec("DELETE FROM ibf_sessions WHERE running_time < {$ibforums->vars['session_expiration']} or ip_address='" . $this->ip_address . "'" . $extra);

		$this->session_id = md5(uniqid(microtime()));

		//---------------------------------
		// Insert the new session
		//---------------------------------

		$data = array(
			'id'           => $this->session_id,
			'member_name'  => '',
			'member_id'    => 0,
			'ip_address'   => $this->ip_address,
			'browser'      => $this->user_agent,
			'running_time' => $this->time_now,
			'login_type'   => 0,
			'member_group' => $ibforums->vars['guest_group'],
		);

		$ibforums->db->insertRow('ibf_sessions', $data);

		// Song * who was today online (guest)

		$std->who_was_guest_or_bot('ibf_g_visitors', 'guests', $this->session_id, $this->ip_address);
	}

	//-------------------------------------------
	// Creates a BOT session
	//-------------------------------------------

	function create_bot_session($bot)
	{
		global $std;
		$ibforums = Ibf::instance();

		$session_id = $bot . "_session";

		if (!isset($ibforums->input['f']))
		{
			$ibforums->input['f'] = 0;
		}
		if (!isset($ibforums->input['t']))
		{
			$ibforums->input['t'] = 0;
		}
		if (!isset($ibforums->input['act']))
		{
			$ibforums->input['act'] = '';
		}
		if (!isset($ibforums->input['p']))
		{
			$ibforums->input['p'] = '';
		}
		if (!isset($ibforums->input['CODE']))
		{
			$ibforums->input['CODE'] = '';
		}

		$data = array(
			'id'           => $session_id,
			'member_name'  => $ibforums->vars['sp_' . $bot],
			'member_id'    => 0,
			'member_group' => $ibforums->vars['spider_group'],
			'in_forum'     => intval($ibforums->input['f']),
			'in_topic'     => intval($ibforums->input['t']),
			'login_type'   => $ibforums->vars['spider_anon'],
			'running_time' => $this->time_now,
			'location'     => $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'],
			'ip_address'   => $this->ip_address,
			'browser'      => $this->user_agent,
		);

		$ibforums->db->insertRow('ibf_sessions', $data, 'DELAYED');
		// Song * who was today online (bot)

		$std->who_was_guest_or_bot('ibf_b_visitors', 'bots', $session_id, $this->ip_address);
	}

	//-------------------------------------------
	// Updates a current session.
	//-------------------------------------------

	function update_member_session()
	{
		global $ibforums;

		// Make sure we have a session id.

		if (!$this->session_id)
		{
			$this->create_member_session();
			return;
		}

		if ((substr($this->r_location, 0, strpos($this->r_location, '|')) == $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE']) AND
		    (substr($this->r_in_forum, 0, strpos($this->r_in_forum, ',')) == intval($ibforums->input['f'])) AND
		    (substr($this->r_in_topic, 0, strpos($this->r_in_topic, ',')) == intval($ibforums->input['t']))
		)
		{

			return;
		} else
		{

			if (empty($this->member['id']))
			{
				$this->unload_member();
				$this->create_guest_session();
				return;
			}

			$locasion = $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'] . '|' . $this->location;

			if (!$this->in_forum)
			{
				$this->in_forum = '0';
			}
			if (!$this->in_topic)
			{
				$this->in_topic = '0';
			}

			$in_forum = intval($ibforums->input['f']) . ',' . $this->in_forum;
			$in_topic = intval($ibforums->input['t']) . ',' . $this->in_topic;

			$temp_loc = explode('|', $locasion);

			if (count($temp_loc) > 10)
			{
				$locasion = substr($locasion, 0, strrpos($locasion, '|'));
				$in_forum = substr($in_forum, 0, strrpos($in_forum, ','));
				$in_topic = substr($in_topic, 0, strrpos($in_topic, ','));
			}

			$db_str = array(
				'member_name'  => $this->member['name'],
				'member_id'    => intval($this->member['id']),
				'member_group' => $this->member['mgroup'],
				'in_forum'     => $in_forum,
				'r_in_forum'   => intval($ibforums->input['f']),
				'in_topic'     => $in_topic,
				'r_in_topic'   => intval($ibforums->input['t']),
				'login_type'   => $ibforums->input['Privacy'],
				'running_time' => $this->time_now,
				'location'     => $locasion,
				'r_location'   => $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'],
			);

			$stmt = $ibforums->db->prepare("UPDATE ibf_sessions
			    SET " . IBPDO::compileKeyPairsString($db_str) . "
			    WHERE id='{$this->session_id}'");
			$stmt->execute($db_str);
		}
	}

	//--------------------------------------------------------------------

	function update_guest_session()
	{
		global $ibforums;

		// Make sure we have a session id.
		if (!$this->session_id)
		{
			$this->create_guest_session();
			return;
		}

		if ((substr($this->r_location, 0, strpos($this->r_location, '|')) == $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE']) AND
		    (substr($this->r_in_forum, 0, strpos($this->r_in_forum, ',')) == intval($ibforums->input['f'])) AND
		    (substr($this->r_in_topic, 0, strpos($this->r_in_topic, ',')) == intval($ibforums->input['t']))
		)
		{
			return;
		} else
		{

			$locasion = $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'] . '|' . $this->location;
			if ($this->in_forum == '')
			{
				$this->in_forum = '0';
			}
			if ($this->in_topic == '')
			{
				$this->in_topic = '0';
			}
			$in_forum = intval($ibforums->input['f']) . ',' . $this->in_forum;
			$in_topic = intval($ibforums->input['t']) . ',' . $this->in_topic;

			$temp_loc = explode('|', $locasion);
			if (count($temp_loc) > 10)
			{
				$locasion = substr($locasion, 0, strrpos($locasion, '|'));
				$in_forum = substr($in_forum, 0, strrpos($in_forum, ','));
				$in_topic = substr($in_topic, 0, strrpos($in_topic, ','));
			}

			$db_str = array(
				'member_name'  => '',
				'member_id'    => 0,
				'member_group' => $ibforums->vars['guest_group'],
				'login_type'   => '0',
				'running_time' => $this->time_now,
				'in_forum'     => $in_forum,
				'r_in_forum'   => intval($ibforums->input['f']),
				'in_topic'     => $in_topic,
				'r_in_topic'   => intval($ibforums->input['t']),
				'location'     => $locasion,
				'r_location'   => $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'],
			);

			// Update the database
			$stmt = $ibforums->db->prepare("UPDATE ibf_sessions
		     SET " . IBPDO::compileKeyPairsString($db_str) . "
		     WHERE id='{$this->session_id}'");
			$stmt->execute($db_str);
		}
	}

	//-------------------------------------------
	// Updates BOT's current session.
	//-------------------------------------------

	function update_bot_session($bot)
	{
		global $ibforums;

		$session_id = $bot . '=' . str_replace('.', '', $this->ip_address) . '_session';

		$params = array(
			'member_name'  => $ibforums->vars['sp_' . $bot],
			'member_id'    => 0,
			'member_group' => $ibforums->vars['spider_group'],
			'in_forum'     => intval($ibforums->input['f']),
			'in_topic'     => intval($ibforums->input['t']),
			'login_type'   => $ibforums->vars['spider_anon'],
			'running_time' => $this->time_now,
			'location'     => $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE']
		);

		$stmt = $ibforums->db->prepare("UPDATE ibf_sessions SET " . IBPDO::compileKeyPairsString($params) . " WHERE id='" . $session_id . "'");
		$stmt->execute($params);
	}

}

// End of class Session
