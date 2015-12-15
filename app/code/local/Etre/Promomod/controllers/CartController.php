<?php



require_once 'Mage/Checkout/controllers/CartController.php';
//require_once 'Enterprise/Checkout/controllers/CartController.php';

class Etre_Promomod_CartController extends Mage_Checkout_CartController 
//class Etre_Promomod_CartController extends Enterprise_Checkout_CartController
{

    public function couponPostAction()
    {
        /**
         * No reason to continue with empty shopping cart
         */
        $isAjax = $this->getRequest()->isXmlHttpRequest();
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            if ($isAjax) {
                $ajaxMessage = "You do not have any items in your cart.";
                $response['status'] = 0;
                $response['message'] = $ajaxMessage;
                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            } else {
                $this->_goBack();
                return;
            }
        }

        $couponCode = (string)$this->getRequest()->getParam('coupon_code');

        if (($this->getRequest()->getParam('remove') == 1) || (trim($this->getRequest()->getParam('remove')) == "")) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            if ($isAjax) {
                //Coupon was removed.
            } else {
                $this->_goBack();
                return;
            }
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    /* IF AMASTY PROMO IS INSTALLED */
                    if (Mage::helper('core')->isModuleEnabled('Amasty_Promo')):
                        $this->loadLayout();

                        $amastyMessage = $this->getLayout()->createBlock(
                            'ampromo/add',
                            'amasty_promo_message',
                            array('template' => 'amasty/ampromo/add.phtml')
                        )->toHtml();
                        $amastyMessageRemovedScript = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $amastyMessage);
                        $checkoutURL = Mage::helper('checkout/url')->getCartUrl();
                        $randParameter = rand(0, 1000);
                        $showFreeGiftsLink = $checkoutURL . "?refresh={$randParameter}#choose-gift";
                        $amastyReadyResponse = str_replace('href="#"', "href='{$showFreeGiftsLink}'", $amastyMessageRemovedScript);
                        $this->_getSession()->addSuccess($amastyReadyResponse);
                    endif;
                    /* END PROMO AMASTY*/
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                } else {
                    $details = "";
                    $couponModel = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                    //  die(json_encode($couponModel->getData()));
                    if (empty($couponModel->getData())):
                        $details = $this->__('This coupon code "%s" does not exist or has expired.', Mage::helper('core')->htmlEscape($couponCode));
                        $this->_getSession()->addError($details);
                        goto beginning_of_response;
                    endif;
                    /*if ($couponModel->getData("from_date")):
                        $details = $this->__('This coupon code "%s" does not exist or has expired.', Mage::helper('core')->htmlEscape($couponCode));
                        $this->_getSession()->addError($details);
                        goto beginning_of_response;
		     endif;
		     */
                    if ($couponModel->getExpirationDate()):
                        // echo "8";
                        $couponExpirationDate = $couponModel->getExpirationDate();
                        $couponExpires = new DateTime($couponExpirationDate);
                        $couponExpiresEndOfDay = $couponExpires->setTime(23, 59, 59);
                        $couponExpires = $couponExpiresEndOfDay;
                        $currentDateTime = new DateTime();
                        if ($couponExpires->getTimestamp() < $currentDateTime->getTimestamp()) :
                            $couponExpires->format("M d, YYYY");
                            $details = $this->__("This coupon expired on %s at midnight EST", $couponExpires->format("M d, Y"));
                            $this->_getSession()->addError($details);
                            goto beginning_of_response;
                        endif;
                    endif;
                    if ($couponModel->getUsageLimit() === $couponModel->getTimesUsed()):
                        $details = $this->__('Sorry, coupon code "%s" had limited availability and has been used by other customers.', Mage::helper('core')->htmlEscape($couponCode));
                        $this->_getSession()->addError($details);
                        goto beginning_of_response;
                    endif;
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.' . " " . $this->__('You may need to refresh the cart page to see changes.')));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $couponRuleId = Mage::getModel('salesrule/coupon')->load($couponCode, 'code')->getRuleId();
            $this->customCouponErrorToSession($couponRuleId);
            Mage::logException($e);
        }
        $couponWasApplied = $this->_getQuote()->getCouponCode();
        if (!$couponWasApplied):
            $couponRuleId = Mage::getModel('salesrule/coupon')->load($couponCode, 'code')->getRuleId();
            $this->customCouponErrorToSession($couponRuleId);
        endif;
        beginning_of_response:
        if ($isAjax) {
            $response['status'] = 0;
            $totals = $this->getLayout()->createBlock('checkout/cart_totals')->setTemplate('checkout/cart/totals.phtml')->toHtml();
            $response['cart_totals'] = $totals;
//					$response['messages'] = $block;
            $smessages = Mage::getSingleton('checkout/session')->getMessages(true)->getItems();
            $response['messages'] = NULL;
            foreach ($smessages as $smessage) {
                $response['messages'] .= $smessage->getText();
            }
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

        } else {
            $this->_goBack();
        }
    }
    private function customCouponErrorToSession($couponRuleId=null){
        if ($couponFailedLabel = Mage::getModel("etre_promomod/failedmessages")->getDefaultStoreLabel($couponRuleId)):
            $this->_getSession()->addError($couponFailedLabel);
        else:
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
        endif;
    }
}
