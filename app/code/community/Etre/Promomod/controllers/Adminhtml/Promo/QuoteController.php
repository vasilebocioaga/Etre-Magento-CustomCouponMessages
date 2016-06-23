<?php

require_once 'Mage/Adminhtml/controllers/Promo/QuoteController.php';

class Etre_Promomod_Adminhtml_Promo_QuoteController extends Mage_Adminhtml_Promo_QuoteController
{

    private function insertFailedLabels($ruleId)
    {

        $storeMessages = $this->getRequest()->getParam("promomod_store_apply_failed_message");
        if (!empty($existingRuleLabels = Mage::getModel('etre_promomod/failedmessages')->getCollection()
            ->addFieldToFilter('rule_id', $ruleId))
        ):
            foreach ($existingRuleLabels as $failedLabel):
                $failedLabel->delete();
            endforeach;
        endif;
        $keys = array_keys($storeMessages);
        foreach ($storeMessages[$keys[0]] as $store => $failedMessage) {
            $failedMessageModel = Mage::getModel("etre_promomod/failedmessages");
            foreach ($keys as $key) {
                $data[$key] = (isset($storeMessages[$key][$store])) ? $storeMessages[$key][$store] : null;
            }
            $data["store_id"] = $store;
            $data["rule_id"] = $ruleId;
            $failedMessageModel->setData($data);
            $failedMessageModel->save();
        }

    }

    private function deleteFailedLabels($ruleId)
    {
        if (!empty($existingRuleLabels = Mage::getModel('etre_promomod/failedmessages')->getCollection()
            ->addFieldToFilter('rule_id', $ruleId))
        ):
            foreach ($existingRuleLabels as $failedLabel):
                //echo $failedLabel->getData("label_id");
                $failedLabel->delete();
            endforeach;
        endif;
    }

    /**
     * Promo quote save action
     *
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                /** @var $model Mage_SalesRule_Model_Rule */
                $model = Mage::getModel('salesrule/rule');
                Mage::dispatchEvent(
                    'adminhtml_controller_salesrule_prepare_save',
                    array('request' => $this->getRequest()));
                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('salesrule')->__('Wrong rule specified.'));
                    }
                }

                $session = Mage::getSingleton('adminhtml/session');

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                    && isset($data['discount_amount'])
                ) {
                    $data['discount_amount'] = min(100, $data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $model->loadPost($data);

                $useAutoGeneration = (int)!empty($data['use_auto_generation']);
                $model->setUseAutoGeneration($useAutoGeneration);

                $session->setPageData($model->getData());

                $model->save();

                $this->insertFailedLabels($model->getId());

                $session->addSuccess(Mage::helper('salesrule')->__('The rule has been saved.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/new');
                }
                return;

            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('salesrule/rule');
                $model->load($id);
                $model->delete();
                $this->deleteFailedLabels($id);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('salesrule')->__('The rule has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while deleting the rule. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('salesrule')->__('Unable to find a rule to delete.'));
        $this->_redirect('*/*/');
    }
}
