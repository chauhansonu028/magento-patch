<?php
/**
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monolog\Handler\HandlerInterface;

/**
 * Class Digitalriver Logger
 *
 * @FIXME Refactor to no longer extend the base class, but instead use composition to inject an abstract logger (i.e.
 *        a LoggerInterface)
 */
class Logger extends \Digitalriver\DrPay\Logger\Drlogger
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Logger constructor.
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first,etc.
     * @param callable[]         $processors Optional array of processors
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        string $name,
        ScopeConfigInterface $scopeConfig,
        array $handlers = [],
        array $processors = []
    ) {
        /**
         * TODO: This should be eliminated with MAGETWO-53989
         */
        $handlers = array_values($handlers);
        $this->scopeConfig = $scopeConfig;

        parent::__construct($name, $handlers, $processors);
        $this->name = $name;
    }
    /**
     * Adds a log record.
     *
     * @param  int     $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return bool    Whether the record has been processed
     */
    public function addRecord(int $level, string $message, array $context = []): bool
    {
        $debug = $this->scopeConfig->getValue('dr_settings/config/debug', ScopeInterface::SCOPE_STORE);
        if ($debug) {
            return parent::addRecord($level, $message, $context);
        }
        return false;
    }
}
