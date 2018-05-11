<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit\SchedulerTask;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\SchedulerTask\CsvConverter;
use OliverKlee\CsvToOpenImmo\SchedulerTask\CsvConverterConfiguration;
use org\bovigo\vfs\vfsStream;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Scheduler;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvConverterConfigurationTest extends UnitTestCase
{
    /**
     * @var CsvConverterConfiguration
     */
    private $subject = null;

    /**
     * @var Scheduler|ProphecySubjectInterface
     */
    private $schedulerStub = null;

    /**
     * @var SchedulerModuleController|ProphecySubjectInterface
     */
    private $schedulerModuleControllerStub = null;

    /**
     * @var LanguageService
     */
    private $languageServiceBackup = null;

    protected function setUp()
    {
        $this->languageServiceBackup = isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
        $this->schedulerModuleControllerStub = $this->prophesize(SchedulerModuleController::class)->reveal();
        $GLOBALS['LANG'] = $this->prophesize(LanguageService::class)->reveal();
        $this->schedulerStub = $this->prophesize(Scheduler::class)->reveal();
        GeneralUtility::setSingletonInstance(Scheduler::class, $this->schedulerStub);

        $this->subject = new CsvConverterConfiguration();
    }

    protected function tearDown()
    {
        GeneralUtility::removeSingletonInstance(Scheduler::class, $this->schedulerStub);
        $GLOBALS['LANG'] = $this->languageServiceBackup;

        parent::tearDown();
    }

    /**
     * @test
     */
    public function implementsAdditionalFieldProvider()
    {
        static::assertInstanceOf(AdditionalFieldProviderInterface::class, $this->subject);
    }

    /**
     * @return string[][]
     */
    public function emptyFieldsDataProvider()
    {
        return [
            '"source folder" input' => [
                'field-source-folder',
                '<input type="text" name="tx_scheduler[csv_to_openimmo][source_folder]" size="100" value="">',
            ],
            '"target folder" input' => [
                'field-target-folder',
                '<input type="text" name="tx_scheduler[csv_to_openimmo][target_folder]" size="100" value="">',
            ],
            '"delete source files" checkbox' => [
                'field-delete-source-files',
                '<input class="checkbox" type="checkbox" ' .
                'name="tx_scheduler[csv_to_openimmo][delete_source_files]" value="1">',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider emptyFieldsDataProvider
     *
     * @param string $fieldId
     * @param string $html
     */
    public function getAdditionalFieldsInitiallyReturnsEmptyFields($fieldId, $html)
    {
        $taskInfo = [];
        $result = $this->subject->getAdditionalFields($taskInfo, null, $this->schedulerModuleControllerStub);

        static::assertSame($html, $result[$fieldId]['code']);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsRendersSourceFolderFromTask()
    {
        $sourceFolder = '/foo/bar';

        $csvConverter = new CsvConverter();
        $csvConverter->setSourceFolder($sourceFolder);

        $taskInfo = [];
        $result = $this->subject->getAdditionalFields($taskInfo, $csvConverter, $this->schedulerModuleControllerStub);

        $expectedHtml = '<input type="text" name="tx_scheduler[csv_to_openimmo][source_folder]" size="100" value="' .
            htmlspecialchars($sourceFolder) . '">';
        static::assertSame($expectedHtml, $result['field-source-folder']['code']);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsRendersTargetFolderFromTask()
    {
        $targetFolder = '/foo/bar';

        $csvConverter = new CsvConverter();
        $csvConverter->setTargetFolder($targetFolder);

        $taskInfo = [];
        $result = $this->subject->getAdditionalFields($taskInfo, $csvConverter, $this->schedulerModuleControllerStub);

        $expectedHtml = '<input type="text" name="tx_scheduler[csv_to_openimmo][target_folder]" size="100" value="' .
            htmlspecialchars($targetFolder) . '">';
        static::assertSame($expectedHtml, $result['field-target-folder']['code']);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsRendersDeleteFlagFromTask()
    {
        $csvConverter = new CsvConverter();
        $csvConverter->setDeleteProcessedSourceFiles(true);

        $taskInfo = [];
        $result = $this->subject->getAdditionalFields($taskInfo, $csvConverter, $this->schedulerModuleControllerStub);

        $expectedHtml = '<input class="checkbox" type="checkbox" ' .
            'name="tx_scheduler[csv_to_openimmo][delete_source_files]" value="1" checked="checked">';
        static::assertSame($expectedHtml, $result['field-delete-source-files']['code']);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsRendersSourceFolderFromFormDataOverTaskData()
    {
        $sourceFolder = '/foo/bar';

        $csvConverter = new CsvConverter();
        $csvConverter->setSourceFolder('/other-folder');

        $taskInfo = ['csv_to_openimmo' => ['source_folder' => $sourceFolder]];
        $result = $this->subject->getAdditionalFields($taskInfo, $csvConverter, $this->schedulerModuleControllerStub);

        $expectedHtml = '<input type="text" name="tx_scheduler[csv_to_openimmo][source_folder]" size="100" value="' .
            htmlspecialchars($sourceFolder) . '">';
        static::assertSame($expectedHtml, $result['field-source-folder']['code']);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsRendersTargetFolderFromFormDataOverTaskData()
    {
        $targetFolder = '/foo/bar';

        $csvConverter = new CsvConverter();
        $csvConverter->setTargetFolder('/other-folder');

        $taskInfo = ['csv_to_openimmo' => ['target_folder' => $targetFolder]];
        $result = $this->subject->getAdditionalFields($taskInfo, $csvConverter, $this->schedulerModuleControllerStub);

        $expectedHtml = '<input type="text" name="tx_scheduler[csv_to_openimmo][target_folder]" size="100" value="' .
            htmlspecialchars($targetFolder) . '">';
        static::assertSame($expectedHtml, $result['field-target-folder']['code']);
    }

    /**
     * @test
     */
    public function getAdditionalFieldsRendersDeleteFlagFromFormDataOverTaskData()
    {
        $csvConverter = new CsvConverter();
        $csvConverter->setDeleteProcessedSourceFiles(false);

        $taskInfo = ['csv_to_openimmo' => ['delete_source_files' => '1']];
        $result = $this->subject->getAdditionalFields($taskInfo, $csvConverter, $this->schedulerModuleControllerStub);

        $expectedHtml = '<input class="checkbox" type="checkbox" ' .
            'name="tx_scheduler[csv_to_openimmo][delete_source_files]" value="1" checked="checked">';
        static::assertSame($expectedHtml, $result['field-delete-source-files']['code']);
    }

    /**
     * @test
     */
    public function saveAdditionalFieldsSavesSourceFolder()
    {
        $sourceFolder = '/bar';
        $submittedData = ['csv_to_openimmo' => ['source_folder' => $sourceFolder]];

        $csvConverter = new CsvConverter();

        $this->subject->saveAdditionalFields($submittedData, $csvConverter);

        static::assertSame($sourceFolder, $csvConverter->getSourceFolder());
    }

    /**
     * @test
     */
    public function saveAdditionalFieldsSavesTargetFolder()
    {
        $targetFolder = '/bar';
        $submittedData = ['csv_to_openimmo' => ['target_folder' => $targetFolder]];

        $csvConverter = new CsvConverter();

        $this->subject->saveAdditionalFields($submittedData, $csvConverter);

        static::assertSame($targetFolder, $csvConverter->getTargetFolder());
    }

    /**
     * @test
     */
    public function saveAdditionalFieldsSavesDeleteFlag()
    {
        $submittedData = ['csv_to_openimmo' => ['delete_source_files' => '1']];

        $csvConverter = new CsvConverter();

        $this->subject->saveAdditionalFields($submittedData, $csvConverter);

        static::assertTrue($csvConverter->getDeleteProcessedSourceFiles());
    }

    /**
     * @test
     */
    public function validateAdditionalFieldsForValidDataReturnsTrue()
    {
        $rootDirectory = vfsStream::setup();
        vfsStream::create(['source' => [], 'target' => []], $rootDirectory);

        $validData = [
            'csv_to_openimmo' => [
                'source_folder' => vfsStream::url('root/source'),
                'target_folder' => vfsStream::url('root/target'),
                'delete_source_files' => '1',
            ],
        ];

        static::assertTrue($this->subject->validateAdditionalFields($validData, $this->schedulerModuleControllerStub));
    }

    /**
     * @return array[]
     */
    public function invalidDataDataProvider()
    {
        $rootDirectory = vfsStream::setup();
        vfsStream::create(['source' => [], 'target' => []], $rootDirectory);
        $validSourceFolder = vfsStream::url('root/source');
        $validTargetFolder = vfsStream::url('root/target');
        $file = vfsStream::url('root/test');

        return [
            'no data at all' => [[]],
            'empty converter data' => ['csv_to_openimmo' => []],
            'valid' => [
                'csv_to_openimmo' => [
                    'source_folder' => $validSourceFolder,
                    'target_folder' => $validTargetFolder,
                    'delete_source_files' => '1',
                ],
            ],
            'empty source folder' => [
                'csv_to_openimmo' => [
                    'source_folder' => '',
                    'target_folder' => $validTargetFolder,
                    'delete_source_files' => '1',
                ],
            ],
            'empty target folder' => [
                'csv_to_openimmo' => [
                    'source_folder' => $validSourceFolder,
                    'target_folder' => '',
                    'delete_source_files' => '1',
                ],
            ],
            'file source folder' => [
                'csv_to_openimmo' => [
                    'source_folder' => $file,
                    'target_folder' => $validTargetFolder,
                    'delete_source_files' => '1',
                ],
            ],
            'file target folder' => [
                'csv_to_openimmo' => [
                    'source_folder' => $validSourceFolder,
                    'target_folder' => $file,
                    'delete_source_files' => '1',
                ],
            ],
            'inexistent source folder' => [
                'csv_to_openimmo' => [
                    'source_folder' => '/inexistent',
                    'target_folder' => $validTargetFolder,
                    'delete_source_files' => '1',
                ],
            ],
            'inexistent target folder' => [
                'csv_to_openimmo' => [
                    'source_folder' => $file,
                    'target_folder' => '/inexistent',
                    'delete_source_files' => '1',
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param array $invalidData
     * @dataProvider invalidDataDataProvider
     */
    public function validateAdditionalFieldsForInvalidDataReturnsFalse(array $invalidData)
    {
        static::assertFalse($this->subject->validateAdditionalFields($invalidData, $this->schedulerModuleControllerStub));
    }
}
