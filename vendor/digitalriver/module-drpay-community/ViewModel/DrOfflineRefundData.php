<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\ViewModel;

use Digitalriver\DrPay\Api\Data\OfflineRefundInterface;
use Digitalriver\DrPay\Model\DropIn\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Digitalriver\DrPay\Api\OfflineRefundRepositoryInterface;

/**
 * View model to display additional data for
 * DR.js refund
 */
class DrOfflineRefundData implements ArgumentInterface
{
    /** @var UrlInterface  */
    private UrlInterface $urlInterface;

    /** @var ConfigProvider  */
    private ConfigProvider $configProvider;

    /** @var StoreManagerInterface  */
    private StoreManagerInterface $storeManager;

    /** @var OfflineRefundRepositoryInterface  */
    private OfflineRefundRepositoryInterface $offlineRefundRepository;

    /**
     * @param UrlInterface $urlInterface
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        UrlInterface $urlInterface,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        OfflineRefundRepositoryInterface $offlineRefundRepository
    ) {
        $this->configProvider = $configProvider;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->offlineRefundRepository = $offlineRefundRepository;
    }

    /**
     * @return bool
     */
    public function getIsDrEnabled(): bool
    {
        return !empty($this->configProvider->getConfig()['payment']['drpay_dropin']['is_active']) ?
            (bool) $this->configProvider->getConfig()['payment']['drpay_dropin']['is_active'] :
            false;
    }

    /**
     * @return string|null
     */
    public function getPublicKey():? string
    {
        return !empty($this->configProvider->getConfig()['payment']['drpay_dropin']['public_key']) ?
            (string) $this->configProvider->getConfig()['payment']['drpay_dropin']['public_key'] :
            null;
    }

    /**
     * @return string|null
     */
    public function getLocale():? string
    {
        return !empty($this->configProvider->getConfig()['payment']['drpay_dropin']['mage_locale']) ?
            (string) $this->configProvider->getConfig()['payment']['drpay_dropin']['mage_locale'] :
            null;
    }

    /**
     * @param string $drRefundId
     * @param string $status
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRefundEndpoint(string $drRefundId, string $status = 'complete'): string
    {
        $code = $this->storeManager->getStore()->getCode();
        $baseUrl = rtrim($this->urlInterface->getBaseUrl(), '/');

        return sprintf(
            '%s/rest/%s/V1/offline-refunds/%s/%s',
            $baseUrl,
            $code,
            $drRefundId,
            $status
        );
    }

    /**
     * @param $creditMemoId
     * @return OfflineRefundInterface|false
     */
    public function getCreditMemoRefundEntity($creditMemoId): ?OfflineRefundInterface
    {
        try {
            return $this->offlineRefundRepository->getByCreditmemoId((int)$creditMemoId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
