<?php
/**
 * Catalog Sync Cron Configuration Setting
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

/**
 * Backend Model for Catalog Sync Cron
 */
namespace Digitalriver\DrPay\Model\Config\Backend;

use Exception;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Sitemap configuration
 */
class CatalogSync extends Value
{
    /**
     * Cron expression string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/catalog_sync/schedule/cron_expr';

    /**
     * Cron mode path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/catalog_sync/run/model';

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @var string
     */
    private $runModelPath = '';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save handler
     *
     * @return CatalogSync
     * @throws Exception
     */
    public function afterSave(): CatalogSync
    {
        $time = $this->getData('groups/catalog_sync/fields/time/value');
        $frequency = $this->getData('groups/catalog_sync/fields/frequency/value');

        $cronExprArray = [
            (int)$time[1], //Minute
            (int)$time[0], //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*', //# Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (Exception $e) {
            throw new LocalizedException(__('We can\'t save the catalog sync cron expression.'));
        }
        return parent::afterSave();
    }
}
