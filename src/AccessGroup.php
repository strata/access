<?php
declare(strict_types=1);

namespace Strata\Access;

use Symfony\Component\Yaml\Yaml;

class AccessGroup
{
    /**
     * @var array
     */
    protected $allowed_ips;

    /**
     * @var array
     */
    protected $allowed_domains;

    /**
     * AccessGroup Constructor
     */
    public function __construct(string $configFile = '')
    {

        $this->allowed_ips = [];
        $this->allowed_domains = [];

        if (!empty($configFile))
        {
            $this->loadConfig($configFile);
        }

    }

    /**
     * Search access group for IP.
     * @returns bool
     */
    public function checkIP(string $ip) {

        return in_array($ip,$this->allowed_ips,true);

    }

    /**
     * Search access group for domain.
     * @returns bool
     */
    public function checkDomain(string $domain) {

        return in_array($domain,$this->allowed_domains,true);

    }


    /**
     * Add IP address to the access group.
     */
    public function allowByIp(string $ip): void
    {
        if (!in_array($ip,$this->allowed_ips)) {

            $this->allowed_ips[] = $ip;

        }
    }


    /**
     * Add domain name to the access group.
     */
    public function allowByEmailDomain(string $emailDomain) : void
    {
        if (!in_array($emailDomain,$this->allowed_domains)) {

            $this->allowed_domains[] = $emailDomain;

        }
    }


    /**
     * Load in config YML file to setup access group
     */
    public function loadConfig(string $configFile) : void
    {
        $configFile = __DIR__.'/../../config/'.$configFile;

        if (!file_exists($configFile)) {
            // @TODO: Throw Exception & Log
            return;
        }

        $config_file = file_get_contents($configFile);

        // @TODO Catch Exceptions
        $config = Yaml::parse($config_file);

        if (!empty($config['ip'])) {

            if (!is_array($config['ip'])) {
                $config['ip'] = [$config['ip']];
            }

            foreach ($config['ip'] as $ip) {
                $this->allowByIp($ip);
            }

        }

        if (!empty($config['emailDomain'])) {

            if (!is_array($config['emailDomain'])) {
                $config['emailDomain'] = [$config['emailDomain']];
            }

            foreach ($config['emailDomain'] as $emailDomain) {
                $this->allowByEmailDomain($emailDomain);
            }

        }
    }

}