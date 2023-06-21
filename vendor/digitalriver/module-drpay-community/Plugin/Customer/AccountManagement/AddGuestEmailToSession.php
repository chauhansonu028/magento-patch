<?php

declare(strict_types=1);

namespace Digitalriver\DrPay\Plugin\Customer\AccountManagement;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AccountManagementInterface;

/**
 * Class AddGuestEmailToSession
 *
 * Whenever a guest email is verified against existing customers, it is set on the checkout session
 **/
class AddGuestEmailToSession
{
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * AddGuestEmailToSession constructor.
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param AccountManagementInterface $subject
     * @param string $customerEmail
     * @param int|null $websiteId
     */
    public function beforeIsEmailAvailable(
        AccountManagementInterface $subject,
        string $customerEmail,
        ?int $websiteId = null
    ): void {
        $this->checkoutSession->setGuestCustomerEmail($customerEmail);
    }
}
