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

    /**
     * @return array|Member
     */
    function authorise()
    {
        global $std;
        $ibforums = Ibf::app();

        //-------------------------------------------------
        // Before we go any lets check the load settings..
        //-------------------------------------------------

        if ($ibforums->vars['load_limit'] > 0) {
            if (file_exists('/proc/loadavg')) {
                if ($fh = @fopen('/proc/loadavg', 'r')) {
                    $data = @fread($fh, 6);
                    @fclose($fh);

                    $load_avg = explode(" ", $data);

                    $ibforums->server_load = trim($load_avg[0]);

                    if ($ibforums->server_load > $ibforums->vars['load_limit']) {
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

        if ($std->is_ip_banned($ibforums->input['IP_ADDRESS'])) {
            $std->Error(array(
                             'LEVEL' => 1,
                             'MSG'   => 'you_are_banned',
                             'EXTRA' => '[' . $ibforums->input['IP_ADDRESS'] . ']',
                             'INIT'  => 1
                        ));
        }

        //--------------------------------------------

        $this->member = new Member([
            'id'       => 0,
            'password' => "",
            'name'     => "",
            'mgroup'   => $ibforums->vars['guest_group']
        ]);

        $_SERVER['HTTP_USER_AGENT'] = $std->clean_value($_SERVER['HTTP_USER_AGENT']);

        $this->ip_address = $ibforums->input['IP_ADDRESS'];

        $this->user_agent = mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

        $this->time_now = time();

        //-------------------------------------------------
        // Manage bots? (tee-hee)
        //-------------------------------------------------

        if ($ibforums->vars['spider_sense'] == 1) {
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
            ) {
                $group = $ibforums->db->prepare("SELECT * FROM ibf_groups WHERE g_id=:id")
                    ->bindParam(':id', $ibforums->vars['spider_group'])
                    ->execute()
                    ->fetch();

                foreach ($group as $k => $v) {
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

                $match = mb_strtolower($match[1]);

                if (!$agent = $remap_agents[$match]) {
                    $agent = 'google';
                }

                if ($ibforums->vars['spider_visit']) {
                    $dba = [
                        'bot'          => $agent,
                        'query_string' => str_replace("'", "", $_SERVER['QUERY_STRING']),
                        'ip_address'   => $_SERVER['REMOTE_ADDR'],
                        'entry_date'   => time(),
                    ];

                    $ibforums->db->insertRow('ibf_spider_logs', $dba);
                }

                if ($ibforums->vars['spider_active']) {
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

        if (isset($ibforums->vars['plg_custom_login']) && $ibforums->vars['plg_custom_login']) {
            $plg_sess = $this->plg_get_session();
            if ($plg_sess) {
                $this->get_session($plg_sess);
            } else {
                $this->session_id = 0;
            }
        } elseif ($cookie['session_id']) {
            $this->get_session($cookie['session_id']);
            $ibforums->session_type = 'cookie';
        } elseif (isset($ibforums->input['s'])) {
            $this->get_session($ibforums->input['s']);
            $ibforums->session_type = 'url';
        } else {
            $this->session_id = 0;
        }

        //-------------------------------------------------
        // Finalise the incoming data..
        //-------------------------------------------------
        //Schooler invisible login patch
        if (!isset($ibforums->input['Privacy'])) {
            $ibforums->input['Privacy'] = $std->my_getcookie('anonlogin');
        }

        //-------------------------------------------------
        // Do we have a valid session ID?
        //-------------------------------------------------

        if ($this->session_id) {
            // We've checked the IP addy and browser, so we can assume that this is
            // a valid session.

            if ($this->session_user_id != 0 and !empty($this->session_user_id)) {
                // It's a member session, so load the member.
                $this->load_member($this->session_user_id);

                // Did we get a member?
                if (!$this->member['id'] or $this->member['id'] == 0) {
                    $this->unload_member();
                    $this->update_guest_session();
                } else {
                    $this->update_member_session();
                }
            } else {
                $this->update_guest_session();
            }
        } else // We didn't have a session, or the session didn't validate
        {
            // Forumizer auth
            if (isset($ibforums->vars['plg_custom_login']) && $ibforums->vars['plg_custom_login']) {
                $this->load_member($ibforums->vars['plg_user_id']);

                // such member exists ?
                if (!$this->member['id'] or $this->member['id'] == 0) {
                    $this->unload_member();

                    if (!$ibforums->info['plg_allow_guests']) {
                        $std->Error(array(
                                         'LEVEL' => 1,
                                         'MSG'   => 'no_guests',
                                         'INIT'  => 1
                                    ));
                    }

                    $this->create_guest_session();
                } else {
                    // check a password
                    if ($this->member['password'] == $ibforums->vars['plg_user_pass']) {
                        $this->create_member_session();
                    } else {
                        $this->unload_member();

                        if (!$ibforums->info['plg_allow_guests']) {
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
            } elseif ($cookie['member_id'] != "") {
                $this->load_member($cookie['member_id']);

                // such member exists ?
                if (!$this->member['id'] or $this->member['id'] == 0) {
                    $this->unload_member();
                    $this->create_guest_session();
                } else {
                    // check a password
                    if (AuthBasic::checkSessionDataIsValid($this->member)) {
                        $this->create_member_session();
                    } else {
                        $this->unload_member();
                        $this->create_guest_session();
                    }
                }
            } else {
                $this->create_guest_session();
            }
        }

        //-------------------------------------------------
        // Set up a guest if we get here and we don't have a member ID
        //-------------------------------------------------

        if (!$this->member['id']) {
            $this->member = new Member($std->set_up_guest());

            $stmt = $ibforums->db->prepare("SELECT * FROM ibf_groups WHERE g_id=?");
            $stmt->execute([$ibforums->vars['guest_group']]);

            $group = $stmt->fetch();

            if (is_array($group)) {
                foreach ($group as $k => $v) {
                    $this->member[$k] = $v;
                }
            }
        } else {
            //------------------------------------------------
            // Synchronise the last visit and activity times if
            // we have some in the member profile
            //-------------------------------------------------

            if (!$ibforums->input['last_activity']) {
                if ($this->member['last_activity']) {
                    $ibforums->input['last_activity'] = $this->member['last_activity'];
                } else {
                    $ibforums->input['last_activity'] = $this->time_now;
                }
            }

            if (!$ibforums->input['last_visit']) {
                if ($this->member['last_visit']) {
                    $ibforums->input['last_visit'] = $this->member['last_visit'];
                } else {
                    $ibforums->input['last_visit'] = $this->time_now;
                }
            }

            //-------------------------------------------------
            // If there hasn't been a cookie update in 2 hours,
            // we assume that they've gone and come back
            //-------------------------------------------------

            if (!$this->member['last_visit']) {
                // No last visit set, do so now!
                $stmt = $ibforums->db->prepare("UPDATE ibf_members
				    SET
					last_visit=?,
					last_activity=?
				    WHERE id=?");
                $stmt->execute([$this->time_now, $this->time_now, $this->member['id']]);
            } elseif ((time() - $ibforums->input['last_activity']) > 300) {
                // If the last click was longer than 5 mins ago and this is a member
                // Update their profile.
                $stmt = $ibforums->db->prepare("UPDATE ibf_members
				    SET last_activity=?
				    WHERE id=?");
                $stmt->execute([$this->time_now, $this->member['id']]);
            }

            //-------------------------------------------------
            // Check ban status
            //-------------------------------------------------

            if ($this->member['temp_ban']) {
                if ($this->member['temp_ban'] == 1) {
                    $std->Error(array(
                                     'LEVEL' => 1,
                                     'MSG'   => 'you_are_banned',
                                     //                             'EXTRA' => '['.$ibforums->input['IP_ADDRESS'].']',
                                     'INIT'  => 1
                                ));
                } else {
                    $ban_arr = $std->hdl_ban_line($this->member['temp_ban']);

                    if (time() >= $ban_arr['date_end']) {
                        // Update this member's profile
                        $stmt = $ibforums->db->prepare("UPDATE ibf_members
						    SET temp_ban=''
						    WHERE id=?");
                        $stmt->execute([$this->member['id']]);
                    } else {
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

        // additional permissions based on a member
        $org_perm_id = isset($this->member['org_perm_id'])
            ? $this->member['org_perm_id']
            : '';
        if ($org_perm_id) {
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
        $ibforums = Ibf::app();

        $member_id = intval($member_id);

        if ($member_id != 0) {
            $stmt = $ibforums->db->prepare("SELECT
				m.*,
				g.*,
				IFNULL(es.name,'Main') as sskin_name,
				md.mid as is_mod,
				md.allow_warn,
				md.time_deleted_link
			FROM ibf_members m
            		LEFT JOIN ibf_groups g
				ON (g.g_id=m.mgroup)
			LEFT JOIN ibf_emoticons_skins es
				ON (es.id=m.sskin_id)
			LEFT JOIN ibf_moderators md ON (md.member_id=m.id)
            		WHERE m.id=?
			LIMIT 1");
            $stmt->execute([$member_id]);

            if ($stmt->rowCount()) {
                $this->member = new Member($stmt->fetch());

                if ($this->member['id']) {
                    $stmt = $ibforums->db->prepare("SELECT read_perms
					FROM ibf_forums
					WHERE id=?");
                    $stmt->execute([$ibforums->vars['club']]);

                    if ($club = $stmt->fetch()) {
                        $this->member['club_perms'] = $club['read_perms'];
                    }

                    // todo BAD MESSAGE! yesterday, today
                    // today/yesterday

                    if ($this->member['language'] == 'en') {
                        $this->member['yesterday'] = 'Yesterday';
                        $this->member['today']     = 'Today';

                        if ($this->member['disable_mail'] and !$this->member['disable_mail_reason']) {
                            $this->member['disable_mail_reason'] = "(no information)";
                        }
                    } else {
                        $this->member['yesterday'] = 'Вчера';
                        $this->member['today']     = 'Сегодня';

                        if ($this->member['disable_mail'] and !$this->member['disable_mail_reason']) {
                            $this->member['disable_mail_reason'] = "(нет информации)";
                        }
                    }

                    $time = time();

                    /**
                     * Publish the moderated forums list
                     * Hint: Result of query include forums, moderated by groups, rather than prev.
                     *
                     **/

                    $this->member['modforums'] = false;
                    $mod                       = array();

                    $stmt = $ibforums->db->query("SELECT forum_id
					    FROM ibf_moderators
					    WHERE member_id='" . $this->member['id'] . "' or group_id='" . $this->member['mgroup'] . "'");

                    foreach ($stmt as $row) {
                        $mod[] = $row['forum_id'];
                    }
                    if (count($mod)) {
                        $this->member['modforums'] = implode(',', $mod);
                    }
                    unset($mod);

                    // if this is a user
                    if (!$this->member['is_mod']) {
                        // ********* REMOVE WARNINGS of current member *****************************************
                        // if user group is not banned
                        if ($this->member['mgroup'] != $ibforums->vars['ban_group']) {
                            $stmt = $ibforums->db->query("SELECT id
							   FROM ibf_warnings
							   WHERE
								mid='" . $this->member['id'] . "' and
								RestrictDate<" . $time . "
							   ORDER BY RestrictDate DESC
							   LIMIT 1");

                            if ($stmt->rowCount()) {
                                $row = $stmt->fetch();

                                $level = intval($this->member['warn_level']);

                                // decrease level
                                $level--;

                                // define a new group of user
                                if ($level > 0) {
                                    $group = 15 + $level;
                                } else {
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
                                // todo BAD MESSAGE!
                                $mes = "Уменьшен уровень Ваших предупреждений в связи с истечением срока действия одного из них.\n\n";

                                if ($group == $ibforums->vars['member_group'] or $group == $ibforums->vars['club_group'] or $group == 26 or $group == 9) {
                                    // todo BAD MESSAGE!
                                    $mes .= "[color=green]Вы обратно переведены в группу участников.[/color]";
                                } else {
                                    // todo BAD MESSAGE!
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
                    ) {
                        // ***********************************************************************************************************************
                        // ************************** BEGIN ADMINS TASKS *****************************************************
                        // ************************* for admins-moderators ***************************************************
                        // ***********************************************************************************************************************
                        // Delete users that marked their accounts to deleting
                        //todo move to cron
                        if ($this->member['mgroup'] == $ibforums->vars['admin_group']) {
                            $ids = array();

                            $stmt = $ibforums->db->prepare("SELECT id
						      FROM ibf_members
									WHERE profile_delete_time != 0 and profile_delete_time < ?")
                                ->execute([$time]);

                            while ($member = $stmt->fetch()) {
                                $ids[] = $member['id'];
                            }

                            if (count($ids)) {
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

            if ($this->member['id'] == 0 or empty($this->member['id'])) {
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

    //---------------------------------------------------
    function clear_posts(PDOStatementWrapper $stmt, &$topics, &$forums)
    {
        global $ibforums;

        foreach ($stmt as $row) {
            $forums[$row['forum_id']] = $row['forum_id'];
            $topics[$row['topic_id']] = $row['topic_id'];

            // delete attached files
            Attachment::deleteAllPostAttachments($row);
        }
    }

    //-------------------------------------------
    // Get a session based on the current session ID
    //-------------------------------------------

    function get_session($session_id = "")
    {
        global $ibforums, $std;

        $result = array();

        $query = "";

        $session_id = preg_replace("/([^a-zA-Z0-9])/", "", $session_id);

        if ($session_id) {
            if ($ibforums->vars['match_browser'] == 1) {
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

            if ($stmt->rowCount() != 1) {
                // Either there is no session, or we have more than one session..
                $this->session_dead_id = $session_id;
                $this->session_id      = 0;
                $this->session_user_id = 0;

                return;
            } else {
                $result = $stmt->fetch();

                if ($result['id'] == "") {
                    $this->session_dead_id = $session_id;
                    $this->session_id      = 0;
                    $this->session_user_id = 0;
                    unset($result);
                    return;
                } else {
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

        if ($ibforums->vars['plg_custom_login']) {
            $ibforums->vars['plg_user_id'] = intval($ibforums->vars['plg_user_id']);
            if (!$ibforums->vars['plg_user_id']) {
                return null;
            }

            $stmt = $ibforums->db->query("SELECT password
			    FROM ibf_members
			    WHERE id='" . $ibforums->vars['plg_user_id'] . "'");
            if ($stmt->rowCount() != 1) {
                return null;
            }

            $pass = $stmt->fetch();
            if ($pass['password'] != $ibforums->vars['plg_user_pass']) {
                return null;
            }

            $stmt = $ibforums->db->query("SELECT id
			    FROM ibf_sessions
			    WHERE member_id='" . $ibforums->vars['plg_user_id'] . "'");
            if ($stmt->rowCount() != 1) {
                return null;
            }

            $sess = $stmt->fetch();
            return $sess['id'];
        }

        return null;
    }

    //-------------------------------------------
    // Creates a member session.
    //-------------------------------------------
    function create_member_session()
    {
        global $std;
        $ibforums = Ibf::app();

        if ($this->member['id']) {
            //---------------------------------
            // Remove the defunct sessions
            //---------------------------------

            $ibforums->vars['session_expiration'] = $ibforums->vars['session_expiration']
                ? (time() - $ibforums->vars['session_expiration'])
                : (time() - 3600);

            $ibforums->db->exec("DELETE FROM ibf_sessions
			    WHERE running_time < {$ibforums->vars['session_expiration']} LIMIT 10");

            $ibforums->db->exec("DELETE FROM ibf_sessions
			    WHERE member_id='" . $this->member['id'] . "'");

            $this->session_id = md5(uniqid(microtime()));

            //---------------------------------
            // Insert the new session
            //---------------------------------
            if (!isset($ibforums->input['act'])) {
                $ibforums->input['act'] = '';
            }
            if (!isset($ibforums->input['f'])) {
                $ibforums->input['f'] = '';
            }
            if (!isset($ibforums->input['t'])) {
                $ibforums->input['t'] = '';
            }
            if (!isset($ibforums->input['p'])) {
                $ibforums->input['p'] = '';
            }
            if (!isset($ibforums->input['CODE'])) {
                $ibforums->input['CODE'] = '';
            }
            $locasion = $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'] . '|' . $this->location;

            if (!$this->in_forum) {
                $this->in_forum = '0';
            }
            if (!$this->in_topic) {
                $this->in_topic = '0';
            }

            $in_forum = intval($ibforums->input['f']) . ',' . $this->in_forum;
            $in_topic = intval($ibforums->input['t']) . ',' . $this->in_topic;

            $temp_loc = explode('|', $locasion);

            if (count($temp_loc) > 10) {
                $locasion = mb_substr($locasion, 0, mb_strrpos($locasion, '|'));
                $in_forum = mb_substr($in_forum, 0, mb_strrpos($in_forum, ','));
                $in_topic = mb_substr($in_topic, 0, mb_strrpos($in_topic, ','));
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
            if (time() - $this->member['last_activity'] > 300) {
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

                //---------------------------------
                // Fix up the last visit/activity times.
                //---------------------------------

                $ibforums->input['last_visit']    = $this->member['last_activity'];
                $ibforums->input['last_activity'] = $this->time_now;
            }

            $std->who_was_member($this->member['id']);
        } else {
            $this->create_guest_session();
        }
    }

    //--------------------------------------------------------------------

    function create_guest_session()
    {
        global $std, $ibforums;
        $ibforums = Ibf::app();

        //---------------------------------
        // Remove the defunct sessions
        //---------------------------------

        if (($this->session_dead_id != 0) and (!empty($this->session_dead_id))) {
            $extra = " or id='" . $this->session_dead_id . "'";
        } else {
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

        $std->who_was_guest_or_bot('ibf_g_visitors', 'guests', $this->session_id, $this->ip_address);
    }

    //-------------------------------------------
    // Creates a BOT session
    //-------------------------------------------

    function create_bot_session($bot)
    {
        global $std;
        $ibforums = Ibf::app();

        $session_id = $bot . "_session";

        if (!isset($ibforums->input['f'])) {
            $ibforums->input['f'] = 0;
        }
        if (!isset($ibforums->input['t'])) {
            $ibforums->input['t'] = 0;
        }
        if (!isset($ibforums->input['act'])) {
            $ibforums->input['act'] = '';
        }
        if (!isset($ibforums->input['p'])) {
            $ibforums->input['p'] = '';
        }
        if (!isset($ibforums->input['CODE'])) {
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

        $std->who_was_guest_or_bot('ibf_b_visitors', 'bots', $session_id, $this->ip_address);
    }

    //-------------------------------------------
    // Updates a current session.
    //-------------------------------------------

    function update_member_session()
    {
        global $ibforums;

        // Make sure we have a session id.

        if (!$this->session_id) {
            $this->create_member_session();
            return;
        }

        if ((mb_substr($this->r_location, 0, mb_strpos($this->r_location, '|')) == $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE']) and
            (mb_substr($this->r_in_forum, 0, mb_strpos($this->r_in_forum, ',')) == intval($ibforums->input['f'])) and
            (mb_substr($this->r_in_topic, 0, mb_strpos($this->r_in_topic, ',')) == intval($ibforums->input['t']))
        ) {
            return;
        } else {
            if (empty($this->member['id'])) {
                $this->unload_member();
                $this->create_guest_session();
                return;
            }

            $locasion = $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'] . '|' . $this->location;

            if (!$this->in_forum) {
                $this->in_forum = '0';
            }
            if (!$this->in_topic) {
                $this->in_topic = '0';
            }

            $in_forum = intval($ibforums->input['f']) . ',' . $this->in_forum;
            $in_topic = intval($ibforums->input['t']) . ',' . $this->in_topic;

            $temp_loc = explode('|', $locasion);

            if (count($temp_loc) > 10) {
                $locasion = mb_substr($locasion, 0, mb_strrpos($locasion, '|'));
                $in_forum = mb_substr($in_forum, 0, mb_strrpos($in_forum, ','));
                $in_topic = mb_substr($in_topic, 0, mb_strrpos($in_topic, ','));
            }

            $db_str = array(
                'member_name'  => $this->member['name'],
                'member_id'    => intval($this->member['id']),
                'member_group' => $this->member['mgroup'],
                'in_forum'     => $in_forum,
                'r_in_forum'   => intval($ibforums->input['f']),
                'in_topic'     => $in_topic,
                'r_in_topic'   => intval($ibforums->input['t']),
                'login_type'   => $ibforums->input['Privacy'] ?: 0,
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
        if (!$this->session_id) {
            $this->create_guest_session();
            return;
        }

        if ((mb_substr($this->r_location, 0, mb_strpos($this->r_location, '|')) == $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE']) and
            (mb_substr($this->r_in_forum, 0, mb_strpos($this->r_in_forum, ',')) == intval($ibforums->input['f'])) and
            (mb_substr($this->r_in_topic, 0, mb_strpos($this->r_in_topic, ',')) == intval($ibforums->input['t']))
        ) {
            return;
        } else {
            $locasion = $ibforums->input['act'] . "," . $ibforums->input['p'] . "," . $ibforums->input['CODE'] . '|' . $this->location;
            if ($this->in_forum == '') {
                $this->in_forum = '0';
            }
            if ($this->in_topic == '') {
                $this->in_topic = '0';
            }
            $in_forum = intval($ibforums->input['f']) . ',' . $this->in_forum;
            $in_topic = intval($ibforums->input['t']) . ',' . $this->in_topic;

            $temp_loc = explode('|', $locasion);
            if (count($temp_loc) > 10) {
                $locasion = mb_substr($locasion, 0, mb_strrpos($locasion, '|'));
                $in_forum = mb_substr($in_forum, 0, mb_strrpos($in_forum, ','));
                $in_topic = mb_substr($in_topic, 0, mb_strrpos($in_topic, ','));
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
