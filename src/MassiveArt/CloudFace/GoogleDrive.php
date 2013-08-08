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


class GoogleDrive extends CloudProvider{

    public function authorize()
    {

    }

    public function upload()
    {
        return "GoogleDrive";
    }

    public function download()
    {

    }
}