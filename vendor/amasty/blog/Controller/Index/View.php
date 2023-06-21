<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Index;

use Amasty\Blog\Api\ViewRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

class View implements HttpGetActionInterface
{
    const POST_ID = 'post_id';
    const VIEWS_COUNT = 'views_count';

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
     * @var ViewRepositoryInterface
     */
    private $viewRepository;

    public function __construct(
        RequestInterface $request,
        JsonFactory $resultFactory,
        Validator $validator,
        ResponseInterface $response,
        ViewRepositoryInterface $viewRepository
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->validator = $validator;
        $this->response = $response;
        $this->viewRepository = $viewRepository;
    }

    public function execute()
    {
        $result = $this->response;

        if ($this->validateRequest()) {
            $postId = (int) $this->request->getParam(self::POST_ID);
            $this->viewRepository->create($postId);
            $viewsCount = $this->viewRepository->getViewCountByPostId($postId);
            $result = $this->resultFactory->create();
            $result->setData([self::VIEWS_COUNT => $viewsCount]);
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
            case $this->request->getParam(self::POST_ID) === null:
                $this->response->setStatusHeader(400, '1.1', 'Bad request');
                $isValid = false;
                break;
        }

        return $isValid;
    }
}
