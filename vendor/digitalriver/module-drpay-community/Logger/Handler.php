<?php
/**
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Logger;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Handler for Digitalriver logger
 */
class Handler extends Base
{
    private const DEBUG_LOG_FILE_NAME = 'dr_settings/catalog_sync/log_filename';
    private const DEFAULT_LOG_FILE_PATH = '/var/log';

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/drlog.log';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param ScopeConfigInterface $scopeConfig
     * @param null|string $filePath
     * @param null|string $fileName
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ScopeConfigInterface $scopeConfig,
        $filePath = null,
        $fileName = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $debugFileName = $this->scopeConfig->getValue(self::DEBUG_LOG_FILE_NAME, ScopeInterface::SCOPE_STORE);
        if ($debugFileName) {
            $fileName = self::DEFAULT_LOG_FILE_PATH . DIRECTORY_SEPARATOR . $debugFileName;
        } else {
            $fileName = $this->fileName;
        }
        parent::__construct($filesystem, $filePath, $fileName);
    }
}
