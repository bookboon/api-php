<?php

use Bookboon\Api\Bookboon;
use Bookboon\Api\Client\BookboonCurlClient;
use Bookboon\Api\Client\BookboonOauthClient;
use Bookboon\Api\Client\ClientFactory;
use Bookboon\Api\Client\Headers;
use Bookboon\Api\Entity\Book;
use Bookboon\Api\Entity\Exam;
use League\OAuth2\Client\Provider\GenericProvider;

require_once '../../vendor/autoload.php';


$bookboon = new Bookboon("ce60b0b656d2aabf", "pEspAphuqUwAdUxeCruwREte5AF4fruH", [], null, "Bookboon\Api\Client\BookboonOauthClient");


    $authorizationUrl = $bookboon->getClient()->getAuthorizationUrl('http://localhost:2323/clientcode.php', ['exams']);

echo $authorizationUrl;

if (isset($_GET['code'])) {
    $bookboon->getClient()->generateAccessToken($_GET['code']);

    $exam = Exam::get($bookboon, '04564e03-0c2a-46f7-b51a-ec52bc1c964b');

    print_r($exam);


}



//else {
//    $accessToken = $bookboon->getClient()->getAccessToken($_GET['code']);
//}


//$bookboon->getClient()->getAccessToken();


//$bookboon = new Bookboon("ce60b0b656d2aabf", "pEspAphuqUwAdUxeCruwREte5AF4fruH");
//session_start();
//
//$provider = new GenericProvider([
//    'clientId'                => 'ce60b0b656d2aabf',    // The client ID assigned to you by the provider
//    'clientSecret'            => 'pEspAphuqUwAdUxeCruwREte5AF4fruH',
//    'redirectUri'             => 'http://localhost:2323/clientcode.php',
//    'urlAuthorize'            => 'http://localhost:2000/api/authorize',
//    'scopes'                  => array('exams'),
//    'urlAccessToken'          => 'http://localhost:2000/api/access_token',
//    'urlResourceOwnerDetails' => 'http://localhost:2000/api/_application'
//]);
//
//if (!isset($_GET['code'])) {
//
//
//    // Fetch the authorization URL from the provider; this returns the
//    // urlAuthorize option and generates and applies any necessary parameters
//    // (e.g. state).
//    $authorizationUrl = $provider->getAuthorizationUrl();
//
//    // Get the state generated for you and store it to the session.
//    $_SESSION['oauth2state'] = $provider->getState();
//
//    print_r($_SESSION['oauth2state']);
////    echo phpinfo();
//    // Redirect the user to the authorization URL.
//   header('Location: ' . $authorizationUrl);
//    exit;
//
//// Check given state against previously stored one to mitigate CSRF attack
//} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
//
//    unset($_SESSION['oauth2state']);
//    exit('Invalid state');
//
//} else {
//
//    try {
//
//        // Try to get an access token using the authorization code grant.r
//        $accessToken = $provider->getAccessToken('authorization_code', [
//            'code' => $_GET['code'],
//        ]);
//
//        // We have an access token, which we may use in authenticated
//        // requests against the service provider's API.
////        echo $accessToken->getToken() . "\n";
////        echo $accessToken->getRefreshToken() . "\n";
////        echo $accessToken->getExpires() . "\n";
////        echo ($accessToken->hasExpired() ? 'expired' : 'not expired') . "\n";
//
//
//        $request = $provider->getAuthenticatedRequest(
//            'GET',
//            'http://localhost:2000/api/exams',
//            $accessToken
//        );
//
//
//        print_r($provider->getResponse($request));
//
//
//
//    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
//
//
//        // Failed to get the access token or user details.
//        exit($e->getMessage());
//
//    }
//
//}
