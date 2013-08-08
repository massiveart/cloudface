<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 10:03
 * To change this template use File | Settings | File Templates.
 */

namespace MassiveArt\CloudFace;

class CloudFace implements CloudInterface{

    private $cloudProvider;

    public function setCloudProvider($cloudProvider)
    {
        $this->cloudProvider = $cloudProvider;
    }

    public function getCloudProvider()
    {
        return $this->cloudProvider;
    }

    public function authorize()
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