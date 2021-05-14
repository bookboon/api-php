<?php

namespace Bookboon\Api\Client;

use Bookboon\Api\Client\Oauth\OauthGrants;
use Bookboon\Api\Exception\ApiAuthenticationException;
use Bookboon\Api\Exception\ApiInvalidStateException;
use Bookboon\Api\Exception\UsageException;
use Helpers\Helpers;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class OauthClientTest
 * @package Client
 * @group client
 * @group oauth
 */
class OauthClientTest extends TestCase
{
    public function testClientCredentialsGrantSuccessful() : void
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"]
        );
        $result = $client->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);
        self::assertInstanceOf(AccessToken::class, $result);
    }

    public function testClientCredentialsGrantUnsuccessful() : void
    {
        $this->expectException(ApiAuthenticationException::class);

        $client = new OauthClient(
            Helpers::getApiId(),
            "BAD SECRET",
            new Headers(),
            ["basic"]
        );

        $result = $client->requestAccessToken([], OauthGrants::CLIENT_CREDENTIALS);
        self::assertInstanceOf(AccessToken::class, $result);
    }

    public function testAuthorizationCodeUrlWithAppUserId() : void
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"],
            null,
            null,
            9999
        );

        $result = $client->getAuthorizationUrl();

        parse_str(parse_url($result)['query'], $redirectData);

        self::assertEquals(9999, $redirectData['act']);
        self::assertEquals(9999, $client->getAct());
    }

    public function testAuthorizationCodeUrlSuccessful() : string
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"],
            null,
            "http://subsites-local.bookboon.com/skeleton/web/exam/authorize"
        );

        $result = $client->getAuthorizationUrl([
            'act' => 'rj7Hq2f9d4n59YZ5',
            'response_type' => 'custom_flow'
        ]);

        self::assertStringStartsWith("https://bookboon.com/login/authorize?", $result);

        return $result;
    }

    private function getLocationHeaderFromBody($body) : string
    {
        $bodyArray = explode("\r\n", $body);
        foreach ($bodyArray as $line) {
            if (strcasecmp("location:", substr($line, 0, 9)) === 0) {
                return substr($line, 10);
            }
        }
        return '';
    }
    /**
     * @depends testAuthorizationCodeUrlSuccessful
     * @param $url
     * @return string
     */
    public function testAuthorizationCodeUrlRequestSuccessful($url) : string
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $body = curl_exec($curl);
        $redirectUrl = $this->getLocationHeaderFromBody($body);

        parse_str(parse_url($redirectUrl)['query'], $redirectData);
        $code = $redirectData['code'];


        self::assertEquals(302, curl_getinfo($curl, CURLINFO_HTTP_CODE));
        self::assertNotEmpty($code);

        return $code;
    }

    /**
     * @depends testAuthorizationCodeUrlRequestSuccessful
     * @param $code
     * @return AccessTokenInterface
     */
    public function testAuthorizationCodeTokenSuccessful($code) : AccessTokenInterface
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"],
            null,
            "http://subsites-local.bookboon.com/skeleton/web/exam/authorize"
        );
        $result = $client->requestAccessToken(["code" => $code], OauthGrants::AUTHORIZATION_CODE);

        self::assertInstanceOf(AccessToken::class, $result);

        return $result;
    }

    /**
     * @depends testAuthorizationCodeTokenSuccessful
     * @param AccessToken $accessToken
     */
    public function testAuthorizationCodeTokenRefreshSuccessful(AccessToken $accessToken) : void
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"]
        );
        $result = $client->refreshAccessToken($accessToken);
        self::assertInstanceOf(AccessToken::class, $result);
    }

    public function testStateCheckInvalid() : void
    {
        $this->expectException(ApiInvalidStateException::class);
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"]
        );
        $client->isCorrectState("a", "b");
    }

    public function testAuthorizationCodeTokenMissingCode() : AccessTokenInterface
    {
        $this->expectException(UsageException::class);
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"]
        );
        $result = $client->requestAccessToken([], OauthGrants::AUTHORIZATION_CODE);
        self::assertInstanceOf(AccessToken::class, $result);

        return $result;
    }

    public function testStateCheckValid() : void
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"]
        );
        $result = $client->isCorrectState("a", "a");
        self::assertTrue($result);
    }

    public function testGenerateState() : void
    {
        $client = new OauthClient(
            Helpers::getApiId(),
            Helpers::getApiSecret(),
            new Headers(),
            ["basic"]
        );
        self::assertNotEmpty($client->generateState());
    }
}