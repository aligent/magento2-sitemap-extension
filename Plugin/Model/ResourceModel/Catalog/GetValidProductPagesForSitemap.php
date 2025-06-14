<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Plugin\Model\ResourceModel\Catalog;

use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;
use Aligent\Sitemap\Model\Config\Data as AligentSitemapConfig;
use Magento\Sitemap\Model\ResourceModel\Catalog\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Sitemap\Helper\Data as SitemapData;

class GetValidProductPagesForSitemap
{
    /**
     * @var Select
     */
    private $select = null;

    /**
     * @var array
     */
    private $attributesCache = null;

    /**
     * Product constructor.
     *
     * @param AligentSitemapConfig $aligentSitemapConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductResource $productResource
     * @param Visibility $productVisibility
     * @param Status $productStatus
     * @param SitemapData $sitemapData
     * @param UrlBuilder $urlBuilder
     * @param Gallery $mediaGalleryResourceModel
     * @param ReadHandler $mediaGalleryReadHandler
     */
    public function __construct(
        private readonly AligentSitemapConfig $aligentSitemapConfig,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductResource $productResource,
        private readonly Visibility $productVisibility,
        private readonly Status $productStatus,
        private readonly SitemapData $sitemapData,
        private readonly UrlBuilder $urlBuilder,
        private readonly Gallery $mediaGalleryResourceModel,
        private readonly ReadHandler $mediaGalleryReadHandler
    ) {
    }

    /**
     * Get product collection array
     *
     * @param Product $subject
     * @param callable $proceed
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Zend_Db_Statement_Exception
     */
    public function aroundGetCollection(Product $subject, callable $proceed, int $storeId): array
    {
        /**
         *  Override changes for the Magento\Sitemap\Model\ResourceModel\Catalog\Product class
         *  Check if the "Exclude Product Pages from Sitemap" config value (line 83)
         *  If the value is Yes , include the show_in_sitemap product attribute to join condition (line 112-115)
         *  and show_in_sitemap = 1  (line 126)
         *  If the value is No , execute the default magento changes (line 84)
         */
        if (!$this->aligentSitemapConfig->isProductExcludeEnabled((int)$storeId)) {
            return $proceed($storeId);
        }
        $products = [];

        /* @var $store Store */
        $store = $this->storeManager->getStore($storeId);
        if (!$store) {
            return [];
        }

        /**
         * Get show_in_sitemap attribute details
         */
        $showInSitemapAttributeDetails = $this->getAttribute('show_in_sitemap');

        $urlRewriteMetaDataCondition = '';
        if (!$this->isCategoryProductURLsConfig((int)$storeId)) {
            $urlRewriteMetaDataCondition = ' AND url_rewrite.metadata IS NULL';
        }

        $connection = $subject->getConnection();
        $this->select = $connection->select()->from(
            ['e' => $subject->getMainTable()],
            [$subject->getIdFieldName(), $this->productResource->getLinkField(), 'updated_at']
        )->joinInner(
            ['w' => $subject->getTable('catalog_product_website')],
            'e.entity_id = w.product_id',
            []
        )->joinLeft(
            ['x' => $showInSitemapAttributeDetails['table']],
            'x.attribute_id = '. $showInSitemapAttributeDetails['attribute_id'] .' AND x.row_id = e.row_id',
            ['xml' => 'x.value']
        )->joinLeft(
            ['url_rewrite' => $subject->getTable('url_rewrite')],
            'e.entity_id = url_rewrite.entity_id'
            . ' AND url_rewrite.metadata IS NULL'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', $store->getId())
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE),
            ['url' => 'request_path']
        )->where(
            'w.website_id = ?',
            $store->getWebsiteId()
        )->where('x.value = ?', 1);

        $this->addFilter(
            $subject,
            (int)$store->getId(),
            'visibility',
            $this->productVisibility->getVisibleInSiteIds(),
            'in'
        );
        $this->addFilter(
            $subject,
            (int)$store->getId(),
            'status',
            $this->productStatus->getVisibleStatusIds(),
            'in'
        );

        // Join product images required attributes
        $imageIncludePolicy = $this->sitemapData->getProductImageIncludePolicy($store->getId());
        if (IncludeImage::INCLUDE_NONE != $imageIncludePolicy) {
            $this->joinAttribute($subject, (int)$store->getId(), 'name', 'name');
            if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
                $this->joinAttribute($subject, (int)$store->getId(), 'thumbnail', 'thumbnail');
            } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy) {
                $this->joinAttribute($subject, (int)$store->getId(), 'image', 'image');
            }
        }

        $query = $connection->query($subject->prepareSelectStatement($this->select));
        while ($row = $query->fetch()) {
            $product = $this->prepareProduct($subject, $row, (int)$store->getId());
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Return Use Categories Path for Product URLs config value
     *
     * Override due to getCollection function required this function, also this function is private visibility
     *
     * @param int|null $storeId
     * @return bool
     */
    private function isCategoryProductURLsConfig(int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            HelperProduct::XML_PATH_PRODUCT_URL_USE_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get attribute details by attribute code
     *
     * @param string $attributeCode
     * @return array
     * @throws LocalizedException
     */
    private function getAttribute(string $attributeCode): array
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->productResource->getAttribute($attributeCode);

            $this->attributesCache[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal(),
                'backend_type' => $attribute->getBackendType(),
            ];
        }
        return $this->attributesCache[$attributeCode];
    }

    /**
     * Add attribute to filter
     *
     * Override due to getCollection function required this function, also this function is protected visibility
     *
     * @param Product $subject
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     *
     * @return Select|bool
     * @throws LocalizedException
     */
    private function addFilter(Product $subject, int $storeId, string $attributeCode, mixed $value, string $type = '=')
    {
        if (!$this->select instanceof Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
        }

        $attribute = $this->getAttribute($attributeCode);
        if ($attribute['backend_type'] == 'static') {
            $this->select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->joinAttribute($subject, $storeId, $attributeCode);
            if ($attribute['is_global']) {
                $this->select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $subject->getConnection()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->select->where('(' . $ifCase . ')' . $conditionRule, $value);
            }
        }

        return $this->select;
    }

    /**
     * Join attribute by code
     *
     * Override due to getCollection function required this function, also this function is protected visibility
     *
     * @param Product $subject
     * @param int $storeId
     * @param string $attributeCode
     * @param string|null $column Add attribute value to given column
     *
     * @return void
     * @throws LocalizedException
     */
    private function joinAttribute(Product $subject, int $storeId, string $attributeCode, string $column = null)
    {
        $connection = $subject->getConnection();
        $attribute = $this->getAttribute($attributeCode);
        $linkField = $this->productResource->getLinkField();
        $attrTableAlias = 't1_' . $attributeCode;
        $this->select->joinLeft(
            [$attrTableAlias => $attribute['table']],
            "e.{$linkField} = {$attrTableAlias}.{$linkField}"
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.store_id = ?', Store::DEFAULT_STORE_ID)
            . ' AND ' . $connection->quoteInto($attrTableAlias . '.attribute_id = ?', $attribute['attribute_id']),
            []
        );
        // Global scope attribute value
        $columnValue = 't1_' . $attributeCode . '.value';

        if (!$attribute['is_global']) {
            $attrTableAlias2 = 't2_' . $attributeCode;
            $this->select->joinLeft(
                ['t2_' . $attributeCode => $attribute['table']],
                "{$attrTableAlias}.{$linkField} = {$attrTableAlias2}.{$linkField}"
                . ' AND ' . $attrTableAlias . '.attribute_id = ' . $attrTableAlias2 . '.attribute_id'
                . ' AND ' . $connection->quoteInto($attrTableAlias2 . '.store_id = ?', $storeId),
                []
            );
            // Store scope attribute value
            $columnValue = $subject->getConnection()->getIfNullSql(
                't2_'  . $attributeCode . '.value',
                $columnValue
            );
        }

        // Add attribute value to result set if needed
        if (isset($column)) {
            $this->select->columns(
                [
                    $column => $columnValue
                ]
            );
        }
    }

    /**
     * Prepare product
     *
     * Override due to getCollection function required this function, also this function is protected visibility
     *
     * @param Product $subject
     * @param array $productRow
     * @param int $storeId
     *
     * @return DataObject
     * @throws LocalizedException
     */
    private function prepareProduct(Product $subject, array $productRow, int $storeId): DataObject
    {
        $product = new DataObject();

        $product['id'] = $productRow[$subject->getIdFieldName()];
        if (empty($productRow['url'])) {
            $productRow['url'] = 'catalog/product/view/id/' . $product->getId();
        }
        $product->addData($productRow);
        $this->loadProductImages($subject, $product, $storeId);

        return $product;
    }

    /**
     * Load product images
     *
     * Override due to getCollection function required this function, also this function is protected visibility
     *
     * @param Product $subject
     * @param DataObject $product
     * @param int $storeId
     * @return void
     */
    private function loadProductImages(Product $subject, DataObject $product, int $storeId): void
    {
        $this->storeManager->setCurrentStore($storeId);
        $helper = $this->sitemapData;
        $imageIncludePolicy = $helper->getProductImageIncludePolicy($storeId);

        // Get product images
        $imagesCollection = [];
        if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
            $imagesCollection = $this->getAllProductImages($product, $storeId);
        } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy &&
            $product->getImage() &&
            $product->getImage() != Product::NOT_SELECTED_IMAGE
        ) {
            $imagesCollection = [
                new DataObject(
                    ['url' => $this->getProductImageUrl($product->getImage())]
                ),
            ];
        }

        if ($imagesCollection) {
            // Determine thumbnail path
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $product->getThumbnail() != Product::NOT_SELECTED_IMAGE) {
                $thumbnail = $this->getProductImageUrl($thumbnail);
            } else {
                $thumbnail = $imagesCollection[0]->getUrl();
            }

            /**
             *  Check if the "Exclude Product Images from Sitemap" config value (line 369)
             *  If the value is Yes , restrict product images from sitemap
             *  If the value is No , execute the default magento changes (line 370 - 374)
             */
            if (!$this->aligentSitemapConfig->isProductImagesExcludeEnabled((int)$storeId)) {
                $product->setImages(
                    new DataObject(
                        ['collection' => $imagesCollection, 'title' => $product->getName(), 'thumbnail' => $thumbnail]
                    )
                );
            }
        }
    }

    /**
     * Get all product images
     *
     * Override due to loadProductImages function required this function, also this function is protected visibility
     *
     * @param DataObject $product
     * @param int $storeId
     * @return array
     */
    private function getAllProductImages(DataObject $product, int $storeId): array
    {
        $product->setStoreId($storeId);
        $gallery = $this->mediaGalleryResourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->mediaGalleryReadHandler->getAttribute()->getId()
        );

        $imagesCollection = [];
        if ($gallery) {
            foreach ($gallery as $image) {
                $imagesCollection[] = new DataObject(
                    [
                        'url' => $this->getProductImageUrl($image['file']),
                        'caption' => $image['label'] ? $image['label'] : $image['label_default'],
                    ]
                );
            }
        }

        return $imagesCollection;
    }

    /**
     * Get product image URL from image filename
     *
     * Override due to loadProductImages function required this function, also this function is private visibility
     *
     * @param string $image
     * @return string
     */
    private function getProductImageUrl(string $image): string
    {
        return $this->urlBuilder->getUrl($image, 'product_page_image_large');
    }
}
