<?php


namespace Digitalriver\DrPay\Api;

interface TaxIdInterface
{
    /**
     * Set Tax ID type
     *
     * @param string $type
     * @return void
     */
    public function setType($type);

    /**
     * Get Tax ID type
     *
     * @return string
     */
    public function getType();

    /**
     * Set Tax ID value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value);

    /**
     * Get Tax ID value
     *
     * @return string
     */
    public function getValue();
}
