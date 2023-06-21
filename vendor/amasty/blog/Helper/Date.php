<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Helper;

/**
 * Class
 */
class Date extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const DATE_TIME_PASSED = 'passed';

    public const DATE_TIME_DIRECT = 'direct';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolverInterface;

    /**
     * @var Settings
     */
    private $helperSettings;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Locale\ResolverInterface $resolverInterface,
        \Amasty\Blog\Helper\Settings $helperSettings,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->timezoneInterface = $timezoneInterface;
        $this->resolverInterface = $resolverInterface;
        $this->helperSettings = $helperSettings;
        $this->date = $date;
    }

    /**
     * @param $datetime
     * @return string
     */
    public function renderTime($datetime)
    {
        $date = $this->timezoneInterface->formatDateTime(
            $datetime,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $this->resolverInterface->getLocale()
        );

        return $date;
    }

    /**
     * @param $date
     * @return bool
     */
    private function isToday($date)
    {
        $today = $nowDate = $this->date->gmtDate('Ymd');
        $day = $this->timezoneInterface->convertConfigTimeToUtc($date, 'Ymd');

        return $today == $day;
    }

    /**
     * @param $date
     * @return bool
     */
    private function isYesterday($date)
    {
        $today = $nowDate = $this->date->gmtDate('Ymd');
        $day = $this->timezoneInterface->convertConfigTimeToUtc($date, 'Ymd');

        return ($today - 1) == $day;
    }

    /**
     * @param $datetime
     * @param $forceDirect
     * @param $dateFormat
     * @param $isEditedAt
     * @return string
     */
    public function renderDate($datetime, $forceDirect = false, $dateFormat = false, $isEditedAt = false)
    {
        if (!$dateFormat) {
            $dateFormat = $this->helperSettings->getDateFormat();
        }

        if ($isEditedAt) {
            $dateFormat = $this->helperSettings->getEditedAtDateFormat();
        }

        if ($forceDirect || ($dateFormat == self::DATE_TIME_DIRECT)) {
            return $this->timezoneInterface->formatDateTime(
                $datetime,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE,
                $this->resolverInterface->getLocale()
            );
        } else {
            return $datetime;
        }
    }
}
