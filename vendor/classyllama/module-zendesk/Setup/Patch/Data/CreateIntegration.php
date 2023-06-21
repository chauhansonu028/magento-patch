<?php

namespace Zendesk\Zendesk\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zendesk\Zendesk\Helper\Integration;

class CreateIntegration implements DataPatchInterface
{

    /**
     * @var \Zendesk\Zendesk\Helper\Integration
     */
    protected $integrationHelper;

    /**
     * InstallData constructor.
     * @param \Zendesk\Zendesk\Helper\Integration $integrationHelper
     */
    public function __construct(
        Integration $integrationHelper
    ) {
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->integrationHelper->createIntegration();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
