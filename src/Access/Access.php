<?php
declare(strict_types=1);

namespace Strata\Access;

use Monolog\Logger;

class Access
{
    public $group;
    protected $logger;
    protected $user_ip;
    public $user_domain;
    protected $uuid;

    public function __construct()
    {

    }

    public function isValid(): bool
    {
        // Check user IP against access group list.
        if (!empty($this->user_ip) && $this->group->checkIP($this->user_ip) )
        {
            echo '<h1>Passed IP Check</h1>';
            return true;
        }

        if (!empty($_COOKIE['strata_access']) && $_COOKIE['strata_access'] == 'verified')
        {
            echo '<h1>Passed Cookie Check</h1>';
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

    public function setUserEmailDomain(string $domain) : void
    {
        if (strpos($domain,'@')) {
            $email = explode('@',$domain);
            $domain = $email[1];
        }

        $this->user_domain = $domain;
    }
}