<?php
namespace Application\Cache;

use Doctrine\Common\Cache\Cache;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Adapter that exposes the legacy Doctrine\Common\Cache\Cache interface
 * while delegating to a PSR-6 CacheItemPoolInterface (e.g. Symfony ArrayAdapter).
 */
class Psr6ToDoctrineCache implements Cache
{
    private CacheItemPoolInterface $pool;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function fetch($id)
    {
        $item = $this->pool->getItem($this->sanitizeId($id));
        if (! $item->isHit()) {
            return false;
        }
        return $item->get();
    }

    public function contains($id)
    {
        $item = $this->pool->getItem($this->sanitizeId($id));
        return $item->isHit();
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $item = $this->pool->getItem($this->sanitizeId($id));
        $item->set($data);
        if ($lifeTime > 0) {
            $item->expiresAfter((int)$lifeTime);
        }
        return $this->pool->save($item);
    }

    public function delete($id)
    {
        return $this->pool->deleteItem($this->sanitizeId($id));
    }

    public function getStats()
    {
        // Not available for PSR-6 pools; return null as Doctrine allows.
        return null;
    }

    private function sanitizeId($id): string
    {
        // PSR-6 key rules are stricter; convert invalid chars to _
        // and guarantee string type.
        $key = (string)$id;
        return preg_replace('/[^A-Za-z0-9_\.]/', '_', $key);
    }
}