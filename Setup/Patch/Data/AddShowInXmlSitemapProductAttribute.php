<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Zend_Validate_Exception;

class AddShowInXmlSitemapProductAttribute implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'show_in_sitemap';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetup $eavSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetup $eavSetup
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply(): AddShowInXmlSitemapProductAttribute
    {
        $this->moduleDataSetup->startSetup();
        $this->addShowInXmlSitemap();
        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * Add show in xml sitemap product attribute
     *
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    private function addShowInXmlSitemap(): void
    {
        $attributeShowInSitemapConfiguration = [
            'label' => 'Show in Sitemap',
            'type' => 'int',
            'input' => 'boolean',
            'source' => Boolean::class,
            'required' => false,
            'system' => false,
            'default' => true,
            'group' => 'Search Engine Optimization',
            'user_defined' => true,
            'is_searchable' => true,
            'is_filterable' => false,
            'is_filterable_in_search' => false,
            'global' => ScopedAttributeInterface::SCOPE_STORE
        ];

        $this->eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            self::ATTRIBUTE_CODE,
            $attributeShowInSitemapConfiguration
        );
    }
}
