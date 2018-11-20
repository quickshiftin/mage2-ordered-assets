<?php
namespace Quickshiftin\Assetorderer\Test\Unit;

use
    PHPUnit\Framework\TestCase,
    Magento\Framework\Config\Dom\UrnResolver as MageUrnResolver,
    Quickshiftin\Assetorderer\Config\Dom\UrnResolver,
    Magento\Framework\TestFramework\Unit\Utility\XsdValidator;

class XsdTest extends TestCase
{
    const CUSTOM_XSD_PATH = __DIR__ . '/../../etc/head.xsd';

    /**
     * We've overridden the <css /> tag's allowed attributes,
     * adding the "order" attribute. Let's see if it's working.
     */
    public function testCssTagSchemaValidation()
    {
        $urnResolver  = new MageUrnResolver();
        $xsdSchema    = $urnResolver->getRealPath('urn:magento:framework:View/Layout/etc/page_configuration.xsd');
        $xsdValidator = new XsdValidator();
        
        $actualError = $xsdValidator->validate(
             $xsdSchema,
            '<?xml version="1.0"?><page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><head><css src="css/calendar.css" order="5"/></head></page>'
        );

        $this->assertEquals([], $actualError);
    }

    /**
     * Determine if we can create a new XSD when the current one doesn't support an order attribute
     * on the css tag.
     */
    public function testUrnResolver()
    {
        $urnResolver = new MageUrnResolver();

        $xsdSchema = UrnResolver::addLinkTypeOrderAttr(realpath(__DIR__ . '/../_files/head-with-order.xsd'));
        $xsdSchema2 = UrnResolver::addLinkTypeOrderAttr(realpath(__DIR__ . '/../_files/head-no-order.xsd'));

        $this->assertEquals(realpath(self::CUSTOM_XSD_PATH), $xsdSchema2);
        $this->assertNotEquals($xsdSchema, $xsdSchema2);
    }

    protected function setUp()
    {
        $this->_cleanupXsd();
    }
    
    protected function tearDown()
    {
        $this->_cleanupXsd();
    }
    
    private static function _cleanupXsd()
    {
        if(file_exists(self::CUSTOM_XSD_PATH)) {
            unlink(self::CUSTOM_XSD_PATH);
        }
    }
}