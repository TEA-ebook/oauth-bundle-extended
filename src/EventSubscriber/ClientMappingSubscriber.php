<?php

declare(strict_types=1);

namespace TeaEbook\Oauth2BundleExtended\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\{
    Event\LoadClassMetadataEventArgs,
    Events
};
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

class ClientMappingSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if ($event->getClassMetadata()->name === Client::class) {
            // previous identifier length was set to 32, we are overriding its length to be 36
            $event->getClassMetadata()->setAttributeOverride('identifier', ['length' => 36]);
        }
    }
}
