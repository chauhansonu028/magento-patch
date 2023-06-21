<?php
/**
 * Used for rendering Attribute details template in product edit page
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Block\Adminhtml\Product;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Model\ResourceModel\EccnCode\CollectionFactory as EccnCodeCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Eccn Attribute Details Block
 */
class EccnDetailsBlock extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'eccn_details_tab.phtml';

    /**
     * @var EccnCodeCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Eccn constructor.
     * @param Context $context
     * @param EccnCodeCollectionFactory $collectionFactory
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        EccnCodeCollectionFactory $collectionFactory,
        Json $jsonSerializer,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->config = $config;
    }

    /**
     * @return Json
     */
    public function getJsonSerializer(): Json
    {
        return $this->jsonSerializer;
    }

    /**
     * @return bool
     */
    public function getIsDrEnabled(): bool
    {
        return (bool)$this->config->getIsEnabled();
    }

    /**
     * returns ECCN attribute details
     *
     * @return array
     */
    public function getEccnDetails(): array
    {
        $eccnCode = $this->collectionFactory->create();
        $eccnData = $eccnCode->getData();

        $options = [];
        foreach ($eccnData as $item) {
            $options []  =
                [
                    'classification_code' => $item['classification_code'],
                    'description' => $item['description'],
                    'notes' => $item['notes']
                ];
        }
        return $options;
    }
}
