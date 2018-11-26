<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\Support;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class OpenImmoSchemaTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $schemaPath = '';

    /**
     * @return void
     */
    private function skipForNoSchemaFile()
    {
        $this->schemaPath = __DIR__ . '/../../../Resources/Private/Specs/openimmo_127b.xsd';
        if (!\file_exists($this->schemaPath)) {
            static::markTestSkipped('This test can only be run with a schema file present.');
        }
    }

    /**
     * @test
     */
    public function minimalOpenImmoFileIsValid()
    {
        $this->skipForNoSchemaFile();

        $document = new \DOMDocument();
        $document->load(__DIR__ . '/Fixtures/MinimalOpenImmo.xml');

        $document->schemaValidate($this->schemaPath);
        static::assertSame([], \libxml_get_errors());
    }
}
