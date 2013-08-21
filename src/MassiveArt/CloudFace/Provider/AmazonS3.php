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
use MassiveArt\CloudFace\Exception\MissingParameterException;

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
     * Sets the required information (if they are set) for accessing the service.
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
        if (isset($params['awsAccessKeyId'], $params['awsSecretKey'])) {
            $this->awsAccessKeyId = $params['awsAccessKeyId'];
            $this->awsSecretKey = $params['awsSecretKey'];
            return true;
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