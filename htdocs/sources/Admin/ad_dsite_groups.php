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
|   > Admin Forum functions
|   > Module written by Matt Mecham
|   > Date started: 17th March 2002
|
|        > Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/




$idx = new ad_dsite_groups();


class ad_dsite_groups {

        var $base_url;

        function ad_dsite_groups() {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //---------------------------------------
                // Kill globals - globals bad, Homer good.
                //---------------------------------------

                $tmp_in = array_merge( $_GET, $_POST, $_COOKIE );

                foreach ( $tmp_in as $k => $v )
                {
                        unset($$k);
                }

                //---------------------------------------

                switch($IN['code'])
                {
                        case 'doadd':
                                $this->save_group('add');
                                break;

                        case 'add':
                                $this->group_form('add');
                                break;

                        case 'edit':
                                $this->group_form('edit');
                                break;

                        case 'doedit':
                                $this->save_group('edit');
                                break;

                        case 'delete':
                                $this->delete_form();
                                break;

                        case 'dodelete':
                                $this->do_delete();
                                break;

                        //-------------------------

                        case 'fedit':
                                $this->forum_perms();
                                break;

                        case 'pdelete':
                                $this->delete_mask();
                                break;

                        case 'dofedit':
                                $this->do_forum_perms();
                                break;

                        case 'permsplash':
                                $this->permsplash();
                                break;

                        case 'view_perm_users':
                                $this->view_perm_users();
                                break;

                        case 'remove_mask':
                                $this->remove_mask();
                                break;

                        case 'preview_forums':
                                $this->preview_forums();
                                break;

                        case 'dopermadd':
                                $this->add_new_perm();
                                break;

                        case 'donameedit':
                                $this->edit_name_perm();
                                break;
                        //-------------------------



                        default:
                                $this->main_screen();
                                break;
                }

        }

        //+---------------------------------------------------------------------------------
        //
        // Member group /forum mask permission form thingy doodle do yes. Viewing Perm users
        //
        //+---------------------------------------------------------------------------------

        function delete_mask()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //+-------------------------------------------
                // Check for a valid ID
                //+-------------------------------------------

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the permission mask setty doodle thingy ID, please try again");
                }

                $DB->query("DELETE FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");

                $old_id = intval($IN['id']);

                //+-------------------------------------------
                // Remove from forums...
                //+-------------------------------------------

                $get = $DB->query("SELECT id, read_perms, reply_perms, start_perms, upload_perms FROM ibf_forums");

                while( $f = $DB->fetch_row($get) )
                {
                        $d_str = "";
                        $d_arr = array();

                        foreach( array( 'read_perms', 'reply_perms', 'start_perms', 'upload_perms' ) as $perm_bit )
                        {
                                if ($f[ $perm_bit ] != '*')
                                {
                                        if ( preg_match( "/(^|,)".$old_id."(,|$)/", $f[ $perm_bit ]) )
                                        {
                                                $f[ $perm_bit ] = preg_replace( "/(^|,)".$old_id."(,|$)/", "\\1\\2", $f[ $perm_bit ] );

                                                $d_arr[ $perm_bit ] = $this->clean_perms( $f[ $perm_bit ] );
                                        }
                                }
                        }

                        // Do we have anything to save?

                        if ( count($d_arr) > 0 )
                        {
                                $d_str = $DB->compile_db_update_string( $d_arr );

                                // Sure?..

                                if ( strlen($d_str) > 5)
                                {
                                        $save = $DB->query("UPDATE ibf_forums
                                                            SET $d_str
                                                            WHERE id={$f['id']}");
                                }
                        }
                }

                $this->permsplash();
        }

        //+---------------------------------------------------------------------------------


        function add_new_perm()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                $IN['new_perm_name'] = trim($IN['new_perm_name']);

                if ($IN['new_perm_name'] == "")
                {
                        $ADMIN->error("You must enter a name");
                }

                $copy_id = $IN['new_perm_copy'];

                //+-------------------------------------------
                // UPDATE DB
                //+-------------------------------------------

                $DB->query("INSERT INTO ibf_forum_perms
                            SET perm_name='".$IN['new_perm_name']."'");

                $new_id = $DB->get_insert_id();

                if ( $copy_id != 'none' )
                {
                        //+-------------------------------------------
                        // Add new mask to forum accesses
                        //+-------------------------------------------

                        $old_id = intval($copy_id);

                        if ( ($new_id > 0) and ($old_id > 0) )
                        {
                                $get = $DB->query("SELECT id, read_perms, reply_perms, start_perms, upload_perms FROM ibf_forums");

                                while( $f = $DB->fetch_row($get) )
                                {
                                        $d_str = "";
                                        $d_arr = array();

                                        foreach( array( 'read_perms', 'reply_perms', 'start_perms', 'upload_perms' ) as $perm_bit )
                                        {
                                                if ($f[ $perm_bit ] != '*')
                                                {
                                                        if ( preg_match( "/(^|,)".$old_id."(,|$)/", $f[ $perm_bit ]) )
                                                        {
                                                                $d_arr[ $perm_bit ] = $this->clean_perms( $f[ $perm_bit ] ) . ",".$new_id;
                                                        }
                                                }
                                        }

                                        // Do we have anything to save?

                                        if ( count($d_arr) > 0 )
                                        {
                                                $d_str = $DB->compile_db_update_string( $d_arr );

                                                // Sure?..

                                                if ( strlen($d_str) > 5)
                                                {
                                                  $save = $DB->query(
                                                        "UPDATE ibf_forums
                                                        SET $d_str
                                                        WHERE id={$f['id']}");
                                                }
                                        }
                                }
                        }


                }

                $this->permsplash();


        }

        //-_-_-_-_-_-_-_-_-
        //_-_-_-_-_-_-_-_-_
        //Now that's pretty

        function preview_forums()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //+-------------------------------------------
                // Check for a valid ID
                //+-------------------------------------------

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the permission mask setty doodle thingy ID, please try again");
                }


                $DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");

                if ( ! $perms = $DB->fetch_row() )
                {
                        $ADMIN->error("Could not resolve the permission mask setty doodle thingy ID, please try again");
                }

                //+-------------------------------------------
                // What we doin'?
                //+-------------------------------------------

                switch( $IN['t'] )
                {
                        case 'start':
                                $human_type = 'Start Topics';
                                $code_word  = 'start_perms';
                                break;

                        case 'reply':
                                $human_type = 'Reply To Topics';
                                $code_word  = 'reply_perms';
                                break;

                        default:
                                $human_type = 'View Forum';
                                $code_word  = 'read_perms';
                                break;
                }

                //+-------------------------------------------
                // Get all members using that ID then!
                //+-------------------------------------------

                $SKIN->td_header[] = array( "$human_type" , "100%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Preview using: " . $perms['perm_name'] );

                $last_cat_id = -1;

                $DB->query("SELECT f.id as forum_id, f.parent_id, f.subwrap, f.sub_can_post, f.name as forum_name, f.position, f.read_perms, f.start_perms, f.reply_perms, c.id as cat_id, c.name
                                    FROM ibf_forums f
                                     LEFT JOIN ibf_categories c ON (c.id=f.category)
                                    ORDER BY c.position, f.position");


                $forum_keys = array();
                $cat_keys   = array();
                $children   = array();
                $subs       = array();
                $the_html   = "";

                $perm_id    = intval($IN['id']);

// Song * endless forums, 20.12.04

                while ( $i = $DB->fetch_row() )
                {

                        if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
                        {
                                $forum_keys[ $i['cat_id'] ][$i['forum_id']] = "- {$i['forum_name']}\n";
                        }
                        else
                        {
                                if ($i[ $code_word ] == '*')
                                {
                                        if ($i['parent_id'] > 0)
                                        {
                                                $children[ $i['parent_id'] ][] = array($i['forum_id'], "---- {$i['forum_name']}\n");
                                        }
                                        else
                                        {
                                                $forum_keys[ $i['cat_id'] ][$i['forum_id']] = "- {$i['forum_name']}\n";
                                        }
                                }
                                else if (preg_match( "/(^|,)".$perm_id."(,|$)/", $i[ $code_word ]) )
                                {
                                        if ($i['parent_id'] > 0)
                                        {
                                                $children[ $i['parent_id'] ][] = array($i['forum_id'], "---- {$i['forum_name']}\n");
                                        }
                                        else
                                        {
                                                $forum_keys[ $i['cat_id'] ][$i['forum_id']] = array($i['forum_id'], "- {$i['forum_name']}\n");
                                        }
                                }
                                else
                                {
                                        //-------------------------------------
                                        // CAN'T ACCESS
                                        //-------------------------------------

                                        if ($i['parent_id'] > 0)
                                        {
                                                $children[ $i['parent_id'] ][] = array($i['forum_id'], "<span style='color:gray'>---- {$i['forum_name']}</span>\n");
                                        }
                                        else
                                        {
                                                $forum_keys[ $i['cat_id'] ][$i['forum_id']] = "<span style='color:gray'>- {$i['forum_name']}</span>\n";
                                        }
                                }
                        }

                        if ($last_cat_id != $i['cat_id'])
                        {

                                // Make sure cats with hidden forums are not shown in forum jump

                                $cat_keys[ $i['cat_id'] ] = "<b>{$i['name']}</b>\n";

                                $last_cat_id = $i['cat_id'];

                        }
                }

                foreach($cat_keys as $cat_id => $cat_text)
                {
                        if ( is_array( $forum_keys[$cat_id] ) && count( $forum_keys[$cat_id] ) > 0 )
                        {
                                $the_html .= $cat_text;

                                foreach($forum_keys[$cat_id] as $idx => $forum_text)
                                {
                                        $the_html .= '&nbsp;&nbsp;'.$forum_text;

                                        if (count($children[$idx]) > 0)
                                        {
                                                foreach($children[$idx] as $ii => $tt)
                                                {
                                                        $the_html .= '&nbsp;&nbsp;'.$tt[1];
                                                        $the_html .= $this->subforums_addpreview($children, $tt[0]);
                                                }
                                        }
                                }
                        }
                }

                $the_html = str_replace( "\n", "<br />\n", $the_html );

                $ADMIN->html .= $SKIN->add_td_row( array( $the_html )      );

                $ADMIN->html .= $SKIN->end_table();

                //----------------------------------------------------------------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'preview_forums' ),
                                                                                                  2 => array( 'act'   , 'group'   ),
                                                                                                  3 => array( 'id'    , $IN['id']      ),
                                                                             )      );

                $SKIN->td_header[] = array( "&nbsp;" , "60%" );
                $SKIN->td_header[] = array( "&nbsp;" , "40%" );

                $ADMIN->html .= $SKIN->start_table( "Legend & Info" );

                $ADMIN->html .= $SKIN->add_td_row( array(
                                                                                                        "Can $human_type for this forum",
                                                                                                        "<input type='text' readonly='readonly' style='border:1px solid black;background-color:black;size=30px' name='blah'>"
                                                                                 )      );

                $ADMIN->html .= $SKIN->add_td_row( array(
                                                                                                        "CANNOT $human_type for this forum",
                                                                                                        "<input type='text' readonly='readonly' style='border:1px solid gray;background-color:gray;size=30px' name='blah'>"
                                                                                 )      );

                $ADMIN->html .= $SKIN->add_td_row( array(
                                                                                                        "Test with...",
                                                                                                        $SKIN->form_dropdown( 't',
                                                                                                                                                array( 0 => array( 'start', 'Start Topics'    ),
                                                                                                                                                           1 => array( 'reply', 'Reply To Topics' ),
                                                                                                                                                           2 => array( 'read' , 'Read Forum'      ),
                                                                                                                                                          ), $IN['t'] )
                                                                                 )      );

                $ADMIN->html .= $SKIN->end_form( "Update" );

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->print_popup();

        }

        //===========================================================================

        function remove_mask()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //+-------------------------------------------
                // Check for a valid ID
                //+-------------------------------------------

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the member ID, please try again");
                }

                //+-------------------------------------------
                // Get, check and reset
                //+-------------------------------------------

                $DB->query("SELECT id, name, org_perm_id FROM ibf_members WHERE id=".intval($IN['id']));

                if ( ! $mem = $DB->fetch_row() )
                {
                        $ADMIN->error("Could not resolve the member ID, please try again");
                }

                if ( $IN['pid'] == 'all' )
                {
                        $DB->query("UPDATE ibf_members
                                    SET org_perm_id=0
                                    WHERE id=".intval($IN['id']));
                }
                else
                {
                        $IN['pid'] = intval($IN['pid']);

                        $pid_array = explode( ",", $mem['org_perm_id'] );

                        if ( count($pid_array) < 2 )
                        {
                                $DB->query("UPDATE ibf_members
                                            SET org_perm_id=0
                                            WHERE id=".intval($IN['id']));
                        }
                        else
                        {
                                $new_arr = array();

                                foreach( $pid_array as $sid )
                                {
                                        if ( $sid != $IN['pid'] )
                                        {
                                                $new_arr[] = $sid;
                                        }
                                }

                                $DB->query("UPDATE ibf_members
                                            SET org_perm_id='".implode(",",$new_arr)."'
                                            WHERE id=".intval($IN['id']));
                        }

                }

                //+-------------------------------------------
                // Get all members using that ID then!
                //+-------------------------------------------

                $SKIN->td_header[] = array( "&nbsp;" , "100%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Result" );



                $ADMIN->html .= $SKIN->add_td_row( array( "Removed the custom permission mask from <b>{$mem['name']}</b>.",
                                                                                 )      );

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->print_popup();

        }

        //===========================================================================


        function view_perm_users()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //+-------------------------------------------
                // Check for a valid ID
                //+-------------------------------------------

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the permission mask setty doodle thingy ID, please try again");
                }


                $DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");

                if ( ! $perms = $DB->fetch_row() )
                {
                        $ADMIN->error("Could not resolve the permission mask setty doodle thingy ID, please try again");
                }

                //+-------------------------------------------
                // Get all members using that ID then!
                //+-------------------------------------------

                $SKIN->td_header[] = array( "User Details" , "50%" );
                $SKIN->td_header[] = array( "Action"       , "50%" );

                //+-------------------------------

                $ADMIN->html .= "<script language='javascript' type='text/javascript'>
                                                 <!--
                                                  function pop_close_and_stop( id )
                                                  {
                                                          opener.location = \"{$SKIN->base_url}&act=mem&code=doform&MEMBER_ID=\" + id;
                                                          self.close();
                                                  }
                                                  //-->
                                                  </script>";

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Members using: " . $perms['perm_name'] );

                $outer = $DB->query("SELECT id, name, email, posts, org_perm_id FROM ibf_members WHERE (org_perm_id IS NOT NULL AND org_perm_id <> 0) ORDER BY name");

                while( $r = $DB->fetch_row($outer) )
                {
                        $exp_pid = explode( ",", $r['org_perm_id'] );

                        foreach( explode( ",", $r['org_perm_id'] ) as $pid )
                        {
                                if ( $pid == $IN['id'] )
                                {
                                        if ( count($exp_pid) > 1 )
                                        {
                                                $extra = "<li>Also using: <em style='color:red'>";

                                                $DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id IN ({$r['org_perm_id']}) AND perm_id <> {$IN['id']}");

                                                while ( $mr = $DB->fetch_row() )
                                                {
                                                        $extra .= $mr['perm_name'].",";
                                                }

                                                $extra = preg_replace( "/,$/", "", $extra );

                                                $extra .= "</em>";
                                        }
                                        else
                                        {
                                                $extra = "";
                                        }

                                        $ADMIN->html .= $SKIN->add_td_row( array( "<div style='font-weight:bold;font-size:11px;padding-bottom:6px;margin-bottom:3px;border-bottom:1px solid #000'>{$r['name']}</div>
                                                                                                                           <li>Posts: {$r['posts']}
                                                                                                                           <li>Email: {$r['email']}
                                                                                                                           $extra" ,
                                                                                                                          "&#149;&nbsp;<a href='{$SKIN->base_url}&amp;act=group&amp;code=remove_mask&amp;id={$r['id']}&amp;pid=$pid' title='Remove this mask from the user (will not remove all if they have multimasks'>Remove This Mask</a>
                                                                                                                           <br />&#149;&nbsp;<a href='{$SKIN->base_url}&amp;act=group&amp;code=remove_mask&amp;id={$r['id']}&amp;pid=all' title='Remove all user masks'>Remove All Masks</a>
                                                                                                                           <br /><br />&#149;&nbsp;<a href='javascript:pop_close_and_stop(\"{$r['id']}\");'>Edit Member</a>",
                                                                                                         )      );
                                }
                        }
                }


                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->print_popup();

        }


        //+---------------------------------------------------------------------------------
        //
        // Member group /forum mask permission form thingy doodle do yes.
        //
        //+---------------------------------------------------------------------------------


        function permsplash()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                $ADMIN->page_title = "Forum Permission Mask [ HOME ]";

                $ADMIN->page_detail = "You can manage your forum permission masks from this section.";

                $ADMIN->page_detail .= "<br /><b>Used by Groups</b> relates to the member groups that use this permission mask
                                                                <br /><b>Used by Members</b> relates to the number of members that have this permission mask set to over ride the group used permission mask
                                                            <br /><b>Preview</b> the forums this mask has access to in a quick, convenient format
                                                           ";


                //+-------------------------------------------
                // Get the names for the perm masks w/id
                //+-------------------------------------------

                $perms = array();

                $DB->query("SELECT * FROM ibf_forum_perms");

                while( $r = $DB->fetch_row() )
                {
                        $perms[ $r['perm_id'] ] = $r['perm_name'];
                }

                //+-------------------------------------------
                // Get the number of members using this mask
                // as an over ride
                //+-------------------------------------------

                $mems = array();

                $DB->query("SELECT COUNT(id) as count, org_perm_id FROM ibf_members WHERE (org_perm_id IS NOT NULL AND org_perm_id <> 0) GROUP by org_perm_id");

                while( $r = $DB->fetch_row() )
                {
                        if ( strstr($r['org_perm_id'] , "," ) )
                        {
                                foreach( explode( ",", $r['org_perm_id'] ) as $pid )
                                {
                                        $mems[ $pid ] += $r['count'];
                                }
                        }
                        else
                        {
                                $mems[ $r['org_perm_id'] ] += $r['count'];
                        }
                }

                //+-------------------------------------------
                // Get the member group names and the mask
                // they use
                //+-------------------------------------------

                $groups = array();

                $DB->query("SELECT g_id, g_title, g_perm_id FROM ibf_groups");

                while( $r = $DB->fetch_row() )
                {
                        if ( strstr($r['g_perm_id'] , "," ) )
                        {
                                foreach( explode( ",", $r['g_perm_id'] ) as $pid )
                                {
                                        $groups[ $pid ][] = $r['g_title'];
                                }
                        }
                        else
                        {
                                $groups[ $r['g_perm_id'] ][] = $r['g_title'];
                        }
                }

                //+-------------------------------------------
                // Print the splash screen
                //+-------------------------------------------

                $SKIN->td_header[] = array( "Mask Name"          , "20%" );
                $SKIN->td_header[] = array( "Used by Group(s)"   , "20%" );
                $SKIN->td_header[] = array( "Used by Mem(s)"     , "20%" );
                $SKIN->td_header[] = array( "Preview"            , "10%" );
                $SKIN->td_header[] = array( "Edit"               , "15%" );
                $SKIN->td_header[] = array( "Delete"             , "15%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->js_pop_win();

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Forum Permission Masks" );

                foreach( $perms as $id => $name )
                {
                        $groups_used = "";

                        $is_active = 0;

                        if ( is_array( $groups[ $id ] ) )
                        {
                                foreach( $groups[ $id ] as $bleh => $g_title )
                                {
                                        $groups_used .= $g_title . "<br />";
                                }

                                $is_active = 1;

                        }
                        else
                        {
                                $groups_used = "<center><i>None</i></center>";
                        }

                        $mems_used = 0;

                        if ( $mems[ $id ] > 0 )
                        {
                                $is_active = 1;
                                $mems_used = $mems[ $id ] . " (<a href='javascript:pop_win(\"&amp;act=group&amp;code=view_perm_users&amp;id=$id\", \"User\", \"500\",\"350\");' title='View the member names of those using this mask in a new window'>View</a>)";
                        }

                        if ( $is_active > 0 )
                        {
                                $delete = "<i>Can't, in use</i>";
                        }
                        else
                        {
                                $delete = "<a href='{$SKIN->base_url}&amp;act=group&amp;code=pdelete&amp;id=$id'>Delete</a>";
                        }

                        $ADMIN->html .= $SKIN->add_td_row( array( "<b>$name</b>" ,
                                                                                                          "$groups_used",
                                                                                                          "<center>$mems_used</center>",
                                                                                                          "<center><a href='javascript:pop_win(\"&amp;act=group&amp;code=preview_forums&amp;id=$id&amp;t=read\", \"400\",\"350\");' title='See what this group can see..'>Preview</a></center>",
                                                                                                          "<center><a href='{$SKIN->base_url}&amp;act=group&amp;code=fedit&amp;id=$id'>Edit</a></center>",
                                                                                                          "<center>$delete</center>",
                                                                                         )      );

                }

                $ADMIN->html .= $SKIN->end_table();

                //+-------------------------------

                $dlist = array();

                $dlist[] = array( 'none', 'Do not inherit' );

                foreach( $perms as $id => $name )
                {
                        $dlist[] = array( $id, $name );
                }

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dopermadd' ),
                                                                                                  2 => array( 'act'   , 'group'   ),
                                                                             )      );


                $SKIN->td_header[] = array( "{none}" , "60%" );
                $SKIN->td_header[] = array( "{none}" , "40%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Create a new permission mask" );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Permission Mask Name</b>" ,
                                                                                                  $SKIN->form_input( 'new_perm_name' ),
                                                                                 )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Inherit forum permission mask from...</b>" ,
                                                                                                 $SKIN->form_dropdown( 'new_perm_copy', $dlist ),
                                                                                 )      );

                $ADMIN->html .= $SKIN->end_form("Create");

                $ADMIN->html .= $SKIN->end_table();



                $ADMIN->output();


        }



        function forum_perms()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the group ID, please try again");
                }

                //+----------------------------------

                $ADMIN->page_title = "Forum Permission Mask [ EDIT ]";

                $ADMIN->page_detail = "You can manage your forum permission masks from this section.";

                $ADMIN->page_detail .= "<br />Simply check the boxes to allow permission for that action, or uncheck the box to deny permission for that action.
                                                           <br /><b>Global</b> indicates that all present and future permission masks have access to that action and as such, cannot be changed";

                //+----------------------------------

                $DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");

                $group = $DB->fetch_row();

                $gid   = $group['perm_id'];
                $gname = $group['perm_name'];

                //+-------------------------------

                $cats     = array();
                $forums   = array();
                $children = array();

                $DB->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position ASC");

                while ($r = $DB->fetch_row())
                {
                        $cats[$r['id']] = $r;
                }

                $DB->query("SELECT * from ibf_forums ORDER BY position ASC");

                while ($r = $DB->fetch_row())
                {

                        if ($r['parent_id'] > 0)
                        {
                                $children[ $r['parent_id'] ][] = $r;
                        }
                        else
                        {
                                $forums[] = $r;
                        }

                }

                //+----------------------------------
                //| EDIT NAME
                //+----------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'donameedit' ),
                                                                                                  2 => array( 'act'   , 'group'   ),
                                                                                                  3 => array( 'id'    , $gid      ),
                                                                             )      );

                $SKIN->td_header[] = array( "&nbsp;"   , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"   , "60%" );

                $ADMIN->html .= $SKIN->start_table( "Rename Group: ".$group['perm_name'] );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Mask Name</b>" ,
                                                                                                  $SKIN->form_input("perm_name", $gname )
                                                                             )      );

                $ADMIN->html .= $SKIN->end_form("Edit Name");

                $ADMIN->html .= $SKIN->end_table();


                //+----------------------------------
                //| MAIN FORM
                //+----------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dofedit' ),
                                                                                                  2 => array( 'act'   , 'group'   ),
                                                                                                  3 => array( 'id'    , $gid      ),
                                                                             )      );

                $SKIN->td_header[] = array( "Forum Name"   , "40%" );
                $SKIN->td_header[] = array( "Read"         , "15%" );
                $SKIN->td_header[] = array( "Reply"        , "15%" );
                $SKIN->td_header[] = array( "Start"        , "15%" );
                $SKIN->td_header[] = array( "Upload"       , "15%" );

                $ADMIN->html .= $SKIN->start_table( "Forum Access Permissions for ".$group['perm_name'] );

                $last_cat_id = -1;

                foreach ($cats as $c)
                {

                        $ADMIN->html .= $SKIN->add_td_basic( $c['name'], 'left', 'catrow' );

                        $last_cat_id = $c['id'];


                        foreach($forums as $r)
                        {

                                if ($r['category'] == $last_cat_id)
                                {

                                        $read   = "";
                                        $start  = "";
                                        $reply  = "";
                                        $upload = "";
                                        $global = '<center id="mgblue"><i>Global</i></center>';

                                        if ($r['read_perms'] == '*')
                                        {
                                                $read = $global;
                                        }
                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['read_perms'] ) )
                                        {
                                                $read = "<center id='mgblue'><input type='checkbox' name='read_".$r['id']."' value='1' checked></center>";
                                        }
                                        else
                                        {
                                                $read = "<center id='mgblue'><input type='checkbox' name='read_".$r['id']."' value='1'></center>";
                                        }

                                        //---------------------------

                                        $global = '<center id="mgred"><i>Global</i></center>';

                                        if ($r['start_perms'] == '*')
                                        {
                                                $start = $global;
                                        }
                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['start_perms'] ) )
                                        {
                                                $start = "<center id='mgred'><input type='checkbox' name='start_".$r['id']."' value='1' checked></center>";
                                        }
                                        else
                                        {
                                                $start = "<center id='mgred'><input type='checkbox' name='start_".$r['id']."' value='1'></center>";
                                        }

                                        //---------------------------

                                        $global = '<center id="mggreen"><i>Global</i></center>';

                                        if ($r['reply_perms'] == '*')
                                        {
                                                $reply = $global;
                                        }
                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['reply_perms'] ) )
                                        {
                                                $reply = "<center id='mggreen'><input type='checkbox' name='reply_".$r['id']."' value='1' checked></center>";
                                        }
                                        else
                                        {
                                                $reply = "<center id='mggreen'><input type='checkbox' name='reply_".$r['id']."' value='1'></center>";
                                        }

                                        //---------------------------

                                        $global = '<center id="memgroup"><i>Global</i></center>';

                                        if ($r['upload_perms'] == '*')
                                        {
                                                $upload = $global;
                                        }
                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['upload_perms'] ) )
                                        {
                                                $upload = "<center id='memgroup'><input type='checkbox' name='upload_".$r['id']."' value='1' checked></center>";
                                        }
                                        else
                                        {
                                                $upload = "<center id='memgroup'><input type='checkbox' name='upload_".$r['id']."' value='1'></center>";
                                        }

                                        //---------------------------

//                                        if ($r['subwrap'] == 1 and $r['sub_can_post'] != 1)
//                                        {
//                                                $ADMIN->html .= $SKIN->add_td_basic( "&gt; ".$r['name'], 'left', 'catrow2' );
//                                        }
//                                        else
//                                        {
                                                $css = $r['subwrap'] == 1 ? 'catrow2' : '';

                                                $ADMIN->html .= $SKIN->add_td_row( array(
                                                                                                                           "<b> - ".$r['name']."</b>",
                                                                                                                           $read,
                                                                                                                           $reply,
                                                                                                                           $start,
                                                                                                                           $upload
                                                                                                         )   , $css   );
//                                        }

                                        if ( ( isset($children[ $r['id'] ]) ) and ( count ($children[ $r['id'] ]) > 0 ) )
                                        {
                                                foreach($children[ $r['id'] ] as $idx => $rd)
                                                {
                                                        $read   = "";
                                                        $start  = "";
                                                        $reply  = "";
                                                        $upload = "";
                                                        $global = "<center id='mgblue'><i>Global</i></center>";

                                                        if ($rd['read_perms'] == '*')
                                                        {
                                                                $read = $global;
                                                        }
                                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['read_perms'] ) )
                                                        {
                                                                $read = "<center id='mgblue'><input type='checkbox' name='read_".$rd['id']."' value='1' checked></center>";
                                                        }
                                                        else
                                                        {
                                                                $read = "<center id='mgblue'><input type='checkbox' name='read_".$rd['id']."' value='1'></center>";
                                                        }

                                                        //---------------------------

                                                        $global = "<center id='mgred'><i>Global</i></center>";

                                                        if ($rd['start_perms'] == '*')
                                                        {
                                                                $start = $global;
                                                        }
                                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['start_perms'] ) )
                                                        {
                                                                $start = "<center id='mgred'><input type='checkbox' name='start_".$rd['id']."' value='1' checked></center>";
                                                        }
                                                        else
                                                        {
                                                                $start = "<center id='mgred'><input type='checkbox' name='start_".$rd['id']."' value='1'></center>";
                                                        }

                                                        //---------------------------

                                                        $global = "<center id='mggreen'><i>Global</i></center>";

                                                        if ($rd['reply_perms'] == '*')
                                                        {
                                                                $reply = $global;
                                                        }
                                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['reply_perms'] ) )
                                                        {
                                                                $reply = "<center id='mggreen'><input type='checkbox' name='reply_".$rd['id']."' value='1' checked></center>";
                                                        }
                                                        else
                                                        {
                                                                $reply = "<center id='mggreen'><input type='checkbox' name='reply_".$rd['id']."' value='1'></center>";
                                                        }

                                                        //---------------------------

                                                        $global = "<center id='memgroup'><i>Global</i></center>";

                                                        if ($rd['upload_perms'] == '*')
                                                        {
                                                                $upload = $global;
                                                        }
                                                        else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['upload_perms'] ) )
                                                        {
                                                                $upload = "<center id='memgroup'><input type='checkbox' name='upload_".$rd['id']."' value='1' checked></center>";
                                                        }
                                                        else
                                                        {
                                                                $upload = "<center id='memgroup'><input type='checkbox' name='upload_".$rd['id']."' value='1'></center>";
                                                        }

                                                        //---------------------------

                                                        $ADMIN->html .= $SKIN->add_td_row( array(
                                                                                                                           "<b> --- ".$rd['name']."</b>",
                                                                                                                           $read,
                                                                                                                           $reply,
                                                                                                                           $start,
                                                                                                                           $upload
                                                                                                         ) , 'subforum'     );
                                                }
                                        }
                                }
                        }
                }

                $ADMIN->html .= $SKIN->end_form("Update Forum Permissions");

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();

        }


        function edit_name_perm()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //---------------------------
                // Check for legal ID
                //---------------------------

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve that group ID");
                }

                if ( $IN['perm_name'] == "" )
                {
                        $ADMIN->error("You must enter a name");
                }

                $gid = $IN['id'];

                //---------------------------

                $DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");

                if ( ! $gr = $DB->fetch_row() )
                {
                        $ADMIN->error("Not a valid group ID");
                }

                $DB->query("UPDATE ibf_forum_perms
                            SET perm_name='{$IN['perm_name']}'
                            WHERE perm_id='".$IN['id']."'");

                $ADMIN->save_log("Forum Access Permissions Name Edited for Mask: '{$gr['perm_name']}'");

                $ADMIN->done_screen("Forum Access Permissions Updated", "Permission Mask Control", "act=group&code=permsplash" );


        }



        function do_forum_perms()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //---------------------------
                // Check for legal ID
                //---------------------------

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve that group ID");
                }

                $gid = $IN['id'];

                //---------------------------

                $DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");

                if ( ! $gr = $DB->fetch_row() )
                {
                        $ADMIN->error("Not a valid group ID");
                }

                //---------------------------
                // Pull the forum data..
                //---------------------------

                $forum_q = $DB->query("SELECT id, read_perms, start_perms, reply_perms, upload_perms FROM ibf_forums ORDER BY position ASC");

                while ( $row = $DB->fetch_row( $forum_q ) )
                {

                        $read  = "";
                        $reply = "";
                        $start = "";
                        $upload = "";
                        //---------------------------
                        // Is this global?
                        //---------------------------

                        if ($row['read_perms'] == '*')
                        {
                                $read = '*';

                        }
                        else
                        {
                                //---------------------------
                                // Split the set IDs
                                //---------------------------

                                $read_ids = explode( ",", $row['read_perms'] );

                                if ( is_array($read_ids) )
                                {
                                   foreach ($read_ids as $i)
                                   {
                                           //---------------------------
                                           // If it's the current ID, skip
                                           //---------------------------

                                           if ($gid == $i)
                                           {
                                                   continue;
                                           }
                                           else
                                           {
                                                   $read .= $i.",";
                                           }
                                   }
                                }
                                //---------------------------
                                // Was the box checked?
                                //---------------------------

                                if ($IN[ 'read_'.$row['id'] ] == 1)
                                {
                                        // Add our group ID...

                                        $read .= $gid.",";
                                }

                                // Tidy..

                                $read = preg_replace( "/,$/", "", $read );
                                $read = preg_replace( "/^,/", "", $read );

                        }

                        //---------------------------
                        // Reply topics..
                        //---------------------------

                        if ($row['reply_perms'] == '*')
                        {
                                $reply = '*';
                        }
                        else
                        {
                                $reply_ids = explode( ",", $row['reply_perms'] );

                                if ( is_array($reply_ids) )
                                {

                                        foreach ($reply_ids as $i)
                                        {
                                                if ($gid == $i)
                                                {
                                                        continue;
                                                }
                                                else
                                                {
                                                        $reply .= $i.",";
                                                }
                                        }

                                }

                                if ($IN[ 'reply_'.$row['id'] ] == 1)
                                {
                                        $reply .= $gid.",";
                                }

                                $reply = preg_replace( "/,$/", "", $reply );
                                $reply = preg_replace( "/^,/", "", $reply );
                        }

                        //---------------------------
                        // Start topics..
                        //---------------------------

                        if ($row['start_perms'] == '*')
                        {
                                $start = '*';
                        }
                        else
                        {
                                $start_ids = explode( ",", $row['start_perms'] );

                                if ( is_array($start_ids) )
                                {

                                        foreach ($start_ids as $i)
                                        {
                                                if ($gid == $i)
                                                {
                                                        continue;
                                                }
                                                else
                                                {
                                                        $start .= $i.",";
                                                }
                                        }

                                }

                                if ($IN[ 'start_'.$row['id'] ] == 1)
                                {
                                        $start .= $gid.",";
                                }

                                $start = preg_replace( "/,$/", "", $start );
                                $start = preg_replace( "/^,/", "", $start );
                        }

                        //---------------------------
                        // Upload topics..
                        //---------------------------

                        if ($row['upload_perms'] == '*')
                        {
                                $upload = '*';
                        }
                        else
                        {
                                $upload_ids = explode( ",", $row['upload_perms'] );

                                if ( is_array($upload_ids) )
                                {

                                        foreach ($upload_ids as $i)
                                        {
                                                if ($gid == $i)
                                                {
                                                        continue;
                                                }
                                                else
                                                {
                                                        $upload .= $i.",";
                                                }
                                        }

                                }

                                if ($IN[ 'upload_'.$row['id'] ] == 1)
                                {
                                        $upload .= $gid.",";
                                }

                                $upload = preg_replace( "/,$/", "", $upload );
                                $upload = preg_replace( "/^,/", "", $upload );
                        }

                        //---------------------------
                        // Update the DB...
                        //---------------------------

                        if (! $new_q = $DB->query("UPDATE ibf_forums
                                                   SET
                                                        read_perms='$read',
                                                        reply_perms='$reply',
                                                        start_perms='$start',
                                                        upload_perms='$upload'
                                                   WHERE id='".$row['id']."'") )
                        {
                                die ("Update query failed on Forum ID ".$row['id']);
                        }

                }

                $ADMIN->save_log("Forum Access Permissions Edited for Mask: '{$gr['perm_name']}'");

                $ADMIN->done_screen("Forum Access Permissions Updated", "Permission Mask Control", "act=group&code=permsplash" );

        }

        //+---------------------------------------------------------------------------------
        //
        // Delete a group
        //
        //+---------------------------------------------------------------------------------

        function delete_form()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the group ID, please try again");
                }

                if ($IN['id'] < 5)
                {
                        $ADMIN->error("You can not move the preset groups. You can rename them and edit the functionality");
                }

                $ADMIN->page_title = "Deleting a User Group";

                $ADMIN->page_detail = "Please check to ensure that you are attempting to remove the correct group.";


                //+-------------------------------

                $DB->query("SELECT COUNT(id) as users FROM ibf_members WHERE mgroup='".$IN['id']."'");
                $black_adder = $DB->fetch_row();

                if ($black_adder['users'] < 1)
                {
                        $black_adder['users'] = 0;
                }

                $DB->query("SELECT g_title FROM ibf_groups WHERE g_id='".$IN['id']."'");
                $group = $DB->fetch_row();

                //+-------------------------------

                $DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id <> '".$IN['id']."'");

                $mem_groups = array();

                while ( $r = $DB->fetch_row() )
                {
                        $mem_groups[] = array( $r['g_id'], $r['g_title'] );
                }

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'  ),
                                                                                                  2 => array( 'act'   , 'group'     ),
                                                                                                  3 => array( 'id'    , $IN['id']   ),
                                                                                                  4 => array( 'name'  , $group['g_title'] ),
                                                                             )      );



                //+-------------------------------

                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Removal Confirmation: ".$group['g_title'] );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Number of users in this group</b>" ,
                                                                                                  "<b>".$black_adder['users']."</b>",
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Move users in this group to...</b>" ,
                                                                                                  $SKIN->form_dropdown("to_id", $mem_groups )
                                                                             )      );

                $ADMIN->html .= $SKIN->end_form("Delete this group");

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();

        }

        function do_delete()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['id'] == "")
                {
                        $ADMIN->error("Could not resolve the group ID, please try again");
                }

                if ($IN['to_id'] == "")
                {
                        $ADMIN->error("No move to group ID was specified. /me cries.");
                }

                // Check to make sure that the relevant groups exist.

                $DB->query("SELECT g_id FROM ibf_groups WHERE g_id IN(".$IN['id'].",".$IN['to_id'].")");

                if ( $DB->get_num_rows() != 2 )
                {
                        $ADMIN->error("Could not resolve the ID's passed to group deletion");
                }

                $DB->query("UPDATE ibf_members SET mgroup='".$IN['to_id']."' WHERE mgroup='".$IN['id']."'");

                $DB->query("DELETE FROM ibf_groups WHERE g_id='".$IN['id']."'");

                // Look for promotions in case we have members to be promoted to this group...

                $prq = $DB->query("SELECT g_id
                                   FROM ibf_groups
                                   WHERE g_promotion LIKE '{$IN['id']}&%'");

                while ( $row = $DB->fetch_row($prq) )
                {
                        $nq = $DB->query("UPDATE ibf_groups
                                          SET g_promotion='-1&-1'
                                          WHERE g_id='".$row['g_id']."'");
                }

                // Remove from moderators table

                $DB->query("DELETE FROM ibf_moderators WHERE is_group=1 AND group_id=".$IN['id']);

                $ADMIN->save_log("Member Group '{$IN['name']}' removed");

                $ADMIN->done_screen("Group Removed", "Group Control", "act=group" );

        }


        //+---------------------------------------------------------------------------------
        //
        // Save changes to DB
        //
        //+---------------------------------------------------------------------------------

        function save_group($type='edit')
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['g_title'] == "")
                {
                        $ADMIN->error("You must enter a group title.");
                }

                if ($type == 'edit')
                {
                        if ($IN['id'] == "")
                        {
                                $ADMIN->error("Could not resolve the group id");
                        }
                }

                $db_string = array(
                                'gname'                 => $IN['g_title'],
                                'gedit_post'            => $IN['gedit_post'],
                                'gadd_post'             => $IN['gadd_post'],
                                'gapprove_post'         => $IN['gapprove_post'],
                                'gdelete_post'          => $IN['gdelete_post'],
                                'gpost_comment'         => $IN['gpost_comment'],
                                'gview_comments'        => $IN['gview_comments'],
                                'gedit_comment'         => $IN['gedit_comment'],
                                'gview_posts'           => $IN['gview_posts'],
                                'gdelete_comments'      => $IN['gdelete_comments'],
                                'gmove_posts'           => $IN['gmove_posts'],
                                'g_add_attach'          => $IN['g_add_attach'],
                                'g_delete_attach'       => $IN['g_delete_attach'],
                                'g_max_attach_size'     => $IN['g_max_attach_size'],
//                                ''                      => $IN[''],
                                  );


                if ( $type == 'edit' )
                {
                        $rstring = $DB->compile_db_update_string( $db_string );

                        $sql= "UPDATE ibf_cms_groups
                               SET $rstring
                               WHERE gid='".$IN['id']."'";

                        $DB->query($sql);

                        $ADMIN->save_log("Edited D-Site Group '{$IN['g_title']}'");

                        $ADMIN->done_screen("Group Edited", "Group Control", "act=dsite_groups" );

                } else
                {
                        $rstring = $DB->compile_db_insert_string( $db_string );

                        $DB->query("INSERT INTO ibf_cms_groups
                                        (" .$rstring['FIELD_NAMES']. ")
                                    VALUES
                                        (". $rstring['FIELD_VALUES'] .")");

                        $ADMIN->save_log("Added CMS Group '{$IN['g_title']}'");

                        $ADMIN->done_screen("D-Site Group Added", "Group Control", "act=dsite_groups" );
                }
        }

        function clean_perms($str)
        {
                $str = preg_replace( "/,$/", "", $str );
                $str = str_replace(  ",,", ",", $str );

                return $str;
        }

        //+---------------------------------------------------------------------------------
        //
        // Add / edit group
        //
        //+---------------------------------------------------------------------------------

        function group_form($type='edit')
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                $all_groups = array( 0 => array ('none', 'Don\'t Promote') );

                if ($type == 'edit')
                {
                        if ($IN['id'] == "")
                        {
                                $ADMIN->error("No group id to select from the database, please try again.");
                        }

                        if ( $INFO['admin_group'] == $IN['id'] )
                        {
                                if ( $MEMBER['mgroup'] != $INFO['admin_group'] )
                                {
                                        $ADMIN->error("Sorry, you are unable to edit that group as it's the root admin group");
                                }
                        }

                        $form_code = 'doedit';
                        $button    = 'Complete Edit';
                } else
                {
                        $form_code = 'doadd';
                        $button    = 'Создать группу';
                }

                $DB->query("SELECT *, gname AS g_title FROM ibf_cms_groups WHERE gid='".$IN['id']."'");

                $group = $DB->fetch_row();


                //-------------------------------------------

                if ($type == 'edit')
                {
                        $ADMIN->page_title = "Editing User Group ".$group['g_title'];

                }
                else
                {
                        $ADMIN->page_title = 'Adding a new user group';
                        $group['g_title'] = 'New Group';
                }

                $guest_legend = "";

                if ($group['g_id'] == $INFO['guest_group'])
                {
                        $guest_legend = "</b><br><i>(Does not apply to guests)</i>";
                }

                $ADMIN->page_detail = "Please double check the information before submitting the form.";

                $ADMIN->html .= $SKIN->start_form(
                                array(
                                        1 => array( 'code'  , $form_code  ),
                                        2 => array( 'act'   , 'dsite_groups'     ),
                                        3 => array( 'id'    , $IN['id']   ),
                                        )
                                      );


                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------

                $prefix = preg_replace( "/'/", "&#39;", $group['prefix'] );
                $prefix = preg_replace( "/</", "&lt;" , $prefix          );
                $suffix = preg_replace( "/'/", "&#39;", $group['suffix'] );
                $suffix = preg_replace( "/</", "&lt;" , $suffix          );

                $ADMIN->html .= $SKIN->start_table( "Global Settings", "Basic Group Settings" );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Название группы</b>" ,
                                                                                                  $SKIN->form_input("g_title", $group['g_title'] )
                                                                             )      );
                $ADMIN->html .= $SKIN->end_table();

                //+-------------------------------

                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------


                $ADMIN->html .= $SKIN->start_table( "Global Permissions", "Restricting what this group can do" );



                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить просмотр статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("gview_posts", $group['gview_posts'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить добавление статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("gadd_post", $group['gadd_post'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить редактирование статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("gedit_post", $group['gedit_post'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить подтверждение статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("gapprove_post", $group['gapprove_post'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить перемещение статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("gmove_posts", $group['gmove_posts'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить прикрепление файлов к статьям?</b>" ,
                                                                                                  $SKIN->form_yes_no("g_add_attach", $group['g_add_attach'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить удаление прикрепленных файлов?</b>" ,
                                                                                                  $SKIN->form_yes_no("g_delete_attach", $group['g_delete_attach'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальный размер прикрепляемого файла?</b>" ,
                                                                                                  $SKIN->form_input("g_max_attach_size", $group['g_max_attach_size'] )
                                                                             )      );


                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить удаление статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("gdelete_post", $group['gdelete_post'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить просмотр коментариев?</b>" ,
                                                                                                  $SKIN->form_yes_no("gview_comments", $group['gview_comments'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить добавление коментариев?</b>" ,
                                                                                                  $SKIN->form_yes_no("gpost_comment", $group['gpost_comment'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить редактирование коментариев?</b>" ,
                                                                                                  $SKIN->form_yes_no("gedit_comment", $group['gedit_comment'] )
                                                                             )      );


                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить удаление коментариев?</b>" ,
                                                                                                  $SKIN->form_yes_no("gdelete_comments", $group['gdelete_comments'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->html .= $SKIN->end_form($button);

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();


        }

        //+---------------------------------------------------------------------------------
        //
        // Show "Management Screen
        //
        //+---------------------------------------------------------------------------------

        function main_screen()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                $ADMIN->page_title = "User Groups";

                $ADMIN->page_detail = "User Grouping is a quick and powerful way to organise your members. There are 4 preset groups that you cannot remove (Validating, Guest, Member and Admin) although you may edit these at will. A good example of user grouping is to set up a group called 'Moderators' and allow them access to certain forums other groups do not have access to.<br>Forum access allows you to make quick changes to that groups forum read, write and reply settings. You may do this on a forum per forum basis in forum control.";

                $g_array = array();

                $SKIN->td_header[] = array( "Group Title"    , "30%" );
                $SKIN->td_header[] = array( "Edit Group"     , "20%" );
                $SKIN->td_header[] = array( "Delete"         , "10%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "User Group Management" );

                $DB->query("SELECT * FROM ibf_cms_groups");

                while ( $r = $DB->fetch_row() )
                {

                        $del  = "";
                        $mod  = '&nbsp;';
                        $adm  = '&nbsp;';

                        if ($r['g_id'] > 4)
                        {
                                $del = "<center><a href='{$ADMIN->base_url}&act=dsite_group&code=delete&id=".$r['gid']."'>Удалить</a></center>";
                        }

                        $ADMIN->html .= $SKIN->add_td_row( array( "<b>{$r['gname']}</b>" ,
                                                                                                      "<center><a href='{$ADMIN->base_url}&act=dsite_groups&code=edit&id=".$r['gid']."'>Изменить группу</a></center>",
                                                                                                      $del

                                                                             )      );

                        $g_array[] = array( $r['g_id'], $r['g_title'] );
                }

                $ADMIN->html .= $SKIN->add_td_basic("&nbsp;", "center", "title");

                $ADMIN->html .= $SKIN->end_table();

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add' ),
                                                                                                  2 => array( 'act'   , 'dsite_groups'     ),
                                                                             )      );

                //+-------------------------------

                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Add a new member group" );

                $ADMIN->html .= $SKIN->end_form("Set up New Group");

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();


        }


}

function subforums_addpreview($children, $id, $t_char='--') {
        $the_html = '';

        if (count($children[$id]) > 0)
        {
                foreach($children[$id] as $ii => $tt)
                {
                        $the_html .= '&nbsp;&nbsp;'.$t_char.$tt[1];
                        $the_html .= $this->subforums_addpreview($children, $tt[0], $t_char.'--');
                }
                return $the_html;
        }
}


?>
