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
}
