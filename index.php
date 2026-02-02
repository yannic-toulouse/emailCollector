<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Collector</title>
</head>

<body>
    <h1 class="main-header">Student List</h1>
    <?php
    include 'secure/config.php';
    $uploadDir = 'secure/uploads/';
    $escape = '\\';
    // Check if folder exists
    if (is_dir($uploadDir)) {
        $useFile = $uploadDir . $selectedFile;
        if ($useFile && file_exists($useFile)) {
            echo "<p>Displayed File: <strong>" . basename($selectedFile) . "</strong></p>";

            // Open File and read content
            if (($handle = fopen($useFile, 'r')) !== false) {
                echo '<table border="1">';
                echo '<tr class="headerRow"><th>Lfd.</br>Nr.</th><th>Student</br>First Name</th><th>Student</br>Last Name</th><th>Parent 1</br>First &amp; Last Name <span style="color:red";>*</span></th><th>Parent 1</br>E-Mail <span style="color:red";>*</span></th><th>Parent 2</br>First- &amp; Last Name</br>(optional)</th><th>Elternteil 2</br>E-Mail</br>(optional)</th><th>Aktion</th></tr>';

                echo '<script>
                        function dobCheck(userId) {
                            let usedob = "' . $usedob . '";
                            let userInput;
                            if(usedob == "1"){
                                userInput = prompt("Please enter your childs date of birth in the format dd.mm.yyyy ein: ");
                            }else{
                                userInput = "";
                            }
                            console.log("Captured input:", userInput); // Debugging log

                                let xhr = new XMLHttpRequest();
                                xhr.open("POST", "ajax_handler.php", true); // Send to separate PHP script
                                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                                xhr.onreadystatechange = function () {
                                    if (xhr.readyState == 4 && xhr.status == 200) {
                                        console.log("Response received:", xhr.responseText);

                                        try {
                                            let response = JSON.parse(xhr.responseText);
                                            alert(response.message);
                                        } catch (e) {
                                            console.error("Error parsing JSON:", e);
                                        }
                                    }
                                };

                                xhr.send("input=" + encodeURIComponent(userInput) + "&id=" + encodeURIComponent(userId)); 
                                // Ensure `userId` is passed correctly
                        }
                    </script>';

                $header = fgetcsv($handle, 0, ",", '"', $escape); //Skip header row
                while (($data = fgetcsv($handle, 0, ",", '"', $escape)) !== false) {
                    //Check if all values were found
                    $id = isset($data[0]) ? htmlspecialchars($data[0]) : null;
                    $sFirstName = isset($data[1]) ? htmlspecialchars($data[1]) : null;
                    $sLastName = isset($data[2]) ? htmlspecialchars($data[2]) : null;
                    $pLastName = isset($data[3]) ? htmlspecialchars($data[3]) : null;
                    $email = isset($data[4]) ? htmlspecialchars($data[4]) : null;
                    $pLastName2= isset($data[5]) ? htmlspecialchars($data[5]) : null;
                    $email2 = isset($data[6]) ? htmlspecialchars($data[6]) : null;
                    if ($usedob) {
                        $dob = isset($data[7]) ? htmlspecialchars($data[7]) : null;
                    }

                    //Save missing variables
                    $missing_vars = [];
                    if (empty($id)) $missing_vars[] = "ID";
                    if (empty($sFirstName)) $missing_vars[] = "Student first name";
                    if (empty($sLastName)) $missing_vars[] = "Student last name";
                    if ($usedob) {
                        if (empty($dob)) $missing_vars[] = "Date of Birth";
                    }

                    //Stop execution and show error message if variables are missing
                    if (!empty($missing_vars)) {
                        die("<strong class='critical' >Error: The following data couldn't be read from the CSV file, it is likely missing or in the wrong columns: " . implode(", ", $missing_vars) . "</strong>
                        </br>
                        <button class='sbutton'><a class='link' href='secure/dashboard.php'>Back to the Dashboard</a></button>");
                    }
                    //Check if E-Mail was already entered
                    if ($email != NULL) {
                        $emailText = "placeholder='E-Mail already entered'";
                        $emailSubmitted = true;
                    } else {
                        $emailText = "required";
                        $emailSubmitted = false;
                    }
                    if ($email2 != NULL) {
                        $emailText2 = "placeholder='E-Mail already entered'";
                        $emailSubmitted2 = true;
                    } else {
                        $emailText2 = "";
                        $emailSubmitted2 = false;
                    }
                    echo '<tr>';
                    echo '<form method="post" action="action.php">';
                    echo '<td>' . $id . '</td>';
                    echo '<td>' . $sFirstName . '</td>';
                    echo '<td>' . $sLastName . '</td>';
                    echo '<td><input type="name" name="enachname" value="' . $pLastName . '" required/></td>';
                    echo '<td><input type="email" name="email" ' . $emailText . '/></td>';
                    echo '<td><input type="name" name="enachname2" value="' . $pLastName2 .'"/></td>';
                    echo '<td><input type="email" name="email2" ' . $emailText2 . '/></td>';
                    //hidden inputs
                    echo '<input type="hidden" name="svorname" value="' . $sFirstName . '"/>';
                    echo '<input type="hidden" name="snachname" value="' . $sLastName . '"/>';
                    echo '<input type="hidden" name="file" value="' . $useFile . '"/>';
                    echo '<input type="hidden" name="id" value="' . $id . '"/>';
                    echo '<input type="hidden" name="emailSubmitted" value="' . $emailSubmitted . '"/>';
                    echo '<input type="hidden" name="emailSubmitted2" value="' . $emailSubmitted2 . '"/>';
                    echo '<td><button type="submit" class="save">Save</button></td>';
                    echo '</form>';
                    //Only chow view Email button if email was already entered
                    if ($emailSubmitted || $emailSubmitted2) {
                        echo '<form method="post">';
                        echo '<td><input class="save" type="button" onclick="dobCheck(' . $id . ')" name="checkMail" value="View E-Mail"/></td>';
                        echo '</form>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
                fclose($handle);
            } else {
                echo '<p>Error: File couldn\'t be opened.</p>';
            }
        } else {
            echo '<p>File wasn\'t found in the uploads folder.</p>';
        }
    } else {
        echo '<p>Uploads folder doesn\'t exist.</p>';
    }
    ?>
    <a href="secure/dashboard.php">Dashboard</a>
</body>
</html>