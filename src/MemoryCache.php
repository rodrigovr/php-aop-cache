<?php

namespace rodrigovr\Aop\Cache;

class MemoryCache implements Storage {

    private $bucket = [];

    public function save($key, $value, $ttl)
    {
        $expires = time() + $ttl;
        $this->bucket[$key] = [ $expires, $value ];
    }

    public function load($key)
    {
        list($expires, $value) = $this->bucket[$key] ?? [ null, null ];

        if (
            $value !== null && 
            $expires !== null && 
            time() < $expires
        ) {
            return $value;
        }
    }
}