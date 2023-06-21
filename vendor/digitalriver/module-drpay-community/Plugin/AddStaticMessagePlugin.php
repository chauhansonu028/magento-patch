<?php declare(strict_types=1);

/**
 * This plugin add static message to print invoice/refund pdfs
 *
 * @summary Add Static Message Plugin
 * @author Vignesh Balasubramani <vignesh.balasubramani@gds.ey.com>
 * @category   Digitalriver
 * @package    Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Zend_Pdf_Page;

/**
 * Class to add static message in invoice/cm printouts
 */
class AddStaticMessagePlugin
{
    const DEFAULT_MESSAGE = "These are not official Digital River Payments Documents";
    const CONFIG_STATIC_MESSAGE_PATH = 'dr_settings/config/dr_static_message';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * AddStaticMessagePlugin constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * After plugin to include static message in Print PDFs
     *
     * @param mixed $subject
     * @param Zend_Pdf_Page $result
     * @return Zend_Pdf_Page mixed
     */
    public function afterNewPage($subject, Zend_Pdf_Page $result): Zend_Pdf_Page
    {
        $messageConfig = $this->scopeConfig->getValue(self::CONFIG_STATIC_MESSAGE_PATH, ScopeInterface::SCOPE_STORE);

        $staticMessage = ($messageConfig && trim($messageConfig) != "") ? $messageConfig : self::DEFAULT_MESSAGE;

        $newLineBlock = ["lines" => [], "height" => 15];

        //Adds the static message in the pdf to be printed
        $newLineBlock["lines"][] = [
            [
                "text" => __($staticMessage),
                "align" => "center",
                "feed" => 30,
                "font" => "bold"
            ]
        ];
        return $subject->drawLineBlocks($result, [$newLineBlock]);
    }
}
