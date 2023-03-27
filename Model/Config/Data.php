<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Data
{
    private const CONFIG_XML_PATH_SITEMAP_ENABLED = 'aligent_sitemap/general/enabled';
    private const CONFIG_XML_PATH_SITEMAP_EXCLUDE_CMS_PAGE = 'aligent_sitemap/general/exclude_cms_page';
    private const CONFIG_XML_PATH_SITEMAP_EXCLUDE_CATEGORY = 'aligent_sitemap/general/exclude_category';
    private const CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT = 'aligent_sitemap/general/exclude_product';
    private const CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT_IMAGES = 'aligent_sitemap/general/exclude_product_images';
    private const CONFIG_XML_PATH_SITEMAP_INCLUDE_PWA_PAGES = 'aligent_sitemap/general/include_pwa_pages';
    private const CONFIG_XML_PATH_SITEMAP_BASE_URL = 'aligent_sitemap/general/sitemap_base_url';
    private const CONFIG_XML_PATH_SITEMAP_PWA_PAGES_URL_KEY = 'aligent_sitemap/general/pwa_pages_url_key';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serializer
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $serializer
    ) {
    }

    /**
     * Sitemap customization for the default sitemap enabled check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSitemapCustomizationEnabled(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Enabled CMS Page Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCmsPageExcludeEnabled(int $storeId = null): bool
    {
        if (!$this->isSitemapCustomizationEnabled()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_CMS_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Enabled Category Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCategoryExcludeEnabled(int $storeId = null): bool
    {
        if (!$this->isSitemapCustomizationEnabled()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Enabled Product Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isProductExcludeEnabled(int $storeId = null): bool
    {
        if (!$this->isSitemapCustomizationEnabled()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Enabled Product Images Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isProductImagesExcludeEnabled(int $storeId = null): bool
    {
        if (!$this->isSitemapCustomizationEnabled()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT_IMAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Include PWA Pages to Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isPwaPagesInclude(int $storeId = null): bool
    {
        if (!$this->isSitemapCustomizationEnabled()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_INCLUDE_PWA_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Sitemap Base Url
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSitemapBaseUrl(int $storeId = null): string
    {
        if (!$this->isSitemapCustomizationEnabled()) {
            return "";
        }
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SITEMAP_BASE_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get PWA Pages Url Key
     *
     * @param int|null $storeId
     * @return array
     */
    public function getPwaPagesUrlKey(int $storeId = null): array
    {
        $pwaPagesUrlKey = [];
        $pwaPagesUrlKeyConfigValue = $this->scopeConfig
            ->getValue(
                self::CONFIG_XML_PATH_SITEMAP_PWA_PAGES_URL_KEY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        if ($pwaPagesUrlKeyConfigValue !== null) {
            $urlKeys = $this->serializer->unserialize($pwaPagesUrlKeyConfigValue);
            foreach ($urlKeys as $urlKey) {
                $pwaPagesUrlKey[] = $urlKey['url_key'];
            }
        }
        return $pwaPagesUrlKey;
    }
}
