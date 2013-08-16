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
use Buzz\Message\Request;
use Buzz\Message\Response;

class AmazonS3 extends CloudProvider
{
    /**
     * @param $clientId
     * @param $clientSecret
     * @param $authorizationCode
     * @return Response
     */
    public function authorize($clientId, $clientSecret, $authorizationCode)
    {

        // A string that AWS distributes to uniquely identify each AWS user; it is an alphanumeric token associated with the secret access key.
        //$awsAccessKeyId = 'AKIAJDD3MWRDKOLBBSAA';
        $awsAccessKeyId = $clientId;

        // The key that Amazon Web Services assigns to you when you sign up for an AWS account.
        //$awsSecretKey = 'UpCJCs2+ouVHu0rtknldbTTru5HnWfA9SmX4wZyZ';
        $awsSecretKey = $clientSecret;
        // Http method
        $httpMethod = 'GET';

        // The object which is stored in a bucket in S3
        $objectName = '/' . 'composer.json';

        // A container for objects stored in S3
        $bucketName = 'my-super-bucket';

        // urlStructure constructed in virtual hosted-style
        $urlStructure = 'https://' . $bucketName . '.s3-eu-west-1.amazonaws.com' . $objectName;

        // Date and time of creating the request in GMT format
        $requestDate = gmdate("D, d M Y H:i:s T");

        // Resource path without host name
        $canonicalizedResource = '/' . $bucketName . $objectName;

        // x-amz-header
        $canonicalizedAmzHeaders = 'x-amz-date:' . $requestDate;


        // concatenates selected elements of the request to form a string
        $stringToSign = $httpMethod . "\n" // VERB + "\n" +
            . "\n" // substitutes CONTENT-MD5
            . "\n" // substitutes CONTENT-TYPE
            . "\n" // substitutes DATE
            . $canonicalizedAmzHeaders
            . "\n"
            . $canonicalizedResource;


        // uses AWS secret key to calculate the HMAC-SHA1 of stringToSign
        $signature = base64_encode(hash_hmac('sha1', utf8_encode($stringToSign), $awsSecretKey, true));

        // The S3 REST API uses the standard HTTP Authorization header to pass authentication information
        $Authorization = 'AWS' . ' ' . $awsAccessKeyId . ':' . $signature;


        $request = new Request();
        $response = new Response();

        $request->fromUrl($urlStructure);
        $request->setMethod($httpMethod);

        $headers = array('Authorization:' . $Authorization, 'x-amz-date:' . $requestDate);
        $request->addHeaders($headers);

        $client = new FileGetContents();
        $client->send($request, $response);

        return $response;

    }

    public function upload()
    {
        return "AmazonS3";
    }

    public function download()
    {

    }
}