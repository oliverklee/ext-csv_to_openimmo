<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'CSV-to-OpenImmo converter',
    'description' => 'This extension provides a Scheduler task that reads zipped CSV files from a configured folder and writes zipped OpenImmo files to another configured folder. The task will also copy all image files and PDF from the ZIPs.',
    'category' => 'module',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'state' => 'beta',
    'version' => '1.0.x-dev',
    'constraints' =>
        [
            'depends' =>
                [
                    'php' => '5.6.0-7.2.99',
                    'typo3' => '6.2.0-7.6.99',
                    'scheduler' => '6.2.0-7.6.99',
                ],
        ],
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
