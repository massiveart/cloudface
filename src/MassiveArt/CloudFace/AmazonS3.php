<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 11:57
 * To change this template use File | Settings | File Templates.
 */

namespace MassiveArt\CloudFace;


class AmazonS3 extends CloudProvider{

    public function authorize()
    {

    }

    public function upload()
    {
        return "AmazonS3";
    }

    public function download()
    {

    }
}