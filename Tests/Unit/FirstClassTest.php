<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Tests\Unit\Fixtures\LoadableClass;

/**
 * Test case.
 */
class FirstClassTest extends UnitTestCase
{
    /**
     * @test
     */
    public function methodReturnsTrue()
    {
        $firstClassObject = new LoadableClass();
        static::assertTrue($firstClassObject->returnsTrue());
    }
}
