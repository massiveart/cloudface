<?php
/*
 * This file is part of the MassiveArt CloudFace Library.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace MassiveArt\CloudFace;


/**
 * This class is an interface which will be implemented from both CloudFace and CloudProvider classes.
 *
 * @package MassiveArt\CloudFace
 */
interface CloudInterface
{

    /**
     * Provides information(e.g. access token) that is essentially to make valid requests and access the services.
     *
     * @param array $params
     * @return mixed
     */
    public function authorize($params = array());

    /**
     * Uploads a file to the given path. Optional parameters can be passed in an array.
     *
     * @param $file
     * @param $path
     * @param array $options
     * @return mixed
     */
    public function upload($file, $path, $options = array());

    /**
     * Downloads the file to the given path. Optional parameters can be passed in an array.
     *
     * @param $file
     * @param $path
     * @param array $options
     * @return mixed
     */
    public function download($file, $path, $options = array());

    /**
     * Creates a new folder.
     *
     * @param $path
     * @return mixed
     */
    public function createFolder($path);

    /**
     * Deletes a file or folder in the given path.
     *
     * @param $path
     * @return mixed
     */
    public function delete($path);

    /**
     * Moves a file or folder to a new location.
     *
     * @param $fromPath
     * @param $toPath
     * @return mixed
     */
    public function move($fromPath, $toPath);

    /**
     * Copies a file or a folder to a new location.
     *
     * @param $fromPath
     * @param $toPath
     * @return mixed
     */
    public function copy($fromPath, $toPath);

    /**
     * Lists all existing files and folders in the given path.
     *
     * @param $path
     * @return mixed
     */
    public function listData($path);
}
