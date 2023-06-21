<?php
/**
 * Catalog Sync Cron Execute
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Jobs;

use Exception;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Digitalriver\DrPay\Logger\Logger as DrLogger;

/**
 *  Sends emails for the scheduled generation of the Catalog sync
 */
class EmailNotification
{
    /**
     * Enable/disable configuration
     */
    public const XML_PATH_GENERATION_ENABLED = 'dr_settings/catalog_sync/enabled';

    /**
     * Error email template configuration
     */
    public const XML_PATH_ERROR_TEMPLATE = 'dr_settings/catalog_sync/error_email_template';

    /**
     * Error email identity configuration
     */
    public const XML_PATH_ERROR_IDENTITY = 'dr_settings/catalog_sync/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    public const XML_PATH_ERROR_RECIPIENT = 'dr_settings/catalog_sync/error_email';

    /**
     * Error email configuration
     */
    public const XML_PATH_ERROR_NOTIFY = 'dr_settings/catalog_sync/error_notify';

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DrLogger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * EmailNotification constructor.
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DrLogger $logger
     */
    public function __construct(
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DrLogger $logger
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Send's error email if catalog sync generate errors.
     *
     * @param array $errors
     * @return void
     * @throws NoSuchEntityException
     */
    public function sendErrorsMail(array $errors): void
    {
        $this->inlineTranslation->suspend();
        // set from email
        $sender = $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $recipient = $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $iSEnabled = $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_NOTIFY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        if (!$iSEnabled || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        try {
            $this->transportBuilder->setTemplateIdentifier(
                $this->scopeConfig->getValue(
                    self::XML_PATH_ERROR_TEMPLATE,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['errors' => join(", ", $errors)]
            )->setFromByScope(
                $sender
            )->addTo(
                $recipient
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (Exception $exception) {
            $this->logger->error('Catalog Sync Errors: ' . $exception->getMessage());
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Get Current store id
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }
}
