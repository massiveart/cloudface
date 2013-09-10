<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace\Provider;

use MassiveArt\CloudFace\Provider\AmazonS3;

/**
 * Test class for AmazonS3.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class AmazonS3Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of AmazonS3 class.
     *
     * @var AmazonS3
     */
    private $amazonS3;

    /**
     * Contains all required parameters for authorization.
     *
     * @var
     */
    private $authorizationParams;

    /**
     * Instantiates an AmazonS3 object and sets the authorization parameters.
     */
    public function setUp()

    {
        $awsAccessKeyId = 'AKIAJDD3MWRDKOLBBSAA';
        $awsSecretKey = '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';
        $this->authorizationParams = array(
            'awsAccessKeyId' => $awsAccessKeyId,
            'awsSecretKey'   => $awsSecretKey
        );

        $this->amazonS3 = new AmazonS3();
    }

    /**
     * Tests the authorize function.
     */
    public function testAuthorize()
    {
        $this->assertEquals(true, $this->amazonS3->authorize($this->authorizationParams));
    }

    /**
     * Tests the upload function.
     */
    public function testUpload()
    {
        $region = 's3-eu-west-1';

        $file = '/Users/Naser/Desktop/haha.pdf';
        $path = 'my-super-bucket';
        $options = array('region' => $region);

        $this->amazonS3->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->amazonS3->upload($file, $path, $options));
    }
}
