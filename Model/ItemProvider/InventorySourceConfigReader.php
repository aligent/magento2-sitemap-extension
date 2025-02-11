<?php

namespace Aligent\Sitemap\Model\ItemProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class InventorySourceConfigReader
{
    private const string XML_PATH_ROUTE_KEY = 'aligent_sitemap/inventory_source/route_key';
    private const string XML_PATH_INCLUDE_IN_SITEMAP = 'aligent_sitemap/inventory_source/include_in_sitemap';
    private const string XML_PATH_CHANGE_FREQUENCY = 'aligent_sitemap/inventory_source/change_frequency';
    private const string XML_PATH_PRIORITY = 'aligent_sitemap/inventory_source/pirority';
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }
    /**
     * Get Route Key for source URLs
     *
     * @param int $storeId
     * @return string
     */
    public function getRouteKey(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ROUTE_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Get whether source URLs include in sitemap or not
     *
     * @param int $storeId
     * @return string
     */
    public function getIncludeInSitemap(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_INCLUDE_IN_SITEMAP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Get priority
     *
     * @param int $storeId
     * @return string
     */
    public function getPriority(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Get change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getChangeFrequency(int $storeId): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CHANGE_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
