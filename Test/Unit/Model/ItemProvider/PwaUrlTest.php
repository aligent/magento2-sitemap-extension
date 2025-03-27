<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Test\Unit\Model\ItemProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Aligent\Sitemap\Model\ItemProvider\PwaUrl;
use Aligent\Sitemap\Model\Config\ConfigReaderInterface as AligentSitemapConfigReaderInterface;

class PwaUrlTest extends TestCase
{
    /**
     * test for getItems method
     */
    public function testGetItems(): void
    {
        $configReaderMock = $this->getConfigReaderMock();
        $itemFactoryMock = $this->getItemFactoryMock();
        $aligentSitemapConfigMock = $this->getAligentSitemapConfigMock();
        $resolver = new PwaUrl($configReaderMock, $itemFactoryMock, $aligentSitemapConfigMock);
        $items = $resolver->getItems(1);

        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertSame('daily', $item->getChangeFrequency());
            $this->assertSame('1.0', $item->getPriority());
        }
    }

    /**
     * @return SitemapItemInterfaceFactory|MockObject
     */
    private function getItemFactoryMock(): SitemapItemInterfaceFactory|MockObject
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);

                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @return ConfigReaderInterface|MockObject
     */
    private function getConfigReaderMock(): MockObject|ConfigReaderInterface
    {
        $configReaderMock = $this->getMockForAbstractClass(ConfigReaderInterface::class);
        $configReaderMock->expects($this->any())
            ->method('getPriority')
            ->willReturn('1.0');
        $configReaderMock->expects($this->any())
            ->method('getChangeFrequency')
            ->willReturn('daily');

        return $configReaderMock;
    }

    /**
     * @return AligentSitemapConfigReaderInterface|MockObject
     */
    private function getAligentSitemapConfigMock(): MockObject|AligentSitemapConfigReaderInterface
    {
        $aligentSitemapConfigMock = $this->getMockForAbstractClass(
            AligentSitemapConfigReaderInterface::class
        );
        $aligentSitemapConfigMock->expects($this->any())
            ->method('isPwaPagesInclude')
            ->with(1)
            ->willReturn(true);
        $aligentSitemapConfigMock->expects($this->any())
            ->method('getPwaPagesUrlKey')
            ->with(1)
            ->willReturn(['test-url-key','test-url-key-2']);

        return $aligentSitemapConfigMock;
    }
}
