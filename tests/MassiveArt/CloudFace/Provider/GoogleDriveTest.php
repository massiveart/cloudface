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

use MassiveArt\CloudFace\Provider\GoogleDrive;

/**
 * Test class for GoogleDrive.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class GoogleDriveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of GoogleDrive class.
     *
     * @var GoogleDrive
     */
    private $googleDrive;

    /**
     * Instantiates a GoogleDrive object.
     */
    public function setUp()
    {
        $this->googleDrive = new GoogleDrive();
    }

    /**
     * Tests the authorize function.
     */
    public function testAuthorize()
    {
        $clientId = 'Your-ClientId';
        $clientSecret = 'Your-ClientSecret';
        $refreshToken = 'Your-RefreshToken';
        $params = array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'refreshToken' => $refreshToken);

        $this->assertEquals(true, $this->googleDrive->authorize($params));
    }

    /**
     * Tests the upload function.
     */
    public function testUpload()
    {
        $path = '';
        $file = 'path to the file on disk';
        $params = array();

        $this->testAuthorize();
        $this->assertEquals(true, $this->googleDrive->upload($file, $path, $params));
    }
}