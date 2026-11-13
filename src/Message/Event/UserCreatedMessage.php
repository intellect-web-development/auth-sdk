<?php

declare(strict_types=1);

namespace IWD\AuthSdk\Message\Event;

use Symfony\Component\Messenger\Attribute\AsMessage;

/**
 * Тонкое событие: пользователь создан. Тело пользователя продукт читает по HTTP, если оно нужно.
 * Очередь продукта {slug}.auth, binding key = код Customer.
 */
#[AsMessage(
    transport: 'auth-events-in',
    serializedTypeName: 'auth.event.userCreated',
)]
readonly class UserCreatedMessage
{
    public function __construct(
        public string $userId,
        public string $userUuid,
        public string $integrationSystemCode,
    ) {
    }
}
