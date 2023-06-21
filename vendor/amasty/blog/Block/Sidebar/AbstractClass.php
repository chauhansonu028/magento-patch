<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Sidebar;

use Amasty\Blog\Helper\Data;
use Amasty\Blog\Helper\Date;
use Amasty\Blog\Helper\Settings;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class AbstractClass extends \Amasty\Blog\Block\Layout\AbstractClass implements BlockInterface
{
    /**
     * @var Settings
     */
    private $settingsHelper;

    /**
     * @var Date
     */
    private $dateHelper;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var null
     */
    private $ampTemplate = null;

    /**
     * Route to get configuration
     *
     * @var string
     */
    private $route = 'abstract';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Settings $settingsHelper,
        Date $dateHelper,
        Data $dataHelper,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingsHelper = $settingsHelper;
        $this->dateHelper = $dateHelper;
        $this->dataHelper = $dataHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @return \Amasty\Blog\Block\Layout\AbstractClass
     */
    public function setAmpTemplate()
    {
        return parent::setTemplate($this->ampTemplate);
    }

    /**
     * @param string
     */
    public function addAmpTemplate($template)
    {
        $this->ampTemplate = $template;
    }

    /**
     * Wrap only for using on magento pages
     * @return bool
     */
    public function isAlreadyWrapped()
    {
        if (!$this->hasData('already_wrapped')) {
            $this->setData('already_wrapped', !$this->getIsWidget());
        }

        return $this->getData('already_wrapped');
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool $allowHtmlEntities
     *
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        return $this->dataHelper->stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    /**
     * @param $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @param $collection
     * @return $this
     */
    protected function checkCategory($collection)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getColorClass()
    {
        return $this->settingsHelper->getIconColorClass();
    }

    /**
     * HTML to text without new lines
     *
     * @param string $content
     *
     * @return string
     */
    private function htmlToPlainText($content)
    {
        $content = $this->sanitize($content);
        $content = str_replace(["\n", "\r"], ' ', $content);

        return $content;
    }

    /**
     * @param $string
     *
     * @return string
     */
    private function sanitize($string)
    {
        $string = str_replace("</p>", "</p> ", $string);
        $string = strip_tags($string);
        $string = htmlspecialchars_decode($string);
        $string = urldecode($string);
        $string = trim($string);

        return $string;
    }

    /**
     * @param $content
     * @return string
     */
    public function getStrippedContent($content)
    {
        $limit = $this->getShortContentLimit();
        $content = $this->htmlToPlainText($content);

        if (mb_strlen($content) > $limit) {
            $content = mb_substr($content, 0, $limit);
            if (mb_strpos($content, " ") !== false) {
                $cuts = explode(" ", $content);
                if (!empty($cuts) && count($cuts) > 1) {
                    unset($cuts[count($cuts) - 1]);
                    $content = implode(" ", $cuts);
                }
            }

            $content .= "...";
        }

        return $content;
    }

    /**
     * @return int
     */
    protected function getShortContentLimit()
    {
        return $this->settingsHelper->getRecentPostsShortLimit();
    }

    /**
     * Prepare widget collection
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    public function preparePostCollection($collection)
    {
        if ($this->hasData('amasty_widget_categories')) {
            $widgetCategories = $this->getData('amasty_widget_categories');
            if ($widgetCategories) {
                if (is_string($widgetCategories)) {
                    $widgetCategories = explode(',', $widgetCategories);
                }
                $collection->addCategoryFilter($widgetCategories);
            }
        }

        if ($this->hasData('amasty_widget_tags')) {
            $widgetTags = $this->getData('amasty_widget_tags');
            if ($widgetTags) {
                if (is_string($widgetTags)) {
                    $widgetTags = explode(',', $widgetTags);
                }
                $collection->addTagFilter($widgetTags);
            }
        }
    }

    /**
     * @param $datetime
     * @param $isEditedAt
     * @return string
     */
    public function renderDate($datetime, $isEditedAt = false)
    {
        return $this->hasData('date_manner')
            ? $this->dateHelper->renderDate($datetime, false, $this->getData('date_manner'), $isEditedAt)
            : $this->dateHelper->renderDate($datetime, false, false, $isEditedAt);
    }

    /**
     * @return Settings
     */
    public function getSettingsHelper()
    {
        return $this->settingsHelper;
    }

    public function isHumanized(): bool
    {
        return $this->configProvider->getDateFormat() === Date::DATE_TIME_PASSED;
    }

    public function isShowEditedAt(): bool
    {
        return $this->configProvider->isShowEditedAt();
    }

    public function isHumanizedEditedAt(): bool
    {
        return $this->configProvider->getEditedAtDateFormat() === Date::DATE_TIME_PASSED;
    }

    public function getReadTime(string $content): int
    {
        return $this->dataHelper->getReadTime($content);
    }

    public function isDisplayReadTime(): bool
    {
        return $this->configProvider->isDisplayReadTime();
    }
}
