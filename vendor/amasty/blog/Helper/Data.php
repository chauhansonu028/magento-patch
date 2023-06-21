<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const ESTIMATED_WORDS_PER_MINUTE = 230;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->request = $context->getRequest();
    }

    /**
     * @return array
     */
    public function getSocialNetworks()
    {
        return explode(
            ",",
            $this->scopeConfig->getValue(
                'amblog/social/networks',
                ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * @deprecated
     * @see \Amasty\Blog\Model\ConfigProvider::isAmpEnabled
     * @return bool
     */
    public function isAmpEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'amblog/accelerated_mobile_pages/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isCurrentPageAmp()
    {
        return $this->isAmpEnable() && strpos($this->request->getFullActionName(), '_amp_') !== false;
    }

    /**
     * @param $data
     * @param null $allowableTags
     * @param bool $allowHtmlEntities
     * @return array|string|string[]|null
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        $result = strip_tags($data, $allowableTags);

        return $allowHtmlEntities ? $result : $this->escapeHtml($result, $allowableTags);
    }

    /**
     * @param $data
     * @param null $allowedTags
     * @return array|string|string[]|null
     */
    private function escapeHtml($data, $allowedTags = null)
    {
        $result = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            $result = $this->processSingleItem($data, $allowedTags);
        }

        return $result;
    }

    /**
     * @param $data
     * @param $allowedTags
     * @return string|string[]|null
     */
    private function processSingleItem($data, $allowedTags)
    {
        if ($data) {
            if (is_array($allowedTags) && !empty($allowedTags)) {
                $allowed = implode('|', $allowedTags);
                $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                $result = $this->htmlSpecialChars($result);
                $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
            } else {
                $result = $this->htmlSpecialChars($data);
            }
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * @param $result
     *
     * @return string
     */
    protected function htmlSpecialChars($result)
    {
        //@codingStandardsIgnoreStart
        return htmlspecialchars($result, ENT_COMPAT, 'UTF-8', false);
        //@codingStandardsIgnoreEnd
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    public function getReadTime(string $content): int
    {
        $content = $this->stripTags($content);
        $content = str_word_count($content);
        $readTime = (int)ceil($content / self::ESTIMATED_WORDS_PER_MINUTE);

        return $readTime === 0 ? 1 : $readTime;
    }
}
