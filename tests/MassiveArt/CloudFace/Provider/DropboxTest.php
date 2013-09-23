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

use MassiveArt\CloudFace\Provider\Dropbox;

/**
 * Test class for Dropbox.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class DropboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of Dropbox class.
     *
     * @var Dropbox
     */
    private $dropbox;

    /**
     * Contains all required parameters for authorization.
     *
     * @var
     */
    private $authorizationParams;

    /**
     * Instantiates a dropbox object and sets the authorization parameters.
     */
    public function setUp()
    {
        $accessToken = 'Your-Access-Token';
        $this->authorizationParams = array('accessToken' => $accessToken);

        $this->dropbox = new Dropbox();
    }

    /**
     * Tests the authorize function.
     */
    public function testAuthorize()
    {
        $this->assertEquals(true, $this->dropbox->authorize($this->authorizationParams));
    }

    /**
     * Tests the upload function
     */
    public function testUpload()
    {
        $path = '';
        $file = '/Users/Naser/Desktop/haha.pdf';
        $options = array('overwrite' => 'false');

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->upload($file, $path, $options));
    }
}
