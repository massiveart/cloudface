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

class Dropbox extends CloudProvider
{
    // contains access token which is will be used to authorize the API requests.
    protected $accessToken;

    /**
     * @param array $params ('clientId' => $clientId, 'clientSecret' => $clientSecret, 'authorizationCode' => $authorizationCode)
     * @return string An access token which will be used to authorize the requests.
     */
    public function authorize($params = array())
    {
        try {
            if (isset($params['clientId'], $params['clientSecret'], $params['authorizationCode'])) {
                // client_id: The apps key, found in the App Console.
                $clientId = $params['clientId'];

                // client_secret: The apps secret, found in the App Console.
                $clientSecret = $params['clientSecret'];

                // code (required): The code acquired by directing the user to /oauth2/authorize.
                $authorizationCode = $params['authorizationCode'];

                // The app calls this endpoint to acquire a bearer token once the user has authorized the app
                $urlBase = 'https://api.dropbox.com/1/oauth2/token';

                // grant_type (required): The grant type, which must be authorization_code.
                $grantType = 'authorization_code';

                // redirect_uri: Only used to validate that it matches the original /oauth2/authorize, not used to redirect again.
                // $redirectUri = 'http://localhost/PHP-Space';

                $request = new Request();
                $response = new Response();

                $request->fromUrl($urlBase);
                $request->setMethod('POST');
                $request->setContent('code=' . $authorizationCode . '&grant_type=' . $grantType . '&client_id=' . $clientId . '&client_secret=' . $clientSecret);
                $request->addHeader('Content-Type : application/json');

                $client = new FileGetContents();
                $client->send($request, $response);

                if (!$response->isOk()) {
                    throw new \Exception($response->getStatusCode() . ' ' . $response->getReasonPhrase() . ' ' . $response->getContent());
                } else {
                    $content = json_decode($response->getContent(), true);
                    return $this->accessToken = $content["access_token"];
                }
            } else {
                throw new \Exception('Error: one or more of the required parameters is missing.');
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     *
     */
    public function upload()
    {

    }

    /**
     *
     */
    public function download()
    {

    }
}