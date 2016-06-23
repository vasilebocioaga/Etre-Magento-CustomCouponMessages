<?php

/**
 * Created by PhpStorm.
 * User: tmills
 * Date: 6/8/2015
 * Time: 12:14 PM
 */
class Etre_Promomod_Block_Adminhtml_Promo_Quote_Edit_Tab_Labels extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Labels
{

    protected function _prepareForm()
    {

        $rule = Mage::registry('current_promo_quote_rule');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $failedLabelMessages = Mage::getModel('etre_promomod/failedmessages');
        $defaultFailedLabel = $failedLabelMessages->getCollection()
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('rule_id', $rule->getId())
            ->getData();
        $fieldset = $form->addFieldset('default_label_fieldset', array(
            'legend' => Mage::helper('salesrule')->__('Default Label')
        ));
        $labels = $rule->getStoreLabels();
        $fieldset->addField('store_default_label', 'text', array(
            'name' => 'store_labels[0]',
            'required' => false,
            'label' => Mage::helper('salesrule')->__('Default Rule Label for All Store Views'),
            'value' => isset($labels[0]) ? $labels[0] : '',
        ));
        $fieldset->addField('promomos_store_default_apply_failed_message', 'text', array(
            'name' => 'promomod_store_apply_failed_message[label][0]',
            'required' => false,
            'label' => Mage::helper('salesrule')->__('Default Message for Failed Coupon'),
            'value' => isset($defaultFailedLabel[0]['label']) ? $defaultFailedLabel[0]['label'] : '',
        ));

        $fieldset->addField('promomos_store_default_apply_failed_message_silog', 'text', array(
            'name' => 'promomod_store_apply_failed_message[label2][0]',
            'required' => false,
            'label' => Mage::helper('salesrule')->__('Default Message for LogIn'),
            'value' => isset($defaultFailedLabel[0]['label2']) ? $defaultFailedLabel[0]['label2'] : '',
        ));
        $fieldset->addField('promomos_store_default_apply_failed_message_nolog', 'text', array(
            'name' => 'promomod_store_apply_failed_message[label3][0]',
            'required' => false,
            'label' => Mage::helper('salesrule')->__('Default Message for not LogIn'),
            'value' => isset($defaultFailedLabel[0]['label3']) ? $defaultFailedLabel[0]['label3'] : '',
        ));

        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend' => Mage::helper('salesrule')->__('Store View Specific Labels'),
            'table_class' => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset');
        $fieldset->setRenderer($renderer);

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label' => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label' => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'text', array(
                        'name' => 'store_labels[' . $store->getId() . ']',
                        'required' => false,
                        'label' => $store->getName(),
                        'after_element_html' => "<div><small>{$this->__("If coupon fails to apply, default message.")}</small></div>",
                        'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                    $failedLabelMessages = Mage::getModel('etre_promomod/failedmessages');
                   /* if(empty($failedLabelMessages->getData())):

                    endif;*/
                    $defaultFailedLabel = $failedLabelMessages->getCollection()
                        ->addFieldToFilter('store_id', $store->getId())
                        ->addFieldToFilter('rule_id', $rule->getId())
                        ->getData();
                    $fieldset->addField("safm_{$store->getId()}", 'text', array(
                        'name' => 'promomod_store_apply_failed_message[label][' . $store->getId() . ']',
                        'required' => false,
                        'label' => $this->__("Failed coupon label"),
                        'after_element_html' => "<div><small>{$this->__("If coupon fails to apply, this message will override the default.")}</small></div>",
                        'value' => isset($defaultFailedLabel[0]["label"]) ? $defaultFailedLabel[0]["label"] : '',
                        'fieldset_html_class' => 'store',
                    ));
                       $fieldset->addField("safm2_{$store->getId()}", 'text', array(
                        'name' => 'promomod_store_apply_failed_message[label2][' . $store->getId() . ']',
                        'required' => false,
                        'label' => $this->__("Failed coupon label Login"),
                        'after_element_html' => "<div><small>{$this->__("If coupon fails to apply, this message will override the default.")}</small></div>",
                        'value' => isset($defaultFailedLabel[0]["label2"]) ? $defaultFailedLabel[0]["label2"] : '',
                        'fieldset_html_class' => 'store',
                    ));
                    $fieldset->addField("safm3_{$store->getId()}", 'text', array(
                        'name' => 'promomod_store_apply_failed_message[label3][' . $store->getId() . ']',
                        'required' => false,
                        'label' => $this->__("Failed coupon label no Login"),
                        'after_element_html' => "<div><small>{$this->__("If coupon fails to apply, this message will override the default.")}</small></div>",
                        'value' => isset($defaultFailedLabel[0]["label3"]) ? $defaultFailedLabel[0]["label3"] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }


        if ($rule->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);
        //return parent::_prepareForm();
    }
}
