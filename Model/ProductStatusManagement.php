<?php


namespace MMPL15\ProductStatus\Model;

use MMPL15\ProductStatus\Api\ProductStatusManagementInterface;
use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

class ProductStatusManagement implements ProductStatusManagementInterface
{
    /**
     * @var ProductStatusAdapterInterface
     */
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter)
    {
        $this->productStatusAdapter = $productStatusAdapter;
    }
    
    /**
     * @param string $sku
     * @return string Status "enabled" or "disabled"
     */
    public function get($sku)
    {
        return $this->productStatusAdapter->getStatusBySku($sku);
    }
}
