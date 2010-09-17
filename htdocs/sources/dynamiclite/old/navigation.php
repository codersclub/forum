<?php

class navigation {

        var $parent_cats;
        var $sub_parent_cats;
        var $cats;
        var $current_cat = 0;
        var $prev_cat;
        var $step = 0;
        var $result;
        var $children;

        function build_main_cats() {
        global $DB, $ibforums;

                $DB->query("SELECT * FROM ibf_cms_uploads_cat");

                while ($cats = $DB->fetch_row()) {

                        if ($cats['parent_id'] == 0) {

                                $this->parent_cats[$cats[id]] = $cats;
                        }

                        $this->cats[$cats['id']] = $cats;
                }

                if ($this->parent_cats) {

                        foreach ($this->parent_cats as $cat) {

                                foreach ($this->cats as $cat2) {

                                        if ($cat2['parent_id'] == $cat['id']) {

                                                $this->sub_parent_cats[] = $cat2;
                                        }
                                }
                        }
                }

                return 0;
        }

        function build_nav($params = null) {
        global $ibforums;

                if (!$this->cats) $this->build_main_cats();
                $i = 0;
                while (!null) {

                        $result[$i] = $this->get_parent($params['current_cat_id']);

                        $params['current_cat_id'] = $result[$i]['parent_id'];

                        if (!$result[$i]) break;

                        $i++;
                }

                $result = array_reverse($result);

                foreach ($result as $cats) {

                        if ($cats) {

                                $nav[] = "<a href = '"
                                         . $ibforums->vars['dynamiclite']
                                         . "cat="
                                         . $cats['id']
                                         . "'>"
                                         . $cats['name']
                                         . "</a>";
                        }
                }

                return $nav;
        }

        function build_path($params = null, $as_url = false) {
        global $ibforums;

                if (!$this->cats) $this->build_main_cats();
                $i = 0;
                while (!null) {

                        $result[$i] = $this->get_parent($params['current_cat_id']);

                        $params['current_cat_id'] = $result[$i]['parent_id'];

                        if (!$result[$i]) break;

                        $i++;
                }

                $result = array_reverse($result);

                foreach ($result as $cats) {

                        if ($cats) {

                                $path[] = $cats['name'];
                        }
                }

                $output_array['NAV'] = $path;

                if (is_array( $output_array['NAV'] ) ) {

                        $mysep = "";
                        foreach ($output_array['NAV'] as $n) {

                                if ($n) {

                                        $mysep = "/";
                                        $res .= $n . $mysep;
                                }
                        }
                }

                if ($as_url == true) {

                        $res = $ibforums->vars['csite_cms_url'] . $res;
                } else {
                        $res = $ibforums->vars['csite_cms_path'] . $res;
                }

                return $res;
        }

        function clear_array($input = array()) {

                foreach ($input as $values) {

                        if ($values) {

                                $output[] = $values;
                        }
                }

                return $output;
        }

        function get_children($id = 0) {

                foreach ($this->cats as $cat) {

                        if ($cat['parent_id'] == $id) {

                                $result[] = $cat;
                        }
                }

                return $result;
        }

        function build_cat_list_select() {

                if (!$this->cats) $this->build_main_cats();

                foreach ($this->parent_cats as $parent_cat) {

                        $tmp .= "<option value='{$parent_cat['id']}'>{$parent_cat['name']}</option>";
                        $res = $this->sort_cats($parent_cat);

                        if ($res) foreach ($res as $c0) {

                                $tmp .= "<option value='{$c0[0]['id']}'>&raquo;&nbsp;{$c0[0]['name']}</option>";

                                if ($c0[1]) foreach ($c0[1] as $c1) {

                                        $tmp .= "<option value='{$c1[0]['id']}'>---{$c1[0]['name']}</option>";

                                        if ($c1[1]) foreach ($c1[1] as $c2) {

                                                $tmp .= "<option value='{$c2[0]['id']}'>------{$c2[0]['name']}</option>";

                                                if ($c2[1]) foreach ($c2[1] as $c3) {

                                                        $tmp .= "<option value='{$c3[0]['id']}'>---------{$c3[0]['name']}</option>";

                                                        if ($c3[1]) foreach ($c3[1] as $c4) {

                                                                $tmp .= "<option value='{$c4[0]['id']}'>------------{$c4[0]['name']}</option>";

                                                        }
                                                }
                                        }
                                }
                        }
                }

                return $tmp;
        }

        function build_cat_list_menu($cat = 0) {
        global $ibforums, $DSITE;

                if (!$this->cats) $this->build_main_cats();

                $res = $this->sort_cats($this->cats[$cat]);

                        if ($res) foreach ($res as $c0) {

                                $tmp .= $DSITE->html->tmpl_child_menu_row($c0[0]);

                                if ($c0[1]) foreach ($c0[1] as $num => $c1) {

                                        $c1[0]['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;" . $c1[0]['name'];
                                        $tmp .= $DSITE->html->tmpl_child_menu_row($c1[0]);


                                        if ($c1[1]) foreach ($c1[1] as $c2) {

                                                $c2[0]['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $c2[0]['name'];
                                                $tmp .= $DSITE->html->tmpl_child_menu_row($c2[0]);

                                                if ($c2[1]) foreach ($c2[1] as $c3) {

                                                        $c3[0]['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $c3[0]['name'];
                                                        $tmp .= $DSITE->html->tmpl_child_menu_row($c3[0]);

                                                        if ($c3[1]) foreach ($c3[1] as $c4) {

                                                                $c4[0]['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $c4[0]['name'];
                                                                $tmp .= $DSITE->html->tmpl_child_menu_row($c4[0]);
                                                        }
                                                }
                                        }
                                }
                        }

                return $tmp;
        }

        function sort_cats($next_cat = array()) {

                while ($next_cat = $this->get_children($next_cat['id'])) {

                        foreach ($next_cat as $children) {

                                $result[$children['id']][] = $children;

                                $result[$children['id']][] = $this->sort_cats($children);
                        }
                }

                return $result;
        }

        function get_parent($parent_id = 0) {

                foreach ($this->cats as $cats) {

                        if ($cats['id'] == $parent_id) {

                                $result = $cats;
                        }
                }

                return $result;
        }


        function get_super_parent($next_cat = 0) {

                while ($next_cat = $this->get_parent($next_cat)) {

                        $this->result = $next_cat['id'];
                        $this->get_super_parent($next_cat['parent_id']);
                }

                return $this->result;
        }
}

?>
