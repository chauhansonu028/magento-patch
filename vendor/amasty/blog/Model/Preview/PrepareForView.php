<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Preview;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Model\Source\PostStatus;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PrepareForView
{
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    public function execute(PostInterface $model): void
    {
        $this->prepareImage($model);
        $this->prepareStatus($model);
    }

    private function prepareStatus(PostInterface $model): void
    {
        $model->setStatus(PostStatus::STATUS_ENABLED);

        if (empty($model->getPublishedAt())) {
            $currentTimestamp = $this->dateTime->gmtTimestamp();
            $convertedDate = $this->dateTime->date('Y-m-d H:i:s', $currentTimestamp);
        } else {
            $convertedDate = $this->dateTime->date('Y-m-d H:i:s', strtotime($model->getPublishedAt()));
        }

        $model->setPublishedAt($convertedDate);
    }

    private function prepareImage(PostInterface $model): void
    {
        $fileName = PostInterface::POST_THUMBNAIL . '_file';
        $thumbnail = $model->getData($fileName);

        if (isset($thumbnail) && is_array($thumbnail)) {
            if (isset($thumbnail[0]['name']) && isset($thumbnail[0]['tmp_name'])) {
                $model->setData(PostInterface::POST_THUMBNAIL, 'tmp/' . $thumbnail[0]['name']);
            }
        }
    }
}
