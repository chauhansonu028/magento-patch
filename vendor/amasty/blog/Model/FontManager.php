<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Filesystem\Driver\Https;

class FontManager
{
    private const GOOGLE_FONT_URL = 'https://fonts.googleapis.com/css?family={font}&display=swap';
    private const FONT_STYLE_PATTERN = '/font-style: ([a-z\_]{0,50});/';
    private const DEFAULT_FONT_STYLE = 'normal';

    /**
     * @var Https
     */
    private $httpsDriver;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    public function __construct(Https $httpsDriver, WriterInterface $configWriter)
    {
        $this->httpsDriver = $httpsDriver;
        $this->configWriter = $configWriter;
    }

    /**
     * @param string $font
     * @return string
     */
    public function getFontUrl(string $font): string
    {
        return str_replace('{font}', urlencode($font), self::GOOGLE_FONT_URL);
    }

    /**
     * @param string $font
     * @return bool
     */
    public function validateFont(string $font): bool
    {
        try {
            $fontContent = $this->getFontContent($font);

            return (bool)$fontContent;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $font
     * @return string
     */
    private function getFontContent(string $font): string
    {
        $fontUrl = str_replace('https://', '', $this->getFontUrl($font));

        return $this->httpsDriver->fileGetContents($fontUrl);
    }

    /**
     * @param string $font
     * @return string
     */
    public function getFontStyle(string $font): string
    {
        $fontContent = $this->getFontContent($font);
        preg_match(self::FONT_STYLE_PATTERN, $fontContent, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        return self::DEFAULT_FONT_STYLE;
    }

    /**
     * @param string $font
     * @param string $scope
     * @param int $scopeId
     */
    public function addConfigFontStyle(string $font, string $scope, int $scopeId): void
    {
        $this->configWriter->save(
            'amblog/fonts/google_font_style',
            $this->getFontStyle($font),
            $scope,
            $scopeId
        );
    }
}
