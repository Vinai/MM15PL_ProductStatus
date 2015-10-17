<?php


namespace MMPL15\ProductStatus\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

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
     * @param int $status
     * @return ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProduct($sku, $status)
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
            $this->mockSearchCriteriaBuilder
        );
    }

    public function testItImplementsTheInterface()
    {
        $this->assertInstanceOf(ProductStatusAdapterInterface::class, $this->productStatusAdapter);
    }

    public function testItThrowsAnExceptionIfTheGivenSkuIsNotAString()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The SKU pattern has to be a string, got "integer"'
        );
        $this->productStatusAdapter->getStatusForProductsMatchingSku(111);
    }

    public function testItThrowsAnExceptionIfTheGivenSkuIsEmpty()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The given SKU pattern can not be empty'
        );
        $this->productStatusAdapter->getStatusForProductsMatchingSku('');
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
            $this->getMockProduct('test1', ProductStatus::STATUS_ENABLED),
            $this->getMockProduct('test2', ProductStatus::STATUS_DISABLED),
        ]);
        $expectedResult = [
            'test1' => ProductStatusAdapterInterface::ENABLED,
            'test2' => ProductStatusAdapterInterface::DISABLED
        ];
        $this->assertSame($expectedResult, $this->productStatusAdapter->getStatusForProductsMatchingSku('test'));
    }
}
