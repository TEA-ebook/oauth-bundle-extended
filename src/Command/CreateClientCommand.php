<?php

declare(strict_types=1);

namespace TeaEbook\Oauth2BundleExtended\Command;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Ramsey\Uuid\FeatureSet;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TeaEbook\Oauth2BundleExtended\Entity\ClientName;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

/**
 * Code copied from vendor/trikoder/oauth2-bundle/Command/CreateClientCommand.php because original class is final...
 */
class CreateClientCommand extends Command
{
    protected static $defaultName = 'trikoder:oauth2:create-client';

    /** @var ClientManagerInterface */
    protected $clientManager;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(ClientManagerInterface $clientManager, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new oAuth2 client')
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.',
                []
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types.',
                []
            )
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed scope for client. Use this option multiple times to set multiple scopes.',
                []
            )
            // name was added as an additional input option
            ->addOption(
                'name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Sets the client human readable name (only used for internal reference).'
            )
            ->addArgument(
                'identifier',
                InputArgument::OPTIONAL,
                'The client identifier'
            )
            ->addArgument(
                'secret',
                InputArgument::OPTIONAL,
                'The client secret'
            )
            ->addOption(
                'public',
                null,
                InputOption::VALUE_NONE,
                'Create a public client.'
            )
            ->addOption(
                'allow-plain-text-pkce',
                null,
                InputOption::VALUE_NONE,
                'Create a client who is allowed to use plain challenge method for PKCE.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $client = $this->buildClientFromInput($input);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }

        $this->clientManager->save($client);

        try {
            $clientName = $this->buildClientNameFromInput($input, $client);
        } catch (InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }

        $this->entityManager->persist($clientName);
        $this->entityManager->flush();

        $io->success('New oAuth2 client created successfully.');

        // following headers were added: 'Name', 'Grants' and 'Scopes'
        $headers = ['Identifier', 'Secret', 'Name', 'Grants', 'Scopes'];
        // there were added 'Name', 'Grants' and 'Scopes' values to table
        $rows = [
            [
                $client->getIdentifier(),
                $client->getSecret(),
                $clientName->getName(),
                implode(' ', $client->getGrants()),
                implode(' ', $client->getScopes()),
            ],
        ];
        $io->table($headers, $rows);

        return 0;
    }

    private function buildClientFromInput(InputInterface $input): Client
    {
        $identifier = $input->getArgument('identifier');
        if (null === $identifier) {
            $uuidFactory = new UuidFactory(new FeatureSet(false, false, false, true));
            Uuid::setFactory($uuidFactory);

            // use for identifier Uuid::uuid4() instead of hash('md5', random_bytes(16))
            $identifier = Uuid::uuid4()->toString();
        }

        $isPublic = $input->getOption('public');

        if (null !== $input->getArgument('secret') && $isPublic) {
            throw new InvalidArgumentException('The client cannot have a secret and be public.');
        }

        // use for secret bin2hex(random_bytes(20)) instead of hash('sha512', random_bytes(32))
        $secret = $isPublic ? null : $input->getArgument('secret') ?? bin2hex(random_bytes(20));

        $client = new Client($identifier, $secret);
        $client->setActive(true);
        $client->setAllowPlainTextPkce($input->getOption('allow-plain-text-pkce'));

        $redirectUris = array_map(
            static function (string $redirectUri): RedirectUri { return new RedirectUri($redirectUri); },
            $input->getOption('redirect-uri')
        );
        $client->setRedirectUris(...$redirectUris);

        $grants = array_map(
            static function (string $grant): Grant { return new Grant($grant); },
            $input->getOption('grant-type')
        );
        $client->setGrants(...$grants);

        $scopes = array_map(
            static function (string $scope): Scope { return new Scope($scope); },
            $input->getOption('scope')
        );
        $client->setScopes(...$scopes);

        return $client;
    }

    protected function buildClientNameFromInput(InputInterface $input, Client $client): ClientName
    {
        return new ClientName($client, $input->getOption('name'));
    }
}
