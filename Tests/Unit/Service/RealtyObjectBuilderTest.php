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
     * @return string[][]
     */
    public function contactPersonDataProvider()
    {
        return [
            'contactPersonSalutation' => ['contactPersonSalutation', 'anrede', 'Frau'],
            'contactPersonFullName' => ['contactPersonFullName', 'name', 'Kate Doe'],
            'contactPersonPhoneNumber' => ['contactPersonPhoneNumber', 'tel_durchw', '+49 1111 123123-22'],
            'contactPersonEmail' => ['contactPersonEmail', 'email_zentrale', 'kate@example.com'],
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
            'city' => ['city', 'ort', 'Kleincodingen', 'Kleincodingen'],
            'street' => ['street', 'strasse', 'Am Eck 42', 'Am Eck 42'],
            'district' => ['district', 'regionaler_zusatz', 'Nordstadt', 'Nordstadt'],
            'floor' => ['floor', 'etage', '3. Stock', '3'],
            'numberOfFloors' => ['numberOfFloors', 'anzahl_etagen', '5 Etagen', '5'],
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
            'title' => ['title', 'objekttitel', 'Maisonette-Wohnung mit 2 Bädern und Sauna (unbeheizt)'],
            'description' => ['description', 'objektbeschreibung', 'Wohnst du noch oder lebst do schon?'],
            'equipment' => ['equipment', 'ausstatt_beschr', 'Diese Wohnung hat alles, was das Herz begehrt.'],
            'location' => ['location', 'lage', 'Direkt in der Innenstadt'],
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
    public function administrationDataProvider()
    {
        return [
            'objectNumber' => ['objectNumber', 'objektnr_extern', 'A/B 42'],
            'availabilityDate' => ['availabilityDate', 'verfuegbar_ab', 'ab Anfang Oktober'],
        ];
    }

    /**
     * @test
     *
     * @param string $fieldName
     * @param string $tagName
     * @param string $value
     * @dataProvider administrationDataProvider
     */
    public function buildMapsAdministrationFields($fieldName, $tagName, $value)
    {
        $this->assertChildElementValue('verwaltung_techn', $fieldName, $tagName, $value, $value);
    }

    /**
     * @return string[][]
     */
    public function stateDataProvider()
    {
        return [
            'yearOfConstruction' => ['yearOfConstruction', 'baujahr', '1968'],
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
            'livingArea' => ['livingArea', 'wohnflaeche', '126,5m²', '126.5'],
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
    public function utilizationDataProvider()
    {
        return [
            'Wohnen' => ['Wohnraum', 'true', 'false'],
        ];
    }

    /**
     * @test
     *
     * @param string $sourceValue
     * @param string $habitationValue
     * @param string $businessValue
     * @dataProvider utilizationDataProvider
     */
    public function buildMapsUtilizationFields($sourceValue, $habitationValue, $businessValue)
    {
        $result = $this->subject->buildFromFields(['utilization' => $sourceValue]);

        $categoryElement = $result->getElementsByTagName('objektkategorie')->item(0);
        static::assertNotNull($categoryElement);

        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);
        static::assertNotNull($utilizationElement);

        static::assertSame($habitationValue, $utilizationElement->getAttribute('WOHNEN'));
        static::assertSame($businessValue, $utilizationElement->getAttribute('GEWERBE'));
    }

    /**
     * @return string[][]
     */
    public function heatingDataProvider()
    {
        return [
            'heatingType' => ['Fernwärme', 'false', 'false', 'false', 'true', 'false'],
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

        $dataElement = $attachmentElement->getElementsByTagName('daten')->item(0);
        static::assertNotNull($dataElement);

        $pathElement = $dataElement->getElementsByTagName('pfad')->item(0);
        static::assertNotNull($pathElement);
        static::assertSame($fileName, $pathElement->nodeValue);

        $titleElement = $attachmentElement->getElementsByTagName('anhangtitel')->item(0);
        static::assertNotNull($titleElement);
        static::assertSame($title, $titleElement->nodeValue);
    }
}
