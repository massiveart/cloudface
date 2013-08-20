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

use MassiveArt\CloudFace\CloudInterface;

/**
 * This class is an abstract class which implements the CloudInterface.
 * All providers(Dropbox, AmazonS3, GoogleDrive) extend this abstract class.
 *
 * @package MassiveArt\CloudFace\Provider
 */
abstract class CloudProvider implements CloudInterface{

    /**
     * Provides information(e.g. access token) that will be used to access the services.
     *
     * @param array $params
     * @return mixed
     */
    public abstract function authorize($params = array());
    public abstract function upload();
    public abstract function download();
}