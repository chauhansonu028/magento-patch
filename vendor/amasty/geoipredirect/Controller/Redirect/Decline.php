<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Controller\Redirect;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;

class Decline extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(Context $context, SessionManagerInterface $sessionManager)
    {
        parent::__construct($context);
        $this->sessionManager = $sessionManager;
    }

    public function execute()
    {
        $this->sessionManager->setNeedShow(false);
        $this->sessionManager->setWillRedirect(false);
    }
}
