<?php


namespace MM15PL\ProductStatus\Console\Command;

use MM15PL\ProductStatus\LibraryApi\ProductStatusAdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowProductStatusCommand extends Command
{
    /**
     * @var ProductStatusAdapterInterface
     */
    private $productStatusAdapter;

    public function __construct(ProductStatusAdapterInterface $productStatusAdapter)
    {
        $this->productStatusAdapter = $productStatusAdapter;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('catalog:product:status');
        $this->setDescription('Show status for products matching the given SKU');
        $this->addArgument('sku', InputArgument::REQUIRED, 'SKU Pattern');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $sku = $input->getArgument('sku');
            $result = $this->productStatusAdapter->getStatusForProductsMatchingSku($sku);
            if (empty ($result)) {
                $output->writeln(sprintf('<comment>No products matching "%s" found</comment>', $sku));
            } else {
                array_map(function ($sku, $status) use ($output) {
                    $output->writeln(sprintf('<info>Status of product "%s": %s</info>', $sku, $status));
                }, array_keys($result), $result);
                
            }
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
    }


}
