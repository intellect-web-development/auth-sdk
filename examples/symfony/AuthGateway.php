<?php

declare(strict_types=1);

namespace App\Common\Auth;

use IWD\AuthSdk\AuthClient;

/**
 * Пример инфраструктурного шлюза. Домен продукта этот класс не импортирует.
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
