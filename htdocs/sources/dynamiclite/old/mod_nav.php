<?php

/*
+--------------------------------------------------------------------------
|   D-Site Category working module
|   ========================================
|   (c) 2004 - 2005 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
|
|   Site categories functions
|
*---------------------------------------------------------------------------
*/

class mod_nav {

        var $parent_cats      = array();
        var $sub_parent_cats  = array();
        var $cats             = array();
        var $html             = "";

        /*--------------------------------------------------------------------*/
        /*                 SYSTEM (NOT VISUAL) FUNCTIONS                      */
        //--------------------------------------------------------------------*/

        //----------------------------------------------------------------------
        //  builds global arrays of cats - global, parent and sub parent
        //----------------------------------------------------------------------

        function mod_nav() {
        global $DB, $ibforums, $std;

                //--------------------------------------
                // load skin and language sets
                // if module used for IPB admin pages,
                // we won't load it
                //--------------------------------------

                if ( isset($ibforums->skin_id) ) {

                        $this->html = $std->load_template('skin_csite_mod_nav');
                }

                //--------------------------------------
                //  load and sort $this->cats and
                //  $this->parent_cats arrays
                //--------------------------------------

                $DB->query("SELECT * FROM ibf_cms_uploads_cat ORDER BY ord");

                while ($dbres = $DB->fetch_row()) {

                        if ($dbres['parent_id'] == 0) {

                                $this->parent_cats[$dbres[id]] = $dbres;
                        }

                        $this->cats[$dbres['id']] = $dbres;
                }

                if (!$this->cats) {

                        return false;
                }

                //--------------------------------------
                //  finished!
                //--------------------------------------

                return true;
        }

        //----------------------------------------------------------------------
        //  bulds file system path of category (like "var/wwww/d-site/cat/art")
        //----------------------------------------------------------------------

        function build_path($cat_id = 0, $as_url = false) {
        global $ibforums, $ART;

               $old_cat = $cat_id;
               $next_cat = array();

               while ($next_cat = $this->get_parent_cat($cat_id)) {

                       $nav[] = $next_cat;

                       $cat_id = $next_cat['id'];
               }

               $ibforums->vars['csite_cms_path'] = preg_replace( "#/$#", "", $ibforums->vars['csite_cms_path']);

               $result = $ibforums->vars['csite_cms_path'] . "/";

               if ( $as_url == true ) {

                       $ibforums->vars['csite_cms_url'] = preg_replace( "#/$#", "", $ibforums->vars['csite_cms_url']);

                       $result = $ibforums->vars['csite_cms_url'] . "/";
               }

               if ( $cat_id != 0 && !$nav ) {

                       return $result . $this->cats[$old_cat]['category_id'] . "/";

               }

               if ( !is_array($nav) ) {

                       return $ibforums->vars['csite_cms_path'] . "/";
               }

               $nav = array_reverse($nav);

               foreach ($nav as $n) {

                       $result .= $n['category_id'] . "/";
               }

               $result .= $this->cats[$old_cat]['category_id'] . "/";

               return $result;
        }

        //----------------------------------------------------------------------
        //  return parent cat of requested cat
        //----------------------------------------------------------------------

        function get_parent_cat( $id = 0 ) {

                return $this->cats[$this->cats[$id]['parent_id']];
        }

        //----------------------------------------------------------------------
        //  return a top-level parent cat of $cat
        //----------------------------------------------------------------------

        function get_super_parent($id = 0) {

                while ($next_cat = $this->_get_parent_cat($id)) {

                        $this->result = $id['id'];
                        $this->get_super_parent($id['parent_id']);
                }

                return $this->result;
        }

        //----------------------------------------------------------------------
        //  checks is requested category is a one of parent
        //----------------------------------------------------------------------

        function is_parent($id = 0, $request_id = 0 ) {

                while ($next_cat = $this->get_parent_cat($id)) {

                        $id = $next_cat['id'];

                        if ( $id == $request_id ) {

                                return true;
                        }

                        $this->is_parent($next_cat['parent_id']);
                }

                return false;
        }

        //----------------------------------------------------------------------
        //  checks is requested category is a one of parent
        //----------------------------------------------------------------------

        function is_children($id = 0, $request_id = 0 ) {

                while ( $next_cat = $this->get_children($id) ) {

                        foreach ( $next_cat as $ncat ) {

                                $id = $ncat['id'];

                                if ( $id == $request_id ) {

                                        return true;
                                }

                                $this->is_children($id, $request_id);
                        }
                }

                return false;
        }


        //----------------------------------------------------------------------
        // returns $children[] array for $cat_id
        //----------------------------------------------------------------------

        function get_children($id = 0) {

                foreach ($this->cats as $cat) {

                        if ($cat['parent_id'] == $id) {

                                $result[] = $cat;
                        }
                }

                return $result;
        }

        //----------------------------------------------------------------------
        // returns $cat['name'] of requested category
        //----------------------------------------------------------------------

        function get_name($cat_id = 0) {

                return $this->cats[$cat_id]['name'];
        }

        //----------------------------------------------------------------------
        //  returns sorted by parent cat array
        //  I'm the super star! 8)
        //----------------------------------------------------------------------

        function sort_cats($next_cat = array()) {

                while ($next_cat = $this->get_children($next_cat['id'])) {

                        foreach ($next_cat as $children) {

                                $result[$children['id']][] = $children;

                                $result[$children['id']][] = $this->sort_cats($children);
                        }
                }

                return $result;
        }


        /*--------------------------------------------------------------------*/
        /*                  VISUAL DATA FUNCTIONS                             */
        /*--------------------------------------------------------------------*/


        //----------------------------------------------------------------------
        //  builds <option> ... </option> human-readable list of $parent_cat
        //----------------------------------------------------------------------

        function get_subnodes_select_ad($node = array(), $sep = "&middot;&middot;", $selected_id = 0) {
        global $USR;

                if (!is_array($node)) {

                        return $result;
                }

                foreach ($node as $sub_node) {

                                if ($sub_node[0]['id'] == $selected_id) {

                                        $result .= "<option value='{$sub_node[0]['id']}' SELECTED>{$sep} {$sub_node[0]['name']}</option>";
                                } else {

                                        $result .= "<option value='{$sub_node[0]['id']}'>{$sep} {$sub_node[0]['name']}</option>";
                                }

                        if (is_array($sub_node[1])) {

                                $old_sep = $sep; $sep = $sep . "&middot;&middot;";

                                $result .= $this->get_subnodes_select_ad($sub_node[1], $sep, $selected_id);

                                $sep = $old_sep;
                        }
                }

                return $result;
        }

        //----------------------------------------------------------------------
        //  builds FULL <option> ... </option> list of cats
        //----------------------------------------------------------------------

        function build_cat_list_select_ad( $cat_id = 0, $cat_to_select = 0 ) {
        global $ibforums, $USR;

                if ( !$this->cats ) {

                        return "";
                }

                if ( $cat_id == 0 ) {

                        $cat_id = intval($ibforums->input['id']);
                }

                $parent_id = ( $cat_to_select == 0 ) ? $this->cats[$cat_id]['parent_id'] : $cat_to_select;
                $result = "";

                foreach ($this->parent_cats as $parent_cat) {

                                if ($parent_cat['id'] == $parent_id) {

                                        $result .= "<option value='{$parent_cat['id']}' SELECTED>{$parent_cat['name']}</option>";
                                } else {

                                        $result .= "<option value='{$parent_cat['id']}'>{$parent_cat['name']}</option>";
                                }


                        $sorted_cat = $this->sort_cats($parent_cat);

                        $result .= $this->get_subnodes_select_ad($sorted_cat, "&middot;&middot;", $parent_id);
                }

                return $result;
        }

        //----------------------------------------------------------------------
        //  builds <option> ... </option> human-readable list of $parent_cat
        //----------------------------------------------------------------------

        function get_subnodes_select($node = array(), $sep = "&middot;&middot;", $selected_id = 0) {
        global $USR;

                if (!is_array($node)) {

                        return $result;
                }

                foreach ($node as $sub_node) {

                        if ( $sub_node[0]['visible'] == 0 && $USR->is_mod($sub_node[0]['id']) === true ) {

                                if ($sub_node[0]['id'] == $selected_id) {

                                        $result .= "<option value='{$sub_node[0]['id']}' SELECTED>{$sep} {$sub_node[0]['name']}</option>";
                                } else {

                                        $result .= "<option value='{$sub_node[0]['id']}'>{$sep} {$sub_node[0]['name']}</option>";
                                }

                        } else if ( $sub_node[0]['visible'] == 1 ) {

                                if ($sub_node[0]['id'] == $selected_id) {

                                        $result .= "<option value='{$sub_node[0]['id']}' SELECTED>{$sep} {$sub_node[0]['name']}</option>";
                                } else {

                                        $result .= "<option value='{$sub_node[0]['id']}'>{$sep} {$sub_node[0]['name']}</option>";
                                }

                        }

                        if (is_array($sub_node[1])) {

                                $old_sep = $sep; $sep = $sep . "&middot;&middot;";

                                $result .= $this->get_subnodes_select($sub_node[1], $sep, $selected_id);

                                $sep = $old_sep;
                        }
                }

                return $result;
        }

        //----------------------------------------------------------------------
        //  builds FULL <option> ... </option> list of cats
        //----------------------------------------------------------------------

        function build_cat_list_select( $cat_id = 0, $cat_to_select = 0 ) {
        global $ibforums, $USR;

                if ( !$this->cats ) {

                        return "";
                }

                if ( $cat_id == 0 ) {

                        $cat_id = intval($ibforums->input['id']);
                }

                $parent_id = ( $cat_to_select == 0 ) ? $this->cats[$cat_id]['parent_id'] : $cat_to_select;
                $result = "";

                foreach ($this->parent_cats as $parent_cat) {


                        if ( ( $parent_cat['visible'] == 0 && $USR->is_mod($parent_cat['id']) === true ) ) {

                                if ($parent_cat['id'] == $parent_id) {

                                        $result .= "<option value='{$parent_cat['id']}' SELECTED>{$parent_cat['name']}</option>";
                                } else {

                                        $result .= "<option value='{$parent_cat['id']}'>{$parent_cat['name']}</option>";
                                }

                        } else if ( $parent_cat['visible'] == 1 ) {

                                if ($parent_cat['id'] == $parent_id) {

                                        $result .= "<option value='{$parent_cat['id']}' SELECTED>{$parent_cat['name']}</option>";
                                } else {

                                        $result .= "<option value='{$parent_cat['id']}'>{$parent_cat['name']}</option>";
                                }

                        }

                        $sorted_cat = $this->sort_cats($parent_cat);

                        $result .= $this->get_subnodes_select($sorted_cat, "&middot;&middot;", $parent_id);
                }

                return $result;
        }


        //----------------------------------------------------------------------
        //  builds rows for ad_csite_moderators
        //----------------------------------------------------------------------

        function get_subnodes_mod( $node = array(), $sep = "&middot;&middot;", $mods ) {
        global $SKIN;

                if (!is_array($node)) {

                        return $result;
                }

                foreach ($node as $sub_node) {

                                //-------------------------------------------
                                // fill html rows
                                //-------------------------------------------

                                $result .= $SKIN->add_td_row( array(
                                                                     "<center><input type='checkbox' name='add_{$sub_node[0]['id']}' value='1'></center>",
                                                                     $sep . " " . $sub_node[0]['name'],
                                                                     $this->get_current_mods($mods, $sub_node[0]['id']),
                                                                   )  ,
                                                              'subforum'
                                                             );

                        //-------------------------------------------
                        // go inner
                        //-------------------------------------------

                        if (is_array($sub_node[1])) {

                                $old_sep = $sep; $sep = $sep . "&middot;&middot;&middot;";

                                $result .= $this->get_subnodes_mod( $sub_node[1], $sep, $mods );

                                $sep = $old_sep;
                        }
                }

                //-------------------------------------------
                // finished!
                //-------------------------------------------

                return $result;
        }

        //----------------------------------------------------------------------
        //  builds FULL <option> ... </option> list of cats
        //----------------------------------------------------------------------

        function build_cat_list_mod( $cat_id = 0 ) {
        global $DB, $SKIN;

                //-------------------------------------------
                //  fill the moderators array for
                //  $this->get_current_mods()
                //-------------------------------------------

                $DB->query(" SELECT * FROM ibf_cms_moderators ");

                while ($dbres = $DB->fetch_row()) {

                        $mods[] = $dbres;
                }

                //-------------------------------------------
                //  build list of categories
                //-------------------------------------------

                foreach ( $this->parent_cats as $parent_cat ) {

                        //-------------------------------------------
                        //  parent categories html rows
                        //-------------------------------------------

                        $result .= $SKIN->add_td_row( array(
                                                                 "<center><input type='checkbox' name='add_{$parent_cat['id']}' value='1'></center>",
                                                                 "<strong>" . $parent_cat['name'] . "</strong>",
                                                                 $this->get_current_mods($mods, $parent_cat['id']),
                                                                 )  ,
                                                           'subforum'    );

                        //-------------------------------------------
                        //  make a sorted array of categories
                        //-------------------------------------------

                        $sorted_cat = $this->sort_cats($parent_cat);

                        //-------------------------------------------
                        //  build subnodes array for each
                        //  parent category
                        //-------------------------------------------

                        $result .= $this->get_subnodes_mod( $sorted_cat, "&middot;&middot;&middot;", $mods );

                }


                return $result;
        }

        //----------------------------------------------------------------------
        //  returnt list of moderators for selected category
        //----------------------------------------------------------------------

        function get_current_mods($mods = array(), $cat_id = 0) {
        global $ADMIN;

                if ( is_array( $mods ) ) {

                        foreach ( $mods as $mod ) {

                                if ($mod['forum_id'] == $cat_id) {

                                        $mod_string .= "<tr>
                                                         <td width='60%'>{$mod['member_name']}</td>
                                                         <td width='20%'><a href='{$ADMIN->base_url}&act=csite_mod&code=remove&mid={$mod['mid']}'>Удалить</a></td>
                                                         <td width='20%'><a href='{$ADMIN->base_url}&act=csite_mod&code=edit&mid={$mod['mid']}'>Изменить</a></td>
                                                       </tr>
                                                       ";
                                }
                        }
                }

                if ($mod_string != "") {

                        $these_mods = "<table cellpadding='3' cellspacing='0' width='100%' align='center'>".$mod_string."</table>";
                } else {

                        $these_mods = "<center><i>Модераторы не назначены</i></center>";
                }

                return $these_mods;
        }


        //----------------------------------------------------------------------
        //  builds list of table rows for Main menu
        //----------------------------------------------------------------------

        function get_subnodes_main_menu($node = array(), $sep = "&nbsp;&nbsp;&nbsp;") {
        global $DSITE, $USR;

                //-------------------------------------
                // nothing to build?
                //-------------------------------------

                if (!is_array($node)) {

                        return $result;
                }

                //-------------------------------------
                // make some html rows
                //-------------------------------------

                foreach ($node as $sub_node) {

                        //--------------------------------------------
                        // do not show hidden cats for non-moderators
                        //--------------------------------------------

                        if ( $sub_node[0]['visible'] == 1 ) {

                                //--------------------------------------------
                                // are we being redirected?
                                //--------------------------------------------

                                if ( $sub_node[0]['redirect_url'] != '' ) {

                                        $result .= $this->html->tmpl_main_menu_row_redir($sub_node[0]['redirect_url'], $sep, $sub_node[0]['name']);

                                } else {

                                        $result .= $this->html->tmpl_main_menu_row($sub_node[0]['id'], $sep, $sub_node[0]['name']);
                                }

                        } else if ( $USR->is_mod($sub_node[0]['id']) === true ) {

                                //--------------------------------------------
                                // are we being redirected?
                                //--------------------------------------------

                                if ( $sub_node[0]['redirect_url'] != '' ) {

                                        $result .= $this->html->tmpl_main_menu_row_redir($sub_node[0]['redirect_url'], $sep, $sub_node[0]['name']  . " (!)");

                                } else {

                                        $result .= $this->html->tmpl_main_menu_row($sub_node[0]['id'], $sep, $sub_node[0]['name']  . " (!)");
                                }
                        }

                        //--------------------------------------------
                        // going deeper and deeper...
                        //--------------------------------------------

                        if (is_array($sub_node[1])) {

                                $old_sep = $sep ; $sep .= "&nbsp;&nbsp;&nbsp;&nbsp;";

                                $result .= $this->get_subnodes_main_menu($sub_node[1], $sep, $selected_id);

                                $sep = $old_sep;
                        }
                }

                //--------------------------------------------
                // recursion results to itself
                //--------------------------------------------

                return $result;
        }

        //----------------------------------------------------------------------
        //  builds FULL list of cats for Main menu
        //----------------------------------------------------------------------

        function build_cat_list_main_menu( $block_caption = "" ) {
        global $ibforums, $DSITE, $USR;

                $result = "";

                //----------------------------------------
                // make a list of top-level categories
                //----------------------------------------

                foreach ($this->parent_cats as $parent_cat) {

                        //----------------------------------------
                        // let's do not show hidden
                        // cats for all othe users
                        //----------------------------------------

                        if ( $parent_cat['visible'] == 1 ) {

                                //----------------------------------------
                                // make some redirects for html
                                //----------------------------------------

                                if ( $parent_cat['redirect_url'] != '' ) {

                                        $result .= $this->html->tmpl_main_menu_parent_row_redir($parent_cat['redirect_url'], $parent_cat['name']);

                                } else {

                                        $result .= $this->html->tmpl_main_menu_parent_row($parent_cat['id'], $sep, $parent_cat['name']);
                                }

                                //----------------------------------------
                                // let's make children of each cat
                                //----------------------------------------

                                $sorted_cat = $this->sort_cats($parent_cat);

                                $result .= $this->get_subnodes_main_menu($sorted_cat, "&nbsp;&nbsp;&nbsp;");

                        } else if ( $USR->is_mod() == true ) {

                                //----------------------------------------
                                // make some redirects for html
                                //----------------------------------------

                                if ( $parent_cat['redirect_url'] != '' ) {

                                        $result .= $this->html->tmpl_main_menu_parent_row_redir($parent_cat['redirect_url'], $parent_cat['name'] . " (!)");

                                } else {

                                        $result .= $this->html->tmpl_main_menu_parent_row($parent_cat['id'], $sep, $parent_cat['name'] . " (!)");
                                }

                                //----------------------------------------
                                // children for each cat
                                //----------------------------------------

                                $sorted_cat = $this->sort_cats($parent_cat);

                                $result .= $this->get_subnodes_main_menu($sorted_cat, "&nbsp;&nbsp;&nbsp;");
                        }
                }

                //----------------------------------------
                // finished!
                //----------------------------------------

                return $this->html->tmpl_main_menu( $result, $block_caption );
        }


        //----------------------------------------------------------------------
        //  builds rows for ad_csite_categories
        //----------------------------------------------------------------------

        function get_subnodes_ad_cat( $node = array(), $sep = "&middot;&middot;" ) {
        global $SKIN, $ADMIN;

                if ( !is_array($node) ) {

                        return $result;
                }

                foreach ($node as $sub_node) {

                        //--------------------------------------------
                        // mark hidden
                        //--------------------------------------------

                        if ( $sub_node[0]['visible'] == 0 ) {

                                $sub_node[0]['name'] = $sub_node[0]['name'] . " (!{$inbforums->lang['hidden_cat']})";
                        }

                                //-------------------------------------------
                                // fill html rows
                                //-------------------------------------------

                                $result .= $SKIN->add_td_row( array(
                                                                     $sep . "&nbsp;<a href='{$ADMIN->base_url}&act=csite_cat&code=edit&cid={$sub_node[0]['id']}'>{$sub_node[0]['name']}</a>",
                                                                     "<center><a href='{$ADMIN->base_url}&act=csite_cat&code=edit&cid={$sub_node[0]['id']}'>Изменить</a> |
                                                                     <a href='{$ADMIN->base_url}&act=csite_cat&code=remove&cid={$sub_node[0]['id']}'>Удалить</a></center>",
                                                                   )  ,
                                                              ''
                                                             );

                        //-------------------------------------------
                        // go inner
                        //-------------------------------------------

                        if (is_array($sub_node[1])) {

                                $old_sep = $sep; $sep = $sep . "&middot;&middot;&middot;";

                                $result .= $this->get_subnodes_ad_cat( $sub_node[1], $sep, $mods );

                                $sep = $old_sep;
                        }
                }

                //-------------------------------------------
                // finished!
                //-------------------------------------------

                return $result;
        }

        //----------------------------------------------------------------------
        //  builds list of categories for ad_csite_cat()
        //----------------------------------------------------------------------

        function build_cat_list_ad_cat( $cat_id = 0 ) {
        global $DB, $SKIN, $ADMIN;

                //-------------------------------------------
                //  are we in the space?
                //-------------------------------------------

                if ( !$this->cats ) {

                        return "";
                }

                //-------------------------------------------
                //  build list of categories
                //-------------------------------------------

                foreach ( $this->parent_cats as $parent_cat ) {

                        //-------------------------------------------
                        //  mark hidden
                        //-------------------------------------------

                        if ( $parent_cat['visible'] == 0 ) {

                                $parent_cat['name'] = $parent_cat['name'] . " (!)";
                        }

                        //-------------------------------------------
                        //  parent categories html rows
                        //-------------------------------------------

                        $result .= $SKIN->add_td_row( array(
                                                                 "<strong><a href='{$ADMIN->base_url}&act=csite_cat&code=edit&cid={$parent_cat['id']}'>{$parent_cat['name']}</a></strong>",
                                                                 "<center><a href='{$ADMIN->base_url}&act=csite_cat&code=edit&cid={$parent_cat['id']}'>Изменить</a> |
                                                                 <a href='{$ADMIN->base_url}&act=csite_cat&code=remove&cid={$parent_cat['id']}'>Удалить</a></center>",
                                                                 )  ,
                                                           ''    );

                        //-------------------------------------------
                        //  make a sorted array of categories
                        //-------------------------------------------

                        $sorted_cat = $this->sort_cats($parent_cat);

                        //-------------------------------------------
                        //  build subnodes array for each
                        //  parent category
                        //-------------------------------------------

                        $result .= $this->get_subnodes_ad_cat( $sorted_cat, "&middot;&middot;&middot;", $mods );

                }


                return $result;
        }

        //----------------------------------------------------------------------
        //  builds human-readable path of current location
        //----------------------------------------------------------------------

        function build_nav($cat_id = 0) {
        global $ibforums, $DSITE, $ART, $USR;

               $old_cat = $cat_id;
               $result = $this->html->start_nav();
               $sep = "&nbsp;->&nbsp;";

               while ($next_cat = $this->get_parent_cat($cat_id)) {

                       $nav[] = $next_cat;

                       $cat_id = $next_cat['id'];
               }

               if ($cat_id != 0 && !$nav) {

                       return $result . $this->html->nav_link($this->cats[$cat_id], $sep) . $this->html->end_nav();

               } else if (!$nav) {

                       return $result . $this->html->end_nav();
               }

               $nav = array_reverse($nav);

               foreach ($nav as $n) {

                       if ( $n['visible'] != 0 ) {

                               $result .= $this->html->nav_link($n, $sep);

                       } else if ( $USR->is_mod() === true ) {

                               $result .= $this->html->nav_link($n, $sep);
                       }
               }

               $result .= $this->html->nav_link($this->cats[$old_cat], $sep);

               return $result . $this->html->end_nav();
        }
}
?>
