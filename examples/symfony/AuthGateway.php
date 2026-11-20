<?php

declare(strict_types=1);

namespace IWD\AuthSdk\Examples\Symfony;

use IWD\AuthSdk\AuthClient;

/**
 * Пример инфраструктурного шлюза. В продукте копируйте в App\PlatformProjection\Auth (или свой BC);
 * домен продукта этот класс и SDK не импортирует.
 */
final readonly class AuthGateway
{
    public function __construct(private AuthClient $authClient)
    {
    }

    /** @return array<string, mixed> */
    public function authenticate(string $email, string $password): array
    {
        return $this->authClient->authenticate($email, $password);
    }
}
