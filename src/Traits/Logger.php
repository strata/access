<?php

/**
 * Logger Class
 *
 * This Class holds the logger object to easily log from inside the plugin
 *
 * @package    Strata / Access
 * @author     Matt Buckland <matt.b@studio24.net>
 */

namespace Strata\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;

class Logger
{

    /**
     * Log property, holds the logging client
     *
     * @var
     */
    public $client;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        // Set up the logger
        $this->setUpLogger();
    }

    /**
     * Creates a new instance of the Logger.
     */
    public function setUpLogger()
    {
        // create a log channel
        $this->client = new MonoLogger('logger');
        $this->client->pushHandler(new StreamHandler('var/log/access.log'));
    }

}
