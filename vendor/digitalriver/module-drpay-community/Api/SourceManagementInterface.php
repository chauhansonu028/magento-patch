<?php
/**
 * DR Sources management model api interface
 *
 * @summary Provides interface for managing DR sources on the backend for a logged in user.
 * @author Vujadin Scepanovic <vujadin.scepanovic@rs.ey.com>
 * @category Digitalriver
 * @package  Digitalriver_DrPay
 */

namespace Digitalriver\DrPay\Api;

interface SourceManagementInterface
{

    /**
     * Locks DR checkout Id
     *
     * @param string $cartId
     * @return bool
     */
    public function lockCheckoutId($cartId);
}
