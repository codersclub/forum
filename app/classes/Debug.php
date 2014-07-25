<?php

class Debug
{
    /**
     * @var int Timer' start time
     */
    protected $starttime = 0;
    /**
     * @var int error Levels to report. Values are similar to values of the errors_reporting option
     */
    protected $errorLevels;
    /**
     * @var int log level
     */
    public $level;
    /**
     * @var stdClass Various statistics. May be refactored in future
     */
    public $stats;

    /**
     * Singleton realization
     * @return Debug
     */
    public static function instance()
    {
        static $instance = null;

        if (!$instance instanceof Debug) {
            $class    = get_called_class();
            $instance = new $class();
        }
        return $instance;
    }

    public function __construct()
    {
        global $INFO;

        $this->level = $INFO['debug_level'];
        //
        $this->stats = new stdClass();
    }

    /**
     * Starts the timer
     */
    public function startTimer()
    {
        $mtime           = explode(' ', microtime());
        $mtime           = $mtime[1] + $mtime[0];
        $this->starttime = $mtime;
    }

    /**
     * Returns the execution time
     * @return float
     */
    public function executionTime()
    {
        $mtime     = explode(' ', microtime());
        $mtime     = $mtime[1] + $mtime[0];
        $endtime   = $mtime;
        $totaltime = round(($endtime - $this->starttime), 5);
        return $totaltime;
    }

    /**
     * Handler to process registering application event
     * A bit dumb but there is no normal method to intercept class creation right now
     * @param CoreApplication $app
     */
    public function onAfterRegisterApplication($app)
    {
        if ($app->db instanceof IBPDO) {
            $query_counter = function (EventObject $event) {
                if (!isset($this->stats->queriesCount)) {
                    $this->stats->queriesCount = 0;
                }
                $this->stats->queriesCount++;
            };
            $app->db->attachEventHandler('afterQuery', $query_counter);
            $app->db->attachEventHandler('afterExec', $query_counter);
            $app->db->attachEventHandler('afterPrepare', $query_counter);
        }
    }
}
