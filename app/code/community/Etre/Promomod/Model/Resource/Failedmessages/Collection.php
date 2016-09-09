<?php

class Etre_Promomod_Model_Resource_Failedmessages_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('etre_promomod/failedmessages');
    }
}
