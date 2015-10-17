<?php


namespace MMPL15\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

class ProductStatusAdapter implements ProductStatusAdapterInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $sku
     * @return string[]
     */
    public function getStatusForProductsMatchingSku($sku)
    {
        $this->validateSku($sku);
        
        $this->searchCriteriaBuilder->addFilter('sku', $this->getLikeSkuExpression($sku), 'like');
        $productList = $this->productRepository->getList($this->searchCriteriaBuilder->create());

        return array_reduce($productList->getItems(), function (array $carry, ProductInterface $product) {
            return array_merge($carry, [$product->getSku() => $this->getStatusString($product)]);
        }, []);
    }

    /**
     * @param string $sku
     */
    private function validateSku($sku)
    {
        if (!is_string($sku)) {
            throw new \InvalidArgumentException('The SKU pattern has to be a string, got "integer"');
        }
        if (empty($sku)) {
            throw new \InvalidArgumentException('The given SKU pattern can not be empty');
        }
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    public function getStatusString(ProductInterface $product)
    {
        return $product->getStatus() == ProductStatus::STATUS_ENABLED?
            ProductStatusAdapterInterface::ENABLED :
            ProductStatusAdapterInterface::DISABLED;
    }

    /**
     * @param string $sku
     * @return string
     */
    private function getLikeSkuExpression($sku)
    {
        $skuWithoutWildcards = str_replace(['%', '_'], '', $sku);
        return '%' . $skuWithoutWildcards . '%';
    }
}
