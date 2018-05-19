<?php
namespace OliverKlee\CsvToOpenImmo\SchedulerTask;

use OliverKlee\CsvToOpenImmo\Service\CsvReader;
use OliverKlee\CsvToOpenImmo\Service\OpenImmoBuilder;
use OliverKlee\CsvToOpenImmo\Service\Zipper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Scheduler task for converting CSV to OpenImmo
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvConverter extends AbstractTask
{
    /**
     * @var string
     */
    private $sourceFolder = '';

    /**
     * @var string
     */
    private $targetFolder = '';

    /**
     * @var bool
     */
    private $deleteProcessedSourceFiles = false;

    /**
     * @var Zipper
     */
    private $zipper = null;

    /**
     * @var CsvReader
     */
    private $csvReader = null;

    /**
     * @return string
     */
    public function getSourceFolder()
    {
        return $this->sourceFolder;
    }

    /**
     * @param string $sourceFolder
     *
     * @return void
     */
    public function setSourceFolder($sourceFolder)
    {
        $this->sourceFolder = $sourceFolder;
    }

    /**
     * @return string
     */
    public function getTargetFolder()
    {
        return $this->targetFolder;
    }

    /**
     * @param string $targetFolder
     *
     * @return void
     */
    public function setTargetFolder($targetFolder)
    {
        $this->targetFolder = $targetFolder;
    }

    /**
     * @return bool
     */
    public function getDeleteProcessedSourceFiles()
    {
        return $this->deleteProcessedSourceFiles;
    }

    /**
     * @param bool $deleteProcessedSourceFiles
     *
     * @return void
     */
    public function setDeleteProcessedSourceFiles($deleteProcessedSourceFiles)
    {
        $this->deleteProcessedSourceFiles = $deleteProcessedSourceFiles;
    }

    /**
     * @return void
     */
    private function initializeServices()
    {
        $this->zipper = GeneralUtility::makeInstance(Zipper::class);
        $this->zipper->setSourceDirectory($this->sourceFolder);
        $this->zipper->setTargetDirectory($this->targetFolder);

        $this->csvReader = GeneralUtility::makeInstance(CsvReader::class);
    }

    /**
     * Executes this task.
     *
     * @return bool true on successful execution, false on error
     */
    public function execute()
    {
        $this->initializeServices();
        $sourceZipPaths = $this->zipper->getPathsOfZipsToExtract();
        foreach ($sourceZipPaths as $sourceZipPath) {
            $this->handleSingleZip($sourceZipPath);
        }

        return true;
    }

    /**
     * @param string $sourceZipPath
     *
     * @return void
     */
    private function handleSingleZip($sourceZipPath)
    {
        $sourceBaseName = basename($sourceZipPath);
        $extractionDirectory = $this->zipper->extractZip($sourceBaseName);
        $csvFilePath = $this->csvReader->findFirstCsvFileInDirectory($extractionDirectory);
        if ($csvFilePath === null) {
            return;
        }
        $csvLines = $this->csvReader->readCsv($csvFilePath);
        if (empty($csvLines)) {
            return;
        }

        $openImmoBuilder = GeneralUtility::makeInstance(OpenImmoBuilder::class);
        foreach ($csvLines as $csvLine) {
            $openImmoBuilder->addObject($csvLine);
        }
        $document = $openImmoBuilder->build();

        $this->zipper->createTargetZip($sourceBaseName, $document);

        if ($this->getDeleteProcessedSourceFiles()) {
            unlink($sourceZipPath);
        }
    }
}
