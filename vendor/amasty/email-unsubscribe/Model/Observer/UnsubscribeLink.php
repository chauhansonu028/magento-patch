<?php

declare(strict_types=1);

namespace Amasty\EmailUnsubscribe\Model\Observer;

use Amasty\EmailUnsubscribe\Model\ResourceModel\UnsubscribeType;
use Amasty\EmailUnsubscribe\Model\UrlHash;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface as Url;

class UnsubscribeLink implements ObserverInterface
{
    public const UNSUBSCRIBE_URL = 'emailunsubscribe/unsubscribe';

    /**
     * @var Url
     */
    private $url;

    /**
     * @var UrlHash
     */
    private $urlHash;

    public function __construct(
        Url $url,
        UrlHash $urlHash
    ) {
        $this->url = $url;
        $this->urlHash = $urlHash;
    }

    public function execute(Observer $observer): void
    {
        $transportObject = $observer->getData('transport_object');
        $email = $transportObject->getData('email');
        $type = $transportObject->getData(UnsubscribeType::TYPE);

        $link = $this->url->getUrl(
            self::UNSUBSCRIBE_URL,
            [
                'type' => $type,
                'email' => $email,
                'hash' => $this->urlHash->getHash($type, $email)
            ]
        );

        $transportObject->setData('link', $link);
    }
}
