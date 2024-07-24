<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Setup\Patch\Data;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddShowInXmlSitemapCategoryAttribute implements DataPatchInterface
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
    public static function getDependencies(): array
    {
        return [];
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
    public function apply(): AddShowInXmlSitemapCategoryAttribute
    {

        $this->moduleDataSetup->startSetup();

        $attributeSetId = $this->eavSetup->getDefaultAttributeSetId(
            CategoryAttributeInterface::ENTITY_TYPE_CODE
        );

        $attributeGroupId = $this->eavSetup->getDefaultAttributeGroupId(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId
        );

        $config = $this->getShowInXmlSitemapCategoryAttributeConfiguration()[self::ATTRIBUTE_CODE];
        $sortOrder = $this->eavSetup->getAttributeSortOrder(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSetId,
            $attributeGroupId
        );
        $config['sort_order'] = $sortOrder;
        $config['group'] = $attributeGroupId;
        $this->eavSetup->addAttribute(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            self::ATTRIBUTE_CODE,
            $config
        );

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * Add show in xml sitemap category attribute
     *
     * @return array[]
     */
    private function getShowInXmlSitemapCategoryAttributeConfiguration(): array
    {
        return [
            self::ATTRIBUTE_CODE => [
                'type' => 'int',
                'label' => 'Show in Sitemap',
                'input' => 'boolean',
                'source' => Boolean::class,
                'required' => false,
                'default' => true,
                'system' => false,
                'user_defined' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        ];
    }
}
