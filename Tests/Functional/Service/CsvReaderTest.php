<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\CsvReader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CsvReaderTest extends UnitTestCase
{
    /**
     * @var CsvReader
     */
    private $subject = null;

    /**
     * @var vfsStreamDirectory
     */
    private $root = null;

    protected function setUp()
    {
        $this->root = vfsStream::setup();

        $this->subject = new CsvReader();
    }

    /**
     * @test
     */
    public function findFirstCsvFileInDirectoryForNoFilesReturnsNull()
    {
        $directory = vfsStream::url('root');

        static::assertNull($this->subject->findFirstCsvFileInDirectory($directory));
    }

    /**
     * @test
     */
    public function findFirstCsvFileInDirectoryForOnlyNonCsvFilesReturnsNull()
    {
        vfsStream::newFile('test.txt')->at($this->root)->setContent('Hello world!');
        $directory = vfsStream::url('root');

        static::assertNull($this->subject->findFirstCsvFileInDirectory($directory));
    }

    /**
     * @test
     */
    public function findFirstCsvFileInDirectoryForOneCsvFileReturnsAbsolutePathToFile()
    {
        vfsStream::newFile('test.csv')->at($this->root)->setContent('a,b');
        $directory = vfsStream::url('root');

        static::assertSame(vfsStream::url('root/test.csv'), $this->subject->findFirstCsvFileInDirectory($directory));
    }

    /**
     * @test
     */
    public function findFirstCsvFileInDirectoryForTwoCsvFilesReturnsFirstFileByAlphabet()
    {
        vfsStream::newFile('foo.csv')->at($this->root)->setContent('a,b');
        vfsStream::newFile('bar.csv')->at($this->root)->setContent('a,b');
        $directory = vfsStream::url('root');

        static::assertSame(vfsStream::url('root/bar.csv'), $this->subject->findFirstCsvFileInDirectory($directory));
    }

    /**
     * @test
     */
    public function readCsvForInexistentFileThrowsException()
    {
        $this->expectException(\RuntimeException::class);

        $path = vfsStream::url('root/test.csv');

        $this->subject->readCsv($path);
    }

    /**
     * @test
     */
    public function readCsvForFileWithIncorrectNumberOfColumnsThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        vfsStream::newFile('test.csv')->at($this->root)->setContent('a,b');
        $path = vfsStream::url('root/test.csv');

        $this->subject->readCsv($path);
    }

    /**
     * @test
     */
    public function readCsvForEmptyFileReturnsEmptyArray()
    {
        vfsStream::newFile('test.csv')->at($this->root)->setContent('');
        $path = vfsStream::url('root/test.csv');

        static::assertSame([], $this->subject->readCsv($path));
    }

    /**
     * @test
     */
    public function readCsvForNonEmptyFileReturnsOneElementPerLine()
    {
        $path = __DIR__ . '/../Fixtures/CorrectCsv/objects.csv';

        $result = $this->subject->readCsv($path);

        static::assertCount(2, $result);
    }

    /**
     * @return array[]
     */
    public function csvResultDataProvider()
    {
        return [
            'object #1' => [
                0,
                [
                    'utilization' => 'Wohnraum',
                    'objectNumber' => '2117 / 1 / 16',
                    'street' => 'Schomerusstraße',
                    'streetNumber' => '1',
                    'zip' => '07745',
                    'city' => 'Jena',
                    'district' => 'Winzerla',
                    'yearOfConstruction' => '1991',
                    'numberOfFloors' => '',
                    'availabilityDate' => 'sofort',
                    'floor' => '3. Etage',
                    'numberOfRooms' => '2',
                    'livingArea' => '55,34',
                    'heatingType' => 'Fernwärme',
                    'rentWithoutHeatingCosts' => '294',
                    'additionalCosts' => '127',
                    'heatingIncludedInAdditionalCosts' => 'ja',
                    'rentWithHeatingCosts' => '421',
                    'deposit' => 'kautionsfrei',
                    'title' => 'großzügige 2 Raumwohnung in Jena-Winzerla',
                    'location' => 'Das Gebäude liegt im unteren Teil von Winzerla mit wunderbarem Blick auf die Kernberge oder das Zentrum von Jena. Einkaufsmöglichkeiten und Dienstleister ebenso Schulen und Kindergärten sind im Stadtteil vorhanden. Mit dem öffentlichen Nahverkehr erreichen Sie in kurzer Zeit das Zentrum und alle anderen Stadtteile Jenas. Ob mit Bus oder Straßenbahn es sind nur wenige Minuten zu den Bahnhöfen Paradies und Göschwitz sowie dem Busbahnhof. Das Freizeit- und Spaßbad GalaxSea ist fußläufig zu erreichen.',
                    'description' => '',
                    'equipment' => '',
                    'elevator' => 'nicht vorhanden',
                    'balcony' => 'vorhanden',
                    'parkingSpaceType' => 'öffentlich',
                    'contactPersonSalutation' => 'Herr',
                    'contactPersonFullName' => 'Matthias Doe',
                    'contactPersonPhoneNumber' => '+49 1111 884429',
                    'contactPersonEmail' => 'winzerla@example.com',
                    'imageFileName 01' => '00513444.jpg',
                    'imageTitle 01' => 'Objektbild',
                    'imageFileName 02' => '00398351.jpg',
                    'imageTitle 02' => '',
                    'imageFileName 03' => '00517279.jpg',
                    'imageTitle 03' => 'Straßenbild',
                    'imageFileName 04' => '00517277.jpg',
                    'imageTitle 04' => 'Grünflächen',
                    'imageFileName 05' => '00517278.jpg',
                    'imageTitle 05' => 'Innenhof',
                ],
            ],
            'object #2' => [
                1,
                [
                    'utilization' => 'Wohnraum',
                    'objectNumber' => '3033 / 3 / 28',
                    'street' => 'Fritz-Kalisch-Straße',
                    'streetNumber' => '3',
                    'zip' => '07743',
                    'city' => 'Jena',
                    'district' => 'Stadt-Nord',
                    'yearOfConstruction' => '1991',
                    'numberOfFloors' => '',
                    'availabilityDate' => 'sofort',
                    'floor' => '1. Etage',
                    'numberOfRooms' => '3',
                    'livingArea' => '65,63',
                    'heatingType' => 'Fernwärme',
                    'rentWithoutHeatingCosts' => '430',
                    'additionalCosts' => '164',
                    'heatingIncludedInAdditionalCosts' => 'ja',
                    'rentWithHeatingCosts' => '594',
                    'deposit' => 'kautionsfrei',
                    'title' => 'Wohnung in Jena Nord sucht WG',
                    'location' => 'Das Gebäude liegt im Zentrum des Stadtteils Jena-Nord am Emil-Höllein-Platz. In unmittelbarer Umgebung finden Sie einzelne Dienstleister und Einkaufsmöglichkeiten. Der öffentliche Nahverkehr ist gut zu Fuß erreichbar. Die Straßenbahn bringt Sie in nur wenigen Minuten in das Stadtzentrum von Jena. In der Nähe befinden sich Kindergärten und Schulen mit unterschiedlichen Bildungsrichtungen. Die grünen Innenhöfe mit altem Baumbestand sorgen für Erholung und Entspannung.',
                    'description' => '',
                    'equipment' => 'Diese Wohnung ist ideal geeignet für eine 2er WG oder einen 2 Personen Haushalt. Die einzelnen Wohnräume bieten sehr viel Stellfläche und sind jeweils mit einem seperaten TV und Telefonanschluss ausgestattet. Der Flur, das Wohnzimmer und das dritte Zimmer verfügen über robustes Holzparkett und die Küche und Bad sind mit Fenster.',
                    'elevator' => 'nicht vorhanden',
                    'balcony' => 'nicht vorhanden',
                    'parkingSpaceType' => 'öffentlich im Wohnumfeld',
                    'contactPersonSalutation' => 'Frau',
                    'contactPersonFullName' => 'Annett Doe',
                    'contactPersonPhoneNumber' => '+49 3641 884469',
                    'contactPersonEmail' => 'stadtmitte@example.com',
                    'imageFileName 01' => '00535445.jpg',
                    'imageTitle 01' => 'Objektbild',
                    'imageFileName 02' => '00539003.jpg',
                    'imageTitle 02' => '',
                    'imageFileName 03' => '00535488.jpg',
                    'imageTitle 03' => 'Straßenbild',
                    'imageFileName 04' => '',
                    'imageTitle 04' => '',
                    'imageFileName 05' => '',
                    'imageTitle 05' => '',
                ],
            ],
        ];
    }

    /**
     * @test
     *
     * @param int $lineIndex
     * @param string[] $expectedResult
     * @dataProvider csvResultDataProvider
     */
    public function readCsvReassignsColumnWithSpeakingKeys($lineIndex, array $expectedResult)
    {
        $path = __DIR__ . '/../Fixtures/CorrectCsv/objects.csv';

        $result = $this->subject->readCsv($path);

        foreach ($expectedResult as $columnKey => $value) {
            $prefix = $columnKey . ': ';
            static::assertSame($prefix . $value, $prefix . $result[$lineIndex][$columnKey]);
        }

        static::assertSame($expectedResult, $result[$lineIndex]);
    }
}
