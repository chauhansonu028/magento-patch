<?php
/**
 * Provides eccn code attributes information.
 *
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
declare(strict_types=1);

namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Model\ResourceModel\EccnCode as EccnCodeResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Eccn
 */
class EccnCode extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'dr_eccn_code';

    /**
     * @var string
     */
    protected $_cacheTag = 'dr_eccn_code';

    /**
     * @var string
     */
    protected $_eventPrefix = 'dr_eccn_code';

    /**
     * @var string
     */
    protected $classificationCode = 'classification_code';

    /**
     * @var string
     */
    protected $description = 'description';

    /**
     * @var string
     */
    protected $notes = 'notes';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(EccnCodeResource::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getEventObject(): string
    {
        return $this->_eventObject;
    }

    /**
     * @param string $eventObject
     */
    public function setEventObject(string $eventObject): void
    {
        $this->_eventObject = $eventObject;
    }

    /**
     * Returns the classification code
     *
     * @return string
     */
    public function getClassificationCode(): string
    {
        return $this->classificationCode;
    }

    /**
     * Sets the classification code
     *
     * @param string $classificationCode
     */
    public function setClassificationCode(string $classificationCode): void
    {
        $this->classificationCode = $classificationCode;
    }

    /**
     * Returns the description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Returns the notes
     *
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * Sets the notes
     *
     * @param string $notes
     */
    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }
}
