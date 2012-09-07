<?php

class Debug {
	private $starttime = 0;
	function startTimer()
	{
		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$this->starttime = $mtime;
	}

	function endTimer()
	{
		$mtime = microtime ();
		$mtime = explode (' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round (($endtime - $this->starttime), 5);
		return $totaltime;
	}
}

