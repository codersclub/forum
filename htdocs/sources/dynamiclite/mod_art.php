<?php

/*
+---------------------------------------------------------------------------
|   D-Site Article working module
|   ========================================
|   Copyright (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   Site articles functions
|
*---------------------------------------------------------------------------
*/

class mod_art extends Post {

        var $csite_html = "";
        var $parser     = "";
        var $result     = "";
        var $current_article_id = "";

        //----------------------------------------------------------------------
        //  module initialization
        //----------------------------------------------------------------------

        function mod_art() {
        global $ibforums, $std, $NAV, $FAV, $MISC;

                //------------------------------------------
                // load html skin templates
                //------------------------------------------

                $this->csite_html = $std->load_template('skin_csite_mod_art');

                //------------------------------------------
                // load the parser
                //------------------------------------------

                require ROOT_PATH."sources/lib/post_parser.php";
                $this->parser = new post_parser();

                //--------------------------------------------------------------
                // if we use mod_rewrite, convert text to act_id
                //--------------------------------------------------------------

                if ( $ibforums->vars['dsite_use_mod_rewrite'] == 1 ) {

                        $ibforums->input['act'] = $MISC->get_action_id();
                }


                //------------------------------------------
                // are we uploading or viewing article
                //------------------------------------------

                if ( isset($ibforums->input['act']) && $ibforums->input['act'] == 'upload' && $ibforums->input['act'] != 'comments' && !$ibforums->input['FAV'] ) {

                        //--------------------------------------------
                        //  viewing upload forms
                        //  ACTION in:
                        //  *  - show_upload()
                        //  1  - do_upload()
                        //  2  - show_edit()
                        //  3  - do_edit()
                        //  4  - show_delete()
                        //  5  - do_delete()
                        //  6  - do_approve(APPROVE)
                        //  7  - do_approve(DISABLE)
                        //  8  - show_move()
                        //  9  - do_move()
                        //  10 - remove attached file
                        //  11 - misc. show articles rates
                        //  12 - misc. search new articles
                        //  15 - favorites management
                        //--------------------------------------------

                        switch ( $ibforums->input['ACTION'] ) {

                                case '1' :
                                            $this->result = $this->do_upload();
                                            break;

                                case '2' :
                                            $this->result = $this->show_edit();
                                            break;

                                case '3' :
                                            $this->result = $this->do_edit();
                                            break;

                                case '4' :
                                            $this->result = $this->show_delete();
                                            break;

                                case '5' :
                                            $this->result = $this->do_delete();
                                            break;

                                case '6' :
                                            $this->result = $this->do_approve(true);
                                            break;

                                case '7' :
                                            $this->result = $this->do_approve(false);
                                            break;

                                case '8' :
                                            $this->result = $this->show_move();
                                            break;

                                case '9' :
                                            $this->result = $this->do_move();
                                            break;

                                case '10' :
                                            $this->result = $this->do_delete_attach();
                                            break;

                                case '11' :
                                            $this->result = $this->show_most_popular();
                                            break;
                                default  :
                                            $this->result = $this->show_upload();
                                            break;
                        }

                } else if ( $ibforums->input['act'] == 'comments' ) {

                        //------------------------------------
                        // are we working with comments?
                        //------------------------------------

                        require "mod_art_comments.php";
                        $COMMENTS = new mod_art_comments(&$this);

                        $this->result = $COMMENTS->result;

                } else if ( $ibforums->input['FAV'] ) {

                        //------------------------------------------------------
                        // are we working with favorites/subscriptions?
                        //------------------------------------------------------

                        new mod_fav( &$this );


                } else if ( isset($ibforums->input['REP']) ) {

                        //------------------------------------------------------
                        // are we working with reputiaon?
                        //------------------------------------------------------

                        new mod_rep( &$this );

                } else {

                        if ( $NAV->get_cat_id() != 0 && $NAV->get_art_id() != 0 ) {

                                //--------------------------------------------
                                // display choosen article
                                //--------------------------------------------

                                $this->result = $this->show_article();

                        } else {

                                //--------------------------------------------
                                // display articles list in selected cat
                                //--------------------------------------------

                                $this->result = $this->show_articles();
                        }
                }

                //--------------------------------------------
                //  finished!
                //--------------------------------------------

                return true;
        }

        /*
          +--------------------------------------------------------------------+
          |                                                                    |
          |       DISPLAY ARTICLES LIST/SEPARATED ARTICLE FUNCTIONS            |
          |                                                                    |
          +--------------------------------------------------------------------+
        */

        //----------------------------------------------------------------------
        //  show list of articles in category/sub-category
        //----------------------------------------------------------------------

         function show_articles() {
         global $ibforums, $DB, $std, $NAV, $USR;

                 $html     = "";
                 $approved = array();
                 $entry    = array();

                 $cat_id = $NAV->get_cat_id();

                 $limit = $ibforums->vars['csite_discuss_max'] ? $ibforums->vars['csite_discuss_max'] : 5;
                 $current_page = intval($ibforums->input['pages']);

                 //--------------------------------------------
                 // are you guest? allowed to show articles?
                 //--------------------------------------------

                 if ( $cat_id != 0 && $NAV->cats[$cat_id]['visible'] == 0 && $USR->is_mod() === false ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                 }

                 //-------------------------------------------------------------
                 // is user's group allowed to show articles?
                 //-------------------------------------------------------------

                 if ( $ibforums->member['g_art_view'] == 0 ) {

                         $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                         exit();
                 }

                 //--------------------------------------------
                 // if the category can have only one article
                 // ibf_cms_uploads_cats[one_article] IN:
                 // 0 - only one article
                 // 1 - many articles
                 //--------------------------------------------

                 if ( $NAV->cats[$cat_id]['one_article'] == 0 ) {

                         $DB->query(" SELECT base,current_version FROM ibf_cms_uploads_cat_links WHERE refs = {$cat_id} LIMIT 0,1 ");

                         $dbres = $DB->fetch_row();

                         //--------------------------------------------
                         //  if we have at list one artile, show it
                         //--------------------------------------------

                         if ( $dbres ) {

                                 $ibforums->input['id'] = $dbres['base'];
                                 $ibforums->input['version'] = $dbres['current_version'];

                                 return $this->show_article( $dbres['base'] );
                         }
                 }

                 //--------------------------------------------
                 // filter by ibf_cms_uploads [approved]
                 //--------------------------------------------

                 if ( $USR->is_mod() === false ) {

                         $approved[0] = "WHERE ( u.user_id = {$ibforums->member['id']} OR u.approved != 0 )";
                         $approved[1] = "AND  ( u.approved != 0 OR u.user_id = {$ibforums->member['id']} )";

                         $order = "submit_date DESC";
                 }

                 //-------------------------------------------------------------
                 // default order
                 //-------------------------------------------------------------

                 $order = "approved DESC";

                 if ( $cat_id == 0 ) {

                         //--------------------------------------------
                         //  we are on the main page, show first n
                         //  articles
                         //--------------------------------------------

                         $sql = " SELECT l.base, l.refs, l.current_version AS version, u.*
				  FROM ibf_cms_uploads_cat_links AS l

                                      LEFT JOIN ibf_cms_uploads AS u

                                      ON l.base = u.id

                                      AND l.current_version = u.version_id

                                      {$approved[0]}

                                      ORDER BY u.{$order}

                                      LIMIT {$current_page},{$limit}

                                    ";

                         $DB->query( $sql );

                         while ($dbres = $DB->fetch_row()) {

                                 if ( $NAV->cats[$dbres['refs']]['visible'] == 0 && $USR->is_mod() === true ) {

                                         $articles[] = $dbres;

                                 } else if ( $NAV->cats[$dbres['refs']]['visible'] == 1 ) {

                                         $articles[] = $dbres;
                                 }
                         }

                 } else {

                         //-----------------------------------------------------
                         // get perfect sql query
                         //-----------------------------------------------------

                         $sql = (string) $this->make_articles_list_sql_query();

                         $sql = str_replace("%APPROVED[0]%", $approved[0], $sql);
                         $sql = str_replace("%APPROVED[1]%", $approved[1], $sql);
                         $sql = str_replace("%ORDER%", $order, $sql);

                         $DB->query( $sql );

                         while ($dbres = $DB->fetch_row()) {

                                 //--------------------------------------------
                                 // do not show articles from hidden cats
                                 //--------------------------------------------

                                 if ( $NAV->cats[$dbres['refs']]['visible'] == 0 && $USR->is_mod() === true ) {

                                         $articles[] = $dbres;

                                 } else if ($NAV->cats[$dbres['refs']]['visible'] == 1) {

                                         $articles[] = $dbres;
                                 }
                         }

                 }

                 //--------------------------------------------
                 // show children of current category
                 //--------------------------------------------

                 if ( $NAV->cats[$cat_id]['show_subcats'] != 0 ) {

                         $entry['sub_cats'] = "";

                         //--------------------------------------
                         // do ew have children?
                         //--------------------------------------

                         $sub_cats = $NAV->get_children($cat_id);

                         if ( $sub_cats ) {

                                 foreach ( $sub_cats as $sub_cat ) {

                                         //--------------------------------------
                                         // do not show hidden for non-moders
                                         //--------------------------------------

                                         if ( $sub_cat['visible'] == 1 ) {

                                                 //--------------------------------------
                                                 // redirect links
                                                 //--------------------------------------

                                                 if ( $sub_cat['redirect_url'] != '') {

                                                         $entry['sub_cats'] .= $this->csite_html->tmpl_sub_cat_row_redir($sub_cat);

                                                 } else {

                                                         $entry['sub_cats'] .= ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                                                                 $this->csite_html->tmpl_sub_cat_row($this->csite_html->tmpl_cat_link($sub_cat)) :
                                                                                 $this->csite_html->tmpl_sub_cat_row_rw($this->csite_html->tmpl_cat_link_rw($NAV->build_path($sub_cat['id'], true), $sub_cat['name']));
                                                 }
                                         } else if ( $USR->is_mod($sub_cat['id']) == true ) {

                                                 //--------------------------------------
                                                 // redirect links
                                                 //--------------------------------------

                                                 if ( $sub_cat['redirect_url'] != '') {

                                                         $sub_cat['name'] = $sub_cat['name'] . " (!)";

                                                         $entry['sub_cats'] .= $this->csite_html->tmpl_sub_cat_row_redir($sub_cat);

                                                 } else {

                                                         $sub_cat['name'] = $sub_cat['name'] . " (!)";

                                                         $entry['sub_cats'] .= ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                                                                 $this->csite_html->tmpl_sub_cat_row($this->csite_html->tmpl_cat_link($sub_cat)) :
                                                                                 $this->csite_html->tmpl_sub_cat_row_rw($this->csite_html->tmpl_cat_link_rw($NAV->build_path($sub_cat['id'], true), $sub_cat['name']));
                                                 }
                                         }
                                 }
                         }
                 }

                 //--------------------------------------------
                 // nothing to display
                 //--------------------------------------------

                 if ( !$articles ) {

                      $entry['title'] = sprintf($ibforums->lang['articles_title'], $NAV->cats[$cat_id]['name']);

                      return $this->csite_html->tmpl_articles( $entry );
                 }


                 //--------------------------------------------
                 // unparsing to html
                 //--------------------------------------------

                 foreach ( $articles as $article ) {


                         $article['bottom_string'] = sprintf($ibforums->lang['main_frame_bottom_str'],
                                                           intval($article['hits']),
                                                           '',
                                                           '',
                                                           '');

                         $article['top_string']    = sprintf($ibforums->lang['main_frame_top_str'],
                                                              $this->csite_html->tmpl_cat_link_rw($NAV->build_path($article['refs'], true), $NAV->cats[$article['refs']]['name']),
                                                              $std->make_profile_link($article['author_name'], $article['user_id']),
                                                              $std->get_date($article['submit_date']));

                         $article_link = ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                           $this->csite_html->tmpl_article_link($article) :
                                           $this->csite_html->tmpl_article_link_rw(
                                                                                   $NAV->build_path($article['refs'], true) . $article['article_id'] . "/",
                                                                                   $article['name']
                                                                                   );

                         $article['article_link'] = $article['static_url'] ? $this->csite_html->tmpl_static_article_link($article) : $article_link;

                         if ($article['icon_id'] != 0) {

                                 $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                              WHERE id = {$article['icon_id']}

                                            ");

                                 $dbres = $DB->fetch_row();

                                 $icon_path = $NAV->build_path($article['refs'], true) . $article['article_id'] . "/" . $dbres['name'];

                                 $article['article_icon_url'] = $this->csite_html->tmpl_article_link($params = array(
                                                                                                     'name' => $this->csite_html->tmpl_article_icon_img($icon_path),
                                                                                                     'id' => $article['id'],
                                                                                                     'refs' => $article['refs']));
                         }

                         $article = $this->make_admin_links($article);

                         if ($USR->is_owner($article['user_id']) == true || $entry['approved'] == 0) {

                                 $html .= $this->csite_html->tmpl_articles_row($article);
                         }
                 }

                 //$pages_bar = $DSITE->build_pages_bar($limit, $cat, $show_approved);

                 //------------------------------------------
                 // show title for the page
                 //------------------------------------------

                 if ( $cat_id == 0 ) {

                         //------------------------------------------
                         // we are at the main page
                         //------------------------------------------

                         $entry['title'] = $ibforums->lang['articles_title_main_pg'];
                 } else {

                         //------------------------------------------
                         // we are in the category
                         //------------------------------------------

                         $entry['title'] = sprintf($ibforums->lang['articles_title'], $NAV->cats[$cat_id]['name']);
                 }

                 $entry['articles'] = (!$html) ? $ibforums->lang['articles_not_found'] : $html.$pages_bar;
                 $entry['description'] = $NAV->cats[$cat]['description'];

                 //------------------------------------------
                 // finished!
                 //------------------------------------------

                 return $this->csite_html->tmpl_articles($entry);
         }

         //---------------------------------------------------------------------
         // articles list different views
         //---------------------------------------------------------------------

         function make_articles_list_sql_query() {
         global $ibforums, $DB, $NAV;

                 $cat_id = $NAV->get_cat_id();

                 //-------------------------------------------------------------
                 // different views
                 // ACTION IN:
                 // --
                 // 12 - get new articles
                 // 13 - get my articles
                 // 14 - view not approved articles
                 // 15 -
                 //-------------------------------------------------------------

                 switch ( intval( $ibforums->input['ACTION'] ) ) {

                         case 12 :
                                  return $this->make_new_articles_sql();

                         case 13 :
                                  return $this->make_my_articles_sql();

                         case 14 :
                                  return $this->make_approve_articles_sql();

                         default :
                                  break;
                 }

                 //-----------------------------------------------------
                 // get all articles from selected category and
                 // all child categories
                 //-----------------------------------------------------

                 $cat_sub_ids = ($NAV->get_all_children_ids( $cat_id )) ? $NAV->get_all_children_ids( $cat_id ) : 0;

                 //-------------------------------------------------------------
                 // return sql by default
                 //-------------------------------------------------------------

                 $sql = (string) " SELECT l.base, l.refs, u.*, l.current_version AS version
				     FROM ibf_cms_uploads_cat_links AS l

                                   LEFT JOIN ibf_cms_uploads AS u

                                   ON l.base = u.id

                                   WHERE l.refs IN ({$cat_sub_ids})

                                   AND u.version_id = l.current_version

                                   ORDER BY u.version_id DESC, u.%ORDER%

                                 ";

                 return $sql;
         }

         //---------------------------------------------------------------------
         // sql for searching new articles
         //---------------------------------------------------------------------

         function make_new_articles_sql() {
         global $ibforums, $DB;

                 $DB->query(" SELECT aid FROM ibf_cms_articles_watchdog WHERE mid = {$ibforums->member['id']} ");

                 while ( $dbres = $DB->fetch_row() ) {

                         $skip_ids[] = $dbres['aid'];
                 }

                 $skip_ids = implode(",", $skip_ids);

                 $sql = " SELECT l.*, u.*
			  FROM ibf_cms_uploads_cat_links AS l

                          LEFT JOIN ibf_cms_uploads AS u

                          ON l.base = u.id

                          WHERE u.id NOT IN ({$skip_ids})

                          %APPROVED[0]%

                          %APPROVED[1]%

                          AND u.version_id = l.current_version

                          ORDER BY u.%ORDER%

                        ";

                 return $sql;
         }

         //---------------------------------------------------------------------
         // sql for searching new articles
         //---------------------------------------------------------------------

         function make_my_articles_sql() {
         global $ibforums;

                 $sql = " SELECT l.*, u.*
			    FROM ibf_cms_uploads_cat_links AS l

                          LEFT JOIN ibf_cms_uploads AS u

                          ON l.base = u.id

                          WHERE u.user_id = {$ibforums->member['id']}

                          %APPROVED[0]%

                          %APPROVED[1]%

                          AND u.version_id = l.current_version

                          ORDER BY u.%ORDER%

                        ";

                 return $sql;
         }

         //---------------------------------------------------------------------
         // sql for searching articles to approve
         //---------------------------------------------------------------------

         function make_approve_articles_sql() {
         global $ibforums, $DB, $USR;

                 $DB->query( " SELECT forum_id FROM ibf_cms_moderators WHERE mid = {$ibforums->member['id']} " );

                 if ( $DB->get_num_rows() >= 1 ) {

                         while ( $dbres = $DB->fetch_row() ) {

                                 if ( $USR->is_mod($dbres['forum_id']) ) {

                                         $cat_ids[] = $dbres['forum_id'];
                                 }
                         }
                 }

                 $cat_ids = implode(",", $cat_ids);

                 $sql = " SELECT l.*, u.*
			     FROM ibf_cms_uploads_cat_links AS l

                          LEFT JOIN ibf_cms_uploads AS u

                          ON l.base = u.id

                          WHERE l.refs IN ({$cat_ids})

                          AND u.approved = 0

                          AND u.version_id = l.current_version

                          ORDER BY u.%ORDER%

                        ";

                 return $sql;
         }

         //---------------------------------------------------------------------
         //  viewing the selected article
         //---------------------------------------------------------------------

        function show_article( $art_id = 0 ) {
        global $ibforums, $DB, $std, $NAV, $MISC, $USR;

                $cat_id = $NAV->get_cat_id();
                $art_id = ( $art_id == 0 ) ? $NAV->get_art_id() : $art_id;
                $ver_id = intval( $ibforums->input['version'] );

                $article = "";
                $files   = array();

                //------------------------------------------
                // did we've got right ids?
                //------------------------------------------

                if (  $art_id == 0 || $cat_id == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                 //-------------------------------------------------------------
                 // is user's group allowed to show articles?
                 //-------------------------------------------------------------

                 if ( $ibforums->member['g_art_view'] == 0 ) {

                         $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                         exit();
                 }

                //------------------------------------------
                // are we in the hidden category?
                //------------------------------------------

                if ( $USR->is_mod() === false && $NAV->cats[$cat_id]['visible'] == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->get_article_version( 'all' );

                //--------------------------------------------------------------
                // which version to display
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $versions['count'] ) {

                        $ver_id_sql = "AND u.version_id = l.current_version";
                } else {

                        $ver_id_sql = "AND u.version_id = {$ver_id}";
                }

                //--------------------------------------------------------------
                // load DB string
                //--------------------------------------------------------------

                $DB->query(" SELECT u.*,l.*,u.version_id AS version
			     FROM ibf_cms_uploads AS u

                             LEFT JOIN ibf_cms_uploads_cat_links AS l

                             ON l.base = u.id

                             WHERE u.id = {$art_id}

                             {$ver_id_sql}

                           ");

                $article = $DB->fetch_row();

                if ( !$article ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // save current version_id
                //-------------------------------------------------------------

                $ver_id = $article['version'];


                //------------------------------------------
                //  does article approved, visible?
                //  $USR->is_owner() also returns 'TRUE'
                //  for modeator or admin
                //------------------------------------------

                if ( $USR->is_owner($article['user_id']) === false && $article['approved'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //------------------------------------------
                // hacking? xe-xe
                //------------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links

                             WHERE base = {$art_id}

                           ");

                $dbres = $DB->fetch_row();

                if ($cat_id != $dbres['refs']) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //------------------------------------------
                // category info for selected article
                //------------------------------------------

                $article['cat'] = $NAV->cats[$cat_id];

                //------------------------------------------
                // do we have any attached file?
                //------------------------------------------

                $DB->query(" SELECT l.*, f.*
			     FROM ibf_cms_uploads_file_links AS l

                             LEFT JOIN ibf_cms_uploads_files AS f

                             ON f.id = l.refs

                             WHERE base = {$art_id}

                           ");

                while ($dbres = $DB->fetch_row()) {

                        $files[] = $dbres;
                }

                //------------------------------------------
                // make "approve", "edit", "delete" links
                //------------------------------------------

                $article = $this->make_admin_links( $article );

                //------------------------------------------
                // unparse DB text
                //------------------------------------------

                        $data = array(  TEXT          => $article['article'],
                                        SMILIES       => 1,
                                        CODE          => 1,
                                        SIGNATURE     => 0,
                                        HTML          => 1,
                                        HID              => $this->highlight,
                                        TID              => $this->topic['tid'],
                                        MID              => $article['user_id'],
                                     );

                $article['article'] = $this->parser->prepare($data);

                //------------------------------------------
                //  unparse [IMG=...] tags
                //  TODO: move it to IPB parser
                //------------------------------------------

                preg_match_all("#\[img=([^\]]+?)\]#ie", $article['article'], $images);
                $images = $images[1];

                foreach ( $files as $file ) {

                        foreach ( $images as $image ) {

                                if ( $file['name'] == basename($image) ) {

                                        $article['article'] = preg_replace("/\[IMG=" . preg_quote($image, '/') . "\]/i",
                                                                        "<img src='{$ibforums->vars['dynamiclite']}get_file={$file['id']}&cat={$cat_id}&id={$art_id}'>",
                                                                        $article['article']);
                                        $used_images[] = $file['name'];
                                }
                        }
                }

                //--------------------------------------------------------------

                $article_url = $NAV->build_path($cat_id, true) . $article['article_id'];

                //--------------------------------------------------------------
                // explode article to some pages
                //--------------------------------------------------------------

                if ( preg_match("#\[NEW_PAGE\]#ie", $article['article']) ) {

                        //------------------------------------------------------
                        // make pages array
                        //------------------------------------------------------

                        $pages = explode("[NEW_PAGE]", $article['article']);

                        //------------------------------------------------------
                        // get current page id
                        //------------------------------------------------------

                        $current_page = $NAV->get_art_page();

                        //------------------------------------------------------

                        $version_link = ( $ibforums->input['version'] != 0 ) ? "?version=" . $ibforums->input['version'] : "";

                        //------------------------------------------------------
                        // create pages bar
                        //------------------------------------------------------

                        foreach ( $pages as $page_num => $page_data ) {

                                //----------------------------------------------
                                // highlight current page
                                //----------------------------------------------

                                if ( $current_page == $page_num+1 ) {

                                        $pages_bar .= $this->csite_html->tmpl_article_page_current($page_num+1);

                                } else {

                                        //--------------------------------------
                                        // make link to the next page
                                        //--------------------------------------

                                        $pages_bar .= ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                                       $this->csite_html->tmpl_article_page_link( $cat_id, $art_id, $ver_id, $page_num+1) :
                                                       $this->csite_html->tmpl_article_page_link_rw( $article_url, $page_num+1, $version_link);
                                }
                        }

                        //------------------------------------------------------
                        // make full view pages bar
                        //------------------------------------------------------

                        $pages_count_string = sprintf($ibforums->lang['article_pages_list'],
                                                                 count($pages),
                                                                 ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                                                              $this->csite_html->tmpl_article_pages_all( $cat_id, $art_id, $ver_id ) :
                                                                              $this->csite_html->tmpl_article_pages_all_rw( $article_url, $art_id, $version_link ),
                                                                 $pages_bar
                                                      );

                        $article['article_pages'] = $this->csite_html->tmpl_article_pages($pages_count_string);

                        //------------------------------------------------------
                        // show only selected page
                        //------------------------------------------------------

                        if ( $current_page != 0 ) {

                                //----------------------------------------------
                                // show only existing pages
                                //----------------------------------------------

                                if ( !$pages[$current_page-1] ) {

                                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                                        exit();
                                }

                                $article['article'] = $pages[$current_page-1];
                        } else {

                                //----------------------------------------------
                                // show all the pages
                                //----------------------------------------------

                                $article['article'] = preg_replace("#\[NEW_PAGE\]#ie", preg_quote(""), $article['article']);
                        }
                }

                //--------------------------------------------------------------
                // make versions choose bar
                //--------------------------------------------------------------

                if ( $versions['count'] > 1 ) {

                        for ( $i = 1; $i <= $versions['count']; $i++ ) {

                                $version_link = ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                                  $this->csite_html->tmpl_article_versions_link( $art_id, $cat_id, $i ) :
                                                  $this->csite_html->tmpl_article_versions_link_rw( $article_url , $i );

                                $version_links .= ( $i != ($ver_id) ) ? $version_link : $this->csite_html->tmpl_article_version_current( $i );
                        }

                        //------------------------------------------------------
                        // make full view versions bar
                        //------------------------------------------------------

                        $versions_count_string = sprintf($ibforums->lang['article_versions_list'],
                                                                 $versions['count'],
                                                                 $version_links
                                                      );

                        $article['article_versions'] = $this->csite_html->tmpl_article_versions($versions_count_string);


                }


                //------------------------------------------
                //  show list of attached files
                //  TODO: move to $this->SKIN
                //------------------------------------------

                foreach ($files as $file) {

                        if ( $MISC->is_used($file['name'], $used_images) === false ) {

                                $path = $NAV->build_path($article['refs']) . $article['article_id'] . "/" . $file['name'];
                                $files_list .= "<a href='{$ibforums->vars['dynamiclite']}get_file={$file['id']}&cat={$article['refs']}&id={$article['id']}' target='blank'>{$file['name']}</a> (" . number_format(( filesize($path) / 1024 ), 2) . " Kb) [Скачиваний: {$file['hits']}]<br>";
                        }
                }


                $article['files'] = (!$files_list) ? "" : $this->csite_html->tmpl_article_files($files_list);

                $article['bottom'] = $this->csite_html->tmpl_article_bottom(
                                                                    sprintf($ibforums->lang['main_frame_bottom_str'],
                                                                            $article['hits'],
                                                                            sprintf($ibforums->lang['main_frame_top_str'],
                                                                                    $this->csite_html->tmpl_cat_link($NAV->cats[$article['refs']]),
                                                                                    $std->make_profile_link($article['author_name'], $article['user_id']),
                                                                                    $std->get_date($article['submit_date'])),
                                                                            null,
                                                                            null));


                //------------------------------------------
                // write statistics
                //------------------------------------------

                $DB->query(" UPDATE ibf_cms_uploads

                             SET hits=hits+1

                             WHERE id = {$art_id}

                             AND version_id = {$ver_id}

                           ");

                //--------------------------------------------------------------
                // article watchdog - insert mid, who saw the article
                //--------------------------------------------------------------

                $this->update_articles_watchdog( $art_id, 'update' );

                //------------------------------------------
                // show comments
                //------------------------------------------

                if ( $NAV->cats[$article['refs']]['allow_comments'] != 0 ) {

                        require ROOT_PATH . "sources/dynamiclite/mod_art_comments.php";

                        $this->current_article_id = $article['article_id'];

                        $COMM = new mod_art_comments( &$this );

                        $article['comments'] = $COMM->result;
                }

                //------------------------------------------
                // finished!
                //------------------------------------------

                return $this->csite_html->tmpl_article($article);
        }

        /*
          +--------------------------------------------------------------------+
          |                                                                    |
          |              DISPLAY ARTICLE UPLOAD/EDIT FUNCTIONS                 |
          |                                                                    |
          +--------------------------------------------------------------------+
        */

        //----------------------------------------------------------------------
        //    displaying article upload form
        //----------------------------------------------------------------------

        function show_upload() {
        global $ibforums, $std, $NAV, $USR, $DB, $IBF_POST;

               $cat_id = $NAV->get_cat_id();

               //------------------------------------------
               // have we selected category?
               // does this categoy exist?
               //------------------------------------------

               if ( $cat_id == 0 || !$NAV->cats[$cat_id] ) {

                       $std->Error(array('MSG' => 'cms_category_not_selected', 'INIT' => '1'));
                       exit();
               }

               //------------------------------------------
               // guests can't upload
               // deny to upload in !allow_posts category
               //------------------------------------------

               if ( ( $NAV->cats[$cat_id]['allow_posts'] == 0 && $USR->is_mod() === false ) || $ibforums->member['id'] < 1 ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
               }

               //-------------------------------------------------------------
               // is user's group allowed to show articles?
               //-------------------------------------------------------------

               if ( $ibforums->member['g_art_add'] == 0 ) {

                       $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                       exit();
               }

               //------------------------------------------
               // can we upload more than one article?
               //------------------------------------------

               if ( $NAV->cats[$cat_id]['one_article'] == 0 ) {

                       $DB->query("SELECT * FROM ibf_cms_uploads_cat_links WHERE refs = {$cat_id}");

                       if ( $DB->get_num_rows() >= 1 ) {

                               $std->Error(array('MSG' => 'only_one_article', 'INIT' => '1'));
                               exit();
                       }
               }


               //----------------------------------------------
               //  make upload form title
               //----------------------------------------------

               $entry['title'] = sprintf($ibforums->lang['show_upload'], $NAV->cats[$cat_id]['name']);

               //----------------------------------------------
               //  make list of files to attach
               //----------------------------------------------

               if ( $ibforums->vars['csite_max_upload_files'] && $ibforums->member['g_art_attach'] == 1 ) {

                       for ( $i = 1; $i <= $ibforums->vars['csite_max_upload_files']; $i++ ) {

                               $entry['files'] .= $this->csite_html->tmpl_upload_file($i);
                       }
               }

               //----------------------------------------------
               //  make article icon to attach
               //----------------------------------------------

               if ( $ibforums->member['g_art_attach'] == 1 ) {

                       $entry['icon'] = $this->csite_html->tmpl_show_upload_icon();
               }

               //----------------------------------------------
               //  make article_id input field
               //----------------------------------------------

               if ( 1 ) {

                       $entry['article_id_form'] = $this->csite_html->tmpl_show_art_id_form();
               }

               //----------------------------------------------
               //  make category id to upload to
               //----------------------------------------------

               if ( 1 ) {

                       $entry['cat'] = $cat_id;
               }


               //----------------------------------------------
               //  make CODE = 1 - $this->do_upload()
               //----------------------------------------------

               if ( 1 ) {

                       $entry['ACTION'] = '1';
               }

               //----------------------------------------------
               //  what form admin uses to upload in this cat?
               //  0 - default BB-tags form
               //  * - WISIWIG editor
               //----------------------------------------------

               if ( $NAV->cats[$cat_id]['add_article_form'] != 0 ) {

                       return $this->csite_html->tmpl_show_upload_form_wisiwig($entry);
               }

               //---------------------------------------------
               // Let's use default IPB add topic boxes
               // and take them from Post class (parent)
               //---------------------------------------------

               //---------------------------------------------
               // initialize Post class for D-Site
               //---------------------------------------------

               parent::csite_init();

               //---------------------------------------------
               // get main post form template
               //---------------------------------------------

               $entry['ibf_post_body'] = parent::html_post_body();

               //---------------------------------------------
               // get javascript for code buttons
               //---------------------------------------------

               $entry['ibf_post_javascript'] = parent::csite_get_javascript();

               //---------------------------------------------
               // check'n'get smilie table
               //---------------------------------------------

               if ( $NAV->cats[$cat_id]['show_smilies'] != 0 ) {

                       $entry['ibf_post_body'] = preg_replace( "/<!--SMILIE TABLE-->/"  ,  parent::html_add_smilie_box()   , $entry['ibf_post_body'] );
               }

               //---------------------------------------------------------------
               // make form action url for mod_rewrite
               //---------------------------------------------------------------

               $entry['url'] = $NAV->build_path($NAV->get_cat_id(), true) . $entry['article_id'] . "/do_create.html";


               //---------------------------------------------
               // finished!
               //---------------------------------------------

               return $this->csite_html->tmpl_show_upload_form_bb( $entry );
        }

        //----------------------------------------------------------------------
        // uploading added article to the server
        //----------------------------------------------------------------------

        function do_upload() {
        global $ibforums, $DB, $CAT, $NAV, $std, $MISC, $USR;

                $entry          = array();
                $uploaded_files = "";
                $uploaded_icon  = "";

                $cat_id = $NAV->get_cat_id();
                $ver_id = intval( $ibforums->input['version'] );

                 //-------------------------------------------------------------
                 // is user's group allowed to show articles?
                 //-------------------------------------------------------------

                 if ( $ibforums->member['g_art_add'] == 0 ) {

                         $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                         exit();
                 }

                //----------------------------------------------
                //  check for empty and incorrect fields
                //----------------------------------------------

                if ( $MISC->is_empty($ibforums->input, array('name', 'Post', 'article_id', 'cat')) ) {

                        $std->Error(array('MSG' => 'data_incorrect_empty', 'INIT' => '1'));
                        exit();
                }

                if ( $cat_id == 0 || !$NAV->cats[$cat_id] ) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                // only allowed symbols
                //----------------------------------------------

                if ( !preg_match("/^[a-z_]/i", $ibforums->input['article_id']) && $current_id == 0 ) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                // fuck off hackorz
                //----------------------------------------------

                if ( $ibforums->member['id'] < 1 || ( $NAV->cats[$cat_id]['allow_posts'] == 0 && $USR->is_mod() == false ) ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //----------------------------------------------
                // make some usefull info
                //----------------------------------------------

                $path = $NAV->build_path($cat_id) . "/" . $ibforums->input['article_id'] . "/";
                $date = time();

                //----------------------------------------------
                // do we have pre-moderation?
                // ibf_cms_uploads_cat [moderate] IN
                // 0 - don't moderate
                // 1 - always moderate
                // 2 - moderate edited
                //----------------------------------------------

                if ( $NAV->cats[$cat_id]['moderate'] != '1' ) {

                        $approved = $date;
                }

                //----------------------------------------------
                // do we have article with the same name and ID?
                //----------------------------------------------

                $DB->query(" SELECT u.id,l.* FROM ibf_cms_uploads AS u

                             LEFT JOIN ibf_cms_uploads_cat_links AS l

                             ON u.id = l.base

                             WHERE article_id = '{$ibforums->input['article_id']}'

                             AND l.refs = {$cat_id}

                           ");

                if ( $DB->get_num_rows() >= 1 ) {

                       $std->Error(array('MSG' => 'article_exists', 'INIT' => '1'));
                       exit();
                }

                //----------------------------------------------
                // compile and insert DB string
                //----------------------------------------------

                $convert = $this->parser->convert(
                                                 array( TEXT     => $ibforums->input['Post'],
                                                        SMILIES  => $ibforums->input['enableemo'],
                                                        CODE     => $this->forum['use_ibc'],
                                                        HTML     => $this->forum['use_html'],
                                                        MOD_FLAG => $modflag,
                                              ),
                                                $this->forum['id'] );



                $DB->query(" INSERT INTO ibf_cms_uploads

                               (
                                    name,
                                    article_id,
                                    short_desc,
                                    article,
                                    user_id,
                                    author_name,
                                    submit_date

                               )

                              VALUES

                                (
                                    '{$ibforums->input['name']}',
                                    '{$ibforums->input['article_id']}',
                                    '{$ibforums->input['short_desc']}',
                                    '{$convert}',
                                    '{$ibforums->member['id']}',
                                    '{$ibforums->member['name']}',
                                    '{$date}'
                                )

                                   ");

                $inserted_id = $DB->get_insert_id();

                //----------------------------------------------
                // attemt to create article directory
                //----------------------------------------------

                if ( $CAT->create_directory($path) == false ) {
                        //----------------------------------------------
                        // if couldn't create - delete inserted
                        //----------------------------------------------

                        $DB->query(" DELETE FROM ibf_cms_uploads WHERE id = {$inserted_id} ");

                        $std->Error(array('MSG' => 'error_create_upload_dir', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                // link the article to the directory
                //----------------------------------------------

                $DB->query(" INSERT INTO ibf_cms_uploads_cat_links

                              (
                                    base,
                                    refs
                              )

                             VALUES

                              (
                                   {$inserted_id},
                                   {$cat_id}
                              )

                           ");

                //----------------------------------------------
                // if files were attached, let's upload them
                // and link to the article
                // TODO: don't save to DB full file['path']
                //----------------------------------------------

                $uploaded_files = $CAT->upload_files($path);

                if ( $uploaded_files && $ibforums->member['g_art_attach'] == 1 ) {

                        foreach ( $uploaded_files as $uploaded_file ) {

                                $DB->query(" INSERT INTO ibf_cms_uploads_file_links

                                               (

                                                 base,
                                                 refs

                                               )

                                              VALUES

                                               (

                                                  {$inserted_id},
                                                  {$uploaded_file['id']}

                                               )

                                           ");
                        }
                }

                //----------------------------------------------
                // if an icon was attached, let's upload it
                // and link to the article
                //----------------------------------------------

                $uploaded_icon = $CAT->upload_icon($path);

                if ( $uploaded_icon['id'] != 0 && $ibforums->member['g_art_attach'] == 1 ) {

                        $DB->query(" UPDATE ibf_cms_uploads SET

                                            icon_id = '{$uploaded_icon['id']}'

                                     WHERE
                                            id = {$inserted_id}
                                   ");
                }

                //----------------------------------------------
                // if button "Attach File" was pressed return
                // editing of uploaded article, otherwise
                // redirect ot current category
                // TODO: all redirects through $std->boink_it();
                //----------------------------------------------

                if ( $ibforums->input['attach_file'] && $ibforums->member['g_art_attach'] == 1 ) {
                		if ( $ver_id != 0 ) {
                			$ver_link = "?version={$ver_id}";
                		}
                        $redirect_url = $NAV->build_path($cat_id, true) . $entry['article_id'] . "/edit.html" . $ver_link;

                        return header("Location: {$redirect_url}");
                } else {

                        $redirect_url = $NAV->build_path($cat_id, true) . $entry['article_id'] . "/edit.html" . $ver_link;

                        return header("Location: {$redirect_url}");
                }

        }


        //----------------------------------------------------------------------
        //    displaying article edit form
        //----------------------------------------------------------------------

        function show_edit() {
        global $ibforums, $NAV, $DB, $std, $DSITE, $USR;

               $cat_id = $NAV->get_cat_id();
               $art_id = $NAV->get_art_id();
               $ver_id = intval( $ibforums->input['version'] );

               $entry = array();

               //------------------------------------------
               // have we selected category, article?
               // does this categoy exist?
               //------------------------------------------

               if ( $cat_id == 0 || !$NAV->cats[$cat_id] || $art_id == 0 ) {

                       $std->Error(array('MSG' => 'cms_category_not_selected', 'INIT' => '1'));
                       exit();
               }

               //-------------------------------------------------------------
               // is user's group allowed to show articles?
               //-------------------------------------------------------------

               if ( $ibforums->member['g_art_edit'] == 0 ) {

                       $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                       exit();
               }

                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->get_article_version( 'all' );

                //--------------------------------------------------------------
                // which version to display
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $versions['count'] ) {

                        $ver_id = $versions['latest'];
                }


               //---------------------------------------------------------------
               //  does the article exists?
               //---------------------------------------------------------------

               $DB->query(" SELECT * FROM ibf_cms_uploads WHERE id = {$art_id} AND version_id = {$ver_id} ");

               $entry = $DB->fetch_row();

               if ( $entry == "" ) {

                       $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                       exit();
               }

               //------------------------------------------
               // deny to upload in !allow_posts category
               // allow to change only owner and admin
               //------------------------------------------

               if ( $USR->is_owner($entry['user_id']) === false || ( $NAV->cats[$cat_id]['allow_posts'] == 0 && $USR->is_mod() === false ) ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
               }


               //----------------------------------------------
               //  make upload form title
               //----------------------------------------------

               $entry['title'] = sprintf($ibforums->lang['show_edit'], $entry['name']);

               //----------------------------------------------
               //  make list of files to attach
               //  if some files were attached, show them
               //  otherwise show upload file fields
               //----------------------------------------------

                $DB->query(" SELECT l.*, f.* FROM ibf_cms_uploads_file_links AS l

                             LEFT JOIN ibf_cms_uploads_files AS f

                             ON f.id = l.refs

                             WHERE base = {$art_id}

                           ");

                while ( $dbres = $DB->fetch_row() ) {

                        $old_uploaded_files[] = $dbres;
                }

                //----------------------------------------------
                // make uploaded files html
                //----------------------------------------------

                if ( $old_uploaded_files && $ibforums->member['g_art_attach'] == 1 ) {

                        foreach( $old_uploaded_files as $uploaded_file ) {

                               //----------------------------------------------
                               // some useful links
                               //----------------------------------------------

                               $path = $NAV->build_path( $cat_id, true );
                               $uploaded_file['url'] = "{$path}{$entry['article_id']}/{$uploaded_file['name']}";
                               $uploaded_file['del'] = $this->csite_html->tmpl_del_file_lnk($uploaded_file['id'], $cat_id, $art_id);

                               //----------------------------------------------
                               // finally make html
                               //----------------------------------------------

                               $uploaded_files[] = $this->csite_html->tmpl_uploaded_file($uploaded_file);
                        }
                }

                //----------------------------------------------
                // if we haven't uploaded all approved files,
                // let's show upload file input forms
                //----------------------------------------------

                if ( $ibforums->member['g_art_attach'] == 1 )  {

                        for ($i = 0; $i < $ibforums->vars['csite_max_upload_files']; $i++) {

                                if ( !$uploaded_files[$i] ) {

                                        $uploaded_files[] = $this->csite_html->tmpl_upload_file($i+1);
                                }
                       }
                }

                //----------------------------------------------
                // make all files as html
                //----------------------------------------------

                if ( is_array($uploaded_files) && $ibforums->member['g_art_attach'] == 1 ) {

                       $entry['files'] = implode("\n", $uploaded_files);
                }

               //----------------------------------------------
               //  make article icon to attach or show attached
               //----------------------------------------------

               if ( $entry['icon_id'] != 0 && $ibforums->member['g_art_attach'] == 1 ) {

                       $DB->query(" SELECT * FROM ibf_cms_uploads_files WHERE id = {$entry['icon_id']} ");

                       $dbres = $DB->fetch_row();

                       if ( $dbres ) {

                               //----------------------------------------------
                               // some useful links
                               //----------------------------------------------

                               $path = $NAV->build_path( $cat_id, true );
                               $icon['url'] = "{$path}{$entry['article_id']}/{$dbres['name']}";
                               $icon['del'] = $this->csite_html->tmpl_del_file_lnk($dbres['id'], $cat_id, $art_id, '1');

                               //----------------------------------------------
                               // make html
                               //----------------------------------------------

                               $uploaded_icon = $this->csite_html->tmpl_uploaded_icon($icon);
                       }
               }

               //----------------------------------------------
               // show uploaded icon or input form to upload
               //----------------------------------------------

               if ( $ibforums->member['g_art_attach'] == 1 ) {

                       $entry['icon'] = ($uploaded_icon) ? $uploaded_icon : $this->csite_html->tmpl_show_upload_icon();
               }


               //----------------------------------------------
               //  make article_id input field
               //----------------------------------------------

               if ( 1 ) {

                       $entry['article_id_form'] = $this->csite_html->tmpl_show_art_id_form($entry['article_id']);
               }

               //----------------------------------------------
               //  make category id to upload to
               //----------------------------------------------

               if ( 1 ) {

                       $entry['cat'] = $cat_id;
               }

               //----------------------------------------------
               //  post the version num
               //----------------------------------------------

               if ( 1 ) {

                       $entry['version'] = $ver_id;
               }

               //----------------------------------------------
               //  make ACTION = 3 - $this->do_edit()
               //----------------------------------------------

               if ( 1 ) {

                       $entry['ACTION'] = '3';
               }

               //---------------------------------------------------------------
               // are new versions creating allowed?
               //---------------------------------------------------------------

               if ( $NAV->cats[$cat_id]['force_versioning'] == 1 ) {

                       //-------------------------------------------------------
                       // template to force a new version
                       //-------------------------------------------------------

                       $entry['force_article_version'] = $this->csite_html->tmpl_force_article_version();

                       //-------------------------------------------------------
                       // template to set version as default
                       //-------------------------------------------------------

                       $entry['default_article_version'] = $this->csite_html->tmpl_default_article_version();
               }

               //---------------------------------------------------------------
               // make form action url for mod_rewrite
               //---------------------------------------------------------------

               $entry['url'] = $NAV->build_path($NAV->get_cat_id(), true) . $entry['article_id'] . "/write.html";

               //----------------------------------------------
               //  what form admin uses to upload in this cat?
               //  0 - default BB-tags form
               //  * - WISIWIG editor
               //----------------------------------------------

               if ( $NAV->cats[$cat_id]['add_article_form'] != 0 ) {

                       return $this->csite_html->tmpl_show_upload_form_wisiwig($entry);
               }

                //-------------------------------------------------
                // Sort out the "raw" textarea input and make it safe incase
                // we have a <textarea> tag in the raw post var.
                //-------------------------------------------------

                $raw_post = $this->parser->unconvert($entry['article'], 1, 0);

                if ( isset($raw_post) )
                {
                        $raw_post = $std->txt_raw2form($raw_post);

                        $raw_post = str_replace( array ("&#091;",         "&#093;"     ),
                                                 array ("&amp;#091;",         "&amp;#093;" ), $raw_post);
                }

               //---------------------------------------------
               // Let's use default IPB add topic boxes
               // and take them from Post class (parent)
               //---------------------------------------------

               //---------------------------------------------
               // initialize Post class for D-Site
               //---------------------------------------------

               parent::csite_init();

               //---------------------------------------------
               // get main post form template
               //---------------------------------------------

               $entry['ibf_post_body'] = parent::html_post_body($raw_post);

               //---------------------------------------------
               // get javascript for code buttons
               //---------------------------------------------

               $entry['ibf_post_javascript'] = parent::csite_get_javascript();

               //---------------------------------------------
               // check'n'get smilie table
               //---------------------------------------------

               if ( $NAV->cats[$cat_id]['show_smilies'] != 0 ) {

                       $entry['ibf_post_body'] = preg_replace( "/<!--SMILIE TABLE-->/"  ,  parent::html_add_smilie_box()   , $entry['ibf_post_body'] );
               }


               //---------------------------------------------
               // finished!
               //---------------------------------------------

               return $this->csite_html->tmpl_show_upload_form_bb( $entry );
        }

        //----------------------------------------------------------------------
        // uploading added article to the server
        //----------------------------------------------------------------------

        function do_edit() {
        global $ibforums, $DB, $CAT, $NAV, $std, $MISC, $USR;

                $entry          = array();
                $uploaded_files = "";
                $uploaded_icon  = "";

                $cat_id = $NAV->get_cat_id();
                $art_id = $NAV->get_art_id();
                $ver_id = intval($ibforums->input['version']);

                //print_r($ibforums->input);

                //echo $cat_id; exit;

                //-------------------------------------------------------------
                // is user's group allowed to show articles?
                //-------------------------------------------------------------

                if ( $ibforums->member['g_art_edit'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                //  does the article exists?
                //----------------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_uploads WHERE id = {$art_id}  ");

                $entry = $DB->fetch_row();

                if ( $entry == "" ) {

                       $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                       exit();
                }

                //----------------------------------------------
                // fuck off hackorz
                //----------------------------------------------

                if ( ( $USR->is_owner($entry['user_id']) === false && $USR->is_mod() === false ) || ( $NAV->cats[$cat_id]['allow_posts'] == 0 && $USR->is_mod() === false ) ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //----------------------------------------------
                //  check for empty and incorrect fields
                //----------------------------------------------

                if ($MISC->is_empty($ibforums->input, array('name', 'Post', 'article_id', 'cat'))) {

                        $std->Error(array('MSG' => 'data_incorrect_empty', 'INIT' => '1'));
                        exit();
                }

                if ( $cat_id == 0 || !$NAV->cats[$cat_id] ) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                // only allowed symbols
                //----------------------------------------------

                if ( !preg_match("/^[a-z_]/i", $ibforums->input['article_id']) && $current_id == 0 ) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                // if article_id was chenged, let's move
                // old attached files to a new location
                //----------------------------------------------

                $new_path = $NAV->build_path($cat_id) . "/" . $ibforums->input['article_id'];

                if ( $ibforums->input['article_id'] != $entry['article_id'] && $ibforums->member['g_art_attach'] == 1 ) {

                        $old_path = $NAV->build_path($cat_id) . "/" . $entry['article_id'];

                        //------------------------------------------------------
                        // do we have article with the same ID?
                        //------------------------------------------------------

                        $DB->query(" SELECT u.id,l.base FROM ibf_cms_uploads AS u

                                     LEFT JOIN ibf_cms_uploads_cat_links AS l

                                     ON u.id = l.base

                                     WHERE article_id = '{$ibforums->input['article_id']}'

                                   ");

                        if ( $DB->get_num_rows() >= 1 ) {

                                $std->Error(array('MSG' => 'article_exists', 'INIT' => '1'));
                                exit();
                        }


                        //----------------------------------------------
                        // attemt to move files
                        //----------------------------------------------

                        if ( $MISC->copy_dir($old_path, $new_path) === false ) {

                                $std->Error(array('MSG' => 'error_create_upload_dir', 'INIT' => '1'));
                                exit();
                        }

                        //----------------------------------------------
                        // attemt to delete old files
                        //----------------------------------------------

                        if ( $MISC->rm_dir($old_path) === false ) {

                                $std->Error(array('MSG' => 'error_create_upload_dir', 'INIT' => '1'));
                                exit();
                        }
                }

                //----------------------------------------------
                // make some usefull info
                //----------------------------------------------

                $date = time();

                //----------------------------------------------
                // do we have pre-moderation?
                // ibf_cms_uploads_cat [moderate] IN
                // 0 - don't moderate
                // 1 - always moderate
                // 2 - moderate edited
                //----------------------------------------------

                if ( $NAV->cats[$cat_id]['moderate'] != '0' ) {

                        $approved = '0';

                } else {

                        $approved = $date;
                }

                //----------------------------------------------
                // compile and insert DB string
                //----------------------------------------------

                $convert = $this->parser->convert(
                                                 array( TEXT     => $ibforums->input['Post'],
                                                        SMILIES  => $ibforums->input['enableemo'],
                                                        CODE     => $this->forum['use_ibc'],
                                                        HTML     => $this->forum['use_html'],
                                                        MOD_FLAG => $modflag,
                                              ),
                                                $this->forum['id'] );

                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->get_article_version( 'all' );

                //--------------------------------------------------------------
                // which version to edit
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $versions['count'] ) {

                        $ver_id = $versions['latest'];
                }

                //--------------------------------------------------------------
                // user's choise of new version
                //--------------------------------------------------------------

                $force_new_version = intval( $ibforums->input['force_new_version'] );

                //--------------------------------------------------------------
                // make sql query to insert/update article
                //--------------------------------------------------------------

                if ( $force_new_version == 0 || $NAV->cats[$cat_id]['force_versioning'] == 0 ) {

                     $sql = (string) " UPDATE ibf_cms_uploads SET

                                         name        = '{$ibforums->input['name']}',
                                         short_desc  = '{$ibforums->input['short_desc']}',
                                         article     = '{$convert}',
                                         article_id  = '{$ibforums->input['article_id']}',
                                         submit_date = '{$date}',
                                         approved    = '{$approved}'

                                  WHERE

                                       id = {$art_id}

                                  AND

                                       version_id = {$ver_id}

                           ";
                } else if ( $NAV->cats[$cat_id]['force_versioning'] == 1 && $force_new_version == 1 ) {

                        //------------------------------------------------------
                        // increment to force new version
                        //------------------------------------------------------

                        $ver_id = $versions['latest'] + 1;

                        //------------------------------------------------------
                        // insert a new version into DB
                        //------------------------------------------------------

                        $sql = (string) " INSERT INTO ibf_cms_uploads

                                                       (
                                                            id,
                                                            version_id,
                                                            name,
                                                            article_id,
                                                            short_desc,
                                                            article,
                                                            user_id,
                                                            author_name,
                                                            submit_date

                                                       )

                                                      VALUES

                                                        (
                                                            '{$art_id}',
                                                            '{$ver_id}',
                                                            '{$ibforums->input['name']}',
                                                            '{$ibforums->input['article_id']}',
                                                            '{$ibforums->input['short_desc']}',
                                                            '{$convert}',
                                                            '{$ibforums->member['id']}',
                                                            '{$ibforums->member['name']}',
                                                            '{$date}'
                                                        )

                                                           ";

                }

                //--------------------------------------------------------------
                // if we don't upload any files, write article
                //--------------------------------------------------------------

                if ( !$ibforums->input['attach_file'] ) {

                        $DB->query( $sql );
                }

                //--------------------------------------------------------------
                // set editing version as default
                //--------------------------------------------------------------

                $set_as_default_version = intval( $ibforums->input['set_as_default_version'] );

                if ( $set_as_default_version == 1 ) {

                        $DB->query( " UPDATE ibf_cms_uploads_cat_links SET current_version = {$ver_id} WHERE base = {$art_id} " );

                } else {

                        $DB->query( " UPDATE ibf_cms_uploads_cat_links SET current_version = {$versions['latest']} WHERE base = {$art_id} " );
                }


                //----------------------------------------------
                // if files were attached, let's upload them
                // and link to the article
                // TODO: if we already have uploaded files, don,t save them
                //----------------------------------------------

                unset($uploaded_files);

                if ( $ibforums->member['g_art_attach'] == 1 ) {

                        $uploaded_files = $CAT->upload_files($new_path);
                }

                if ( $uploaded_files ) {

                        foreach ( $uploaded_files as $uploaded_file ) {

                                $DB->query(" INSERT INTO ibf_cms_uploads_file_links

                                               (

                                                 base,
                                                 refs

                                               )

                                              VALUES

                                               (

                                                  {$art_id},
                                                  {$uploaded_file['id']}

                                               )

                                           ");
                        }
                }

                //----------------------------------------------
                // if an icon was attached, let's upload it
                // and link to the article
                //----------------------------------------------

                unset($uploaded_icon);

                if ( $ibforums->member['g_art_attach'] == 1 ) {

                        $uploaded_icon = $CAT->upload_icon($new_path);
                }

                if ($uploaded_icon['id'] != 0) {

                        $DB->query(" UPDATE ibf_cms_uploads SET

                                            icon_id = '{$uploaded_icon['id']}'

                                     WHERE
                                            id = {$art_id}
                                   ");
                }

                //--------------------------------------------------------------
                // articles watchdog - flush viewed results for all users
                //--------------------------------------------------------------

                $this->update_articles_watchdog( $art_id );

                //----------------------------------------------
                // if button "Attach File" was pressed return
                // editing of uploaded article, otherwise
                // redirect ot current category
                // TODO: all redirects through $std->boink_it();
                //----------------------------------------------

                if ( $ibforums->member['g_art_attach'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                if ( $ibforums->input['attach_file'] ) {

                        $redirect_url = $NAV->build_path($cat_id, true) . $entry['article_id'] . "/edit.html";

                        return header("Location: {$redirect_url}");

                } else {

                        $redirect_url = $NAV->build_path($cat_id, true) . $entry['article_id'] . "/index.html";

                        return header( "Location: {$redirect_url}" );
                }

        }

        //----------------------------------------------------------------------
        //  show deletion confirmation form
        //----------------------------------------------------------------------

        function show_delete() {
        global $ibforums, $std, $DB, $USR, $NAV;

                $html = "";

                $art_id = $NAV->get_art_id();
                $cat_id = $NAV->get_cat_id();
                $ver_id = intval( $ibforums->input['version'] );

                //--------------------------------------
                // check some posted info
                //--------------------------------------

                if ( $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------------------------------
                // is user's group allowed to show articles?
                //-------------------------------------------------------------

                if ( $ibforums->member['g_art_delete'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->get_article_version( 'all' );


                //--------------------------------------------------------------
                // which version to display
                //--------------------------------------------------------------

                if ( $ver_id <= 0 || $ver_id > $versions['count'] ) {

                        $ver_id = $versions['latest'];
                }

                //--------------------------------------
                // does the article exists?
                //--------------------------------------

                $DB->query(" SELECT *, version_id AS version FROM ibf_cms_uploads

                             WHERE id = {$art_id}

                             AND version_id = {$ver_id}

                           ");

                $dbres = $DB->fetch_row();

                if ( !$dbres ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------
                // only owner and moderator can do it
                //--------------------------------------

                if ( $USR->is_owner($dbres['user_id']) === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //--------------------------------------
                // the html form
                //--------------------------------------

                $dbres['title'] = sprintf($ibforums->lang['delete_article'], $dbres['name']);

                $dbres['ACTION'] = '5';

                //--------------------------------------------------------------
                // return url
                //--------------------------------------------------------------

                $dbres['url'] =  $NAV->build_path($cat_id, true) . $dbres['article_id'];

                $dbres['cat'] = $cat_id;

                $html = $this->csite_html->tmpl_delete_form($dbres);

                //--------------------------------------
                // finished!
                //--------------------------------------

                return $html;
        }

        //----------------------------------------------------------------------
        //  deleting an article with all files and it's directory
        //----------------------------------------------------------------------

        function do_delete() {
        global $ibforums, $DB, $NAV, $std, $MISC, $USR;


                $art_id = $NAV->get_art_id();
                $cat_id = $NAV->get_cat_id();
                $ver_id = intval( $ibforums->input['version'] );

                if ( $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }


                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->get_article_version( 'all' );


                //--------------------------------------------------------------
                // which version to display
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $versions['count'] ) {

                        $ver_id = $versions['latest'];
                }

                //--------------------------------------------------------------
                // check if article exists
                //--------------------------------------------------------------


                $DB->query(" SELECT * FROM ibf_cms_uploads

                             WHERE id = {$art_id}

                             AND version_id = {$ver_id}

                           ");

                $dbres = $DB->fetch_row();

                if ( !$dbres ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------------------------------
                // is user's group allowed to show articles?
                //-------------------------------------------------------------

                if ( $ibforums->member['g_art_delete'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                $right_referer = $NAV->build_path( $cat_id, true ) . $dbres['article_id'] . '/delete_screen.html?version=' . $ver_id;

                if ( $_SERVER['HTTP_REFERER'] != $right_referer ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();

                }



                //--------------------------------------------------------------

                if ( $USR->is_owner($dbres['user_id']) === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //--------------------------------------------------------------


                //--------------------------------------------------------------
                // does user want to delete version?
                //--------------------------------------------------------------

                $delete_version = intval( $ibforums->input['delete_version'] );

                //--------------------------------------------------------------
                // delete only a version, not article
                //--------------------------------------------------------------

                if ( $delete_version == 1 && $versions['count'] > 1 ) {

                        //------------------------------------------------------
                        // delete version
                        //------------------------------------------------------

                        $DB->query( " DELETE FROM ibf_cms_uploads WHERE id = {$art_id} AND version_id = {$ver_id} " );

                        //------------------------------------------------------
                        // repair incorrect sequence
                        //------------------------------------------------------

                        $this->repair_versions_sequence( $art_id );


                        //------------------------------------------------------
                        // clear watchdog results
                        //------------------------------------------------------

                        $this->update_articles_watchdog( $art_id );

                        //------------------------------------------------------
                        // return to previous category
                        //------------------------------------------------------

                        $return_url = $NAV->build_path( $cat_id, true );


                        return header("Location: {$return_url}");
                }

                //--------------------------------------------------------------
                // we have only one version || wath to delete all
                //--------------------------------------------------------------

                $DB->query(" DELETE FROM ibf_cms_uploads

                             WHERE id = '{$art_id}'

                           ");

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links

                             WHERE base = {$art_id}

                           ");

                $result = $DB->fetch_row();

                $path = $NAV->build_path($result['refs']) . $dbres['article_id'];

                $MISC->rm_dir($path);


                $DB->query(" DELETE FROM ibf_cms_uploads_cat_links

                             WHERE base = {$art_id}

                           ");

                $DB->query(" SELECT * FROM ibf_cms_uploads_file_links

                             WHERE
                                      base = {$art_id}
                           ");

                while ($result = $DB->fetch_row()) {

                        $files[] = $result;
                }

                $DB->query(" DELETE FROM ibf_cms_uploads_file_links

                             WHERE
                                      base = {$art_id}
                           ");

                if ($files) {

                        foreach ($files as $file) {

                                $DB->query(" DELETE FROM ibf_cms_uploads_files

                                             WHERE
                                                    id = {$file['refs']}
                                           ");
                        }
                }

                if ($dbres['icon_id'] != 0) {

                        $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                     WHERE id = {$dbres['icon_id']}

                                   ");

                        $DB->query(" DELETE FROM ibf_cms_uploads_files

                                     WHERE id = {$dbres['icon_id']}

                                   ");
                }

                //--------------------------------------------------------------
                // articles watchdog - flush viewed results for all users
                //--------------------------------------------------------------

                $this->update_articles_watchdog( $art_id );

                //------------------------------------------------------
                // return to previous category
                //------------------------------------------------------

                $return_url = $NAV->build_path( $cat_id, true );

                return header("Location: {$return_url}");
        }

        //----------------------------------------------------------------------
        //  article's pre-moderation - if we show it to all users or not
        //----------------------------------------------------------------------

        function do_approve( $approve = true ) {
        global $ibforums, $DB, $std, $USR, $NAV;

                $art_id = $NAV->get_art_id();
                $cat_id = $NAV->get_cat_id();
                $ver_id = intval( $ibforums->input['version'] );

                //-------------------------------------------------
                // only moderator can do this
                //-------------------------------------------------

                if ( $USR->is_mod($cat_id) === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //-------------------------------------------------------------
                // is user's group allowed to show articles?
                //-------------------------------------------------------------

                if ( $ibforums->member['g_art_approve'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------------------
                // does article exists?
                //-------------------------------------------------

                if ( $cat_id == 0 || $cat_id == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->get_article_version( 'all' );

                //--------------------------------------------------------------
                // which version to display
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $versions['count'] ) {

                        $ver_id = $versions['latest'];
                }


                $current_time = ($approve) ? time() : null;
                $increment = ($approve == true) ? "+" : "-";

                $DB->query("UPDATE ibf_cms_uploads

                            SET approved = '{$current_time}'

                            WHERE id = {$art_id}

                            AND version_id = {$ver_id}
                            ");

                $query_increment = " UPDATE

                                           ibf_cms_uploads_cat

                                     SET
                                           num=num{$increment}1

                                     WHERE
                                          id = {$art_id}
                                   ";

                $DB->query($query_increment);

                //--------------------------------------------------------------
                // articles watchdog - flush viewed results for all users
                //--------------------------------------------------------------

                $this->update_articles_watchdog( $art_id );

                //------------------------------------------------------
                // return to previous category
                //------------------------------------------------------

                $return_url = $NAV->build_path( $cat_id, true );

                return header("Location: {$return_url}");

        }

        //----------------------------------------------------------------------
        //  show move article form
        //----------------------------------------------------------------------

        function show_move() {
        global $ibforums, $std, $DB, $USR, $NAV;

                $html = "";

                $art_id = $NAV->get_art_id();
                $cat_id = $NAV->get_cat_id();

                //--------------------------------------
                // check some posted info
                //--------------------------------------

                if ( $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                 //-------------------------------------------------------------
                 // is user's group allowed to show articles?
                 //-------------------------------------------------------------

                 if ( $ibforums->member['g_art_move'] == 0 ) {

                         $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                         exit();
                 }

                //--------------------------------------
                // only moderator can do it
                //--------------------------------------

                if ( $USR->is_mod() === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //--------------------------------------
                // does the article exists?
                //--------------------------------------

                $DB->query(" SELECT *, version_id AS version FROM ibf_cms_uploads

                             WHERE id = {$art_id}

                           ");

                $dbres = $DB->fetch_row();

                if ( !$dbres ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------
                // the html form
                //--------------------------------------

                $dbres['title'] = $ibforums->lang['move_article'];

                $dbres['ACTION'] = '9';

                $dbres['cat_list'] = $NAV->build_cat_list_select();

                $dbres['url'] =  $NAV->build_path($cat_id, true) . $dbres['article_id'] . "/do_move.html";

                $html = $this->csite_html->tmpl_move_form($dbres);

                //--------------------------------------
                // finished!
                //--------------------------------------

                return $html;
        }

        //----------------------------------------------------------------------
        //  moving article to another category
        //----------------------------------------------------------------------

        function do_move() {
        global $ibforums, $std, $DB, $NAV, $USR, $MISC;

                //--------------------------------------
                // only moderator can do it
                //--------------------------------------

                if ( $USR->is_mod() === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //-------------------------------------------------------------
                // is user's group allowed to show articles?
                //-------------------------------------------------------------

                if ( $ibforums->member['g_art_move'] == 0 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // do we have all needed data?
                //-------------------------------------

                $cat_id  = $NAV->get_cat_id();
                $mcat_id = intval($ibforums->input['move_cat_id']);
                $art_id  = $NAV->get_art_id();

                if ( $cat_id == 0 || $art_id == 0 || $mcat_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // does we move it to the same category?
                //-------------------------------------

                if ( $mcat_id == $cat_id ) {

                        $std->Error(array('MSG' => 'article_move_incorrect', 'INIT' => '1'));
                        exit();

                }

                //-------------------------------------
                // does the article exists?
                //-------------------------------------

                $DB->query("SELECT * FROM ibf_cms_uploads WHERE id = {$art_id}");

                $dbres = $DB->fetch_row();

                if ( !$dbres ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // check for valid referer
                //-------------------------------------

                $right_referer = $NAV->build_path( $cat_id, true ) . $dbres['article_id'] . '/move.html';

                if ( $_SERVER['HTTP_REFERER'] != $right_referer ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();

                }

                //-------------------------------------
                // can we move there?
                //-------------------------------------

                if ( $NAV->cats[$mcat_id]['allow_posts'] == 0 && $USR->is_admin() === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //------------------------------------------
                // can we upload more than one article?
                //------------------------------------------

                if ( $NAV->cats[$mcat_id]['one_article'] == 0 ) {

                        $DB->query("SELECT * FROM ibf_cms_uploads_cat_links WHERE refs = {$mcat_id}");

                        if ( $DB->get_num_rows() >= 1 ) {

                                $std->Error(array('MSG' => 'only_one_article', 'INIT' => '1'));
                                exit();
                        }
                }

                //----------------------------------------------
                // do we have pre-moderation?
                // ibf_cms_uploads_cat [moderate] IN
                // 0 - don't moderate
                // 1 - always moderate
                // 2 - moderate edited
                //----------------------------------------------

                $approved = '';

                if ( $NAV->cats[$cat_id]['moderate'] != '1' ) {

                        $approved = $date;
                }

                //------------------------------------------
                // try to move directory data
                //------------------------------------------

                //----------------------------------------------
                // do we have article with the same ID?
                //----------------------------------------------

                $DB->query(" SELECT u.id,l.* FROM ibf_cms_uploads AS u

                             LEFT JOIN ibf_cms_uploads_cat_links AS l

                             ON u.id = l.base

                             WHERE article_id = '{$dbres['article_id']}'

                             AND l.refs = {$mcat_id}

                           ");

                if ( $DB->get_num_rows() >= 1 ) {

                     $std->Error(array('MSG' => 'article_exists', 'INIT' => '1'));
                     exit();
                }

                //----------------------------------------------
                // make path info
                //----------------------------------------------

                $old_path = $NAV->build_path($cat_id) . "/" . $dbres['article_id'];
                $new_path = $NAV->build_path($mcat_id) . "/" . $dbres['article_id'];

                //----------------------------------------------
                // attemt to copy to a new location
                //----------------------------------------------

                if ( $MISC->copy_dir($old_path, $new_path) == false ) {
                        $std->Error(array('MSG' => 'error_create_upload_dir', 'INIT' => '1'));
                        exit();
                }

                //----------------------------------------------
                // attemt to delete old files
                //----------------------------------------------

                if ( $MISC->rm_dir($old_path) === false ) {

                     $std->Error(array('MSG' => 'error_create_upload_dir', 'INIT' => '1'));
                     exit();
                }

                //----------------------------------------------
                // update DB links
                //----------------------------------------------

                $DB->query(" UPDATE ibf_cms_uploads_cat_links SET

                                  refs    = '{$mcat_id}'

                             WHERE

                                  base = {$art_id}

                           ");

                //----------------------------------------------
                // update article data
                //----------------------------------------------

                $DB->query(" UPDATE ibf_cms_uploads SET

                                  approved    = '{$approved}'

                             WHERE

                                  id = {$art_id}

                           ");

                //--------------------------------------------------------------
                // articles watchdog - flush viewed results for all users
                //--------------------------------------------------------------

                $this->update_articles_watchdog( $art_id );

                //------------------------------------------------------
                // return to previous category
                //------------------------------------------------------

                $return_url = $NAV->build_path( $mcat_id, true ) . $dbres['article_id'] . "/";

                return header("Location: {$return_url}");
        }

        //----------------------------------------------------------------------
        //  Remove attached file or icon from article
        //----------------------------------------------------------------------

        function do_delete_attach() {
        global $ibforums, $std, $USR, $DB, $MISC, $NAV;

                //--------------------------------------------------------------
                // can this user deete file at all?
                //--------------------------------------------------------------

                if ( $USR->is_owner( $ibforums->member['id'] ) === false || $ibforums->member['g_art_del_attach'] == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // was the posted file and category ids an integer?
                //--------------------------------------------------------------

                $file_id = intval( $ibforums->input['fid'] );
                $cat_id  = $NAV->get_cat_id();
                $art_id  = intval( $ibforums->input['id'] );
                $is_icon = intval( $ibforums->input['is_icon'] );

                if ( $file_id == 0 || $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // do we have this file in the DB?
                //--------------------------------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_uploads_files WHERE id={$file_id} ");

                if ( !$DB->get_num_rows()  ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                $dbres = $DB->fetch_row();

                //--------------------------------------------------------------
                // make nice file path
                //--------------------------------------------------------------

                $path = $NAV->build_path( $cat_id ) . $dbres['name'];

                //--------------------------------------------------------------
                // try to delete file
                //--------------------------------------------------------------

                if ( $MISC->rm_attached_file( $path ) !== -1 ) {

                        //------------------------------------------------------
                        // if we have or don't have file - let's update DB
                        //------------------------------------------------------

                        $DB->query(" DELETE FROM ibf_cms_uploads_files WHERE id={$file_id}");

                        $DB->query(" DELETE FROM ibf_cms_uploads_file_links WHERE refs={$file_id} ");

                        //------------------------------------------------------
                        // if the file is icon - emove it
                        //------------------------------------------------------

                        if ( $is_icon != 0  ) {

                                $DB->query(" UPDATE ibf_cms_uploads SET icon_id='0' WHERE id='{$art_id}' ");
                        }
                } else {

                        //------------------------------------------------------
                        // we have file, but couldn't delete it (access?)
                        //------------------------------------------------------

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // continue editing article
                //--------------------------------------------------------------

                return header("Location: " . $ibforums->vars['dynamiclite'] . "&act=upload&cat=" . $cat_id . "&id=" . $art_id . "&ACTION=2");
        }

         //---------------------------------------------------------------------
         //  generating links 4 administrator/moderator (edit, delete, approve)
         //---------------------------------------------------------------------

         function make_admin_links($entry = null) {
         global $ibforums, $std, $DSITE, $USR, $NAV;

                //--------------------------------------------------------------
                // do not show these links on the top
                //--------------------------------------------------------------

                if ( $USR->is_owner($entry['user_id']) == true ) {

                        if (!$entry['approved'] || $entry['approved'] == 0) {


                                $entry['approve'] = $ibforums->lang['not_approved'];
                        } else {

                                $entry['approve'] = sprintf($ibforums->lang['approved'], $std->get_date($entry['approved']));
                        }

                        $entry['edit_link'] = $this->csite_html->tmpl_edit_link($entry);
                }

                if ( $USR->is_mod() == true ) {

                        //------------------------------------------------------
                        // url to post in skin files for mod_rewrite
                        //------------------------------------------------------

                        $entry['url'] = $NAV->build_path( $entry['refs'], true) . $entry['article_id'];

                        //------------------------------------------------------
                        // make links
                        //------------------------------------------------------

                        if ( $ibforums->member['g_art_approve'] == 1 ) {

                                $entry['approve'] = (!$entry['approved']) ? $this->csite_html->tmpl_approve_link_rw($entry) : $this->csite_html->tmpl_disable_link_rw($entry);
                        }

                        $entry['del_link']  = ( $ibforums->member['g_art_delete'] == 1 ) ?
                                                ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ? $this->csite_html->tmpl_delete_link($entry) : $this->csite_html->tmpl_delete_link_rw($entry) :
                                                '';

                        $entry['edit_link'] = ( $ibforums->member['g_art_edit']   == 1 ) ?
                                                ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ? $this->csite_html->tmpl_edit_link($entry) : $this->csite_html->tmpl_edit_link_rw($entry) :
                                                '';

                        $entry['move_link'] = ( $ibforums->member['g_art_move']   == 1 ) ?
                                                ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ? $this->csite_html->tmpl_move_link($entry) : $this->csite_html->tmpl_move_link_rw($entry) :
                                                '';
                }

                //--------------------------------------------------------------
                // favorites/subscriptions links
                //--------------------------------------------------------------

                if ( $ibforums->member['id'] > 0 ) {

                        $entry['subscribe_link'] = $this->csite_html->tmpl_subscribe_link($entry);
                        $entry['favorites_link'] = $this->csite_html->tmpl_favorites_link($entry);
                }

                //--------------------------------------------------------------
                // reputation links
                //--------------------------------------------------------------

                if ( $ibforums->member['g_art_allow_rep'] == 1 ) {

                        $entry['rep_article_link'] = $this->csite_html->tmpl_rep_article_link($entry);
                        $entry['rep_member_link'] = $this->csite_html->tmpl_rep_member_link($entry);
                }

                return $entry;
        }

        //----------------------------------------------------------------------
        // get article version/check requested version
        //----------------------------------------------------------------------

        function get_article_version( $retval = 'latest' ) {
        global $ibforums, $DB, $NAV;

                $ver_id = intval( $ibforums->input['version'] );
                $art_id = $NAV->get_art_id();

                //------------------------------------------------------
                // get the latest article version
                //------------------------------------------------------

                $DB->query( " SELECT MAX(version_id) AS latest, COUNT(version_id) AS count

                     FROM ibf_cms_uploads

                     WHERE id = {$art_id}

                     GROUP BY (id)

                   " );

                $version = $DB->fetch_row();

                //--------------------------------------------------------------
                // return all info about versions
                //--------------------------------------------------------------

                if ( $retval == 'all' ) {

                        return $version;
                }

                //--------------------------------------------------------------
                // which version to use
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $version['count'] ) {

                        $ver_id = $version['latest'];
                }

                if ( $retval == 'latest' ) {

                        return $version['latest'];
                }

                return $ver_id;
        }

        //----------------------------------------------------------------------
        // check & update incorrect versions sequence
        //----------------------------------------------------------------------

        function repair_versions_sequence( $art_id = 0 ) {
        global $DB;

                //--------------------------------------------------------------
                // get data about incorrect versions
                //--------------------------------------------------------------

                $DB->query( " SELECT version_id FROM ibf_cms_uploads WHERE id = {$art_id} ORDER BY version_id" );

                //--------------------------------------------------------------
                // make incorrect version ids array
                //--------------------------------------------------------------

                while ( $dbres = $DB->fetch_row() ) {

                        $versions['real_id'][] = $dbres['version_id'];
                }

                //--------------------------------------------------------------
                // check current article's version
                //--------------------------------------------------------------

                $DB->query( " SELECT current_version FROM ibf_cms_uploads_cat_links " );

                $dbres = $DB->fetch_row();

                $current_version = (int) $dbres['current_version'];

                //--------------------------------------------------------------
                // to remember from repaired
                //--------------------------------------------------------------

                $repaierd_version = (int) 0;

                //--------------------------------------------------------------
                // repair to correct sequence
                //--------------------------------------------------------------

                foreach ( $versions['real_id'] as $seq => $real_id ) {

                        $sql = (string) " UPDATE ibf_cms_uploads SET version_id = {$seq}+1 WHERE id = {$art_id} AND version_id = {$real_id} ";

                        $DB->query( $sql );

                        //------------------------------------------------------

                        $repaired_version = ($seq+1);
                }

                //--------------------------------------------------------------
                // rpair current article version
                //--------------------------------------------------------------

                if ( $current_version > $repaired_version ) {
                	$DB->query( " UPDATE ibf_cms_uploads_cat_links SET current_version={$repaired_version} WHERE base={$art_id} " );
                }



                return true;
        }

        //----------------------------------------------------------------------
        // clear wathcdog article information
        //----------------------------------------------------------------------

        function update_articles_watchdog( $art_id = 0, $action = 'clear' ) {
        global $ibforums, $DB, $FAV;

                //--------------------------------------------------------------
                // don't update info for guests
                //--------------------------------------------------------------

                if ( $ibforums->member['id'] != 0 ) {

                        if ( $action == 'clear' ) {

                                //----------------------------------------------
                                // if article updated - clear data
                                //----------------------------------------------

                                $DB->query(" DELETE FROM ibf_cms_articles_watchdog WHERE aid = {$art_id} ");

                                //----------------------------------------------
                                // user's subscriptions
                                //----------------------------------------------

                                //----------------------------------------------
                                // send subscription notifications
                                //----------------------------------------------

                                $this->notify_subscribed( $art_id );

                        } else if ( $action = 'update' ) {

                                //----------------------------------------------
                                // add viewed articles to watchdog
                                //----------------------------------------------

                                $DB->query(" SELECT id FROM ibf_cms_articles_watchdog where aid = {$art_id} and mid = {$ibforums->member['id']} ");

                                if ( $DB->get_num_rows() < 1 ) {

                                        $DB->query(" INSERT INTO ibf_cms_articles_watchdog (aid,mid) VALUES ({$art_id}, {$ibforums->member['id']}) ");
                                }
                        }
                }

                return true;
        }

        //----------------------------------------------------------------------
        // checks if user subscribed to the article/category
        //----------------------------------------------------------------------

        function notify_subscribed( $art_id = 0, $cat_id = 0 ) {
        global $ibforums, $std, $DB;

                //--------------------------------------------------------------
                // check subscription to the article
                //--------------------------------------------------------------

                if ( $art_id != 0 && $ibforums->member['id'] > 0 ) {

                        $DB->query( "SELECT * FROM ibf_cms_subscriptions

                                     WHERE article_id = {$art_id}

                                     AND member_id = {$ibforums->member['id']}

                                     AND type = 'subscribe'

                                   " );

                        if ( $DB->get_num_rows() < 1 ) {

                                return false;
                        }

                        while ( $dbres = $DB->fetch_row() ) {

                                //----------------------------------------------
                                // subscription to articles
                                //----------------------------------------------

                                $article_link = $this->csite_html->tmpl_article_link( array(
                                                                                                       'name' => $dbres['name'],
                                                                                                       'id'   => $dbres['article_id'],
                                                                                                       'version'  => $dbres['article_version'],
                                                                                                       'refs' => $dbres['category_id'],
                                                                                                     )
                                                                                             );

                                //----------------------------------------------
                                // compile and insert DB string
                                //----------------------------------------------

                                $article_link = $this->parser->convert(
                                                                 array( TEXT     => $article_link,
                                                                        SMILIES  => $ibforums->input['enableemo'],
                                                                        CODE     => $this->forum['use_ibc'],
                                                                        HTML     => $this->forum['use_html'],
                                                                        MOD_FLAG => $modflag,
                                                              ),
                                                                $this->forum['id'] );



                                $std->sendpm( $dbres['member_id'], "The article {$article_link} has been changed!", "D-Site Subscriptions" );
                        }
                }

                return true;
        }

}


