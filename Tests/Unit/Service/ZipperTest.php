<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\Zipper;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ZipperTest extends UnitTestCase
{
    /**
     * @var Zipper
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new Zipper();
    }

    /**
     * @test
     */
    public function isSingleton()
    {
        static::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function setExtractionDirectoryOverwritesExtractionDirectory()
    {
        $directory = '/foo/bar';

        $this->subject->setExtractionDirectory($directory);

        static::assertSame($directory, $this->subject->getExtractionDirectory());
    }
}
