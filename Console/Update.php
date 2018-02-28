<?php
namespace FutureActivities\PriceFrom\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\{ObjectManager, State};

/**
 * Updates all configurable products with their price from value
 */
class Update extends Command
{
    protected $objectManager;
    
    protected function configure()
    {
        $this->setName('fa:price-from:update');
        $this->setDescription('Update the price from field for all configurable products.');
       
        parent::configure();
    }
   
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->objectManager = ObjectManager::getInstance();
        
        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('frontend');

        $storeManager = $this->objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores();
        foreach ($stores AS $store)
            $this->processStore($store, $output);
    }
    
    protected function processStore($store, &$output)
    {
        $productCollection = $this->objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');
        $productCollection->addAttributeToSelect(['name', 'price_from']);
        $productCollection->addFieldToFilter('type_id', 'configurable');
        $productCollection->addStoreFilter($store);
        
        $output->writeln('Total configurable products found in store '.$store->getName().': '.$productCollection->getSize());
        
        foreach ($productCollection AS $product) {
            $output->write('.');
            
            // Set the product store ID then save - this will trigger the observer event
            $product->setStoreId($store->getId());
            $product->save();
        }
        
        $output->writeLn('');
    }
}