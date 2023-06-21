<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\AbstractController;

use Magento\Framework\App\Action\Action;

class Redirect extends Action
{
    public function execute()
    {
        $url = $this->getRequest()->getParam('url');
        if ($url) {
            $this->getResponse()->setRedirect($url, 301)->sendHeaders();
        }
    }
}
