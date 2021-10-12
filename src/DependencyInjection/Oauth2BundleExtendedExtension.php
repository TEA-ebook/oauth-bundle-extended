<?php

declare(strict_types=1);

namespace TeaEbook\Oauth2BundleExtended\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Extension\Extension,
    Loader\YamlFileLoader
};

final class Oauth2BundleExtendedExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
