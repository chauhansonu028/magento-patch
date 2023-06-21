<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Index;

use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\ResourceModel\View\GetPostsViewsCountByPostsIds;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

class ViewList implements HttpPostActionInterface
{
    const POSTS_IDS = 'posts_ids';
    const VIEWS_COUNT_LIST = 'views_count_list';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultFactory;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var GetPostsViewsCountByPostsIds
     */
    private $getPostsViewsCountByPostsIds;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(
        RequestInterface $request,
        JsonFactory $resultFactory,
        Validator $validator,
        ResponseInterface $response,
        GetPostsViewsCountByPostsIds $getPostsViewsCountByPostsIds,
        Settings $settings
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->validator = $validator;
        $this->response = $response;
        $this->getPostsViewsCountByPostsIds = $getPostsViewsCountByPostsIds;
        $this->settings = $settings;
    }

    public function execute()
    {
        $result = $this->response;

        if ($this->validateRequest()) {
            $postIds = (array) $this->request->getParam(self::POSTS_IDS);
            $postIds = array_slice($postIds, 0, $this->settings->getPostsLimit());
            $postIds = array_map('intval', $postIds);
            $postViews = $this->getPostsViewsCountByPostsIds->execute($postIds);
            $result = $this->resultFactory->create();
            $result->setData([self::VIEWS_COUNT_LIST => $postViews]);
        }

        return $result;
    }

    private function validateRequest(): bool
    {
        $isValid = true;

        switch (true) {
            case !$this->request->isAjax() || !$this->validator->validate($this->request):
                $this->response->setStatusHeader(403, '1.1', 'Forbidden');
                $isValid = false;
                break;
            case $this->request->getParam(self::POSTS_IDS) === null:
                $this->response->setStatusHeader(400, '1.1', 'Bad request');
                $isValid = false;
                break;
        }

        return $isValid;
    }
}
