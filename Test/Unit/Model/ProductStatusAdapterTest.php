<?php


namespace MMPL15\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\State as AppState;
use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

/**
 * @covers \MMPL15\ProductStatus\Model\ProductStatusAdapter
 */
class ProductStatusAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStatusAdapter
     */
    private $productStatusAdapter;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductRepository;

    /**
     * @var ProductSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchResults;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchCriteriaBuilder;

    /**
     * @param string $sku
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockEnabledProduct($sku)
    {
        return $this->getMockProductWithStatus($sku, ProductStatus::STATUS_ENABLED);
    }

    /**
     * @param string $sku
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockDisabledProduct($sku)
    {
        return $this->getMockProductWithStatus($sku, ProductStatus::STATUS_DISABLED);
    }

    /**
     * @param string $sku
     * @param int $status
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProductWithStatus($sku, $status)
    {
        $mockProduct = $this->getMock(ProductInterface::class);
        $mockProduct->method('getSku')->willReturn($sku);
        $mockProduct->method('getStatus')->willReturn($status);
        return $mockProduct;
    }

    protected function setUp()
    {
        $this->mockSearchResults = $this->getMock(ProductSearchResultsInterface::class);
        $this->mockProductRepository = $this->getMock(ProductRepositoryInterface::class);
        $this->mockProductRepository->method('getList')->willReturn($this->mockSearchResults);
        $this->mockSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class, [], [], '', false);
        $this->mockSearchCriteriaBuilder->method('create')->willReturn($this->getMock(SearchCriteriaInterface::class));
        $this->productStatusAdapter = new ProductStatusAdapter(
            $this->mockProductRepository,
            $this->mockSearchCriteriaBuilder,
            $this->getMock(AppState::class, [], [], '', false)
        );
    }
    public function testItImplementsTheInterface()
    {
        $this->assertInstanceOf(ProductStatusAdapterInterface::class, $this->productStatusAdapter);
    }

    /**
     * @param string $methodName
     * @dataProvider methodsWithSkuArgumentProvider
     */
    public function testItThrowsAnExceptionIfTheGivenSkuIsNotAString($methodName)
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The SKU pattern has to be a string, got "integer"'
        );
        call_user_func([$this->productStatusAdapter, $methodName], 111);
    }

    /**
     * @param string $methodName
     * @dataProvider methodsWithSkuArgumentProvider
     */
    public function testItThrowsAnExceptionIfTheGivenSkuIsEmpty($methodName)
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The given SKU pattern can not be empty'
        );
        call_user_func([$this->productStatusAdapter, $methodName], ' ');
    }

    public function methodsWithSkuArgumentProvider()
    {
        return [
            'getStatusForProductsMatchingSku' => ['getStatusForProductsMatchingSku'],
            'disableProductWithSku' => ['disableProductWithSku'],
            'getStatusBySku' => ['getStatusBySku'],
        ];
    }

    public function testItQueriesAProductRepository()
    {
        $this->mockSearchResults->expects($this->once())->method('getItems')->willReturn([]);
        $this->productStatusAdapter->getStatusForProductsMatchingSku('test');
    }

    public function testItReturnsAnEmptyArrayIfThereIsNoMatch()
    {
        $this->mockSearchResults->expects($this->once())->method('getItems')->willReturn([]);
        $this->assertSame([], $this->productStatusAdapter->getStatusForProductsMatchingSku('test'));
    }

    public function testItSetsTheSkuPatternAsASearchCriteriaFilter()
    {
        $this->mockSearchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with('sku', '%test%', 'like');
        $this->mockSearchResults->expects($this->once())->method('getItems')->willReturn([]);
        $this->productStatusAdapter->getStatusForProductsMatchingSku('test');
    }

    public function testItTranslatesProductRepositorySearchResultsIntoTheDesiredReturnArrayFormat()
    {
        $this->mockSearchResults->method('getItems')->willReturn([
            $this->getMockEnabledProduct('test1'),
            $this->getMockDisabledProduct('test2'),
        ]);
        $expectedResult = [
            'test1' => ProductStatusAdapterInterface::ENABLED,
            'test2' => ProductStatusAdapterInterface::DISABLED
        ];
        $this->assertSame($expectedResult, $this->productStatusAdapter->getStatusForProductsMatchingSku('test'));
    }

    public function testItThrowsAnExceptionIfTheProductAlreadyIsDisabled()
    {
        $this->mockProductRepository->method('get')->willReturn($this->getMockDisabledProduct('test'));
        $this->setExpectedException(
            \RuntimeException::class,
            'The product with the SKU "test" already is disabled'
        );
        $this->productStatusAdapter->disableProductWithSku('test');
    }

    public function testItDisablesAnExistingProduct()
    {
        $mockProduct = $this->getMockEnabledProduct('test');
        $mockProduct->expects($this->once())->method('setStatus')->with(ProductStatus::STATUS_DISABLED);
        $this->mockProductRepository->method('get')->willReturn($mockProduct);
        $this->mockProductRepository->expects($this->once())->method('save');
        $this->productStatusAdapter->disableProductWithSku('test');
    }

    public function testItTranslatesTheProductsStatusToTheStatusString()
    {
        $mockProduct = $this->getMockEnabledProduct('test');
        $this->mockProductRepository->method('get')->with('test')->willReturn($mockProduct);
        
        $this->assertSame(ProductStatusAdapterInterface::ENABLED, $this->productStatusAdapter->getStatusBySku('test'));
    }
}
