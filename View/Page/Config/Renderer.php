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
                $template = $this->getAssetTemplate(
                    $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE),
                    $this->addDefaultAttributes($this->getAssetContentType($asset), $attributes)
                );
                $tag = sprintf($template, $asset->getUrl());
                
                //--------------------------------------------------------------------
                // Store the desired order while generating the original string output
                //--------------------------------------------------------------------
                if(method_exists($asset, 'getOrder')) {
                    $this->_aAssetOrder[md5(trim($tag))] = $asset->getOrder();
                } else {
                    $this->_aAssetOrder[md5(trim($tag))] = 1;
                }

                $result .= $tag;
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $result .= sprintf($template, $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']));
        }
        return $result;
    }

    private function _afterRenderAssets($result)
    {
        $aPieces    = explode("\n", $result);
        $sNewResult = '';
        $aUnordered = [];
        $aOrdered   = [];
        $bNeedsFlattenend = false;
        foreach($aPieces as $sNewTag) {
            $sSearch = md5($sNewTag);
            // Special processing for css tag, whereby order from layout is honored
            if(!isset($this->_aAssetOrder[$sSearch]) || $this->_aAssetOrder[$sSearch] == 1) {
                $aUnordered[] = $sNewTag;
            } else {
                $iOrder = $this->_aAssetOrder[$sSearch];

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