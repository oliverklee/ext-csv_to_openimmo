<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\Service;

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

    /**
     * @var string
     */
    private $schemaPath = '';

    protected function setUp()
    {
        $this->schemaPath = __DIR__ . '/../../../Resources/Private/Specs/openimmo_127b.xsd';
        if (!file_exists($this->schemaPath)) {
            static::markTestSkipped('This test can only be run with a schema file present.');
        }

        $this->subject = new OpenImmoBuilder();
    }

    /**
     * @test
     */
    public function documentWithoutObjectsIsValid()
    {
        $document = $this->subject->build();

        $document->schemaValidate($this->schemaPath);

        static::assertSame([], libxml_get_errors());
    }
}
