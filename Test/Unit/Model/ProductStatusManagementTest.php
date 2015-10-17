<?php


namespace MMPL15\ProductStatus\Model;

use MMPL15\ProductStatus\Api\ProductStatusManagementInterface;
use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

class ProductStatusManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStatusManagement
     */
    private $statusManagement;

    /**
     * @var ProductStatusAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStatusAdapter;

    protected function setUp()
    {
        $this->mockProductStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->statusManagement = new ProductStatusManagement($this->mockProductStatusAdapter);
    }
    
    public function testItImplementsTheProductStatusManagementInterface()
    {
        $this->assertInstanceOf(ProductStatusManagementInterface::class, $this->statusManagement);
    }

    public function testItPassesOnTheStatusReturnedByTheProductStatusAdapter()
    {
        $this->mockProductStatusAdapter->method('getStatusBySku')->willReturn(ProductStatusAdapterInterface::ENABLED);
        $this->assertSame(ProductStatusAdapterInterface::ENABLED, $this->statusManagement->get('test'));
    }
}
