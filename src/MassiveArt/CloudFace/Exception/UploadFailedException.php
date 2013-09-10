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


class UploadFailedException extends \Exception
{
    protected $code;
    protected $reason;
    protected $content;

    public function __construct($code, $reason, $content)
    {
        $this->code = $code;
        $this->reason = $reason;
        $this->content = $content;
        parent::__construct('Status code: ' . $code . ', Reason: ' . $reason . ', Content: ' . $content);
    }
}