<?php
/**
 * Provides DR quote billing address details in checkout page
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Controller\Quotebilling;

use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;

class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $resultJsonFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        CollectionFactory $collectionFactory,
        Region $region,
        RegionFactory $regionFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->region = $region;
        $this->regionFactory = $regionFactory;
        return parent::__construct($context);
    }

    /**
     * Returns Dr quote billing in checout page
     *
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();
        $regionId = $this->getRequest()->getParam('region_id');

        $regionModel = $this->regionFactory->create();
        $this->region->load($regionModel, $regionId);

        $options []  =
            [
                'region_code' => $regionModel->getCode(),
            ];
        return $result->setData($options);
    }
}
