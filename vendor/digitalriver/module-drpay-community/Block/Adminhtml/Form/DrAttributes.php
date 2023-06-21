<?php
/**
 * Provides DR Attributes options export page
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Adminhtml\Form;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Model\ResourceModel\TaxTypes\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Get collection of DR custom attributes from custom table.
 * Class DrAttributes
 */
class DrAttributes extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $taxTypes;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Config $config,
        Context $context,
        CollectionFactory $taxTypes,
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->config = $config;
        $this->taxTypes = $taxTypes;
        parent::__construct($context, $data);
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
     * Retrieve json
     *
     * @return string
     */
    public function getTaxValues(): string
    {
        $dataCollection = $this->taxTypes->create()->getData();
        return $this->jsonSerializer->serialize($dataCollection);
    }
}
