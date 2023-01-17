<?php
// Upload script to be included in admin.php.
// Store file directory (where to be moved).
$directory = "uploads/";
// Store full file path.
$file = $directory . basename($_FILES["img_upload"]["name"]);
// Store filetype.
$filetype = strtolower(pathinfo($file, PATHINFO_EXTENSION));


// Check if the imagefile is actually added in the form.
if (!is_null($_FILES['img_upload'])) {

    // Check if the file already exists, if not having checked to overwrite.
    if (!$overwrite) {
        if (file_exists($file)) {
            echo "Error: File already exists.";
            echo "<br>";
            $canUpload = false;
        }
    }


    // Require the file type to be jpg/jpeg.
    if ($filetype != "jpg" && $filetype != "jpeg") {
        echo "Error: Only JPG/JPEG allowed!";
        echo "<br>";
        $canUpload = false;
    }

    // If no upload problems are presented:
    if ($canUpload) {
        // Move the uploaded file from the temp directory to the actual file directory.
        if (move_uploaded_file($_FILES["img_upload"]["tmp_name"], $file)) {
            // If this succeeds, save the name of the image-file in the product_img variable
            // and confirm the upload is done.
            $product_img = $_FILES["img_upload"]["name"];
            $uploadDone = true;
        } else {
            // Could not move the file, upload was not completed.
            echo "Sorry, there was an error moving your file.";
            echo "<br>";
            $uploadDone = false;
        }
    } else {
        // Could not upload the file, upload was not completed.
        echo "Error: Could not upload file.";
        echo "<br>";
        $uploadDone = false;
    }
} else {
    // The user did not select a file.
    echo "Error: No file chosen.";
    echo "<br>";
    $uploadDone = false;
}
