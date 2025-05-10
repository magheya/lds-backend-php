<?php
// api/upload-handler.php

class UploadHandler {
    private $uploadDir;
    
    public function __construct($uploadDir = null) {
        if ($uploadDir === null) {
            $uploadDir = __DIR__ . '/../assets/uploads';
        }
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $this->uploadDir = $uploadDir;
    }
    
    public function handleUpload($file) {
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadErrorMessage($file['error']));
        }
        
        // Generate a unique filename
        $filename = time() . '-' . basename($file['name']);
        $targetPath = $this->uploadDir . '/' . $filename;
        
        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/assets/uploads/' . $filename;
        } else {
            throw new Exception("Failed to move uploaded file");
        }
    }
    
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload";
            default:
                return "Unknown upload error";
        }
    }
}