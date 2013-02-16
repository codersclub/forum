<?
//---------------------------------------------------
// IBStore Basic Steal
//---------------------------------------------------
class item
{
	var $name = "Steal";
	var $desc = "Steal some money from any user.";
	var $extra_one = "0";
	var $extra_two = "1000";
	var $extra_three = "50";
	var $apply_protect = 1;

	// This is triggered when a Admin adds a item to the store
	// after they have selected a name a icon and a description and stock
	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::instance();
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Minumum amount aloud to steal?</b><br>The lowest amount a user can steal from another user.",
		                                       $SKIN->form_input("extra_one", $EXTRA['extra_one'])
		                                  ));
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Maximum amount aloud to steal</b><br>The highest amount a user can steal from another user.",
		                                       $SKIN->form_input("extra_two", $EXTRA['extra_two'])
		                                  ));
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Chance of a Successful</b><br>The percentage of a Successful steal. (e.g 50 would give them 50/50 odds of a successful steal)",
		                                       $SKIN->form_input("extra_three", $EXTRA['extra_three'])
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

	// We trigger this function as soon as "Use Item" is clicked.
	// this is were you would put the things a user could change (Like a members title as a e.g)
	function on_buy()
	{
	}

	function on_use($itemid = "")
	{
		global $ibforums;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Steal</td>
			</tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Username to Steal From:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='username'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Amount to Steal:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='amount'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Go!'></td>
			   </tr>
			</form>
EOF;
	}

	// This will take all the input from on_use (If any)
	// and run the querys or change some thing...Which probly would be in a query
	function run_job()
	{
	}

	function do_on_use($minimum, $maximum, $percentage)
	{
		global $ibforums, $print, $lib;

		$minimum    = (int)$minimum;
		$percentage = (int)$percentage;
		$maximum    = (int)$maximum;
		if ($ibforums->input['amount'] < $minimum)
		{
			$lib->itemerror("The amount you are trying to steal is to low. (lowest is {$minimum} )");
		} else
		{
			if ($ibforums->input['amount'] > $maximum)
			{
				$lib->itemerror("The amount you are trying to steal is to high. (max is {$maximum} )");
			}
		}
		$stmt = $ibforums->db->query("SELECT id,name,mgroup,points FROM ibf_members WHERE LOWER(name)='" . strtolower($ibforums->input['username']) . "' LIMIT 1");
		if ($stmt->rowCount() == 0)
		{
			$lib->itemerror("We cannot seem to find that name.");
		}
		if ($ibforums->member['points'] <= 0)
		{
			$lib->itemerror("Sorry you're {$ibforums->vars['currency_name']} is going to go into the negtive values if this steal goes through.");
		}
		$user = $stmt->fetch();
		$temp = $user['points'];
		if ($temp - $ibforums->input['amount'] <= 0)
		{
			$lib->itemerror("Sorry you are trying to to steal more then the user has. (The user only has {$user['points']} )");
		}
		$lost_amount = $ibforums->input['amount'] * 2;
		$chance      = mt_rand(1, 100);
		if ($chance <= $percentage)
		{
			$msg  = "You stole {$ibforums->input['amount']} from {$user['name']} Successfully!";
			$lost = $user['points'] - $ibforums->input['amount'];
			$ibforums->db->exec("UPDATE ibf_members SET points=points+" . $ibforums->input['amount'] . " WHERE id='{$ibforums->member['id']}' LIMIT 1");
			$ibforums->db->exec("UPDATE ibf_members SET points='{$lost}' WHERE id='{$user['id']}' LIMIT 1");
			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "Stole {$ibforums->input['amount']} from {$user['name']} Successfully.", "", "item");
		} else
		{
			$msg = "You wern't able to steal {$ibforums->input['amount']} from {$user['name']}, and lost {$lost_amount} trying to steal from him.";
			$ibforums->member['points'] -= $lost_amount;
			$ibforums->db->exec("UPDATE ibf_members SET points='{$ibforums->member['points']}' WHERE id='{$ibforums->member['id']}' LIMIT 1");
			$lib->write_log($ibforums->member['id'], $ibforums->member['name'], $ibforums->member['id'], $ibforums->member['name'], 0, "Tried to steal {$ibforums->input['amount']} from {$user['name']} lost {$lost_amount}.", "", "item");
		}
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect($msg, "act=store", "1");
		return "";
	}
}

?>


