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


class ResourceAlreadyExistsException extends \Exception
{

    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
        parent::__construct($resource . '-resource exists already.');
    }
}
