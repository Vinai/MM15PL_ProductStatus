<?php


namespace MMPL15\ProductStatus\Console\Command;

use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers MMPL15\ProductStatus\Console\Command\EnableProductCommand
 */
class EnableProductCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnableProductCommand
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
        $this->command = new EnableProductCommand($this->mockProductStatusAdapter);
        $this->mockInput = $this->getMock(InputInterface::class);
        $this->mockOutput = $this->getMock(OutputInterface::class);
    }
    
    public function testItIsAConsoleCommand()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testItHasAName()
    {
        $this->assertSame('catalog:product:enable', $this->command->getName());
    }

    public function testItHasADescription()
    {
        $this->assertNotEmpty($this->command->getDescription());
    }

    public function testItTakesARequiredSkuArgument()
    {
        $argument = $this->command->getDefinition()->getArgument('sku');
        $this->assertTrue($argument->isRequired());
        $this->assertNotEmpty($argument->getDescription());
    }

    public function testItDelegatesToTheProductStatusAdapter()
    {
        $this->mockProductStatusAdapter->expects($this->once())->method('enableProductWithSku')->with('test');
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysExceptionsAsAnErrorMessage()
    {
        $expectedMessage = 'Dummy Exception';
        $this->mockOutput->expects($this->once())->method('writeln')
            ->with('<error>' . $expectedMessage . '</error>');

        $this->mockProductStatusAdapter->method('enableProductWithSku')
            ->willThrowException(new \Exception($expectedMessage));
        $this->mockInput->method('getArgument')->willReturn('test');
        $this->command->run($this->mockInput, $this->mockOutput);
    }

    public function testItDisplaysAConfirmationMessage()
    {
        $expectedMessage = 'Status of product "test": enabled';

        $this->mockOutput->expects($this->once())->method('writeln')
            ->with('<info>' . $expectedMessage . '</info>');

        $this->mockInput->method('getArgument')->willReturn('test');
        $this->command->run($this->mockInput, $this->mockOutput);
    }
}
