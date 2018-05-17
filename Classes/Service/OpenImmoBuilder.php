<?php
namespace OliverKlee\CsvToOpenImmo\Service;

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
            <uebertragung art="ONLINE" umfang="VOLL" version="1.2.7" sendersoftware="TYPO3" senderversion="1.0.x-dev"/>
            <anbieter>
                <anbieternr/>
                <firma/>
                <openimmo_anid/>
            </anbieter>
        </openimmo>
    ';

    /**
     * @var \DOMDocument
     */
    private $document = null;

    public function __construct()
    {
        $this->buildBasicDocument();
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
    }

    /**
     * @return \DOMDocument
     */
    public function build()
    {
        return $this->document;
    }
}
