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


interface CloudInterface {
    public function authorize($clientId, $clientSecret, $authorizationCode);
    public function upload();
    public function download();
}