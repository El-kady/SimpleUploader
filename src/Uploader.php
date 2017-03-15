<?php

/**
 * Class Uploader BY Moustafa ELkady <Moustafa.M.Elkady@gmail.com>
 * @package Core\System
 */
class Uploader
{
    private $upload_tree = [];
    private $upload_dist;
    private $file = [];
    private $file_array = array();
    private $uploaded = false;
    private $max_file_size = 0;
    private $mime_types_map = array(
        'text' => array(
            'text/plain' => ["txt"],
        ),
        'image' => array(
            'image/jpeg' => ["jpeg"],
            'image/jpg' => ["jpg"],
            'image/pjpeg' => ["pjpeg"],
            'image/png' => ["png"],
            'image/gif' => ["gif"],
        ),
        'document' => array(
            'application/pdf' => ["pdf"],
            'application/msword' => ["doc"],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ["docx"],
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ["dotx"],
            'application/vnd.ms-powerpoint' => ["ppa", "pptx"],
            'application/vnd.ms-excel' => ["xls", "xlsb", "xlsx"],
            'application/vnd.oasis.opendocument.spreadsheet' => ["ods"],
            'application/vnd.oasis.opendocument.presentation' => ["ppt", "pptx"],
        ),
        'video' => array(
            'video/3gpp' => ["3gpp"],
            'video/avi' => ["avi"],
            'video/mpeg4' => ["mpeg4"],
            'video/mp4' => ["mp4"],
            'video/mpeg' => ["mpeg"],
            'video/mpg' => ["mpg"],
            'video/x-ms-wmv' => ["wmv"],
            'video/x-flv' => ["flv"],
        ),
    );

    private $allowed_mime_types = [];
    private $allowed_extensions = [];

    function __construct($upload_root, $max_file_size = 0)
    {
        $this->upload_tree = array(date("Y"), date("m"));
        $this->upload_dist = $upload_root . implode(DIRECTORY_SEPARATOR, $this->upload_tree) . DIRECTORY_SEPARATOR;
        $this->max_file_size = $max_file_size;

        if (isset($_FILES) AND is_array($_FILES)) {
            $this->file_array = $_FILES;
        }
    }

    public function upload($input_name, $allowed_type = [])
    {
        $this->setFile($input_name);
        $this->setTypes($allowed_type);

        if ($this->makeDir($this->upload_dist, 0755) && is_writable($this->upload_dist)) {
            if (in_array($this->file["type"], $this->allowed_mime_types) && in_array($this->file["extension"], $this->allowed_extensions)) {
                if ($this->max_file_size > $this->file["size"]) {
                    if (move_uploaded_file($this->file["tmp_name"], $this->upload_dist . $this->file["physical_name"])) {
                        $this->uploaded = true;
                    } else {
                        throw new \Exception("UPLOAD_MOVE_ERROR");
                    }
                } else {
                    throw new \Exception("UPLOAD_EXCEEDIRECTORY_SEPARATOR_SIZE");
                }
            } else {
                throw new \Exception("UPLOAD_TYPE_NOT_ALLOWED");
            }
        } else {
            throw new \Exception("UPLOAD_CANT_USE_DIST");
        }
    }

    public function isUploaded()
    {
        return $this->uploaded;
    }

    public function getFile($field = null)
    {
        return ($field) ? $this->file[$field] : $this->file;
    }

    private function setFile($input_name)
    {
        if (isset($this->file_array[$input_name]) && $this->file_array[$input_name]["error"] == 0) {
            $physical_name = $this->generateRandName($this->file_array[$input_name]["name"]);
            $this->file = [
                "original_name" => $this->file_array[$input_name]["name"],
                "physical_name" => $physical_name,
                "physical_url" => implode("/", $this->upload_tree) . '/' . $physical_name,
                "type" => $this->file_array[$input_name]["type"],
                "tmp_name" => $this->file_array[$input_name]["tmp_name"],
                "size" => (int)$this->file_array[$input_name]["size"],
                "extension" => $this->getExtension($this->file_array[$input_name]["name"])
            ];
        } else {
            throw new \Exception("UPLOAD_EMPTY");
        }
    }

    private function getExtension($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }

    private function generateRandName($name)
    {
        return substr(md5(time() * rand()), 0, 10) . "." . $this->getExtension($name);
    }

    private function setTypes($allowed_type = [])
    {
        foreach ($allowed_type as $type) {
            foreach ($this->mime_types_map[$type] as $mime_type => $extensions) {
                $this->allowed_mime_types[] = $mime_type;
                $this->allowed_extensions = array_merge($this->allowed_extensions, $extensions);
            }
        }
    }

    private function makeDir($path, $mode = 0777)
    {
        return is_dir($path) || ($this->makeDir(dirname($path), $mode) && $this->_makeDir($path, $mode));
    }

    private function _makeDir($path, $mode = 0777)
    {
        $old = umask(0);
        $res = mkdir($path, $mode);
        umask($old);
        return $res;
    }


}