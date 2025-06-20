<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Plugin\Model\ResourceModel\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Model\ResourceModel\Catalog\Category;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Aligent\Sitemap\Model\Config\Data as AligentSitemapConfig;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DataObject;

class GetValidCategoryPagesForSitemap
{

    /**
     * @var Select
     */
    private $select = null;

    /**
     * Attribute cache
     *
     * @var array
     */
    private $attributesCache = [];

    /**
     * @param AligentSitemapConfig $aligentSitemapConfig
     * @param StoreManagerInterface $storeManager
     * @param CategoryResource $categoryResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly AligentSitemapConfig $aligentSitemapConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly CategoryResource $categoryResource,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Get category collection array for sitemap xml
     *
     * @param Category $subject
     * @param callable $proceed
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function aroundGetCollection(Category $subject, callable $proceed, int $storeId): array
    {
        /**
         *  Override changes for the Magento\Sitemap\Model\ResourceModel\Catalog\Category class
         *  Check if the "Exclude Category Pages from Sitemap" config value (line 67)
         *  If the value is Yes , include the show_in_sitemap category attribute to join condition (line 110-114)
         *  and show_in_sitemap = 1  (line 117)
         *  If the value is No , execute the default magento changes (line 68)
         */
        if (!$this->aligentSitemapConfig->isCategoryExcludeEnabled((int)$storeId)) {
            return $proceed($storeId);
        }

        $categories = [];

        /* @var $store Store */
        $store = $this->storeManager->getStore($storeId);

        if (!$store) {
            return [];
        }

        $connection = $subject->getConnection();

        /**
         * Get show_in_sitemap attribute details
         */
        $showInSitemapAttributeDetails = $this->getAttributeDetails('show_in_sitemap');

        // phpcs:disable
        $this->select = $connection->select()->from(
            $subject->getMainTable()
        )->where(
            $subject->getIdFieldName() . '=?',
            $store->getRootCategoryId()
        );
        // phpcs:enable
        $categoryRow = $connection->fetchRow($this->select);

        if (!$categoryRow) {
            return [];
        }

        $this->select = $connection->select()->from(
            ['e' => $subject->getMainTable()],
            [$subject->getIdFieldName(), 'updated_at']
        )->joinLeft(
            ['url_rewrite' => $subject->getTable('url_rewrite')],
            'e.entity_id = url_rewrite.entity_id'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', (int)$storeId)
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', CategoryUrlRewriteGenerator::ENTITY_TYPE),
            ['url' => 'request_path']
        )->joinLeft(
            ['x' => $showInSitemapAttributeDetails['table']],
            'x.attribute_id = '. $showInSitemapAttributeDetails['attribute_id'] .' AND x.row_id = e.row_id',
            ['xml' => 'x.value']
        )->where(
            'e.path LIKE ?',
            $categoryRow['path'] . '/%'
        )->where('x.value = ?', 1);

        $this->addFilter($subject, $storeId, 'is_active', 1);

        $query = $connection->query($this->select);
        while ($row = $query->fetch()) {
            $category = $this->prepareCategory($subject, $row);
            $categories[$category->getId()] = $category;
        }

        return $categories;
    }

    /**
     * Get attribute details by attribute code
     *
     * @param string $attributeCode
     * @return array
     * @throws LocalizedException
     */
    private function getAttributeDetails(string $attributeCode): array
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->categoryResource->getAttribute($attributeCode);

            $this->attributesCache[$attributeCode] = [
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal(),
                'backend_type' => $attribute->getBackendType(),
            ];
        }
        $attribute = $this->attributesCache[$attributeCode];

        return $attribute;
    }

    /**
     * Prepare category
     *
     * Override due to getCollection function required this function, also this function is protected visibility
     *
     * @param Category $subject
     * @param array $categoryRow
     * @return DataObject
     * @throws LocalizedException
     */
    private function prepareCategory(Category $subject, array $categoryRow): DataObject
    {
        $category = new DataObject();
        $category->setId($categoryRow[$subject->getIdFieldName()]);
        $categoryUrl = !empty($categoryRow['url']) ? $categoryRow['url'] : 'catalog/category/view/id/' .
            $category->getId();
        $category->setUrl($categoryUrl);
        $category->setUpdatedAt($categoryRow['updated_at']);
        return $category;
    }

    /**
     * Add attribute to filter
     *
     * Override due to getCollection function required this function, also this function is protected visibility
     *
     * @param Category $subject
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Select|bool
     * @throws LocalizedException
     */
    private function addFilter(Category $subject, int $storeId, string $attributeCode, mixed $value, string $type = '=')
    {
        $meta = $this->metadataPool->getMetadata(CategoryInterface::class);
        $linkField = $meta->getLinkField();

        if (!$this->select instanceof Select) {
            return false;
        }

        $attribute = $this->getAttributeDetails($attributeCode);

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

        if ($attribute['backend_type'] == 'static') {
            $this->select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->select->join(
                ['t1_' . $attributeCode => $attribute['table']],
                'e.' . $linkField . ' = t1_' . $attributeCode . '.' . $linkField .
                ' AND t1_' . $attributeCode . '.store_id = 0',
                []
            )->where(
                't1_' . $attributeCode . '.attribute_id=?',
                $attribute['attribute_id']
            );

            if ($attribute['is_global']) {
                $this->select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $subject->getConnection()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->select->joinLeft(
                    ['t2_' . $attributeCode => $attribute['table']],
                    $subject->getConnection()->quoteInto(
                        't1_' .
                        $attributeCode .
                        '.' . $linkField . ' = t2_' .
                        $attributeCode .
                        '.' . $linkField . ' AND t1_' .
                        $attributeCode .
                        '.attribute_id = t2_' .
                        $attributeCode .
                        '.attribute_id AND t2_' .
                        $attributeCode .
                        '.store_id=?',
                        $storeId
                    ),
                    []
                )->where(
                    '(' . $ifCase . ')' . $conditionRule,
                    $value
                );
            }
        }

        return $this->select;
    }
}
