<?php
namespace Quickshiftin\Assetorderer\View\Page\Config;

use
    SimpleXMLElement,
    Magento\Framework\View\Page\Config\Renderer as MageRenderer,
    Magento\Framework\Exception\LocalizedException,
    Magento\Framework\View\Asset\GroupedCollection,
    Magento\Framework\View\Page\Config;

/**
 * This file relies on the extension's custom PropertyGroup class to tell us what order the CSS
 * tags should be in. It then rewrites them honoring the order attribute from the layout XML.
 */
class Renderer extends MageRenderer
{
    private
        $_aAssetOrder = [];

    public function renderAssets($resultGroups = [])
    {
        /** @var $group \Magento\Framework\View\Asset\PropertyGroup */
        foreach ($this->pageConfig->getAssetCollection()->getGroups() as $group) {
            $type = $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE);
            if (!isset($resultGroups[$type])) {
                $resultGroups[$type] = '';
            }
            $resultGroups[$type] .= $this->renderAssetGroup($group);
        }
        $originalResult = implode('', $resultGroups);

        //-----------------------------------------------------------------------------
        // Now reprocess the string output, rearranging asset tags in the desired order
        //-----------------------------------------------------------------------------
        return $this->_afterRenderAssets($originalResult);
    }

    protected function renderAssetHtml(\Magento\Framework\View\Asset\PropertyGroup $group)
    {
        $assets = $this->processMerge($group->getAll(), $group);
        $attributes = $this->getGroupAttributes($group);
        $result = '';
        try {
            /** @var $asset \Magento\Framework\View\Asset\AssetInterface */
            foreach ($assets as $asset) {
                //--------------------------------------------------------------------
                // Store the desired order while generating the original string output
                //--------------------------------------------------------------------
                if(method_exists($asset, 'getOrder')) {
                    $this->_aAssetOrder[] = $asset->getOrder();
                } else {
                    $this->_aAssetOrder[] = 1;
                }

                $template = $this->getAssetTemplate(
                    $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE),
                    $this->addDefaultAttributes($this->getAssetContentType($asset), $attributes)
                );
                $result .= sprintf($template, $asset->getUrl());
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $result .= sprintf($template, $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']));
        }
        return $result;
    }

    private function _afterRenderAssets($result)
    {
        $sResult    = '<?xml version="1.0" encoding="UTF-8"?><root>' . $result . '</root>';
        $oResult    = new SimpleXMLElement($sResult);
        $sNewResult = '';
        $aUnordered = [];
        $aOrdered   = [];
        $bNeedsFlattenend = false;
        $i = 0;
        foreach($oResult as $oAssetTag) {
            // Skip over non-css tags
            if((string)$oAssetTag['type'] != 'text/css') {
                if($oAssetTag->getName() == 'script') {
                    $sInitialResult  = (string)$oAssetTag->asXml() . "\n";
                    $sNewResult     .= str_replace('/>', '></script>', $sInitialResult);
                } else {
                    $sNewResult .= (string)$oAssetTag->asXml() . "\n";
                }
                $i++;
                continue;
            }

            $sNewTag = (string)$oAssetTag->asXml();
            if($this->_aAssetOrder[$i] == 1) {
                $aUnordered[] = $sNewTag;
            } else {
                $iOrder = $this->_aAssetOrder[$i];

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

            $i++;
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