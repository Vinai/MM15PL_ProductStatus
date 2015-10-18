<?php


namespace MM15PL\ProductStatus\Console\Command;

use MM15PL\ProductStatus\LibraryApi\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers MM15PL\ProductStatus\Console\Command\ShowProductStatusCommand
 */
class ShowProductStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShowProductStatusCommand
     */
    private $command;

    /**
     * @var ProductStatusAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStatusAdapter;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInput;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutput;

    protected function setUp()
    {
        $this->mockProductStatusAdapter = $this->getMock(ProductStatusAdapterInterface::class);
        $this->command = new ShowProductStatusCommand($this->mockProductStatusAdapter);

        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasTheRightCommandName()
    {
        $this->assertSame('catalog:product:status', $this->command->getName());
    }

    public function testItHasADescription()
    {
        $this->assertNotEmpty($this->command->getDescription());
    }

    public function testItTakesARequiresSkuArgument()
    {
        $argument = $this->command->getDefinition()->getArgument('sku');
        $this->assertInstanceOf(InputArgument::class, $argument);
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItDelegatesToAProductStatusAdapter()
    {
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->mockProductStatusAdapter->expects($this->once())->method('getStatusForProductsMatchingSku')
            ->with('test')
            ->willReturn([]);
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysExceptionsAsErrorMessages()
    {
        $exceptionMessage = 'Test Dummy';
        $this->mockProductStatusAdapter->method('getStatusForProductsMatchingSku')
            ->willThrowException(new \Exception($exceptionMessage));
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with($this->stringStartsWith('<error>' . $exceptionMessage));
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessageIfThereIsNoException()
    {
        $expectedMessage = 'Status of product "test": ';
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->mockProductStatusAdapter->method('getStatusForProductsMatchingSku')
            ->willReturn(['test' => ProductStatusAdapterInterface::ENABLED]);
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with($this->stringStartsWith('<info>' . $expectedMessage));
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAMessageIfNoProductsMatchedTheGivenSku()
    {
        $expectedMessage = 'No products matching "test" found';
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->mockProductStatusAdapter->method('getStatusForProductsMatchingSku')->willReturn([]);
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with($this->stringStartsWith('<comment>' . $expectedMessage));
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysTheStatusForAllReturnedProducts()
    {
        $this->mockProductStatusAdapter->method('getStatusForProductsMatchingSku')
            ->willReturn([
                'test1' => ProductStatusAdapterInterface::ENABLED,
                'test2' => ProductStatusAdapterInterface::DISABLED,
            ]);
        $this->mockOutput->expects($this->exactly(2))->method('writeln')
            ->withConsecutive(
                [$this->stringContains('Status of product "test1": ' . ProductStatusAdapterInterface::ENABLED)],
                [$this->stringContains('Status of product "test2": ' . ProductStatusAdapterInterface::DISABLED)]
            );
        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
