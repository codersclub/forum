<? 

// Stub class. Needed for the IBForums engine.
class Post {
}

class Html {
	function errors($error) {

		echo "MESSAGE: " . $error;
		exit();
	}
}

require "sources/ReplyPrivate.php";

$Log = new Log();

class IPBReplyPrivate extends ReplyPrivate {

	function IPBReplyPrivate() {

	global $std, $DB, $ibforums;

		$query = "SELECT id
			FROM ibf_members
			WHERE name='".addslashes($ibforums->input['U_Username'])."'";
		$DB->query($query);
		
		$row = $DB->fetch_row();
		$user_id = $row['id'];
		if(!empty($user_id))
		{

			$std->sendpm($user_id, $ibforums->input['M_Message'], $ibforums->input['M_Subject']);
			echo "~OK~";
		}
		else
		{
			echo "MESSAGE: Unknown user name...";
		}		
		
	}
}
?>
