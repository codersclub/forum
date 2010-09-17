<?php

/*
+--------------------------------------------------------------------------
|   D-Site Miscelanious functions module
|   ========================================
|   Copyright (c) 2004 - 2005 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   Miscelanious functions
|
*---------------------------------------------------------------------------
*/

class mod_misc {

        //---------------------------------------------------
        // checks if an element of array was in this array
        //---------------------------------------------------

        function is_used($tok, $values = array()) {

                if (is_array($values)) {

                        foreach ($values as $val) {

                                if ($val == $tok) {

                                        return true;
                                }
                        }
                }

                return false;
        }

        //---------------------------------------------------
        // checks if an element of array is empry
        //---------------------------------------------------

        function is_empty($data = array(), $values = array()) {

                $result = false;

                foreach ($values as $v) {

                        if (empty($data[$v])) {

                                $result = true;
                        }
                }

                return $result;
        }

        //**********************************************/
        // copy_dir
        //
        // Copies to contents of a dir to a new dir, creating
        // destination dir if needed.
        //
        //**********************************************/

        function copy_dir($from_path, $to_path, $mode = 0777)
        {

                // Strip off trailing slashes...

                $from_path = preg_replace( "#/$#", "", $from_path);
                $to_path   = preg_replace( "#/$#", "", $to_path);

                if ( ! is_dir($from_path) )
                {
                        $this->errors = "Could not locate directory '$from_path'";
                        return FALSE;
                }

                if ( ! is_dir($to_path) )
                {
                        if ( ! @mkdir($to_path, $mode) )
                        {
                                $this->errors = "Could not create directory '$to_path' please check the CHMOD permissions and re-try";
                                return FALSE;
                        }
                        else
                        {
                                @chmod($to_path, $mode);
                        }
                }

                $this_path = getcwd();

                if (is_dir($from_path))
                {
                        chdir($from_path);
                        $handle=opendir('.');
                        while (($file = readdir($handle)) !== false)
                        {
                                if (($file != ".") && ($file != ".."))
                                {
                                        if (is_dir($file))
                                        {

                                                $this->copy_dir($from_path."/".$file, $to_path."/".$file);

                                                chdir($from_path);
                                        }

                                        if ( is_file($file) )
                                        {
                                                copy($from_path."/".$file, $to_path."/".$file);
                                                @chmod($to_path."/".$file, 0777);
                                        }
                                }
                        }
                        closedir($handle);
                }

                if ($this->errors == "")
                {
                        return TRUE;
                }
        }

        //**********************************************/
        // rm_dir
        //
        // Removes directories, if non empty, removes
        // content and directories
        // (Code based on annotations from the php.net
        // manual by pal@degerstrom.com)
        //**********************************************/

        function rm_dir($file)
        {

                $errors = 0;

                // Remove trailing slashes..

                $file = preg_replace( "#/$#", "", $file );

                if ( file_exists($file) )
                {
                        // Attempt CHMOD

                        @chmod($file, 0777);

                        if ( is_dir($file) )
                        {
                                $handle = opendir($file);

                                while (($filename = readdir($handle)) !== false)
                                {
                                        if (($filename != ".") && ($filename != ".."))
                                        {
                                                $this->rm_dir($file."/".$filename);
                                        }
                                }

                                closedir($handle);

                                if ( ! @rmdir($file) )
                                {
                                        $errors++;
                                }
                        }
                        else
                        {
                                if ( ! @unlink($file) )
                                {
                                        $errors++;
                                }
                        }
                }

                if ($errors == 0)
                {
                        return TRUE;
                }
                else
                {
                        return FALSE;
                }
        }

        //----------------------------------------------------------------------
        // remove attached to article files
        //----------------------------------------------------------------------

        function rm_attached_file( $path = "" ) {
        global $ibforums;

                //--------------------------------------------------------------
                // check input data
                //--------------------------------------------------------------

                if ( $path == "" ) {

                        return false;
                }

                //--------------------------------------------------------------
                // check if file exists
                //--------------------------------------------------------------

                if ( file_exists( $path ) == false ) {

                        return false;
                }

                //--------------------------------------------------------------
                // try to delete file
                //--------------------------------------------------------------

                if ( unlink( $path ) == true ) {

                        //------------------------------------------------------
                        // file deleted successfully
                        //------------------------------------------------------

                        return true;

                } else {

                        //------------------------------------------------------
                        // file couldn't be deleted (check access?)
                        //------------------------------------------------------

                        return -1;
                }
        }

        /*
        +----------------------------------------------------------------------+
        |                                                                      |
        |                ARTICLE SEARCH - SUBSCRIPTIONS                        |
        |                                                                      |
        +----------------------------------------------------------------------+
        */


        //----------------------------------------------------------------------
        // check user's subscriptions of the article/category
        //----------------------------------------------------------------------

        function is_subscibed( $art_id = 0, $cat_id = 0 ) {
        global $ibforums, $DB;


                if ( $ibforums->member['id'] > 0 && $art_id > 0 ) {

                        $DB->query( " SELECT * FROM ibf_cms_subscriptions

                                      WHERE article_id = {$art_id}

                                      OR category_id = {$cat_id}

                                      AND member_id = {$ibforums->member['id']}

                                    " );
                }

                return false;
        }

        //----------------------------------------------------------------------
        // subscribe user to article/category
        //----------------------------------------------------------------------

        function subscribe_member( $art_id = 0, $cat_id = 0, $type = 'favorite' ) {
        global $ibforums, $DB;

                if ( $ibforums->member['id'] > 0 ) {

                        //------------------------------------------------------
                        // subscribe to the article
                        //------------------------------------------------------

                        if ( $art_id != 0 ) {

                                //----------------------------------------------
                                // check older subscriptions
                                //----------------------------------------------

                                $DB->query(" SELECT id FROM ibf_cms_subscriptions

                                             WHERE member_id = {$ibforums->member['id']}

                                             AND article_id = {$art_id}

                                           ");

                                //----------------------------------------------
                                // already subscribed
                                //----------------------------------------------

                                if ( $DB->get_num_rows() > 0 ) {

                                        $std->Error(array('MSG' => 'subscribe_article_exists', 'INIT' => '1'));
                                        exit();
                                }

                                //----------------------------------------------
                                // make subscription
                                //----------------------------------------------

                                $DB->query(" INSERT INTO ibf_cms_upload_subscriptions

                                             (article_id, member_id, type)

                                             VALUES

                                             ({$art_id}, {$ibforums->member['id']}, {$type})

                                           ");
                        }
                }

                return true;
        }

        //----------------------------------------------------------------------
        // convert action ids
        //----------------------------------------------------------------------

        function get_action_id() {
        global $ibforums, $NAV;

                //--------------------------------------------------------------
                // convert only mod_rewrite actions
                //--------------------------------------------------------------

                if ( $ibforums->vars['dsite_use_mod_rewrite'] == 1 ) {

                        //------------------------------------------------------
                        // get %NAME%.html from uri
                        //------------------------------------------------------

                        preg_match( "|(\w+)\.\w+$|", preg_replace("|\/$|", "", $ibforums->input['art']), $match );

                        //------------------------------------------------------
                        // matches are:
                        //  upload:
                        //  - edit
                        //  - delete
                        //  - approve
                        //  - move
                        //  - remove
                        //------------------------------------------------------

                        switch ( $match[1] ) {

                                case 'create'   	: $ibforums->input['ACTION'] = 0;
													  return 'upload';

                                case 'do_create' 	: $ibforums->input['ACTION'] = 1;
                                					  return 'upload';

                                case 'edit'    		: $ibforums->input['ACTION'] = 2;
                                                      return 'upload';

                                case 'write'   		: $ibforums->input['ACTION'] = 3;
                                                 	  return 'upload';

                                case 'delete_screen': $ibforums->input['ACTION'] = 4;
                                                 	  return 'upload';

                                case 'delete' 		: $ibforums->input['ACTION'] = 5;
                                                 	  return 'upload';

                                case 'approve' 		: $ibforums->input['ACTION'] = 6;
                                                 	  return 'upload';

                                case 'disable' 		: $ibforums->input['ACTION'] = 7;
                                                 	  return 'upload';

                                case 'move'    		: $ibforums->input['ACTION'] = 8;
                                                 	  return 'upload';

                                case 'do_move' 		: $ibforums->input['ACTION'] = 9;
                                                 	  return 'upload';

                                case 'remove'  		: $ibforums->input['ACTION'] = 10;
                                                 	  return 'upload';

                                case 'add_comment'	: $ibforums->input['ACTION'] = 2;
                                                 	  return 'comments';

                                case 'delete_comment': $ibforums->input['ACTION'] = 5;
                                                 	  return 'comments';

                                case 'edit_comment': $ibforums->input['ACTION'] = 3;
                                                 	  return 'comments';

                                case 'do_edit_comment': $ibforums->input['ACTION'] = 4;
                                                 	  return 'comments';

                                case 'favorites'	  : $ibforums->input['FAV'] = 3;
                                						break;

                                case 'subscriptions'  : $ibforums->input['FAV'] = 1;
                                						break;

                                case 'subscribe'      : $ibforums->input['FAV'] = 5;
                                						break;

                                case 'add_to_favorite' : $ibforums->input['FAV'] = 6;
                                						break;

                                case 'unsubscribe'    : $ibforums->input['FAV'] = 4;
                                						break;
                        }
                }

                return null;
        }

        //---------------------------------------------------------------------
        // get action sub id - delete,approve etc
        //----------------------------------------------------------------------

        function get_action_sub_id() {
        global $ibforums;

                //--------------------------------------------------------------
                // get sud ibs for mod_rewrite
                //--------------------------------------------------------------

                if ( $ibforums->vars['dsite_use_mod_rewrite'] == 1 ) {

                        $actions = array(
                                           '1' => 'upload',
                                           '2' => 'edit',
                                           '3' => 'delete',
                                           '4' => 'approve',
                                           '5' => 'move',
                                         );

                        foreach ( $actions as $num => $action ) {

                                if ( isset( $ibforums->input[$action] ) ) {

                                        echo $num;
                                }
                        }
                }

                return 0;
        }

}