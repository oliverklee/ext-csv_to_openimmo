<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'CSV-to-OpenImmo converter',
    'description' => 'This extension provides a Scheduler task that reads zipped CSV files from a configured folder and writes zipped OpenImmo files to another configured folder. The task will also copy all image files and PDF from the ZIPs.',
    'category' => 'module',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'state' => 'stable',
    'version' => '0.1.0',
    'constraints' =>
        array(
            'depends' =>
                array(
                    'php' => '5.6.0-7.2.99',
                    'typo3' => '6.2.30-7.6.99',
                ),
            'conflicts' =>
                array(),
            'suggests' =>
                array(),
        ),
);
