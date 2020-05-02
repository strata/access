<?php
declare(strict_types=1);

namespace Strata\Data\Tests;

use PHPUnit\Framework\TestCase;
use Strata\Access\Traits\LoggerTrait;
use Monolog\Logger;
use Monolog\Handler\NoopHandler;

/**
 * Create local class to test trait
 */
class TestLogger {
    use LoggerTrait;
}

final class LoggerTraitTest extends TestCase
{

    public function testHasLogger()
    {
        $handler = new NoopHandler();
        $logger = new Logger('test');
        $logger->pushHandler($handler);

        $testLogger = new TestLogger();
        $this->assertFalse($testLogger->hasLogger());

        $testLogger->setLogger($logger);
        $this->assertTrue($testLogger->hasLogger());

        $this->assertTrue($testLogger->getLogger() instanceof Logger);
    }

}
