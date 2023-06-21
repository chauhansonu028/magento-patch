# 2.1.2

 * Bug Fixes:
    * Drop-in frame not displayed for checkout with virtual products (fixed in [#17](https://github.com/DRMagento/Digitalriver/pull/17))
    * Shopping cart not getting cleared after a purchase (fixed in [#7](https://github.com/DRMagento/Digitalriver/pull/7))
    * Billing Address is updated in order review after payment through PayPal (fixed in [#16](https://github.com/DRMagento/Digitalriver/pull/16))

# 2.2.0

 * Payment Labeling:
    * Added translations for Labeling of Payment type (fixed in [#5](https://github.com/DRMagento/Digitalriver/pull/5))
    * Added Payment method info and Credit Card info for DR Payments in following pages:
        * Customer Dashboard - Order Detail
        * Customer Dashboard - Invoice
        * Customer Dashboard - Shipment
        * Admin - Order Detail
        * Admin - Invoice
        * Admin - Shipment (fixed in [#10](https://github.com/DRMagento/Digitalriver/pull/10))
    * Displayed Payment details in Order review step of checkout (fixed in [#15](https://github.com/DRMagento/Digitalriver/pull/15))
    * The label `Pay Pal` is changed to `PayPal` (fixed in [#55](https://github.com/DRMagento/Digitalriver/pull/55))
    * Bug fixes for the above changes

 * Invoice and Credit Memo:
    * Added a file-link creation process that stores file-links of invoice/refund details stored in Digital River server (fixed in [#24](https://github.com/DRMagento/Digitalriver/pull/24))
    * Added a webhook that triggers file-link creation for invoice/refund (fixed in [#24](https://github.com/DRMagento/Digitalriver/pull/24))
    * Replaced Invoice details page in storefront with file-links from Digital River (fixed in [#18](https://github.com/DRMagento/Digitalriver/pull/18))
    * Replaced Credit Memo details page in storefront with file-links from Digital River (fixed in [#20](https://github.com/DRMagento/Digitalriver/pull/20))
    * Added a configurable notice message in Admin for Invoice and Credit Memo details page (fixed in [#21](https://github.com/DRMagento/Digitalriver/pull/21))
    * Added a new section in Order Details page of Admin to display file-links from Digital River (fixed in [#42](https://github.com/DRMagento/Digitalriver/pull/42))
    * Added the configurable static message in OOB Magento Invoice/refund emails and printouts (fixed in [#43](https://github.com/DRMagento/Digitalriver/pull/43))
    * Bug fixes for the above changes

 * Global Tax ID feature:
    * Added atomic operations for TaxID creation and Assigning TaxID to checkout (fixed in [#25](https://github.com/DRMagento/Digitalriver/pull/25))
    * Added the feature to change customer type and Assigning TaxID to checkout (fixed in [#27](https://github.com/DRMagento/Digitalriver/pull/27))
    * Added the feature to reload order totals after applying TaxID successfully (fixed in [#41](https://github.com/DRMagento/Digitalriver/pull/41))
    * Added an option to declare whether a purchase is business purchase or not (fixed in [#44](https://github.com/DRMagento/Digitalriver/pull/44))
    * Updated Digital River drop-in to be viable for billing address change (fixed in [#46](https://github.com/DRMagento/Digitalriver/pull/46))
    * Added the feature to clear TaxID after changing billing address during a purchase (fixed in [#54](https://github.com/DRMagento/Digitalriver/pull/54))

# 2.3.0
* Added support for:
  * U.S. Tax Certificate Management
  * Regulatory Fees (Tax Exclusive Pricing Only)
  * Magento Static Bundles 
  * Magento Open Source

# 2.3.1
* Added support for:
  * Stored Payment Methods Management

# 2.4.0
* Added support for:
  * Guaranteed Landed Cost
  * Redirect Payment Method Order Flow Changes
  * SCA Challenge Order Flow Changes
  * Digital River Tax Estimates in Cart
  * Wire Transfer Refunds
  * Zero Dollar Checkout Support
  * Configurable Compliance Links

# 3.0.0
* Added support for:
  * Magento 2.4.4
  * PHP8

# 3.1.0
* Added support for:
  * Taiwan eGUI

# 3.1.1
* Addressed defects related to Taiwan eGUI
* Added support for Magento 2.4.5

# 3.1.2
* Added Multi Origin Shipping
* Redirect Payment Method Updates

# 3.1.3
* Added Multiple DR API Key Support
* Compatibility Updates for Non-DR Storefronts
* Support Item Level Refund
* Remove Tax Id Check List 
