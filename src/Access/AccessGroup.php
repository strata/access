<?php
declare(strict_types=1);

namespace Strata\Access;

use Monolog\Logger;
use Symfony\Component\Yaml\Yaml;

class AccessGroup
{
    protected $allowed_ips;
    protected $allowed_domains;
    protected $logger;

    public function __construct(string $configFile = '')
    {

        $this->allowed_ips = [];
        $this->allowed_domains = [];

        $configFile = __DIR__.'/../../config/'.$configFile;

        if (!empty($configFile) && file_exists($configFile))
        {
            $this->loadConfig($configFile);
        }

    }

    public function checkIP(string $ip) {

        return in_array($ip,$this->allowed_ips,true);

    }

    public function checkDomain(string $domain) {

        return in_array($domain,$this->allowed_domains,true);

    }

    public function allowByIp(string $ip): void
    {
        if (!in_array($ip,$this->allowed_ips)) {

            $this->allowed_ips[] = $ip;

        }
    }

    public function allowByEmailDomain(string $emailDomain) : void
    {
        if (!in_array($emailDomain,$this->allowed_domains)) {

            $this->allowed_domains[] = $emailDomain;

        }
    }

    public function loadConfig(string $configFile) : void
    {
        $config_file = file_get_contents($configFile);

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

    public function setLogger(Logger $logger) : void
    {
        $this->logger = $logger;
    }

}