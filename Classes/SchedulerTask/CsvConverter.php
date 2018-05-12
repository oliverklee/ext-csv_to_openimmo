<?php
namespace OliverKlee\CsvToOpenImmo\SchedulerTask;

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
     * Executes this task.
     *
     * @return bool true on successful execution, false on error
     */
    public function execute()
    {
        return true;
    }

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
}
