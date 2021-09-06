<?php
namespace rodrigovr\Aop\Cache;

/**
 * This Decorator can be used to attach caching behavior to foreign methods.
 * 
 * Ex: 
 * $decorator = Cache\Decorator::create(new Cache\MemoryCache)
 *              ->attachTo('TargetClass', 'targetMethod);
 * 
 * The same decorator can be attached to multiple methods on any user-defined
 * classes, but not on built-ins.
 */
class Decorator {
    /* cache duration in seconds */
    private $ttl = 60;
    /* maximum entry size in bytes */
    private $sizeLimit = 1000000;
    /* salt used to rename methods */
    private $salt;
    /* storage backend, set on create() */
    private Storage $cache;

    /**
     * Intercept calls to a given class::method and return
     * a cached response if one is found.
     * The method arguments are serialized and hashed to create an
     * unique cache key.
     * Should only be used on public non-static methods. 
     */
    public function attachTo(string $class, string $method)
    {
        $oldMethod = $method . $this->salt;
        $decorator = $this;

        $closure = function () use ($decorator, $class, $method, $oldMethod) {

            $cache = $decorator->storage();

            $args = func_get_args();
            $key = $class . $method . sha1(serialize($this) . serialize($args));
            $cached = $cache->load($key);
            if ($cached !== null) {
                return unserialize($cached);
            }
            $result = call_user_func_array([$this, $oldMethod], $args);
            $cached = serialize($result);
            if (strlen($cached) <= $decorator->limit()) {
                $cache->save($key, $cached, $decorator->expires());
            }
            return $result;
        };

        \runkit7_method_rename($class, $method, $oldMethod);
        \runkit7_method_add($class, $method, $closure, \RUNKIT7_ACC_PUBLIC, null, null);

        return $this;
    }

    /**
     * Creates a new AOP Cache and makes it easier to chain calls
     * @return self
     */
    public static function create(Storage $storage)
    {
        if (!function_exists('runkit7_method_rename')) {
            throw new \Exception('AOP Cache depends on runkit7');
        }

        $object = new self;
        $object->cache = $storage;
        $object->salt = '___' . random_int(1, 9999999);
        return $object;
    }

    /**
     * sets or gets the cache expiration time for each cached entry
     * @return int|self
     */
    public function expires($seconds = null)
    {
        if ($seconds === null) {
            return $this->ttl;
        }
        $this->ttl = $seconds;
        return $this;
    }

    /**
     * sets or gets the maximum entry size.
     * any return value above this limit won't be cached
     * @return int|self
     */
    public function limit($bytes = null)
    {
        if ($bytes === null) {
            return $this->sizeLimit;
        }
        $this->sizeLimit = $bytes;
        return $this;
    }

    public function storage(): Storage
    {
        return $this->cache;
    }
}