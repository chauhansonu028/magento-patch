<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout;

use Amasty\Blog\Block\Layout\MobileWrapper;
use Amasty\Blog\Block\Layout\WidgetWrapper;
use Amasty\Blog\Exceptions\LayoutGenerationException as LayoutGenerationException;
use Magento\Framework\Config\Dom as XmlDomValidator;
use Psr\Log\LoggerInterface;
use SimpleXMLElement as SimpleXMLElement;

class Generator implements GeneratorInterface
{
    public const LAYOUT_XSD_URN = 'urn:magento:framework:View/Layout/etc/page_configuration.xsd';
    public const MOBILE_POST = 'mobile_post';
    public const MOBILE_LIST = 'mobile_list';
    public const DESKTOP_POST = 'desktop_post';
    public const DESKTOP_LIST = 'desktop_list';

    /**
     * @var BlockNameGeneratorInterface
     */
    private $blockNameGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        BlockNameGeneratorInterface $blockNameGenerator,
        LoggerInterface $logger
    ) {
        $this->blockNameGenerator = $blockNameGenerator;
        $this->logger = $logger;
    }

    /**
     * @param Config $layoutConfig
     * @return string
     * @throws LayoutGenerationException
     */
    public function generate(Config $layoutConfig): string
    {
        $xml = $this->generateXml($layoutConfig);
        $xml = $this->prepareXml($xml);
        $this->validate($xml);

        return $xml;
    }

    /**
     * @param string $xml
     * @throws LayoutGenerationException
     */
    private function validate(string $xml): void
    {
        $dom = new \DOMDocument();
        $dom->loadXML(sprintf('<page>%s</page>', $xml));

        try {
            $errorsArray = XmlDomValidator::validateDomDocument($dom, self::LAYOUT_XSD_URN);

            if (!empty($errorsArray)) {
                $this->logger->critical(join(PHP_EOL, $errorsArray));
                throw new LayoutGenerationException(__('Invalid xml was generated'));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LayoutGenerationException(__('Invalid xml was generated'));
        }
    }

    private function prepareXml(string $xml): string
    {
        $xml = preg_replace('@\<\?xml.*?\?\>@', '', $xml);
        $xml = trim($xml);

        return $xml;
    }

    private function generateXml(Config $layoutConfig): string
    {
        $layout = new SimpleXMLElement('<body/>');

        foreach (ConfigFactory::DI_NAMES_MAP as $referenceBlockName => $sectionName) {
            $getter = sprintf('get%s', ucfirst($sectionName));
            $sectionBlocks = $layoutConfig->{$getter}();

            if (!empty($sectionBlocks)) {
                $referenceSection = $layout->addChild('referenceContainer');
                $referenceSection->addAttribute(
                    'name',
                    sprintf('amasty_blog.layout.%s', $referenceBlockName)
                );

                if (in_array($layoutConfig->getConfigIdentifier(), [self::MOBILE_LIST, self::MOBILE_POST])
                    && in_array($referenceBlockName, [ConfigFactory::RIGHT_SIDE, ConfigFactory::LEFT_SIDE])
                ) {
                    $header = $layout->addChild('referenceContainer');
                    $header->addAttribute('name', 'amasty_blog.layout.header');
                    $referenceSection = $this->generateMobileWrapper(
                        $header,
                        $referenceBlockName === ConfigFactory::RIGHT_SIDE
                    );
                }

                $previousBlockName = null;

                foreach ($sectionBlocks as $layoutBlockConfig) {
                    $previousBlockName = $this->appendBlock($layoutBlockConfig, $referenceSection, $previousBlockName);
                }
            }
        }

        return $layout->asXML();
    }

    private function generateMobileWrapper(SimpleXMLElement $parentElement, bool $rightSide): SimpleXMLElement
    {
        $mobileWrapper = $parentElement->addChild('block');
        $mobileWrapper->addAttribute('class', MobileWrapper::class);
        $mobileWrapper->addAttribute('template', 'Amasty_Blog::list/mobile_wrapper.phtml');
        $mobileWrapper->addAttribute(
            'name',
            $this->blockNameGenerator->generate(MobileWrapper::class)
        );

        if ($rightSide) {
            $actionNode = $mobileWrapper->addChild('action');
            $actionNode->addAttribute('method', 'setRightSideBlocks');
        }

        return $mobileWrapper;
    }

    private function getElementWrapper(SimpleXMLElement $target, string $nameInLayout): SimpleXMLElement
    {
        $wrapper = $target->addChild('block');
        $wrapper->addAttribute('class', WidgetWrapper::class);
        $wrapper->addAttribute('template', 'Amasty_Blog::widget_wrapper.phtml');
        $wrapper->addAttribute('name', $nameInLayout);

        return $wrapper;
    }

    private function appendBlock(BlockConfig $config, SimpleXMLElement $target, ?string $previousBlockName): string
    {
        $renderedName = $config->getLayoutName();

        if ($config->isNeedWrap()) {
            $renderedName = $this->blockNameGenerator->generate(WidgetWrapper::class);
            $target = $this->getElementWrapper($target, $renderedName);
        }

        $blockNode = $target->addChild($config->getContainerType());
        $blockNode->addAttribute('name', $config->getLayoutName());

        if ($config->isNeedWrap()) {
            $blockNode->addAttribute('as', $config->getAlias());

            if ($previousBlockName) {
                $target->addAttribute('after', $previousBlockName);
            }
        } elseif ($previousBlockName) {
            $blockNode->addAttribute('after', $previousBlockName);
        }

        if ($config->getContainerType() === BlockConfig::TYPE_BLOCK) {
            $this->prepareBlockNode($blockNode, $config);
        }

        return $renderedName;
    }

    private function prepareBlockNode(SimpleXMLElement $blockNode, BlockConfig $config): void
    {
        $blockNode->addAttribute('class', $config->getClassName());

        if (null !== $config->getTemplate()) {
            $blockNode->addAttribute('template', $config->getTemplate());
        }

        if (!empty($config->getArguments())) {
            $argumentsNode = $blockNode->addChild('arguments');

            foreach ($config->getArguments() as $name => $value) {
                $argumentNode = $argumentsNode->addChild('argument', $value);
                $argumentNode->addAttribute('name', $name);
            }
        }
    }
}
