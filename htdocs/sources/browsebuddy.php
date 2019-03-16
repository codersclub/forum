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
|   > Browse Buddy Module
|   > Module written by Matt Mecham
|   > Date started: 2nd July 2002
|
|   > Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

use Views\View;

$idx = new buddy();

class buddy
{

    var $output     = "";
    var $page_title = "";
    var $nav        = array();



    function buddy()
    {
        global $std, $print;

        $ibforums = Ibf::app();

        //--------------------------------------------
        // Require the HTML and language modules
        //--------------------------------------------

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_buddy', $ibforums->lang_id);

        //--------------------------------------------
        // What to do?
        //--------------------------------------------

        switch ($ibforums->input['code']) {
            default:
                $this->splash();
                break;
        }

        // If we have any HTML to print, do so...

        $this->output = str_replace("<!--CLOSE.LINK-->", View::make("buddy.closelink"), $this->output);
        $print->js->addLocal('buddy.js');
        $print->pop_up_window($ibforums->lang['page_title'], $this->output);
    }

    function splash()
    {
        global $std;

        $ibforums = Ibf::app();

        //--------------------------------------------
        // Is this a guest? If so, get 'em to log in.
        //--------------------------------------------

        if (! $ibforums->member['id']) {
            $this->output = View::make("buddy.login");
            return;
        } else {
            //--------------------------------------------
            // Get the forums we're allowed to search in
            //--------------------------------------------

            $allow_forums   = array();

            $allow_forums[] = '0';

            $result = $ibforums->db->query("SELECT id, read_perms, password FROM ibf_forums");

            foreach ($result as $i) {
                $pass = 1;

                if ($i['password'] != "") {
                    if (! $c_pass = $std->my_getcookie('iBForum' . $i['id'])) {
                        $pass = 0;
                    }

                    if ($c_pass == $i['password']) {
                        $pass = 1;
                    } else {
                        $pass = 0;
                    }
                }

                if ($pass == 1) {
                    if ($std->check_perms($i['read_perms']) == true) {
                        $allow_forums[] = $i['id'];
                    }
                }
            }

            $forum_string = implode(",", $allow_forums);
            $q_string = IBPDO::placeholders($allow_forums);
            $params = $allow_forums;

            //--------------------------------------------
            // Get the number of posts since the last visit.
            //--------------------------------------------

            if (! $ibforums->member['last_visit']) {
                $ibforums->member['last_visit'] = time() - 3600;
            }
            $params[] = $ibforums->member['last_visit'];

            $stmt = $ibforums->db->prepare("SELECT COUNT(pid) as posts FROM ibf_posts WHERE forum_id IN(" . $q_string . ") AND post_date > ? AND queued <> 1 ");
            $stmt->execute($params);
            $posts = $stmt->fetchColumn();

            $posts_total = ($posts < 1) ? 0 : $posts;

            //-----------------------------------------------------------------------
            // Get the number of posts since the last visit to topics we've started.
            //-----------------------------------------------------------------------

            $stmt = $ibforums->db->prepare("SELECT COUNT(tid) as replies
 						FROM ibf_topics WHERE
						forum_id IN($q_string)
						AND last_post > ?
 						AND approved=1
 						AND posts > 0
 						AND starter_id= ?
						");
            $stmt->execute(array_merge($allow_forums, [$ibforums->member['last_visit']], [$ibforums->member['id']]));

            $topic = $stmt->fetchColumn();

            $topics_total = ($topic < 1) ? 0 : $topic;

            $text = $ibforums->lang['no_new_posts'];

            if ($posts_total > 0) {
                $ibforums->lang['new_posts']  = sprintf($ibforums->lang['new_posts'], $posts_total);
                $ibforums->lang['my_replies'] = sprintf($ibforums->lang['my_replies'], $topics_total);

//              $ibforums->lang['new_posts'] .= View::Make("buddy.append_view", ['url' => '&act=Select&CODE=getnew']);
                $ibforums->lang['new_posts'] .= View::make("buddy.append_view", ['url' => '&act=Select&CODE=getnew']);

                if ($topic > 0) {
                    $ibforums->lang['my_replies'] .= View::make(
                        "buddy.append_view",
                        ['url' => '&act=Select&CODE=getreplied']
                    );
                }

                $text = View::make("buddy.build_away_msg");
            }


            $this->output = View::make("buddy.main", ['away_text' => $text]);
        }
    }
}
