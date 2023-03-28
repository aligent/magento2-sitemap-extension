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

class Data implements ConfigReaderInterface
{
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
     * @inheritDoc
     */
    public function isSitemapCustomizationEnabled(?int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isCmsPageExcludeEnabled(?int $storeId): bool
    {
        if (!$this->isSitemapCustomizationEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_CMS_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isCategoryExcludeEnabled(?int $storeId): bool
    {
        if (!$this->isSitemapCustomizationEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isProductExcludeEnabled(?int $storeId): bool
    {
        if (!$this->isSitemapCustomizationEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isProductImagesExcludeEnabled(?int $storeId): bool
    {
        if (!$this->isSitemapCustomizationEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT_IMAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isPwaPagesInclude(?int $storeId): bool
    {
        if (!$this->isSitemapCustomizationEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_XML_PATH_SITEMAP_INCLUDE_PWA_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getSitemapBaseUrl(?int $storeId): string
    {
        if (!$this->isSitemapCustomizationEnabled($storeId)) {
            return "";
        }
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_SITEMAP_BASE_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getPwaPagesUrlKey(?int $storeId): array
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
