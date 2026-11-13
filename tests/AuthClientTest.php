<?php

declare(strict_types=1);

namespace IWD\AuthSdk\Tests;

use IWD\AuthSdk\AuthClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AuthClientTest extends TestCase
{
    public function testAuthenticatePostsToSdkAndUser404IsNull(): void
    {
        $client = new MockHttpClient([
            function (string $method, string $url): MockResponse {
                self::assertSame('POST', $method);
                self::assertSame('http://auth.test/api/sdk/authentication/email', $url);

                return new MockResponse(json_encode([
                    'accessToken' => 'access',
                    'permissions' => ['demo:read'],
                ], JSON_THROW_ON_ERROR));
            },
            new MockResponse('', ['http_code' => 404]),
        ]);
        $sdk = new AuthClient($client, 'http://auth.test', 'platform-token');

        $session = $sdk->authenticate('user@example.com', 'password1');
        self::assertSame(['demo:read'], $session['permissions']);
        self::assertNull($sdk->user(15));
    }

    public function testSetGrantRejectsUnknownEffect(): void
    {
        $sdk = new AuthClient(new MockHttpClient(), 'http://auth.test', 'token');
        $this->expectException(\InvalidArgumentException::class);
        $sdk->setGrant(1, 'demo:read', 'maybe');
    }
}
