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
        $file = '';
        $options = array('overwrite' => 'false');

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->upload($file, $path, $options));
    }

    /**
     * Tests the download function
     */
    public function testDownload()
    {
        // The path to where the file will be downloaded to
        $path = '';
        $defaultPath = '';
        // The path to the FILE on dropbox
        $file = '';
        $options = array('override' => false, 'defaultPath' => $defaultPath);

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->download($file, $path, $options));
    }

    /**
     * Tests the create folder function
     */
    public function testCreateFolder()
    {
        $path = '';

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->createFolder($path));
    }

    /**
     * Tests the Delete function.
     */
    public function testDelete()
    {
        $path = '';

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->delete($path));
    }

    /**
     * Tests the Move function.
     */
    public function testMove()
    {
        $fromPath = '';
        $toPath = '';

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->move($fromPath, $toPath));
    }

    /**
     * Tests the Copy function.
     */
    public function testCopy()
    {
        $fromPath = '';
        $toPath = '';

        $this->dropbox->authorize($this->authorizationParams);
        $this->assertEquals(true, $this->dropbox->copy($fromPath, $toPath));
    }

    /**
     * Tests the listData function.
     */
    public function testListData()
    {
        $path = '';

        $this->dropbox->authorize($this->authorizationParams);
        $list = $this->dropbox->listData($path);
        print_r($list);
    }

    /**
     * Tests the getLink function.
     */
    public function testGetLink()
    {
        $path = '';

        $this->dropbox->authorize($this->authorizationParams);
        print_r($this->dropbox->getLink($path));
    }

    /**
     * Tests the getMedia function.
     */
    public function testGetMedia()
    {
        $path = '';

        $this->dropbox->authorize($this->authorizationParams);
        print_r($this->dropbox->getMedia($path));
    }

    /**
     * Tests the getCopyRef function.
     */
    public function testGetCopyRef()
    {
        $path = '';

        $this->dropbox->authorize($this->authorizationParams);
        print_r($this->dropbox->getCopyReference($path));
    }


    /**
     * Tests the getThumbnail function.
     */
    public function testGetThumbnail()
    {
        $path = '';
        $format = 'jpeg';
        $size = 's';

        $this->dropbox->authorize($this->authorizationParams);
        print_r($this->dropbox->getThumbnail($path, $format, $size));
    }

    /**
     * Tests the search function.
     */
    public function testSearch()
    {
        $path = '/';
        $query = '';
        $query = urlencode($query);

        $this->dropbox->authorize($this->authorizationParams);
        print_r($this->dropbox->search($path, $query));
    }

    /**
     * Tests the getDelta function.
     */
    public function testGetDelta()
    {
        $this->dropbox->authorize($this->authorizationParams);

        $response = $this->dropbox->getDelta('');
        print_r($response);

        $response = $this->dropbox->getDelta($response['cursor']);
        print_r($response);

        $response = $this->dropbox->getDelta($response['cursor']);
        print_r($response);
    }
}
