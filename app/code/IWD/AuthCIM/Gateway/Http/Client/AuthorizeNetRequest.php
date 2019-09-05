<?php

namespace IWD\AuthCIM\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Xml\Security;

/**
 * Class AuthorizeNetRequest
 * @package IWD\AuthCIM\Gateway\Http\Client
 */
class AuthorizeNetRequest implements ClientInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var TransferInterface
     */
    private $transferObject;

    /**
     * @var Security
     */
    private $xmlSecurity;

    /**
     * @var string
     */
    private $response;

    /**
     * AuthorizeNetRequest constructor.
     * @param Logger $logger
     * @param CurlFactory $curlFactory
     * @param Security $xmlSecurity
     */
    public function __construct(
        Logger $logger,
        CurlFactory $curlFactory,
        Security $xmlSecurity
    ) {
        $this->logger = $logger;
        $this->curlFactory = $curlFactory;
        $this->xmlSecurity = $xmlSecurity;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $this->transferObject = $transferObject;

        $this->saveRequestToLog();

        $this->sendCurlRequest();
        $this->convertResponseToArray();
        $this->saveResponseToLog();

        return $this->response;
    }

    /**
     * Save request to log
     *
     * @return void
     */
    private function saveRequestToLog()
    {
        $request = [$this->getRootName() => $this->getBody()];
        //$request = json_encode($request);
        $request = ['IWD AUTH CIM REQUEST: ' => $request];
        $this->logger->debug($request, ['name', 'transactionKey', 'cardCode', 'cardNumber', 'expirationDate']);
    }

    /**
     * Save response to log
     *
     * @return void
     */
    private function saveResponseToLog()
    {
        $response = $this->response;
        //$response = json_encode($response);
        $response = ['IWD AUTH CIM RESPONSE:' => $response];
        $this->logger->debug($response);
    }

    /**
     * Posts the request to AuthorizeNet & returns response.
     *
     * @return string
     */
    private function sendCurlRequest()
    {
        $config = [
            'timeout' => 45,
            'header' => false,
            'verifypeer' => 0,
            'verifyhost' => 0
        ];

        $http = $this->curlFactory->create();
        $http->setConfig($config);
        $http->write(
            \Zend_Http_Client::POST,
            $this->transferObject->getUri(),
            '1.1',
            $this->getHeaders(),
            $this->getPostFields()
        );

        $this->response = $http->read();

        return $this->response;
    }

    /**
     * Convert response to array
     *
     * @return array
     */
    private function convertResponseToArray()
    {
        $response = str_replace('xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"', '', $this->response);
        $this->response = $this->xmlToAssoc($response);
        return $this->response;
    }

    /**
     * Transform \SimpleXMLElement to associative array
     * \SimpleXMLElement must be conform structure, generated by assocToXml()
     *
     * @param string $xml
     * @return array
     */
    private function xmlToAssoc($xml)
    {
        $domDocument = new \DomDocument('1.0', 'UTF-8');
        $domDocument->formatOutput = true;
        $domDocument->loadXML($xml);

        return $this->convertXmlToArray($domDocument->documentElement);
    }

    /**
     * @param $node
     * @return array|string
     */
    private function convertXmlToArray($node)
    {
        $output = [];

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                $output['@cdata'] = trim($node->textContent);
                break;

            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;

            case XML_ELEMENT_NODE:
                $count = $node->childNodes->length;
                for ($i = 0; $i < $count; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->convertXmlToArray($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = [];
                        }

                        $output[$t][] = $v;
                    } else {
                        if ($v !== '') {
                            $output = $v;
                        }
                    }
                }

                if (is_array($output)) {
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1) {
                            $output[$t] = $v[0];
                        }
                    }

                    if (empty($output)) {
                        $output = '';
                    }
                }

                if ($node->attributes->length) {
                    $a = [];
                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrName] = (string)$attrNode->value;
                    }

                    if (!is_array($output)) {
                        $output = ['@value' => $output];
                    }

                    $output['@attributes'] = $a;
                }
                break;
        }

        return $output;
    }

    /**
     * Get headers for http request
     *
     * @return array
     */
    private function getHeaders()
    {
        $headers = [];
        if (preg_match('/xml/', $this->transferObject->getUri())) {
            $headers = ["Content-Type: text/xml"];
        }

        return $headers;
    }

    /**
     * Get post fields
     *
     * @return string
     */
    private function getPostFields()
    {
        $rootName = $this->getRootName();
        $body = $this->getBody();

        $xml = $this->convertArray2Xml($rootName, $body);
        $xml->addAttribute('xmlns', 'AnetApi/xml/v1/schema/AnetApiSchema.xsd');

        return $xml->asXML();
    }

    /**
     * @param $rootName
     * @param $body
     * @return \SimpleXMLElement
     */
    private function convertArray2Xml($rootName, $body)
    {
        $createArrayImporter = function (\SimpleXMLElement $subject) {
            $add = function (\SimpleXMLElement $subject, $key, $value) use (&$add) {
                $value = is_int($value)
                    ? "$value"
                    : (is_float($value)
                        ? "$value"
                        : (is_bool($value)
                            ? ($value ? "true" : "false")
                            : $value
                        )
                    );
                $hasKey    = is_string($key);
                $isString  = is_string($value);
                $isArray   = is_array($value);
                $count     = count($value);
                $isIndexed = $isArray && $count > 1 && array_keys($value) === range(0, $count - 1);
                $isKeyed   = $isArray && $count && !$isIndexed;
                switch (true) {
                    case $isString && $hasKey:
                        return $subject->addChild($key, $value);
                    case $isIndexed && $hasKey:
                        foreach ($value as $oneof_value) {
                            $add($subject, $key, $oneof_value);
                        }
                        return $subject->$key;
                    case $isKeyed && $hasKey:
                        $subject = $subject->addChild($key);
                        // fall-through intended
                    case $isKeyed:
                        foreach ($value as $oneof_key => $oneof_value) {
                            $add($subject, $oneof_key, $oneof_value);
                        }
                        return true;
                    default:
                        throw new LocalizedException(__('Unknown node type'));
                }
            };
            return function (array $array) use ($subject, $add) {
                $add($subject, null, $array);
                return $subject;
            };
        };

        $xmlStr = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<$rootName></$rootName>
XML;

        $xml = new \SimpleXMLElement($xmlStr);
        $importer = $createArrayImporter($xml);

        return $importer($body);
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    private function getRootName()
    {
        $body = $this->transferObject->getBody();
        if (!isset($body['root'])) {
            throw new LocalizedException(__('Parameter "root" does not exists'));
        }

        return $body['root'];
    }

    /**
     * @return array|string
     */
    private function getBody()
    {
        $body = $this->transferObject->getBody();

        if (is_array($body) && isset($body['root'])) {
            unset($body['root']);
        }

        return $this->removeEmptyValues($body);
    }

    /**
     * @param $input
     * @return array
     */
    private function removeEmptyValues($input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->removeEmptyValues($value);
            }
        }

        return array_filter(
            $input,
            function ($element) {
                return $element !== null && !(is_string($element) && trim($element) === '');
            }
        );
    }

    /**
     * Parse XML string and return XML document object or false
     *
     * @param string $xmlContent
     * @param string $customSimplexml
     * @return \SimpleXMLElement|bool
     * @throws LocalizedException
     */
    public function parseXml($xmlContent, $customSimplexml = 'SimpleXMLElement')
    {
        if (!$this->xmlSecurity->scan($xmlContent)) {
            throw new LocalizedException(__('Security validation of XML document has been failed.'));
        }

        return simplexml_load_string($xmlContent, $customSimplexml);
    }
}