<?php
declare(strict_types=1);

namespace Strata\Access;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Rfc4122\UuidV1;
use Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use Strata\Access\Exception\InvalidEmailException;
use Strata\Access\Exception\InvalidIpAddressException;
use Strata\Access\Exception\MissingParamsException;
use Strata\Access\Traits\LoggerTrait;

class OneTimeLogin
{
    use LoggerTrait;

    /** @var string */
    protected $uuid;

    /** @var string */
    protected $secret;

    /** @var string */
    protected $userHash;

    /** @var string */
    protected $email;

    /** @var string */
    protected $ip;

    /**
     * Return or generate the UUID
     *
     * Email and IP address must be set before calling this method
     *
     * @return string
     */
    public function getUuid (): UuidV1
    {
        if ($this->uuid !== null) {
            return $this->uuid;
        }

        $nodeProvider = new RandomNodeProvider();
        $this->uuid = Uuid::uuid1($nodeProvider->getNode());

        return $this->uuid;
    }

    /**
     * Return creation time from a time-based UUID
     *
     * @param Uuid $uuid
     * @return \DateTimeInterface
     */
    public function getUuidDateTime(UuidV1 $uuid): \DateTimeInterface
    {
        return $uuid->getDateTime();
    }

    /**
     * Set secret to help create unique hashes
     *
     * @param string $secret
     */
    public function setSecret(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * Return or generate the user hash
     *
     * @return string
     * @throws MissingParamsException
     */
    public function getUserHash(): string
    {
        if ($this->userHash !== null) {
            return $this->userHash;
        }

        if (empty($this->getEmail()) || empty($this->getIp()) || empty($this->secret)) {
            throw MissingParamsException('You must set email, IP address and secret before generating the user hash');
        }

        $this->userHash = password_hash($this->getEmail() . $this->getIp() . $this->secret,  PASSWORD_DEFAULT);
        return $this->userHash;
    }

    /**
     * Verify a user hash against a passed email and IP address
     *
     * @param string $hash
     * @param string $email
     * @param string $ip
     * @return bool
     */
    public function verifyHash(string $hash, string $email, string $ip): bool
    {
        return password_verify($email . $ip . $this->secret, $hash);
    }


    /**
     * Check database for valid OTP/UUID entry.
     */
    public function verifyOTP() : bool
    {
        global $wpdb;

        // @TODO Fix and clean up query, use PDO.

        $query = $wpdb->query("SELECT * FROM strata_access WHERE email='".$this->email."' AND uuid='".$this->uuid."'");

        return (bool)$query;

    }


    /**
     * Generate OTP / UUID
     */
    public function generateOTP() : void
    {

        $this->uuid = Uuid::uuid4()->toString();

    }


    /**
     * Send OTP password link to user.
     */
    public function sendOTP() : void
    {
        if (!$this->email) {
            return;
        }

        $message = '
        <p>Your login for '.wp_title().' is ready.</p>
        <p>Please click the link below to verify and login using your credentials.</p>
        
        '.get_site_url().'/access/otp?email='.$this->email.'&uuid='.$this->uuid.' ';

        // @TODO: Replace with proper notifier dependency and text/HTML email.
        mail($this->email,'One Time Password',$message);

        $this->storeOTP();
    }


    /**
     * Save OTP and email to database.
     */
    protected function storeOTP() : void
    {

        global $wpdb;

        $data = [
            'email' => $this->email,
            'uuid' => $this->uuid
        ];

        $wpdb->insert('strata_access',$data);
    }


    /**
     * Set email
     *
     * @param string $email
     * @throws InvalidEmailException
     */
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException(sprintf('Email address "%s" is not valid', $email));
        }
        $this->email = $email;
    }

    /**
     * Return email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set IP address
     *
     * @param string $ip
     * @throws InvalidIpAddressException
     */
    public function setIp(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidIpAddressException(sprintf('IP address "%s" is not valid', $ip));
        }
        $this->ip = $ip;
    }

    /**
     * Return IP address
     *
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

}