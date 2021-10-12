<?php

declare(strict_types=1);

namespace TeaEbook\Oauth2BundleExtended\Entity;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;

/**
 * Client entity is extended by creating a dedicated table for client name
 */
class ClientName
{
    /** @var int|null */
    protected $id;

    /** @var Client|null */
    protected $client;

    /** @var string|null */
    protected $name;

    public function __construct(Client $client, string $name = null)
    {
        $this->client = $client;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
