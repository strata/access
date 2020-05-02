<?php
declare(strict_types=1);

namespace Strata\Data\Tests;

use PHPUnit\Framework\TestCase;
use Strata\Access\Exception\InvalidEmailException;
use Strata\Access\Exception\InvalidIpAddressException;
use Strata\Access\Exception\MissingParamsException;
use Strata\Access\OneTimeLogin;

final class OneTimeLoginTest extends TestCase
{

    public function testParams()
    {
        $onetime = new OneTimeLogin();

        $this->assertNull($onetime->getEmail());
        $this->assertNull($onetime->getIp());

        $onetime->setEmail('test@studio24.net');
        $onetime->setIp('192.168.0.1');

        $this->assertEquals('test@studio24.net', $onetime->getEmail());
        $this->assertEquals('192.168.0.1', $onetime->getIp());
    }

    public function testInvalidEmail()
    {
        $onetime = new OneTimeLogin();

        $this->expectException(InvalidEmailException::class);
        $onetime->setEmail('Fake email address @ somewhere there');
    }

    public function testInvalidIp()
    {
        $onetime = new OneTimeLogin();

        $this->expectException(InvalidIpAddressException::class);
        $onetime->setIp('My test string');
    }

    public function testUuid()
    {
        $onetime = new OneTimeLogin();

        $onetime->setEmail('test@studio24.net');
        $onetime->setIp('192.168.0.1');

        $this->assertFalse(false);
        $uuid = $onetime->getUuid();

        $this->assertIsString($uuid->toString());

        $date = new \DateTime();
        $oneTimeDate = $onetime->getUuidDateTime($uuid);
        $this->assertTrue($date > $oneTimeDate);
        $this->assertTrue($onetime->verifyUuidTime($uuid, 1));

        $timeInFuture = new \DateTime();
        $timeInFuture->add(new \DateInterval('PT2H'));
        $this->assertFalse($onetime->verifyUuidTime($uuid, 1, $timeInFuture));

        $onetime->setSecret('ABC123');
        $hash = $onetime->getUserHash();
        $this->assertTrue($onetime->verifyHash($hash, 'test@studio24.net', '192.168.0.1'));
    }

    public function testMissingParams()
    {
        $onetime = new OneTimeLogin();
        $onetime->setEmail('test@studio24.net');

        $this->expectException(MissingParamsException::class);
        $hash = $onetime->getUserHash();
    }


}
