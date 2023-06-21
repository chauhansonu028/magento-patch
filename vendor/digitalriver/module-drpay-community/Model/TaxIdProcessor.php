<?php
/**
 * TaxID management
 *
 * @summary
 * Manages DR Tax IDs
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 *
 * @category Digitalriver
 * @package Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Exception\DrPayException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdMaskFactory;

class TaxIdProcessor
{
    const ERROR_CODE_TAX_ID_NOT_VALID = 1;
    const ERROR_CODE_GENERAL_DR_FAILURE = 2;
    const ERROR_CODE_CUSTOMER_TYPE_NOT_VALID = 3;

    const DR_TAX_ID_STATUS_NOT_VALID = 'not_valid';
    const DR_TAX_ID_STATUS_PENDING = 'pending';
    const DR_TAX_ID_STATUS_VERIFIED = 'verfied';

    const DR_CUSTOMER_TYPES = [
        'business',
        'individual'
    ];

    /**
     * DrApi helper
     *
     * @var \Digitalriver\DrPay\Helper\Data
     */
    protected $helper;

    /**
     * Checkout session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * Logger
     *
     * @var \Digitalriver\DrPay\Logger\Logger
     */
    protected $logger;

    /**
     * Action context.
     *
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * Quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Digitalriver\DrPay\Logger\Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Digitalriver\DrPay\Helper\Data $helper,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->logger = $logger;
        $this->helper =  $helper;
        $this->checkoutSession = $checkoutSession;
        $this->context = $context;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteRepository = $quoteRepository;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Assigns Tax ID to the DR Checkout
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quote
     * @param string $customerType
     * @param \Digitalriver\DrPay\Api\Data\TaxIdInterface[] $taxIdentifiers
     * @return bool
     * @throws DrPayException
     * @throws LocalizedException
     */
    public function assignTaxIds($quote, $customerType, $taxIdentifiers)
    {
        try {
            if (!$customerType) {
                throw new InvalidArgumentException(
                    __('Missing customerType argument'),
                    null,
                    self::ERROR_CODE_CUSTOMER_TYPE_NOT_VALID
                );
            }

            if (!in_array($customerType, self::DR_CUSTOMER_TYPES)) {
                throw new InvalidArgumentException(
                    __('Invalid type argument'),
                    null,
                    self::ERROR_CODE_CUSTOMER_TYPE_NOT_VALID
                );
            }

            $checkoutId = $this->checkoutSession->getDrCheckoutId();

            if (empty($checkoutId)) {
                throw new NoSuchEntityException(
                    __('Missing DR Checkout Id'),
                    null,
                    self::ERROR_CODE_GENERAL_DR_FAILURE
                );
            }

            $createdTaxIds= [];

            foreach ($taxIdentifiers as $taxIdentifier) {
                $createdTaxId = $this->helper->createTaxId($taxIdentifier->getType(), $taxIdentifier->getValue());
                $createdTaxIds[] = ['id' => $createdTaxId['id']];
            }

            $quote->setDrTaxIdentifiers($this->jsonSerializer->serialize($createdTaxIds));
            $quote->setDrCustomerType($customerType);

            $this->quoteRepository->save($quote);

            return true;
        } catch (DrPayException $e) {
            $this->logger->error('Error: ' . __FUNCTION__ . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(
                __('Something went wrong'),
                $e,
                self::ERROR_CODE_GENERAL_DR_FAILURE
            );
        }
    }

    /**
     * Clears TaxIds from the quote
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quote
     * @return bool
     * @throws LocalizedException
     */
    public function clearTaxIds($quote)
    {
        try {

            // only set the values if there is something already set
            if ($quote->getDrTaxIdentifiers() === null) {
                return false;
            }

            $quote->setDrTaxIdentifiers(null);
            $quote->setDrCustomerType(null);

            $this->quoteRepository->save($quote);

            // force new checkout or checkout update
            $this->checkoutSession->unsSessionCheckSum();

            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(
                __('Something went wrong'),
                $e,
                self::ERROR_CODE_GENERAL_DR_FAILURE
            );
        }
    }

    protected function getQuote(string $cartId)
    {
        if ($this->checkoutSession->isLoggedIn()) {
            return $this->quoteRepository->getActive($cartId);
        } else {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            return $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        }
    }
}
