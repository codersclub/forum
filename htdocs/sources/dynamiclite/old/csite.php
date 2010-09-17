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
|  written by Anton (Chainick), Copyright (c) 2004-2005
|  version: 0.2.3
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
            global $ibforums, $DB, $std, $print, $NAV, $ART, $USR, $CAT;

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

                $cat_id = intval($ibforums->input['cat']);

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

                $cat_id = intval($ibforums->input['cat']);

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

                 if ( $ibforums->skin['css_method'] == 'external' ) {

                         $css = $this->html->csite_css_external($ibforums->skin['css_id'], $ibforums->skin['img_dir']);


                 } else {

                         //$css = $this->html->csite_css_inline( preg_replace( "#(?!".preg_quote($ibforums->vars['board_url'], '/')."/)style_images/<\#IMG_DIR\#>#is", $ibforums->vars['board_url']."/style_images/".$ibforums->skin['img_dir'], $ibforums->skin['css_text'] ) );

                         $css = $this->html->csite_css_inline( str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $ibforums->skin['css_text'] ) );
                 }

                 $css = $this->html->csite_css_inline( str_replace( "<#IMG_DIR#>", $ibforums->skin['img_dir'], $ibforums->skin['css_text'] ) );

                 //------------------------------------------
                 // TEMPLATE REPLACEMENTS
                 //------------------------------------------

                 $ibforums->vars['csite_title'] = $this->article.$this->title.$ibforums->vars['csite_title'];
                 $this->site_bits['title']      = $ibforums->vars['csite_title'];
                 $this->site_bits['css']        = $css;
                 $this->site_bits['javascript'] = $this->html->csite_javascript();

                 //------------------------------------------
                 // SITE REPLACEMENTS
                 //------------------------------------------

                 foreach( $this->site_bits as $sbk => $sbv ) {

                         $this->template = str_replace( "<!--CS.TEMPLATE.".strtoupper($sbk)."-->", $sbv, $this->template );
                 }


                //------------------------------------------
                // MACROS
                //------------------------------------------

                $DB->query("SELECT macro_value, macro_replace FROM ibf_macro WHERE macro_set={$ibforums->skin['macro_id']}");

                while ( $row = $DB->fetch_row() ) {

                        if ($row['macro_value'] != "") {

                                $this->template = str_replace( "<{".$row['macro_value']."}>", $row['macro_replace'], $this->template );
                        }
                }

                //$this->template = preg_replace( "#(?!".preg_quote($ibforums->vars['board_url'], '/')."/)style_images/<\#IMG_DIR\#>#is", $ibforums->vars['board_url']."/style_images/".$ibforums->skin['img_dir'], $this->template );

                //----------------------------------------
                // vot removed 'style_images', me too :)
                // Date: 06/07/2005 (c) Anton
                //----------------------------------------
                $this->template = preg_replace( "#(?!".preg_quote($ibforums->vars['board_url'], '/')."/)<\#IMG_DIR\#>#is", $ibforums->vars['board_url']."/style_images/".$ibforums->skin['img_dir'], $this->template );

                //------------------------------------------
                // DEBUG
                //------------------------------------------

                //show debug-info in any vay, it'd be useful 4 frist time (Chainick)
                $this->template = str_replace( "<!--CS.TEMPLATE.DEBUG-->", $this->html->tmpl_debug( $DB->get_query_cnt(), sprintf( "%.4f",$Debug->endTimer() ) ), $this->template );

                if ( $ibforums->vars['debug_level'] ) {
//                        $this->template = str_replace( "<!--CS.TEMPLATE.DEBUG-->", $this->html->tmpl_debug( $DB->get_query_cnt(), sprintf( "%.4f",$Debug->endTimer() ) ), $this->template );
                }


                //------------------------------------------
                // CPYRT
                //------------------------------------------

                $extra = "";
                $ur    = '(U)';

                if ( $ibforums->vars['ipb_reg_number'] ) {

                        $ur = '(R)';

                        if ( $ibforums->vars['ipb_reg_show'] and $ibforums->vars['ipb_reg_name'] ) {

                                $extra = "- Registered to: ". $ibforums->vars['ipb_reg_name'];
                        }
                }

                $copyright = "\n\n<div align='center' class='copyright'>Powered by <a href=\"http://www.invisionboard.com\" target='_blank'>IPDynamic Lite </a>{$ur}  {$ibforums->version} &copy; 2003 &nbsp;<a href='http://www.invisionpower.com' target='_blank'>IPS, Inc.</a>$extra</div>";

                if ($ibforums->vars['ips_cp_purchase']) {

                        $copyright = "";
                }

                $this->template = str_replace( "<!--CS.TEMPLATE.COPYRIGHT-->", $copyright, $this->template );


                //---------------------------------------
                // BOARD RULES
                //---------------------------------------

                if ($ibforums->vars['gl_show'] and $ibforums->vars['gl_title']) {

                        if ($ibforums->vars['gl_link'] == "") {

                                $ibforums->vars['gl_link'] = $ibforums->base_url."act=boardrules";
                        }

                        $this->template = str_replace( "<!--IBF.RULES-->", $this->html->rules_link($ibforums->vars['gl_link'], $ibforums->vars['gl_title']), $this->template );
                }

                //---------------------------------------
                // Close this DB connection
                //---------------------------------------

                //$this->template .= $ibforums->debug_html;

                $DB->close_db();

                //---------------------------------------
                // Start GZIP compression
                //---------------------------------------

                if ($ibforums->vars['disable_gzip'] != 1) {

                        $buffer = ob_get_contents();
                        ob_end_clean();
                        ob_start('ob_gzhandler');
                        print $buffer;
                }

                //        $this->template = $print->striptags($this->template);

                $print->do_headers();

                //------------------------------------------
                // PRINT!
                //------------------------------------------

                print $this->template;

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
                         return;
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
        global $ibforums, $DB, $USR;

                if ($ibforums->member['id'] < 1) {

                        return "";
                }

                $cat_id = intval($ibforums->input['cat']);

                if ( $cat_id != 0 ) {

                        return $this->html->tmpl_author_links($this->html->tmpl_author_add_article_lnk(), $block_caption );
                }

                return "";
        }

        function site_view() {
        global $ibforums, $DB, $USR, $NAV;


                 $DB->query("SELECT * FROM ibf_cms_views WHERE visible = 1 ORDER BY b_order");

                 while ( $dbres = $DB->fetch_row() ) {

                         if ( $dbres['id'] == '999' ) {

                                 $block_width[0] = $dbres['bname'];
                                 $block_width[1] = $dbres['bdescription'];
                                 $block_width[2] = $dbres['bcaption'];

                         } else {

                                 $site_blocks[] = $dbres;
                         }
                 }

                 $cat_id = intval($ibforums->input['cat']);
                 $art_id = intval($ibforums->input['id']);

                 foreach ( $site_blocks as $dbres ) {

                         $myFunc = "_show_" . $dbres['bname'];

                         if ( $dbres['bname'] == 'moderator' && $cat_id != 0 ) {

                                 $this->site_bits[$dbres['bname']] = $this->$myFunc();

                         } else if ( $dbres['bname'] != 'moderator' ) {

                                 $this->site_bits[$dbres['bname']] = $this->$myFunc($dbres['bcaption']);
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

                                 $this->site_bits['block_center'] .= $this->site_bits[$dbres['bname']];

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

                 if ( $NAV->cats[$cat_id]['show_fullscreen'] != '0' || $art_id == 0 ) {

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
                 } else if ( $art_id != 0 ) {

                         $this->site_bits['block_left'] = '';
                         $this->site_bits['block_right'] = '';

                 }
        }

        function _show_moderator() {
        global $USR;

                return $USR->moderators;
        }
}

?>
