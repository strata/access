<?php
declare(strict_types=1);

namespace Strata\Access\Traits;

use Monolog\Logger;

/**
 * This trait holds the logger object to easily log
 */
trait LoggerTrait
{
    /**
     * Logging client
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Set the logger
     *
     * @param Logger $logger Monolog object
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Do we have a valid logger setup?
     *
     * @return bool
     */
    public function hasLogger(): bool
    {
        if ($this->getLogger() instanceof Logger) {
            return true;
        }
        return false;
    }

    /**
     * Return the logger
     *
     * @return Logger or null if logger not set
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

}
