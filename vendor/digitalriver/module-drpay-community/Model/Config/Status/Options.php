<?php
/**
 * Catalog Sync Option Status
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Config\Status;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class for grid Status options
 */
class Options implements OptionSourceInterface
{
    /**
     * Sync statuses constants
     */
    const PENDING = 'PENDING';
    const FAIL = 'FAIL';
    const SUCCESS = 'SUCCESS';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::PENDING,
                'label' => self::PENDING
            ],
            [
                'value' => self::SUCCESS,
                'label' => self::SUCCESS
            ],
            [
                'value' => self::FAIL,
                'label' => self::FAIL
            ]
        ];
    }
}
