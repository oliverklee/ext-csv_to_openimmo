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
     * @test
     */
    public function readCsvReassignsColumnWithSpeakingKeys()
    {
        $path = __DIR__ . '/../Fixtures/CorrectCsv/objects.csv';

        $result = $this->subject->readCsv($path);

        $expectedResult = [
            'utilization' => 'Wohnraum',
            'objectNumber' => '2117 / 1 / 16',
            'street' => 'Schomerusstraße 1',
            'zip' => '07745',
            'city' => 'Jena',
            'district' => 'Winzerla',
            'yearOfConstruction' => '1991',
            'floors' => '',
            'availabilityDate' => 'sofort',
            'floor' => '3. Etage',
            'numberOfRooms' => '2',
            'livingArea' => '55,34',
            'heatingType' => 'Fernwärme',
            'rentExclusiveOfHeating' => '294',
            'additionalCosts' => '127',
            'heatingIncludedInAdditionalCosts' => 'ja',
            'totalRent' => '421',
            'deposit' => 'kautionsfrei',
            'title' => 'großzügige 2 Raumwohnung in Jena-Winzerla',
            'location' => 'Das Gebäude liegt im unteren Teil von Winzerla mit wunderbarem Blick auf die Kernberge oder das Zentrum von Jena. Einkaufsmöglichkeiten und Dienstleister ebenso Schulen und Kindergärten sind im Stadtteil vorhanden. Mit dem öffentlichen Nahverkehr erreichen Sie in kurzer Zeit das Zentrum und alle anderen Stadtteile Jenas. Ob mit Bus oder Straßenbahn es sind nur wenige Minuten zu den Bahnhöfen Paradies und Göschwitz sowie dem Busbahnhof. Das Freizeit- und Spaßbad GalaxSea ist fußläufig zu erreichen.',
            'description' => '',
            'equipment' => '',
            'elevator' => 'nicht vorhanden',
            'balcony' => 'vorhanden',
            'garageType' => 'öffentlich',
            'contactPersonSalutation' => 'Herr',
            'contactPersonFullName' => 'Matthias Doe',
            'contactPersonPhoneNumber' => '+49 1111 884429',
            'contactPersonEmail' => 'winzerla@example.com',
            'imageFileName 01' => '00513444.jpg',
            'imageDescription 01' => 'Objektbild',
            'imageFileName 02' => '00398351.jpg',
            'imageDescription 02' => '',
            'imageFileName 03' => '00517279.jpg',
            'imageDescription 03' => 'Straßenbild',
            'imageFileName 04' => '00517277.jpg',
            'imageDescription 04' => 'Grünflächen',
            'imageFileName 05' => '00517278.jpg',
            'imageDescription 05' => 'Innenhof',
        ];

        foreach ($expectedResult as $key => $value) {
            $prefix = $key . ': ';
            static::assertSame($prefix . $value, $prefix . $result[0][$key]);
        }

        static::assertSame($expectedResult, $result[0]);
    }
}
