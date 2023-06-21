<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Steps extends Template
{
    public const STEP1 = 'files';
    public const STEP2 = 'stores';
    public const STEP3 = 'import';

    public const STEPS = [
        self::STEP1 => 'Prepare Attachments For Import',
        self::STEP2 => 'Select Stores For Configuration',
        self::STEP3 => 'Import Your Attachments'
    ];

    /**
     * @var string
     */
    private $currentStep;

    /**
     * @var string
     */
    private $backLink;

    /**
     * @var string
     */
    private $nextLink;

    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;

        return $this;
    }

    public function getCurrentStep()
    {
        if (!array_key_exists($this->currentStep, self::STEPS)) {
            return self::STEP1;
        }

        return $this->currentStep;
    }

    public function getSteps()
    {
        return self::STEPS;
    }

    public function getBackLink()
    {
        return $this->backLink;
    }

    public function setBackLink($backLink)
    {
        $this->backLink= $backLink;

        return $this;
    }

    public function getNextLink()
    {
        return $this->nextLink;
    }

    public function setNextLink($nextLink)
    {
        $this->nextLink = $nextLink;

        return $this;
    }

    public function getImportListingUrl()
    {
        return $this->getUrl('amfile/import/index');
    }
}
