<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\OpenImmoBuilder;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class OpenImmoBuilderTest extends UnitTestCase
{
    /**
     * @var OpenImmoBuilder
     */
    private $subject = null;

    /**
     * @var string
     */
    private $schemaPath = '';

    /**
     * @var string[][]
     */
    private static $objectsData = [
        [
            'utilization' => 'Wohnraum',
            'objectNumber' => '2117 / 1 / 16',
            'street' => 'Schomerusstraße 1',
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
        [
            'utilization' => 'Wohnraum',
            'objectNumber' => '3033 / 3 / 28',
            'street' => 'Fritz-Kalisch-Straße 3',
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
        [
            'utilization' => 'wohnung',
            'objectNumber' => '236/92/73',
            'street' => 'Fritz-Kalisch-Straße 3',
            'zip' => '07743',
            'city' => 'Jena',
            'district' => 'Stadt-Nord',
            'yearOfConstruction' => '1962',
            'numberOfFloors' => '',
            'availabilityDate' => '01.11.2018',
            'floor' => '1. Obergeschoss',
            'numberOfRooms' => '4',
            'livingArea' => '68,35',
            'heatingType' => 'Fernwärme',
            'rentWithoutHeatingCosts' => '430',
            'additionalCosts' => '140',
            'heatingIncludedInAdditionalCosts' => 'ja',
            'rentWithHeatingCosts' => '570',
            'deposit' => 'kautionsfrei',
            'title' => 'Familienfreundliche 4-Raum-Wohnung im grünen Stadtteil Nord',
            'location' => 'Naumburger Straße 9, 07743 Jena',
            'description' => 'Das Gebäude befindet sich im Stadtteil Jena-Nord.',
            'equipment' => 'Das 5-geschossige Mehrfamilienhaus mit Satteldach wurde umfassend saniert.',
            'elevator' => 'nicht vorhanden',
            'balcony_or_patio' => 'nicht vorhanden',
            'parkingSpaceType' => '',
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
    ];

    protected function setUp()
    {
        $this->subject = new OpenImmoBuilder();
    }

    /**
     * @return void
     */
    private function skipForNoSchemaFile()
    {
        $this->schemaPath = __DIR__ . '/../../../Resources/Private/Specs/openimmo_127b.xsd';
        if (!file_exists($this->schemaPath)) {
            static::markTestSkipped('This test can only be run with a schema file present.');
        }
    }

    /**
     * @test
     */
    public function documentWithoutObjectsIsValid()
    {
        $this->skipForNoSchemaFile();

        $document = $this->subject->build();

        $document->schemaValidate($this->schemaPath);
        static::assertSame([], libxml_get_errors());
    }

    /**
     * @return string[][]
     */
    public function objectDataDataProvider()
    {
        $result = [];
        foreach (self::$objectsData as $objectData) {
            $result[] = [$objectData];
        }

        return $result;
    }

    /**
     * @test
     *
     * @param string[] $objectData
     * @dataProvider objectDataDataProvider
     */
    public function documentWithObjectIsValid(array $objectData)
    {
        $this->skipForNoSchemaFile();

        $this->subject->addObject($objectData);
        $document = $this->subject->build();

        $document->schemaValidate($this->schemaPath);
        static::assertSame([], libxml_get_errors());
    }

    /**
     * @test
     */
    public function documentWithAllObjectsTogetherIsValid()
    {
        $this->skipForNoSchemaFile();

        foreach (self::$objectsData as $objectData) {
            $this->subject->addObject($objectData);
        }
        $document = $this->subject->build();

        $document->schemaValidate($this->schemaPath);
        static::assertSame([], libxml_get_errors());
    }

    /**
     * @test
     */
    public function documentWithOneObjectInsertsObjectElement()
    {
        $this->subject->addObject(self::$objectsData[0]);
        $document = $this->subject->build();

        $objectElement = $document->getElementsByTagName('immobilie')->item(0);
        static::assertInstanceOf(\DOMElement::class, $objectElement);
    }

    /**
     * @test
     */
    public function documentWithTwoObjectsInsertsTwoObjectElements()
    {
        $this->subject->addObject(self::$objectsData[0]);
        $this->subject->addObject(self::$objectsData[1]);
        $document = $this->subject->build();

        $objectElements = $document->getElementsByTagName('immobilie');
        static::assertCount(2, $objectElements);
    }
}
