<?php
namespace Quickshiftin\Assetorderer\View\Asset;

use Magento\Framework\View\Asset\MergeableInterface;

class File implements MergeableInterface
{
    private $_iOrder = 1;
    private $_oRealFile;

    // Implement AssetInterface
    public function getUrl() { return $this->_oRealFile->getUrl(); }
    public function getContentType() { return $this->_oRealFile->getContentType(); }
    public function getSourceContentType() { return $this->_oRealFile->getSourceContentType(); }

    // Implement LocalInterface
    public function getSourceFile() { return $this->_oRealFile->getSourceFile(); }
    public function getContent() { return $this->_oRealFile->getContent(); }
    public function getFilePath() { return $this->_oRealFile->getFilePath(); }
    public function getContext() { return $this->_oRealFile->getContext(); }
    public function getModule() { return $this->_oRealFile->getModule(); }
    public function getPath() { return $this->_oRealFile->getPath(); }

    // @note MergeableInterface implements LocalInterface, but does not add any methods as of yet

    // Forward everything else to the wrapped File instance
    public function __call($sMethod, array $aArgs=[])
    {
        return call_user_func_array([$this->_oRealFile, $sMethod], $aArgs);
    }

    public function setRealFile($oRealFile) {
        $this->_oRealFile = $oRealFile;
    }

    public function getOrder() { return $this->_iOrder; }
    public function setOrder($iOrder) { $this->_iOrder = $iOrder; }
}
