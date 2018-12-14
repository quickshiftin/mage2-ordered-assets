<?php
namespace Quickshiftin\Assetorderer\Config\Dom;

use
    DomDocument,
    Magento\Framework\Config\Dom\UrnResolver as MageUrnResolver;

class UrnResolver
{
    const NS = 'http://www.w3.org/2001/XMLSchema';

    public function aroundGetRealPath(MageUrnResolver $urnResolver, Callable $proceed, $urn)
    {
        $result = $proceed($urn);
        if($urn == 'urn:magento:framework:View/Layout/etc/head.xsd') {
           $result = self::addLinkTypeOrderAttr($result);
        }
        return $result;
    }

    /**
     * On older versions of Magento2, add the order attribute along with this extension
     */
    public static function addLinkTypeOrderAttr($schemaPath)
    {
        $xsd = new DomDocument();
        $xsd->load($schemaPath);
        $hasOrder = false;
        $linkTypeNode = null;
        foreach($xsd->getElementsByTagNameNS(self::NS, 'complexType') as $outerNode) {
            if($outerNode->attributes && $outerNode->attributes->getNamedItem('name')->nodeValue != 'linkType') {
                continue;
            }
            $linkTypeNode = $outerNode;
            foreach($outerNode->childNodes as $innerNode) {
                if($innerNode->attributes) {
                    $nodeName = $innerNode
                        ->attributes
                        ->getNamedItem('name')
                        ->nodeValue;
        
                    if($nodeName == 'order') {
                        $hasOrder = true;
                        break 2;
                    }
                }
            }
        }

        // If this is an older version of Magento,
        // we'll ammend the linkType complexType with support for the order attribute
        if($hasOrder) {
            return realpath($schemaPath);
        }

        $newNode = $xsd->createElementNS(self::NS, 'xs:attribute');
        $newNode->setAttribute('name', 'order');
        $newNode->setAttribute('type', 'xs:string');
        $linkTypeNode->appendChild($newNode);

        $newXsdPath = __DIR__ . '/../../etc/head.xsd';
        if(!file_exists($newXsdPath)) {
            if(file_put_contents($newXsdPath, $xsd->saveXML())) {
                return realpath($newXsdPath);
            }
            return realpath($schemaPath);
        }
        return realpath($newXsdPath);
    }
}