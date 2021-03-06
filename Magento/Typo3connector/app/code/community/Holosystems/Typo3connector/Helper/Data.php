<?php

class Holosystems_Typo3connector_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     *
     * @param string $identifier 
     * @return string $content
     */
    public function getContent($identifier) {

        $content = '';
        if ($identifier != null && $identifier != '') {
            $typo3connector = Mage::getModel('typo3connector/typo3connector')->getCollection()->addFilter('identifier', array('eq' => $identifier))->getFirstItem();
            if ($typo3connector->getStatus() == 1) {
                if ($typo3connector->getTypo3PagesId()) {
                    $content .= $this->_getPageContent($typo3connector->getTypo3PagesId());
                }
                if ($typo3connector->getTypo3TtContentIds()) {
                    $content .= $this->_getContentElements($typo3connector->getTypo3TtContentIds());
                }
            }
        }

        return $content;
    }

    /**
     *
     * @param integer $id
     * @return string $content
     */
    private function _getPageContent($id) {

        $content = '';
        $baseUrl = Mage::getStoreConfig('system/holosystems/typo3connector_baseurl');
        if ($baseUrl) {
            $feed_url = $baseUrl . "index.php?id=" . intval($id);
            $content = $this->_getCurlContent($feed_url);
        }
        return $content;
    }

    /**
     *
     * @param string $ids
     * @return string $content
     */
    private function _getContentElements($ids) {

        $content = '';
        $connectorUrl = Mage::getStoreConfig('system/holosystems/typo3connector_connectorurl');
        if ($connectorUrl) {
            $feed_url = $connectorUrl . "&tx_magentosync_content[contentIds]=" . $ids;
            $content = $this->_getCurlContentWithoutTrim($feed_url);
            return $content;
        }
    }

    /**
     * @param string $url
     * @return string 
     */
    private function _getCurlContent($url) {
        $ch = curl_init();
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(curl_setopt($ch, CURLOPT_HEADER, false));
        if (Mage::getStoreConfig('system/holosystems/typo3connector_httpuser') != '' && Mage::getStoreConfig('system/holosystems/typo3connector_httppassword') != '') {
            $curl->setConfig(array('timeout' => 60,'header'=>false,'userpwd' => Mage::getStoreConfig('system/holosystems/typo3connector_httpuser').':'.Mage::getStoreConfig('system/holosystems/typo3connector_httppassword')));
        } else {
            $curl->setConfig(array('timeout' => 60,'header'=>false)); //Timeout in no of seconds 
        }
        $curl->write(Zend_Http_Client::GET, $url, '1.1',array());
        
        $data = $curl->read();
        
        if ($data === false) {
            $content = '<!-- empty Content -->';
        }
        
        $curl->close();
        try {
            $content = $data; //output the data 
        } catch (Exception $e) {
            $content = '<!-- Content delivery failed -->';
        }
        return $content;
    }

    /**
     * @param string $url
     * @return string 
     */
    private function _getCurlContentWithoutTrim($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
        $data = curl_exec($ch);
        curl_close($ch);
        try {
            //$content = '<!-- '.$data.' -->'; //output the data 
            $content = $data; //output the data 
        } catch (Exception $e) {
            $content = '<!-- Content delivery failed -->';
        }
        return $content;
    }

}

?>