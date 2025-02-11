<?php

namespace Aligent\Sitemap\Model\ItemProvider;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\Sitemap\Model\SitemapItemInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
class InventorySource
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param InventorySourceConfigReader $configReader
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly SitemapItemInterfaceFactory $itemFactory,
        private readonly InventorySourceConfigReader $configReader,
    ) {
    }

    /**
     * Get Items
     *
     * @param int $storeId
     * @return SitemapItemInterface[]
     */
    public function getItems(int $storeId)
    {
        if (!$this->configReader->getIncludeInSitemap($storeId)) {
            return[];
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(SourceInterface::NAME)
            ->addFieldToSelect('url_key')
            ->addFieldToFilter('show_in_sitemap', ['eq' => 1])
            ->addFieldToFilter('url_key', ['neq' => 'NULL'])
            ->addFieldToFilter('enabled', ['eq' => 1]);
        $items = [];
        $routeKey = $this->configReader->getRouteKey($storeId);
        $priority = $this->configReader->getPriority($storeId);
        $changeFreq = $this->configReader->getChangeFrequency($storeId);
        // Adding inventory source main URL to the sitemap
        $items[] = $this->itemFactory->create([
            'url' => $routeKey,
            'priority' => $priority,
            'changeFrequency' => $changeFreq
        ]);
        foreach ($collection as $item) {
            $itemUrl = $routeKey . '/' . $item->getUrlKey();
            $items[] = $this->itemFactory->create([
                'url' => $itemUrl,
                'priority' => $priority,
                'changeFrequency' => $changeFreq
            ]);
        }
        return $items;
    }
}
