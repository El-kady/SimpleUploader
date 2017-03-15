<?php

include "src/Uploader.php";

$uploader = new Uploader(dirname(__FILE__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR ,1024 * 1024 * 2);

if (!empty($_FILES["file_input"]["name"])) {
    try {
        $uploader->upload("file_input",["image"]);
        if ($uploader->isUploaded()) {
            echo "<pre>".print_r($uploader->getFile(),true)."</pre>";
        }
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}

?>

<form method="post" enctype="multipart/form-data">
    <p>
        <label for="file">File</label>
        <input type="file" name="file_input">
    </p>
    <p>
        <button type="submit">Upload</button>
    </p>
</form>
