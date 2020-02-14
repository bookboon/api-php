<?php

namespace Bookboon\Api\Client;
use Bookboon\Api\Client\Oauth\OauthGrants;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class OauthClientTest
 * @package Client
 * @group client
 * @group oauth
 */
class OauthClientTest extends \PHPUnit_Framework_TestCase
{
    public function testClientCredentialsGrantSuccessful()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
        $result = $client->requestAccessToken(array(), OauthGrants::CLIENT_CREDENTIALS);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $result);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiAuthenticationException
     */
    public function testClientCredentialsGrantUnsuccessful()
    {
        $client = new OauthClient(\Helpers::getApiId(), "BAD SECRET", new Headers(), array("basic"));
        $result = $client->requestAccessToken(array(), OauthGrants::CLIENT_CREDENTIALS);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $result);
    }

    public function testAuthorizationCodeUrlWithAppUserId()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"), null, null, 9999);
        $result = $client->getAuthorizationUrl();

        parse_str(parse_url($result)['query'], $redirectData);

        $this->assertEquals(9999, $redirectData['app_user_id']);
        $this->assertEquals(9999, $client->getAppUserId());
    }

    /**
     * @return string
     */
    public function testAuthorizationCodeUrlSuccessful()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"), null, 'http://subsites-local.bookboon.com/skeleton/web/exam/authorize');
        $result = $client->getAuthorizationUrl();
        $this->assertStringStartsWith('https://bookboon.com/login/authorize?', $result);

        return $result;
    }

    private function getLocationHeaderFromBody($body)
    {
        $bodyArray = explode("\r\n", $body);
        foreach ($bodyArray as $line) {
            if (strcasecmp("location:", substr($line, 0, 9)) === 0) {
                return substr($line, 10);
            }
        }
        return false;
    }
    /**
     * @depends testAuthorizationCodeUrlSuccessful
     * @param $url
     * @return mixed
     */
    public function testAuthorizationCodeUrlRequestSuccessful($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $body = curl_exec($curl);
        $redirectUrl = $this->getLocationHeaderFromBody($body);

        //parse_str(parse_url($redirectUrl)['query'], $redirectData);
        //$code = $redirectData['code'];

        $this->assertEquals(302, curl_getinfo($curl, CURLINFO_HTTP_CODE));
        //$this->assertNotEmpty($code);

//        return $code;
    }

//    /**
//     * @depends testAuthorizationCodeUrlRequestSuccessful
//     * @param $code
//     * @return AccessToken
//     */
//    public function testAuthorizationCodeTokenSuccessful($code)
//    {
//        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
//        $result = $client->requestAccessToken(array("code" => $code), OauthGrants::AUTHORIZATION_CODE);
//        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $result);
//
//        return $result;
//    }

    /**
     * @depends testAuthorizationCodeTokenSuccessful
     * @param AccessToken $accessToken
     */
    public function testAuthorizationCodeTokenRefreshSuccessful(AccessToken $accessToken)
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
        $result = $client->refreshAccessToken($accessToken);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $result);
    }

    /**
     * @expectedException \Bookboon\Api\Exception\ApiInvalidStateException
     */
    public function testStateCheckInvalid()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
        $client->isCorrectState("a", "b");
    }

    /**
     * @expectedException \Bookboon\Api\Exception\UsageException
     */
    public function testAuthorizationCodeTokenMissingCode()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
        $result = $client->requestAccessToken(array(), OauthGrants::AUTHORIZATION_CODE);
        $this->assertInstanceOf('League\OAuth2\Client\Token\AccessToken', $result);

        return $result;
    }

    public function testStateCheckValid()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
        $result = $client->isCorrectState("a", "a");
        $this->assertTrue($result);
    }

    public function testGenerateState()
    {
        $client = new OauthClient(\Helpers::getApiId(), \Helpers::getApiSecret(), new Headers(), array("basic"));
        $this->assertNotEmpty($client->generateState());
    }
}
