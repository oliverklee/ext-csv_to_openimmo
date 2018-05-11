<?php
namespace OliverKlee\CsvToOpenImmo\SchedulerTask;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Provides the configuration fields fot the CsvConverter task.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvConverterConfiguration implements AdditionalFieldProviderInterface
{
    /**
     * Gets additional fields to render in the form to add/edit a task.
     *
     * @param array $taskInfo Values of the fields from the add/edit task form
     * @param AbstractTask|null $task The task object being edited. Null when adding a task!
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return array A two-dimensional array,
     *         array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        /** @var CsvConverter $task */
        if (isset($taskInfo['csv_to_openimmo']) && is_array($taskInfo['csv_to_openimmo'])) {
            $converterData = $taskInfo['csv_to_openimmo'];
            $sourceFolder = (string)$converterData['source_folder'];
            $targetFolder = (string)$converterData['target_folder'];
            $deleteSourceFiles = $converterData['delete_source_files'] ? ' checked="checked"' : '';
        } elseif ($task instanceof CsvConverter) {
            /** @var CsvConverter $task */
            $sourceFolder = $task->getSourceFolder();
            $targetFolder = $task->getTargetFolder();
            $deleteSourceFiles = $task->getDeleteProcessedSourceFiles() ? ' checked="checked"' : '';
        } else {
            $sourceFolder = '';
            $targetFolder = '';
            $deleteSourceFiles = '';
        }

        $additionalFields = [
            'field-source-folder' => [
                'code' => '<input type="text" name="tx_scheduler[csv_to_openimmo][source_folder]" size="100" value="' .
                    htmlspecialchars($sourceFolder) . '">',
                'label' => $this->localize('sourceFolder'),
            ],
            'field-target-folder' => [
                'code' => '<input type="text" name="tx_scheduler[csv_to_openimmo][target_folder]" size="100" value="' .
                    htmlspecialchars($targetFolder) . '">',
                'label' => $this->localize('targetFolder'),
            ],
            'field-delete-source-files' => [
                'code' => '<input class="checkbox" type="checkbox" ' .
                    'name="tx_scheduler[csv_to_openimmo][delete_source_files]" value="1"' . $deleteSourceFiles . '>',
                'label' => $this->localize('deleteProcessedSourceFiles'),
            ],
        ];

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values.
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     *
     * @return bool true if validation was okay (or selected class is not relevant), false otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $isValid = true;
        $converterData = isset($submittedData['csv_to_openimmo']) ? (array)$submittedData['csv_to_openimmo'] : [];

        if (empty($converterData['source_folder'])) {
            $schedulerModule->addMessage($this->localize('errors.sourceFolder.empty'), FlashMessage::ERROR);
            $sourceFolder = '';
            $isValid = false;
        } else {
            $sourceFolder = (string)$converterData['source_folder'];
            $deleteSourceFiles = !empty($converterData['delete_source_files'])
                && (bool)$converterData['delete_source_files'];

            if (!file_exists($sourceFolder)) {
                $schedulerModule->addMessage($this->localize('errors.sourceFolder.nonexistent'), FlashMessage::ERROR);
                $isValid = false;
            } elseif (!is_dir($sourceFolder)) {
                $schedulerModule->addMessage($this->localize('errors.sourceFolder.noDirectory'), FlashMessage::ERROR);
                $isValid = false;
            } elseif ($deleteSourceFiles && !is_writable($sourceFolder)) {
                $schedulerModule->addMessage($this->localize('errors.sourceFolder.notWritable'), FlashMessage::ERROR);
                $isValid = false;
            } elseif (!$this->isAbsolutePath($sourceFolder)) {
                $schedulerModule->addMessage($this->localize('errors.sourceFolder.relative'), FlashMessage::ERROR);
                $isValid = false;
            }
        }
        if (empty($converterData['target_folder'])) {
            $schedulerModule->addMessage($this->localize('errors.targetFolder.empty'), FlashMessage::ERROR);
            $targetFolder = '';
            $isValid = false;
        } else {
            $targetFolder = (string)$converterData['target_folder'];
            if (!file_exists($targetFolder)) {
                $schedulerModule->addMessage($this->localize('errors.targetFolder.nonexistent'), FlashMessage::ERROR);
                $isValid = false;
            } elseif (!is_dir($targetFolder)) {
                $schedulerModule->addMessage($this->localize('errors.targetFolder.noDirectory'), FlashMessage::ERROR);
                $isValid = false;
            } elseif (!is_writable($targetFolder)) {
                $schedulerModule->addMessage($this->localize('errors.targetFolder.notWritable'), FlashMessage::ERROR);
                $isValid = false;
            } elseif (!$this->isAbsolutePath($targetFolder)) {
                $schedulerModule->addMessage($this->localize('errors.targetFolder.relative'), FlashMessage::ERROR);
                $isValid = false;
            }
        }
        if ($isValid && ($sourceFolder === $targetFolder)) {
            $schedulerModule->addMessage($this->localize('errors.folders.identical'), FlashMessage::ERROR);
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isAbsolutePath($path)
    {
        return strncmp($path, 'vfs://', 6) === 0 || GeneralUtility::isAbsPath($path);
    }

    /**
     * Takes care of saving the additional fields' values in the task's object.
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param AbstractTask $task Reference to the scheduler backend module
     *
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        if (!$task instanceof CsvConverter) {
            return;
        }

        /** @var CsvConverter $task */
        $converterData = isset($submittedData['csv_to_openimmo']) ? (array)$submittedData['csv_to_openimmo'] : [];

        if (isset($converterData['source_folder'])) {
            $task->setSourceFolder((string)$converterData['source_folder']);
        }
        if (isset($converterData['target_folder'])) {
            $task->setTargetFolder((string)$converterData['target_folder']);
        }
        if (isset($converterData['delete_source_files'])) {
            $task->setDeleteProcessedSourceFiles((bool)$converterData['delete_source_files']);
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function localize($key)
    {
        return $this->getLanguageService()->sL(
            'LLL:EXT:csv_to_openimmo/Resources/Private/Language/locallang.xlf:schedulerTask.configuration.' . $key
        );
    }

    /**
     * @return LanguageService
     */
    private function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
