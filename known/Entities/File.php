<?php

    /**
     * User-created file representation
     *
     * @package known
     * @subpackage core
     */

    namespace known\Entities {

        class File
        {

            /**
             * Return the MIME type associated with this file
             * @return null|string
             */
            function getMimeType()
            {
                $mime_type = $this->mime_type;
                if (!empty($mime_type)) {
                    return $this->mime_type;
                }

                return 'application/octet-stream';
            }

            /**
             * Get the publicly visible filename associated with this file
             * @return string
             */
            function getURL()
            {
                if (!empty($this->_id)) {
                    return \known\Core\site()->config()->url . 'file/' . $this->_id . '/' . urlencode($this->filename);
                }

                return '';
            }

            function delete()
            {
                // TODO deleting files would be good ...
            }

            /**
             * Save a file to the filesystem and return the ID
             *
             * @param string $file_path Full local path to the file
             * @param string $filename Filename to store
             * @param string $mime_type MIME type associated with the file
             * @param bool $return_object Return the file object? If set to false (as is default), will return the ID
             * @return bool|\MongoID Depending on success
             */
            public static function createFromFile($file_path, $filename, $mime_type = 'application/octet-stream', $return_object = false)
            {
                if (file_exists($file_path) && !empty($filename)) {
                    if ($fs = \known\Core\site()->db()->getFilesystem()) {
                        $file     = new File();
                        $metadata = array(
                            'filename'  => $filename,
                            'mime_type' => $mime_type
                        );
                        if ($id = $fs->storeFile($file_path, $metadata, $metadata)) {
                            if (!$return_object) {
                                return $id;
                            } else {
                                return self::getByID($id);
                            }
                        }
                    }
                }

                return false;
            }

            /**
             * Determines whether a file is an image or not.
             * @param string $file_path The path to a file
             * @return bool
             */
            public static function isImage($file_path)
            {
                if ($photo_information = getimagesize($file_path)) {
                    return true;
                }

                return false;
            }

            /**
             * Given a path to an image on disk, generates and saves a thumbnail with maximum dimension $max_dimension.
             * @param string $file_path Path to the file.
             * @param string $filename Filename that the file should have on download.
             * @param int $max_dimension The maximum number of pixels the thumbnail image should be along its longest side.
             * @return bool|MongoID
             */
            public static function createThumbnailFromFile($file_path, $filename, $max_dimension = 800)
            {

                $thumbnail = false;

                if ($photo_information = getimagesize($file_path)) {
                    if ($photo_information[0] > $max_dimension || $photo_information[1] > $max_dimension) {
                        switch ($photo_information['mime']) {
                            case 'image/jpeg':
                                $image = imagecreatefromjpeg($file_path);
                                break;
                            case 'image/png':
                                $image = imagecreatefrompng($file_path);
                                break;
                            case 'image/gif':
                                $image = imagecreatefromgif($file_path);
                                break;
                        }
                        if (!empty($image)) {
                            if ($photo_information[0] > $photo_information[1]) {
                                $width  = $max_dimension;
                                $height = round($photo_information[1] * ($max_dimension / $photo_information[0]));
                            } else {
                                $height = $max_dimension;
                                $width  = round($photo_information[0] * ($max_dimension / $photo_information[1]));
                            }
                            $image_copy = imagecreatetruecolor($width, $height);
                            imagecopyresampled($image_copy, $image, 0, 0, 0, 0, $width, $height, $photo_information[0], $photo_information[1]);

                            if (is_callable('exif_read_data')) {
                                $exif = exif_read_data($file_path);
                                if (!empty($exif['Orientation'])) {
                                    switch ($exif['Orientation']) {
                                        case 8:
                                            $image_copy = imagerotate($image_copy, 90, 0);
                                            break;
                                        case 3:
                                            $image_copy = imagerotate($image_copy, 180, 0);
                                            break;
                                        case 6:
                                            $image_copy = imagerotate($image_copy, -90, 0);
                                            break;
                                    }
                                }
                            }

                            $tmp_dir = dirname($file_path);
                            switch ($photo_information['mime']) {
                                case 'image/jpeg':
                                    imagejpeg($image_copy, $tmp_dir . '/' . $filename . '.jpg');
                                    $thumbnail = \known\Entities\File::createFromFile($tmp_dir . '/' . $filename . '.jpg', 'thumb.jpg', 'image/jpeg') . '/thumb.jpg';
                                    @unlink($tmp_dir . '/' . $filename . '.jpg');
                                    break;
                                case 'image/png':
                                    imagepng($image_copy, $tmp_dir . '/' . $filename . '.png');
                                    $thumbnail = \known\Entities\File::createFromFile($tmp_dir . '/' . $filename . '.png', 'thumb.png', 'image/png') . '/thumb.png';
                                    @unlink($tmp_dir . '/' . $filename . '.png');
                                    break;
                                case 'image/gif':
                                    imagegif($image_copy, $tmp_dir . '/' . $filename . '.gif');
                                    $thumbnail = \known\Entities\File::createFromFile($tmp_dir . '/' . $filename . '.gif', 'thumb.gif', 'image/gif') . '/thumb.gif';
                                    @unlink($tmp_dir . '/' . $filename . '.gif');
                                    break;
                            }
                        }
                    } else {

                    }

                    return $thumbnail;

                }

                return false;
            }

            /**
             * Retrieve a file by UUID
             * @param string $uuid
             * @return bool|\known\Common\Entity
             */
            static function getByUUID($uuid)
            {
                if ($fs = \known\Core\site()->db()->getFilesystem()) {
                    return $fs->findOne($uuid);
                }

                return false;
            }

            /**
             * Retrieve a file by ID
             * @param string $id
             * @return \known\Common\Entity|\MongoGridFSFile|null
             */
            static function getByID($id)
            {
                if ($fs = \known\Core\site()->db()->getFilesystem()) {
                    return $fs->findOne(array('_id' => new \MongoId($id)));
                }

                return false;
            }

            /**
             * Retrieve file data by ID
             * @param string $id
             * @return mixed
             */
            static function getFileDataByID($id)
            {
                if ($file = self::getByID($id)) {
                    return $file->getBytes();
                }

                return false;
            }

        }

    }