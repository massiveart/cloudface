<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART Webservices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace\Provider;

use Buzz\Client\FileGetContents;
use MassiveArt\CloudFace\Provider\CloudProvider;
use Buzz\Message\Request;
use Buzz\Message\Response;

class GoogleDrive extends CloudProvider
{
    // contains access token which is will be used to authorize the API requests.
    protected $accessToken;

    /**
     * @param array $params ('clientId' => $clientId, 'clientSecret' => $clientSecret, 'authorizationCode' => $authorizationCode).
     * @return string An access token which will be used to authorize the API requests.
     */
    public function authorize($params = array())
    {
        try {
            if (isset($params['clientId'], $params['clientSecret'], $params['authorizationCode'])) {
                // The application calls this endpoint to acquire a bearer token once the user has authorized the app
                $urlBase = 'https://accounts.google.com/o/oauth2/token';

                // code (required): The code acquired by directing the user to /oauth2/authorize.
                $authorizationCode = $params['authorizationCode'];

                // grant_type (required): The grant type, which must be authorization_code.
                $grantType = 'authorization_code';

                // The URI registered with the application.
                $redirectUri = 'http://localhost/PHP-Space/';

                // client_id: The client_id obtained during application registration.
                $clientId = $params['clientId'];

                // client_secret: The client secret obtained during application registration.
                $clientSecret = $params['clientSecret'];


                $request = new Request();
                $response = new Response();

                $request->fromUrl($urlBase);
                $request->setMethod('POST');
                $request->setContent('code=' . $authorizationCode . '&grant_type=' . $grantType . '&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&redirect_uri=' . $redirectUri);
                $request->addHeader('Content-Type: application/x-www-form-urlencoded');

                $client = new FileGetContents();
                $client->send($request, $response);

                if (!$response->isOk()) {
                    throw new \Exception($response->getStatusCode() . ' ' . $response->getReasonPhrase() . ' ' . $response->getContent());
                } else {
                    $content = json_decode($response->getContent(), true);

                    // access_token expires in one hour. You should generate a new access_token by calling refresh_token.
                    // refresh_token never expires and you should store it long term.
                   return $this->accessToken = $content["access_token"];
                }
            } else {
                throw new \Exception('Error: one or more of the required parameters is missing.');
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function upload()
    {
        return "GoogleDrive";
    }

    public function download()
    {

    }
}