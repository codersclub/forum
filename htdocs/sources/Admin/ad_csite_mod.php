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
|   > Admin D-Site Functions
|   > Moderators management routine
|   > Module written by Anton
|   > Date started: 22th may 2005
|
|   > Module Version Number: 1.0.3
|
|   > Copyright (c) Anton, 2004-2005
|   > E-mail: anton@sources.ru
+--------------------------------------------------------------------------
*/



$idx = new ad_csite_mod();


class ad_csite_mod {

        var $base_url;

        function ad_csite_mod() {
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
                        case 'add':
                                $this->add_one();
                                break;
                        case 'add_two':
                                $this->add_two();
                                break;
                        case 'add_final':
                                $this->mod_form('add');
                                break;
                        case 'doadd':
                                $this->add_mod();
                                break;

                        case 'edit':
                                $this->mod_form('edit');
                                break;

                        case 'doedit':
                                $this->do_edit();
                                break;

                        case 'remove':
                                $this->do_delete();
                                break;

                        default:
                                $this->show_list();
                                break;
                }

        }

        //+---------------------------------------------------------------------------------
        //
        // DELETE MODERATOR
        //
        //+---------------------------------------------------------------------------------

        function do_delete()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['mid'] == "")
                {
                        $ADMIN->error("You did not choose a valid moderator ID");
                }

                $DB->query("SELECT * FROM ibf_cms_moderators WHERE mid='".$IN['mid']."'");
                $mod = $DB->fetch_row();

                if ( $mod['is_group'] )
                {
                        $name = 'Group: '.$mod['group_name'];
                }
                else
                {
                        $name = $mod['member_name'];
                }

                $DB->query("DELETE FROM ibf_cms_moderators WHERE mid='".$IN['mid']."'");

                $ADMIN->save_log("Removed D-Site Moderator '{$name}'");

                $ADMIN->done_screen("Moderator Removed", "Moderator Control", "act=csite_mod" );

        }


        //+---------------------------------------------------------------------------------
        //
        // EDIT MODERATOR
        //
        //+---------------------------------------------------------------------------------

        function do_edit()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['mid'] == "")
                {
                        $ADMIN->error("You did not choose a valid moderator ID");
                }

                $DB->query("SELECT member_name FROM ibf_cms_moderators WHERE mid='".$IN['mid']."'");
                $mod = $DB->fetch_row();

                //--------------------------------------
                // Build Mr Hash
                //--------------------------------------

                $mr_hash = array(
                                                        'forum_id'     => $IN['forum_id'],
                                                        'edit_post'    => $IN['edit_post'],
                                                        'delete_post'  => $IN['delete_post'],
                                                        'approve_post' => $IN['approve_post'],
                                                );



                $db_string = $DB->compile_db_update_string( $mr_hash );

                $DB->query("UPDATE ibf_cms_moderators SET $db_string WHERE mid='".$IN['mid']."'");

                $ADMIN->save_log("Edited D-Site Moderator '{$mod['member_name']}'");

                $ADMIN->done_screen("Moderator Edited", "Moderator Control", "act=csite_mod" );

        }

        //+---------------------------------------------------------------------------------
        //
        // ADD MODERATOR
        //
        //+---------------------------------------------------------------------------------

        function add_mod()
        {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['fid'] == "")
                {
                        $ADMIN->error("You did not choose any forums to add this member to");
                }

                //--------------------------------------
                // Build Mr Hash
                //--------------------------------------

                $mr_hash = array(
                                                        'edit_post'    => $IN['edit_post'],
                                                        'delete_post'  => $IN['delete_post'],
                                                        'approve_post'      => $IN['approve_post'],
                                                );

                $forum_ids = array();

                $DB->query("SELECT id FROM ibf_cms_uploads_cat WHERE id IN(".$IN['fid'].")");

                while( $i = $DB->fetch_row() )
                {
                        $forum_ids[ $i['id'] ] = $i['id'];
                }



                if ($IN['mem'] == "") {

                        $ADMIN->error("You did not choose a member to add as a moderator");
                }

                $DB->query("SELECT id, name from ibf_members WHERE id='".$IN['mem']."'");

                if ( ! $mem = $DB->fetch_row() ) {

                        $ADMIN->error("Could not match that member name so there.");
                }

                //---------------------------------------
                // Already using this member on this forum?
                //---------------------------------------

                $DB->query("SELECT * FROM ibf_cms_moderators WHERE forum_id IN(".$IN['fid'].") and member_id={$IN['mem']}");

                while( $f = $DB->fetch_row() ) {

                        unset($forum_ids[ $f['forum_id'] ]);
                }

                $mr_hash['member_name'] = $mem['name'];
                $mr_hash['member_id']   = $mem['id'];
                $mr_hash['is_group']    = 0;

                $ad_log = "Added Member '{$mem['name']}' as a moderator";

                //--------------------------------------
                // Check for legal forums
                //--------------------------------------

                if ( count($forum_ids) == 0)
                {
                        $ADMIN->error("You did not select any forums that do not have this group or member already moderating.");
                }

                //--------------------------------------
                // Loopy loopy
                //--------------------------------------

                foreach ($forum_ids as $cartman)
                {
                        $mr_hash['forum_id'] = $cartman;

                        $kenny = $DB->compile_db_insert_string( $mr_hash );

                        $DB->query("INSERT INTO ibf_cms_moderators (" .$kenny['FIELD_NAMES']. ") VALUES (". $kenny['FIELD_VALUES'] .")");
                }

                $ADMIN->save_log($ad_log);

                $ADMIN->done_screen("Moderator Added", "Moderator Control", "act=csite_mod" );

        }

        //+---------------------------------------------------------------------------------
        //
        // ADD FINAL, display the add / edit form
        //
        //+---------------------------------------------------------------------------------

        function mod_form( $type='add' ) {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $NAV, $USR;

                $group = array();

                if ($type == 'add')
                {
                        if ($IN['fid'] == "")
                        {
                                $ADMIN->error("You did not choose any forums to add this member to");
                        }

                        $mod   = array();
                        $names = array();

                        //--------------------------------------

                        $DB->query("SELECT name FROM ibf_forums WHERE id IN(".$IN['fid'].")");

                        while ( $r = $DB->fetch_row() )
                        {
                                $names[] = $r['name'];
                        }

                        $thenames = implode( ", ", $names );

                        //--------------------------------------

                        $button = "Add this moderator";

                        $form_code = 'doadd';

                        if ($IN['mod_type'] == 'group')
                        {
                                $DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id='".$IN['mod_group']."'");

                                if (! $group = $DB->fetch_row() )
                                {
                                        $ADMIN->error("Could not find that group to add as a moderator");
                                }

                                $ADMIN->page_detail = "Adding <b>group: {$group['g_title']}</b> as a moderator to: $thenames";
                                $ADMIN->page_title = "Add a moderator group";
                        }
                        else
                        {

                                if ($IN['MEMBER_ID'] == "")
                                {
                                        $ADMIN->error("Could not resolve the member id bucko");
                                }
                                else
                                {
                                        $DB->query("SELECT name, id FROM ibf_members WHERE id='".$IN['MEMBER_ID']."'");

                                        if ( ! $mem = $DB->fetch_row() )
                                        {
                                                $ADMIN->error("That member ID does not resolve");
                                        }

                                        $member_id   = $mem['id'];
                                        $member_name = $mem['name'];
                                }

                                $ADMIN->page_detail = "Adding a $member_name as a moderator to: $thenames";
                                $ADMIN->page_title = "Add a moderator";

                        }

                }
                else
                {
                        if ($IN['mid'] == "")
                        {
                                $ADMIN->error("You must choose a valid moderator to edit.");
                        }

                        $button    = "Редактировать";

                        $form_code = "doedit";

                        $ADMIN->page_title  = "Редактирование прав модератора";
                        $ADMIN->page_detail = "";

                        $DB->query("SELECT * from ibf_cms_moderators WHERE mid='".$IN['mid']."'");

                        if ( ! $mod = $DB->fetch_row() )
                        {
                                $ADMIN->error("Could not retrieve that moderators record");
                        }

                        $member_id   = $mod['member_id'];
                        $member_name = $mod['member_name'];
                }


                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'     , $form_code ),
                                                                                                  2 => array( 'act'      , 'csite_mod'      ),
                                                                                                  3 => array( 'mid'      , $mod['mid']),
                                                                                                  4 => array( 'fid'      , $IN['fid'] ),
                                                                                                  5 => array( 'mem'      , $member_id ),
                                                                                                  6 => array( 'mod_type' , $IN['mod_type'] ),
                                                                                                  7 => array( 'gid'      , $group['g_id'] ),
                                                                                                  8 => array( 'gname'    , $group['g_name'] ),
                                                                             )      );

                //+-------------------------------

                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Настройка доступа" );

                //+-------------------------------

                if ($type == 'edit')
                {
                        $cats = $NAV->build_cat_list_select_ad( 0, $mod['forum_id'] );

                        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Модерируемый раздел...</b>" ,
                                                                  "<select name='forum_id'>" . $cats . "</select>",
                                                                )
                                                         );
                }

                //+-------------------------------

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить изменять статьи пользователей?</b>" ,
                                                                                                  $SKIN->form_yes_no("edit_post", $mod['edit_post'] )
                                                                             )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить удаление статей пользователей?</b>" ,
                                                                                                  $SKIN->form_yes_no("delete_post", $mod['delete_post'] )
                                                                             )      );


                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить отклонение/разрешение статей?</b>" ,
                                                                                                  $SKIN->form_yes_no("approve_post", $mod['approve_post'] )
                                                                             )      );


                $ADMIN->html .= $SKIN->end_table();

                //+-------------------------------


                $ADMIN->html .= $SKIN->end_form($button);

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();


        }


        //+---------------------------------------------------------------------------------
        //
        // ADD step one: Look up a member
        //
        //+---------------------------------------------------------------------------------

        function add_one() {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                //-----------------------------
                // Grab and serialize the input
                //-----------------------------

                $fid      = "";
                $fidarray = array();

                foreach ($IN as $k => $v)
                {
                        if ( preg_match( "/^add_(\d+)$/", $k, $match ) )
                        {
                                if ($IN[ $match[0] ])
                                {
                                        $fidarray[] = $match[1];
                                }
                        }
                }

                if ( count($fidarray) < 1 )
                {
                        $ADMIN->error("Не выбран раздел сайта, в который будут длбавлены модераторы!");
                }

                $fid = implode( "," ,$fidarray );

                $ADMIN->page_title = "Добавление модератора";

                $ADMIN->page_detail = "Введите имя модератора, который будет добавлен в выбранные разделы.";

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add_two' ),
                                                                                                  2 => array( 'act'   , 'csite_mod'     ),
                                                                                                  3 => array( 'fid'   , $fid      ),
                                                                                                  4 => array( 'mod_type' , $IN['mod_type'] ),
                                                                             )      );

                //+-------------------------------

                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Поиск зарегистрированного пользователя" );


                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Введите имя или часть имени</b>" ,
                                                                                                          $SKIN->form_input( "USER_NAME" )
                                                                                         )      );

                $ADMIN->html .= $SKIN->end_form("Найти");

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();


        }

        //+---------------------------------------------------------------------------------
        //
        // REFINE MEMBER SEARCH
        //
        //+---------------------------------------------------------------------------------

        function add_two() {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

                if ($IN['USER_NAME'] == "")
                {
                        $ADMIN->error("You didn't choose a member name to look for!");
                }

                $DB->query("SELECT id, name FROM ibf_members WHERE name LIKE '".$IN['USER_NAME']."%'");

                if (! $DB->get_num_rows() )
                {
                        $ADMIN->error("Sorry, we could not find any members that matched the search string you entered");
                }

                $form_array = array();

                while ( $r = $DB->fetch_row() )
                {
                        $form_array[] = array( $r['id'] , $r['name'] );
                }



                $ADMIN->page_title = "Добавление модератора";

                $ADMIN->page_detail = "Выберите имя участника из списка для того чтобы добавить его в список модераторов раздела";

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add_final' ),
                                                                                                  2 => array( 'act'   , 'csite_mod'    ),
                                                                                                  3 => array( 'fid'   , $IN['fid']),
                                                                             )      );

                //+-------------------------------

                $SKIN->td_header[] = array( "&nbsp;"  , "40%" );
                $SKIN->td_header[] = array( "&nbsp;"  , "60%" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Поиск зарегистрированного участника" );


                $ADMIN->html .= $SKIN->add_td_row( array( "<b>Выберите из списка...</b>" ,
                                                                                                  $SKIN->form_dropdown( "MEMBER_ID", $form_array )
                                                                             )      );

                $ADMIN->html .= $SKIN->end_form("Далее");

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();

        }


        //+---------------------------------------------------------------------------------
        //
        // SHOW LIST
        // Renders a complete listing of all the forums and categories w/mods.
        //
        //+---------------------------------------------------------------------------------

        function show_list() {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $NAV;

                $ADMIN->page_title = "Упарвление модераторами D-Site";
                $ADMIN->page_detail  = "Наследование прав не реализовано! В каждый раздел добавляются свои модераторы.";

                $ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add' ),
                                                                                                  2 => array( 'act'   , 'csite_mod'   ),
                                                                             )      );

                $SKIN->td_header[] = array( "Добавить"                , "7%" );
                $SKIN->td_header[] = array( "Раздел сайта"         , "38%" );
                $SKIN->td_header[] = array( "Текущие модераторы" , "55%" );

                $ADMIN->html .= $SKIN->start_table( "Список модераторов" );

                //------------------------------------

                $cats   = array();
                $forums = array();
                $mods   = array();
                $children = array();

                //--------------------------------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_moderators ");

                while ($dbres = $DB->fetch_row()) {

                        $mods[] = $dbres;
                }

                //--------------------------------------------------------------

                $ADMIN->html .= $NAV->build_cat_list_mod();

                $ADMIN->html .= $SKIN->end_form("Добавить модераторов в выбранные категории");

                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->output();

        }

}


?>
