<?php
/**
 * Fetches Digital River ECCN details
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\Adminhtml\Eccncode;

use Digitalriver\DrPay\Helper\Config;
use Digitalriver\DrPay\Model\ResourceModel\EccnCode\CollectionFactory as EccnCodeCollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Eccn extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var EccnCodeCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var config
     */
    private $config;

    /**
     * Eccn constructor.
     * @param Context $context
     * @param EccnCodeCollectionFactory $collectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        EccnCodeCollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory,
        Config $config
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
    }

    /**
     * Returns Digital River ECCN details
     *
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        $eccnCode = $this->collectionFactory->create();
        $eccnData = $eccnCode->getData();

        $options = [];
        if ($this->config->getIsEnabled()) {
            foreach ($eccnData as $item) {
                $options []  =
                    [
                        'classification_code' => $item['classification_code'],
                        'description' => $item['description'],
                        'notes' => $item['notes']
                    ];
            }
        }

        return $result->setData($options);
    }
}
