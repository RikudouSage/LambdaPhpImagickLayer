<?php

namespace App\Traits;

trait ConfigTrait
{
    private function getJson(): array
    {
        return json_decode(file_get_contents($this->getConfigPath()), true);
    }

    private function getVersions(): array
    {
        return array_filter(array_keys($this->getJson()), function (string $key) {
            return is_numeric($key);
        });
    }

    private function getRootDir(): string
    {
        return __DIR__ . '/../../..';
    }

    private function getConfigPath(): string
    {
        return $this->getRootDir() . '/config.json';
    }
}
