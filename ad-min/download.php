<?php
// services/database.php (or include your configuration file here)
include_once('./services/database.php');

if (isset($_GET['file'])) {
    // Get the file name from the query string
    $file = urldecode($_GET['file']); // Decode URL-encoded file name

    // Define the path to your file directory
    $file_path = $_SERVER['DOCUMENT_ROOT'] . $file;

    // Check if the file exists
    if (file_exists($file_path)) {
        // Set the headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        // Read the file and output it to the browser
        readfile($file_path);
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
