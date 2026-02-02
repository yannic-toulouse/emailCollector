<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Dashboard</title>
</head>

<body>
    <h1>Upload CSV file</h1>

    <?php
    namespace secure;
    include 'config.php';
    $escape = "\\";

    //Delete temp files
    $tmpDir = 'tmp/';
    $maxFileAge = 7200; //2 hours in seconds

    if (is_dir($tmpDir)) {
        foreach (scandir($tmpDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $filePath = $tmpDir . $file;
            if (is_file($filePath)) {
                if (filemtime($filePath) < (time() - $maxFileAge)) {
                    unlink($filePath);
                }
            }
        }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_upload']) && isset($_POST['filename'])) {
        $fileName = basename($_POST['filename']);
        $tmpPath = 'tmp/' . $fileName;
        $finalPath = 'uploads/' . $fileName;

        if ($_POST['confirm_upload'] == "1") {
            // Move the file from tmp to uploads
            if (file_exists($tmpPath)) {
                if (!is_dir('uploads/')) {
                    mkdir('uploads/', 0777, true);
                }
                if (rename($tmpPath, $finalPath)) {
                    // Update config.php as before
                    $config_file = 'config.php';
                    $config_content = file_get_contents($config_file);
                    $var_str = var_export($fileName, true);
                    $config_content = preg_replace('/\$selectedFile\s*=\s*[^;]*;/', "\$selectedFile = $var_str;", $config_content);
                    file_put_contents($config_file, $config_content);
                    echo "<p>Datei <strong>$fileName</strong> wurde gespeichert und als aktive Liste gespeichert.</p>";
                } else {
                    echo "<p>Fehler: Datei konnte nicht nach <code>uploads/</code> verschoben werden.</p>";
                }
            } else {
                echo "<p>Fehler: Temporäre Datei existiert nicht (mehr).</p>";
            }
        } else {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
                echo "Datei wurde gelöscht.";
            } else {
                echo "Datei existiert nicht (mehr).";
            }
        }
    }


    //PREVIEW LOGIC
    // Upload CSV-File
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check file upload
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $uploadTmpDir = 'tmp/';
            if (!is_dir($uploadTmpDir)) {
                mkdir($uploadTmpDir, 0777, true);
            }

            $uploadedFile = $_FILES['csv_file']['tmp_name'];
            $fileName = basename($_FILES['csv_file']['name']);
            $tmpPath = $uploadTmpDir . $fileName;

            if (move_uploaded_file($uploadedFile, $tmpPath)) {
                echo "<p>Datei wurde erfolgreich hochgeladen: <strong>$fileName</strong></p>";

                // Preview file
                if (($handle = fopen($tmpPath, "r")) !== false) {
                    echo "<h2>Vorschau der Datei:</h2><table border='1'>";
                    fgetcsv($handle, 0, ",", '"', $escape);
                    $rowCount = 0;
                    echo "<tr class='headerRow'><th>ID</th><th>Student first name</th><th>Student last name</th><th>Parent 1 first &amp; last name</th><th>Parent 1 E-Mail</th><th>Parent 2 first &amp; last name</th><th>Parent 2 E-Mail</th><th>Student date of birth</th></tr>";
                    while (($data = fgetcsv($handle, 0, ",", '"', $escape)) !== false && $rowCount < 10) {
                        echo "<tr>";
                        foreach ($data as $cell) {
                            echo "<td>" . htmlspecialchars($cell) . "</td>";
                        }
                        echo "</tr>";
                        $rowCount++;
                    }
                    fclose($handle);
                    echo "</table>";

                    echo '<form method="post" action="">
                        <input type="hidden" name="confirm_upload" value="1">
                        <input type="hidden" name="filename" value="' . htmlspecialchars($fileName) . '">
                        <button class="sbutton" type="submit">Use this list</button>
                      </form>';
                    echo '</br>';
                    echo '<form method="post" action="">
                        <input type="hidden" name="confirm_upload" value="0">
                        <input type="hidden" name="filename" value="' . htmlspecialchars($fileName) . '">
                        <button class="critical" type="submit">Cancel</button>
                        </form>';
                } else {
                    echo '<p>Error: File couldn\'t be read.</p>';
                }
            } else {
                echo '<p>Error: File couldn\'t be saved.</p>';
            }
        }
        echo '</br>';
    }

    ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label for="csv_file">Select a CSV file:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <input type="hidden" name="preview" value="1">
        <br><br>
        <button class="sbutton" type="submit">Upload</button>
    </form>

    <h1>Select list</h1>
    <?php
    $uploadDir = 'uploads/';

    if (is_dir($uploadDir)) {
        $files = array_diff(scandir($uploadDir), ['.', '..']);

        if (!empty($files)) {
            // Check if file was selected
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selfile'])) {
                $selectedFile = basename($_POST['selfile']);
                $filePath = $uploadDir . $selectedFile;

                if (file_exists($filePath)) {
                    $config_file = 'config.php';
                    $config_content = file_get_contents($config_file);

                    // Use a regular expression to update only the $usedob variable
                    $var_str = var_export($selectedFile, true);
                    $config_content = preg_replace('/\$selectedFile\s*=\s*[^;]*;/', "\$selectedFile = $var_str;", $config_content);

                    // Write the updated content to config.php file
                    file_put_contents($config_file, $config_content);

                    // Refresh page
                    header("Refresh:0");
                    exit;
                } else {
                    echo '<p>Error: File couldn\'t be found.</p>';
                }
            }

            echo '<form method="post" action="">';
            echo '<label for="file">Select a list: </label>';
            echo '<select name="selfile" id="selfile">';

            foreach ($files as $file) {
                echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</option>';
            }
            echo '</select><br>';
            echo 'Selected list: <strong>' . $selectedFile . '</strong>';
            echo '<br><br><button class="sbutton" type="submit">Save</button>';
            echo '</form>';
        } else {
            echo '<p>No files were found in the uploads folder.</p>';
        }
    } else {
        echo '<p>The uploads folder doesn\'t exist.</p>';
    }
    ?>

    <h1>Delete file</h1>

    <?php
    $uploadDir = 'uploads/';

    //Check if uploads folder exists
    if (is_dir($uploadDir)) {
        $files = array_diff(scandir($uploadDir), ['.', '..']); //Get files in uploads folder

        if (!empty($files)) {
            echo '<form method="post" action="">';
            echo '<label for="file">Select a file: </label>';
            echo '<select name="delfile" id="delfile">';

            foreach ($files as $file) {
                echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</option>';
            }

            echo '</select><br><br>';
            echo '<button class="critical" type="submit">DELETE SELECTED FILE</button>';
            echo '</form>';

            //Check if a file was selected
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delfile'])) {
                $selectedFile = basename($_POST['delfile']);
                $filePath = $uploadDir . $selectedFile;

                if (file_exists($filePath)) {
                    unlink($filePath);
                } else {
                    echo '<p>Error: File wasn\'t found.</p>';
                }
                header("Refresh:0");
            }
        } else {
            echo '<p>No files found in uploads folder.</p>';
        }
    } else {
        echo '<p>The upload folder doesn\'t exist.</p>';
    }
    ?>
    <br>
    <form method="post">
        <button class="critical" type="submit" name="delete">DELETE ALL UPLOADED FILES</button>
        <?php
        if (isset($_POST['delete'])) {
            $uploadDir = 'uploads/';
            //Load all files
            $files = glob($uploadDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        ?>

    </form>

    <h1>Download CSV file</h1>

    <?php
    $uploadDir = 'uploads/';

    //Check if uploads folder exists
    if (is_dir($uploadDir)) {
        $files = array_diff(scandir($uploadDir), ['.', '..']); //Files in uploads folder

        if (!empty($files)) {
            echo '<form method="post" action="">';
            echo '<label for="file">Select a file: </label>';
            echo '<select name="file" id="file">';

            foreach ($files as $file) {
                echo '<option value="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</option>';
            }

            echo '</select><br><br>';
            echo '<button class="sbutton" type="submit">Download</button>';
            echo '</form>';

            //Check if a file was selected
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file'])) {
                $selectedFile = basename($_POST['file']);
                $filePath = $uploadDir . $selectedFile;

                if (file_exists($filePath)) {
                    ob_clean(); //Clear HTML content before starting download
                    //Start download
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($filePath));
                    readfile($filePath);
                    exit;
                } else {
                    echo '<p>Error: The file wasn\'t found.</p>';
                }
            }
        } else {
            echo '<p>No files were found in the uploads folder.</p>';
        }
    } else {
        echo '<p>The uploads folder doesn\'t exist.</p>';
    }
    ?>
    <?php
    //Save date of birth config
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drop'])) {
        $usedob = $_POST['drop'];

        // Read the existing content of the config.php file
        $config_file = 'config.php';
        $config_content = file_get_contents($config_file);

        // Use a regular expression to update only the $usedob variable
        $config_content = preg_replace('/\$usedob\s*=\s*[^;]*;/', "\$usedob = $usedob;", $config_content);

        // Write updated content back to config.php file
        file_put_contents($config_file, $config_content);
        header("Refresh:0");
        exit;
    }

    ?>
    <h1>Use date of birth to secure E-Mails?</h1>
    <form method="post">
        <select id="drop" name="drop">
            <?php
            if ($usedob) {
                echo '<option value="true">Yes</option>';
                echo '<option value="false">No</option>';
            } else {
                echo '<option value="false">No</option>';
                echo '<option value="true">Yes</option>';
            }
            ?>
        </select>
        <br>
        <button class="sbutton" type="submit">Save</button>
    </form>
    <br>
    <br>
    <a class="link" href="../index.php"><button class="sbutton">Back to Start</button></a>
</body>

</html>