<?php


namespace MMPL15\ProductStatus\LibraryApi;

interface ProductStatusAdapterInterface
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
    
    /**
     * @param string $sku
     * @return string[]
     */
    public function getStatusForProductsMatchingSku($sku);

    /**
     * @param string $sku
     * @return void
     */
    public function disableProductWithSku($sku);

    /**
     * @param string $sku
     * @return void
     */
    public function enableProductWithSku($sku);

    /**
     * @param $sku
     * @return string
     */
    public function getStatusBySku($sku);
}
