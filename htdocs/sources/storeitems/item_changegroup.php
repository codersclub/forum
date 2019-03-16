<?php
//---------------------------------------------------
// IBStore Change Group
//---------------------------------------------------
class item
{
    var $name = "Change Group";
    var $desc = "Change You're member group to a new one.";
    var $extra_one = "";
    var $extra_two = "";
    var $extra_three = "";

    // This is triggered when a Admin adds a item to the store
    // after they have selected a name a icon and a description and stock
    function on_add($EXTRA)
    {
        global $IN, $SKIN, $ADMIN;
        $ibforums = Ibf::app();
        $stmt     = $ibforums->db->query("SELECT g_id,g_title FROM ibf_groups WHERE g_access_cp='0' ORDER BY g_id DESC");
        while ($group = $stmt->fetch()) {
            $groups[] = array($group['g_id'], $group['g_title']);
        }
        $ADMIN->HTML .= $SKIN->add_td_row(array(
                                               "<b>User Group To Upgrade To:</b>",
                                               $SKIN->form_dropdown('extra_one', $groups)
                                          ));
        return $ADMIN->HTML;
    }

    function on_add_edits($admin)
    {
    }

    // As soon as a user has hit "Buy Item" this is triggered
    function on_add_extra()
    {
    }

    function on_buy()
    {
    }

    // We trigger this function as soon as "Use Item" is clicked.
    // this is were you would put the things a user could change (Like a members title as a e.g)
    function on_use($itemid = "")
    {
    }

    // This will take all the input from on_use (If any)
    // and run the querys or change some thing...Which probly would be in a query
    function run_job()
    {
    }

    function do_on_use($group, $blank = "", $blank = "")
    {
        global $ibforums, $print;
        $ibforums->db->exec("UPDATE ibf_members SET mgroup='{$group}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
        $lib->delete_item($ibforums->input['itemid']);
        $lib->redirect("User Group Changed.", "act=store", "1");
        return "";
    }
}
