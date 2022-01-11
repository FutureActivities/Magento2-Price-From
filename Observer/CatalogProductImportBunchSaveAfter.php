<?php

namespace FutureActivities\PriceFrom\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductImportBunchSaveAfter implements ObserverInterface
{
    protected $productCollectionFactory;
    protected $productFactory;
    protected $productRepository; 
    
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $bunch = $observer->getBunch();
            
            foreach($bunch as $p) {
                $product = $this->productRepository->get($p['sku']);
                if ($product->getTypeId() != 'configurable') continue;
                
                $childIds = $product->getTypeInstance()->getUsedProductIds($product);
                $collection = $this->productCollectionFactory->create();
                $collection->addAttributeToSelect('*');
                $collection->addAttributeToFilter('entity_id', array('in' => $childIds));
                $collection->setOrder('price', 'ASC');
                $collection->setPageSize(1);
                
                if ($collectionFirst = $collection->getFirstItem()) {
                    if ($cheapestProduct = $this->productFactory->create()->setStoreId($product->getStoreId())->load($collectionFirst->getId())) {
                        $priceFrom = floatval($cheapestProduct->getPrice());
                        $product->addAttributeUpdate('price_from', $priceFrom, $product->getStoreId());
                    }
                }
            }
            
        } catch (\Execption $e) {
        }
    }
}