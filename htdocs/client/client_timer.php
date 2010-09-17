<?

// Performance timer
class PerfTimer {

var $starttime;
var $counter;
var $time_period;
var $factor;

	function PerfTimer() {

	global $ibforums;
	        
		if(!$ibforums->vars['client_db_restrictions_on'])
			return;

		$this->counter = 0;
		$this->factor = (float) $ibforums->vars['client_loads_factor'];
		$this->time_period = $this->factor / (100 - $this->factor) * 1000000;

		if($this->factor >= 100) {

			$this->factor = 0;
		} else if($this->factor <= 0) {
			
			$this->factor = 10000;
		} else {

			$this->factor = (float) (100. - $this->factor) / $this->factor;
		}
		$this->startTimer();
	}

	function startTimer() {

		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$this->starttime = $mtime;
	}

	function endTimer() {

		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round (($endtime - $this->starttime) * 1000000, 5);
		return $totaltime;
	}

	function sleep() {

	global $ibforums, $Log;

		if(!$ibforums->vars['client_db_restrictions_on'])
			return;

		$this->counter++;

		$time = $this->endTimer();
	
		if($ibforums->vars['client_use_usleep']) {

			usleep($time * $this->factor);
			$Log->addSleepTime(($time * $this->factor) / 1000000.);
			$this->startTimer();
		} else {

			if($time >= $this->time_period) {

				sleep(1);
				$Log->addSleepTime(1);
				$this->startTimer();
			}
		}
	}
}

?>