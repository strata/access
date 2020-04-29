<?php
declare(strict_types=1);

namespace Strata\Access;

use Monolog\Logger;

class Access
{
    protected $group;
    protected $logger;
    protected $user_ip;
    protected $user_domain;
    protected $uuid;

    public function __construct()
    {

    }

    public function isValid(): bool
    {
        // Check user IP against access group list.
        if ( $this->group->checkIP($this->user_ip) )
        {
            return true;
        }

        return false;
    }


    public function setUuid(string $uuid) : void
    {
        $this->uuid = $uuid;
    }

    public function setAccessGroup(AccessGroup $group) : void
    {
        $this->group = $group;
    }

    public function setLogger(Logger $logger) : void
    {
        $this->logger = $logger;
    }

    public function setUserIpAddress(string $ip) : void
    {
        $this->user_ip = $ip;
    }
}