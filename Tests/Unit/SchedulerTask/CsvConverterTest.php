<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit\SchedulerTask;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\SchedulerTask\CsvConverter;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Scheduler;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

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

    protected function setUp()
    {
        $this->schedulerStub = $this->prophesize(Scheduler::class)->reveal();
        GeneralUtility::setSingletonInstance(Scheduler::class, $this->schedulerStub);

        $this->subject = new CsvConverter();
    }

    protected function tearDown()
    {
        GeneralUtility::removeSingletonInstance(Scheduler::class, $this->schedulerStub);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isSchedulerTask()
    {
        static::assertInstanceOf(AbstractTask::class, $this->subject);
    }

    /**
     * @test
     */
    public function getDeleteProcessedSourceFilesInitiallyReturnsFalse()
    {
        static::assertFalse($this->subject->getDeleteProcessedSourceFiles());
    }

    /**
     * @test
     */
    public function setDeleteProcessedSourceFilesSetsdeleteProcessedSourceFiles()
    {
        $this->subject->setDeleteProcessedSourceFiles(true);

        static::assertTrue($this->subject->getDeleteProcessedSourceFiles());
    }

    /**
     * @test
     */
    public function getSourceFolderInitiallyReturnsEmptyString()
    {
        static::assertSame('', $this->subject->getSourceFolder());
    }

    /**
     * @test
     */
    public function setSourceFolderSetsSourceFolder()
    {
        $value = 'Club-Mate';
        $this->subject->setSourceFolder($value);

        static::assertSame($value, $this->subject->getSourceFolder());
    }

    /**
     * @test
     */
    public function getTargetFolderInitiallyReturnsEmptyString()
    {
        static::assertSame('', $this->subject->getTargetFolder());
    }

    /**
     * @test
     */
    public function setTargetFolderSetsTargetFolder()
    {
        $value = 'Club-Mate';
        $this->subject->setTargetFolder($value);

        static::assertSame($value, $this->subject->getTargetFolder());
    }
}
