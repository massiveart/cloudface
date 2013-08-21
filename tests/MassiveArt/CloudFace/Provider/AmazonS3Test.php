<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART Webservices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace\Provider;

use MassiveArt\CloudFace\Provider\AmazonS3;

/**
 * Test class for AmazonS3.
 * @package MassiveArt\CloudFace\Provider
 */
class AmazonS3Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of AmazonS3 class.
     * @var AmazonS3
     */
    private $amazonS3;

    /**
     * Initialize an AmazonS3 object.
     */
    public function setUp()
    {
        $this->amazonS3 = new AmazonS3();
    }

    /**
     * Tests the function authorize.
     */
    public function testAuthorize()
    {
        $awsAccessKeyId = 'AKIAJDD3MWRDKOLBBSAA';
        $awsSecretKey = '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';

        $params = array('awsAccessKeyId' => $awsAccessKeyId, 'awsSecretKey' => $awsSecretKey);

        $this->assertEquals(true,$this->amazonS3->authorize($params));
    }
}
