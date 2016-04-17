<?php
namespace Quickshiftin\Assetorderer\View\Asset;

/**
 * This file assigns the order (set in layout XML) to File objects
 * so they can later be sorted by said order.
 */
class PropertyGroup
{
    public function afterGetAll(\Magento\Framework\View\Asset\PropertyGroup $subject, $result)
    {
        // Inspect the properties and bail early if they don't interest us
        $aProperties = $subject->getProperties();
        if(!isset($aProperties['content_type']) || $aProperties['content_type'] != 'css') {
            return $result;
        }

        $aRealResult = [];
        foreach($result as $sAssetPath => $_oFile) {
            $oFile = new File();
            $oFile->setRealFile($_oFile);

            if(isset($aProperties['attributes']) && isset($aProperties['attributes']['order'])) {
                $oFile->setOrder((int)$aProperties['attributes']['order']);
            } else {
                $oFile->setOrder(1);
            }

            $aRealResult[$sAssetPath] = $oFile;
        }

        return $aRealResult;
    }
}
