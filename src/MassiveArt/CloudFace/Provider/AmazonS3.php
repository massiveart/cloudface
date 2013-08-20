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


use Buzz\Message\Request;

class AmazonS3 extends CloudProvider
{
    // contains AWS Access Key Id which will be used to authorize the API requests.
    protected $awsAccessKeyId;
    // contains AWS Secret Key which will be used to authorize the API requests.
    protected $awsSecretKey;

    /**
     * @param array $params ('awsAccessKeyId' => $awsAccessKeyId, 'awsSecretKey' => $awsSecretKey).
     * @return array|string returns an array including both of awsAccessKeyId and awsSecretKey if they are set. Otherwise an error.
     */
    public function authorize($params = array())
    {
        try {
            if (isset($params['awsAccessKeyId'], $params['awsSecretKey'])) {
                $this->awsAccessKeyId = $params['awsAccessKeyId'];
                $this->awsSecretKey = $params['awsSecretKey'];
                return array($this->awsAccessKeyId, $this->awsSecretKey);
            } else {
                throw new \Exception('Error: one or more of the required parameters is missing.');
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function upload()
    {
        return "AmazonS3";
    }

    public function download()
    {

    }
}