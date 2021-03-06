<?php

namespace FutureActivities\PriceFrom\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveProduct implements ObserverInterface
{
    protected $productCollectionFactory;
    protected $productFactory;
    
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\ProductFactory $productFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        
        if ($product->getTypeId() != 'configurable')
            return;
        
        $childIds = $product->getTypeInstance()->getUsedProductIds($product);
        
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', array('in' => $childIds));
        $collection->setOrder('price', 'ASC');
        $collection->setPageSize(1);
        
        if ($collectionFirst = $collection->getFirstItem()) {
            if ($cheapestProduct = $this->productFactory->create()->setStoreId($product->getStoreId())->load($collectionFirst->getId()))
                $product->setPriceFrom(floatval($cheapestProduct->getPrice()));
        }
    }
}