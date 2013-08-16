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
    /**
     * @param $clientId
     * @param $clientSecret
     * @param $authorizationCode
     * @return Response
     */
    public function authorize($clientId, $clientSecret, $authorizationCode)
    {

        /*
         * The app calls this endpoint to acquire a bearer token once the user has authorized the app.
         * URL_STRUCTURE : https://accounts.google.com/o/oauth2/token
         * METHOD : POST
         * PARAMETERS :
         *  - code (required): The code acquired by directing the user to /oauth2/authorize.
         *  - grant_type (required): The grant type, which must be authorization_code.
         *  - client_id: The apps key, found in the App Console.
         *  - client_secret: The apps secret, found in the App Console.
         *  - redirect_uri: Only used to validate that it matches the original /oauth2/authorize, not used to redirect again.
         * RETURNS : A JSON-encoded dictionary including an access token (access_token), token type (token_type), and Dropbox user ID (uid). The token type will always be "bearer".
         */


        // The application calls this endpoint to acquire a bearer token once the user has authorized the app
        $urlStructure = 'https://accounts.google.com/o/oauth2/token';

        // code (required): The code acquired by directing the user to /oauth2/authorize.
        $authorizationCode = '4/wGepbthLJ4YVKE2el2Fw_tYjctU3.ovOZtK5c5iMaOl05ti8ZT3bTcyf3gAI';

        // grant_type (required): The grant type, which must be authorization_code.
        $grantType = 'authorization_code';

        // redirect_uri: Only used to validate that it matches the original /oauth2/authorize, not used to redirect again.
        $redirectUri = 'http://localhost/PHP-Space/';

        // client_id: The apps key, found in the App Console.
        $clientId = '565391687116.apps.googleusercontent.com';

        // client_secret: The apps secret, found in the App Console.
        $clientSecret = 'skWCWQsbcruC5jgYzNr5CfzR';


        $request = new Request();
        $response = new Response();

        $request->fromUrl($urlStructure);
        $request->setMethod('POST');

        $request->setContent('code=' . $authorizationCode . '&grant_type=' . $grantType . '&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&redirect_uri=' . $redirectUri);
        $request->addHeader('Content-Type: application/x-www-form-urlencoded');

        $client = new FileGetContents();
        $client->send($request, $response);

        return $response;

    }

    public function upload()
    {
        return "GoogleDrive";
    }

    public function download()
    {

    }
}