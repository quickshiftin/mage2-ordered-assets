<?php
namespace Quickshiftin\Assetorderer\View\Page\Config;

/**
 * This file relies on the extension's custom PropertyGroup class to tell us what order the CSS
 * tags should be in. It then rewrites them honoring the order attribute from the layout XML.
 */
class Renderer
{
    public function afterRenderAssets(\Magento\Framework\View\Page\Config\Renderer $subject, $result)
    {
        $sResult    = '<?xml version="1.0" encoding="UTF-8"?><root>' . $result . '</root>';
        $oResult    = new \SimpleXMLElement($sResult);
        $sNewResult = '';
        $aUnordered = [];
        $aOrdered   = [];
        $bNeedsFlattenend = false;
        foreach($oResult as $oAssetTag) {
            // Skip over non-css tags
            if((string)$oAssetTag['type'] != 'text/css') {
                if($oAssetTag->getName() == 'script') {
                    $sInitialResult = (string)$oAssetTag->asXml() . "\n";
                    $sNewResult    .= str_replace('/>', '></script>', $sInitialResult);
                } else {
                    $sNewResult .= (string)$oAssetTag->asXml() . "\n";
                }
                continue;
            }

            if(!isset($oAssetTag['order'])) {
                $aUnordered[] = (string)$oAssetTag->asXml();
            } else {
                $iOrder  = (int)$oAssetTag['order'];
                $sNewTag = str_replace('order="' . $iOrder . '" ', '', (string)$oAssetTag->asXml());

                // Handle tags that have the same order
                if(isset($aOrdered[$iOrder])) {
                    // If this is the second element w/ a duplicate order, create a sub-array for this order
                    if(!is_array($aOrdered[$iOrder])) {
                        $bNeedsFlattenend    = true;
                        $sFirstValForOrder   = $aOrdered[$iOrder];
                        $aOrdered[$iOrder]   = [$sFirstValForOrder];
                        $aOrdered[$iOrder][] = $sNewTag;
                    } else {
                        $aOrdered[$iOrder][] = $sNewTag;
                    }
                } else {
                    $aOrdered[$iOrder] = $sNewTag;
                }
            }
        }

        ksort($aOrdered);
        $sOrdered = '';
        if($bNeedsFlattenend) {
            foreach($aOrdered as $mOrderedSection) {
                if(is_array($mOrderedSection)) {
                    $sOrdered .= implode("\n", $mOrderedSection) . "\n";
                } else {
                    $sOrdered .= $mOrderedSection . "\n";
                }
            }
        } else {
            $sOrdered = implode("\n", $aOrdered);
        }

        $sOut = implode("\n", $aUnordered) . "\n" . $sOrdered . "\n" . $sNewResult;
        return str_replace("\n\n", "\n", $sOut);
    }
}