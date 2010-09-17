<? 

class GetPosts {

var $timeMin;
var $timeMax;
var $Posts;
var $entryCount = 0;

	function GetPosts() {

		$this->TimeSettings();
		$this->FetchPosts();
	}

	function FetchPosts() {

	}

	function TimeSettings() {

	global $period, $from, $to, $ibforums;

// time settings
		$this->timeMin = $this->timeMax = 0;
		$arrDate = getdate( time() );

		if( $period == "new" ) {
// all new posts

			if( $from == "" ) {

				echo $errMsg;
				exit;
			}

			$this->timeMin = $from;

			$this->timeMax = time() + 1;/*mktime(
					0,
					0,
					0, 
					$arrDate["mon"],
					$arrDate["mday"] + 1,
					$arrDate["year"]
			);                        */

		} else if( $period == "today" ) {
// today posts

			$this->timeMin = mktime(
					0,
					0,
					0, 
					$arrDate["mon"],
					$arrDate["mday"],
					$arrDate["year"]
			);

			$this->timeMax = mktime(
					0,
					0,
					0, 
					$arrDate["mon"],
					$arrDate["mday"] + 1,
					$arrDate["year"]
			);
		} else if( $period == "week" ) {
// week posts

			$this->timeMin = mktime(
					0,
					0,
					0, 
					$arrDate["mon"],
					$arrDate["mday"] - 7,
					$arrDate["year"]
			);

			$this->timeMax = mktime(
					0,
					0,
					0, 
					$arrDate["mon"],
					$arrDate["mday"] + 1,
					$arrDate["year"]
			);
		} else if( $period == "month" ) {
// month posts

			$this->timeMin = mktime(
					0,
					0,
					0, 
					$arrDate["mon"] - 1,
					$arrDate["mday"],
					$arrDate["year"]
			);

			$this->timeMax = mktime(
					0,
					0,
					0, 
					$arrDate["mon"],
					$arrDate["mday"] + 1,
					$arrDate["year"]
			);
		} else if( $period == "period" ) {
// period posts

			if($from == "" || $to == "") {

				echo $errMsg;
				exit;
			}

			$this->timeMin = $from;
			$this->timeMax = $to;
		} else {

			echo $errMsg;
			exit;
		}

		//check for period restrictions
		if($ibforums->vars['client_max_load_period']) {

			if($this->timeMax - $this->timeMin > $ibforums->vars['client_max_load_period']) {

				$this->timeMin = $this->timeMax - $ibforums->vars['client_max_load_period'];
			}
		}
	}

	function Process() {

	global $Log, $VerNum;


		//only first entry should contain this record
		if(0==$this->entryCount) {

			$out = "Found posts:<br>";
		}

		$this->entryCount++;

		$breakStr = "~<br>~";

		if(0 != count($this->Posts)) {

			//create stream for portion
			foreach( $this->Posts as $id => $post ) {

				$out .= "<p>~";
				$out .= $post['FORUM']     . $breakStr;
				$out .= $id                . $breakStr;
				$out .= $post['TOPIC1']    . $breakStr;
				$out .= $post['TOPIC2']    . $breakStr;
				$out .= $post['NAME']      . $breakStr;
			   if( $VerNum >=1.309 )				
				$out .= $post['AUTID']     . $breakStr;
				$out .= $post['TITLE']     . $breakStr;
				$out .= $post['POST']      . $breakStr;
				$out .= $post['DATE']      . $breakStr;
				$out .= $post['ICON']      . $breakStr;
				$out .= "~<p>";
			}
		}

		$Log->addOutSize(strlen($out));
		$Log->incrementPortion();

		print $out;

		//out portion to the user
		flush();
		ob_flush();

		$this->Posts = array();
	}
}

?>
