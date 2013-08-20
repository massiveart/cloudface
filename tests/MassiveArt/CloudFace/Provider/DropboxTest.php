<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART Webservices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use MassiveArt\CloudFace\Provider\Dropbox;

class DropboxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Dropbox
     */
    private $dropbox;

    public function setUp()
    {
        $this->dropbox = new Dropbox();
    }

    public function testAuthorize()
    {
        $clientId = '35l5jgbz3nr8wc7';
        $clientSecret = 'n6wy4pnpgop9o55';
        $authorizationCode = 'uxEX5JMJUa8AAAAAAAAAAenpjWA7iDPdMACtUxW81wo';

        $params = array('clientId' => $clientId, 'clientSecret' => $clientSecret, 'authorizationCode' => $authorizationCode);

        $this->assertEquals("haha", $this->dropbox->authorize($params));
    }
}
