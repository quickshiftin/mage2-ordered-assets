<?php
namespace Quickshiftin\Assetorderer\View\Asset;

use
    Magento\Framework\View\Asset\PropertyGroup as MagePropertyGroup,
    Magento\Framework\View\Asset\File as FileAsset,
    Magento\Framework\View\Asset\Remote as RemoteAsset;

/**
 * This file assigns the order (set in layout XML) to File objects
 * (in memory) so they can later be sorted by said order, at render time.
 */
class PropertyGroup
{
    public function afterGetAll(MagePropertyGroup $subject, $result)
    {
        // Inspect the properties and bail early if they don't interest us
        $aProperties = $subject->getProperties();
        if(!isset($aProperties['content_type']) || $aProperties['content_type'] != 'css') {
            return $result;
        }

        // Decorate instances of Mage assets with our decorator classes,
        // imbuing them with new behavior
        $aRealResult = [];
        foreach($result as $sAssetPath => $_oFile) {
            // Fix for remote files
            if ($_oFile instanceof FileAsset) {
                $oFile = new File();
                $oFile->setRealFile($_oFile);
            } else if ($_oFile instanceof RemoteAsset) {
                $oFile = new Remote();
                $oFile->setRealRemoteFile($_oFile);
            }

            if(isset($aProperties['order'])) {
                $oFile->setOrder((int)$aProperties['order']);
            } else {
                $oFile->setOrder(1);
            }

            $aRealResult[$sAssetPath] = $oFile;
        }

        return $aRealResult;
    }
}
