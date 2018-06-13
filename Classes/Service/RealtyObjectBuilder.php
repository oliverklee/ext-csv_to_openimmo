<?php
namespace OliverKlee\CsvToOpenImmo\Service;

/**
 * This builds a single "immobilie" XML element from a CSV line passed as an array.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RealtyObjectBuilder
{
    /**
     * @var string
     */
    const TYPE_STRING = 'string';

    /**
     * @var string
     */
    const TYPE_DECIMAL = 'decimal';

    /**
     * @var string
     */
    const TYPE_INTEGER = 'integer';

    /**
     * @var string
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * @var string
     */
    const TYPE_EXISTENCE = 'existence';

    /**
     * outmost array: OpenImmo element names
     * mid array: OpenImmo element names
     * inner array: source field name => [OpenImmo element name, type]
     *
     * @var string[][]
     */
    private static $elementMapping = [
        'objektkategorie' => [],
        'geo' => [
            'zip' => ['plz', self::TYPE_STRING],
            'city' => ['ort', self::TYPE_STRING],
            'street' => ['strasse', self::TYPE_STRING],
            'streetNumber' => ['hausnummer', self::TYPE_STRING],
            'floor' => ['etage', self::TYPE_INTEGER],
            'numberOfFloors' => ['anzahl_etagen', self::TYPE_INTEGER],
            'district' => ['regionaler_zusatz', self::TYPE_STRING],
        ],
        'kontaktperson' => [
            'contactPersonEmail' => ['email_zentrale', self::TYPE_STRING],
            'contactPersonPhoneNumber' => ['tel_durchw', self::TYPE_STRING],
            'contactPersonFullName' => ['name', self::TYPE_STRING],
            'contactPersonSalutation' => ['anrede', self::TYPE_STRING],
        ],
        'preise' => [
            'rentWithoutHeatingCosts' => ['kaltmiete', self::TYPE_DECIMAL],
            'rentWithHeatingCosts' => ['warmmiete', self::TYPE_DECIMAL],
            'additionalCosts' => ['nebenkosten', self::TYPE_DECIMAL],
            'heatingIncludedInAdditionalCosts' => ['heizkosten_enthalten', self::TYPE_BOOLEAN],
            'deposit' => ['kaution_text', self::TYPE_STRING],
        ],
        'flaechen' => [
            'livingArea' => ['wohnflaeche', self::TYPE_DECIMAL],
            'numberOfRooms' => ['anzahl_zimmer', self::TYPE_DECIMAL],
            'balcony' => ['anzahl_balkone', self::TYPE_EXISTENCE],
        ],
        'ausstattung' => [],
        'zustand_angaben' => [
            'yearOfConstruction' => ['baujahr', self::TYPE_STRING],
        ],
        'freitexte' => [
            'title' => ['objekttitel', self::TYPE_STRING],
            'location' => ['lage', self::TYPE_STRING],
            'equipment' => ['ausstatt_beschr', self::TYPE_STRING],
            'description' => ['objektbeschreibung', self::TYPE_STRING],
        ],
        'anhaenge' => [],
        'verwaltung_objekt' => [
            'availabilityDate' => ['verfuegbar_ab', self::TYPE_STRING],
        ],
        'verwaltung_techn' => [
            'objectNumber' => ['objektnr_extern', self::TYPE_STRING],
        ],
    ];

    /**
     * @var \DOMDocument
     */
    private $document = null;

    /**
     * @var string[]
     */
    private $fieldValues = [];

    /**
     * @var \DOMElement
     */
    private $immoElement = null;

    /**
     * @param \DOMDocument $immoDocument
     */
    public function __construct(\DOMDocument $immoDocument)
    {
        $this->document = $immoDocument;
    }

    /**
     * @param string[] $fieldValues
     *
     * @return \DOMElement
     *
     * @throws \UnexpectedValueException
     */
    public function buildFromFields(array $fieldValues)
    {
        $this->fieldValues = $fieldValues;
        $this->immoElement = $this->document->createElement('immobilie');
        $this->addElements();
        $this->addSpecialElements();

        return $this->immoElement;
    }

    /**
     * Adds and converts the element in the correct sequence/order.
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    private function addElements()
    {
        $this->populateCategoryElement();
        foreach (static::$elementMapping as $elementName => $fieldMappings) {
            $parentElement = $this->createOrFindElement($elementName);
            foreach ($fieldMappings as $sourceFieldName => list($targetElementName, $type)) {
                if (empty($this->fieldValues[$sourceFieldName])) {
                    continue;
                }

                $rawValue = $this->fieldValues[$sourceFieldName];
                switch ($type) {
                    case self::TYPE_STRING:
                        $value = $rawValue;
                        break;
                    case self::TYPE_DECIMAL:
                        $value = $this->normalizeDecimalValue($rawValue);
                        break;
                    case self::TYPE_INTEGER:
                        $value = $this->normalizeIntegerValue($rawValue);
                        break;
                    case self::TYPE_BOOLEAN:
                        $value = $this->normalizeBooleanValue($rawValue);
                        break;
                    case self::TYPE_EXISTENCE:
                        $value = $this->normalizeExistenceValue($rawValue);
                        break;
                    default:
                        throw new \UnexpectedValueException(
                            'Unexpected type ' . $type . ' for element: ' . $sourceFieldName,
                            1526661439
                        );
                }
                $child = $this->document->createElement($targetElementName, $value);
                $parentElement->appendChild($child);
            }
        }
    }

    /**
     * @return void
     */
    private function populateCategoryElement()
    {
        $categoryElement = $this->createOrFindElement('objektkategorie');

        $utilizationElement = $this->document->createElement('nutzungsart');
        $categoryElement->appendChild($utilizationElement);

        $marketingTypeElement = $this->document->createElement('vermarktungsart');
        // This is hardcoded as this converter is limited to objects that are for rent.
        $marketingTypeElement->setAttribute('KAUF', 'false');
        $marketingTypeElement->setAttribute('MIETE_PACHT', 'true');
        $categoryElement->appendChild($marketingTypeElement);

        $objectTypeElement = $this->document->createElement('objektart');
        $flatElement = $this->document->createElement('wohnung');
        $objectTypeElement->appendChild($flatElement);
        $categoryElement->appendChild($objectTypeElement);
    }

    /**
     * @return void
     */
    private function addSpecialElements()
    {
        $this->mapUtilization();
        $this->mapHeating();
        $this->mapFiring();
        $this->mapImages();
        $this->populateTechnicalAdministrationElement();
    }

    /**
     * @param string $elementName
     *
     * @return \DOMElement
     */
    private function createOrFindElement($elementName)
    {
        $element = $this->immoElement->getElementsByTagName($elementName)->item(0);
        if ($element === null) {
            $element = $this->document->createElement($elementName);
            $this->immoElement->appendChild($element);
        }

        return $element;
    }

    /**
     * @param string $rawValue
     *
     * @return string
     */
    private function normalizeDecimalValue($rawValue)
    {
        $withDecimalPoints = str_replace(',', '.', $rawValue);
        $nonDecimalCharactersPattern = '/[^\\d\\.]/';

        return preg_replace($nonDecimalCharactersPattern, '', $withDecimalPoints);
    }

    /**
     * @param string $rawValue
     *
     * @return string
     */
    private function normalizeIntegerValue($rawValue)
    {
        return (string)(int)$rawValue;
    }

    /**
     * @param string $rawValue
     *
     * @return string
     */
    private function normalizeBooleanValue($rawValue)
    {
        $yesValues = ['true', '1', 'ja'];

        return in_array($rawValue, $yesValues, true) ? 'true' : 'false';
    }

    /**
     * @param string $rawValue
     *
     * @return string
     */
    private function normalizeExistenceValue($rawValue)
    {
        $oneValues = ['vorhanden'];

        return in_array($rawValue, $oneValues, true) ? '1' : '0';
    }

    /**
     * @return void
     */
    private function mapUtilization()
    {
        if (empty($this->fieldValues['utilization'])) {
            return;
        }

        $categoryElement = $this->createOrFindElement('objektkategorie');
        $utilizationElement = $categoryElement->getElementsByTagName('nutzungsart')->item(0);

        $value = $this->fieldValues['utilization'];

        $isHabitation = ($value === 'Wohnraum') ? 'true' : 'false';
        $utilizationElement->setAttribute('WOHNEN', $isHabitation);

        $isBusiness = 'false';
        $utilizationElement->setAttribute('GEWERBE', $isBusiness);
    }

    /**
     * @return void
     */
    private function mapHeating()
    {
        if (empty($this->fieldValues['heatingType'])) {
            return;
        }

        $equipmentElement = $this->createOrFindElement('ausstattung');
        $heatingTypeElement = $this->document->createElement('heizungsart');
        $equipmentElement->appendChild($heatingTypeElement);

        $value = $this->fieldValues['heatingType'];

        $isRemote = ($value === 'Fernwärme') ? 'true' : 'false';
        $heatingTypeElement->setAttribute('FERN', $isRemote);
        $isStove = ($value === 'Ofenheizung') ? 'true' : 'false';
        $heatingTypeElement->setAttribute('OFEN', $isStove);
        $isOneFloor = 'false';
        $heatingTypeElement->setAttribute('ETAGE', $isOneFloor);
        $isCentral = ($value === 'Gas zentral') ? 'true' : 'false';
        $heatingTypeElement->setAttribute('ZENTRAL', $isCentral);
        $isFloor = 'false';
        $heatingTypeElement->setAttribute('FUSSBODEN', $isFloor);
    }

    /**
     * @return void
     */
    private function mapFiring()
    {
        if (empty($this->fieldValues['heatingType'])) {
            return;
        }

        $equipmentElement = $this->createOrFindElement('ausstattung');
        $firingElement = $this->document->createElement('befeuerung');
        $equipmentElement->appendChild($firingElement);

        $value = $this->fieldValues['heatingType'];

        $isOil = ($value === 'Öl') ? 'true' : 'false';
        $firingElement->setAttribute('OEL', $isOil);
        $isGas = ($value === 'Gas dezentral' || $value === 'Gas zentral') ? 'true' : 'false';
        $firingElement->setAttribute('GAS', $isGas);
        $isRemote = ($value === 'Fernwärme') ? 'true' : 'false';
        $firingElement->setAttribute('FERN', $isRemote);
    }

    /**
     * @return void
     */
    private function mapImages()
    {
        for ($i = 1; $i <= 5; $i++) {
            $pathFieldName = 'imageFileName ' . sprintf('%02d', $i);
            if (empty($this->fieldValues[$pathFieldName])) {
                continue;
            }

            $attachmentsElement = $this->createOrFindElement('anhaenge');
            $attachmentElement = $this->document->createElement('anhang');
            $attachmentsElement->appendChild($attachmentElement);
            $attachmentElement->setAttribute('location', 'EXTERN');

            $titleFieldName = 'imageTitle ' . sprintf('%02d', $i);
            $title = $this->fieldValues[$titleFieldName];
            $titleElement = $this->document->createElement('anhangtitel', $title);
            $attachmentElement->appendChild($titleElement);

            $formatElement = $this->document->createElement('format', 'jpeg');
            $attachmentElement->appendChild($formatElement);

            $dataElement = $this->document->createElement('daten');
            $attachmentElement->appendChild($dataElement);

            $path = $this->fieldValues[$pathFieldName];
            $pathElement = $this->document->createElement('pfad', $path);
            $dataElement->appendChild($pathElement);
        }
    }

    /**
     * @return void
     */
    private function populateTechnicalAdministrationElement()
    {
        $administrationElement = $this->createOrFindElement('verwaltung_techn');

        $actionElement = $this->document->createElement('aktion');
        $administrationElement->appendChild($actionElement);

        $objectIdElement = $this->document->createElement('openimmo_obid', uniqid());
        $administrationElement->appendChild($objectIdElement);

        $originElement = $this->document->createElement('kennung_ursprung');
        $administrationElement->appendChild($originElement);

        $changeDateElement = $this->document->createElement('stand_vom', date('Y-m-d'));
        $administrationElement->appendChild($changeDateElement);
    }
}
