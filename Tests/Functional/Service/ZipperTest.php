<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\Zipper;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    /**
     * @var string
     */
    private $targetDirectory = '';

    protected function setUp()
    {
        $this->subject = new Zipper();
        $this->subject->setSourceDirectory(__DIR__ . '/../Fixtures/Zips');

        $rootDirectory = vfsStream::setup();

        vfsStream::create(['extraction' => [], 'target' => []], $rootDirectory);
        $this->extractionDirectory = vfsStream::url('root/extraction');
        $this->subject->setExtractionDirectory($this->extractionDirectory);

        // We cannot use vfsStream for this as ZipArchive does not work with it.
        $this->targetDirectory = PATH_site . 'typo3temp/openimmo-csv-target/';
        GeneralUtility::mkdir_deep($this->targetDirectory);
        $this->subject->setTargetDirectory($this->targetDirectory);
        $this->testFilesToDelete[] = $this->targetDirectory;
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

        static::assertDirectoryExists($createdDirectory);
        static::fileExists($createdDirectory . 'objects.csv');
        static::fileExists($createdDirectory . '00517278.jpg');
    }

    /**
     * @test
     */
    public function removeExtractionFolderForZipForExistingFolderRemovesFolder()
    {
        $createdDirectory = $this->subject->extractZip('objects.zip');

        $this->subject->removeExtractionFolderForZip('objects.zip');

        static::assertDirectoryNotExists($createdDirectory);
    }

    /**
     * @test
     */
    public function removeExtractionFolderForZipForRemovedFolderNotThrowsException()
    {
        $createdDirectory = $this->subject->extractZip('objects.zip');
        GeneralUtility::rmdir($createdDirectory, true);

        $this->subject->removeExtractionFolderForZip('objects.zip');

        static::assertDirectoryNotExists($createdDirectory);
    }

    /**
     * @test
     */
    public function createTargetZipCreatesZipFileNamedLikeSourceZip()
    {
        $xmlDocument = new \DOMDocument('1.0', 'utf-8');

        $targetZipPath = $this->subject->createTargetZip('objects.zip', $xmlDocument);

        static::assertFileExists($targetZipPath);
        static::assertContains('objects-', $targetZipPath);
    }

    /**
     * @test
     */
    public function createTargetZipDumpsOpenImmoXmlIntoTargetZip()
    {
        $xmlDocument = new \DOMDocument('1.0', 'utf-8');

        $targetZipPath = $this->subject->createTargetZip('objects.zip', $xmlDocument);

        $zip = new \ZipArchive();
        static::assertTrue($zip->open($targetZipPath, \ZipArchive::CHECKCONS));

        static::assertInternalType('int', $zip->locateName('objects.xml'));
        static::assertSame($xmlDocument->saveXML(), $zip->getFromName('objects.xml'));

        $zip->close();
    }
}
