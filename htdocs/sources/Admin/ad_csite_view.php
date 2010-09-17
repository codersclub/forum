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
|   > Category management routine
|   > Module written by Anton
|   > Date started: 2nd june 2005
|
|   > Module Version Number: 1.0.0
|
|   > Copyright (c) Anton, 2004-2005
|   > E-mail: anton@sources.ru
+--------------------------------------------------------------------------
*/





$idx = new ad_csite_view();

class ad_csite_view {

        var $base_url;

        function ad_csite_view() {
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
                        case 'get_html':
                                $this->get_html();
                                break;
                        //+-------------------------
                        case 'edit_html':
                                $this->edit_html();
                                break;
                        //+-------------------------
                        case 'edit':
                                $this->edit_form();
                                break;
                        case 'doedit':
                                $this->do_edit();
                                break;
                        //+-------------------------
                        default:
                                $this->show_list();
                                break;
                }

        }


       //+---------------------------------------------------------------------------------

        function do_edit() {
                global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $NAV;

                foreach ( $IN as $num => $in ) {

                     if ( is_array( $in ) ) {

                             //----------------------------------------------
                             //  create and write DB string
                             //----------------------------------------------

                             $db_string = $DB->compile_db_update_string(
                                                                         array
                                                                               (
                                                                                 'pid'     => intval($in['position']),
                                                                                 'b_order' => intval($in['order']),
                                                                                 'bcaption'=> $in['bcaption'],
                                                                                 'visible' => intval($in['visible']),
                                                                                 'break'   => intval($in['break']),
                                                                               )
                                                                        );

                             $DB->query("UPDATE ibf_cms_views SET " . $db_string ." WHERE id = " . $num );

                     }

                }


                $DB->query("UPDATE ibf_cms_views SET bname = '{$IN['block_width_0']}', bdescription = '{$IN['block_width_1']}', bcaption = '{$IN['block_width_2']}' WHERE id = 999" );

                //----------------------------------------------
                //  finished!
                //----------------------------------------------

                $ADMIN->save_log("Был изменен внешний вид сайта.");

                $ADMIN->done_screen("Ввнешний вид сайта изменен.", "Управление внешним видом D-Site", "act=csite_view" );

        }

          //--------------------------------------------------------------------
          //  show list of categories with subcategories
          //--------------------------------------------------------------------

          function show_list() {
          global $SKIN, $ADMIN, $DB;

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form(
                                                     array(
                                                            1 => array( 'code'  , 'doedit'  ),
                                                            2 => array( 'act'   , 'csite_view'  ),
                                                          )
                                                 );

                //+-------------------------------

                $DB->query("SELECT * FROM ibf_cms_views ORDER BY id");

                while ( $dbres = $DB->fetch_row() ) {

                        if ( $dbres['id'] != '999' ) {

                                $site_blocks[] = $dbres;
                        } else {

                                $block_width[0] = $dbres['bname'];
                                $block_width[1] = $dbres['bdescription'];
                                $block_width[2] = $dbres['bcaption'];
                        }
                }

                //+-------------------------------


                $ADMIN->page_title = "Настройки внешнего вида сайта.";

                $ADMIN->page_detail = "";

                $SKIN->td_header[] = array( "Ширина первой колонки"  );
                $SKIN->td_header[] = array( "Ширина центральной колонки" );
                $SKIN->td_header[] = array( "Ширина второй колонки" );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Глобальные настройки" );

                $ADMIN->html .= $SKIN->add_td_row( array
                                                         (
                                                            "&nbsp;" .
                                                            $SKIN->form_input("block_width_0", $block_width[0]),
                                                            "&nbsp;" .
                                                            $SKIN->form_input("block_width_1", $block_width[1]),
                                                            "&nbsp;" .
                                                            $SKIN->form_input("block_width_2", $block_width[2]),

                                                         )
                                                 );

                $ADMIN->html .= $SKIN->end_table();

                //+-------------------------------


                $SKIN->td_header[] = array( "Название блока"  , "30%" );
                $SKIN->td_header[] = array( "Заголовок блока"  , "25%" );
                $SKIN->td_header[] = array( "Порядок сортировки"  , "10%" );
                $SKIN->td_header[] = array( "Положение на странице"  , "15%" );
                $SKIN->td_header[] = array( "Отображать на сайте"  , "8%" );
                $SKIN->td_header[] = array( "Добавить разделитель &lt;br&gt;"  , "12%" );




                for ( $i = 1; $i <= count($site_blocks); $i++ ) {

                        $block_order[] = array($i, $i);
                }


                $num = 0;
                foreach ( $site_blocks as $num => $site_block ) {

                        $block_position = array();

                        $block_position[] = array(0, "первый блок");
                        $block_position[] = array(1, "центральный блок");
                        $block_position[] = array(2, "второй блок");

                        if ( $site_block['id'] == '1' ) {

                                $ADMIN->html .= $SKIN->start_table( "Настройки блоков" );
                        }

                        if ( $site_block['id'] == '1000' ) {

                                $ADMIN->html .= $SKIN->end_table();

                                $SKIN->td_header[] = array( "Название блока"  , "30%" );
                                $SKIN->td_header[] = array( "Отображать на сайте"  , "8%" );
                                $SKIN->td_header[] = array( "Добавить разделитель &lt;br&gt;"  , "12%" );

                                $ADMIN->html .= $SKIN->start_table( "Настройки блоков2" );
                        }



                        if ( $site_block['id'] < '999' ) {

                        $ADMIN->html .= $SKIN->add_td_row( array
                                                         (
                                                            "<b>{$site_block['bdescription']}</b><br>(<a href='{$ADMIN->base_url}&act=csite_view&code=get_html&f={$site_block['bname']}'>HTML-код функции</a>)" ,
                                                            $SKIN->form_input("{$site_block['id']}[bcaption]", $site_block['bcaption']),
                                                            $SKIN->form_dropdown( "{$site_block['id']}[order]",
                                                                                                  $block_order,
                                                                                                  $site_block['b_order']
                                                                                                ),
                                                            $SKIN->form_dropdown( "{$site_block['id']}[position]",
                                                                                                  $block_position,
                                                                                                  $site_block['pid']
                                                                                                ),
                                                            $SKIN->form_checkbox("{$site_block['id']}[visible]}", $site_block['visible']),

                                                            $SKIN->form_dropdown( "{$site_block['id']}[break]}",
                                                                                   array
                                                                                        (
                                                                                          0 => array( 0, 'Не добавлять' ),
                                                                                          1 => array( 1, 'Перед блоком' ),
                                                                                          2 => array( 2, 'После блока' ),
                                                                                         ),
                                                                                   $site_block['break']
                                                                                ),
                                                         )
                                                 );

                        }

                        if ( $site_block['id'] >= '1000' ) {

                        $ADMIN->html .= $SKIN->add_td_row( array
                                                         (
                                                            "<b>{$site_block['bdescription']}</b><br>(<a href='{$ADMIN->base_url}&act=csite_view&code=get_html&f={$site_block['bname']}''>HTML-код функции</a>)" ,
                                                            $SKIN->form_checkbox("{$site_block['id']}[visible]}", $site_block['visible']),
                                                            $SKIN->form_dropdown( "{$site_block['id']}[break]}",
                                                                                   array
                                                                                        (
                                                                                          0 => array( 0, 'Не добавлять' ),
                                                                                          1 => array( 1, 'Перед блоком' ),
                                                                                          2 => array( 2, 'После блока' ),
                                                                                         ),
                                                                                   $site_block['break']
                                                                                ),

                                                         )
                                                 );
                        }

                        }





                $ADMIN->html .= $SKIN->end_table();


                $ADMIN->html .= $SKIN->start_table( "Принять изменения" );
                $ADMIN->html .= $SKIN->end_form("Принять изменения");
                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->html .= $SKIN->end_table();


                $ADMIN->output();
          }


        function form_radio( $name, $checked ) {

                if ($checked == 1)
                {

                        return "<input type='radio' name='$name' checked='checked'>";
                }
                else
                {
                        return "<input type='radio' name='$name'>";
                }

        }

        function get_html() {
        global $SKIN, $ADMIN, $DB, $IN, $ibforums;


                //--------------------------------------------------------------
                // get skins directory
                //--------------------------------------------------------------

                $DB->query("SELECT default_set FROM ibf_skins");

                $dbres = $DB->fetch_row();

                $skin_path = $ibforums->vars['base_dir'] . "Skin/s" . $dbres['default_set'] . "/";

        		//--------------------------------------------------------------
        		// select what skin file to use
        		//--------------------------------------------------------------

        		switch ( $IN['f'] ) {

        			case "main_menu" :
        								$skin_file = "skin_csite_mod_nav.php";

        			default : "";
        		}

        		//--------------------------------------------------------------
        		// what skin to use witch path
        		//--------------------------------------------------------------

        		$used_skin = $skin_path . $skin_file;


                //--------------------------------------------------------------
                // get used skin functions list
                //--------------------------------------------------------------

                if ( !$IN['f'] ) {

                	exit("Internal error - no function name specified!");
        		}

        		$DB->query(" SELECT used_func FROM ibf_cms_views WHERE bname = '{$IN['f']}' ");

        		while ( $dbres = $DB->fetch_row() ) {

        			$functions = explode("|", $dbres['used_func']);
        		}

        		//--------------------------------------------------------------
        		// make hiddens
        		//--------------------------------------------------------------

        		foreach ( $functions as $num => $function ) {

        			$f_hiddens[$num+2][] = "f_hiddens[]";
        			$f_hiddens[$num+2][] = $function;
        		}

        		print//_r($f_hiddens);

        		//--------------------------------------------------------------
        		// open file
        		//--------------------------------------------------------------

        		$lines = file( $used_skin );

        		//--------------------------------------------------------------
        		// parse html
        		//--------------------------------------------------------------

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_form(
                                                     array(
                                                            1 => array( 'code'  , 'edit_html'  ),
                                                            2 => array( 'act'   , 'csite_view'  ),
                                                            $f_hiddens,
                                                          )
                                                 );


				$arr =                                   $f_hiddens . array(
                                                            1 => array( 'code'  , 'edit_html'  ),
                                                            2 => array( 'act'   , 'csite_view'  ),
                                                          );

    			print_r($arr);


                //+-------------------------------

                $ADMIN->page_title = "Редактирование HTML-кода функции &quot;_show_{$IN['f']}()&quot;";

                $ADMIN->page_detail = "";

                $SKIN->td_header[] = array( "&nbsp;"  );

                //+-------------------------------

                $ADMIN->html .= $SKIN->start_table( "Редактирование" );


          		foreach ( $functions as $function ) {

	                //----------------------------------------------------------
	                // add row function name
	                //----------------------------------------------------------

	                $ADMIN->html .= $SKIN->add_td_row( array
	                                                         (
	                                                            "<b>" . $function . "</b>",
	                                                         )
	                                                 );

	                //----------------------------------------------------------
	                // add function contents
	                //----------------------------------------------------------

	                $contents = array();

	                preg_match("#" . $function . "#", $skin_contents, $contents);

                 	$data = "";
                 	$copy = false;
                 	$skip = -1;

                 	foreach ( $lines as $line ) {

                   		if ( preg_match("/" . $function . "\(/", $line) == 1 ) {

                      		$skip = 4;
                   		}

                   		if ( $skip > 0 ) {

                   			$skip--;
                   		} else if ( $skip == 0 ) {

                   			$copy=true;
                   			$skip = -1;
                   		}

                   		if ( preg_match("/EOF;/", $line) == 1 ) {

                   			$copy = false;
                   		}

                   		if ( $copy == true ) {

                   			$data .= $line;
                   		}
                 	}


	                $ADMIN->html .= $SKIN->add_td_row( array
	                                                         (
	                                                            $SKIN->form_textarea($function, $data, 200),
	                                                         )
	                                                 );

          		}

                $ADMIN->html .= $SKIN->end_table();


                $ADMIN->html .= $SKIN->start_table( "Принять изменения" );
                $ADMIN->html .= $SKIN->end_form("Принять изменения");
                $ADMIN->html .= $SKIN->end_table();

                $ADMIN->html .= $SKIN->end_table();


                $ADMIN->output();
        }

        function edit_html() {
        global $SKIN, $ADMIN, $DB, $IN, $ibforums;

        	$text = $IN;

        	$text = preg_replace("/&#60;/", "<", $text);
			$text = preg_replace("/&#62;/", ">", $text);
			$text = preg_replace("/&#38;/", "&", $text);
			$text = str_replace( '\\n' , '\\\\\\n', $text );
			$text = preg_replace("/\r/", "", $text);


        	print_r($text);
        }


}


?>
