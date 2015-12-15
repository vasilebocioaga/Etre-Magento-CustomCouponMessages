<?php

class Etre_Promomod_Model_Failedmessages extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('etre_promomod/failedmessages');
    }

    public function getDefaultStoreLabel($ruleId)
    {
        $storeId = Mage::app()->getStore()->getId();
        $defaultGlobalLabel = $this->getDefaultGlobalLabel($ruleId);
        if(is_array($defaultStoreLabel = $this->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('rule_id', $ruleId)
            ->getData()));
        if (isset($defaultStoreLabel[0]["label"])):
            return $defaultStoreLabel[0]["label"];
        else:
            return $defaultGlobalLabel;
        endif;
    }

    public function getDefaultGlobalLabel($ruleId)
    {
        $result = $this->getCollection()
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('rule_id', $ruleId)
            ->getData();
        if(isset($result[0]["label"])) return $result[0]["label"];
        return false;
    }
}
