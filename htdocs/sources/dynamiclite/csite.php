<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2 (Click Site Module)
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
|   > Click site core module
|   > Module written by Matt Mecham
|   > Date started: 1st July 2003
|
|  > Module Version Number: 1.0.0
+--------------------------------------------------------------------------
|  IPB Dynamic Lite Modifications (D-Site CMS)
|  written by Anton (Chainick), Copyright (c) 2004-2006
|  version: 0.2.4
*---------------------------------------------------------------------------
*/

class csite {

    var $output     = "";
    var $html       = "";
    var $template   = "";
    var $site_bits  = array();
    var $parser     = "";
    var $raw        = "";
    var $nav        = "";
    var $title      = "";
    var $article      = "";

    function click_site()
    {
            global $ibforums, $DB, $std, $print, $NAV, $ART, $USR, $CAT, $FAV;

            //--------------------------------------------
            // Require the HTML and language modules
            //--------------------------------------------

            if ( ! $ibforums->vars['csite_on'] )
            {
                    print "IPDynamic Lite has not been enabled. Please check your Invision Power Board Admin Settings";
                    exit();
            }

            $this->html = $std->load_template('skin_csite');

            if ( !$ART->parser ) {

                    require ROOT_PATH."sources/lib/post_parser.php";
                    $this->parser = new post_parser();

            } else {

                    $this->parser = $ART->parser;
            }

            if ( $ibforums->vars['dynamiclite'] == "" ) {

                    $ibforums->vars['dynamiclite'] = $ibforums->base_url.'act=home';
            }

            $this->template = $this->html->csite_skeleton_template();

              //--------------------------------------------
             // Get site nav / favourites
            //--------------------------------------------

            $DB->query("SELECT cs_key, cs_value FROM ibf_cache_store WHERE cs_key IN ('csite_nav_contents', 'csite_fav_contents')");

            while ( $row = $DB->fetch_row() ) {

                    $this->raw[ $row['cs_key'] ] = str_replace( '&#39;', "'", str_replace( "\r\n", "\n", $row['cs_value'] ) );
            }

            if ( intval($ibforums->input['get_file']) != 0 ) {

                    $CAT->get_file();
                    exit;
            }

            switch ( $ibforums->input['act'] ) {

                    case "Login" :
                                  $this->do_log_out();
                                  break;
             }


            $this->site_view();


            $this->_do_output();
         }

        //***************************************************
        //       IBP CSITE MODIFICATIONS - D-SITE
        //***************************************************

        //---------------------------------------------------
        //  shows main menu box
        //---------------------------------------------------

        function _show_main_menu( $block_caption = "") {
        global $ibforums, $NAV;

                $cat_id = $NAV->get_cat_id();

                $html = "";

                if ( $NAV->cats ) {

                        $html = $NAV->build_cat_list_main_menu( $block_caption );
                }

                return $html;
        }

        //---------------------------------------------------
        //  shows articles list or current artile
        //---------------------------------------------------

        function _show_articles( $block_caption = "" ) {
        global $ibforums, $ART;

                return $ART->result;
        }


         //---------------------------------------------------
        //  shows navigation bar (site_root -> cat0 -> cat1)
        //---------------------------------------------------

        function _show_nav( $block_caption = "" ) {
        global $ibforums, $NAV, $ART;

                $cat_id = $NAV->get_cat_id();

                if ( !$NAV->cats ) {

                        return null;
                }

                return $NAV->build_nav($cat_id);
        }

         //***************************************************
         //              DEFAULT IPB FUNCTIONS
         //***************************************************

         //---------------------------------------------------
         // Do OUTPUT
         //---------------------------------------------------

         function _do_output() {

                 global $ibforums, $DB, $std, $print, $Debug, $sess, $NAV;

                 //------------------------------------------
                 // SITE REPLACEMENTS
                 //------------------------------------------

                 foreach( $this->site_bits as $sbk => $sbv ) {

                         $this->template = str_replace( "<!--CS.TEMPLATE.".strtoupper($sbk)."-->", $sbv, $this->template );
                 }

                $print = new Display();

                $print->to_print = $this->template;

                $print->do_output( array(  'TITLE' => $ibforums->vars['csite_title'],
                                           'JS'    => 0,
                                           'NAV'   => $this->nav,
                                           'RSS'          => $rss,
                         )         );

                exit();
         }

         //---------------------------------------------------
         // Format topic entry
         //---------------------------------------------------

         function _tmpl_format_topic($entry, $cut) {
         global $ibforums, $DB, $std, $print;

                 $entry['title'] = strip_tags($entry['title']);
                 $entry['title'] = str_replace( "&#33;" , "!" , $entry['title'] );
                 $entry['title'] = str_replace( "&quot;", "\"", $entry['title'] );

                 if (strlen($entry['title']) > $cut) {

                        $entry['title'] = substr( $entry['title'],0,($cut - 3) ) . "...";
                        $entry['title'] = preg_replace( '/&(#(\d+;?)?)?(\.\.\.)?$/', '...',$entry['title'] );
                }

                $entry['forum_title'] = $this->good_forum_name[$entry['forum_id']];

                return $this->html->tmpl_topic_row($entry['tid'], $entry['title'], $entry['forum_title'], $entry['forum_id']);
         }

         //---------------------------------------------------
         // Recent articles
         //---------------------------------------------------

         function _show_recentarticles( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print;

                 if ( ! $ibforums->vars['csite_article_recent_on'] )
                 {
                         return;
                 }

                 if ( count( $this->recent ) < 1 )
                 {
                         return;
                 }

                 $html = "";

                 foreach( $this->recent as $pid => $entry )
                 {
                         $html .= $this->_tmpl_format_topic($entry, $ibforums->vars['csite_article_len']);
                 }

                 return $this->html->tmpl_recentarticles($html, $block_caption );
         }

         //---------------------------------------------------
         // Latest Posts
         //---------------------------------------------------

         function _show_latestposts( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print;

                 //--------------------------------------------
                 // Get forums we're allowed to read
                //--------------------------------------------

                 //$ibforums->vars['csite_discuss_on'] = null;
                 if ( ! $ibforums->vars['csite_discuss_on'] )
                 {
                       //  return;
                 }

                $DB->query("SELECT id, name, read_perms FROM ibf_forums");

                while( $f = $DB->fetch_row() ) {

                        if ( $std->check_perms($f['read_perms']) != TRUE ) {

                                $this->bad_forum[] = $f['id'];
                        }
                        else {

                                $this->good_forum[] = $f['id'];
                                $this->good_forum_name[$f['id']] = $f['name'];
                        }
                }


                 $html  = "";
                 $limit = $ibforums->vars['csite_discuss_max'] ? $ibforums->vars['csite_discuss_max'] : 5;

                 if ( count($this->bad_forum) > 0 )
            {
                    $qe = " AND forum_id NOT IN(".implode(',', $this->bad_forum ).") ";
            }

                 $DB->query("SELECT tid, title, forum_id
                             FROM ibf_topics
                             WHERE
                             state != 'closed'
                             AND approved=1 AND ISNULL(moved_to)
                             $qe
                             ORDER BY tid DESC LIMIT 0,$limit");


                 while ( $row = $DB->fetch_row() )
                 {
                         $html .= $this->_tmpl_format_topic($row, $ibforums->vars['csite_discuss_len']);
                 }

                 return $this->html->tmpl_latestposts($html, $block_caption );
         }

         //---------------------------------------------------
         // Poll
         //---------------------------------------------------

         function _show_poll( $block_caption = "" )
         {
                 return null;
                 global $ibforums, $DB, $std, $print;

                 $extra = "";
                 $sql   = "";
                 $check = 0;

                 if ( ! $ibforums->vars['csite_poll_show'] )
                 {
                         return;
                 }

                 if ( ! $ibforums->vars['csite_poll_url'] )
                 {
                         return;
                 }

                 //------------------------------------------
                // Get the topic ID of the entered URL
                //------------------------------------------

                preg_match( "/(\?|&amp;)?(t|showtopic)=(\d+)($|&amp;)/", $ibforums->vars['csite_poll_url'], $match );

                $tid = intval(trim($match[3]));

                if ($tid == "")
                {
                        return;
                }

                if ( $ibforums->member['id'] )
                {
                        $extra = "LEFT JOIN ibf_voters v ON (v.member_id={$ibforums->member['id']} and v.tid=t.tid)";
                        $sql   = ", v.member_id as member_voted";
                }

                //------------------------------------------
                // Get the stuff from the DB
                //------------------------------------------

                $DB->query("SELECT t.tid, t.title, t.state, t.last_vote, p.* $sql
                                         FROM ibf_topics t, ibf_polls p
                                         $extra
                                         WHERE t.tid=$tid AND p.tid=t.tid");

                $poll = $DB->fetch_row();

                if ( ! $poll['pid'] )
                {
                        return;
                }

                $poll['poll_question'] = $poll['poll_question'] ? $poll['poll_question'] : $poll['title'];

                //------------------------------------------
                // Can we vote?
                //------------------------------------------

                if ( $poll['state'] == 'closed' )
        {
                $check = 1;
                $poll_footer = $ibforums->lang['poll_finished'];
        }
                else if (! $ibforums->member['id'] )
        {
                $check = 1;
                $poll_footer = $ibforums->lang['poll_noguest'];
        }
                else if ( $poll['member_voted'] )
        {
                $check = 1;
                $poll_footer = $ibforums->lang['poll_voted'];
        }
        else if ( ($poll['starter_id'] == $ibforums->member['id']) and ($ibforums->vars['allow_creator_vote'] != 1) )
        {
                $check = 1;
                $poll_footer = $ibforums->lang['poll_novote'];
        }
        else
        {
                $check = 0;
                $poll_footer = $this->html->tmpl_poll_vote();
        }

                //------------------------------------------
                // Show it
                //------------------------------------------

        if ($check == 1)
        {
                //----------------------------------
                // Show the results
                //----------------------------------

                $total_votes = 0;

                $html = $this->html->tmpl_poll_header($poll['poll_question'], $poll['tid'], $block_caption );

                $poll_answers = unserialize(stripslashes($poll['choices']));

                reset($poll_answers);
                foreach ($poll_answers as $entry)
                {
                        $id     = $entry[0];
                        $choice = $entry[1];
                        $votes  = $entry[2];

                        $total_votes += $votes;

                        if ( strlen($choice) < 1 )
                        {
                                continue;
                        }

                        if ($ibforums->vars['poll_tags'])
                        {
                                $choice = $this->parser->parse_poll_tags($choice);
                        }
                        if ( $ibforums->vars['post_wordwrap'] > 0 )
                                {
                                        $choice = $this->parser->my_wordwrap( $choice, $ibforums->vars['post_wordwrap']) ;
                                }

                        $percent = $votes == 0 ? 0 : $votes / $poll['votes'] * 100;
                        $percent = sprintf( '%.2f' , $percent );
                        $width   = $percent > 0 ? floor( round( $percent ) * ( 150 / 100 ) ) : 0;

                        $html   .= $this->html->tmpl_poll_result_row($votes, $id, $choice, $percent, $width);
                }
        }
        else
        {
                $poll_answers = unserialize(stripslashes($poll['choices']));
                reset($poll_answers);

                //----------------------------------
                // Show poll form
                //----------------------------------

                $html = $this->html->tmpl_poll_header($poll['poll_question'], $poll['tid'], $block_caption);

                foreach ($poll_answers as $entry)
                {
                        $id     = $entry[0];
                        $choice = $entry[1];
                        $votes  = $entry[2];

                        $total_votes += $votes;

                        if ( strlen($choice) < 1 )
                        {
                                continue;
                        }

                        if ($ibforums->vars['poll_tags'])
                        {
                                $choice = $this->parser->parse_poll_tags($choice);
                        }
                        if ( $ibforums->vars['post_wordwrap'] > 0 )
                                {
                                        $choice = $this->parser->my_wordwrap( $choice, $ibforums->vars['post_wordwrap']) ;
                                }

                        $html   .= $this->html->tmpl_poll_choice_row($id, $choice);
                }

        }

        $html .= $this->html->tmpl_poll_footer($poll_footer, sprintf( $ibforums->lang['poll_total_votes'], $total_votes ), $poll['tid'] );

                 return $html;
         }


         function _show_sitenav( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print, $cat;

                 if ( $ibforums->vars['csite_nav_show'] != 1 ) {

                         return null;
                 }

                 $links = "";

                 $raw_nav = explode( "\n", $this->raw['csite_nav_contents']);

                 foreach( $raw_nav as $l )
                 {
                         preg_match( "#^(.+?)\[(.+?)\]$#is", trim($l), $matches );

                         $matches[1] = trim($matches[1]);
                         $matches[2] = trim($matches[2]);

                         if ( $matches[1] and $matches[2] )
                         {
                                 $links .= $this->html->tmpl_links_wrap( str_replace( '{site_url}', $ibforums->vars['dynamiclite'], $matches[1] ), $matches[2] );
                         }
                 }

                 return $this->html->tmpl_rawnav($links, $block_caption );
         }

         //---------------------------------------------------
         // Affiliates
         //---------------------------------------------------

         function _show_affiliates( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print;

                 if ( ! $ibforums->vars['csite_fav_show'] )
                 {
                         return;
                 }

                 return $this->html->tmpl_affiliates($this->raw['csite_fav_contents'], $block_caption );
         }

         //---------------------------------------------------
         // Change skin
         //---------------------------------------------------

         function _show_changeskin( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print;

                 if ( ! $ibforums->vars['csite_skinchange_show'] )
                 {
                         return;
                 }

                 $select = $this->html->tmpl_skin_select_top();

                 //---------------------------------------
                 // Query DB for skins
                 //---------------------------------------

                 $DB->query("SELECT sname, sid, default_set FROM ibf_skins where hidden=0");

                 while( $s = $DB->fetch_row() )
                 {
                         $used = "";

                         if ( $ibforums->member['skin'] == "" )
                         {
                                 if ( $s['default_set'] == 1 )
                                 {
                                         $used = 'selected="selected"';
                                 }
                         }
                         else
                         {
                                 if ( $ibforums->member['skin'] == $s['sid'] )
                                 {
                                         $used = 'selected="selected"';
                                 }
                         }

                         $select .= $this->html->tmpl_skin_select_row($s['sid'], $s['sname'], $used);
                 }

                 $select .= $this->html->tmpl_skin_select_bottom();

                 return $this->html->tmpl_changeskin($select, $block_caption);
         }

        //---------------------------------------------------
         // Search box
         //---------------------------------------------------

         function _show_search( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print;

                 if ( ! $ibforums->vars['csite_search_show'] )
                 {
                         return;
                 }

                 return $this->html->tmpl_search( $block_caption );
         }


         //---------------------------------------------------
         // Welcome Box
         //---------------------------------------------------

         function _show_welcomebox( $block_caption = "" )
         {
                 global $ibforums, $DB, $std, $print;


                 if ( ! $ibforums->vars['csite_pm_show'] )
                 {
                         //return;
                 }

                 $html = "";

                 $return = $_SERVER["HTTP_REFERER"];

                 if ( $return == "" )
                 {
                         $return = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
                 }

                 $return = urlencode($return);

                 if ( $ibforums->member['id'] )
                 {
                         //------------------------------
                         // Work member info
                         //------------------------------

                    $pm_string  = sprintf( $ibforums->lang['wbox_pm_string'] , "<a href='{$ibforums->base_url}act=Msg'>".intval($ibforums->member['new_msg'])."</a>" );
                    $last_visit = sprintf( $ibforums->lang['wbox_last_visit'], $std->get_date( $ibforums->member['last_visit'], 'LONG' ) );

                    $html = $this->html->tmpl_welcomebox_member($pm_string, $last_visit, $ibforums->member['name'], $ibforums->base_url.'act=home', $block_caption);

                 }
                 else
                 {
                         $top_string = sprintf( $ibforums->lang['wbox_guest_reg'], "<a href='{$ibforums->base_url}act=Reg'>{$ibforums->lang['wbox_register']}</a>" );

                         $html = $this->html->tmpl_welcomebox_guest($top_string, $return, $block_caption );
                 }

                 return $html;
        }

        function _show_member_bar( $block_caption = "" ) {
        global $ibforums;

                if ($ibforums->member['id'] == 0) {

                        return $this->html->Guest_bar();
                }

                $mod_link = null;
                $admin_link = null;

                if ( ($ibforums->member['is_mod']) or ($ibforums->member['g_is_supmod'] == 1) ) {

                        $mod_link = $this->html->mod_link();
                }

                $admin_link = $ibforums->member['g_access_cp'] ? $this->html->admin_link() : '';
                $valid_link = $ibforums->member['mgroup'] == $ibforums->vars['auth_group'] ? $this->html->validating_link() : '';

                if ( ($ibforums->member['g_max_messages'] > 0) and ($ibforums->member['msg_total'] >= $ibforums->member['g_max_messages']) ) {

                        $msg_data['TEXT'] = $ibforums->lang['msg_full'];
                } else {

                        $ibforums->member['new_msg'] = $ibforums->member['new_msg'] == "" ? 0 : $ibforums->member['new_msg'];

                        $msg_data['TEXT'] = sprintf( $ibforums->lang['msg_new'], $ibforums->member['new_msg']);

                        // from CBP & vot:
                        if ($ibforums->member['new_msg']) {

                                $msg_data['TEXT'] .= " <img border=0 src='{$ibforums->vars['board_url']}/style_images/{$ibforums->skin['img_dir']}/bat.gif'>";
                        }
                }

                 $return = $_SERVER["HTTP_REFERER"];

                 if ( $return == "" )
                 {
                         $return = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
                 }

                 $return = urlencode($return);


                return $this->html->Member_bar($msg_data, $admin_link, $mod_link, $valid_link, $return);
        }



        function _show_author_links( $block_caption = "" ) {
        global $ibforums, $DB, $USR, $NAV;

                if ($ibforums->member['id'] < 1) {

                        return "";
                }

                $cat_id = $NAV->get_cat_id();

                if ( $cat_id != 0 ) {

                        return $this->html->tmpl_author_links($this->html->tmpl_author_add_article_lnk(), $block_caption );
                }

                return "";
        }


        function _show_favorites( $block_caption = "" ) {
        global $ibforums, $DB, $USR;

                if ($ibforums->member['id'] < 1) {

                        return "";
                }

                return $this->html->tmpl_favorites_links($block_caption );
        }

        //----------------------------------------------------------------------
        // Move site blocks/I like to mov't (c) nigga
        //----------------------------------------------------------------------


        function site_view() {
        global $ibforums, $DB, $USR, $NAV, $ART;

                 //-------------------------------------------------------------
                 // get placement information from DB
                 //-------------------------------------------------------------


                 $DB->query("SELECT * FROM ibf_cms_views WHERE visible = 1 ORDER BY b_order");

                 while ( $dbres = $DB->fetch_row() ) {

                         //-----------------------------------------------------
                         // we have some special virtual blocks - width, height
                         //-----------------------------------------------------

                         if ( $dbres['id'] == '999' ) {

                                 //---------------------------------------------
                                 // write block size information
                                 //---------------------------------------------

                                 $block_width[0] = $dbres['bname'];
                                 $block_width[1] = $dbres['bdescription'];
                                 $block_width[2] = $dbres['bcaption'];

                         } else {

                                 $site_blocks[] = $dbres;
                         }
                 }

                 $cat_id = $NAV->get_cat_id();
                 $art_id = $NAV->get_art_id();

                 //-------------------------------------------------------------
                 // make class functions from string vars of array of blocks
                 //-------------------------------------------------------------

                 foreach ( $site_blocks as $dbres ) {

                         $myFunc = "_show_" . $dbres['bname'];

                         //-----------------------------------------------------
                         // do not show moderators on the main page
                         //-----------------------------------------------------

                         if ( $dbres['bname'] == 'moderator' && $cat_id != 0 ) {

                                 $this->site_bits[$dbres['bname']] = $this->$myFunc();

                         } else if ( $dbres['bname'] != 'moderator' ) {

                                 //---------------------------------------------
                                 // show latestposts only on the main page
                                 //---------------------------------------------

                                 if ( $dbres['bname'] == 'latestposts' && $cat_id == 0 ) {


                                         $this->site_bits[$dbres['bname']] = $this->$myFunc($dbres['bcaption']);

                                 } else if ( $dbres['bname'] != 'latestposts' ) {

                                         //-------------------------------------
                                         // show all other blocks
                                         //-------------------------------------

                                         $this->site_bits[$dbres['bname']] = $this->$myFunc($dbres['bcaption']);
                                 }
                         }

                         if ( $dbres['pid'] == 0 && $dbres['id'] < '999' ) {

                                 if ( $dbres['break'] == '1' ) {

                                         $this->site_bits['block_left'] .= "<br />";
                                 }

                                 $this->site_bits['block_left'] .= $this->site_bits[$dbres['bname']];

                                 if ( $dbres['break'] == '2' ) {

                                         $this->site_bits['block_left'] .= "<br />";
                                 }
                         }

                         if ( $dbres['pid'] == 1 && $dbres['id'] < '999' ) {

                                 if ( $dbres['break'] == '1' ) {

                                         $this->site_bits['block_center'] .= "<br />";
                                 }

                                 if ( $art_id == 0 && !$ART->result ) {

                                         $this->site_bits['block_center'] .= $this->site_bits[$dbres['bname']];
                                 } else {

                                         $this->site_bits['block_center'] = $this->site_bits['articles'];
                                 }

                                 if ( $dbres['break'] == '2' ) {

                                         $this->site_bits['block_center'] .= "<br />";
                                 }

                         }

                         if ( $dbres['pid'] == 2 && $dbres['id'] < '999' ) {

                                 if ( $dbres['break'] == '1' ) {

                                         $this->site_bits['block_right'] .= "<br />";
                                 }

                                 $this->site_bits['block_right'] .= $this->site_bits[$dbres['bname']];

                                 if ( $dbres['break'] == '2' ) {

                                         $this->site_bits['block_right'] .= "<br />";
                                 }
                         }

                 }

                 if ( $NAV->cats[$cat_id]['show_fullscreen'] != '0' || $art_id == 0 && $ibforums->input['act'] != 'upload' ) {

                         $this->site_bits['block_left']   = $this->html->block_left(
                                                                                    array
                                                                                         (
                                                                                           'data'  => $this->site_bits['block_left'],
                                                                                           'width' => $block_width[0],
                                                                                         )
                                                                                    );

                         $this->site_bits['block_center'] = $this->html->block_center
                                                                                     (
                                                                                       array
                                                                                             (
                                                                                               'data' => $this->site_bits['block_center'],
                                                                                               'width' => $block_width[1],
                                                                                             )
                                                                                     );

                         $this->site_bits['block_right']  = $this->html->block_right(
                                                                                      array
                                                                                            (
                                                                                              'data' => $this->site_bits['block_right'],
                                                                                              'width' => $block_width[2],
                                                                                            )
                                                                                     );
                 } else if ( $art_id != 0  || $ibforums->input['act'] == 'upload') {

                         $this->site_bits['block_left'] = '';
                         $this->site_bits['block_right'] = '';

                 }
        }

        function _show_moderator() {
        global $USR;

                return $USR->moderators;
        }

        function do_log_out()
        {
                global $std, $ibforums, $DB, $print, $sess, $HTTP_COOKIE_VARS;

                // Update the DB

                $DB->query("UPDATE ibf_sessions SET ".
                                     "member_name='',".
                                     "member_id='0',".
                                     "login_type='0' ".
                                     "WHERE id='". $sess->session_id ."'");

                // Set some cookies
                $std->my_setcookie( "member_id" , "0"  );
                $std->my_setcookie( "pass_hash" , "0"  );
                $std->my_setcookie( "anonlogin" , "-1" );

                // Redirect...
                $url = "";

                return header("Location: {$ibforums->vars['dynamiclite']}");
        }
}

?>
