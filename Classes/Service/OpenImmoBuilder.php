<?php
namespace OliverKlee\CsvToOpenImmo\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class takes care of building an OpenImmo XML structure from CSV lines.
 *
 * Create a new instance for each CSV file (or OpenImmo file).
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class OpenImmoBuilder
{
    /**
     * @var string
     */
    const BASIC_XML = '
        <openimmo>
            <uebertragung art="ONLINE" umfang="TEIL" modus="CHANGE" version="1.2.7" sendersoftware="TYPO3" senderversion="1.0.x-dev"/>
            <anbieter>
                <anbieternr/>
                <firma/>
                <openimmo_anid/>
            </anbieter>
        </openimmo>
    ';

    /**
     * @var RealtyObjectBuilder
     */
    private $realtyObjectBuilder = null;

    /**
     * @var \DOMDocument
     */
    private $document = null;

    /**
     * @var \DOMElement
     */
    private $offererElement = null;

    public function __construct()
    {
        $this->buildBasicDocument();
        $this->realtyObjectBuilder = GeneralUtility::makeInstance(RealtyObjectBuilder::class, $this->document);
    }

    /**
     * Builds a basic valid document in $this->document.
     *
     * @return void
     */
    private function buildBasicDocument()
    {
        $this->document = new \DOMDocument('1.0', 'utf-8');
        $this->document->loadXML(static::BASIC_XML);
        $this->offererElement = $this->document->getElementsByTagName('anbieter')->item(0);
    }

    /**
     * @return \DOMDocument
     */
    public function build()
    {
        return $this->document;
    }

    /**
     * @param string[] $fieldValues the data for one object as it comes from the CsvReader
     *
     * @return void
     */
    public function addObject(array $fieldValues)
    {
        $this->offererElement->appendChild($this->realtyObjectBuilder->buildFromFields($fieldValues));
    }
}
