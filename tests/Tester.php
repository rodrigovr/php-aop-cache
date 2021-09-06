<?php
namespace rodrigovr\Aop\Cache\Test;

use rodrigovr\Aop\Cache;

class Tester {

    public static function run()
    {
        $cacheTypes = [
            Cache\MemoryCache::class,
            Cache\ApcuCache::class,
            Cache\SessionCache::class
        ];

        $tester = new self;

        $assertions = [
            'consecutiveCallsMustBeEquals',
            'expiresShouldAvoidCachedResult',
            'sizeLimitShouldAvoidCache',
            'shouldCacheManyResults'
        ];

        foreach ($cacheTypes as $type) {
            echo "Testing $type: ";
            $subject = Cache\Decorator::create(new $type)
                       ->attachTo(Tester::class, 'methodToBeCached');
            
            $fail = 0; 
            foreach ($assertions as $function) {
                $subject->expires(2)->limit(1000);
                if ($tester->{$function}($subject)) {
                    echo ".";
                } else {
                    echo "F";
                    $fail++;
                }
            }

            echo $fail > 0 ? ' FAIL' : ' PASS';
            echo "\n";
        }
    }

    public function consecutiveCallsMustBeEquals(Cache\Decorator $subject)
    {
        $a = $this->methodToBeCached(1000000);
        $b = $this->methodToBeCached(1000000);

        return $a === $b;
    }

    public function expiresShouldAvoidCachedResult(Cache\Decorator $subject)
    {
        $a = $this->methodToBeCached(2000000);
        sleep(1 + $subject->expires());
        $b = $this->methodToBeCached(2000000);

        return $a !== $b;
    }

    public function sizeLimitShouldAvoidCache(Cache\Decorator $subject)
    {
        $subject->limit(0);
        $a = $this->methodToBeCached(3000000);
        $b = $this->methodToBeCached(3000000);

        return $a != $b;
    }

    public function shouldCacheManyResults(Cache\Decorator $subject)
    {
        $a = $this->methodToBeCached(0);
        $b = $this->methodToBeCached(4000000);

        $c = $this->methodToBeCached(0);
        $d = $this->methodToBeCached(4000000);

        return $a != $b && $a === $c && $b == $d;
    }

    public function methodToBeCached($param)
    {
        return random_int(0, $param);
    }
}

Tester::run();