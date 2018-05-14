<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\Zipper;
use org\bovigo\vfs\vfsStream;

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

    /**
     * @var string
     */
    private $extractionDirectory = '';

    protected function setUp()
    {
        $this->subject = new Zipper();
        $this->subject->setSourceDirectory(__DIR__ . '/../Fixtures/Zips');

        $rootDirectory = vfsStream::setup();
        vfsStream::create(['extraction' => []], $rootDirectory);
        $this->extractionDirectory = vfsStream::url('root/extraction');

        $this->subject->setExtractionDirectory($this->extractionDirectory);
    }

    /**
     * @test
     */
    public function getExtractionDirectoryByDefaultReturnsAbsoluteDirectoryWithinTempDirectory()
    {
        $subject = new Zipper();

        static::assertSame(PATH_site . 'typo3temp/' . 'csv_to_openimmo/', $subject->getExtractionDirectory());
    }

    /**
     * @test
     */
    public function getPathsOfZipsToExtractWithoutSourceDirectoryThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new Zipper();

        $subject->getPathsOfZipsToExtract();
    }

    /**
     * @test
     */
    public function getPathsOfZipsToExtractWithEmptySourceDirectoryThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $subject = new Zipper();
        $subject->setSourceDirectory('');

        $subject->getPathsOfZipsToExtract();
    }

    /**
     * @test
     */
    public function getPathsOfZipsToExtractForEmptyDirectoryReturnsEmptyArray()
    {
        $this->subject->setSourceDirectory(__DIR__ . '/../Fixtures/EmptyFolder');

        static::assertSame([], $this->subject->getPathsOfZipsToExtract());
    }

    /**
     * @test
     */
    public function getPathsOfZipsToExtractIgnoresNonZipFiles()
    {
        $this->subject->setSourceDirectory(__DIR__ . '/../Fixtures/OtherFiles');

        static::assertSame([], $this->subject->getPathsOfZipsToExtract());
    }

    /**
     * @test
     */
    public function getPathsOfZipsForSourceDirectoryWithoutTrailingSlashToExtractFindsZipFiles()
    {
        $result = $this->subject->getPathsOfZipsToExtract();

        static::assertNotEmpty($result);
        static::assertContains('/objects.zip', array_shift($result));
    }

    /**
     * @test
     */
    public function getPathsOfZipsForSourceDirectoryWithTrailingSlashToExtractFindsZipFiles()
    {
        $result = $this->subject->getPathsOfZipsToExtract();

        static::assertNotEmpty($result);
        static::assertContains('/objects.zip', array_shift($result));
    }

    /**
     * @test
     */
    public function extractZipForNonZipFileThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->extractZip('non-zip.txt');
    }

    /**
     * @test
     */
    public function extractZipForInexistentFolderThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->extractZip('inexistent.zip');
    }

    /**
     * @test
     */
    public function extractZipCreatesFolderWithFilesFromZip()
    {
        $createdDirectory = $this->subject->extractZip('objects.zip');

        static::assertSame($this->extractionDirectory . '/objects/', $createdDirectory);

        static::fileExists($createdDirectory . 'objects.csv');
        static::fileExists($createdDirectory . '00517278.jpg');
    }
}
