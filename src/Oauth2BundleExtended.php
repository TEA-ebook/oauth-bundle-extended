<?php

declare(strict_types=1);

namespace TeaEbook\Oauth2BundleExtended;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Oauth2BundleExtended extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                [realpath(__DIR__ . '/Resources/config/doctrine/mapping') => __NAMESPACE__ . '\Entity']
            )
        );
    }
}
