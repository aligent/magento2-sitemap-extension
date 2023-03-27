<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Aligent\Sitemap\Model\Config\Data as AligentSitemapConfig;

class PwaUrl implements ItemProviderInterface
{
    /**
     * StoreUrlSitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param SitemapItemInterfaceFactory $itemFactory
     * @param AligentSitemapConfig $aligentSitemapConfig
     */
    public function __construct(
        private readonly ConfigReaderInterface $configReader,
        private readonly SitemapItemInterfaceFactory $itemFactory,
        private readonly AligentSitemapConfig $aligentSitemapConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getItems($storeId): array
    {
        $items = [];
        if ($this->aligentSitemapConfig->isPwaPagesInclude((int)$storeId)) {
            $pwaPagesUrlKeys = $this->aligentSitemapConfig->getPwaPagesUrlKey((int)$storeId);
            foreach ($pwaPagesUrlKeys as $pwaPagesUrlKey) {
                $items[] = $this->itemFactory->create([
                    'url' => $pwaPagesUrlKey,
                    'priority' => $this->configReader->getPriority($storeId),
                    'changeFrequency' => $this->configReader->getChangeFrequency($storeId)
                ]);
            }
        }

        return $items;
    }
}
