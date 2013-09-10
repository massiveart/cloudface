<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace;

use MassiveArt\CloudFace\Provider\CloudProvider;

/**
 * This class implements the CloudInterface. It delegates the function calls to the appropriate provider.
 *
 * @package MassiveArt\CloudFace
 */
class CloudFace implements CloudInterface
{
    /**
     * Contains the provider which is using currently.
     *
     * @var CloudProvider
     */
    private $cloudProvider;

    /**
     * Sets the current cloud provider to the given one.
     *
     * @param $provider
     */
    public function setCloudProvider($provider)
    {
        $this->cloudProvider = $provider;
    }

    /**
     * Gets the current cloud provider.
     *
     * @return CloudProvider
     */
    public function getCloudProvider()
    {
        return $this->cloudProvider;
    }

    /**
     * Provides information(e.g. access token) that is essentially to make valid requests and access the services.
     * Delegates the function call to the function implemented in the appropriate cloud provider.
     *
     * @param array $params
     * @return mixed|void
     */
    public function authorize($params = array())
    {
        $this->cloudProvider->authorize($params);
    }

    /**
     * Uploads a file to the given path. Optional parameters can be passed in an array.
     * Delegates the function call to the function implemented in the appropriate cloud provider.
     *
     * @param $file
     * @param $path
     * @param array $options
     * @return mixed|void
     */
    public function upload($file, $path, $options = array())
    {
        $this->cloudProvider->upload($file, $path, $options = array());
    }

    public function download($file, $path, $options = array())
    {

    }
}