<?php

namespace Quickshiftin\Assetorderer\View\Asset;


class Remote
{
    private $_iOrder = 1;
    private $_oRealRemoteFile;

    public function __call($sMethod, array $aArgs=[])
    {
        return call_user_func_array([$this->_oRealRemoteFile, $sMethod], $aArgs);
    }

    public function setRealRemoteFile(\Magento\Framework\View\Asset\Remote $oRealRemoteFile) {
        $this->_oRealRemoteFile = $oRealRemoteFile;
    }

    public function getOrder() { return $this->_iOrder; }
    public function setOrder($iOrder) { $this->_iOrder = $iOrder; }
}