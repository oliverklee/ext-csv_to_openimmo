<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\CsvReader;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvReaderTest extends UnitTestCase
{
    /**
     * @var CsvReader
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new CsvReader();
    }

    /**
     * @test
     */
    public function isSingleton()
    {
        static::assertInstanceOf(SingletonInterface::class, $this->subject);
    }
}
