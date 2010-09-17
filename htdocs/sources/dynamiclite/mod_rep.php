<?php

/*
+---------------------------------------------------------------------------
|   D-Site Reputation working module
|   ========================================
|   Copyright (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   Reputation management functions
|
*---------------------------------------------------------------------------
*/

class mod_rep extends mod_art {

        var $PARENT;
        var $html = '';

        //----------------------------------------------------------------------
        // class constructor
        //----------------------------------------------------------------------

        function mod_rep( $PARENT ) {
        global $ibforums, $std;

                //--------------------------------------------------------------
                // init variables
                //--------------------------------------------------------------

                $this->PARENT = $PARENT;

                $this->html = $std->load_template( 'skin_csite_mod_rep' );

                //--------------------------------------------------------------
                // get action ids
                //--------------------------------------------------------------

                $rep_id = intval( $ibforums->input['REP'] );

                //--------------------------------------------------------------
                // decide what to do
                // --
                // REP IN:
                // 0 - show_rep_form
                // 1 - save_rep
                // 2 - add_article_rep
                //--------------------------------------------------------------

                switch ( $rep_id ) {

                        case 2 :
                                $PARENT->result = $this->save_rep();
                                return;

                        default:
                                $PARENT->result = $this->show_rep_form();
                                return;
                }
        }

        //----------------------------------------------------------------------
        // show reputation form
        //----------------------------------------------------------------------

        function show_rep_form() {
        global $ibforums;

                //--------------------------------------------------------------
                // init vars
                //--------------------------------------------------------------

                $result = array();
                $this->make_data_array( &$result );

                //--------------------------------------------------------------
                // decide what to show
                // --
                // 1 - show members rep
                // 2 - show article rep
                //--------------------------------------------------------------

                $result['title']  = ( $result['rep_cd'] == 1 ) ? $ibforums->lang['rep_change_member'] : $ibforums->lang['rep_change_article'];
                $result['rep_id'] = 2;

                //--------------------------------------------------------------
                // check restrictions
                //--------------------------------------------------------------

                $this->check_restrictions( true, $result );

                return $this->html->tmpl_show_rep_form( $result );
        }

        //----------------------------------------------------------------------
        // inc/dec member's reputation
        //----------------------------------------------------------------------

        function save_rep() {
        global $ibforums, $std, $DB;

                //--------------------------------------------------------------
                // init variables
                //--------------------------------------------------------------

                $result = array();
                $this->make_data_array( &$result );
                $result['data']   = $ibforums->input['Post'];

                //--------------------------------------------------------------
                // check restrictions
                //--------------------------------------------------------------

                $this->check_restrictions( true, $result );

                //--------------------------------------------------------------
                // decide what to save
                // -
                // 1 - member
                // 2 - article
                //--------------------------------------------------------------

                if ( $result['rep_cd'] == 1 ) {

                        //------------------------------------------------------
                        // save member's reputation
                        //------------------------------------------------------

                        echo  "member()";

                } else if ( $result['rep_cd'] == 2 ) {

                        //------------------------------------------------------
                        // save article reputation
                        //------------------------------------------------------

                        echo "article()";

                } else {

                        //------------------------------------------------------
                        // some hack?
                        //------------------------------------------------------

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                return "MEM REP!";
        }

        //----------------------------------------------------------------------
        // check user's rights and input data
        //----------------------------------------------------------------------

        function check_restrictions( $check_data = true, $data = array() ) {
        global $ibforums, $std;

                //--------------------------------------------------------------
                // check restrictions
                //--------------------------------------------------------------

                if ( $ibforums->member['g_art_allow_rep'] != 1 ) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                //--------------------------------------------------------------
                // access only
                //--------------------------------------------------------------

                if ( $check_data == false ) {

                        return true;
                }

                //--------------------------------------------------------------
                // check data
                //--------------------------------------------------------------

                foreach ( $data as $r => $res ) {

                        if ( empty($res) ) {

                                $std->Error(array('MSG' => 'no_data', 'INIT' => '1'));
                                exit();
                        }
                }

                //--------------------------------------------------------------
                // no restrictions
                //--------------------------------------------------------------

                return true;
        }

        //----------------------------------------------------------------------
        // get data from input into array
        //----------------------------------------------------------------------

        function make_data_array( $result = array() ) {
        global $ibforums;

                $result['rep_id'] = (int) intval( $ibforums->input['REP'] );
                $result['rep_cd'] = (int) intval( $ibforums->input['CODE'] );
                $result['art_id'] = (int) intval( $ibforums->input['art_id'] );
                $result['cat_id'] = (int) intval( $ibforums->input['cat_id'] );
                $result['ver_id'] = (int) intval( $ibforums->input['ver_id'] );
                $result['mem_id'] = (int) intval( $ibforums->input['mem_id'] );
        }
}