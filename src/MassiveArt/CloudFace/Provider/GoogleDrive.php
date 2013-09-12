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
 * This is the class with which you can talk to the Google Drive REST API.
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
        if (!isset($params['clientId'])) {
            throw new MissingParameterException('clientId');
        } elseif (!isset($params['clientSecret'])) {
            throw new MissingParameterException('clientSecret');
        } elseif (!isset($params['refreshToken'])) {
            throw new MissingParameterException('refreshToken');
        } else {
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

            // type of file
            $mimeType = 'application/x-www-form-urlencoded';

            $requestHeaders = array('Authorization: ' . $this->getAccessToken(), 'Content-Type: ' . $mimeType);
            $requestContent =
                'grant_type=' . $grantType . '&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&refresh_token=' . $refreshToken;
            $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                            'content'    => $requestContent);

            $response = $this->sendRequest($params);

            if (!$response->isOk()) {
                throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
                ), $response->getContent());
            } else {
                $content = json_decode($response->getContent(), true);
                $this->accessToken = $content["access_token"];

                return true;
            }
        }
    }

    /**
     * Uploads a file to the given path. Optional parameters can be passed in an array.
     * Uses curl to make the request for getting the resumable upload id. The Buzz library does not work for this request.
     *
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
        // the endpoint for initiating a resumable upload
        $urlBase = 'https://www.googleapis.com/upload/drive/v2/files?uploadType=resumable';

        // The path on Google Drive where the file will be uploaded. If '' given, the file will be uploaded onto the root directory.
        $path = $this->getParentFolder($path);
        // Contains the unique id of folder in which the file will be uploaded. The kind parameter is always "drive#parentReference"
        $path = array("id" => $path, "kind" => "drive#parentReference");
        $path = json_encode($path);
        $path = '[' . $path . ']';

        // Gets file information
        $fileInfo = new \finfo(FILEINFO_MIME);
        $mimeType = $fileInfo->file($file);
        $fileSize = filesize($file);
        $fileName = basename($file);

        // Metadata which is optionally and if any presences it will be sent in the body of the request that gets the upload id.
        $metaData = array("title" => "$fileName", "parents" => json_decode($path));
        $metaData = json_encode($metaData);

        // Initiate curl handle to get the resumable upload id
        $ch = curl_init();
        $requestHeaders = array('Authorization: ' . $this->getAccessToken(), 'Content-Length: ' . strlen($metaData),
                                'X-Upload-Content-Type: ' . $mimeType, 'Content-Type: application/json');

        curl_setopt($ch, CURLOPT_URL, $urlBase);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $metaData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlResponse = curl_exec($ch);
        curl_close($ch);
        $response = $this->parseCurlResponseHeader($curlResponse);

        // The value of the Location header is used as the HTTP endpoint for doing the actual file upload.
        $sessionUri = $response['Location'];

        // Upload the file by sending a PUT request to the session URI obtained in the previous step.
        $requestHeaders = array('Authorization: ' . $this->getAccessToken(), 'Content-Length: ' . $fileSize,
                                'Content-Type: ' . $mimeType);
        $requestContent = file_get_contents($file);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $sessionUri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlResponse = curl_exec($ch);
        curl_close($ch);
        $response = $this->parseCurlResponseHeader($curlResponse);
        $httpCode = $this->parseHttpCodeLine($response['Http-Code-Line']);

        if ($httpCode != 200 and $httpCode != 201) {
            $reason = 'Your upload request is terminated before receiving a valid response';
            $content = 'Retry the upload process or try "resumeInterruptedUpload" function';
            throw new UploadFailedException($httpCode, $reason, $content);
        } else {
            return true;
        }
    }

    /**
     * Returns the unique id of the folder in which the file will be uploaded.
     *
     * @param $path
     * @return mixed
     * @throws FolderNotFoundException
     */
    protected function getParentFolder($path)
    {
        $explodedPath = explode('/', ltrim($path, '/'));
        $info = json_decode($this->getGoogleDriveAccountInfo()->getContent());
        $parentsId = $info->{'rootFolderId'};

        if ($path == '') {
            return $parentsId;
        }

        foreach ($explodedPath as $folder) {
            $query = urlencode(
                "title = '$folder' and '$parentsId' in parents and mimeType = 'application/vnd.google-apps.folder'"
            );
            $response = $this->getFiles('?q=' . $query);
            $response = json_decode($response->getContent(), true);
            if (count($response['items']) === 0) {
                throw new FolderNotFoundException($path);
            }
            $parentsId = $response['items'][0]['id'];
        }

        return $parentsId;
    }

    /**
     * Parses the given HttpCodeLine and Returns the http code.
     *
     * @param $httpCodeLine
     * @return mixed
     */
    protected function parseHttpCodeLine($httpCodeLine)
    {
        $explodedHttpCodeLine = explode(' ', $httpCodeLine);

        return $explodedHttpCodeLine[1];
    }

    /**
     * Returns the basic information (user information and settings) about the Google Drive Account.
     *
     * @return Response
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function getGoogleDriveAccountInfo()
    {
        $httpMethod = 'GET';
        $urlBase = 'https://www.googleapis.com/drive/v2/about';

        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $requestContent = null;
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        } else {
            return $response;
        }
    }

    /**
     * Lists the user's files. Accepts a query string for searching files.
     *
     * @param $query
     * @return Response
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function getFiles($query)
    {
        $httpMethod = 'GET';
        $urlBase = 'https://www.googleapis.com/drive/v2/files' . $query;
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        } else {
            return $response;
        }
    }

    /**
     * Resumes an upload process after a communication failure has interrupted the flow of data.
     * Finds out how much data is already successfully sent and then resumes the upload starting from that point.
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\UploadFailedException
     */
    public function resumeInterruptedUpload($params = array())
    {
        // Request the upload status
        $ch = curl_init();
        $requestHeaders = array('Authorization: ' . $this->getAccessToken(), 'Content-Length: 0',
                                'Content-Range: bytes */' . $params['fileSize']);

        curl_setopt($ch, CURLOPT_URL, $params['sessionUri']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlResponse = curl_exec($ch);
        curl_close($ch);

        // Extract the number of bytes uploaded so far from the response.
        $response = $this->parseCurlResponseHeader($curlResponse);
        $responseRangeLine = explode('-', $responseRangeLine = $response['Range']);
        // $underRange = $responseRangeLine[0];
        $upperRange = $responseRangeLine[1];

        // Resume the upload from the point where it left off
        $ch = curl_init();
        $requestContent = file_get_contents($params['path'], null, null, $upperRange + 1);
        $requestHeaders =
            array('Authorization: ' . $this->getAccessToken(), 'Content-Length: ' . strlen($requestContent),
                  'Content-Range: bytes ' . ($upperRange + 1) . '-' . ($params['fileSize'] - 1) . '/' . $params['fileSize']);

        curl_setopt($ch, CURLOPT_URL, $params['sessionUri']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlResponse = curl_exec($ch);
        $response = $this->parseCurlResponseHeader($curlResponse);
        $httpCode = $this->parseHttpCodeLine($response['Http-Code-Line']);

        if ($httpCode != 200 and $httpCode != 201) {
            $reason = 'Your upload request is terminated before receiving a valid response';
            $content = 'Retry the upload process or try "resumeInterruptedUpload" function';
            throw new UploadFailedException($httpCode, $reason, $content);
        } else {
            return true;
        }
    }

    public function download($file, $path, $options = array())
    {

    }

    public function createFolder($path)
    {

    }

    public function delete($path)
    {

    }

    public function move($fromPath, $toPath)
    {

    }

    public function copy($fromPath, $toPath)
    {

    }

    public function listData($path)
    {

    }

    /**
     * Parses and returns an array of curl response headers.
     *
     * @param $response
     * @return array
     */
    protected function parseCurlResponseHeader($response)
    {

        $parsedHeaders = array();

        if (!$jsonPos = strpos($response, "{")) {
            $header = array($response);
            $part = 0;
        } else {
            $jsonPos = strpos($response, "{");
            $header = substr($response, 0, $jsonPos);
            $header = explode("\r\n\r\n", $header);
            $part = count($header);
            $part -= 2;
        }

        foreach (explode("\r\n", $header[$part]) as $i => $line) {
            if ($i === 0) {
                $parsedHeaders['Http-Code-Line'] = $line;
            } else {
                if (!empty($line)) {
                    list ($key, $value) = explode(': ', $line);
                    $parsedHeaders[$key] = $value;
                }

            }
        }

        return $parsedHeaders;
    }

    /**
     * Sends requests using BUZZ Library.
     *
     * @param array $params
     * @return Response
     */
    protected function sendRequest($params = array())
    {
        $urlBase = $params['urlBase'];
        $httpMethod = $params['httpMethod'];
        $headers = $params['headers'];
        $content = isset($params['content']) ? $params['content'] : null;

        $request = new Request();
        $response = new Response();

        $request->setMethod($httpMethod);
        $request->fromUrl($urlBase);
        $request->setHeaders($headers);
        $request->setContent($content);

        $client = new FileGetContents();
        $client->send($request, $response);

        return $response;
    }
}