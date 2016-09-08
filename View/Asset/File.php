<?php
namespace Quickshiftin\Assetorderer\View\Asset;

class File
{
    private $_iOrder = 1;
    private $_oRealFile;

    public function __call($sMethod, array $aArgs=[])
    {
        return call_user_func_array([$this->_oRealFile, $sMethod], $aArgs);
    }

    public function setRealFile(\Magento\Framework\View\Asset\File $oRealFile) {
        $this->_oRealFile = $oRealFile;
    }

    public function getOrder() { return $this->_iOrder; }
    public function setOrder($iOrder) { $this->_iOrder = $iOrder; }
}