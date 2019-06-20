<?php

namespace Vladimino\Discoverist\Cache;

interface CacheInterface
{
    public function get(string $key): ?string;

    public function set(string $key, string $value, int $expiration = 0);

    public function addServer(string $host, ?int $port = null);
}
