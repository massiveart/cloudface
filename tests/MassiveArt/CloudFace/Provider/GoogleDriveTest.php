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
     * Contains all required parameters for authorization.
     *
     * @var
     */
    private $authorizationParams;

    /**
     * Instantiates a GoogleDrive object and sets the authorization parameters.
     */
    public function setUp()
    {
        $clientId = 'Your-Client-ID';
        $clientSecret = 'Your-Client-Secret';
        $refreshToken = 'Your-Refresh-Token';

        $this->authorizationParams =
            array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'refreshToken' => $refreshToken);

        $this->googleDrive = new GoogleDrive();
    }

    /**
     * Tests the authorize function.
     */
    public function testAuthorize()
    {
        $this->assertEquals(true, $this->googleDrive->authorize($this->authorizationParams));
    }

    /**
     * Tests the upload function.
     */
    public function testUpload()
    {
        $path = '';
        $file = '';
        $options = array();

        $this->googleDrive->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->googleDrive->upload($file, $path, $options));
    }
}