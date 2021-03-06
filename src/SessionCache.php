<?php

namespace rodrigovr\Aop\Cache;

class SessionCache implements Storage {

    protected $prefix = 'AopCache/';

    public function save($key, $value, $ttl)
    {
        $key = $this->prefix . $key;
        $expires = time() + $ttl;
        $_SESSION[$key] = [ $expires, $value ];
    }

    public function load($key)
    {
        $key = $this->prefix . $key;
        list($expires, $value) = $_SESSION[$key] ?? [ null, null ];

        if ($expires !== null && time() > $expires) {
            unset($_SESSION[$key]);
        } else {
            return $value;
        }
    }
}