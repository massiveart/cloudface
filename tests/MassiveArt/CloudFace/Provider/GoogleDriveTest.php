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

/**
 * Test class for GoogleDrive.
 * @package MassiveArt\CloudFace\Provider
 */
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

    /**
     * Tests the function authorize.
     */
        public function testAuthorize()
        {
            $clientId = '565391687116.apps.googleusercontent.com';
            $clientSecret = 'skWCWQsbcruC5jgYzNr5CfzR';
            $refreshToken = '1/iFfu_MEHKxkwWVkmyK8IiXK1w0_pRTqE2WLerR8gJO4';

            $params = array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'refreshToken' => $refreshToken);

            $this->assertEquals(true, $this->googleDrive->authorize($params));
        }
}