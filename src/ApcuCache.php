<?php
namespace rodrigovr\Aop\Cache;

use Exception;

class ApcuCache implements Storage {

    public function __construct()
    {
        if (!apcu_enabled() && php_sapi_name() == 'cli') {
            throw new Exception("apcu is not enabled");
        }
    }

    protected $prefix = 'AopCache/';

    public function save($key, $value, $ttl)
    {
        \apcu_store($this->prefix . $key, $value, $ttl);
    }

    public function load($key)
    {
        $found = false;
        $result = \apcu_fetch($this->prefix . $key, $found);
        if (!$found) {
            return null;
        }
        return $result;
    }
}