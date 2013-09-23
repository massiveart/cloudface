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
use Buzz\Message\Request;
use Buzz\Message\Response;
use MassiveArt\CloudFace\Exception\FileNotFoundException;
use MassiveArt\CloudFace\Exception\InvalidRequestException;
use MassiveArt\CloudFace\Exception\MissingParameterException;
use MassiveArt\CloudFace\Exception\UploadFailedException;
use MassiveArt\CloudFace\Exception\NotYetImplementedException;

/**
 * This is the class with which you can talk to the AmazonS3 REST API.
 * This class is a subclass of Provider class.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class AmazonS3 extends CloudProvider
{
    /**
     * contains AWS Access Key Id which will be used to authorize the API requests.
     * @var string
     */
    protected $awsAccessKeyId;

    /**
     * contains AWS Secret Key which will be used to authorize the API requests.
     * @var string
     */
    protected $awsSecretKey;

    /**
     * Minimum size of an uploading part in bytes 67108864 = 64MB
     *
     * @const integer
     */
    const PART_SIZE = 67108864;

    /**
     * The maximum size of a file in bytes that will be uploaded in a single request. 104857600 = 100 MB
     *
     * @const integer
     */
    const FILE_LIMIT_SIZE = 104857600;

    /**
     * Provides information(e.g. access token) that is essentially to make valid requests and access the services.
     * If the required information is not set, it throws an missing parameter Exception.
     *
     * An array with the required information has to be passed:
     * <code>
     * array('awsAccessKeyId' => $awsAccessKeyId, 'awsSecretKey' => $awsSecretKey);
     * </code>
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     */
    public function authorize($params = array())
    {
        if (!isset($params['awsAccessKeyId'])) {
            throw new MissingParameterException('AWS access key id is missing.');
        } elseif (!isset($params['awsSecretKey'])) {
            throw new MissingParameterException('AWS secret key id is missing.');
        } else {
            $this->awsAccessKeyId = $params['awsAccessKeyId'];
            $this->awsSecretKey = $params['awsSecretKey'];

            return true;
        }
    }

    /**
     * Uploads a file to the given path. Optional parameters can be passed in an array.
     * If the file/object size is greater than 100MB it will be uploaded in multi parts. Otherwise the object will be uploaded in a single part.
     *
     * Multi part upload:
     *  - Initiates the multi part upload and gets an upload id.
     *  - Uploads the object parts and keeps the part number and ETag for all parts in a list.
     *  - After all parts have been uploaded, it completes the multi part upload.
     *
     * @param $file
     * @param $path
     * @param array $params
     * @return bool|mixed
     * @throws \MassiveArt\CloudFace\Exception\UploadFailedException
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     * @throws \MassiveArt\CloudFace\Exception\FileNotFoundException
     */
    public function upload($file, $path, $params = array())
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        if (!isset($params['region'])) {
            // Sets to the user's default region. The bucket/path must exists in this region too.
            $params['region'] = 's3-eu-west-1';
        }

        // Http method to do the upload process.
        $httpMethod = 'PUT';

        // Defines the protocol which will be used.
        $scheme = 'https';

        // host name
        $host = 'amazonaws.com';

        // The entry point for the service depends on the region.
        $region = $params['region'];

        // the entry point for the service.
        $endPoint = $region . '.' . $host;

        // Each file will be uploaded to S3 as an object.
        $objectName = basename($file);

        // object size in bytes.
        $objectSize = filesize($file);

        // Get mime type of the file/object
        $fileInfo = new \finfo(FILEINFO_MIME);
        $mimeType = $fileInfo->file($file);

        // container name
        $bucketName = $path;

        // urlBase constructed in virtual hosted-style
        $urlBase = $scheme . '://' . $bucketName . '.' . $endPoint . '/' . $objectName;

        // date and time of creating the request in GMT format
        $requestDate = gmdate("D, d M Y H:i:s T");

        // Resource path without host name
        $canonicalizedResource = '/' . $bucketName . '/' . $objectName;

        // x-amz-header for date
        $canonicalizedAmzHeaders = 'x-amz-date:' . $requestDate;

        // The XML root element which will contains the parts list using multi part upload
        $xmlCompleteMultipartUpload = new \SimpleXMLElement('<CompleteMultipartUpload></CompleteMultipartUpload>');

        if ($objectSize <= self::FILE_LIMIT_SIZE) {
            $authorizationParams = array('httpMethod'              => $httpMethod, 'mimeType' => $mimeType,
                                         'canonicalizedAmzHeaders' => $canonicalizedAmzHeaders,
                                         'canonicalizedResource'   => $canonicalizedResource);
            $requestHeaders =
                array('Authorization:' . $this->getAuthorization($authorizationParams), 'x-amz-date:' . $requestDate,
                      'Content-Type:' . $mimeType, 'Content-Length:' . $objectSize);
            $requestContent = file_get_contents($file);
            $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                            'content'    => $requestContent);

            $response = $this->sendRequest($params);

            if (!$response->isOk()) {
                throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
                ), $response->getContent());
            } else {
                return true;
            }
        } else {
            // number of current part starting with 1.
            $partNumber = 1;

            // start point relative to the file's begin.
            $offset = 0;

            // The unique upload id which identifies the uploaded parts.
            $uploadId = $this->getMultiPartUploadId(
                $urlBase, $canonicalizedResource, $canonicalizedAmzHeaders, $requestDate
            );

            // upload each part of object until all parts are uploaded. Also create XML Content for Complete Multi-Part Upload request.
            while ($objectPart = file_get_contents($file, null, null, $offset, self::PART_SIZE)) {
                $eTag = $this->uploadPart(
                    $partNumber, $uploadId, $objectPart, $urlBase, $canonicalizedResource, $canonicalizedAmzHeaders,
                    $requestDate, $mimeType
                );
                $this->createPartsList($partNumber, $eTag, $xmlCompleteMultipartUpload);
                $partNumber++;
                $offset += self::PART_SIZE;
            }

            // All parts (including Part Number and ETag) should be provide for complete multi part request.
            $xmlContent = $xmlCompleteMultipartUpload->asXML();
            $partsList = explode("\n", $xmlContent);
            $partsList = $partsList[1];

            // Complete Multipart upload request
            $httpMethod = 'POST';
            $mimeType = 'application/xml';
            $urlBase .= '?uploadId=' . $uploadId;
            $canonicalizedResource .= '?uploadId=' . $uploadId;
            $contentLength = strlen($partsList);

            $authorizationParams = array('httpMethod'              => $httpMethod, 'mimeType' => $mimeType,
                                         'canonicalizedAmzHeaders' => $canonicalizedAmzHeaders,
                                         'canonicalizedResource'   => $canonicalizedResource);
            $requestHeaders =
                array('Authorization:' . $this->getAuthorization($authorizationParams), 'x-amz-date:' . $requestDate,
                      'Content-Type:' . $mimeType, 'Content-Length:' . $contentLength);
            $requestContent = $partsList;
            $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                            'content'    => $requestContent);

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
     * Adds the parts (including Part Number and ETag) that have been uploaded in a SimpleXMLElement object.
     *
     * @param $partNumber
     * @param $eTag
     * @param \SimpleXMLElement $xmlCompleteMultipartUpload
     */
    protected function createPartsList($partNumber, $eTag, \SimpleXMLElement $xmlCompleteMultipartUpload)
    {
        $xmlPart = $xmlCompleteMultipartUpload->addChild('Part');
        $xmlPart->addChild('PartNumber', $partNumber);
        $xmlPart->addChild('ETag', $eTag);
    }

    /**
     * Initiates a multi part upload and gets the unique upload id to identify the parts that will be uploaded later.
     *
     * @param $urlBase
     * @param $canonicalizedResource
     * @param $canonicalizedAmzHeaders
     * @param $requestDate
     * @return mixed
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    protected function getMultiPartUploadId($urlBase, $canonicalizedResource, $canonicalizedAmzHeaders, $requestDate)
    {
        $httpMethod = 'POST';
        $urlBase .= '?uploads';
        $canonicalizedResource .= '?uploads';

        $authorizationParams = array('httpMethod'              => $httpMethod,
                                     'canonicalizedAmzHeaders' => $canonicalizedAmzHeaders,
                                     'canonicalizedResource'   => $canonicalizedResource);
        $requestHeaders =
            array('Authorization: ' . $this->getAuthorization($authorizationParams), 'x-amz-date:' . $requestDate);
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);
        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        } else {
            $responseContent = new \SimpleXMLElement($response->getContent());
            $responseContent = (array)$responseContent;

            return $responseContent['UploadId'];
        }
    }

    /**
     * Uploads a part of file and returns an ETag which specifies this uploaded part of file.
     *
     * @param $partNumber
     * @param $uploadId
     * @param $objectPart
     * @param $urlBase
     * @param $canonicalizedResource
     * @param $canonicalizedAmzHeaders
     * @param $requestDate
     * @param $mimeType
     * @return array|null|string
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    protected function uploadPart($partNumber, $uploadId, $objectPart, $urlBase, $canonicalizedResource,
                                  $canonicalizedAmzHeaders, $requestDate, $mimeType)
    {
        $httpMethod = 'PUT';
        $objectPartSize = strlen($objectPart);
        $urlBase .= '?partNumber=' . $partNumber . '&uploadId=' . $uploadId;
        $canonicalizedResource .= '?partNumber=' . $partNumber . '&uploadId=' . $uploadId;

        $authorizationParams = array('httpMethod'              => $httpMethod, 'mimeType' => $mimeType,
                                     'canonicalizedAmzHeaders' => $canonicalizedAmzHeaders,
                                     'canonicalizedResource'   => $canonicalizedResource);
        $requestHeaders =
            array('Authorization: ' . $this->getAuthorization($authorizationParams), 'x-amz-date:' . $requestDate,
                  'Content-Length:' . $objectPartSize, 'Content-Type:' . $mimeType);
        $requestContent = $objectPart;
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);
        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        } else {
            return $response->getHeader('ETag');
        }
    }

    public function download($file, $path, $options = array())
    {
        throw new NotYetImplementedException;
    }

    public function createFolder($path)
    {
        throw new NotYetImplementedException;
    }

    public function delete($path)
    {
        throw new NotYetImplementedException;
    }

    public function move($fromPath, $toPath)
    {
        throw new NotYetImplementedException;
    }

    public function copy($fromPath, $toPath)
    {
        throw new NotYetImplementedException;
    }

    public function listData($path)
    {
        throw new NotYetImplementedException;
    }

    /**
     * Concatenate stringToSign, calculates the signature and returns the authorization parameter that is needed by every API request.
     * Uses AWS secret key to calculate the HMAC-SHA1 of stringToSign.
     * The S3 REST API uses the standard HTTP Authorization header to pass authentication information.
     *
     * @param array $params
     * @return string
     */
    protected function getAuthorization($params = array())
    {
        $httpMethod = $params['httpMethod'];
        $mimetype = $params['mimeType'];
        $canonicalizedAmzHeaders = $params['canonicalizedAmzHeaders'];
        $canonicalizedResource = $params['canonicalizedResource'];

        $stringToSign = $httpMethod . "\n" // VERB + "\n" +
            . "\n" // substitutes CONTENT-MD5
            . $mimetype . "\n" // substitutes CONTENT-TYPE
            . "\n" // substitutes DATE
            . $canonicalizedAmzHeaders . "\n" . $canonicalizedResource;

        $signature = base64_encode(hash_hmac('sha1', utf8_encode($stringToSign), $this->awsSecretKey, true));

        return $authorization = 'AWS' . ' ' . $this->awsAccessKeyId . ':' . $signature;
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

        $request->fromUrl($urlBase);
        $request->setMethod($httpMethod);
        $request->addHeaders($headers);
        $request->setContent($content);

        $client = new FileGetContents();
        $client->send($request, $response);

        return $response;
    }

}
