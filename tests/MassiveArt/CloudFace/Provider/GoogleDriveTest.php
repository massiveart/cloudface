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
class GoogleDriveTest extends \PHPUnit_Framework_TestCase {

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
        $this->assertEquals("haha",$this->googleDrive->authorize());
    }
}
