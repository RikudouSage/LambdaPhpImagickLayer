<?php

namespace App;

use App\DependencyInjection\CommandLocatorCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles()
    {
        return [];
    }

    /**
     * Loads the container configuration.
     * @param LoaderInterface $loader
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . "/../config/app.yaml");
        $loader->load(__DIR__ . "/../config/services.yaml");
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . "/{$this->getAppKey()}/cache";
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . "/{$this->getAppKey()}/logs";
    }

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CommandLocatorCompilerPass());
    }

    /**
     * Returns app key that will be used to store generated files (e.g. compiled container, logs, etc.)
     *
     * @return string
     */
    private function getAppKey(): string
    {
        return md5(__FILE__);
    }
}
