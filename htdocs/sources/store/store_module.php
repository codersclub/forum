<?PHP
/*---------------------------------------------------------------------*\
|   IBStore 2.5															|
+-----------------------------------------------------------------------+
|   (c) 2003 Zachary Anker												|
|	Email: wingzero1018@hotmail.com										|
|   http://www.subzerofx.com/shop/										|
+-----------------------------------------------------------------------+
|	You may edit this file as long as you retain this Copyright notice.	|
|	Redistribution not permitted without permission from Zachary Anker.	|
\*---------------------------------------------------------------------*/
class module
{
	var $inc_postcount = 0;
	var $lock_inc = 0;

	function topic_overcheck($info)
	{
		$ibforums = Ibf::app();
		if ($this->lock_inc)
		{
			return false;
		}
		// $info = array('posts' => 0,'amount_over' => 5,'amount_give' => 1);
		if (!is_array($info))
		{
			return false;
		}

		if ($info['posts'] == $info['amount_over'] && $info['amount_over'] != 0)
		{
			$ibforums->db->exec("UPDATE ibf_members SET points=points+" . $info['amount_give'] . " WHERE id='{$info['give_to']}' LIMIT 1");
		}
	}

	function item_runjob($item, $itemid = "", $info)
	{
		require(ROOT_PATH . "/sources/storeitems/" . $item);
		$run = new item;
		$run->run_job($info);
		return true;
	}

	function post_points($amount)
	{
		global $ibforums, $class;
		if ($this->lock_inc)
		{
			return "";
		}
		return "points=points+" . $amount . ",";
	}

	function post_new_post_action()
	{
		// POST NEW MARKER
		return;
	}

	function post_poll_action()
	{
		// POST POLL MARKER
		return;
	}

	function post_reply_action()
	{
		// POST REPLY MARKER
		return;
	}

	function post_q_reply_action()
	{
		// POST Q REPLY MARKER
		return;
	}

}

?>
