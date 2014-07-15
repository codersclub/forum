<?php

namespace Logs\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

/**
 * Simple handler wrapper that filters records based on clients IP
 *
 * @author Yuri "Jureth" Minin
 */
class IPFilterHandler extends AbstractHandler
{

    /**
     * Handler or factory callable($record, $this)
     *
     * @var callable|\Monolog\Handler\HandlerInterface
     */
    protected $handler;

    /**
     * IP addresses to check
     * @var array|string
     */
    protected $ip = [];
    /**
     * Cache result or not. Uses for skip repeated calculations according
     * to fact that remote address can't be changed during the session
     * @var bool
     */
    protected $cacheResult = true;
    /**
     * Cached result
     * @var bool|null
     */
    private $cachedResult = null;

    /**
     * @param callable|HandlerInterface $handler Handler or factory callable($record, $this).
     * @param string|array $ip Array of IP addresses to pass, can use * instead of exact numbers, e.g "192.168.*.*"
     * @param bool $cache_result Cache ip comparison results.
     * @param int $level Minimal level to process
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($handler, array $ip = [], $cache_result = true, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->handler     = $handler;
        $this->bubble      = $bubble;
        $this->ip          = $ip;
        $this->cacheResult = $cache_result;
    }

    public function getIP(){
        return $this->ip;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        // The same logic as in FingersCrossedHandler
        if (!$this->handler instanceof HandlerInterface) {
            if (!is_callable($this->handler)) {
                throw new \RuntimeException("The given handler (" . json_encode(
                        $this->handler
                    ) . ") is not a callable nor a Monolog\\Handler\\HandlerInterface object");
            }
            $this->handler = call_user_func($this->handler, $record, $this);
            if (!$this->handler instanceof HandlerInterface) {
                throw new \RuntimeException("The factory callable should return a HandlerInterface");
            }
        }

        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        $this->handler->handle($record);

        return false === $this->bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return parent::isHandling($record) && $this->checkIP();
    }

    protected function checkIP()
    {
        //the clients ip address doesn't change during single session, so we don't need to recalculate it again
        if ($this->cachedResult === null || !$this->cacheResult) {
            $c_addr = [];
            preg_match('/(\d+|\*)\.(\d+|\*)\.(\d+|\*)\.(\d+|\*)/', $_SERVER['REMOTE_ADDR'], $c_addr);
            array_shift($c_addr); //remove full match
            $this->cachedResult = false; //deny by default. It will also work if ip list is empty
            foreach ($this->ip as $ip) {
                $addr = [];
                if (preg_match('/(\d+|\*)\.(\d+|\*)\.(\d+|\*)\.(\d+|\*)/', $ip, $addr)) {
                    array_shift($addr);
                    $tmp_result = true;
                    for ($c = reset($c_addr), $p = reset($addr); $c !== false; $c = next($c_addr), $p = next($addr)) {
                        if ($c !== $p && $p !== '*') {
                            $tmp_result = false; //check failed
                            break;
                        }
                    }
                    if ($tmp_result) { //check passed, we can pass
                        $this->cachedResult = true;
                        break;
                    }
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'Pattern "%s" can not be used to check ip addresses',
                        $ip
                    ));
                }
            }
        }
        return $this->cachedResult;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $filtered = array();
        foreach ($records as $record) {
            if ($this->isHandling($record)) {
                $filtered[] = $record;
            }
        }

        $this->handler->handleBatch($filtered);
    }
}
