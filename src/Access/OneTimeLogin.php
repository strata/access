<?php
declare(strict_types=1);

namespace Strata\Access;

use Ramsey\Uuid\Uuid;

class OneTimeLogin
{
    protected $uuid;
    protected $user_email;

    public function __construct(string $user_email,string $uuid = '')
    {
        $this->user_email = $user_email;

        if (!empty($uuid)) {
            $this->uuid = $uuid;
        }
    }

    public function verifyOTP() : bool
    {
        global $wpdb;

        $query = $wpdb->query("SELECT * FROM strata_access WHERE email='".$this->user_email."' AND uuid='".$this->uuid."'");

        return (bool)$query;

    }
    public function generateOTP() : void
    {

        $this->uuid = Uuid::uuid4()->toString();

    }

    public function sendOTP() : void
    {
        if (!$this->user_email) {
            return;
        }

        $message = '
        <p>Your login for '.wp_title().' is ready.</p>
        <p>Please click the link below to verify and login using your credentials.</p>
        
        '.get_site_url().'/access/otp?email='.$this->user_email.'&uuid='.$this->uuid.' ';

        // @TODO: Replace with proper notifier dependency and text/HTML email.
        mail($this->user_email,'One Time Password',$message);

        $this->storeOTP();
    }

    protected function storeOTP() {

        global $wpdb;

        $data = [
            'email' => $this->user_email,
            'uuid' => $this->uuid
        ];

        $wpdb->insert('strata_access',$data);
    }

    public function setUserEmail(string $email) : void
    {
        $this->user_email = $email;
    }

}