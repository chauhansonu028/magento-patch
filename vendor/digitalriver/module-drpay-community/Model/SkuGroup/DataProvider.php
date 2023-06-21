<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\SkuGroup;

use Magento\Framework\Message\ManagerInterface;
use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Api\SkuGroupApiClientInterface;
use Digitalriver\DrPay\Api\SkuGroupProviderInterface;

/**
 * Class DataProvider
 *
 * Provides SKU groups directly from the API client
 */
class DataProvider implements SkuGroupProviderInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SkuGroupApiClientInterface
     */
    private $skuGroupApi;

    /**
     * DataProvider constructor.
     * @param SkuGroupApiClientInterface $skuGroupApi
     */
    public function __construct(
        ManagerInterface $messageManager,
        Config $config,
        SkuGroupApiClientInterface $skuGroupApi
        )
    {
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->skuGroupApi = $skuGroupApi;
    }

    /**
     * Returns a list of all available sku groups
     *
     * @return array
     */
    public function getSkuGroups(): array
    {
        $startingAfter = null;
        $skuGroups = [];
        $hasMore = false;

        if ($this->config->getIsEnabled() 
            && (strlen($this->config->getPublicKey()) > 0) 
            && (strlen($this->config->getSecretKey()) > 0)) {
                do {
                    $groupChunk = $this->skuGroupApi->getSkuGroups($startingAfter);
                    if (!isset($groupChunk['error'])) {
                        $hasMore = $groupChunk['hasMore'];
                        $skuGroups = array_merge($groupChunk['data'], $skuGroups);
                        $startingAfter = end($skuGroups);
                    } else {
                        $this->messageManager->addError(_($groupChunk['message']));
                    }
                } while ($hasMore === 'true');
        }

        return $skuGroups;
    }
}
