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
     * outer array: OpenImmo element names
     * inner array: source field name => OpenImmo element name
     *
     * @var string[][]
     */
    private static $stringSubElementMapping = [
        'kontaktperson' => [
            'contactPersonSalutation' => 'anrede',
            'contactPersonFullName' => 'name',
            'contactPersonPhoneNumber' => 'tel_durchw',
            'contactPersonEmail' => 'email_zentrale',
        ],
        'geo' => [
            'street' => 'strasse',
            'zip' => 'plz',
            'city' => 'ort',
            'district' => 'regionaler_zusatz',
        ],
        'freitexte' => [
            'title' => 'objekttitel',
            'description' => 'objektbeschreibung',
            'equipment' => 'ausstatt_beschr',
            'location' => 'lage',
        ],
        'verwaltung_techn' => [
            'objectNumber' => 'objektnr_extern',
            'availabilityDate' => 'verfuegbar_ab',
        ],
        'zustand_angaben' => [
            'yearOfConstruction' => 'baujahr',
        ],
        'preise' => [
            'deposit' => 'kaution_text',
        ],
    ];

    /**
     * outer array: OpenImmo element names
     * inner array: source field name => OpenImmo element name
     *
     * @var string[][]
     */
    private static $decimalSubElementMapping = [
        'flaechen' => [
            'numberOfRooms' => 'anzahl_zimmer',
            'livingArea' => 'wohnflaeche',
        ],
        'preise' => [
            'rentWithoutHeatingCosts' => 'kaltmiete',
            'rentWithHeatingCosts' => 'warmmiete',
            'additionalCosts' => 'nebenkosten',
        ],
    ];

    /**
     * outer array: OpenImmo element names
     * inner array: source field name => OpenImmo element name
     *
     * @var string[][]
     */
    private static $integerSubElementMapping = [
        'geo' => [
            'floor' => 'etage',
            'numberOfFloors' => 'anzahl_etagen',
        ],
    ];

    /**
     * outer array: OpenImmo element names
     * inner array: source field name => OpenImmo element name
     *
     * @var string[][]
     */
    private static $booleanSubElementMapping = [
        'preise' => [
            'heatingIncludedInAdditionalCosts' => 'heizkosten_enthalten',
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
     */
    public function buildFromFields(array $fieldValues)
    {
        $this->fieldValues = $fieldValues;
        $this->immoElement = $this->document->createElement('immobilie');
        $this->addSubElements();

        return $this->immoElement;
    }

    /**
     * @return void
     */
    private function addSubElements()
    {
        foreach (static::$stringSubElementMapping as $newElementName => $mappings) {
            $newElement = $this->createOrFindElement($newElementName);
            foreach ($mappings as $sourceName => $targetName) {
                $this->createAndAppendStringBasedChildElement($newElement, $sourceName, $targetName);
            }
        }
        foreach (static::$decimalSubElementMapping as $newElementName => $mappings) {
            $newElement = $this->createOrFindElement($newElementName);
            foreach ($mappings as $sourceName => $targetName) {
                $this->createAndAppendDecimalBasedChildElement($newElement, $sourceName, $targetName);
            }
        }
        foreach (static::$integerSubElementMapping as $newElementName => $mappings) {
            $newElement = $this->createOrFindElement($newElementName);
            foreach ($mappings as $sourceName => $targetName) {
                $this->createAndAppendIntegerBasedChildElement($newElement, $sourceName, $targetName);
            }
        }
        foreach (static::$booleanSubElementMapping as $newElementName => $mappings) {
            $newElement = $this->createOrFindElement($newElementName);
            foreach ($mappings as $sourceName => $targetName) {
                $this->createAndAppendBooleanBasedChildElement($newElement, $sourceName, $targetName);
            }
        }
        $this->mapUtilization();
        $this->mapHeating();
        $this->mapImages();
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
     * @param \DOMElement $parentElement
     * @param string $sourceFieldName
     * @param string $targetElementName
     *
     * @return void
     */
    private function createAndAppendStringBasedChildElement(
        \DOMElement $parentElement,
        $sourceFieldName,
        $targetElementName
    ) {
        if (empty($this->fieldValues[$sourceFieldName])) {
            return;
        }

        $child = $this->document->createElement($targetElementName, $this->fieldValues[$sourceFieldName]);
        $parentElement->appendChild($child);
    }

    /**
     * @param \DOMElement $parentElement
     * @param string $sourceFieldName
     * @param string $targetElementName
     *
     * @return void
     */
    private function createAndAppendDecimalBasedChildElement(
        \DOMElement $parentElement,
        $sourceFieldName,
        $targetElementName
    ) {
        if (empty($this->fieldValues[$sourceFieldName])) {
            return;
        }

        $value = $this->normalizeDecimalValue($this->fieldValues[$sourceFieldName]);
        $child = $this->document->createElement($targetElementName, $value);
        $parentElement->appendChild($child);
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
     * @param \DOMElement $parentElement
     * @param string $sourceFieldName
     * @param string $targetElementName
     *
     * @return void
     */
    private function createAndAppendIntegerBasedChildElement(
        \DOMElement $parentElement,
        $sourceFieldName,
        $targetElementName
    ) {
        if (empty($this->fieldValues[$sourceFieldName])) {
            return;
        }

        $value = $this->normalizeIntegerValue($this->fieldValues[$sourceFieldName]);
        if ($value !== '0') {
            $child = $this->document->createElement($targetElementName, $value);
            $parentElement->appendChild($child);
        }
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
     * @param \DOMElement $parentElement
     * @param string $sourceFieldName
     * @param string $targetElementName
     *
     * @return void
     */
    private function createAndAppendBooleanBasedChildElement(
        \DOMElement $parentElement,
        $sourceFieldName,
        $targetElementName
    ) {
        if (empty($this->fieldValues[$sourceFieldName])) {
            return;
        }

        $value = $this->normalizeBooleanValue($this->fieldValues[$sourceFieldName]);
        $child = $this->document->createElement($targetElementName, $value);
        $parentElement->appendChild($child);
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
     * @return void
     */
    private function mapUtilization()
    {
        if (empty($this->fieldValues['utilization'])) {
            return;
        }

        $categoryElement = $this->createOrFindElement('objektkategorie');
        $utilizationElement = $this->document->createElement('nutzungsart');
        $categoryElement->appendChild($utilizationElement);

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

        $isStove = 'false';
        $heatingTypeElement->setAttribute('OFEN', $isStove);
        $isOneFloor = 'false';
        $heatingTypeElement->setAttribute('ETAGE', $isOneFloor);
        $isCentral = 'false';
        $heatingTypeElement->setAttribute('ZENTRAL', $isCentral);
        $isFloor = 'false';
        $heatingTypeElement->setAttribute('FUSSBODEN', $isFloor);
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

            $dataElement = $this->document->createElement('daten');
            $attachmentElement->appendChild($dataElement);

            $path = $this->fieldValues[$pathFieldName];
            $pathElement = $this->document->createElement('pfad', $path);
            $dataElement->appendChild($pathElement);

            $titleFieldName = 'imageTitle ' . sprintf('%02d', $i);
            $title = $this->fieldValues[$titleFieldName];
            $titleElement = $this->document->createElement('anhangtitel', $title);
            $attachmentElement->appendChild($titleElement);
        }
    }
}
