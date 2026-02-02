<?php
include 'secure/config.php';
$escape = "\\";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($usedob){
        $input = isset($_POST['input']) ? trim($_POST['input']) : '';
    }
    $id = isset($_POST['id']) ? $_POST['id'] : ''; // Identify the correct user

    // Open the CSV file and search for the matching user
    $uploadDir = 'secure/uploads/';
    $useFile = $uploadDir . $selectedFile;

    if (($handle = fopen($useFile, 'r')) !== false) {
        $header = fgetcsv($handle, 0, ",", '"', $escape); // Skip header row
        while (($data = fgetcsv($handle, 0, ",", '"', $escape)) !== false) {
            $csvId = isset($data[0]) ? htmlspecialchars($data[0]) : null;
            $dob = isset($data[7]) ? trim($data[7]) : null;

            if ($csvId == $id) { // Find the correct user by ID
                fclose($handle);
                if($usedob){
                    if ($input === $dob) {
                        echo json_encode(["message" => htmlspecialchars($data[4]) . "\n" . htmlspecialchars($data[6])], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    } else {
                        echo json_encode(["message" => "Incorrect date of birth. Access denied."]);
                    }
                }else{
                    echo json_encode(["message" => htmlspecialchars($data[4]) . "\n" . htmlspecialchars($data[6])], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
                exit;
            }
        }
        fclose($handle);
    }

    echo json_encode(["message" => "Student not found!"]);
}
?>
