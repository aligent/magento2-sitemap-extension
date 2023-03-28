<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Model\Config;

interface ConfigReaderInterface
{
    public const CONFIG_XML_PATH_SITEMAP_ENABLED = 'aligent_sitemap/general/enabled';
    public  const CONFIG_XML_PATH_SITEMAP_EXCLUDE_CMS_PAGE = 'aligent_sitemap/general/exclude_cms_page';
    public  const CONFIG_XML_PATH_SITEMAP_EXCLUDE_CATEGORY = 'aligent_sitemap/general/exclude_category';
    public  const CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT = 'aligent_sitemap/general/exclude_product';
    public  const CONFIG_XML_PATH_SITEMAP_EXCLUDE_PRODUCT_IMAGES = 'aligent_sitemap/general/exclude_product_images';
    public  const CONFIG_XML_PATH_SITEMAP_INCLUDE_PWA_PAGES = 'aligent_sitemap/general/include_pwa_pages';
    public  const CONFIG_XML_PATH_SITEMAP_BASE_URL = 'aligent_sitemap/general/sitemap_base_url';
    public  const CONFIG_XML_PATH_SITEMAP_PWA_PAGES_URL_KEY = 'aligent_sitemap/general/pwa_pages_url_key';

    /**
     * Sitemap customization for the default sitemap enabled check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isSitemapCustomizationEnabled(?int $storeId): bool;

    /**
     * Enabled CMS Page Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCmsPageExcludeEnabled(?int $storeId): bool;

    /**
     * Enabled Category Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCategoryExcludeEnabled(?int $storeId): bool;

    /**
     * Enabled Product Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isProductExcludeEnabled(?int $storeId): bool;

    /**
     * Enabled Product Images Exclude from Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isProductImagesExcludeEnabled(?int $storeId): bool;

    /**
     * Include PWA Pages to Sitemap Xml check
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isPwaPagesInclude(?int $storeId): bool;

    /**
     * Get Sitemap Base Url
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSitemapBaseUrl(?int $storeId): string;

    /**
     * Get PWA Pages Url Key
     *
     * @param int|null $storeId
     * @return array
     */
    public function getPwaPagesUrlKey(?int $storeId): array;
}
