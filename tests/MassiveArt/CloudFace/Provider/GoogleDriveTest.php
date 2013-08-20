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

use MassiveArt\CloudFace\Provider\GoogleDrive;

class GoogleDriveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GoogleDrive
     */
    private $googleDrive;

    public function setUp()
    {
        $this->googleDrive = new GoogleDrive();
    }

    public function testAuthorize()
    {
        $clientId = '565391687116.apps.googleusercontent.com';
        $clientSecret = 'skWCWQsbcruC5jgYzNr5CfzR';
        $authorizationCode = '4/mezbIHjpfBq1KlpMg_enuQazzR0e.YlbT__xyxcAcOl05ti8ZT3b5Ma8wgQI.';

        $params = array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'authorizationCode' => $authorizationCode);

        $this->assertEquals("haha", $this->googleDrive->authorize($params));
    }
}
