<?php


namespace MMPL15\ProductStatus\Console\Command;

use MMPL15\ProductStatus\LibraryApi\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableProductCommand extends Command
{
    /**
     * @var ProductStatusAdapterInterface
     */
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter)
    {
        parent::__construct();
        $this->productStatusAdapter = $productStatusAdapter;
    }


    protected function configure()
    {
        $this->setName('catalog:product:enable');
        $this->setDescription('Enable the product with the given SKU');
        $this->addArgument('sku', InputArgument::REQUIRED, 'The SKU of the product to enable');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $sku = $input->getArgument('sku');
            $this->productStatusAdapter->enableProductWithSku($sku);
            $output->writeln(sprintf('<info>Status of product "%s": enabled</info>', $sku));
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
    }
}
