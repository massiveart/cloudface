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
use MassiveArt\CloudFace\Exception\FileNotFoundException;
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
     * An array with the required information has to be passed:
     * <code>
     * array('refreshToken' => $refreshToken);
     * </code>
     * If the required information is not set, a MissingParameterException will be thrown.
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     */
    public function authorize($params = array())
    {
        if (isset($params['accessToken'])) {
            $this->accessToken = $params['accessToken'];
            return true;
        } else {
            throw new MissingParameterException('Error: one or more of the required parameters is missing.');
        }
    }

    public function upload()
    {
        $chunkSize = 4194304; // 4 MB
        $httpMthod = 'POST';
        $baseUrl = 'https://api-content.dropbox.com/1/commit_chunked_upload';
        $authorization = 'Bearer 2zwmdlOpLakAAAAAAAAAAT1lNuF2aNvO501xVu4HPLVzdkMXE-p-lytjLLYEfgnf';


        $filename = 'prankvideo';
        $offset = 0;
        $uploadId = null;

        if (file_exists($filename)) {

            while (!feof($filename)) {
                $chunk = file_get_contents($filename, null, null, $offset, $chunkSize);
                list($uploadId, $offset) = $this->sendChunk($chunk, $uploadId, $offset);
            }

            $request = new Request();
            


        } else {
            throw new FileNotFoundException($filename . ' could not be opened for reading.');
        }

    }

    private function sendChunk($chunk, $uploadId, $offset)
    {
        $httpMethod = 'PUT';
        $urlBase = 'https://api-content.dropbox.com/1/chunked_upload';
        // $options = '?overwrite=false';
        $authorization = 'Bearer 2zwmdlOpLakAAAAAAAAAAT1lNuF2aNvO501xVu4HPLVzdkMXE-p-lytjLLYEfgnf';


        $request = new Request();
        $response = new Response();

        $request->fromUrl($urlBase );
        $request->setMethod($httpMethod);
        $request->addHeader('Authorization: ' . $authorization);
        $request->addHeader('Content-Type: application');
        $request->setContent($chunk);
        // $request->addHeader('Content-Length:' . strlen($request->getContent()));

        $client = new FileGetContents();
        $client->send($request, $response);

        $content = json_decode($response->getContent(), true);
        $uploadId = $content['upload_id'];
        $offset = $content['offset'];
        return array($uploadId, $offset);
    }

    public function download()
    {

    }
}