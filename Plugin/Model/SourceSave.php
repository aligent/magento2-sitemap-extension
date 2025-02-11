<?php

namespace Aligent\Sitemap\Plugin\Model;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceExtensionInterfaceFactory;
use Aligent\Sitemap\Api\Data\InventorySourceInterface;
class SourceSave
{
    /**
     * @param SourceExtensionInterfaceFactory $extensionFactory
     */
    public function __construct(
        private readonly SourceExtensionInterfaceFactory $extensionFactory
    ) {
    }

    /**
     * Plugin for Get
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     * @return SourceInterface
     */
    public function afterGet(SourceRepositoryInterface $subject, SourceInterface $source)
    {
        $sourceComment = $source->getData(InventorySourceInterface::FIELD_NAME);
        $extensionAttributes = $source->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setShowInSitemap($sourceComment);
        $source->setExtensionAttributes($extensionAttributes);
        return $source;
    }
    /**
     * Plugin for GetList
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceSearchResultsInterface $result
     * @return SourceSearchResultsInterface
     */
    public function afterGetList(SourceRepositoryInterface $subject, SourceSearchResultsInterface $result)
    {
        $products = [];
        $sources = $result->getItems();
        foreach ($sources as $source) {
            $sourceComment = $source->getData(InventorySourceInterface::FIELD_NAME);
            $extensionAttributes = $source->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setShowInSitemap($sourceComment);
            $source->setExtensionAttributes($extensionAttributes);
            $products[] = $source;
        }
        $result->setItems($products);
        return $result;
    }
    /**
     * Plugin for save function
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     * @return SourceInterface[]
     */
    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ) {
        $extensionAttributes = $source->getExtensionAttributes() ?: $this->extensionFactory->create();
        if ($extensionAttributes !== null && $extensionAttributes->getShowInSitemap() !== null) {
            $source->setShowInSitemap($extensionAttributes->getShowInSitemap());
        }
        return [$source];
    }
}
