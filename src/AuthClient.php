<?php

declare(strict_types=1);

namespace IWD\AuthSdk;

use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Клиент универсального сервиса Auth.
 *
 * Токен — персональный JWT системы (аудитория auth). Сервис берёт код системы из claim `sub`.
 * 404 превращается в null там, где вызывающий отличает «нет записи» от ошибки.
 * Остальные 4xx/5xx пробрасываются.
 */
final readonly class AuthClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $token,
    ) {
    }

    /**
     * @param list<string> $roles
     *
     * @return array{joinRequestId: string, confirmToken: string}
     */
    public function requestJoin(string $email, string $password, array $roles): array
    {
        /** @var array{joinRequestId: string, confirmToken: string} $data */
        $data = $this->request('POST', '/join/email/request', [
            'json' => ['email' => $email, 'password' => $password, 'roles' => $roles],
        ]);

        return $data;
    }

    /** @return array<string, mixed> */
    public function confirmJoin(string $confirmToken): array
    {
        return $this->request('POST', '/join/email/confirm', ['json' => ['confirmToken' => $confirmToken]]);
    }

    /** @return list<string> */
    public function registrationMethods(): array
    {
        $data = $this->request('GET', '/registration-methods');
        $methods = $data['methods'] ?? [];
        if (!is_array($methods)) {
            throw new InvalidArgumentException('methods: список строк.');
        }
        $normalized = [];
        foreach ($methods as $method) {
            if (!is_string($method) || '' === $method) {
                throw new InvalidArgumentException('methods: список строк.');
            }
            $normalized[] = $method;
        }

        return $normalized;
    }

    /** @return array<string, mixed> */
    public function authenticate(string $email, string $password): array
    {
        return $this->request('POST', '/authentication/email', [
            'json' => ['email' => $email, 'password' => $password],
        ]);
    }

    /** @return array<string, mixed> */
    public function refresh(string $refreshToken): array
    {
        return $this->request('POST', '/token/refresh', ['json' => ['refreshToken' => $refreshToken]]);
    }

    /** @return array<string, mixed>|null */
    public function user(int $userId): ?array
    {
        $this->assertUserId($userId);
        try {
            return $this->request('GET', '/users/' . $userId);
        } catch (ClientExceptionInterface $exception) {
            if (404 === $exception->getResponse()->getStatusCode()) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * @param list<string> $roles
     *
     * @return array<string, mixed>
     */
    public function setRoles(int $userId, array $roles): array
    {
        $this->assertUserId($userId);

        return $this->request('PUT', '/users/' . $userId . '/roles', ['json' => ['roles' => $roles]]);
    }

    /** @return array<string, mixed> */
    public function setGrant(int $userId, string $permissionKey, string $effect): array
    {
        $this->assertUserId($userId);
        if (!in_array($effect, ['allow', 'deny', 'inherit'], true)) {
            throw new InvalidArgumentException('effect: allow, deny или inherit.');
        }

        return $this->request('PUT', '/users/' . $userId . '/grants', [
            'json' => ['permissionKey' => $permissionKey, 'effect' => $effect],
        ]);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $options = []): array
    {
        $options['headers'] = ['Authorization' => 'Bearer ' . $this->token];
        $response = $this->httpClient->request($method, rtrim($this->baseUrl, '/') . '/api/sdk' . $path, $options);
        if (204 === $response->getStatusCode()) {
            return [];
        }

        /** @var array<string, mixed> $data */
        $data = $response->toArray();

        return $data;
    }

    private function assertUserId(int $userId): void
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('userId: положительное число.');
        }
    }
}
