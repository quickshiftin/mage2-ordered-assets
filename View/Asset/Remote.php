<?php
namespace Quickshiftin\Assetorderer\View\Asset;

use
    Magento\Framework\View\Asset\Remote as RemoteAsset,
    Magento\Framework\View\Asset\MergeableInterface;

class Remote implements MergeableInterface
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
    // AssetInterface;
    public function getUrl()               { return $this->_oRealRemoteFile->getUrl(); }
    public function getContentType()       { return $this->_oRealRemoteFile->getContentType(); }
    public function getSourceContentType() { return $this->_oRealRemoteFile->getSourceContentType(); }

    // LocalInterface
    public function getSourceFile() { return $this->_oRealRemoteFile->getSourceFile(); }
    public function getContent()    { return $this->_oRealRemoteFile->getContent(); }
    public function getFilePath()   { return $this->_oRealRemoteFile->getFilePath(); }
    public function getContext()    { return $this->_oRealRemoteFile->getContext(); }
    public function getModule()     { return $this->_oRealRemoteFile->getModule(); }
    public function getPath()       { return $this->_oRealRemoteFile->getPath();  }
}