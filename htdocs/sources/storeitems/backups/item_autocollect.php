<?php
//---------------------------------------------------
// IBStore Turn Auto Collect On/Off
//---------------------------------------------------
class item
{
    var $name = "Auto Collect ";
    var $desc = "turn Auto Collect On and Off";
    var $extra_one = "";
    var $extra_two = "";
    var $extra_three = "";

    function on_add($EXTRA)
    {
    }

    function on_add_edits($admin)
    {
    }

    function on_add_extra()
    {
    }

    function on_buy()
    {
    }

    function on_use($itemid = "")
    {
    }

    function run_job()
    {
    }

    function do_on_use($blank = "", $blank = "", $blank = "")
    {
        global $ibforums, $print, $lib;

        if ($ibforums->member['auto_collect'] == 1) {
            $ibforums->db->exec("UPDATE ibf_members SET auto_collect='0' WHERE id='{$ibforums->member['id']}' LIMIT 1");
            $lib->write_log("Auto Collect Off", "item");
            $lib->delete_item($ibforums->input['itemid']);
            $lib->redirect("Auto Collect Off.", "act=store&code=inventory");
        } else {
            $ibforums->db->exec("UPDATE ibf_members SET auto_collect='1' WHERE id='{$ibforums->member['id']}' LIMIT 1");
            $lib->write_log("Auto Collect On", "item");
            $lib->delete_item($ibforums->input['itemid']);
            $lib->redirect("Auto Collect On.", "act=store&code=bank");
        }
        return "";
    }
}
