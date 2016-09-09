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

        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            if (isset($defaultStoreLabel[0]["label3"])):
                return $defaultStoreLabel[0]["label3"];
            elseif (isset($defaultStoreLabel[0]["label"])):
                return $defaultStoreLabel[0]["label"];
            else: return $defaultGlobalLabel;
            endif;        }
        else{
            if (isset($defaultStoreLabel[0]["label2"])):
                return $defaultStoreLabel[0]["label2"];
            elseif (isset($defaultStoreLabel[0]["label"])):
                return $defaultStoreLabel[0]["label"];
            else: return $defaultGlobalLabel;
            endif;        }

        /*if (isset($defaultStoreLabel[0]["label"])):
            return $defaultStoreLabel[0]["label"];
        else:
            return $defaultGlobalLabel;
        endif;*/

    }

    public function getDefaultGlobalLabel($ruleId)
    {
        $result = $this->getCollection()
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('rule_id', $ruleId)
            ->getData();
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            if(isset($result[0]["label3"]))
                return $result[0]["label3"];
            else if(isset($result[0]["label"]))
                return $result[0]["label"];
        }else{
            if(isset($result[0]["label2"])) return $result[0]["label2"];
            else if(isset($result[0]["label"])) return $result[0]["label"];
        }
       // if(isset($result[0]["label"])) return $result[0]["label"];
        return false;
    }
}
