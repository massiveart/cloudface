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

use MassiveArt\CloudFace\Provider\Dropbox;

/**
 * Test class for Dropbox.
 * @package MassiveArt\CloudFace\Provider
 */
class DropboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Contains an instance of Dropbox class.
     * @var Dropbox
     */
    private $dropbox;

    /**
     * Initialize a dropbox object.
     */
    public function setUp()
    {
        $this->dropbox = new Dropbox();
    }

    /**
     * Tests the function authorize.
     */
    public function testAuthorize()
    {
        $clientId = '35l5jgbz3nr8wc7';
        $clientSecret = 'n6wy4pnpgop9o55';
        $authorizationCode = 'D0K1hEbe4NQAAAAAAAAAAZNAxkhisUZHLT2nEqnlFO0';

        $params = array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'authorizationCode' => $authorizationCode);

       $this->assertEquals(true, $this->dropbox->authorize($params));
    }

    public function testUpload()
    {
        $file = '/Users/Naser/Desktop';
        $path = '/CrazyFolder/test.txt';

        $params = array('file' => $file, 'path' => $path);

        $this->assertEquals("haha", $this->dropbox->upload($params));
    }
}