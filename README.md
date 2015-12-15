# Magento-CustomCouponMessages
This module allows for adding custom error messages per coupon/cart rule.

The flow is as follows:

1. Check to see if coupon exists - static response: `$details = $this->__('This coupon code "%s" does not exist or has expired.', Mage::helper('core')->htmlEscape($couponCode));`
2. Check if coupon expired - static response: `$details = $this->__("This coupon expired on %s at midnight EST", $couponExpires->format("M d, Y"));`
3. Check if usage limit has been reached - static response: `$details = $this->__('Sorry, coupon code "%s" had limited availability and has been used by other customers.', Mage::helper('core')->htmlEscape($couponCode));`
4. If after the above checks the code still has not been applied, assume the conditional rules failed.
 - If code has custom response: `$couponFailedLabel = Mage::getModel("etre_promomod/failedmessages")->getDefaultStoreLabel($couponRuleId)`
 - Otherwise: `$this->_getSession()->addError($this->__('Cannot apply the coupon code.'));`

Example:

![Preview of conditions](https://i.imgur.com/outudse.jpg "Magento Custom Coupon Messages")

![Preview of messages](https://i.imgur.com/p6Faydb.jpg "Magento Custom Coupon Errors")




