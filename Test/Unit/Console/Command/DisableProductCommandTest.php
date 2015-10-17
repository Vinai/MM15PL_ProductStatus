<?php


namespace MMPL15\ProductStatus\Console\Command;

use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \MMPL15\ProductStatus\Console\Command\DisableProductCommand
 */
class DisableProductCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DisableProductCommand
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
        $this->command = new DisableProductCommand($this->mockProductStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }

    public function testItIsACommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasTheRightName()
    {
        $this->assertSame('catalog:product:disable', $this->command->getName());
    }

    public function testItHasADescription()
    {
        $this->assertNotEmpty($this->command->getDescription());
    }

    public function testItTakesARequiredSkuArgument()
    {
        $argument = $this->command->getDefinition()->getArgument('sku');
        $this->assertInstanceOf(InputArgument::class, $argument);
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItDelegatesToTheProductStatusAdapter()
    {
        $this->mockProductStatusAdapter->expects($this->once())->method('disableProductWithSku')->with('test');
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysExceptionsAsErrorMessages()
    {
        $expectedMessage = 'Dummy Exception';
        $this->mockProductStatusAdapter->method('disableProductWithSku')
            ->willThrowException(new \Exception($expectedMessage));

        $this->mockOutput->expects($this->once())->method('writeln')
            ->with($this->stringStartsWith('<error>' . $expectedMessage));
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessageIfThereWasNoException()
    {
        $expectedMessage = 'Status of product "test": ' . ProductStatusAdapterInterface::DISABLED;
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with($this->stringStartsWith('<info>' . $expectedMessage));

        $this->mockInput->method('getArgument')->willReturn('test');

        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
