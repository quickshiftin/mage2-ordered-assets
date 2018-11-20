<?php
namespace Quickshiftin\Assetorderer\Test\Unit;

use
    PHPUnit\Framework\TestCase,
    Magento\Framework\Config\Dom\UrnResolver,
    Magento\Framework\TestFramework\Unit\Utility\XsdValidator;

class XsdTest extends TestCase
{
    /**
     * We've overridden the <css /> tag's allowed attributes,
     * adding the "order" attribute. Let's see if it's working.
     */
    public function testCssTagSchemaValidation()
    {
        $urnResolver = new UrnResolver();
        $this->_xsdSchema = $urnResolver->getRealPath('urn:magento:framework:View/Layout/etc/page_configuration.xsd');
        $xsdValidator = new XsdValidator();
        
        $actualError = $xsdValidator->validate(
             $this->_xsdSchema,
            '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><head><css src="css/calendar.css" order="5"/></head></page>'
        );

        $this->assertEquals([], $actualError);
    }
}