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

use Buzz\Client\FileGetContents;
use MassiveArt\CloudFace\Exception\FileNotFoundException;
use MassiveArt\CloudFace\Exception\FolderNotFoundException;
use MassiveArt\CloudFace\Exception\FileAlreadyExistsException;
use MassiveArt\CloudFace\Exception\InvalidRequestException;
use MassiveArt\CloudFace\Exception\MissingParameterException;
use MassiveArt\CloudFace\Exception\UploadFailedException;
use MassiveArt\CloudFace\Provider\CloudProvider;
use Buzz\Message\Request;
use Buzz\Message\Response;

/**
 * This is the class with which you can talk to the Dropbox REST API.
 * This class is a subclass of Provider class.
 *
 * @package MassiveArt\CloudFace\Provider
 */
class Dropbox extends CloudProvider
{
    /**
     * Contains the access token which will be used to authorize the API requests.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Maximum size of a chunk in bytes 67108864 = 64MB
     *
     * @const integer
     */
    const CHUNK_SIZE = 67108864;

    /**
     * The maximum size of a file in bytes that can be uploaded in a single request, 157286400 = 150 MB
     *
     * @const integer
     */
    const  FILE_LIMIT_SIZE = 157286400;

    /**
     * Concatenates and returns the access token needed to make API requests.
     *
     * @return string
     */
    private function getAccessToken()
    {
        return 'Bearer ' . $this->accessToken;
    }

    /**
     * Provides information(e.g. access token) that is essentially to make valid requests and access the services.
     *
     * An array with the required information has to be passed:
     * <code>
     * array('refreshToken' => $refreshToken);
     * </code>
     *
     * @param array $params
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\MissingParameterException
     */
    public function authorize($params = array())
    {
        if (!isset($params['accessToken'])) {
            throw new MissingParameterException('Dropbox\'s access token is missing.');
        } else {
            $this->accessToken = $params['accessToken'];

            return true;
        }
    }


    /**
     * Uploads a file to the given path. Optional parameters can be passed in an array.
     * If the file size is greater than 150MB it will be uploaded in chunks. Otherwise it will be uploaded in a single part.
     *
     * Upload in chunks:
     *  - Sends a PUT request to /chunked_upload with the first chunk of file with upload_id null and offset 0. Doing so
     *    the server returns an upload_id and an offset(representing the number of bytes transferred so far).
     *  - Repeatedly PUTs subsequent chunks using the upload_id(obtained in previous step) and offset.
     *  - After the last chunk, POSTs to /commit_chunked_upload to complete the upload process.
     *
     * Upload in a single part:
     *  - Sends a PUT request to /files_put.
     *
     * Note that /files_put takes the file contents in the request body, but /commit_chunked_upload takes the upload_id.
     *
     * @param $file
     * @param $path
     * @param array $options
     * @return bool|mixed
     * @throws \MassiveArt\CloudFace\Exception\UploadFailedException
     * @throws \MassiveArt\CloudFace\Exception\FileNotFoundException
     */
    public function upload($file, $path, $options = array())
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        if (!isset($options['overwrite'])) {
            $options['overwrite'] = 'false';
        }

        $httpMethod = 'POST';
        $urlBase = 'https://api-content.dropbox.com/1/commit_chunked_upload/dropbox/';

        // The path on Dropbox where the file will be uploaded. If null or '' the file will be uploaded onto the root directory.
        $path = $path . basename($file);

        // Represents the number of bytes transferred so far.
        $offset = 0;

        // The unique ID of the in-progress upload on the server.
        $uploadId = null;

        // Contains both upload id and offset
        $uploadIdAndOffset = array('uploadId' => $uploadId, 'offset' => $offset);

        if (filesize($file) > self::FILE_LIMIT_SIZE) {
            $file = fopen($file, 'r');
            while ($chunkOfFile = fread($file, self::CHUNK_SIZE)) {
                $params = array('chunkOfFile' => $chunkOfFile, 'uploadId' => $uploadIdAndOffset['uploadId'],
                                'offset'      => $uploadIdAndOffset['offset']);
                $uploadIdAndOffset = $this->uploadChunk($params);
            }

            $requestHeaders = array('Authorization: ' . $this->getAccessToken());
            $requestContent = 'upload_id=' . $uploadIdAndOffset['uploadId'] . '&overwrite=' . $options['overwrite'];
            $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase . $path, 'headers' => $requestHeaders,
                            'content'    => $requestContent);
            $response = $this->sendRequest($params);

            if (!$response->isOk()) {
                throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
                ), $response->getContent());
            } else {
                return true;
            }
        } else {
            $httpMethod = 'PUT';
            $urlBase = 'https://api-content.dropbox.com/1/files_put/dropbox/';

            // If there is already a file at the specified path, the new file will be automatically renamed.
            $urlParams = '?overwrite=false';

            $requestHeaders = array('Authorization:' . $this->getAccessToken(), 'Content-Type: application');
            $requestContent = file_get_contents($file);
            $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase . $path . $urlParams,
                            'headers'    => $requestHeaders, 'content' => $requestContent);

            $response = $this->sendRequest($params);

            if (!$response->isOk()) {
                throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
                ), $response->getContent());
            } else {
                return true;
            }
        }
    }

    /**
     * Uploads a chunk of file and returns the upload id and offset representing the number of bytes transferred so far.
     * The first chunk will be uploaded without setting an upload_id. The default offset for the first chunk is 0.
     * After each chunk has been uploaded, the server returns a new offset.
     *
     * @param array $params
     * @return array
     * @throws \MassiveArt\CloudFace\Exception\UploadFailedException
     */
    private function uploadChunk($params = array())
    {
        $httpMethod = 'PUT';
        $urlBase = 'https://api-content.dropbox.com/1/chunked_upload';
        $urlParams = '';

        if ($params['uploadId'] != null && $params['offset'] != 0) {
            $urlParams = '?upload_id=' . $params['uploadId'] . '&offset=' . $params['offset'];
        }

        $requestHeaders = array('Authorization: ' . $this->getAccessToken(), 'Content-Type: application');
        $requestContent = $params['chunkOfFile'];
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase . $urlParams, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new UploadFailedException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        } else {
            $content = json_decode($response->getContent(), true);
            $uploadId = $content['upload_id'];
            $offset = $content['offset'];

            return array('uploadId' => $uploadId, 'offset' => $offset);
        }
    }

    /**
     * Downloads the file to the given path. Optional parameters can be passed in an array.
     *
     * @param $file
     * @param $path
     * @param array $options
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\FolderNotFoundException
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     * @throws \MassiveArt\CloudFace\Exception\FileAlreadyExistsException
     */
    public function download($file, $path, $options = array())
    {
        // Set to the user's default path (e.g. /Users/Name/Downloads)
        if ($path == '') {
            $path = isset($options['defaultPath']) ? $options['defaultPath'] : null;
        }

        if (!file_exists($path)) {
            throw new FolderNotFoundException($path);
        }

        if (!isset($options['override'])) {
            $options['override'] = true;
        }

        list($file, $path) = $this->doTrim($file, $path);

        $httpMethod = 'GET';
        $urlBase = 'https://api-content.dropbox.com/1/files/dropbox/' . $file;

        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        $path = $path . basename($file);

        if ((!file_exists($path)) || file_exists($path) && $options['override'] == true) {
            file_put_contents($path, $response->getContent());

            return true;
        } else {
            throw new FileAlreadyExistsException($path);
        }
    }

    /**
     * Creates a new folder in the given path.
     * The path should also include the name of the new folder, separated by a '/'
     * If the path does not exists, it will be assumed that you are about creating a nested folder.
     * If a folder is already exists at the specified path, the new folder can not be created.
     *
     * @param $path
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function createFolder($path)
    {
        $root = 'dropbox';
        $httpMethod = 'POST';
        $urlBase = 'https://api.dropbox.com/1/fileops/create_folder';
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $requestContent = 'root=' . $root . '&path=' . $path;
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return true;
    }

    /**
     * Deletes a file or folder in the given path.
     *
     * @param $path
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function delete($path)
    {
        $root = 'dropbox';
        $httpMethod = 'POST';
        $urlBase = 'https://api.dropbox.com/1/fileops/delete';
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $requestContent = 'root=' . $root . '&path=' . $path;
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return true;
    }

    /**
     * Moves a file or folder(without any files) to a new location
     * Note that toPath must include the new name for the file or folder
     * If toPath does not exists, first it will be created and then the file or folder will be moved in it.
     *
     * @param $fromPath
     * @param $toPath
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function move($fromPath, $toPath)
    {
        $httpMethod = 'POST';
        $urlBase = 'https://api.dropbox.com/1/fileops/move';
        $root = 'dropbox';
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $requestContent = 'root=' . $root . '&from_path=' . $fromPath . '&to_path=' . $toPath;
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return true;
    }

    /**
     * Copies a file or folder (including all files and folders in it) to a new location.
     * Note that when copying a file, toPath must include the new name for the file.
     * If toPath does not exists, first it will be created and then the file or folder will be copied in it.
     *
     * @param $fromPath
     * @param $toPath
     * @return bool
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function copy($fromPath, $toPath)
    {
        $httpMethod = 'POST';
        $urlBase = 'https://api.dropbox.com/1/fileops/copy';
        $root = 'dropbox';
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $requestContent = 'root=' . $root . '&from_path=' . $fromPath . '&to_path=' . $toPath; // &from_copy_ref
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders,
                        'content'    => $requestContent);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return true;
    }

    /**
     * Retrieves files or folders metadata in the given path.
     * Calls on folders will return a hash field which can be provide with 'hash' parameter so that if nothing has
     * changed , the response will be a 304(Not Modified ).
     *
     * @param $path
     * @return array
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function listData($path)
    {
        $httpMethod = 'GET';
        $hash = '';
        $urlBase = 'https://api.dropbox.com/1/metadata/dropbox/' . $path . '?hash=' . $hash;
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }
        $metadata = json_decode($response->getContent(), true);

        return $this->getAbstractFormat($metadata);
    }

    /**
     * Returns a dropbox link to files or folders in the given path.
     * Users can use this link to view a preview of the file in a web browser.
     *
     * NOTE: The returned link for a folder (specified in the given path) allows users to see ALL files or folders
     * within this folder. If you want to share only a single file make sure that you specifies the exact path to
     * this single file in the given path.
     *
     * The returned links are set to expire far enough in the future so that expiration is effectively not an issue.
     *
     * @param $path
     * @return mixed
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function getLink($path)
    {
        $httpMethod = 'POST';
        $urlBase = 'https://api.dropbox.com/1/shares/dropbox/' . $path;
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return $this->getValueFromJsonKey($response->getContent(), 'url');
    }

    /**
     * Returns the value of a given key in a json string
     *
     * @param $json
     * @param $key
     * @return mixed
     */
    private function getValueFromJsonKey($json, $key)
    {
        $decoded = json_decode($json, true);

        return $decoded[$key];
    }

    /**
     * Returns a link directly to a file
     * Note: The path must specifies a file, not a folder. This link expires after four hours.
     *
     * @param $path
     * @return mixed
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function getMedia($path)
    {
        $httpMethod = 'POST';
        $urlBase = 'https://api.dropbox.com/1/media/dropbox/' . $path;
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return $this->getValueFromJsonKey($response->getContent(), 'url');
    }

    /**
     * Returns a copy reference (copy_ref) to a file.
     *
     * This reference string can be used to copy that file to another user's Dropbox by passing it in as the
     * from_copy_ref parameter on /fileops/copy.
     *
     * @param $path
     * @return mixed
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function getCopyReference($path)
    {
        $httpMethod = 'GET';
        $urlBase = 'https://api.dropbox.com/1/copy_ref/dropbox/' . $path;
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return $this->getValueFromJsonKey($response->getContent(), 'copy_ref');
    }

    /**
     * Returns a thumbnail in a specified format and size for the image given in the path.
     * Note that the returned value is the content of the thumbnail. This means it should be written in a new file before
     * thumbnail can be viewed or used.
     *
     * The valid values for format are 'jpeg' (default) or 'png'.
     * The valid values for size are as follows: ('xs', 's', 'm', 'l' and 'xl')
     *  xs  32x32(px), s  64x64(px), m  128x128(px), l  640x480(px), xl  1024x768(px).
     *
     * The HTTP response contains the content metadata in JSON format within an x-dropbox-metadata header.
     *
     * NOTES:
     * This method currently supports files with the following file extensions: 'jpg', 'jpeg', 'png', 'tiff', 'tif',
     * 'gif' and 'bmp'. Photos larger than 20MB in size won't be converted to a thumbnail.
     *
     * @param $path
     * @param $format
     * @param $size
     * @return mixed
     * @throws \MassiveArt\CloudFace\Exception\InvalidRequestException
     */
    public function getThumbnail($path, $format, $size)
    {
        $httpMethod = 'GET';
        $urlBase =
            'https://api-content.dropbox.com/1/thumbnails/dropbox/' . $path . '?format=' . $format . '&size=' . $size;
        $requestHeaders = array('Authorization: ' . $this->getAccessToken());
        $params = array('httpMethod' => $httpMethod, 'urlBase' => $urlBase, 'headers' => $requestHeaders);

        $response = $this->sendRequest($params);

        if (!$response->isOk()) {
            throw new InvalidRequestException($response->getStatusCode(), $response->getReasonPhrase(
            ), $response->getContent());
        }

        return $response->getContent();
    }

    /**
     * Returns an abstract format of the given metadata.
     *
     * @See comments for 'doFormat' function for more information about the structure of the abstract format.
     *
     * Following parameter is omitted if the path in 'listData' function refers to a file.
     *  items: List of metadata entries for the contents of the folder. It can be null if the entry is a file.
     *
     * @param $metadata
     * @return array
     */
    private function getAbstractFormat($metadata)
    {
        list($path, $isDir, $bytes, $createdDate, $lastModified, $mimeType, $icon, $permission, $revision) =
            $this->doFormat($metadata);

        $items = isset($metadata['contents']) ? $metadata['contents'] : null;
        $abstractFormat = array('path'         => $path, 'isDir' => $isDir, 'bytes' => $bytes,
                                'createdDate'  => $createdDate, 'lastModified' => $lastModified,
                                'mimeType'     => $mimeType, 'icon' => $icon, 'permission' => $permission,
                                'revision'     => $revision, 'items' => $this->getMetadataOfItems($items));

        return $abstractFormat;
    }

    /**
     * Returns the abstract format for the given items for folders. This time without 'items' filed.
     *
     * @param $items
     * @return array|null
     */
    private function getMetadataOfItems($items)
    {
        if ($items != null) {
            $metadataOfItems = array();
            foreach ($items as $item) {
                list($path, $isDir, $bytes, $createdDate, $lastModified, $mimeType, $icon, $permission, $revision) =
                    $this->doFormat($item);

                $item = array('path'         => $path, 'isDir' => $isDir, 'bytes' => $bytes,
                              'createdDate'  => $createdDate, 'lastModified' => $lastModified, 'mimeType' => $mimeType,
                              'icon'         => $icon, 'permission' => $permission, 'revision' => $revision);
                array_push($metadataOfItems, $item);
            }

            return $metadataOfItems;
        } else {
            return $items;
        }
    }

    /**
     * Returns the new format of the given item.
     *
     * Following parameters make the structure of the abstract format:
     *  path: Returns the canonical path to the file or directory.
     *  isDir: Indicates whether the entry is a folder or not.
     *  bytes: The file size in bytes
     *  createdDate: The creations date of the file or folder. It can be null.
     *  lastModified: The last time the file was modified on Dropbox.
     *  mimeType: Identifies the format of a file.
     *  icon: The name or type of the icon can be used to illustrate the file type.
     *  permission: Identifies the Read-Write rights for a file. It can be null.
     *  revision:
     *      hash: Can be used for indicating change's to the folder's contents.
     *      rev: A unique identifier for the current revision of a file. It can be used to detect changes.
     *
     * @param $item
     * @return array
     */
    private function doFormat($item)
    {
        $path = isset($item['path']) ? $item['path'] : null;
        $isDir = isset($item['is_dir']) ? $item['is_dir'] : null;
        $bytes = isset($item['bytes']) ? $item['bytes'] : null;
        $createdDate = isset($item['created_date']) ? $item['created_date'] : null;
        $lastModified = isset($item['modified']) ? $item['modified'] : null;
        $mimeType = isset($item['mime_type']) ? $item['mime_type'] : null;
        $icon = isset($item['icon']) ? $item['icon'] : null;
        $permission = isset($item['permission']) ? $item['permission'] : null;

        if ($isDir) {
            $revision = isset($item['hash']) ? $item['hash'] : null;
        } else {
            $revision = isset($item['rev']) ? $item['rev'] : null;
        }

        return array($path, $isDir, $bytes, $createdDate, $lastModified, $mimeType, $icon, $permission, $revision);
    }

    /**
     * Sends requests using BUZZ Library.
     *
     * @param array $params
     * @return Response
     */
    private function sendRequest($params = array())
    {
        $urlBase = $params['urlBase'];
        $httpMethod = $params['httpMethod'];
        $headers = $params['headers'];
        $content = isset($params['content']) ? $params['content'] : null;


        $request = new Request();
        $response = new Response();

        $request->fromUrl($urlBase);
        $request->setMethod($httpMethod);
        $request->addHeaders($headers);
        $request->setContent($content);

        $client = new FileGetContents();
        $client->send($request, $response);

        return $response;
    }

    /**
     * Trims the given file and path name.
     *
     * @param $file
     * @param $path
     * @return array
     */
    private function doTrim($file, $path)
    {
        $path = trim($path, '/');
        $path = '/' . $path . '/';
        $file = trim($file, '/');

        return array($file, $path);
    }
}
