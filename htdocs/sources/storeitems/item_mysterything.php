<?php
//---------------------------------------------------
// IBStore Mystery Thing
//---------------------------------------------------
class item
{
    var $name = "??????";
    var $desc = "Who knows what you get from this, could be money or could be a item!";
    var $extra_one = "100";
    var $extra_two = "0";
    var $extra_three = "1000";

    function on_add($EXTRA)
    {
        global $IN, $SKIN, $ADMIN;
        $ibforums = Ibf::app();
        $ADMIN->Html .= $SKIN->add_td_row(array(
                                               "<b>Only give items with a sell price above?</b><br>This will only give items out with a sellprice above what you set.",
                                               $SKIN->form_input("extra_one", $EXTRA['extra_one'])
                                          ));
        $ADMIN->Html .= $SKIN->add_td_row(array(
                                               "<b>Lowest Random Amount to get?</b><br>If user gets points the lowest random amount they can get.",
                                               $SKIN->form_input("extra_two", $EXTRA['extra_two'])
                                          ));

        $ADMIN->Html .= $SKIN->add_td_row(array(
                                               "<b>Highest Random Amounts</b><br>If user gets points the highest random amount they can get.",
                                               $SKIN->form_input("extra_three", $EXTRA['extra_three'])
                                          ));

        return $ADMIN->Html;
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

    function do_on_use($above_price, $random_min, $random_max)
    {
        global $ibforums, $print, $lib;
        $above_price = (int)$above_price;
        $random_min  = (int)$random_min;
        $random_max  = (int)$random_max;
        mt_srand(time());
        $action = mt_rand(0, 100);
        $action = 100;
        if ($action > 50) {
            $stmt = $ibforums->db->query("SELECT * FROM ibf_store_shopstock WHERE sell_price>'{$above_price}'");
            while ($temp = $stmt->fetch()) {
                $giveable_items[$i]      = $temp['id'];
                $giveable_item_name[$i]  = $temp['item_name'];
                $giveable_item_price[$i] = $temp['sell_price'];
                $i++;
            }
            $giveitem  = mt_rand(0, count($giveable_items));
            $temp      = $giveable_items[$giveitem];
            $item_name = $giveable_item_name[$giveitem];
            $price     = $giveable_item_price[$giveitem];
            $price     = $price - (($ibforums->member['g_discount'] / 100) * $price);
            $log_msg   = "Got Item {$item_name} Price: {$price}.";
            $msg       = "You got \"{$item_name}\" Shop value is {$price} {$ibforums->vars['currency_name']}.";
            if ($temp < 1) {
                $points = mt_rand($random_min, $random_max);
                $points = $ibforums->member['points'] + $points;
                $ibforums->db->exec("UPDATE ibf_members SET points='{$points}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
                if ($points < 0) {
                    $log_msg = "Lost {$points} {$ibforums->vars['currency_name']} from using Mystery Thing.";
                    $msg     = "You lost {$points} {$ibforums->vars['currency_name']}.";
                } else {
                    $log_msg = "Gained {$points} {$ibforums->vars['currency_name']} from using Mystery Thing.";
                    $msg     = "You gained {$points} {$ibforums->vars['currency_name']}.";
                }
            } else {
                $ibforums->db->exec("INSERT INTO ibf_store_inventory VALUES('','{$ibforums->member['id']}','{$temp}','{$price}')");
            }
        } else {
            $points = mt_rand($random_min, $random_max);
            $ibforums->db->exec("UPDATE ibf_members SET points=points+{$points} WHERE id='{$ibforums->member['id']}' LIMIT 1");
            if ($points < 0) {
                $log_msg = "Lost {$points} {$ibforums->vars['currency_name']} from using Mystery Thing.";
                $msg     = "You lost {$points} {$ibforums->vars['currency_name']}.";
            } else {
                $log_msg = "Gained {$points} {$ibforums->vars['currency_name']} from using Mystery Thing.";
                $msg     = "You gained {$points} {$ibforums->vars['currency_name']}.";
            }
        }
        $lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, $log_msg, "", "item");
        $lib->delete_item($ibforums->input['itemid']);
        $lib->redirect($msg, "act=store&code=inventory", "1");
        return;
    }
}
