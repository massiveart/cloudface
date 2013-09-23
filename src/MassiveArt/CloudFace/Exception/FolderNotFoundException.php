<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace\Exception;


class FolderNotFoundException extends ResourceNotFoundException
{
    public function __construct($folder)
    {
        $this->resource = $folder;
        parent::__construct($folder);
    }
}