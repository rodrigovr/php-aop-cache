<?php

namespace rodrigovr\Aop\Cache;

interface Storage {
    public function save(string $key, string $value, int $ttl);
    public function load(string $key);
}