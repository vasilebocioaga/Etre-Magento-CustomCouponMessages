<?php

class Etre_Promomod_Model_Resource_Failedmessages extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('etre_promomod/failedmessages', 'label_id');
    }
}
