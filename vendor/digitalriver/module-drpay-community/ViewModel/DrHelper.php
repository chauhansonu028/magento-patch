<?php

namespace Digitalriver\DrPay\ViewModel;

use Digitalriver\DrPay\Helper\Config;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class DrHelper implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Config $config,
        Json $jsonSerializer
    ) {
        $this->config = $config;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return bool
     */
    public function getIsDrEnabled(): bool
    {
        return (bool)$this->config->getIsEnabled();
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getJsonSerializer()
    {
        return $this->jsonSerializer;
    }
}
