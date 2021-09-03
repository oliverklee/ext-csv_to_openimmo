<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'CSV-to-OpenImmo converter',
    'description' => 'This extension provides a Scheduler task that reads zipped CSV files from a configured folder and writes zipped OpenImmo files to another configured folder. The task will also copy all image files and PDF from the ZIPs.',
    'version' => '2.0.0',
    'category' => 'be',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.2.99',
            'typo3' => '7.6.0-8.7.99',
            'scheduler' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => 'typo3temp/csv_to_openimmo',
    'clearCacheOnLoad' => false,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'psr-4' => [
            'OliverKlee\\CsvToOpenImmo\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\CsvToOpenImmo\\Tests\\' => 'Tests/',
        ],
    ],
];
