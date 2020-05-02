<?php
declare(strict_types=1);

namespace Strata\Access;

use Strata\Access\Traits\LoggerTrait;

class Access
{
    use LoggerTrait;

    /**
     * @var \Strata\Access\AccessGroup
     */
    public $group;

    /**
     * @var string
     */
    protected $user_ip;

    /**
     * @var string
     */
    public $user_domain;

    /**
     * @var string
     */
    protected $uuid;


    /**
     * Access Constructor
     */
    public function __construct()
    {

    }

    /**
     * Check IP and Access COOKIE/SESSION against access group.
     * @returns bool
     */
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

    /**
     * Property Setters
     */
    public function setUuid(string $uuid) : void
    {
        $this->uuid = $uuid;
    }

    public function setAccessGroup(AccessGroup $group) : void
    {
        $this->group = $group;
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