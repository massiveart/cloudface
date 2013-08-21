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

use MassiveArt\CloudFace\Provider\AmazonS3;

class CloudFaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CloudFace
     */
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
