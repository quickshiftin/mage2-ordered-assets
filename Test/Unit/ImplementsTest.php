<?php
namespace Quickshiftin\Assetorderer\Test\Unit;

use
    PHPUnit\Framework\TestCase,
    Magento\Framework\View\Asset\LocalInterface,
    Magento\Framework\View\Asset\MergeableInterface,
    Quickshiftin\Assetorderer\View\Asset\File,
    Quickshiftin\Assetorderer\View\Asset\Remote;

/**
 * A test to verify the fix for issue #11 is working as expected.
 * https://github.com/quickshiftin/mage2-ordered-assets/issues/11
 */
class ImplementsTest extends TestCase
{
    /**
     * Verify instances of File & Remote object implement expected interfaces.
     */
    public function testImplements()
    {
        $this->_assertImplements(new File());
        $this->_assertImplements(new Remote());
    }

    private function _assertImplements($o)
    {
        $this->assertInstanceOf(LocalInterface::class, $o);
        $this->assertInstanceOf(MergeableInterface::class, $o);
    }
}