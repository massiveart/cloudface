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


/**
 * This class is an interface which will be implemented from both CloudFace and CloudProvider classes.
 *
 * @package MassiveArt\CloudFace
 */
interface CloudInterface {

    /**
     * @param array $params
     * @return mixed
     */
    public function authorize($params = array());
    public function upload();
    public function download();
}