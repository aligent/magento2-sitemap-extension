<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Plugin\Model\ResourceModel\Cms\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetUtilityPageIdentifiersInterface;
use Magento\Framework\Exception\LocalizedException;
use Zend_Db_Statement_Exception;
use Aligent\Sitemap\Model\Config\Data as AligentSitemapConfig;
use Magento\Sitemap\Model\ResourceModel\Cms\Page;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\DataObject;

class GetValidCmsPagesForSitemap
{
    /**
     * @param GetUtilityPageIdentifiersInterface $getUtilityPageIdentifiers
     * @param MetadataPool $metadataPool
     * @param AligentSitemapConfig $aligentSitemapConfig
     * @param Page $page
     */
    public function __construct(
        private readonly GetUtilityPageIdentifiersInterface $getUtilityPageIdentifiers,
        private readonly MetadataPool $metadataPool,
        private readonly AligentSitemapConfig $aligentSitemapConfig,
        private readonly Page $page
    ) {
    }

    /**
     * Retrieve valid cms page collection array for sitemap
     *
     * @param Page $subject
     * @param callable $proceed
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function aroundGetCollection(Page $subject, callable $proceed, int $storeId): array
    {
        /**
         *  Override changes for the Magento\Sitemap\Model\ResourceModel\Cms\Page class
         *  Check if the "Exclude CMS Pages from Sitemap" config value (line 54)
         *  If the value is Yes , include the show_in_sitemap = 1  (line 70)
         *  If the value is No , execute the default magento changes (line 55)
         */
        if (!$this->aligentSitemapConfig->isCmsPageExcludeEnabled((int)$storeId)) {
            return $proceed($storeId);
        }
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $subject->getConnection()->select()->from(
            ['main_table' => $subject->getMainTable()],
            [$subject->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $subject->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.show_in_sitemap = 1'
        )->where(
            'main_table.identifier NOT IN (?)',
            $this->getUtilityPageIdentifiers->execute()
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        $pages = [];
        $query = $subject->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $page = $this->prepareObject($row);
            $pages[$page->getId()] = $page;
        }

        return $pages;
    }

    /**
     * Prepare page object
     *
     * @param array $data
     * @return DataObject
     * @throws LocalizedException
     */
    private function prepareObject(array $data): DataObject
    {
        $object = new DataObject();
        $object->setId($data[$this->page->getIdFieldName()]);
        $object->setUrl($data['url']);
        $object->setUpdatedAt($data['updated_at']);
        return $object;
    }
}
