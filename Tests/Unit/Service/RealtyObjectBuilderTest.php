<?php
namespace OliverKlee\CsvToOpenImmo\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\CsvToOpenImmo\Service\RealtyObjectBuilder;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RealtyObjectBuilderTest extends UnitTestCase
{
    /**
     * @var RealtyObjectBuilder
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new RealtyObjectBuilder(new \DOMDocument());
    }

    /**
     * @test
     */
    public function buildFromFieldsReturnsDomElementOfImmoType()
    {
        $result = $this->subject->buildFromFields([]);

        static::assertInstanceOf(\DOMElement::class, $result);
        static::assertSame('immobilie', $result->tagName);
    }

    /**
     * @test
     */
    public function buildFromFieldsAlwaysHasUtilizationElement()
    {
        $result = $this->subject->buildFromFields([]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);
        static::assertNotNull($utilizationElement);
    }

    /**
     * @test
     */
    public function buildFromFieldsAlwaysHasMarketingTypeElementSetToRent()
    {
        $result = $this->subject->buildFromFields([]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $marketingTypeElement = $categoryElement->getElementsByTagName('vermarktungsart')->item(0);
        static::assertNotNull($marketingTypeElement);
        static::assertSame('false', $marketingTypeElement->getAttribute('KAUF'));
        static::assertSame('true', $marketingTypeElement->getAttribute('MIETE_PACHT'));
    }

    /**
     * @return string[][]
     */
    public function habitationUtilizationDataProvider()
    {
        return [
            'Wohnraum' => ['Wohnraum'],
            'wohnraum' => ['wohnraum'],
            'wohnung' => ['wohnung'],
            'Wohnung' => ['Wohnung'],
        ];
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider habitationUtilizationDataProvider
     */
    public function buildFromFieldsForHabitationUtilizationSetsObjectTypeToHabitation($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $objectTypeElement = $categoryElement->getElementsByTagName('objektart')->item(0);
        static::assertNotNull($objectTypeElement);

        $habitationElement = $objectTypeElement->getElementsByTagName('wohnung')->item(0);
        static::assertNotNull($habitationElement);
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider habitationUtilizationDataProvider
     */
    public function buildFromFieldsForHabitationUtilizationSetsUtilizationTypeElementToHabitation($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);
        static::assertNotNull($utilizationElement);

        static::assertSame('true', $utilizationElement->getAttribute('WOHNEN'));
    }

    /**
     * @test
     */
    public function buildFromFieldsForOfficeUtilizationSetsObjectTypeToOffice()
    {
        $result = $this->subject->buildFromFields(['utilization' => 'buero_praxen']);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $objectTypeElement = $categoryElement->getElementsByTagName('objektart')->item(0);
        static::assertNotNull($objectTypeElement);

        $habitationElement = $objectTypeElement->getElementsByTagName('buero_praxen')->item(0);
        static::assertNotNull($habitationElement);
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider habitationUtilizationDataProvider
     */
    public function buildFromFieldsForNonParkingUtilizationNotSetsObjectTypeElementToParking($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $objectTypeElement = $categoryElement->getElementsByTagName('objektart')->item(0);
        static::assertNotNull($objectTypeElement);

        $parkingElement = $objectTypeElement->getElementsByTagName('parken')->item(0);
        static::assertNull($parkingElement);
    }

    /**
     * @return string[][]
     */
    public function parkingUtilizationDataProvider()
    {
        return [
            'Stellplatz' => ['Stellplatz'],
            'Garage' => ['Garage'],
            'Parken' => ['Parken'],
            'parken' => ['parken'],
        ];
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider parkingUtilizationDataProvider
     */
    public function buildFromFieldsForParkingUtilizationSetsObjectTypeElementToParking($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $objectTypeElement = $categoryElement->getElementsByTagName('objektart')->item(0);
        static::assertNotNull($objectTypeElement);

        $parkingElement = $objectTypeElement->getElementsByTagName('parken')->item(0);
        static::assertNotNull($parkingElement);
    }

    /**
     * @return string[][]
     */
    public function otherUtilizationDataProvider()
    {
        return [
            'gewerbe' => ['gewerbe'],
            'Gewerbe' => ['Gewerbe'],
            'sonstige' => ['sonstige'],
            'Sonstige' => ['Sonstige'],
            'sonstiges' => ['sonstiges'],
            'Sonstiges' => ['Sonstiges'],
            '(empty string)' => [''],
        ];
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider otherUtilizationDataProvider
     */
    public function buildFromFieldsForOtherUtilizationSetsObjectTypeElementToOther($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $objectTypeElement = $categoryElement->getElementsByTagName('objektart')->item(0);
        static::assertNotNull($objectTypeElement);

        $otherUtilizationElement = $objectTypeElement->getElementsByTagName('sonstige')->item(0);
        static::assertNotNull($otherUtilizationElement);
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider parkingUtilizationDataProvider
     */
    public function buildFromFieldsForParkingUtilizationNotSetsObjectTypeElementToHabitation($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $objectTypeElement = $categoryElement->getElementsByTagName('objektart')->item(0);
        static::assertNotNull($objectTypeElement);

        $habitationElement = $objectTypeElement->getElementsByTagName('wohnung')->item(0);
        static::assertNull($habitationElement);
    }

    /**
     * @return string[][]
     */
    public function contactPersonDataProvider()
    {
        return [
            'contactPersonSalutation' => ['contactPersonSalutation', 'anrede', 'Frau'],
            'empty contactPersonSalutation' => ['contactPersonSalutation', 'anrede', ''],
            'contactPersonFullName' => ['contactPersonFullName', 'name', 'Kate Doe'],
            'empty contactPersonFullName' => ['contactPersonFullName', 'name', ''],
            'contactPersonPhoneNumber' => ['contactPersonPhoneNumber', 'tel_durchw', '+49 1111 123123-22'],
            'empty contactPersonPhoneNumber' => ['contactPersonPhoneNumber', 'tel_durchw', ''],
            'contactPersonEmail' => ['contactPersonEmail', 'email_zentrale', 'kate@example.com'],
            'empty contactPersonEmail' => ['contactPersonEmail', 'email_zentrale', ''],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $value
     * @dataProvider contactPersonDataProvider
     */
    public function buildMapsContactPerson($fieldName, $tagName, $value)
    {
        $this->assertChildElementValue('kontaktperson', $fieldName, $tagName, $value, $value);
    }

    /**
     * @param string $parentTagName
     * @param string $fieldName
     * @param string $tagName
     * @param string $sourceValue
     * @param string $targetValue
     *
     * @return void
     */
    private function assertChildElementValue($parentTagName, $fieldName, $tagName, $sourceValue, $targetValue)
    {
        $result = $this->subject->buildFromFields([$fieldName => $sourceValue]);

        $parentElement = $result->getElementsByTagName($parentTagName)->item(0);
        static::assertNotNull($parentElement);

        $childElement = $parentElement->getElementsByTagName($tagName)->item(0);

        static::assertNotNull($childElement);
        static::assertSame($targetValue, $childElement->textContent);
    }

    /**
     * @return string[][]
     */
    public function geoDataProvider()
    {
        return [
            'zip' => ['zip', 'plz', '12345', '12345'],
            'empty zip' => ['zip', 'plz', '', ''],
            'city' => ['city', 'ort', 'Kleincodingen', 'Kleincodingen'],
            'empty city' => ['city', 'ort', '', ''],
            'street' => ['street', 'strasse', 'Am Eck', 'Am Eck'],
            'empty street' => ['street', 'strasse', '', ''],
            'street number' => ['streetNumber', 'hausnummer', '42', '42'],
            'empty street number' => ['streetNumber', 'hausnummer', '', ''],
            'district' => ['district', 'regionaler_zusatz', 'Nordstadt', 'Nordstadt'],
            'empty district' => ['district', 'regionaler_zusatz', '', ''],
            'numberOfFloors' => ['numberOfFloors', 'anzahl_etagen', '5 Etagen', '5'],
            'empty numberOfFloors' => ['numberOfFloors', 'anzahl_etagen', '', '0'],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $sourceValue
     * @param string $targetValue
     * @dataProvider geoDataProvider
     */
    public function buildMapsGeo($fieldName, $tagName, $sourceValue, $targetValue)
    {
        $this->assertChildElementValue('geo', $fieldName, $tagName, $sourceValue, $targetValue);
    }

    /**
     * @return string[][]
     */
    public function freeTextDataProvider()
    {
        return [
            'non-empty title' => ['title', 'objekttitel', 'Maisonette-Wohnung mit 2 Bädern und Sauna (unbeheizt)'],
            'title with ampersand' => ['title', 'objekttitel', 'A & B'],
            'title with quotes' => ['title', 'objekttitel', 'Hello "world"'],
            'empty title' => ['title', 'objekttitel', ''],
            'non-empty description' => ['description', 'objektbeschreibung', 'Wohnst du noch oder lebst do schon?'],
            'empty description' => ['description', 'objektbeschreibung', ''],
            'non-empty equipment' => ['equipment', 'ausstatt_beschr', 'Diese Wohnung hat alles, was das Herz begehrt.'],
            'empty equipment' => ['equipment', 'ausstatt_beschr', ''],
            'non-empty location' => ['location', 'lage', 'Direkt in der Innenstadt'],
            'empty location' => ['location', 'lage', ''],
            'non-empty floor' => ['floor', 'sonstige_angaben', '1. Obergeschoss / Dachgeschoss,'],
            'empty floor' => ['floor', 'sonstige_angaben', ''],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $value
     * @dataProvider freeTextDataProvider
     */
    public function buildMapsFreeTextFields($fieldName, $tagName, $value)
    {
        $this->assertChildElementValue('freitexte', $fieldName, $tagName, $value, $value);
    }

    /**
     * @return string[][]
     */
    public function objectAdministrationDataProvider()
    {
        return [
            'availabilityDate' => ['availabilityDate', 'verfuegbar_ab', 'ab Anfang Oktober'],
            'empty availabilityDate' => ['availabilityDate', 'verfuegbar_ab', ''],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $value
     * @dataProvider objectAdministrationDataProvider
     */
    public function buildMapsObjectAdministrationFields($fieldName, $tagName, $value)
    {
        $this->assertChildElementValue('verwaltung_objekt', $fieldName, $tagName, $value, $value);
    }

    /**
     * @return string[][]
     */
    public function technicalAdministrationDataProvider()
    {
        return [
            'objectNumber' => ['objectNumber', 'objektnr_extern', 'A/B 42'],
            'empty objectNumber' => ['objectNumber', 'objektnr_extern', ''],
        ];
    }

    /**
     * @test
     */
    public function buildUsesHashOfObjectNumberAsObjectId()
    {
        $objectNumber = 'A/B 42';
        $this->assertChildElementValue(
            'verwaltung_techn',
            'objectNumber',
            'openimmo_obid',
            $objectNumber,
            md5($objectNumber)
        );
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $value
     * @dataProvider technicalAdministrationDataProvider
     */
    public function buildMapsTechnicalAdministrationFields($fieldName, $tagName, $value)
    {
        $this->assertChildElementValue('verwaltung_techn', $fieldName, $tagName, $value, $value);
    }

    /**
     * @return string[][]
     */
    public function technicalDefaultFieldDataProvider()
    {
        return [
            'aktion' => ['aktion'],
            'openimmo_obid' => ['openimmo_obid'],
            'kennung_ursprung' => ['kennung_ursprung'],
            'stand_vom' => ['stand_vom'],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @dataProvider technicalDefaultFieldDataProvider
     */
    public function buildAddsDefaultTechnicalAdministrationFields($fieldName)
    {
        $result = $this->subject->buildFromFields([]);

        $parentElement = $result->getElementsByTagName('verwaltung_techn')->item(0);
        static::assertNotNull($parentElement);

        $childElement = $parentElement->getElementsByTagName($fieldName)->item(0);
        static::assertNotNull($childElement);
    }

    /**
     * @test
     */
    public function buildMakesAddressAvailable()
    {
        $result = $this->subject->buildFromFields([]);

        $parentElement = $result->getElementsByTagName('verwaltung_objekt')->item(0);
        static::assertNotNull($parentElement);

        $childElement = $parentElement->getElementsByTagName('objektadresse_freigeben')->item(0);
        static::assertNotNull($childElement);
        static::assertSame('true', $childElement->nodeValue);
    }

    /**
     * @return string[][]
     */
    public function stateDataProvider()
    {
        return [
            'yearOfConstruction' => ['yearOfConstruction', 'baujahr', '1968'],
            'empty yearOfConstruction' => ['yearOfConstruction', 'baujahr', '0'],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $value
     * @dataProvider stateDataProvider
     */
    public function buildMapsStateFields($fieldName, $tagName, $value)
    {
        $this->assertChildElementValue('zustand_angaben', $fieldName, $tagName, $value, $value);
    }

    /**
     * @return string[][]
     */
    public function areaDataProvider()
    {
        return [
            'numberOfRooms' => ['numberOfRooms', 'anzahl_zimmer', '3,5 Zimmer', '3.5'],
            'empty numberOfRooms' => ['numberOfRooms', 'anzahl_zimmer', '', '0.0'],
            'livingArea' => ['livingArea', 'wohnflaeche', '126,5m²', '126.5'],
            'empty livingArea' => ['livingArea', 'wohnflaeche', '', '0.0'],
            'balcony_or_patio' => ['balcony_or_patio', 'anzahl_balkone', 'vorhanden', '1'],
            'no balcony' => ['balcony_or_patio', 'anzahl_balkone', 'nicht vorhanden', '0'],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $sourceValue
     * @param string $targetValue
     * @dataProvider areaDataProvider
     */
    public function buildMapsAreaFields($fieldName, $tagName, $sourceValue, $targetValue)
    {
        $this->assertChildElementValue('flaechen', $fieldName, $tagName, $sourceValue, $targetValue);
    }

    /**
     * @return string[][]
     */
    public function priceDataProvider()
    {
        return [
            'rentWithoutHeatingCosts' => ['rentWithoutHeatingCosts', 'kaltmiete', '270,24€', '270.24'],
            'rentWithHeatingCosts' => ['rentWithHeatingCosts', 'warmmiete', '270,24€', '270.24'],
            'additionalCosts' => ['additionalCosts', 'nebenkosten', '170,24€', '170.24'],
            'deposit' => ['deposit', 'kaution_text', 'kautionsfrei', 'kautionsfrei'],
            'heatingIncludedInAdditionalCosts (yes)' => [
                'heatingIncludedInAdditionalCosts',
                'heizkosten_enthalten',
                'ja',
                'true',
            ],
            'heatingIncludedInAdditionalCosts (no)' => [
                'heatingIncludedInAdditionalCosts',
                'heizkosten_enthalten',
                'nein',
                'false',
            ],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $sourceValue
     * @param string $targetValue
     * @dataProvider priceDataProvider
     */
    public function buildMapsPriceFields($fieldName, $tagName, $sourceValue, $targetValue)
    {
        $this->assertChildElementValue('preise', $fieldName, $tagName, $sourceValue, $targetValue);
    }

    /**
     * @return string[][]
     */
    public function nonHabitationUtilizationDataProvider()
    {
        return [
            'empty string' => [''],
            'commercial' => ['gewerbliche Nutzung'],
            'parking' => ['Parkplatz'],
            'storage' => ['Lagerhaus'],
        ];
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider nonHabitationUtilizationDataProvider
     */
    public function buildConvertsNonHabitationUtilizationToNoHabitation($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);
        static::assertNotNull($utilizationElement);

        static::assertSame('false', $utilizationElement->getAttribute('WOHNEN'));
    }

    /**
     * @return string[][]
     */
    public function commercialUtilizationDataProvider()
    {
        return [
            'Gewerbe' => ['Gewerbe'],
            'gewerbe' => ['gewerbe'],
            'gewerbliche Nutzung' => ['gewerbliche Nutzung'],
        ];
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider commercialUtilizationDataProvider
     */
    public function buildForCommercialUtilizationSetsCommercialUtilizationFieldsToTrue($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);
        static::assertNotNull($utilizationElement);

        static::assertSame('true', $utilizationElement->getAttribute('GEWERBE'));
    }

    /**
     * @return string[][]
     */
    public function nonCommercialUtilizationDataProvider()
    {
        return [
            'Garage' => ['Garage'],
            'Stellplatz' => ['Stellplatz'],
            'Wohnraum' => ['Wohnraum'],
            'something else' => ['something else'],
            'empty string' => [''],
        ];
    }

    /**
     * @test
     *
     * @param string $utilization
     * @dataProvider nonCommercialUtilizationDataProvider
     */
    public function buildForNonCommercialUtilizationSetsCommercialUtilizationFieldsToFalse($utilization)
    {
        $result = $this->subject->buildFromFields(['utilization' => $utilization]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);
        static::assertNotNull($utilizationElement);

        static::assertSame('false', $utilizationElement->getAttribute('GEWERBE'));
    }

    /**
     * @return string[][]
     */
    public function heatingDataProvider()
    {
        return [
            'Fernwärme' => ['Fernwärme', 'false', 'false', 'false', 'true', 'false'],
            'Ofenheizung' => ['Ofenheizung', 'true', 'false', 'false', 'false', 'false'],
            'Gas dezentral' => ['Gas dezentral', 'false', 'false', 'false', 'false', 'false'],
            'Gas zentral' => ['Gas zentral', 'false', 'false', 'true', 'false', 'false'],
            'Öl' => ['Öl', 'false', 'false', 'false', 'false', 'false'],
        ];
    }

    /**
     * @test
     *
     * @param string $sourceValue
     * @param string $stoveValue
     * @param string $oneFloorValue
     * @param string $centralValue
     * @param string $remoteValue
     * @param string $floorValue
     * @dataProvider heatingDataProvider
     */
    public function buildMapsHeatingFields(
        $sourceValue,
        $stoveValue,
        $oneFloorValue,
        $centralValue,
        $remoteValue,
        $floorValue
    ) {
        $result = $this->subject->buildFromFields(['heatingType' => $sourceValue]);

        $equipmentElement = $result->getElementsByTagName('ausstattung')->item(0);
        static::assertNotNull($equipmentElement);

        $heatingElement = $equipmentElement->getElementsByTagName('heizungsart')->item(0);
        static::assertNotNull($heatingElement);

        static::assertSame($stoveValue, $heatingElement->getAttribute('OFEN'));
        static::assertSame($oneFloorValue, $heatingElement->getAttribute('ETAGE'));
        static::assertSame($centralValue, $heatingElement->getAttribute('ZENTRAL'));
        static::assertSame($remoteValue, $heatingElement->getAttribute('FERN'));
        static::assertSame($floorValue, $heatingElement->getAttribute('FUSSBODEN'));
    }

    /**
     * @return string[][]
     */
    public function firingDataProvider()
    {
        return [
            'Fernwärme' => ['Fernwärme', 'false', 'false', 'true'],
            'Ofenheizung' => ['Ofenheizung', 'false', 'false', 'false'],
            'Gas dezentral' => ['Gas dezentral', 'false', 'true', 'false'],
            'Gas zentral' => ['Gas zentral', 'false', 'true', 'false'],
            'Öl' => ['Öl', 'true', 'false', 'false'],
        ];
    }

    /**
     * @test
     *
     * @param string $sourceValue
     * @param string $oilValue
     * @param string $gasValue
     * @param string $remoteValue
     * @dataProvider firingDataProvider
     */
    public function buildMapsFiringFields($sourceValue, $oilValue, $gasValue, $remoteValue)
    {
        $result = $this->subject->buildFromFields(['heatingType' => $sourceValue]);

        $equipmentElement = $result->getElementsByTagName('ausstattung')->item(0);
        static::assertNotNull($equipmentElement);

        $firingElement = $equipmentElement->getElementsByTagName('befeuerung')->item(0);
        static::assertNotNull($firingElement);

        static::assertSame($oilValue, $firingElement->getAttribute('OEL'));
        static::assertSame($gasValue, $firingElement->getAttribute('GAS'));
        static::assertSame($remoteValue, $firingElement->getAttribute('FERN'));
    }

    /**
     * @test
     */
    public function buildMapsExistingElevator()
    {
        $result = $this->subject->buildFromFields(['elevator' => 'vorhanden']);

        $equipmentElement = $result->getElementsByTagName('ausstattung')->item(0);
        static::assertNotNull($equipmentElement);

        $elevatorElement = $equipmentElement->getElementsByTagName('fahrstuhl')->item(0);
        static::assertNotNull($elevatorElement);

        static::assertSame('true', $elevatorElement->getAttribute('PERSONEN'));
    }

    /**
     * @return string[][]
     */
    public function noElevatorDataProvider()
    {
        return [
            'inexistent' => ['nicht vorhanden'],
            'empty string' => [''],
        ];
    }

    /**
     * @test
     *
     * @param string $sourceValue
     * @dataProvider noElevatorDataProvider
     */
    public function buildMapsNonExistingElevator($sourceValue)
    {
        $result = $this->subject->buildFromFields(['elevator' => $sourceValue]);

        $equipmentElement = $result->getElementsByTagName('ausstattung')->item(0);
        static::assertNotNull($equipmentElement);

        $elevatorElement = $equipmentElement->getElementsByTagName('fahrstuhl')->item(0);
        static::assertNull($elevatorElement);
    }

    /**
     * @return string[][]
     */
    public function parkingSpaceDataProvider()
    {
        return [
            'Garage' => ['Garage', 'true', 'false', 'false', 'false'],
            'Tiefgarage' => ['Tiefgarage', 'false', 'true', 'false', 'false'],
            'Stellplatz' => ['Stellplatz', 'false', 'false', 'true', 'false'],
            'Parkhaus' => ['Parkhaus', 'false', 'false', 'false', 'true'],
            'nicht vorhanden' => ['nicht vorhanden', 'false', 'false', 'false', 'false'],
        ];
    }

    /**
     * @test
     *
     * @param string $sourceValue
     * @param string $garageValue
     * @param string $undergroundValue
     * @param string $parkingSpaceValue
     * @param string $parkingGarageValue
     * @dataProvider parkingSpaceDataProvider
     */
    public function buildMapsParkingSpaceFields(
        $sourceValue,
        $garageValue,
        $undergroundValue,
        $parkingSpaceValue,
        $parkingGarageValue
    ) {
        $result = $this->subject->buildFromFields(['parkingSpaceType' => $sourceValue]);

        $equipmentElement = $result->getElementsByTagName('ausstattung')->item(0);
        static::assertNotNull($equipmentElement);

        $packingSpaceElement = $equipmentElement->getElementsByTagName('stellplatzart')->item(0);
        static::assertNotNull($packingSpaceElement);

        static::assertSame($garageValue, $packingSpaceElement->getAttribute('GARAGE'));
        static::assertSame($undergroundValue, $packingSpaceElement->getAttribute('TIEFGARAGE'));
        static::assertSame($parkingSpaceValue, $packingSpaceElement->getAttribute('FREIPLATZ'));
        static::assertSame($parkingGarageValue, $packingSpaceElement->getAttribute('PARKHAUS'));
    }

    /**
     * @return string[][]
     */
    public function imagesDataProvider()
    {
        return [
            'image 1 with title' => ['imageFileName 01', '00513444.jpg', 'imageTitle 01', 'Objektbild'],
            'image 1 without title' => ['imageFileName 01', '00513444.jpg', 'imageTitle 01', ''],
            'image 2 with title' => ['imageFileName 02', '00513444.jpg', 'imageTitle 02', 'Objektbild'],
            'image 3 with title' => ['imageFileName 03', '00513444.jpg', 'imageTitle 03', 'Objektbild'],
            'image 4 with title' => ['imageFileName 04', '00513444.jpg', 'imageTitle 04', 'Objektbild'],
            'image 5 with title' => ['imageFileName 05', '00513444.jpg', 'imageTitle 05', 'Objektbild'],
        ];
    }

    /**
     * @test
     *
     * @param string $fileFieldName
     * @param string $fileName
     * @param string $titleFieldName
     * @param string $title
     * @dataProvider imagesDataProvider
     */
    public function buildMapsImagesFields(
        $fileFieldName,
        $fileName,
        $titleFieldName,
        $title
    ) {
        $result = $this->subject->buildFromFields([$fileFieldName => $fileName, $titleFieldName => $title]);

        $attachmentsElement = $result->getElementsByTagName('anhaenge')->item(0);
        static::assertNotNull($attachmentsElement);

        $attachmentElement = $attachmentsElement->getElementsByTagName('anhang')->item(0);
        static::assertNotNull($attachmentElement);
        static::assertSame('EXTERN', $attachmentElement->getAttribute('location'));

        $titleElement = $attachmentElement->getElementsByTagName('anhangtitel')->item(0);
        static::assertNotNull($titleElement);
        static::assertSame($title, $titleElement->nodeValue);

        $formatElement = $attachmentElement->getElementsByTagName('format')->item(0);
        static::assertNotNull($formatElement);
        static::assertSame('jpeg', $formatElement->nodeValue);

        $dataElement = $attachmentElement->getElementsByTagName('daten')->item(0);
        static::assertNotNull($dataElement);

        $pathElement = $dataElement->getElementsByTagName('pfad')->item(0);
        static::assertNotNull($pathElement);
        static::assertSame($fileName, $pathElement->nodeValue);
    }
}
