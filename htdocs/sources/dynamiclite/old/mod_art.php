<?php

/*
+---------------------------------------------------------------------------
|   D-Site Article working module
|   ========================================
|   Copyright (c) 2004 - 2005 Anton
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

        //----------------------------------------------------------------------
        //  module initialization
        //----------------------------------------------------------------------

        function mod_art() {
        global $ibforums, $std;

                //------------------------------------------
                // load html skin templates
                //------------------------------------------

                $this->csite_html = $std->load_template('skin_csite_mod_art');

                //------------------------------------------
                // load the parser
                //------------------------------------------

                require ROOT_PATH."sources/lib/post_parser.php";
                $this->parser = new post_parser();


                //------------------------------------------
                // are we uploading or viewing article
                //------------------------------------------

                if ( isset($ibforums->input['act']) && $ibforums->input['act'] == 'upload' && $ibforums->input['act'] != 'comments' ) {

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

                                default  :
                                            $this->result = $this->show_upload();
                                            break;
                        }

                } else if ( $ibforums->input['act'] == 'comments' ) {

                        //------------------------------------
                        // are we working with comments?
                        //------------------------------------

                        require "mod_art_comments.php";
                        $COMMENTS = new comments(&$this);

                        $this->result = $COMMENTS->result;


                } else {

                        if ( isset($ibforums->input['id']) ) {

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

                 $cat_id = intval($ibforums->input['cat']);

                 $limit = $ibforums->vars['csite_discuss_max'] ? $ibforums->vars['csite_discuss_max'] : 5;
                 $current_page = intval($ibforums->input['pages']);

                 //--------------------------------------------
                 // are you guest? allowed to show articles?
                 //--------------------------------------------

                 if ( $cat_id != 0 && $NAV->cats[$cat_id]['visible'] == 0 && $USR->is_mod() === false ) {

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

                         $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links WHERE refs = {$cat_id} LIMIT 0,1 ");

                         $dbres = $DB->fetch_row();

                         //--------------------------------------------
                         //  if we have at list one artile, show it
                         //--------------------------------------------

                         if ( $dbres ) {

                                 return $this->show_article( $dbres['base'] );
                         }
                 }

                 //--------------------------------------------
                 // building a list of articles
                 //--------------------------------------------

                 $order = "approved DESC";

                 //--------------------------------------------
                 // filter by ibf_cms_uploads [approved]
                 //--------------------------------------------

                 if ( $USR->is_mod() === false ) {

                         $approved[0] = "WHERE ( u.user_id = {$ibforums->member['id']} OR u.approved != 0 )";
                         $approved[1] = "AND  ( u.approved != 0 OR u.user_id = {$ibforums->member['id']} )";

                         $order = "submit_date DESC";
                 }

                 if ( $cat_id == 0 ) {

                         //--------------------------------------------
                         //  we are on the main page, show first n
                         //  articles
                         //--------------------------------------------

                         $DB->query(" SELECT l.*, u.* FROM ibf_cms_uploads_cat_links AS l

                                      LEFT JOIN ibf_cms_uploads AS u

                                      ON l.base = u.id

                                      {$approved[0]}

                                      ORDER BY u.{$order}

                                      LIMIT {$current_page},{$limit}

                                    ");

                         while ($dbres = $DB->fetch_row()) {

                                 if ( $NAV->cats[$dbres['refs']]['visible'] == 0 && $USR->is_mod() === true ) {

                                         $articles[] = $dbres;

                                 } else if ( $NAV->cats[$dbres['refs']]['visible'] == 1 ) {

                                         $articles[] = $dbres;
                                 }
                         }

                 } else {

                         //--------------------------------------------
                         // get all the articles in selected cat
                         //--------------------------------------------

                         $DB->query(" SELECT l.*, u.* FROM ibf_cms_uploads_cat_links AS l

                                      LEFT JOIN ibf_cms_uploads AS u

                                      ON l.base = u.id

                                      WHERE l.refs = {$cat_id}

                                      {$approved[1]}

                                    ");

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

                                                         $entry['sub_cats'] .= $this->csite_html->tmpl_sub_cat_row($this->csite_html->tmpl_cat_link($sub_cat));
                                                 }
                                         } else if ( $USR->is_mod($sub_cat['id']) === true ) {

                                                 //--------------------------------------
                                                 // redirect links
                                                 //--------------------------------------

                                                 if ( $sub_cat['redirect_url'] != '') {

                                                         $sub_cat['name'] = $sub_cat['name'] . " (!)";

                                                         $entry['sub_cats'] .= $this->csite_html->tmpl_sub_cat_row_redir($sub_cat);

                                                 } else {

                                                         $sub_cat['name'] = $sub_cat['name'] . " (!)";

                                                         $entry['sub_cats'] .= $this->csite_html->tmpl_sub_cat_row($this->csite_html->tmpl_cat_link($sub_cat));
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
                                                              $this->csite_html->tmpl_cat_link($NAV->cats[$article['refs']]),
                                                              $std->make_profile_link($article['author_name'], $article['user_id']),
                                                              $std->get_date($article['submit_date'], 'LONG'));

                         $article['article_link'] = $article['static_url'] ? $this->csite_html->tmpl_static_article_link($article) : $this->csite_html->tmpl_article_link($article);

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
         //  viewing the selected article
         //---------------------------------------------------------------------

        function show_article( $art_id = 0 ) {
        global $ibforums, $DB, $std, $NAV, $MISC, $USR;

                $art_id = ( $art_id == 0 ) ? intval($ibforums->input['id']) : $art_id;
                $cat_id = intval($ibforums->input['cat']);

                $article = "";
                $files   = array();

                //------------------------------------------
                // did we've got right ids?
                //------------------------------------------

                if (  $art_id == 0 || $cat_id == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                //------------------------------------------
                // are we in the hidden category?
                //------------------------------------------

                if ( $USR->is_mod() === false && $NAV->cats[$cat_id]['visible'] == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }


                //------------------------------------------
                // load DB string
                //------------------------------------------

                $DB->query(" SELECT u.*,l.* FROM ibf_cms_uploads AS u

                             LEFT JOIN ibf_cms_uploads_cat_links AS l

                             ON l.base = u.id

                             WHERE u.id = {$art_id}

                           ");

                $article = $DB->fetch_row();

                if ( !$article ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

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

                $DB->query(" SELECT l.*, f.* FROM ibf_cms_uploads_file_links AS l

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
                                                                                    $std->get_date($article['submit_date'], 'LONG')),
                                                                            null,
                                                                            null));


                //------------------------------------------
                // write statistics
                //------------------------------------------

                $DB->query(" UPDATE ibf_cms_uploads

                             SET hits=hits+1

                             WHERE id = {$art_id}

                           ");

                //------------------------------------------
                // show comments
                //------------------------------------------

                if ( $NAV->cats[$article['refs']]['allow_comments'] != 0 ) {

                        require ROOT_PATH . "sources/dynamiclite/mod_art_comments.php";

                        $ibforums->input['id'] = $article['id'];

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

               $cat_id = intval( $ibforums->input['cat'] );

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

               if ( $ibforums->vars['csite_max_upload_files'] ) {

                       for ( $i = 1; $i <= $ibforums->vars['csite_max_upload_files']; $i++ ) {

                               $entry['files'] .= $this->csite_html->tmpl_upload_file($i);
                       }
               }

               //----------------------------------------------
               //  make article icon to attach
               //----------------------------------------------

               if ( 1 ) {

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

               if ( $NAV->cats[$cat_id]['show_smiles'] != 0 ) {

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

        function do_upload() {
        global $ibforums, $DB, $CAT, $NAV, $std, $MISC, $USR;

                $entry          = array();
                $uploaded_files = "";
                $uploaded_icon  = "";

                $cat_id = intval($ibforums->input['cat']);

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

                if ( $ibforums->member['id'] < 1 || ( $NAV->cats[$cat_id]['allow_posts'] == 0 && $USR->is_mod() === false ) ) {

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

                if ( $CAT->create_directory($path) === false ) {

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

                if ( $uploaded_files ) {

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

                if ($uploaded_icon['id'] != 0) {

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

                if ($ibforums->input['attach_file']) {

                        return header("Location: " . $ibforums->vars['dynamiclite'] . "act=upload&id=" . $inserted_id . "&cat=" . $cat_id . "&ACTION=2");

                } else {

                        return header("Location: " . $ibforums->vars['dynamiclite'] . "cat=" . $cat_id);
                }

        }


        //----------------------------------------------------------------------
        //    displaying article edit form
        //----------------------------------------------------------------------

        function show_edit() {
        global $ibforums, $NAV, $DB, $std, $DSITE, $USR;

               $cat_id = intval( $ibforums->input['cat'] );
               $art_id = intval( $ibforums->input['id'] );

               $entry = array();

               //------------------------------------------
               // have we selected category, article?
               // does this categoy exist?
               //------------------------------------------

               if ( $cat_id == 0 || !$NAV->cats[$cat_id] || $art_id == 0 ) {

                       $std->Error(array('MSG' => 'cms_category_not_selected', 'INIT' => '1'));
                       exit();
               }

               //----------------------------------------------
               //  does the article exists?
               //----------------------------------------------

               $DB->query(" SELECT * FROM ibf_cms_uploads WHERE id = {$art_id} ");

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

                if ( $old_uploaded_files ) {

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

                for ($i = 0; $i < $ibforums->vars['csite_max_upload_files']; $i++) {

                       if ( !$uploaded_files[$i] ) {

                               $uploaded_files[] = $this->csite_html->tmpl_upload_file($i+1);
                       }
                }

                //----------------------------------------------
                // make all files as html
                //----------------------------------------------

                if ( is_array($uploaded_files) ) {

                       $entry['files'] = implode("\n", $uploaded_files);
                }

               //----------------------------------------------
               //  make article icon to attach or show attached
               //----------------------------------------------

               if ( $entry['icon_id'] != 0 ) {

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

               $entry['icon'] = ($uploaded_icon) ? $uploaded_icon : $this->csite_html->tmpl_show_upload_icon();


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
               //  make ACTION = 3 - $this->do_edit()
               //----------------------------------------------

               if ( 1 ) {

                       $entry['ACTION'] = '3';
               }

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

               if ( $NAV->cats[$cat_id]['show_smiles'] != 0 ) {

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

                $cat_id = intval($ibforums->input['cat']);
                $art_id = intval($ibforums->input['id']);

                //----------------------------------------------
                //  does the article exists?
                //----------------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_uploads WHERE id = {$art_id} ");

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

                if ( $ibforums->input['article_id'] != $entry['article_id'] ) {

                        $old_path = $NAV->build_path($cat_id) . "/" . $entry['article_id'];

                        //----------------------------------------------
                        // do we have article with the same ID?
                        //----------------------------------------------

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


                $DB->query(" UPDATE ibf_cms_uploads SET

                                  name        = '{$ibforums->input['name']}',
                                  short_desc  = '{$ibforums->input['short_desc']}',
                                  article     = '{$convert}',
                                  article_id  = '{$ibforums->input['article_id']}',
                                  submit_date = '{$date}',
                                  approved    = '{$approved}'

                             WHERE

                                  id = {$art_id}

                           ");



                //----------------------------------------------
                // if files were attached, let's upload them
                // and link to the article
                // TODO: if we already have uploaded files, don,t save them
                //----------------------------------------------

                unset($uploaded_files);
                $uploaded_files = $CAT->upload_files($new_path);

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
                $uploaded_icon = $CAT->upload_icon($new_path);

                if ($uploaded_icon['id'] != 0) {

                        $DB->query(" UPDATE ibf_cms_uploads SET

                                            icon_id = '{$uploaded_icon['id']}'

                                     WHERE
                                            id = {$art_id}
                                   ");
                }

                //----------------------------------------------
                // if button "Attach File" was pressed return
                // editing of uploaded article, otherwise
                // redirect ot current category
                // TODO: all redirects through $std->boink_it();
                //----------------------------------------------

                if ($ibforums->input['attach_file']) {

                        return header("Location: " . $ibforums->vars['dynamiclite'] . "act=upload&id=" . $art_id . "&cat=" . $cat_id . "&ACTION=2");

                } else {

                        return header("Location: " . $ibforums->vars['dynamiclite'] . "cat=" . $cat_id);
                }

        }

        //----------------------------------------------------------------------
        //  show deletion confirmation form
        //----------------------------------------------------------------------

        function show_delete() {
        global $ibforums, $std, $DB, $USR;

                $html = "";

                $art_id = intval($ibforums->input['id']);
                $cat_id = intval($ibforums->input['cat']);

                //--------------------------------------
                // check some posted info
                //--------------------------------------

                if ( $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------
                // does the article exists?
                //--------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_uploads

                             WHERE id = {$art_id}

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


                $art_id = intval($ibforums->input['id']);
                $cat_id = intval($ibforums->input['cat']);

                if ( $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                $right_referer = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?act=upload&cat=' . $cat_id . '&id=' . $art_id . '&ACTION=4';

                if ( $_SERVER['HTTP_REFERER'] != $right_referer ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();

                }

                $DB->query(" SELECT * FROM ibf_cms_uploads

                             WHERE id = {$art_id}

                           ");

                $dbres = $DB->fetch_row();

                if ( !$dbres ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------

                if ( $USR->is_owner($dbres['user_id']) === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

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

                return header("Location: " . $ibforums->vars['dynamiclite'] . "cat=" . $cat_id);
        }

        //----------------------------------------------------------------------
        //  article's pre-moderation - if we show it to all users or not
        //----------------------------------------------------------------------

        function do_approve($approve = true) {
        global $ibforums, $DB, $std, $USR;

                $art_id = intval($ibforums->input['id']);
                $cat_id = intval($ibforums->input['cat']);

                //-------------------------------------------------
                // only moderator can do this
                //-------------------------------------------------

                if ( $USR->is_mod($cat_id) === false ) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
                }

                //-------------------------------------------------
                // does article exists?
                //-------------------------------------------------

                if ( $cat_id == 0 || $cat_id == 0 ) {

                        $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                        exit();
                }

                $current_time = ($approve) ? time() : null;
                $increment = ($approve == true) ? "+" : "-";

                $DB->query("UPDATE ibf_cms_uploads

                            SET approved = '{$current_time}'

                            WHERE id = {$art_id}
                            ");

                $query_increment = " UPDATE

                                           ibf_cms_uploads_cat

                                     SET
                                           num=num{$increment}1

                                     WHERE
                                          id = {$art_id}
                                   ";

                $DB->query($query_increment);


                return header("Location: " . $ibforums->vars['dynamiclite'] . "cat=" . $cat_id . "&id=" . $art_id);
        }

        //----------------------------------------------------------------------
        //  show move article form
        //----------------------------------------------------------------------

        function show_move() {
        global $ibforums, $std, $USR, $NAV;

                $html = "";

                $art_id = intval($ibforums->input['id']);
                $cat_id = intval($ibforums->input['cat']);

                //--------------------------------------
                // check some posted info
                //--------------------------------------

                if ( $cat_id == 0 || $art_id == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
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
                // the html form
                //--------------------------------------

                $dbres['title'] = $ibforums->lang['move_article'];

                $dbres['ACTION'] = '9';

                $dbres['cat_list'] = $NAV->build_cat_list_select();

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

                //-------------------------------------
                // do we have all needed data?
                //-------------------------------------

                $cat_id      = intval($ibforums->input['cat']);
                $mcat_id = intval($ibforums->input['move_cat_id']);
                $art_id      = intval($ibforums->input['id']);

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

                $right_referer = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?act=upload&cat=' . $cat_id . '&id=' . $art_id . '&ACTION=8';

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


                //----------------------------------------------
                // finished!
                //----------------------------------------------

                return header("Location: " . $ibforums->vars['dynamiclite'] . 'cat=' . $mcat_id);
        }

         //---------------------------------------------------------------------
         //  generating links 4 administrator/moderator (edit, delete, approve)
         //---------------------------------------------------------------------

         function make_admin_links($entry = null) {
         global $ibforums, $std, $DSITE, $USR;

                if ( $USR->is_owner($entry['user_id']) === true ) {

                        if (!$entry['approved'] || $entry['approved'] == 0) {


                                $entry['approve'] = $ibforums->lang['not_approved'];
                        } else {

                                $entry['approve'] = sprintf($ibforums->lang['approved'], $std->get_date($entry['approved'], 'LONG'));
                        }

                        $entry['edit_link'] = $this->csite_html->tmpl_edit_link($entry);
                }

                if ( $USR->is_mod() === true ) {

                        $entry['approve'] = (!$entry['approved']) ? $this->csite_html->tmpl_approve_link($entry) : $this->csite_html->tmpl_disable_link($entry);
                        $entry['del_link'] = $this->csite_html->tmpl_delete_link($entry);
                        $entry['edit_link'] = $this->csite_html->tmpl_edit_link($entry);
                        $entry['move_link'] = $this->csite_html->tmpl_move_link($entry);
                }

                return $entry;
        }
}


?>