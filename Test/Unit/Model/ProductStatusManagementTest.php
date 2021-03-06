<?php


namespace MM15PL\ProductStatus\Model;

use MM15PL\ProductStatus\Api\ProductStatusManagementInterface;
use MM15PL\ProductStatus\LibraryApi\ProductStatusAdapterInterface;

/**
 * @covers \MM15PL\ProductStatus\Model\ProductStatusManagement
 */
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

    public function testItDelegatesEnablingProductsToTheProductStatusAdapter()
    {
        $this->mockProductStatusAdapter->expects($this->once())->method('enableProductWithSku');
        $this->statusManagement->set('test', ProductStatusAdapterInterface::ENABLED);
    }

    public function testItDelegatesDisablingProductsToTheProductStatusAdapter()
    {
        $this->mockProductStatusAdapter->expects($this->once())->method('disableProductWithSku');
        $this->statusManagement->set('test', ProductStatusAdapterInterface::DISABLED);
    }

    public function testItThrowsAnExceptionIfTheStatusIsInvalid()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The given status is invalid, it must be one of "enabled" or "disabled"'
        );
        $this->statusManagement->set('test', 'foo');
    }
}
