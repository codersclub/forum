<? 

class ForumList {

var $forums;

	function ForumList() {

	}

	function Process() {
		global $VerNum;
		echo "Forums list:<br>";

		if(0 == count($this->forums))
			return;
		$order = 0;
		foreach( $this->forums as $id => $name ) {

			echo "<p>~";
			echo $id   . "~<br>~";
			echo $name . "~<br>~";
			if( $VerNum >= 1.309 )
				echo $order . "~<br>~";
			echo "~<p>";
			$order++;
		}
	}
}

?>
