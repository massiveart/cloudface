<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 11:59
 * To change this template use File | Settings | File Templates.
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