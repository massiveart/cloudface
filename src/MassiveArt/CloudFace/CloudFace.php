<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART Webservices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace;

use MassiveArt\CloudFace\Provider\CloudProvider;

class CloudFace implements CloudInterface
{

    /**
     * @var CloudProvider
     */
    private $cloudProvider;

    public function setCloudProvider($cloudProvider)
    {
        $this->cloudProvider = $cloudProvider;
    }

    public function getCloudProvider()
    {
        return $this->cloudProvider;
    }

    public function authorize($clientId, $clientSecret, $authorizationCode)
    {

    }

    public function upload()
    {
        $this->cloudProvider->upload();
    }

    public function download()
    {

    }
}