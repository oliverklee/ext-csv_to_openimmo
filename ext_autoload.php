<?php
defined('TYPO3_MODE') or die();

// This file can be removed once this extension requires TYPO3 >= 7.6.

$extensionClassesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('csv_to_openimmo') . 'Classes/';

return [
    'OliverKlee\\CsvToOpenImmo\\SchedulerTask\\CsvConverter' => $extensionClassesPath . 'SchedulerTask/CsvConverter.php',
    'OliverKlee\\CsvToOpenImmo\\SchedulerTask\\CsvConverterConfiguration' => $extensionClassesPath . 'SchedulerTask/CsvConverterConfiguration.php',
];
