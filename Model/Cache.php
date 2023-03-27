<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json as MagentoSerializerJson;

class Cache
{
    private const CACHE_LIFETIME = '864000000';

    /**
     * @param CacheInterface $cacheManager
     * @param MagentoSerializerJson $magentoSerializerJson
     */
    public function __construct(
        private readonly CacheInterface $cacheManager,
        private readonly MagentoSerializerJson $magentoSerializerJson
    ) {
    }

    /**
     * Store Cache Data
     *
     * @param string $key
     * @param string $data
     * @return void
     */
    public function storeCache(string $key, string $data): void
    {
        $unserializedData = $this->magentoSerializerJson->serialize($data);
        $this->cacheManager->save($unserializedData, $key, [], self::CACHE_LIFETIME);
    }

    /**
     * Get the stored cache data by cache key
     *
     * @param string $key
     * @return string
     */
    public function getCache(string $key): string
    {
        $data = $this->cacheManager->load($key);
        if (!$data) {
            return "";
        }
        return (string)$this->magentoSerializerJson->unserialize($data);
    }
}
