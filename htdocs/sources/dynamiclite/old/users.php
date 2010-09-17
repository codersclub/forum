<?

//require_once ROOT_PATH . "/sources/cms/core/table_linker.php";

class cms_users {

// ------------------------------------------------------------
// Information about current user
// ------------------------------------------------------------
var $user_info = NULL;

// ------------------------------------------------------------
// Cache of information about forum members
// ------------------------------------------------------------
var $users_names = array();
var $users_ids = array();
var $user = array();


        //--------------------------------------------------------------
       // getting info aboubt moderators
      //----------------------------------------------------------------

        function init() {
        global $DB;

                if (!$this->user) {

                        $DB->query(" SELECT * FROM ibf_cms_moderators ");

                        while ($row = $DB->fetch_row()) {

                                $this->user['mod_info'][] = $row;
                        }
                }
        }

        // ------------------------------------------------------------
        //
        // This function creates form to manage by users, handles those
        // actions and reports about results of completion.
        //
        // PERMISSIONS:
        //        only administrators should have access to this
        //        functionality.
        //
        // GENERAL STEPS:
        //        1) check for admin permissions and return, if fails
        //        2) handle action< if any
        //        3) create html for user list (no additional
        //           permission checks is needed)
        //        4) create general users form
        //
        // ------------------------------------------------------------
        function show_users() {
        global $DB, $ibforums, $GROUPS, $CMS, $BUILDER;

                $CMS->page_title = $ibforums->lang["users_title"];

                $params = array();

                // ------------------------------------------------------------
                // Check permissions to access
                // ------------------------------------------------------------

                if(!$this->is_admin()) {

                        $CMS->cms_error('access_denied');
                        return;
                }

                if(NULL == $ibforums->input['from'])
                        $ibforums->input['from'] = 0;


                // ------------------------------------------------------------
                // Handle user actions. Each handler returns html-formated
                // human readable string, which contains notes about success or
                // warning failure results of action.
                // ------------------------------------------------------------

                switch($ibforums->input['do']) {

                case 'add_to_group':
                        $params['result'] = $this->do_add_to_group();
                        break;

                case 'remove_from_group':
                        $params['result'] = $this->do_remove_from_group();
                        break;

                case 'add':
                        $params['result'] = $this->do_add_new_user();
                        break;

                case 'delete':
                        $params['result'] = $this->do_delete_user();
                        break;
                }


                // ------------------------------------------------------------
                // Fetch user list from database. ibf_cms_users does not
                // contain user names. So, we also ask to ibf_members
                // ------------------------------------------------------------

                $query = $DB->query("
                        SELECT
                                c.*, m.name
                        FROM
                                ibf_cms_users c, ibf_members m
                        WHERE
                                m.id=c.id
                        LIMIT
                                " . $ibforums->input['from'] . ", 30");


                // ------------------------------------------------------------
                // Here we get lambda function, which generates one row in
                // users' table. It requires one parameter, associative array
                // with the next fields:
                //      - all column names of the ibf_cms_users table
                //      - 'name'   - user name
                //                - 'groups' - human readable string with the list of
                //                    groups this user is a member of
                //      - 'class'  - css class name for <TD> tag
                // ------------------------------------------------------------

                $users_row_func = $BUILDER->get_skin_function('users_row');

                $class = "row2";
                while($row = $DB->fetch_row($query)) {

                        $row['class'] = $class;

                        $row['groups'] = $GROUPS->get_group_list_for_user($row['id']);;

                        // ------------------------------------------------------------
                        // Add one row (<tr>...</tr>) to the $content variable.
                        // ------------------------------------------------------------

                        $params['content'] .= $users_row_func($row);


                        // ------------------------------------------------------------
                        // Swap color of the next table row.
                        // ------------------------------------------------------------

                        if($class == "row2") {
                                $class = "row4";
                        } else {
                                $class = "row2";
                        }
                }

                $params['options'] = $GROUPS->get_group_list_options(0);


                // ------------------------------------------------------------
                // Create lambda function for whole users form. It requires one
                // parameter - associative array with the next fields:
                //      - 'content' - the list of users - rows of table
                //      - 'options' - the list of groups - options for select
                //      - 'result'  - information about results of the previous
                //                    action
                //      - 'nav'     - navigation thru the user list (next|prev)
                // ------------------------------------------------------------

                $users_table_func = $BUILDER->get_skin_function('users');


                // ------------------------------------------------------------
                // Redirect another output to the main engine with results of
                // this function.
                // ------------------------------------------------------------

                $CMS->do_std_output($users_table_func($params));
        }

        // ------------------------------------------------------------
        // Checks, if current user is a member of Admins group.
        // Force return FALSE, if environments are not initialized.
        // ------------------------------------------------------------

        //back-compartability
        function is_admin() {

                return $this->has_admin_privs();
        }

        function has_admin_privs() {
        global $ibforums, $DB;

                if(empty($ibforums->member) || empty($ibforums->vars['admin_group'])) {

                        return false;
                }

                $result = ($ibforums->member['mgroup'] == $ibforums->vars['admin_group']);

                $cat = (!$ibforums->input['cat']) ? 0 : $ibforums->input['cat'];

                if ($this->user && is_array($this->user['mod_info'])) {

                        foreach ($this->user['mod_info'] as $mod) {

                                if ($mod['category_id'] == $cat && $ibforums->member['id'] == $mod['member_id']) {

                                        $result = true;
                                }
                        }
                }


                return $result;
        }

        // ------------------------------------------------------------
        // Checks, if current user is a member of CMS team.
        // ------------------------------------------------------------
        function is_member() {

                $this->load_current_user_info();

                return $this->is_admin() || ($this->user_info['id'] != 0);
        }

        // ------------------------------------------------------------
        // Fetchs user ID by user name.
        // ------------------------------------------------------------
        function get_member_id($name) {
        global $DB;

                if(!$this->users_names[$name]) {

                        $DB->query("
                                SELECT
                                        *
                                FROM
                                        ibf_members
                                WHERE
                                        name='{$name}'
                                ");

                        $user = $DB->fetch_row();

                        $this->users_names[$name] = $user;
                        $this->users_ids[$user['id']] = $user;
                }

                return $this->users_names[$name]['id'];
        }

        // ------------------------------------------------------------
        // Fetchs user name by user id.
        // ------------------------------------------------------------
        function get_member_name($id) {
        global $DB;

                if(!$this->users_ids[$id]) {

                        $DB->query("
                                SELECT
                                        *
                                FROM
                                        ibf_members
                                WHERE
                                        id='{$id}'
                                ");

                        $user = $DB->fetch_row();

                        $this->users_ids[$id] = $user;
                        $this->users_names[$user['name']] = $user;
                }
                return $this->users_ids[$id]['name'];
        }

        // ------------------------------------------------------------
        // Loads and caches in $this->user_info variable information
        // about current user
        // ------------------------------------------------------------
        function load_current_user_info() {
        global $DB, $sess;

                if(NULL == $this->user_info) {

                        $DB->query("
                                SELECT
                                        *
                                FROM
                                        ibf_cms_users
                                WHERE
                                        id=" . $sess->member['id']
                        );
                        $this->user_info = $DB->fetch_row();
                }
        }

        // ------------------------------------------------------------
        // Returns aggregate permissions for current user.
        // ------------------------------------------------------------
        function adopt_permissions($info) {
        global $GROUPS;

                if(empty($this->user_info)) {

                        return NULL;
                }

                if($info['owner'] == $this->user_info['id']) {

                        // ------------------------------------------------------------
                        // User is owner - return owner part of rights.
                        // ------------------------------------------------------------

                        return array (
                                'r' => $info[0],
                                'w' => $info[1],
                                'x' => $info[2]
                        );

                } else if($GROUPS->is_user_in_group($this->user_info['id'], $info['group'])) {

                        // ------------------------------------------------------------
                        // User is a member of owner groupowner - return group part of
                        // rights.
                        // ------------------------------------------------------------

                        return array (
                                'r' => $info[3],
                                'w' => $info[4],
                                'x' => $info[5]
                        );
                }

                // ------------------------------------------------------------
                // User is just a guest here - return everyone part of rights.
                // ------------------------------------------------------------

                return array (
                        'r' => $info[6],
                        'w' => $info[7],
                        'x' => $info[8]
                );
        }

        // ------------------------------------------------------------
        // Gets the name of the item owner.
        // ------------------------------------------------------------
        function get_owner_name(&$file) {
        global $DB;

                $owner = $file->get_owner_id();

                if(0 == $owner) {

                        return 'admin';
                }

                $DB->query("
                        SELECT
                                name
                        FROM
                                ibf_members
                        WHERE
                                id={$owner}");

                $row = $DB->fetch_row();

                return $row['name'];
        }

        // ------------------------------------------------------------
        // Returns the HTML <options> tags for whole list of users
        // ------------------------------------------------------------
        function get_user_list_options($sel, $admin = false) {
        global $DB, $BUILDER;

                $DB->query("
                        SELECT
                                m.id, m.name
                        FROM
                                ibf_cms_users c, ibf_members m
                        WHERE
                                c.id = m.id");

                if($admin) {

                        $result = $BUILDER->build_option_tag('0', 'Admin only', $sel == 0);
                }

                while($row = $DB->fetch_row()) {

                        $result .= $BUILDER->build_option_tag($row['id'], $row['name'], $sel == $row['id']);
                }

                return $result;
        }


        function do_add_new_user() {
        global $DB, $ibforums;

                $user = $ibforums->input['name'];

                $add = false;

                if($user) {

                        if($this->get_member_id($user)) {

                                return $ibforums->lang['user_alread_here'];
                        }

                        $DB->query("SELECT id FROM ibf_members WHERE name='{$user}'");
                        $id = $DB->fetch_row();
                        $id = $id['id'];

                        if($id) {

                                $DB->query("INSERT INTO ibf_cms_users (
                                        id
                                ) VALUES (
                                        {$id}
                                )");

                                $add = true;
                        }
                }

                if($add)
                        return $ibforums->lang['user_added'];

                return $ibforums->lang['user_not_added'];
        }

        function do_add_to_group($group = NULL, $to_user = NULL) {
        global $DB, $ibforums;

                if($group) {
                        $users = array($to_user);
                        $new_group = $group;
                } else {
                        $users = $ibforums->input['users'];
                        $new_group = $ibforums->input['group_name'];
                }

                $add = false;

                if(count($users) && $new_group) {

                        $linker = new table_linker('ibf_cms_user_group', 'ibf_cms_users', 'ibf_cms_groups');

                        foreach($users as $user) {

                        echo $user;
                                $add |= $linker->add_link($user, $new_group);
                        }
                }

                if($add)
                        return $ibforums->lang['group_added'];

                return $ibforums->lang['group_not_added'];
        }


        function do_remove_from_group($group = NULL, $user = NULL) {
        global $DB, $ibforums, $GROUPS;

                if($group) {
                        $users = array($user);
                        $old_group = $group;
                } else {
                        $users = $ibforums->input['users'];
                        $old_group = $ibforums->input['group_name'];
                }

                $remove = false;

                if(count($users) && $old_group) {

                        $linker = new table_linker('ibf_cms_user_group', 'ibf_cms_users', 'ibf_cms_groups');

                        foreach($users as $user) {

                                $remove |= $linker->remove_link($user, $old_group);
                        }
                }

                if($remove)
                        return $ibforums->lang['group_removed'];

                return $ibforums->lang['group_not_removed'];
        }


        function do_delete_user() {
        global $DB, $ibforums;

                $users = $ibforums->input['users'];

                $del = false;

                if(count($users)) {

                        $linker = new table_linker('ibf_cms_user_group', 'ibf_cms_users', 'ibf_cms_groups');

                        foreach($users as $user) {

                                $del = true;
                                $DB->query("DELETE FROM ibf_cms_users WHERE id='{$user}'");
                                $linker->remove_all_refs_for_basis($user);
                        }
                }

                if($del)
                        return $ibforums->lang['users_removed'];

                return $ibforums->lang['users_not_removed'];
        }

}

?>