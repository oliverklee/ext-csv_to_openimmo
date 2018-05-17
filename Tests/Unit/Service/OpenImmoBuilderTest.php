<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\OpenImmoBuilder;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class OpenImmoBuilderTest extends UnitTestCase
{
    /**
     * @var OpenImmoBuilder
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new OpenImmoBuilder();
    }

    /**
     * @test
     */
    public function buildReturnsDomDocument()
    {
        static::assertInstanceOf(\DOMDocument::class, $this->subject->build());
    }
}
