<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Controller\Index;

/**
 * Class Redirect
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        if ($url = $this->getRequest()->getParam('url')) {
            $this->getResponse()->setRedirect($url, 301)->sendHeaders();
        }
    }
}
