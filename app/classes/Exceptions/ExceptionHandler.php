<?php
namespace Exceptions;
/**
 * This file is part of Forum package.
 *
 * serafim <nesk@xakep.ru> (24.06.2014 20:31)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ExceptionHandler
{
    /**
     * Create exception handler
     */
    public function __construct($debug = true, $reporting = E_ALL)
    {
        error_reporting($reporting);

        if ($debug) {
            $whoops = new Run;

            #$handler = ($this->isCli())
            #   ? new JsonResponseHandler
            #    : new PrettyPageHandler;
            $handler = new PrettyPageHandler;

            $whoops->pushHandler($handler);
            $whoops->register();
        } else {
            die('Something went wrong =( ' .
                'Please try again later.');
        }
    }
}
