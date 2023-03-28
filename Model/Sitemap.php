<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Model;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime as ModelDate;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\Sitemap as MagentoSitemap;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sitemap\Helper\Data as SitemapHelper;
use Aligent\Sitemap\Model\Config\Data as AligentSitemapConfig;

class Sitemap extends MagentoSitemap
{
    private const CACHE_KEY = 'sitemap-base-url';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Escaper $escaper
     * @param SitemapHelper $sitemapData
     * @param Filesystem $filesystem
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param PageFactory $cmsFactory
     * @param ModelDate $modelDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param DateTime $dateTime
     * @param AligentSitemapConfig $aligentSitemapConfig
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DocumentRoot|null $documentRoot
     * @param ItemProviderInterface|null $itemProvider
     * @param SitemapConfigReaderInterface|null $configReader
     * @param SitemapItemInterfaceFactory|null $sitemapItemFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Escaper $escaper,
        SitemapHelper $sitemapData,
        Filesystem $filesystem,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        PageFactory $cmsFactory,
        ModelDate $modelDate,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        DateTime $dateTime,
        private readonly AligentSitemapConfig $aligentSitemapConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DocumentRoot $documentRoot = null,
        ItemProviderInterface $itemProvider = null,
        SitemapConfigReaderInterface $configReader = null,
        SitemapItemInterfaceFactory $sitemapItemFactory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data,
            $documentRoot,
            $itemProvider,
            $configReader,
            $sitemapItemFactory
        );
    }

    /**
     * Override changes for the Magento\Sitemap\Model\Sitemap class
     * Check sitemap_base_url configuration is exists get the base url from configuration ( line 119 - 128)
     * If not exists sitemap_base_url configuration , get the default magento base url (line 140)
     * Get url
     *
     * @param string $url
     * @param string $type
     * @return string
     */
    protected function _getUrl($url, $type = UrlInterface::URL_TYPE_LINK)
    {
        $sitemapBaseUrl = $this->aligentSitemapConfig->getSitemapBaseUrl();
        if ($sitemapBaseUrl) {
            return $sitemapBaseUrl . ltrim($url, '/');
        }
        return $this->_getStoreBaseUrl($type) . ltrim($url, '/');
    }
}
