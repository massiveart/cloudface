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


class MissingParameterException extends \Exception
{
    protected $parameter;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
        parent:: __construct($parameter . '-parameter is missing.');
    }
}
