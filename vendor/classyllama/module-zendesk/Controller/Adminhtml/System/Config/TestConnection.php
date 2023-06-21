<?php

namespace Zendesk\Zendesk\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\ScopeHelper;

class TestConnection extends \Magento\Backend\App\Action
{
    /**
     * ACL resource ID
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Zendesk_Zendesk::zendesk';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var StripTags
     */
    protected $tagFilter;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * TestConnection constructor.
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Api $apiHelper
     * @param StripTags $tagFilter
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        Api $apiHelper,
        StripTags $tagFilter,
        ScopeHelper $scopeHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->apiHelper = $apiHelper;
        $this->tagFilter = $tagFilter;
        $this->scopeHelper = $scopeHelper;
    }

    /**
     * Check for connection to server
     *
     * @return Json
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        try {
            list($scopeType, $scopeId) = $this->scopeHelper->getScope();
            $this->apiHelper->tryAuthenticate($scopeType, $scopeId);

            // Success! :partyparrot:

            $result['success'] = true;
        } catch (\Zendesk\API\Exceptions\AuthException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
