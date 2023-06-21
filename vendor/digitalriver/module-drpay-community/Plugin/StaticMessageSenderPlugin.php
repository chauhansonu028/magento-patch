<?php declare(strict_types=1);

/**
 * This plugin add static message to email invoice/refund information
 *
 * @summary Add Static Message Plugin for emails
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Plugin;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class to add static message in invoice/cm emails
 */
class StaticMessageSenderPlugin
{
    const DEFAULT_MESSAGE = "These are not official Digital River Payments Documents";
    const CONFIG_STATIC_MESSAGE_PATH = 'dr_settings/config/dr_static_message';
    const DIGITAL_RIVER_PAYMENT_METHOD = 'drpay_dropin';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * AddStaticMessagePlugin constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Before plugin to include static message in Emails
     *
     * @param mixed $subject
     * @param mixed $object
     * @param bool $forceSyncMode
     * @return array
     */
    public function beforeSend($subject, $object, $forceSyncMode = false): array
    {
        try {
            $message = $this->scopeConfig->getValue(self::CONFIG_STATIC_MESSAGE_PATH, ScopeInterface::SCOPE_STORE);

            $staticMessage = ($message && trim($message) != "") ? $message : self::DEFAULT_MESSAGE;

            if ($this->isDigitalRiverPayment($subject, $object) === true) {
                $customerNote = $object->getCustomerNoteNotify() ? $object->getCustomerNote() : '';
                if ($customerNote && $customerNote != '') {
                    $staticMessage = $staticMessage . ' ' . $customerNote;
                }
                $object->setCustomerNoteNotify(true);
                $object->setCustomerNote($staticMessage);
            }

            return [$object, $forceSyncMode];
        } catch (Exception $exception) {
            return [$object, $forceSyncMode];
        }
    }

    /**
     * Checks if DR is the payment method for this order
     *
     * @param mixed $subject
     * @param mixed $object
     * @return bool
     */
    private function isDigitalRiverPayment($subject, $object): bool
    {
        if ($subject &&
            $object->getOrder() &&
            $object->getOrder()->getPayment() &&
            $object->getOrder()->getPayment()->getMethod() &&
            $object->getOrder()->getPayment()->getMethod() === self::DIGITAL_RIVER_PAYMENT_METHOD) {
            return true;
        }
        return false;
    }
}
