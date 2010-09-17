<?

class PrivateProtocol {

var $messages;

	function PrivateProtocol() {

	}

	function Process() {

		global $VerNum;
		echo "Found messages:<br>";

		if(empty($this->messages))
			return;
		foreach($this->messages as $message) {
			echo "<p>~";
			echo $message['privmsgs_id']	. "~<br>~";
			echo $message['privmsgs_subject']	. "~<br>~";
			echo $message['privmsgs_text']	. "~<br>~";
			echo $message['privmsgs_date']	. "~<br>~";
			echo $message['username']. "~<br>~";
		 if( $VerNum >=1.309 )
			echo $message['from_id']. "~<br>~";
			echo "~<p>";

		}
		$this->messages = array();
	}
}

?>