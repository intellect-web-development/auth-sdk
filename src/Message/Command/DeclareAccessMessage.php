<?php

declare(strict_types=1);

namespace IWD\AuthSdk\Message\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * Полная замена каталога ролей, ключей и дефолтов ролей Customer.
 * Транспорт продукта объявляется с queues: false.
 */
#[AsMessage(
    transport: 'auth-commands-transport',
    serializedTypeName: 'auth.command.declareAccess',
)]
readonly class DeclareAccessMessage
{
    /**
     * @param list<string> $roles
     * @param list<string> $permissions
     * @param array<string, list<string>> $roleGrants
     */
    public function __construct(
        public string $integrationSystemCode,
        public array $roles,
        public array $permissions,
        public array $roleGrants,
    ) {
    }
}
