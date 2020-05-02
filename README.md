# Strata Access

Manage access to a web resource via IP address or one-time login. This is useful where you need to restrict access by IP address, but your users have dynamic IP addresses due to their home internet connection. Access in this instance can be granted by a one-time password sent to an authorised email address.

## How this works

This software provides the low-level functionality to make this work. You will need to implement the email form for users to submit an email address to request a one-time login.

### User journey

1. User accesses web resource 
2. If user is within authorised IP address group then access is granted, no cookie is set
2. If user not within authorised IP address group, form displayed to allow user to access resource by email
3. If user email is within an authorised email group, one-time login link is sent via email
4. User can click one-time login link in email to login. Access is automatically remembered for 12 hours

### One-time logins

On a successful request the following happens:

1. On the server a universally unique identifiers (UUID) is generated with a timestamp indicating it's generation time (UUIDs are generated using [Ramsey/UUID](https://uuid.ramsey.dev/))
2. UUID is stored along with the user's IP address and email address to a database
2. Email is sent with a login link containing the UUID (using [Symfony Notifier](https://symfony.com/doc/current/notifier.html))
3. A cookie is set to store the UUID on the user's local computer
4. On each user request the UUID is checked for validity: is it within 12 hours of the one-time login request? If database storage is used does the current user IP address match?
5. On expiry, access requests automatically expire
6. All access requests are logged (using [Monolog](https://github.com/Seldaek/monolog/blob/master/README.md))

## Usage

### Setup access groups

```php
use Strata\Access\AccessGroup;

$group = new AccessGroup();
$group->allowByIp('100.200.300.400');
$group->allowByEmailDomain('studio24.net');
```

Setup access groups from a configuration file:

```php
use Strata\Access\AccessGroup;

$group = new AccessGroup();
$group->fromYaml('path/to/config.json');
```

Configuration format:

```yaml
ip:
  - 100.200.300.400
  - 100.200.300.401
emailDomain:
  - studio24.net
```

### Check if user has access by IP addresss

```php
use Strata\Access\Access;

$access = new Access();
$access->setGroup($group);

// Pass logger of type Monolog\LoggerTrait
$access->setLogger($logger);

// Set user's IP address
$access->setUserIpAddress('100.200.300.401');

if ($access->isValid()) {
    // Do something
}
```

### Generate a one-time login email

We're assuming here you've built a form to allow the user to submit an email address to request access.

```php
use Strata\Access\OneTimeLogin;

// Pass notifier of type Symfony\Component\Notifier\NotifierInterface
$oneTime = new OneTimeLogin($notifier);

// Pass logger of type Monolog\LoggerTrait
$oneTime->setLogger($logger);

$oneTime->setIp('100.200.300.400');
$oneTime->setEmail('test@domain.com');

// Check the email is in an authorised group
if ($oneTime->isValid()) {
    $oneTime->send();
}
```

### Authenticate an access request

We're assuming here the user has clicked on a one-time login link.

```php
use Strata\Access\Access;

$access = new Access();
$access->setLogger($logger);
$access->setGroup($group);

// UUID from one-time login link
$access->setUuid('73a39bea-88b6-11ea-b675-0f1ac6150413');

if ($access->isValid()) {
    // Output a cookie to remember access request (please note this sends HTTP headers to the user)
    $access->setCookie();
	
    // Do something
}
```

### Check subsequent access requests via the cookie

```php
$access = new Access();
$access->setLogger($logger);
$access->fromCookie();

if ($access->isValid()) {
    // Do something
}
```

## Additional security via database storage

Database storage is optional. [Time-based UUIDs](https://uuid.ramsey.dev/en/latest/rfc4122/version1.html) are used so it's possible to calculate expiry time without database storage. 

If database storage is used then an additional checks are made to ensure the user's IP address hasn't changed.

The one-time login link will contain a second parameter based on a hash of the user's IP address, the user's UUID and a secret key. This is stored to a cookie and on subsequent requests is checked against the value on the server.

To use database storage you need to pass a valid `PDO` object to the `setDb()` method.

### SQL

```sql
CREATE TABLE `strata_authorised_access` (
    `uuid` char(36) NOT NULL,
    `ip_address` text NOT NULL,
	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Example usage

Generate a one-time login:

```php
$oneTime = new OneTimeLogin($notifier);
$oneTime->setLogger($logger);

// pass database connection object of type PDO
$oneTime->setDb($pdo);

// pass a secret key that remains the same for all requests (required for database storage)
$oneTime->setSecretKey('ABC123456DEF');

$oneTime->setIp('100.200.300.400');
$oneTime->setEmail('test@domain.com');

if ($oneTime->isValid()) {
    $oneTime->send();
}
```

Authenticate access request:

```php
$access = new Access();
$access->setLogger($logger);
$access->setDb($pdo);
$access->setGroup($group);

// set user current IP address
$access->setUserIpAddress('100.200.300.400');

if ($access->isValid()) {
    $access->setCookie();

    // Do something
}
```

Check subsequent access requests via the cookie:

```php
$access = new Access();
$access->setLogger($logger);
$access->setDb($pdo);
$access->fromCookie();

if ($access->isValid()) {
    // Do something
}
```

## One-time login lifetime

By default an access request authorised via a one-time login is valid for up to 12 hours, after which point a new one-time login is required. The user cookie set has a lifetime equal to the one-time login lifetime. 

Please note the UUID has the generation time encoded within it, so after 12 hours the login is invalidated. 

If you wish, you can amend this. For example, set lifetime to 6 hours:

```
$oneTime = new OneTimeLogin($notifier);
$oneTim->setExpiry(6);
```

## Cookie configuration

You can change the cookie defaults via the `Cookie` class and by passing this to objects via the `setCookie()` method.

```php
use Strata\Access\Cookie;

$cookie = new Cookie();

$access->setCookie($cookie);
```

Available methods (and default values)

* `setName()` - Cookie name to remember authenticated access (default, STRATA_ACCESS)
* `setLifetime()` - Lifetime of cookie (default, equal to the OneTimeLogin lifetime, 12hrs)
* `setCookiePath()` - The path on the server in which the cookie will be available on (default, /)
* `setCookieDomain()` - The (sub)domain that the cookie is available to (defaults to current full hostname)
* `setSecure()` - Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client (defaults to true)
* `setHttpOnly()` - Accessible only via HTTP, so not available to JavaScript (defaults to true)
* `setSameSite()` - [SameSite](https://web.dev/samesite-cookies-explained/) cookie attribute (defaults to 'Strict')

Please note if you're using Strata Access to secure an admin sub-folder, it's recommended to set the cookie path. E.g. if restricting access to the `wp-admin/` folder use:

```php
$cookie = new Cookie();
$cookie->setCookieDomain('/wp-admin/');
```

## Tests

Run [PHPUnit](https://phpunit.readthedocs.io/en/8.0/) tests via:

```
vendor/bin/phpunit
```

## Coding standards

You can test coding standards (PSR2) via:

```
vendor/bin/phpcs
```

Where possible you can auto-fix code via:

```
vendor/bin/phpcbf
```

## Data privacy & cookies

This software sets the following first-party cookie. You may want to add these details to your cookies page on your website if you are using Strata Access in production.

* Name: STRATA_ACCESS
* Purpose: Used to allow access to a protected web resource
* Expires: 12 hours

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
