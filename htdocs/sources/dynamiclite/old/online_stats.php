<?php

class online_stats {
                 //*********************************************/
                // Add in show online users
                //*********************************************/

                var $output = "";
                var $html = "";

                function show_stats() {
                global $DB, $ibforums, $std;

                $this->html = $std->load_template('skin_boards');
                $ibforums->lang = $std->load_words($ibforums->lang, 'lang_boards', $ibforums->lang_id );

                $active = array( 'TOTAL'   => 0 ,
                                                 'NAMES'   => "",
                                                 'GUESTS'  => 0 ,
                                                 'MEMBERS' => 0 ,
                                                 'ANON'    => 0 ,
                                           );

                $stats_html = "";

                if ($ibforums->vars['show_active'])
                {

                        if ($ibforums->vars['au_cutoff'] == "")
                        {
                                $ibforums->vars['au_cutoff'] = 15;
                        }

                        // Get the users from the DB

                        $cut_off = $ibforums->vars['au_cutoff'] * 60;
                        $time    = time() - $cut_off;


                        $DB->query("SELECT s.id, s.member_id, s.member_name, s.login_type, g.suffix, g.prefix
                                    FROM ibf_sessions s
                                      LEFT JOIN ibf_groups g ON (g.g_id=s.member_group)
                                    WHERE running_time > $time
                                    ORDER BY s.running_time DESC");

                        // cache all printed members so we don't double print them

                        $cached = array();

                        while ($result = $DB->fetch_row() )
                        {
                                if ( strstr( $result['id'], '_session' ) )
                                {
                                        if ( $ibforums->vars['spider_anon'] )
                                        {
                                                if ( $ibforums->member['mgroup'] == $ibforums->vars['admin_group'] )
                                                {
                                                        $active['NAMES'] .= "{$result['member_name']}*{$this->sep_char} \n";
                                                }
                                        }
                                        else
                                        {
                                                $active['NAMES'] .= "{$result['member_name']}{$this->sep_char} \n";
                                        }
                                }
                                else if ($result['member_id'] == 0 )
                                {
                                        $active['GUESTS']++;
                                }
                                else
                                {
                                        if ( empty( $cached[ $result['member_id'] ] ) )
                                        {
                                                $cached[ $result['member_id'] ] = 1;
                                                if ($result['login_type'] == 1)
                                                {
                                                        if ( ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and ($ibforums->vars['disable_admin_anon'] != 1) )
                                                        {
                                                                $active['NAMES'] .= "<a href='{$ibforums->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*{$this->sep_char} \n";
                                                                $active['ANON']++;
                                                        }
                                                        else
                                                        {
                                                                $active['ANON']++;
                                                        }
                                                }
                                                else
                                                {
                                                        $active['MEMBERS']++;
                                                        $active['NAMES'] .= "<a href='{$ibforums->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>{$this->sep_char} \n";
                                                }
                                        }
                                }
                        }

                        $active['NAMES'] = preg_replace( "/".preg_quote($this->sep_char)."$/", "", trim($active['NAMES']) );

                        $active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];


                        // Show a link?

                        if ($ibforums->vars['allow_online_list'])
                        {
                                $active['links'] = $this->html->active_user_links();
                        }

                        $ibforums->lang['active_users'] = sprintf( $ibforums->lang['active_users'], $ibforums->vars['au_cutoff'] );

                        $stats_html .= $this->html->ActiveUsers($active, $ibforums->vars['au_cutoff']);
                }

                //-----------------------------------------------
                // Are we viewing the calendar?
                //-----------------------------------------------

                if ($ibforums->vars['show_birthdays'])
                {

                        $a = explode( ',', gmdate( 'Y,n,j,G,i,s', time() + $std->get_time_offset() ) );

                        $day   = $a[2];
                        $month = $a[1];
                        $year  = $a[0];

                        $birthstring = "";
                        $count       = 0;

                        $DB->query("SELECT id, name, bday_day as DAY, bday_month as MONTH, bday_year as YEAR
                                                FROM ibf_members WHERE bday_day=$day and bday_month=$month");

                        while ( $user = $DB->fetch_row() )
                        {
                                $birthstring .= "<a href='{$this->base_url}showuser={$user['id']}'>{$user['name']}</a>";

                                if ($user['YEAR'])
                                {
                                        $pyear = $year - $user['YEAR'];  // $year = 2002 and $user['YEAR'] = 1976
                                        $birthstring .= "(<b>$pyear</b>)";
                                }

                                $birthstring .= $this->sep_char."\n";

                                $count++;
                        }

                        $birthstring = preg_replace( "/".$this->sep_char."$/", "", trim($birthstring) );

                        $lang = $ibforums->lang['no_birth_users'];

                        if ($count > 0)
                        {
                                $lang = ($count > 1) ? $ibforums->lang['birth_users'] : $ibforums->lang['birth_user'];
                                $stats_html .= $this->html->birthdays( $birthstring, $count, $lang  );
                        }
                        else
                        {
                                $count = "";

                                if ( ! $ibforums->vars['autohide_bday'] )
                                {
                                        $stats_html .= $this->html->birthdays( $birthstring, $count, $lang  );
                                }
                        }
                }


                //-----------------------------------------------
                // Are we viewing the calendar?
                //-----------------------------------------------

                if ($ibforums->vars['show_calendar'])
                {

                        if ($ibforums->vars['calendar_limit'] < 2)
                        {
                                $ibforums->vars['calendar_limit'] = 2;
                        }

                        $our_unix         = time() + $std->get_time_offset();
                        $max_date         = $our_unix + ($ibforums->vars['calendar_limit'] * 86400);

                        $DB->query("SELECT eventid, title, read_perms, priv_event, userid, unix_stamp
                                    FROM ibf_calendar_events WHERE unix_stamp > $our_unix and unix_stamp < $max_date ORDER BY unix_stamp ASC");

                        $show_events = array();

                        while ($event = $DB->fetch_row())
                        {
                                if ($event['priv_event'] == 1 and $ibforums->member['id'] != $event['userid'])
                                {
                                        continue;
                                }

                                //-----------------------------------------
                                // Do we have permission to see the event?
                                //-----------------------------------------

                                if ( $event['read_perms'] != '*' )
                                {
                                        if ( ! preg_match( "/(^|,)".$ibforums->member['mgroup']."(,|$)/", $event['read_perms'] ) )
                                        {
                                                continue;
                                        }
                                }

                                $c_time = date( 'j-F-y', $event['unix_stamp']);

                                $show_events[] = "<a href='{$ibforums->base_url}act=calendar&amp;code=showevent&amp;eventid={$event['eventid']}' title='$c_time'>".$event['title']."</a>";
                        }

                        $ibforums->lang['calender_f_title'] = sprintf( $ibforums->lang['calender_f_title'], $ibforums->vars['calendar_limit'] );

                        if ( count($show_events) > 0 )
                        {
                                $event_string = implode( $this->sep_char.' ', $show_events );
                                $stats_html .= $this->html->calendar_events( $event_string  );
                        }
                        else
                        {
                                if ( ! $ibforums->vars['autohide_calendar'] )
                                {
                                        $event_string = $ibforums->lang['no_calendar_events'];
                                        $stats_html .= $this->html->calendar_events( $event_string  );
                                }
                        }
                }

                //*********************************************/
                // Add in show stats
                //*********************************************/


                if ($ibforums->vars['show_totals'])
                {

                        $DB->query("SELECT * FROM ibf_stats");
                        $stats = $DB->fetch_row();

                        // Update the most active count if needed

                        if ($active['TOTAL'] > $stats['MOST_COUNT'])
                        {
                                $DB->query("UPDATE ibf_stats SET MOST_DATE='".time()."', MOST_COUNT='".$active[TOTAL]."'");
                                $stats['MOST_COUNT'] = $active[TOTAL];
                                $stats['MOST_DATE']  = time();
                        }

                        $most_time = $std->get_date( $stats['MOST_DATE'], 'LONG' );

                        $ibforums->lang['most_online'] = str_replace( "<#NUM#>" ,   $std->do_number_format($stats['MOST_COUNT'])  , $ibforums->lang['most_online'] );
                        $ibforums->lang['most_online'] = str_replace( "<#DATE#>",                   $most_time                    , $ibforums->lang['most_online'] );

                        $total_posts = $stats['TOTAL_REPLIES'] + $stats['TOTAL_TOPICS'];

                        $total_posts        = $std->do_number_format($total_posts);
                        $stats['MEM_COUNT'] = $std->do_number_format($stats['MEM_COUNT']);

                        $link = $ibforums->base_url."showuser=".$stats['LAST_MEM_ID'];

                        $ibforums->lang['total_word_string'] = str_replace( "<#posts#>" , "$total_posts"          , $ibforums->lang['total_word_string'] );
                        $ibforums->lang['total_word_string'] = str_replace( "<#reg#>"   , $stats['MEM_COUNT']     , $ibforums->lang['total_word_string'] );
                        $ibforums->lang['total_word_string'] = str_replace( "<#mem#>"   , $stats['LAST_MEM_NAME'] , $ibforums->lang['total_word_string'] );
                        $ibforums->lang['total_word_string'] = str_replace( "<#link#>"  , $link                   , $ibforums->lang['total_word_string'] );

                        $stats_html .= $this->html->ShowStats($ibforums->lang['total_word_string']);

                }

                if ($stats_html != "")
                {
                        $this->output .= $this->html->stats_header();
                        $this->output .= $stats_html;
                        $this->output .= $this->html->stats_footer();
                }

                //---------------------------------------
                // Add in board info footer
                //---------------------------------------

                $this->output .= $this->html->bottom_links();

                return $this->output;
      }
}
?>
