<?php

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE `{$installer->getTable('salesrule_label_failed')}` (
  `label_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();