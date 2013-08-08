<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 10:39
 * To change this template use File | Settings | File Templates.
 */

namespace MassiveArt\CloudFace;


interface CloudInterface {
    public function authorize();
    public function upload();
    public function download();
}