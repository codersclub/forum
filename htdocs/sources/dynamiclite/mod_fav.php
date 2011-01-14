<?php

/*
+--------------------------------------------------------------------------
|   D-Site Favorites && Subscriptions functions module
|   ========================================
|   Copyright (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   Subscription functions
|
*---------------------------------------------------------------------------
*/

class mod_fav extends mod_art {

        var $html = '';
        var $parent;

        //----------------------------------------------------------------------
        // class constructor
        //----------------------------------------------------------------------

        function mod_fav( &$PARENT ) {
        global $ibforums, $std;

                //--------------------------------------------------------------
                // init variables
                //--------------------------------------------------------------

                $this->PARENT = &$PARENT;

                //--------------------------------------------------------------
                // load skin
                //--------------------------------------------------------------

                $this->html = $std->load_template('skin_csite_mod_fav');

                //--------------------------------------------------------------
                // favorites actions
                // --
                // 01 - show_subscriptions
                // 02 - do_subscribe
                //--------------------------------------------------------------

                switch ( intval( $ibforums->input['FAV'] ) ) {

                        case 1 :
                                  $PARENT->result = $this->show_subscriptions();
                                  return;

                        case 3 :
                                  $PARENT->result = $this->show_subscriptions('favorite');
                                  return;

                        case 4 :
                                  $PARENT->result = $this->delete_subscription();
                                  return;

                        case 5 :
                                  $PARENT->result = $this->do_subscribe('subscribe');
                                  return;

                        case 6 :
                                  $PARENT->result = $this->do_subscribe();
                                  return;

                        default :
                                  return;

                }
        }

        /*
        +----------------------------------------------------------------------+
        |                                                                      |
        |                       ARTICLE - SUBSCRIPTIONS                        |
        |                                                                      |
        +----------------------------------------------------------------------+
        */

        //----------------------------------------------------------------------
        // subscribe user to article/category
        //----------------------------------------------------------------------

        function do_subscribe( $type = 'favorite' ) {
        global $ibforums, $DB, $std, $NAV;

                $art_id = $NAV->get_art_id();
                $cat_id = $NAV->get_cat_id();
                $ver_id = intval( $ibforums->input['version'] );

                //--------------------------------------------------------------
                // guests cant't subscribe
                //--------------------------------------------------------------

                if ( $ibforums->member['id'] <= 0 ) {

                        $std->Error(array('MSG' => 'subscribe_no_guests', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // have we gog any data?
                //--------------------------------------------------------------

                if ( $cat_id == 0 && $art_id == 0 && $ver_id == 0 ) {

                        $std->Error(array('MSG' => 'subscribe_no_data', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // get the latest article version
                //--------------------------------------------------------------

                $versions = $this->PARENT->get_article_version( 'all' );


                //--------------------------------------------------------------
                // which version to display
                //--------------------------------------------------------------

                if ( $ver_id < 1 || $ver_id > $versions['count'] ) {

                        $ver_id = $versions['latest'];
                }


                //--------------------------------------------------------------
                // subscribe to the category
                //--------------------------------------------------------------

                if ( $cat_id != 0 && $art_id == 0 ) {

                     //---------------------------------------------------------
                     // check older subscriptions
                     //---------------------------------------------------------

                     $DB->query(" SELECT id FROM ibf_cms_subscriptions

                                  WHERE member_id = {$ibforums->member['id']}

                                  AND category_id = {$cat_id}

                                  AND type = '{$type}'

                                ");

                     //---------------------------------------------------------
                     // already subscribed
                     //---------------------------------------------------------

                     if ( $DB->get_num_rows() > 0 ) {

                          $std->Error(array('MSG' => $type . '_cat_exists', 'INIT' => '1'));
                          exit();
                     }

                     //---------------------------------------------------------
                     // make subscription
                     //---------------------------------------------------------

                     $DB->query(" INSERT INTO ibf_cms_subscriptions

                                  (category_id, member_id, type)

                                  VALUES

                                  ({$cat_id}, {$ibforums->member['id']}, '{$type}')

                               ");

                } else {

                        //------------------------------------------------------
                        // subscribe/add to favorite articles
                        //------------------------------------------------------

                        //------------------------------------------------------
                        // check older subscriptions
                        //------------------------------------------------------

                        $DB->query(" SELECT id FROM ibf_cms_subscriptions

                                     WHERE member_id = {$ibforums->member['id']}

                                     AND article_id = {$art_id}

                                     AND article_version = {$ver_id}

                                     AND type = '{$type}'

                                   ");

                     //---------------------------------------------------------
                     // already subscribed
                     //---------------------------------------------------------

                     if ( $DB->get_num_rows() > 0 ) {

                          $std->Error(array('MSG' => $type . '_art_exists', 'INIT' => '1'));
                          exit();
                     }

                     //---------------------------------------------------------
                     // make subscription
                     //---------------------------------------------------------

                     $DB->query(" INSERT INTO ibf_cms_subscriptions

                                  (article_id, article_version, category_id, member_id, type)

                                  VALUES

                                  ({$art_id}, {$ver_id}, {$cat_id}, {$ibforums->member['id']}, '{$type}')

                               ");
                }

                //---------------------------------------------------------
                // where to return
                //---------------------------------------------------------

                //$url = $NAV->build_path($cat_id, true) . $NAV->get_art_id( null, true ) . "/";

                $url = $ibforums->vars['csite_cms_url'] . "/";

                $return_fav_url = ( $type == "favorite" ) ? $url . "favorites.html" : $url . "subscriptions.html";

                //---------------------------------------------------------
                // show finish screen
                //---------------------------------------------------------

                return $this->html->tmpl_success_subscription( $ibforums->lang[$type . '_title'], $ibforums->lang[$type . '_art_success'], $return_fav_url, $ibforums->lang[$type . '_cat_return'] );

        }

        //----------------------------------------------------------------------
        // show users subscriptions
        //----------------------------------------------------------------------

        function show_subscriptions( $type = 'subscribe' ) {
        global $ibforums, $std, $DB, $NAV;

                //--------------------------------------------------------------
                // init counters
                //--------------------------------------------------------------

                $result = array();
                $active_art[$type] = (int) 0;
                $active_cat[$type] = (int) 0;

                //--------------------------------------------------------------
                // guests cant't subscribe
                //--------------------------------------------------------------

                if ( $ibforums->member['id'] <= 0 ) {

                        $std->Error(array('MSG' => 'subscribe_no_guests', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // search subscriptions
                //--------------------------------------------------------------

                $DB->query(" SELECT s.*, u.id AS art_id,u.name,u.version_id, u.article_id as rw_id

                	     FROM ibf_cms_subscriptions AS s

                             LEFT JOIN ibf_cms_uploads AS u

                             ON s.article_id = u.id

                             AND s.article_version = u.version_id

                             WHERE member_id = {$ibforums->member['id']}

                             AND type='{$type}'

                          ");

                //--------------------------------------------------------------
                // make headers
                //--------------------------------------------------------------

                $result['title'] = $ibforums->lang[$type . '_title'];

                $result['cat_' . $type] = $this->html->tmpl_subscriptions_cat_header();
                $result['art_' . $type] = $this->html->tmpl_subscriptions_art_header();

                //--------------------------------------------------------------
                // found subscriptions, show them
                //--------------------------------------------------------------

                while ( $dbres = $DB->fetch_row() ) {

                        //------------------------------------------------------
                        // subscription to categories
                        //------------------------------------------------------

                        if ( $dbres['category_id'] != 0 && $dbres['article_id'] == 0 ) {
                        		$url = $NAV->build_path( $dbres['category_id'], true );

                                $entry['cat_link'] = $this->html->tmpl_link( $url, $NAV->get_name($dbres['category_id']) );

                                $tmpl_name = "tmpl_" . $type . "_delete_link";
                                $entry['delete_link'] = $this->html->$tmpl_name( $dbres );

                                $result['cat_' . $type] .= $this->html->tmpl_subscriptions_cats_row( $entry );

                                //----------------------------------------------
                                // increment subscription count
                                //----------------------------------------------

                                $active_cat[$type]++;

                        } else {

                                //----------------------------------------------
                                // subscription to articles
                                //----------------------------------------------

                                $url = $NAV->build_path( $dbres['category_id'], true ) . $dbres['rw_id'] . "/index.html";

                                $entry['cat_link'] = $this->html->tmpl_link( $url, $dbres['name'] );

                                $tmpl_name = "tmpl_" . $type . "_delete_link";
                                $entry['delete_link'] = $this->html->$tmpl_name( $dbres );

                                $result['art_' . $type] .= $this->html->tmpl_subscriptions_cats_row( $entry );

                                //----------------------------------------------
                                // increment subscription count
                                //----------------------------------------------

                                $active_art[$type]++;
                        }
                }

                //--------------------------------------------------------------
                // if no subscriptions
                //--------------------------------------------------------------

                $result['cat_' . $type] .= ( $active_cat[$type] == 0 ) ? $this->html->tmpl_subscriptions_no_subscription() : '';
                $result['art_' . $type] .= ( $active_art[$type] == 0 ) ? $this->html->tmpl_subscriptions_no_subscription() : '';

                //--------------------------------------------------------------
                // make footers
                //--------------------------------------------------------------

                $result['cat_' . $type] .= $this->html->tmpl_subscriptions_cat_footer();
                $result['art_' . $type] .= $this->html->tmpl_subscriptions_art_footer();


                //--------------------------------------------------------------
                // glue results
                //--------------------------------------------------------------

                $result['subscriptions'] = $result['cat_' . $type] . $result['art_' . $type];

                return $this->html->tmpl_show_subscriptions( $result );
        }

        //----------------------------------------------------------------------
        // delete existing subscriptions
        //----------------------------------------------------------------------

        function delete_subscription( $sid = 0 ) {
        global $ibforums, $std, $DB, $USR, $NAV;

                //--------------------------------------------------------------
                // select what to delete
                //--------------------------------------------------------------

                $sid = intval( $ibforums->input['id'] );

                //--------------------------------------------------------------
                // select type of subscription
                // --
                // 0 - subscribe
                // 1 - favorite
                //--------------------------------------------------------------

                $t = intval( $ibforums->input['t'] );

                //--------------------------------------------------------------
                // no such subscription!
                //--------------------------------------------------------------

                if ( $sid == 0 ) {

                        $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // check if subscription exists
                //--------------------------------------------------------------

                $DB->query( " SELECT id,member_id FROM ibf_cms_subscriptions WHERE id = {$sid} " );

                if ( $DB->get_num_rows() < 1 ) {

                        $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // only moderator can delete not own subscriptions
                //--------------------------------------------------------------

                $dbres = $DB->fetch_row();

                if ( $ibforums->member['id'] != $dbres['member_id'] && $USR->is_mod() == false ) {

                        $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // finally delete subscription
                //--------------------------------------------------------------

                $DB->query( " DELETE FROM ibf_cms_subscriptions WHERE id = {$sid} " );

                //--------------------------------------------------------------
                // where to return
                //--------------------------------------------------------------

                //--------------------------------------------------------------
                // where to return
                //--------------------------------------------------------------

                $url = $ibforums->vars['csite_cms_url'] . "/";

                $return_fav_url = ( $type == "favorite" ) ? $url . "favorites.html" : $url . "subscriptions.html";


                $type = ( $t == 1 ) ? 'favorite' : 'subscribe';

                //--------------------------------------------------------------
                // show finish screen
                //--------------------------------------------------------------

                $url = $ibforums->vars['csite_cms_url'] . "/";

                $return_fav_url = ( $type == "favorite" ) ? $url . "favorites.html" : $url . "subscriptions.html";

                return $this->html->tmpl_success_subscription( $ibforums->lang[$type . '_title'], $ibforums->lang[$type . '_delete_success'], $return_fav_url, $ibforums->lang[$type . '_cat_return'] );
        }
}

?>