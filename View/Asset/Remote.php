<?php
namespace Quickshiftin\Assetorderer\View\Asset;

use
    Magento\Framework\View\Asset\Remote as RemoteAsset,
    Magento\Framework\View\Asset\LocalInterface;

class Remote implements LocalInterface
{
    private
        $_iOrder = 1,
        $_oRealRemoteFile;

    public function __call($sMethod, array $aArgs=[])
    {
        return call_user_func_array([$this->_oRealRemoteFile, $sMethod], $aArgs);
    }

    public function setRealRemoteFile(RemoteAsset $oRealRemoteFile)
    {
        $this->_oRealRemoteFile = $oRealRemoteFile;
    }

    public function getOrder() { return $this->_iOrder; }
    public function setOrder($iOrder) { $this->_iOrder = $iOrder; }

    // Boilerplate to implement interface -----------------------------------
    // AssetInterface
    public function getUrl();
    public function getContentType();
    public function getSourceContentType();

    // LocalInterface
    public function getSourceFile() { return $this->_oRealFile->getSourceFile(); }
    public function getContent()    { return $this->_oRealFile->getContent(); }
    public function getFilePath()   { return $this->_oRealFile->getFilePath(); }
    public function getContext()    { return $this->_oRealFile->getContext(); }
    public function getModule()     { return $this->_oRealFile->getModule(); }
    public function getPath()       { return $this->_oRealFile->getPath();  }
}