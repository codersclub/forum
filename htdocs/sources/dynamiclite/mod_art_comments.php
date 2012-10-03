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
|   Site articles functions exstention module
|   Working with user comments
|
*---------------------------------------------------------------------------
*/

class mod_art_comments extends mod_art {

        var $result = '';

        //----------------------------------------------------------------------
        //  module initialization
        //----------------------------------------------------------------------

        function mod_art_comments( $PARENT ) {
        global $ibforums, $std;

                //--------------------------------------------
                //  viewing comments
                //  ACTION in:
                //  *  - show_comments()
                //  1  - show_upload()
                //  2  - do_upload()
                //  3  - show_edit()
                //  4  - do_edit()
                //  5  - do_delete()
                //--------------------------------------------

                switch ( $ibforums->input['ACTION'] ) {

                        case '1' :
                                   $this->result = $this->show_upload( &$PARENT );
                                   break;

                        case '2' :
                                   $this->result = $this->do_upload( &$PARENT );
                                   break;

                        case '3' :
                                   $this->result = $this->show_edit( &$PARENT );
                                   break;

                        case '4' :
                                   $this->result = $this->do_edit( &$PARENT );
                                   break;

                        case '5' :
                                   $this->result = $this->do_delete( &$PARENT );
                                   break;

                        default :
                                   $this->result = $this->show_comments( &$PARENT );
                                   break;

                }

                return $this->result;
        }

        //----------------------------------------------------------------------
        //  viewing the list of uploaded comments
        //----------------------------------------------------------------------

        function show_comments( $PARENT ) {
        global $ibforums, $DB, $std, $USR, $NAV;

                $html   = '';
                $art_id = $NAV->get_art_id();
                $cat_id = $NAV->get_cat_id();
                $sh_comments = intval($ibforums->input['comments']);

                //--------------------------------------
                // are comments allowed here?
                //--------------------------------------

                if ( $NAV->cats[$cat_id]['allow_comments'] == 0 || $ibforums->member['g_art_view_comments'] != 1 ) {

                        return null;
                }


                //--------------------------------------
                // have we got a rigth data?
                //--------------------------------------

                if ( $art_id == 0 || $cat_id == 0 ) {

                        return $this->show_upload( &$PARENT );
                }

                //--------------------------------------------------------------

                $article_url = $NAV->build_path($cat_id, true) . $PARENT->current_article_id . "/index.html";

                //------------------------------------------------------

                $version_link = ( $ibforums->input['version'] != 0 ) ? "&version=" . $ibforums->input['version'] : "";

                //--------------------------------------------------------------
                // do we use quick comments?
                // show only count of comments and link to add them
                //--------------------------------------------------------------

                if ( $ibforums->vars['csite_use_quick_comments'] == 1 && $sh_comments == 0 ) {

                        $DB->query( " SELECT COUNT(*) AS comments_count FROM ibf_cms_comments_links WHERE base = {$art_id} " );

                        $dbres = $DB->fetch_row();

                        $html .= ( $ibforums->vars['dsite_use_mod_rewrite'] != 1 ) ?
                                   $PARENT->csite_html->tmpl_quick_comments($dbres) :
                                   $PARENT->csite_html->tmpl_quick_comments_rw( $dbres, $article_url, $version_link );

                        return $html;
                }


                //--------------------------------------
                // load comments data array
                //--------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_comments_links WHERE base = {$art_id} ");

                //--------------------------------------
                // do ew have at least one?
                //--------------------------------------

                if ( $DB->get_num_rows() >= 1 ) {

                        while ( $dbres = $DB->fetch_row() ) {

                                $comment_ids[] = $dbres['refs'];
                        }

                        $comment_ids = implode(",", $comment_ids);

                        //--------------------------------------
                        // load comments base and make html
                        //--------------------------------------

                        $count = 1;

                        $DB->query(" SELECT * FROM ibf_cms_comments WHERE id IN ({$comment_ids})");

                        while ( $dbres = $DB->fetch_row() ) {


                                //------------------------------------------
                                // unparse DB text
                                //------------------------------------------

                                $dbres['comment'] = $PARENT->parser->prepare_code_tabs($dbres['comment']);

                                $dbres['comment'] = $PARENT->parser->prepare(
                                                                             array(
                                                                                   'TEXT' => $dbres['comment'],
                                                                                   'HTML' => 1,
                                                                                   'SMILIES' => 1,
                                                                                   'CODE' => 1,
                                                                                   'SIGNATURE' => 0
                                                                                   )
                                                                             );


                                $dbres['title'] = sprintf($ibforums->lang['comments_title'], $count++, $std->get_date($dbres['submit_date']), $std->make_profile_link($dbres['user_name'], $dbres['user_id']));

                                //------------------------------------------
                                // make admin && owner links
                                //------------------------------------------

                                if ( ($ibforums->member['id'] == $dbres['user_id'] || $USR->is_mod($cat_id)) && $ibforums->member['id'] >= 1 ) {

                                        $edit_url = $NAV->build_path($cat_id, true) . $PARENT->current_article_id . "/edit_comment.html";
                                        $delete_url = $NAV->build_path($cat_id, true) . $PARENT->current_article_id . "/delete_comment.html";

                                        $dbres['edit_link'] = ($ibforums->member['g_art_edit_comment'] == 1) ? $PARENT->csite_html->tmpl_comment_edit_link($edit_url, $dbres['id']) : '';
                                        $dbres['dele_link'] = ($ibforums->member['g_art_delete_comments'] == 1) ? $PARENT->csite_html->tmpl_comment_dele_link($delete_url, $dbres['id']) : '';
                                }


                                $html .= $PARENT->csite_html->tmpl_comments_row($dbres);
                        }
                } else {

                        return $this->show_upload( &$PARENT );
                }

                //-------------------------------------
                // show comments upload form
                //-------------------------------------

                $html = ($html) ? $PARENT->csite_html->tmpl_comments($html) : '';

                return $html . $this->show_upload( &$PARENT );
        }


        //----------------------------------------------------------------------
        // view add comment form
        //----------------------------------------------------------------------

        function show_upload( $PARENT ) {
        global $ibforums, $NAV, $std;

                $html = '';

                if ( $ibforums->member['g_art_add_comment'] != 1 ) {

                       return null;
                }

                //-------------------------------------
                // fill html forms
                //-------------------------------------

                $entry['ACTION'] = '2';
                $entry['cat']    = $NAV->get_cat_id();
                $entry['id']     = $NAV->get_art_id();
                $entry['title']  = $ibforums->lang['add_comment'];
                $entry['url']    = $NAV->build_path($NAV->get_cat_id(), true) . $NAV->get_art_id( null, true ) . "/add_comment.html";

                //-------------------------------------
                // make html
                //-------------------------------------


                $html = $PARENT->csite_html->tmpl_comment_upload_form($entry);


                return $html;
        }

        //----------------------------------------------------------------------
        // upload added comments to the server
        //----------------------------------------------------------------------

        function do_upload( $PARENT ) {
        global $ibforums, $std, $DB, $NAV;

                $cat_id = $NAV->get_cat_id();
                $art_id = $NAV->get_art_id();

                //--------------------------------------
                // are comments allowed here?
                //--------------------------------------

                if ( $NAV->cats[$cat_id]['allow_comments'] == 0 || $ibforums->member['g_art_add_comment'] != 1 ) {

                        $std->Error(array('MSG' => 'comments_not_allowed', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------------
                // did we've got a correct data?
                //-------------------------------------------

                if ( $cat_id == 0 || $art_id == 0 || empty($ibforums->input['Post'])) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();

                }

                //-------------------------------------------
                // parse post data
                //-------------------------------------------

                $ibforums->input['Post'] =  $PARENT->parser->unconvert($ibforums->input['Post'], 0, 1);

                //-------------------------------------------
                // write comment data to DB
                //-------------------------------------------

                $DB->query(" INSERT INTO ibf_cms_comments

                               (
                                    user_id,
                                    user_name,
                                    comment,
                                    submit_date

                               )

                              VALUES

                                (
                                    '{$ibforums->member['id']}',
                                    '{$ibforums->member['name']}',
                                    '{$ibforums->input['Post']}',
                                    '" . time() . "'
                                )

                                   ");

                $inserted_id = $DB->get_insert_id();

                //-------------------------------------------
                // make DB links
                //-------------------------------------------

                $DB->query(" INSERT INTO ibf_cms_comments_links

                               (
                                    base,
                                    refs

                               )

                              VALUES

                                (
                                    '{$art_id}',
                                    '{$inserted_id}'

                                )

                                   ");

                $redirect_url = $NAV->build_path($cat_id, true) . $NAV->get_art_id( null, true ) . "/index.html?comments=1#comments";

                return header("Location: {$redirect_url}");
        }

        //----------------------------------------------------------------------
        // view edit comment form
        //----------------------------------------------------------------------

        function show_edit( $PARENT ) {
        global $ibforums, $std, $USR, $DB, $NAV;

                $html = '';

                if ( $ibforums->member['g_art_edit_comment'] == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // get some interesting data
                //-------------------------------------

                $entry['cid'] = intval($ibforums->input['id']);
                $entry['cat'] = $NAV->get_cat_id();
                $entry['id']  = $NAV->get_art_id();
                $entry['url']    = $NAV->build_path($NAV->get_cat_id(), true) . $NAV->get_art_id( null, true ) . "/do_edit_comment.html";

                //-------------------------------------------
                // did we've got a correct data?
                //-------------------------------------------

                if ( $entry['cid'] == 0 || $entry['cat'] == 0 || $entry['id'] == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // do we have this comment?
                //-------------------------------------

                $DB->query("SELECT * FROM ibf_cms_comments WHERE id = {$entry['cid']} ");

                $dbres = $DB->fetch_row();

                //-------------------------------------
                // can we edit it?
                //-------------------------------------

                if ( $ibforums->member['id'] != $dbres['user_id'] && !$USR->is_mod($entry['cat']) ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // fill html input
                //-------------------------------------

                $entry['comment'] = $dbres['comment'];
                $entry['title']  = sprintf($ibforums->lang['edit_comment'], $dbres['user_name'], $std->get_date($dbres['submit_date']));
                $entry['ACTION'] = '4';

                //-------------------------------------
                // make html
                //-------------------------------------

                $html = $PARENT->csite_html->tmpl_comment_upload_form($entry);

                return $html;
        }

        //----------------------------------------------------------------------
        // upload edited comments to the server
        //----------------------------------------------------------------------

        function do_edit( $PARENT ) {
        global $ibforums, $std, $DB, $USR, $NAV;

                $cat_id = $NAV->get_cat_id();
                $art_id = $NAV->get_art_id();
                $cid = intval($ibforums->input['cid']);

                //--------------------------------------
                // are comments allowed here?
                //--------------------------------------

                if ( $NAV->cats[$cat_id]['allow_comments'] == 0 || $ibforums->member['g_art_edit_comment'] != 1 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------------
                // did we've got a correct data?
                //-------------------------------------------

                if ( $cat_id == 0 || $art_id == 0 || $cid == 0 || empty($ibforums->input['Post'])) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();

                }

                //-------------------------------------
                // do we have this comment?
                //-------------------------------------

                $DB->query("SELECT * FROM ibf_cms_comments WHERE id = {$cid} ");

                $dbres = $DB->fetch_row();

                //-------------------------------------
                // can we edit it?
                //-------------------------------------

                if ( $ibforums->member['id'] != $dbres['user_id'] && !$USR->is_mod($entry['cat']) ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }


                //-------------------------------------------
                // parse post data
                //-------------------------------------------

                $ibforums->input['Post'] =  $PARENT->parser->unconvert($ibforums->input['Post'], 0, 1);

                //-------------------------------------------
                // write comment data to DB
                //-------------------------------------------

                $DB->query(" UPDATE ibf_cms_comments SET

                                  comment     = '{$ibforums->input['Post']}'

                             WHERE

                                  id = {$cid}

                           ");

                $redirect_url = $NAV->build_path($cat_id, true) . $NAV->get_art_id( null, true ) . "/index.html?comments=1#comments";

                return header("Location: {$redirect_url}");
        }

        //----------------------------------------------------------------------
        // upload edited comments to the server
        //----------------------------------------------------------------------

        function do_delete( $PARENT ) {
        global $ibforums, $std, $DB, $USR, $NAV;

                $cat_id = $NAV->get_cat_id();
                $art_id = $NAV->get_art_id();
                $cid    = intval($ibforums->input['id']);

                //--------------------------------------
                // are comments allowed here?
                //--------------------------------------

                if ( $NAV->cats[$cat_id]['allow_comments'] == 0 || $ibforums->member['g_art_delete_comments'] != 1 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }


                //-------------------------------------------
                // did we've got a correct data?
                //-------------------------------------------

                if ( $cat_id == 0 || $art_id == 0 || $cid == 0 ) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();

                }

                //-------------------------------------
                // do we have this comment?
                //-------------------------------------

                $DB->query("SELECT * FROM ibf_cms_comments WHERE id = {$cid} ");

                $dbres = $DB->fetch_row();

                //-------------------------------------
                // can we edit it?
                //-------------------------------------

                if ( $ibforums->member['id'] != $dbres['user_id'] && !$USR->is_mod($entry['cat']) ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //-------------------------------------
                // delete data from DB
                //-------------------------------------

                $DB->query("DELETE FROM ibf_cms_comments WHERE id = {$cid}");

                $DB->query( " DELETE FROM ibf_cms_comments_links where refs = {$cid}" );

                $redirect_url = $NAV->build_path($cat_id, true) . $NAV->get_art_id( null, true ) . "/index.html?comments=1#comments";

                return header("Location: {$redirect_url}");
       }
}