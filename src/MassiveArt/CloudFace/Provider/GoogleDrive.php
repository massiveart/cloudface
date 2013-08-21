<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART WebServices GmbH
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
 * This is the class you can use to communicate with the Google Drive REST API.
 * This class is a subclass of Provider class.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class GoogleDrive extends CloudProvider
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
     * array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'refreshToken' => $refreshToken);
     * </code>
     * If the required information is not set, it throws an MissingParameterException.
     * If the required information is not valid, it throws an InvalidRequestException.
     *
     * IMPORTANT:
     * Access token expires in about one hour. You should generate a new access_token by calling refresh_token.
     * Refresh token never expires and you should store it long term.
     * The request for an authorization code should include the access_type parameter, where the value of that parameter is offline.
     * To obtain a new access token this way, the application must perform an HTTPs POST to https://accounts.google.com/o/oauth2/token.
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     */
    public function authorize($params = array())
    {
        if (isset($params['clientId'], $params['clientSecret'], $params['refreshToken'])) {
            // http method
            $httpMethod = 'POST';

            // The application calls this endpoint to acquire a bearer access token on-demand.
            $urlBase = 'https://accounts.google.com/o/oauth2/token';

            // refresh_token: The refresh token acquired by directing the user to the consent page where the value of the access_type is offline.
            $refreshToken = $params['refreshToken'];

            // grant_type: The grant type, which must be refresh_token.
            $grantType = 'refresh_token';

            // client_id: The client_id obtained during application registration.
            $clientId = $params['clientId'];

            // client_secret: The client secret obtained during application registration.
            $clientSecret = $params['clientSecret'];


            $request = new Request();
            $response = new Response();

            $request->fromUrl($urlBase);
            $request->setMethod($httpMethod);
            $request->setContent('grant_type=' . $grantType . '&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&refresh_token=' . $refreshToken);
            $request->addHeader('Content-Type: application/x-www-form-urlencoded');

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