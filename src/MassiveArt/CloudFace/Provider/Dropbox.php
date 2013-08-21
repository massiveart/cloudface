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

    }

    public function download()
    {

    }
}