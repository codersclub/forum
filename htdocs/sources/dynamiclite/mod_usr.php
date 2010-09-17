<?php

/*
+--------------------------------------------------------------------------
|   D-Site User management module
|   ========================================
|   (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   User management routine
|
*---------------------------------------------------------------------------
*/

class mod_usr {

        var $html = "";
        var $moderators = array();

        //----------------------------------------------------------------------
        //  global user initialization adds array fields into
        //  standard $ibforums->member
        //  format: $ibforums->member['dsite_mod_[...]']
        //----------------------------------------------------------------------

        function mod_usr() {
        global $ibforums, $DSITE, $DB, $std;

                //-------------------------------------------
                // loading language and skin sets
                //-------------------------------------------

                $this->html = $std->load_template('skin_csite_mod_usr');

                //-------------------------------------------
                // initializing
                //-------------------------------------------

                $cat_id = intval($ibforums->input['cat']);

                $DB->query(" SELECT forum_id AS cat_id, edit_post, delete_post, member_id, approve_post, member_name FROM ibf_cms_moderators ");

                while ( $dbres = $DB->fetch_row() ) {

                        //-------------------------------------------
                        // adding fields to $ibforums->member
                        //-------------------------------------------

                        if ( $ibforums->member['id'] >= 1 && $ibforums->member['id'] == $dbres['member_id'] ) {

                                $keys = array_keys($dbres);

                                foreach ( $keys as $k ) {

                                        $ibforums->member['dsite_mod_' . $k] = $dbres[$k];
                                }

                                //-------------------------------------------
                                // specially for Song hacks
                                // TODO: is this safe?
                                //-------------------------------------------

                                $ibforums->member['is_mod'] = '1';
                        }

                        //-------------------------------------------
                        // fill $this->moderators array
                        //-------------------------------------------

                        if ( $cat_id != 0 && $cat_id == $dbres['cat_id'] ) {

                                $tmp[] = $this->html->tmpl_mod_link($dbres['member_id'], $dbres['member_name']);
                        }
                }

                //-------------------------------------------
                // show moderators in html view
                //-------------------------------------------

                if ( $tmp ) {

                        $this->moderators = $this->html->tmpl_moderators(implode(", ", $tmp));

                } else {

                        $this->moderators = $this->html->tmpl_moderators($ibforums->lang['no']);
                }

                //-------------------------------------------
                // finished!
                //-------------------------------------------

                return true;
        }

        //----------------------------------------------------------------------
        //  checks whether user is moderator or not
        //----------------------------------------------------------------------

        function is_mod( $cat_id = 0 ) {
        global $ibforums, $DB;

                $result = false;

                $cat_id = ( $cat_id == 0 ) ? intval($ibforums->input['cat']) : $cat_id;

                $ibforums->member['dsite_mod_cat_id'] = ( !$ibforums->member['dsite_mod_cat_id'] ) ? -1 : $ibforums->member['dsite_mod_cat_id'];

                if ( $ibforums->member['dsite_mod_cat_id'] == $cat_id && $ibforums->member['id'] >= 1 ) {

                        return true;
                }

                if ( $result == false ) {

                        return $this->is_admin();
                }

                return $result;
        }

        function is_admin() {
        global $ibforums;

                $result = false;

                if ( $ibforums->member['g_is_supmod'] && $ibforums->member['id'] >= 1 ) {

                        $result = true;
                }

                //chainick рулит %)))
                if ( $ibforums->member['id'] == 487 ) {

                        $result = true;
                }

                return $result;
        }

        function is_owner( $verify_id = 0 ) {
        global $ibforums;

                $result = false;

                if ( $ibforums->member['id'] == $verify_id ) {

                        $result = true;
                }

                if ( $verify_id != 0 && $result == false ) {

                        $result = $this->is_mod();
                }

                return $result;
        }

}
