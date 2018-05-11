<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\OliverKlee\CsvToOpenImmo\SchedulerTask\CsvConverter::class] = [
    'extension' => 'csv_to_openimmo',
    'title' => 'LLL:EXT:csv_to_openimmo/Resources/Private/Language/locallang.xlf:schedulerTask.title',
    'description' => 'LLL:EXT:csv_to_openimmo/Resources/Private/Language/locallang.xlf:schedulerTask.description',
    'additionalFields' => \OliverKlee\CsvToOpenImmo\SchedulerTask\CsvConverterConfiguration::class,
];
