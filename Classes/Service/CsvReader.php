<?php
namespace OliverKlee\CsvToOpenImmo\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class reads and checks a CSV file with realty data.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvReader implements SingletonInterface
{
    /**
     * @var bool
     */
    const DEBUG = false;

    /**
     * @var int
     */
    const NUMBER_OF_COLUMNS = 107;

    /**
     * The keys are one-based, not zero-based
     *
     * @var string[]
     */
    private static $columnKeys = [
        3 => 'utilization',
        4 => 'objectNumber',
        6 => 'street',
        8 => 'zip',
        9 => 'city',
        10 => 'district',
        11 => 'yearOfConstruction',
        13 => 'floors',
        14 => 'availabilityDate',
        15 => 'floor',
        16 => 'numberOfRooms',
        17 => 'livingArea',
        18 => 'heatingType',
        23 => 'rentExclusiveOfHeating',
        24 => 'additionalCosts',
        26 => 'heatingIncludedInAdditionalCosts',
        27 => 'totalRent',
        29 => 'deposit',
        30 => 'title',
        32 => 'location',
        33 => 'description',
        34 => 'equipment',
        35 => 'elevator',
        36 => 'balcony',
        46 => 'garageType',
        49 => 'contactPersonSalutation',
        50 => 'contactPersonFullName',
        55 => 'contactPersonPhoneNumber',
        56 => 'contactPersonEmail',
        58 => 'imageFileName 01',
        62 => 'imageDescription 01',
        63 => 'imageFileName 02',
        67 => 'imageDescription 02',
        68 => 'imageFileName 03',
        72 => 'imageDescription 03',
        73 => 'imageFileName 04',
        77 => 'imageDescription 04',
        78 => 'imageFileName 05',
        82 => 'imageDescription 05',
    ];

    /**
     * The keys are one-based, not zero-based
     *
     * @var string[]
     */
    private static $ignoredColumns = [
        1 => 'I',
        2 => '1',
        5 => 'company name',
        7 => '1',
        19 => '119,44',
        20 => 'Bedarfsausweis',
        21 => '08.20.2008',
        25 => '83',
        31 => 'full object address',
        37 => 'Kellerbox 401',
        51 => 'some street (of the offerer or contact person?)',
        52 => 'ZIP code (of the offerer or contact person?)',
        53 => 'city (of the offerer or contact person?)',
        54 => 'some phone number (whose? of the contact person?)',
        57 => 'some URL',
        59 => 'jpg',
        60 => 'B',
        64 => 'jpg',
        65 => 'B',
        69 => 'jpg',
        70 => 'B',
        74 => 'jpg',
        75 => 'B',
        79 => 'jpg',
        80 => 'B',
    ];

    /**
     * @param string $directory
     *
     * @return string|null
     */
    public function findFirstCsvFileInDirectory($directory)
    {
        /** @var string[] $allCsvFiles */
        $allCsvFiles = GeneralUtility::getAllFilesAndFoldersInPath([], $directory, 'csv');

        return array_shift($allCsvFiles);
    }

    /**
     * @param string $path
     *
     * @return string[][]
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    public function readCsv($path)
    {
        $rawCsvLines = $this->readCsvLines($path);
        return $this->convertLinesToSpeakingKeys($rawCsvLines);
    }

    /**
     * @param string $path
     *
     * @return string[][]
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    private function readCsvLines($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('The CSV file "' . $path . '" could not be read.', 1526493542);
        }

        /** @var string[][] $csvLines */
        $csvLines = [];
        $fileHandle = fopen($path, 'rb');
        while (($csvLine = fgetcsv($fileHandle, 0, ';'))) {
            /** @var string[] $csvLine */
            $csvLines[] = $csvLine;
        }
        fclose($fileHandle);

        $this->validateNumberOfColumns($path, $csvLines);

        return $csvLines;
    }

    /**
     * @param string $path
     * @param string[][] $csvLines
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    private function validateNumberOfColumns($path, array $csvLines)
    {
        if (!empty($csvLines)) {
            $numberOfColumns = count($csvLines[0]);
            if ($numberOfColumns !== self::NUMBER_OF_COLUMNS) {
                throw new \UnexpectedValueException(
                    'The CSV file "' . $path . '" should have ' . self::NUMBER_OF_COLUMNS .
                    ' columns, but actually has ' . $numberOfColumns . ' columns.',
                    1526494172
                );
            }
        }
    }

    /**
     * @param string[][] $rawCsvLines
     *
     * @return string[][]
     */
    private function convertLinesToSpeakingKeys(array $rawCsvLines)
    {
        $result = [];
        foreach ($rawCsvLines as $rawCsvLine) {
            $lineWithSpeakingKeys = $this->convertSingleLineToSpeakingKeys($rawCsvLine);
            $encodedLine = array_map('utf8_encode', $lineWithSpeakingKeys);
            $trimmedEncodedLine = array_map('trim', $encodedLine);
            $result[] = $trimmedEncodedLine;
        }

        return $result;
    }

    /**
     * @param string[] $rawLine
     *
     * @return string[]
     */
    private function convertSingleLineToSpeakingKeys(array $rawLine)
    {
        $result = [];

        foreach ($rawLine as $zeroBasedColumnNumber => $value) {
            $oneBasedColumnNumber = $zeroBasedColumnNumber + 1;
            if (array_key_exists($oneBasedColumnNumber, self::$columnKeys)) {
                $speakingKey = self::$columnKeys[$oneBasedColumnNumber];
                $result[$speakingKey] = $value;
            } else {
                if (self::DEBUG && trim($value) !== ''
                    && !array_key_exists($oneBasedColumnNumber, self::$ignoredColumns)) {
                    throw new \UnexpectedValueException(
                        'The column #' . $oneBasedColumnNumber . ' with the value "' .
                        utf8_encode($value) . '" is not mapped yet.',
                        1526554511
                    );
                }
            }
        }

        return $result;
    }
}
