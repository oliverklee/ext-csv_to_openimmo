<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\SchedulerTask;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\SchedulerTask\CsvConverter;
use OliverKlee\CsvToOpenImmo\Service\Zipper;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Scheduler;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvConverterTest extends UnitTestCase
{
    /**
     * @var CsvConverter
     */
    private $subject = null;

    /**
     * @var Scheduler|ProphecySubjectInterface
     */
    private $schedulerStub = null;

    /**
     * @var string
     */
    private $sourceDirectory = '';

    /**
     * @var string
     */
    private $targetDirectory = '';

    protected function setUp()
    {
        $this->schedulerStub = $this->prophesize(Scheduler::class)->reveal();
        GeneralUtility::setSingletonInstance(Scheduler::class, $this->schedulerStub);

        $this->subject = new CsvConverter();

        $extractionDirectory = PATH_site . 'typo3temp/' . Zipper::EXTRACTION_DIRECTORY_IN_TEMP;
        GeneralUtility::mkdir_deep($extractionDirectory);
        $this->testFilesToDelete[] = $extractionDirectory;

        $this->sourceDirectory = PATH_site . 'typo3temp/openimmo-csv-source';
        GeneralUtility::mkdir_deep($this->sourceDirectory);
        $this->subject->setSourceFolder($this->sourceDirectory);
        $this->testFilesToDelete[] = $this->sourceDirectory;

        $this->targetDirectory = PATH_site . 'typo3temp/openimmo-csv-target';
        GeneralUtility::mkdir_deep($this->targetDirectory);
        $this->subject->setTargetFolder($this->targetDirectory);
        $this->testFilesToDelete[] = $this->targetDirectory;
    }

    protected function tearDown()
    {
        GeneralUtility::removeSingletonInstance(Scheduler::class, $this->schedulerStub);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function executeForFilesSourceDirectoryNotCreatesZipInTargetDirectory()
    {
        $this->subject->execute();

        $filesInTargetDirectory = GeneralUtility::getAllFilesAndFoldersInPath([], $this->targetDirectory, 'zip');

        static::assertSame([], $filesInTargetDirectory);
    }

    /**
     * @test
     */
    public function executeForNoCsvZipsInSourceDirectoryNotCreatesZipInTargetDirectory()
    {
        copy(__DIR__ . '/../Fixtures/OtherFiles/empty.txt', $this->sourceDirectory . '/empty.txt');

        $this->subject->execute();

        $filesInTargetDirectory = GeneralUtility::getAllFilesAndFoldersInPath([], $this->targetDirectory, 'zip');

        static::assertSame([], $filesInTargetDirectory);
    }

    /**
     * @test
     */
    public function executeForNonCsvZipInSourceDirectoryNotCreatesZipInTargetDirectory()
    {
        copy(__DIR__ . '/../Fixtures/NonCsvZips/objects.zip', $this->sourceDirectory . '/objects.zip');

        $this->subject->execute();

        $filesInTargetDirectory = GeneralUtility::getAllFilesAndFoldersInPath([], $this->targetDirectory, 'zip');

        static::assertSame([], $filesInTargetDirectory);
    }

    /**
     * @test
     */
    public function executeForCsvZipInSourceDirectoryCreatesZipInTargetDirectory()
    {
        copy(__DIR__ . '/../Fixtures/Zips/objects.zip', $this->sourceDirectory . '/objects.zip');

        $this->subject->execute();

        $filesInTargetDirectory = GeneralUtility::getAllFilesAndFoldersInPath([], $this->targetDirectory, 'zip');

        static::assertCount(1, $filesInTargetDirectory);
    }

    /**
     * @test
     */
    public function executeForTwoCsvZipsInSourceDirectoryCreatesTwoZipsInTargetDirectory()
    {
        copy(__DIR__ . '/../Fixtures/Zips/objects.zip', $this->sourceDirectory . '/objects.zip');
        copy(__DIR__ . '/../Fixtures/Zips/objects.zip', $this->sourceDirectory . '/objects2.zip');

        $this->subject->execute();

        $filesInTargetDirectory = GeneralUtility::getAllFilesAndFoldersInPath([], $this->targetDirectory, 'zip');

        static::assertCount(2, $filesInTargetDirectory);
    }

    /**
     * @test
     */
    public function executeForCsvZipInSourceDirectoryWithoutDeleteNotDeletesSourceZip()
    {
        $this->subject->setDeleteProcessedSourceFiles(false);

        $sourceZip = $this->sourceDirectory . '/objects.zip';
        copy(__DIR__ . '/../Fixtures/Zips/objects.zip', $sourceZip);

        $this->subject->execute();

        static::assertFileExists($sourceZip);
    }

    /**
     * @test
     */
    public function executeForCsvZipInSourceDirectoryWithDeleteDeletesSourceZip()
    {
        $this->subject->setDeleteProcessedSourceFiles(true);

        $sourceZip = $this->sourceDirectory . '/objects.zip';
        copy(__DIR__ . '/../Fixtures/Zips/objects.zip', $sourceZip);

        $this->subject->execute();

        static::assertFileNotExists($sourceZip);
    }

    /**
     * @test
     */
    public function executeForNonCsvZipInSourceDirectoryWithDeleteNotDeletesSourceZip()
    {
        $this->subject->setDeleteProcessedSourceFiles(true);

        $sourceZip = $this->sourceDirectory . '/objects.zip';
        copy(__DIR__ . '/../Fixtures/NonCsvZips/objects.zip', $sourceZip);

        $this->subject->execute();

        static::assertFileExists($sourceZip);
    }
}
