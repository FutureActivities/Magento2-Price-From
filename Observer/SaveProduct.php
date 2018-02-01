<?php

namespace FutureActivities\PriceFrom\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveProduct implements ObserverInterface
{
    protected $productFactory;
    
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory)
    {
        $this->productFactory = $productCollectionFactory;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        
        if ($product->getTypeId() != 'configurable')
            return;
            
        $childIds = $product->getTypeInstance()->getUsedProductIds($product);
        
        $collection = $this->productFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', array('in' => $childIds));
        $collection->setOrder('price', 'ASC');
        $collection->setPageSize(1);
       
        if ($cheapestProduct = $collection->getFirstItem())
            $product->setPriceFrom(floatval($cheapestProduct->getPrice()));
    }
}