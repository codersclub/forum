<?
class Log {

var $user = 0;
var $loginTime = 0;
var $totalTime = 0;
var $sleepTime = 0;
var $startTime = 0;
var $interval = 0;
var $forumList = 0;
var $outSize = 0;
var $msgsCount = 0;
var $portion = 0;
var $debugOut = "";

	function Log() {

	global $sess, $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		$this->user = "<a href=" . $ibforums->vars['board_url'] . "?showuser=" . $sess->member['id'] . ">" . $sess->member['name'] . "</a>";
		$this->loginTime = time();

		$this->reset();

		if(!is_file($ibforums->vars['client_log_file'])) {
			
			$file = fopen($ibforums->vars['client_log_file'], "w");

			if($file) {

				fwrite($file, "<link href='style.css' rel='stylesheet' type='text/css'>
                        <table border='1'><tr>
						<td>Login time</td>
						<td>User Name</td>
						<td>Total time, sec</td>
						<td>Sleep time, sec</td>
						<td>Interval</td>
						<td>Forums</td>
						<td>Messages read</td>
						<td>Reply size</td>
						<td>Debug Output</td>
						</tr>\n");
				fclose($file);
			}
		}
	}

	function reset() {

		$this->startTime = microtime();
		$this->startTime = explode (' ', $this->startTime);
		$this->startTime = $this->startTime[1] + $this->startTime[0];
		$this->sleepTime = 0;
		$this->outSize = 0;
		$this->msgsCount = 0;
		$this->debugOut = "";
	}

	function addSleepTime($time) {

	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		$this->sleepTime += $time;
	}

	function incrementPortion() {

	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		$this->portion++;
	}

	function addDebugOut($debugInfo) {

	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		if(!$ibforums->vars['client_do_debug_log'])
			return;

		$this->debugOut .= $debugInfo . "<br>";
		
	}

	function addInterval($min, $max, $desc) {

	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		if("reply" == $desc) {

			if(0 == $min) {
				
				$this->interval = "(new topic)";
			} else {

				$this->interval = "<a href=" . $ibforums->vars['board_url'] . "?showtopic=" . $min . ">" . $min . "</a> (" . $desc . ")";
			}
		} else if("get thread" == $desc) {

			$this->interval = "<a href=" . $ibforums->vars['board_url'] . "?showtopic=" . $min . ">" . $min . "</a> (" . $desc . ")";
		} else {
			
			$this->interval = date("d-m-Y, H:i:s",$min) . " - " . date("d-m-Y, H:i:s",$max) . " (" . $desc . ")";
		}
	}

	function addForumList($boards) {

	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

        $boards = str_replace( "(", "", $boards );
        $boards = str_replace( ")", "", $boards );
        $boards = str_replace( "\'", "", $boards );
        $arr = explode(",", $boards);

        $nfirst = 0;
        $this->forumList = "&nbsp;";
        foreach($arr as $id) {

            if($nfirst)
                $this->forumList .= ", ";

            $nfirst = 1;
            $this->forumList .= "<a href=" . $ibforums->vars['board_url'] . "?showforum=" . $id . ">" . $id . "</a>";
        }
	}

	function addOutSize($outSize) {
	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		$this->outSize += $outSize;
	}

    function addMsgsCount($count) {
	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

        $this->msgsCount += $count;
    }

	function doLog() {

	global $ibforums;

		if(!$ibforums->vars['client_do_log'])
			return;

		$this->totalTime = microtime();
		$this->totalTime = explode (' ', $this->totalTime);
		$this->totalTime = $this->totalTime[1] + $this->totalTime[0];
		$this->totalTime -= $this->startTime;

		$file = fopen($ibforums->vars['client_log_file'], "a");

		if($file) {

			$to_log =
				"<tr><td>". strftime("%d/%m/%y-%H:%M:%S",$this->loginTime) . "</td>" .
				"<td>". $this->user . "</td>" .
				"<td>". round($this->totalTime, 2) . "</td>" .
				"<td>". round($this->sleepTime, 2) . "</td>" .
				"<td>". $this->interval . "</td>" .
				"<td>". (("" != $this->forumList) ? $this->forumList : "&nbsp") . "</td>" .
				"<td>". $this->msgsCount . "</td>" .
				"<td>". $this->outSize . "</td>" .
				"<td>". ($this->debugOut ? $this->debugOut : "&nbsp") . "</td></tr>\n";
			fwrite($file, $to_log);
			fclose($file);
		}
	}
}
?>
