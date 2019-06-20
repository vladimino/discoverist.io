<?php

namespace Vladimino\Discoverist\Cache;

class SimpleCache implements CacheInterface
{
    /**
     * @var string
     */
    private $path = '';

    public function get(string $key): ?string
    {
        $this->checkPathInitialisation();
        $fileName = $this->makeKey($key);

        if (!file_exists($fileName)) {
            return null;
        }

        return (string)file_get_contents($fileName);
    }

    public function set(string $key, string $value, int $expiration = 0): void
    {
        $this->checkPathInitialisation();
        $fileName = $this->makeKey($key);

        file_put_contents($fileName, $value);
    }

    public function addServer(string $host, ?int $port = null): void
    {
        $this->path = $host;
    }

    private function makeKey(string $url): string
    {
        return $this->path.preg_replace('/[^a-z0-9]+/', '-', strtolower($url));
    }

    private function checkPathInitialisation(): void
    {
        if (empty($this->path)) {
            throw new \LogicException('Path must be initialised before using SimpleCache');
        }
    }
}
