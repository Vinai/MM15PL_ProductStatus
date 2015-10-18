<?php


namespace MM15PL\ProductStatus\Test\Integration;

use Magento\Framework\Console\CommandList;
use Magento\TestFramework\Helper\Bootstrap;
use MM15PL\ProductStatus\Console\Command\DisableProductCommand;
use MM15PL\ProductStatus\Console\Command\EnableProductCommand;
use MM15PL\ProductStatus\Console\Command\ShowProductStatusCommand;

class CommandRegistrationTest extends \PHPUnit_Framework_TestCase
{
    public function testItKnowsTheProductStatusCommands()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var CommandList $commandList */
        $commandList = $objectManager->create(CommandList::class);
        $registeredCommands = $commandList->getCommands();
        $this->assertArrayHasKey('showProductStatus', $registeredCommands);
        $this->assertArrayHasKey('disableProduct', $registeredCommands);
        $this->assertArrayHasKey('enableProduct', $registeredCommands);
        $this->assertInstanceOf(ShowProductStatusCommand::class, $registeredCommands['showProductStatus']);
        $this->assertInstanceOf(DisableProductCommand::class, $registeredCommands['disableProduct']);
        $this->assertInstanceOf(EnableProductCommand::class, $registeredCommands['enableProduct']);
    }
}
