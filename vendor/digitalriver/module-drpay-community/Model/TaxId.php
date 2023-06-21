<?php
/**
 * DR Tax ID Model
 *
 * @summary Provides methods for managing DR Tax IDs.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */
namespace Digitalriver\DrPay\Model;

use Digitalriver\DrPay\Api\TaxIdInterface;
use Magento\Framework\Exception\InvalidArgumentException;

class TaxId implements TaxIdInterface
{
    const ERROR_CODE_TAX_ID_TYPE_NOT_VALID = 1;

    /**
     * Tax ID type
     *
     * @var string
     */
    protected $type;

    /**
     * Tax ID value
     *
     * @var string
     */
    protected $value;

    /**
     * Sets Tax ID type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Gets Tax ID type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets Tax ID value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        if (!$value) {
            throw new InvalidArgumentException(
                __('Invalid tax identifier'),
                null,
                self::ERROR_CODE_TAX_ID_TYPE_NOT_VALID
            );
        }

        $this->value = $value;
    }

    /**
     * Gets Tax ID value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
