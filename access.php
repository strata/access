<?php
/**
 * Plugin Name:     Strata / Access
 * Plugin URI:      http://www.studio24.net/
 * Description:     Lock down CMS access to specific IP's and email domains via a one-time password.
 * Author:          Studio 24
 * Author URI:      http://www.studio24.net/
 * Text Domain:     access
 * Domain Path:     /
 * Version:         0.0.1
 *
 * @package    Strata / Access
 */

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Strata\Access\Access;
use Strata\Access\AccessGroup;
use Strata\Logger\Logger;

/*
 * Hook into Wordpress login form to check access is valid.
 */

add_action( 'login_init', 'strata_access_validation' );

function strata_access_validation() {

    $logger = new Logger();

    $access = new Access;
    $access->setLogger($logger->client);

    $accessgroup = new AccessGroup('config.yml');

    //$accessgroup->allowByIp('::1');

    $access->setAccessGroup($accessgroup);

    $access->setUserIpAddress( ($_SERVER['X-Forwarded-For']) ?? $_SERVER['REMOTE_ADDR'] );

    if ( $access->isValid() ) {

        echo '<h1>Access Granted</h1>';

    } else {

        // Throw up OTP form.

        die(file_get_contents(__DIR__ . '/views/loginform.html'));

    }

}




