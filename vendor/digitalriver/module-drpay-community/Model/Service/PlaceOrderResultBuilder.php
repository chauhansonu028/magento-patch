<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Service;

use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterface;
use Digitalriver\DrPay\Api\Data\PlaceOrderResultInterfaceFactory;
use Digitalriver\DrPay\Api\PlaceOrderResultBuilderInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Validator\Url;
use Magento\Framework\Webapi\Response;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class PlaceOrderResultInitializer
 *
 * Builds PlaceOrderResultInterface with API data
 */
class PlaceOrderResultBuilder implements PlaceOrderResultBuilderInterface
{
    private const STATUS_PENDING_PAYMENT = 'pending_payment';
    private const STATUS_PENDING_REDIRECT = 'pending_redirect';
    private const ARRAY_PATH_SESSION_STATE = 'payment/session/state';
    private const ARRAY_PATH_REDIRECT_URL = 'payment/session/nextAction/data/redirectUrl';
    private const ARRAY_PATH_STATE = 'state';
    /**
     * @var PlaceOrderResultInterfaceFactory
     */
    private $placeOrderResultFactory;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var Url
     */
    private $urlValidator;
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * PlaceOrderResultInitializer constructor.
     * @param PlaceOrderResultInterfaceFactory $placeOrderResultFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Url $urlValidator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        PlaceOrderResultInterfaceFactory $placeOrderResultFactory,
        DataObjectHelper $dataObjectHelper,
        Url $urlValidator,
        ArrayManager $arrayManager
    ) {
        $this->placeOrderResultFactory = $placeOrderResultFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->urlValidator = $urlValidator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param OrderInterface $order
     * @param array $apiResult
     * @return PlaceOrderResultInterface
     * @throws LocalizedException
     */
    public function buildSuccessResult(OrderInterface $order, array $apiResult): PlaceOrderResultInterface
    {
        $result = $this->placeOrderResultFactory->create();
        $data = [
            PlaceOrderResultInterface::FIELD_STATUS => Response::HTTP_OK,
            PlaceOrderResultInterface::FIELD_CODE => PlaceOrderResultInterface::CODE_OK,
            PlaceOrderResultInterface::FIELD_ORDER_ID => $order->getEntityId(),
            PlaceOrderResultInterface::FIELD_ORDER_INCREMENT_ID => $order->getIncrementId(),
        ];
        if ($this->isStatusPending($apiResult)) {
            $data = $this->addRedirect($data, $apiResult);
        }
        $this->dataObjectHelper->populateWithArray($result, $data, PlaceOrderResultInterface::class);
        return $result;
    }

    /**
     * @param int $statusCode
     * @param string $fieldCode
     * @return PlaceOrderResultInterface
     */
    public function buildFailedResult(int $statusCode, string $fieldCode): PlaceOrderResultInterface
    {
        $result = $this->placeOrderResultFactory->create();
        $data = [
            PlaceOrderResultInterface::FIELD_STATUS => $statusCode,
            PlaceOrderResultInterface::FIELD_CODE => $fieldCode,
        ];
        $this->dataObjectHelper->populateWithArray($result, $data, PlaceOrderResultInterface::class);
        return $result;
    }

    /**
     * @param array $data
     * @param array $apiResult
     * @return array
     * @throws LocalizedException
     */
    private function addRedirect(array $data, array $apiResult): array
    {
        $redirectURL = $this->getRedirect($apiResult);
        if (!$this->urlValidator->isValid($redirectURL)) {
            throw new LocalizedException(__('Invalid redirect URL: %1', $redirectURL));
        }
        if ($redirectURL !== null) {
            $data[PlaceOrderResultInterface::FIELD_REDIRECT_URL] = $redirectURL;
        }
        return $data;
    }

    /**
     * @param array $apiResult
     * @return bool
     */
    private function isStatusPending(array $apiResult): bool
    {
        return (self::STATUS_PENDING_PAYMENT == $this->arrayManager->get(self::ARRAY_PATH_STATE, $apiResult)) &&
            (self::STATUS_PENDING_REDIRECT == $this->arrayManager->get(self::ARRAY_PATH_SESSION_STATE, $apiResult));
    }

    /**
     * @param array $apiResult
     * @return string|null
     */
    private function getRedirect(array $apiResult): ?string
    {
        return $this->arrayManager->get(self::ARRAY_PATH_REDIRECT_URL, $apiResult, null);
    }
}
