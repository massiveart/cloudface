<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 11:55
 * To change this template use File | Settings | File Templates.
 */

namespace MassiveArt\CloudFace;


class Dropbox extends CloudProvider{

    public function authorize()
    {

    }
    public function upload()
    {
        return "Dropbox";
    }

    public function download()
    {

    }
}