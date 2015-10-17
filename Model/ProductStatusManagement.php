<?php


namespace MMPL15\ProductStatus\Model;

use MMPL15\ProductStatus\Api\ProductStatusManagementInterface;
use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

class ProductStatusManagement implements ProductStatusManagementInterface
{
    private $validStateIdentifiers = [ProductStatusAdapterInterface::ENABLED, ProductStatusAdapterInterface::DISABLED];
    
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

    /**
     * @param string $sku
     * @param string $status "enabled" or "disabled"
     * @return string
     */
    public function set($sku, $status)
    {
        $this->validateStatus($status);
        if (ProductStatusAdapterInterface::ENABLED === $status) {
            $this->productStatusAdapter->enableProductWithSku($sku);
        } else {
            $this->productStatusAdapter->disableProductWithSku($sku);
        }
        return $status;
    }

    /**
     * @param string $status
     */
    private function validateStatus($status)
    {
        if (!in_array($status, $this->validStateIdentifiers)) {
            throw new \InvalidArgumentException(sprintf(
                'The given status is invalid, it must be one of "%s"',
                implode('" or "', $this->validStateIdentifiers)
            ));
        }
    }
}
