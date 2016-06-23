<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($this->getTable('salesrule_label_failed'),
        'label2',
        'varchar(255) DEFAULT NULL'
    );

$installer->getConnection()
    ->addColumn($this->getTable('salesrule_label_failed'),
        'label3',
        'varchar(255) DEFAULT NULL'
    );

$installer->endSetup();