<?php

use Strata\Access\Access;
use Strata\Access\AccessGroup;
use Strata\Access\OneTimeLogin;
use Strata\Logger\Logger;


class StrataAccessPlugin
{
    /**
     * @var StrataAccessPlugin
     */
    private static $instance;

    /**
     * @var Access
     */
    private $access;


    /**
     * Plugin Constructor
     */
    public function __construct()
    {
        $this->load_dependencies();
        $this->create_routes();
        $this->initialise_plugin();
    }


    /**
     * Add singleton functionality
     * @returns StrataAccessPlugin
     */
    public static function getInstance()
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Get current access instance.
     * @returns Strata\Access\Access
     */
    public function getAccess() {

        return $this->access;
    }


    /**
     * Instantiate all necessary Access code
     */
    private function initialise_plugin() : void
    {

        $this->logger = new Logger();

        $this->access = new Access;
        $this->access->setLogger($this->logger->client);

        $accessgroup = new AccessGroup('config.yml');
        //$accessgroup->allowByIp('::1');
        $this->access->setAccessGroup($accessgroup);
        $this->access->setUserIpAddress(($_SERVER['X-Forwarded-For']) ?? $_SERVER['REMOTE_ADDR']);


        add_action('login_init', function() {

            if ($this->access->isValid()) {

                echo '<h1>Access Granted</h1>';

            } else {

                // Throw up OTP form.

                // @TODO: Twig
                require(__DIR__ . '/views/loginform.php');
                exit;

            }

        });

    }


    /**
     * Load dependencies / Composer
     */
    private function load_dependencies() : void
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }


    /**
     * Create routes in Wordpress, requires Timber plugin
     */
    private function create_routes() : void
    {
        $languageSlug = substr(home_url(), -2);

        Routes::map($languageSlug . '/access/verify', function ($params) {

            $access = StrataAccessPlugin::getInstance()->getAccess();

            $email = $_POST['access_otp_email'];

            if (empty($email)) {
                echo '<p>Error: Missing email</p>';
                exit;
            }

            $access->setUserEmailDomain($email);

            // Check user email against access group list.
            if  ($access->group->checkDomain($access->user_domain) )
            {
                // Send OTP

                $otp = new OneTimeLogin($email);

                $otp->generateOTP();

                $otp->sendOTP();

                // @TODO: Twig
                require(__DIR__ . '/views/otp-sent.php');

            } else {

                echo 'N/A';

            }


            exit;
        });

        Routes::map($languageSlug . '/access/otp', function ($params) {

            $access = StrataAccessPlugin::getInstance()->getAccess();

            $email = $_GET['email'];
            $uuid = $_GET['uuid'];

            if (empty($email)) {
                echo '<p>Error: Missing email</p>';
                exit;
            }

            $otp = new OneTimeLogin($email,$uuid);

            if ($otp->verifyOTP() ) {

                // @TODO TESTING ONLY. Secure this cookie!
                setcookie('strata_access', 'verified',time()+(3600*12),'/');

                header('Location:'.home_url().'/wp-admin');

            } else {

                echo 'Access Denied';

            }


            exit;
        });

    }


}