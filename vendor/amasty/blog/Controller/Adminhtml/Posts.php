<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Adminhtml;

use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Helper\Url;
use Amasty\Blog\Model\BlogRegistry;
use Amasty\Blog\Model\ImageProcessor;
use Amasty\Blog\Model\ResourceModel\Posts\RelatedProducts\PopulateRelatedProductsInfo;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

abstract class Posts extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var BlogRegistry
     */
    private $blogRegistry;

    /**
     * @var PopulateRelatedProductsInfo
     */
    private $populateRelatedProductsInfo;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Url $urlHelper,
        PostRepositoryInterface $postRepository,
        DataPersistorInterface $dataPersistor,
        BlogRegistry $blogRegistry,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        ImageProcessor $imageProcessor,
        PopulateRelatedProductsInfo $populateRelatedProductsInfo
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->urlHelper = $urlHelper;
        $this->postRepository = $postRepository;
        $this->dataPersistor = $dataPersistor;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->imageProcessor = $imageProcessor;
        $this->blogRegistry = $blogRegistry;
        $this->populateRelatedProductsInfo = $populateRelatedProductsInfo;
    }

    /**
     * @return ImageProcessor
     */
    public function getImageProcessor()
    {
        return $this->imageProcessor;
    }

    public function getRelatedProductsInfoLoader(): PopulateRelatedProductsInfo
    {
        return $this->populateRelatedProductsInfo;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Blog::posts');
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return DataPersistorInterface
     */
    public function getDataPersistor()
    {
        return $this->dataPersistor;
    }

    /**
     * @return BlogRegistry
     */
    public function getRegistry()
    {
        return $this->blogRegistry;
    }

    /**
     * @return Url
     */
    public function getUrlHelper()
    {
        return $this->urlHelper;
    }

    /**
     * @return PostRepositoryInterface
     */
    public function getPostRepository()
    {
        return $this->postRepository;
    }

    /**
     * @return PageFactory
     */
    public function getPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @return TimezoneInterface
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}
