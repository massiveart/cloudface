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
        $accessToken = '3MxMAbx5aoYAAAAAAAAAAVqN4vGd7XasOmTOEDfj4uwfWPB-Jo3Rp19XsRR5UprT';

        $params = array('accessToken' => $accessToken);

       $this->assertEquals(true, $this->dropbox->authorize($params));
    }

}