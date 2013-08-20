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
use MassiveArt\CloudFace\Exception\InvalidRequestException;
use MassiveArt\CloudFace\Exception\MissingParameterException;
use MassiveArt\CloudFace\Provider\CloudProvider;
use Buzz\Message\Request;
use Buzz\Message\Response;

/**
 * This is the class with which you can talk to the Dropbox REST API.
 * This class is a subclass of Provider class.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class Dropbox extends CloudProvider
{
    /**
     * Contains the access token which will be used to authorize the API requests.
     * @var string
     */
    protected $accessToken;

    /**
     * Sets the access token for making API requests.
     *
     * An array with the required information has to be passed:
     * <code>
     * array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'authorizationCode' => $authorizationCode);
     * </code>
     * If the required information is not set, it throws an MissingParameterException.
     * If the required information is not valid, it throws an InvalidRequestException.
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     */
    public function authorize($params = array())
    {
        if (isset($params['clientId'], $params['clientSecret'], $params['authorizationCode'])) {
            // http method
            $httpMethod = 'POST';

            // The app calls this endpoint to acquire a bearer token once the user has authorized the app.
            $urlBase = 'https://api.dropbox.com/1/oauth2/token';

            // client_id: The apps key, found in the App Console.
            $clientId = $params['clientId'];

            // client_secret: The apps secret, found in the App Console.
            $clientSecret = $params['clientSecret'];

            // code: The code acquired by directing the user to /oauth2/authorize.
            $authorizationCode = $params['authorizationCode'];

            // grant_type: The grant type, which must be authorization_code.
            $grantType = 'authorization_code';

            // redirect_uri: Only used to validate that it matches the original /oauth2/authorize, not used to redirect again.
            // $redirectUri = 'http://localhost/PHP-Space';

            $request = new Request();
            $response = new Response();

            $request->fromUrl($urlBase);
            $request->setMethod($httpMethod);
            $request->setContent('code=' . $authorizationCode . '&grant_type=' . $grantType . '&client_id=' . $clientId . '&client_secret=' . $clientSecret);
            $request->addHeader('Content-Type : application/json');

            $client = new FileGetContents();
            $client->send($request, $response);

            if (!$response->isOk()) {
                throw new InvalidRequestException($response->getStatusCode() . ' ' . $response->getReasonPhrase() . ' ' . $response->getContent());
            } else {
                $content = json_decode($response->getContent(), true);
                $this->accessToken = $content["access_token"];
                return true;
            }
        } else {
            throw new MissingParameterException('Error: one or more of the required parameters is missing.');
        }
    }

    public function upload()
    {

    }

    public function download()
    {

    }
}