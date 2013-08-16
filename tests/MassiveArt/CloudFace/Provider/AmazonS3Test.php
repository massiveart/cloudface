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


class AmazonS3Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AmazonS3
     */
    private $amazonS3;

    public function setUp()
    {
        $this->amazonS3 = new AmazonS3();
    }

    public function testAuthorize()
    {
        $clientId = 'AKIAJDD3MWRDKOLBBSAA';
        $clientSecret = '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';
        $authorizationCode = null;

        $this->assertEquals("haha",$this->amazonS3->authorize($clientId, $clientSecret, $authorizationCode));
    }


}
