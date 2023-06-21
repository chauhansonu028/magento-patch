<?php

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Framework\HTTP\Client\Curl;
use Digitalriver\DrPay\Helper\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Digitalriver\DrPay\Logger\Logger;

/**
 * File Sender for DigitalRiver
 */
class FileSend extends AbstractHelper
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @param Curl $curl
     * @param Config $config
     * @param Json $jsonSerializer
     */
    public function __construct(
        Context $context,
        Curl $curl,
        Config $config,
        Json $jsonSerializer,
        Logger $logger
    ) {
        $this->config = $config;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @param $purpose
     * @param $path
     * @return string
     */
    public function sendFile($purpose, $path): string
    {
        $this->logger->info("\n\nDRAPI URL /files");

        $secret = $this->config->getSecretKey();
        $this->curl->addHeader("Authorization", "Bearer {$secret}");

        $data = [
            'purpose' => $purpose,
            'title' => '',
            'fileName' => $this->getFileNameFromPath($path),
            'file' => new \CURLFile($path)
        ];

        $this->curl->post($this->config->getUrl() . '/files', $data);
        $result = $this->curl->getBody();

        try {
            $this->logger->info("\n\nDRAPI RESPONSE: " . $result);
            $result = $this->jsonSerializer->unserialize($result);
            return $result["id"];
        } catch (\Exception $e) {
            $this->logger->error("DR_API_ERROR", [$result, $e->getMessage()]);
            return '';
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getFileNameFromPath(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        $filenameParts = explode('/', $path);
        return end($filenameParts);
    }
}
