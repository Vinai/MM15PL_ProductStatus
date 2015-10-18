<?php


namespace MM15PL\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use MM15PL\ProductStatus\LibraryApi\ProductStatusAdapterInterface;
use Magento\Framework\App\State as AppState;

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
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AppState $appState
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        try {
            $appState->setAreaCode('adminhtml');
        } catch (\Exception $e) {}
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
            throw new \InvalidArgumentException(sprintf('The SKU pattern has to be a string, got "%s"', gettype($sku)));
        }
        if (empty(trim($sku))) {
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

    /**
     * @param string $sku
     */
    public function disableProductWithSku($sku)
    {
        $this->validateSku($sku);
        $product = $this->productRepository->get($sku);
        if ($product->getStatus() === ProductStatus::STATUS_DISABLED) {
            throw new \RuntimeException(sprintf('The product with the SKU "%s" already is disabled', $sku));
        }
        $product->setStatus(ProductStatus::STATUS_DISABLED);
        $this->productRepository->save($product);
    }

    /**
     * @param string $sku
     * @return string
     */
    public function getStatusBySku($sku)
    {
        $this->validateSku($sku);
        $product = $this->productRepository->get($sku);
        return $this->getStatusString($product);
    }

    /**
     * @param string $sku
     */
    public function enableProductWithSku($sku)
    {
        $this->validateSku($sku);
        $product = $this->productRepository->get($sku);
        if ($product->getStatus() == ProductStatus::STATUS_ENABLED) {
            throw new \RuntimeException(sprintf('The product with the SKU "%s" already is enabled', $sku));
        }
        $product->setStatus(ProductStatus::STATUS_ENABLED);
        $this->productRepository->save($product);
    }
}
