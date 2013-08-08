<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Naser
 * Date: 07.08.13
 * Time: 10:07
 * To change this template use File | Settings | File Templates.
 */

namespace MassiveArt\CloudFace;

class CloudFaceTest extends \PHPUnit_Framework_TestCase
{
    private $cloudFace;

    public function setUp()
    {
        $this->cloudFace = new CloudFace();
        $this->cloudFace->setCloudProvider(new AmazonS3());
    }

    public function testUpload()
    {
        $this->assertEquals("AmazonS3", $this->cloudFace->getCloudProvider()->upload());
    }
}
