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
use MassiveArt\CloudFace\Exception\FileNotFoundException;
use MassiveArt\CloudFace\Exception\FolderNotFoundException;
use MassiveArt\CloudFace\Exception\InvalidRequestException;
use MassiveArt\CloudFace\Exception\MissingParameterException;
use MassiveArt\CloudFace\Exception\UploadFailedException;
use MassiveArt\CloudFace\Provider\CloudProvider;
use Buzz\Message\Request;
use Buzz\Message\Response;

/**
 * This is the class you can use to communicate with the Dropbox REST API.
 * This class is a subclass of Provider class.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class Dropbox extends CloudProvider
{
    /**
     * Contains the access token which will be used to authorize the API requests.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Maximum size of a chunk in bytes 67108864 = 64MB
     *
     * @const integer
     */
    const CHUNK_SIZE = 67108864;

    /**
     * The maximum size of a file in bytes that can be uploaded in a single request, 157286400 = 150 MB
     *
     * @const integer
     */
    const  FILE_LIMIT_SIZE = 157286400;

    /**
     * Concatenates and returns the access token needed to make API requests.
     *
     * @return string
     */
    private function getAccessToken()
    {
        return 'Bearer ' . $this->accessToken;
    }

    /**
     * Provides information(e.g. access token) that is essentially to make valid requests and access the services.
     *
     * An array with the required information has to be passed:
     * <code>
     * array('refreshToken' => $refreshToken);
     * </code>
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     */
    public function authorize($params = array())
    {
        if (!isset($params['accessToken'])) {
            throw new MissingParameterException('Dropbox\'s access token is missing.');
        } else {
            $this->accessToken = $params['accessToken'];

            return true;
        }
    }


    /**
     * Uploads a file to the given path. Optional parameters can be passed in an array.
     * If the file size is greater than 150MB it will be uploaded in chunks. Otherwise it will be uploaded in a single part.
     *
     * Upload in chunks:
     *  - Sends a PUT request to /chunked_upload with the first chunk of file with upload_id null and offset 0. Doing so
     *    the server returns an upload_id and an offset(representing the number of bytes transferred so far).
     *  - Repeatedly PUTs subsequent chunks using the upload_id(obtained in previous step) and offset.
     *  - After the last chunk, POSTs to /commit_chunked_upload to complete the upload process.
     *
     * Upload in a single part:
     *  - Sends a PUT request to /files_put.
     *
     * Note that /files_put takes the file contents in the request body, but /commit_chunked_upload takes the upload_id.
     *
     * @param $file
     * @param $path
     * @param array $options
     * @return bool|mixed
     * @throws \MassiveArt\CloudFace\Exception\UploadFailedException
     * @throws \MassiveArt\CloudFace\Exception\FileNotFoundException
     */
    public function upload($file, $path, $options = array())
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        if (!isset($options['overwrite'])) {
            $options['overwrite'] = 'false';
        }

        $httpMethod = 'POST';
        $urlBase = 'https://api-content.dropbox.com/1/commit_chunked_upload/dropbox/';

        // The path on Dropbox where the file will be uploaded. If null or '' the file will be uploaded onto the root directory.
        $path = $path . basename($file);

        // Represents the number of bytes transferred so far.
        $offset = 0;

        // The unique ID of the in-progress upload on the server.
        $uploadId = null;

        // Contains both upload id and offset
        $uploadIdAndOffset = array(
            'uploadId' => $uploadId,
            'offset'   => $offset
        );

        if (filesize($file) > self::FILE_LIMIT_SIZE) {
            $file = fopen($file, 'r');
            while ($chunkOfFile = fread($file, self::CHUNK_SIZE)) {
                $params = array(
                    'chunkOfFile' => $chunkOfFile,
                    'uploadId'    => $uploadIdAndOffset['uploadId'],
                    'offset'      => $uploadIdAndOffset['offset']
                );
                $uploadIdAndOffset = $this->uploadChunk($params);
            }

            $requestHeaders = array('Authorization: ' . $this->getAccessToken());
            $requestContent = 'upload_id=' . $uploadIdAndOffset['uploadId'] . '&overwrite=' . $options['overwrite'];
            $params = array(
                'httpMethod' => $httpMethod,
                'urlBase'    => $urlBase . $path,
                'headers'    => $requestHeaders,
                'content'    => $requestContent
            );
            $response = $this->sendRequest($params);

            if (!$response->isOk()) {
                throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
                ), $response->getContent());
            } else {
                return true;
            }
        } else {
            $httpMethod = 'PUT';
            $urlBase = 'https://api-content.dropbox.com/1/files_put/dropbox/';

            // If there is already a file at the specified path, the new file will be automatically renamed.
            $urlParams = '?overwrite=false';

            $requestHeaders = array(
                'Authorization:' . $this->getAccessToken(),
                'Content-Type: application'
            );
            $requestContent = file_get_contents($file);
            $params = array(
                'httpMethod' => $httpMethod,
                'urlBase'    => $urlBase . $path . $urlParams,
                'headers'    => $requestHeaders,
                'content'    => $requestContent
            );

            $response = $this->sendRequest($params);

            if (!$response->isOk()) {
                throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
                ), $response->getContent());
            } else {
                return true;
            }
        }
    }

    /**
     * Uploads a chunk of file and returns the upload id and offset representing the number of bytes transferred so far.
     * The first chunk will be uploaded without setting an upload_id. The default offset for the first chunk is 0.
     * After each chunk has been uploaded, the server returns a new offset.
     *
     * @param array $params
     * @return array
     * @throws \MassiveArt\CloudFace\Exception\UploadFailedException
     */
    private function uploadChunk($params = array())
    {
        $httpMethod = 'PUT';
        $urlBase = 'https://api-content.dropbox.com/1/chunked_upload';
        $urlParams = '';

        if ($params['uploadId'] != null && $params['offset'] != 0) {
            $urlParams = '?upload_id=' . $params['uploadId'] . '&offset=' . $params['offset'];
        }

        $requestHeaders = array(
            'Authorization: ' . $this->getAccessToken(),
            'Content-Type: application'
        );
        $requestContent = $params['chunkOfFile'];
        $params = array(
            'httpMethod' => $httpMethod,
            'urlBase'    => $urlBase . $urlParams,
            'headers'    => $requestHeaders,
            'content'    => $requestContent
        );

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        } else {
            $content = json_decode($response->getContent(), true);
            $uploadId = $content['upload_id'];
            $offset = $content['offset'];

            return array(
                'uploadId' => $uploadId,
                'offset'   => $offset
            );
        }
    }

    /**
     * Sends requests using BUZZ Library.
     *
     * @param array $params
     * @return Response
     */
    private function sendRequest($params = array())
    {
        $urlBase = $params['urlBase'];
        $httpMethod = $params['httpMethod'];
        $headers = $params['headers'];
        $content = isset($params['content']) ? $params['content'] : null;


        $request = new Request();
        $response = new Response();

        $request->fromUrl($urlBase);
        $request->setMethod($httpMethod);
        $request->addHeaders($headers);
        $request->setContent($content);

        $client = new FileGetContents();
        $client->send($request, $response);

        return $response;
    }

    public function download($file, $path)
    {

    }
}
