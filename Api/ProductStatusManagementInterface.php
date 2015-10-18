<?php


namespace MM15PL\ProductStatus\Api;

interface ProductStatusManagementInterface
{
    /**
     * @param string $sku "enabled" or "disabled"
     * @return string
     */
    public function get($sku);

    /**
     * @param string $sku
     * @param string $status "enabled" or "disabled"
     * @return string
     */
    public function set($sku, $status);
}
