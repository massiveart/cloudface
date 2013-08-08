<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 10:39
 * To change this template use File | Settings | File Templates.
 */

namespace MassiveArt\CloudFace;


abstract class CloudProvider implements CloudInterface{

    public abstract function authorize();
    public abstract function upload();
    public abstract function download();
}