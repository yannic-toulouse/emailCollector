<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">    
        <link rel="stylesheet" href="style.css">
    </head>
<?php
$escape = "\\";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $latestFile = $_POST['file'] ?? null;
    $email = $_POST['email'] ?? null;
    $sFirstName = $_POST['sFirstName'] ?? null;
    $sLastName = $_POST['sLastName'] ?? null;
    $pLastName = $_POST['pLastName'] ?? null;
    $pLastName2 = $_POST['pLastName2'] ?? null;
    $email2 = $_POST['email2'] ?? null;
    $id = $_POST['id'] ?? null;
    $emailSubmitted = $_POST['emailSubmitted'] ?? null;
    $emailSubmitted2 = $_POST['emailSubmitted2'] ?? null;
    
    if ($latestFile && $email && $sFirstName && $sLastName && $id && $pLastName || $latestFile && $sFirstName && $sLastName && $id && $pLastName && $emailSubmitted == true) {
        if (file_exists($latestFile)) {
            $tempFile = $latestFile . '.tmp';

            if (($inputHandle = fopen($latestFile, 'r')) !== false && ($outputHandle = fopen($tempFile, 'w')) !== false) {
                $header = fgetcsv($inputHandle, 0, ",", '"', $escape);
                if ($header) {
                    fputcsv($outputHandle, $header, ",", '"', $escape, PHP_EOL);
                }

                $updated = false;

                while (($row = fgetcsv($inputHandle, 0, ",", '"', $escape)) !== false) {
                    if (isset($row[0],$row[1], $row[2]) && $row[0] === $id && $row[1] === $sFirstName && $row[2] === $sLastName) {
                        $row[3] = $pLastName; // Update Parent1 last name
                        //Only change e-mail if one wasn't yet submitted
                        if(!$emailSubmitted)
                        {
                            $row[4] = $email; // Update e-mail
                        }elseif($email != ""){ //Check if e-mail was changed
                            $row[4] = $email; //Set to new e-mail
                        }
                        $row[5] = $pLastName2; // Update second last name
                        if(!$emailSubmitted2){
							$row[6] = $email2; //Update second e-mail
						}elseif($email2 != ""){
                            $row[6] = $email2;
                        }
                        $updated = true;
                    }
                    fputcsv($outputHandle, $row, ",", '"', $escape, PHP_EOL);
                }

                fclose($inputHandle);
                fclose($outputHandle);

                if ($updated) {
                    rename($tempFile, $latestFile);
                    echo '<h1>Die Daten wurden erfolgreich aktualisiert.</h1>';
                } else {
                    unlink($tempFile);
                    echo '<h1>Fehler: Kein passender Eintrag gefunden.</h1>';
                }
            } else {
                echo '<h1>Fehler: Die Datei konnte nicht geöffnet werden.</h1>';
            }
        } else {
            echo '<h1>Fehler: Die angegebene Datei existiert nicht.</h1>';
        }
    } else {
        echo '<h1>Fehler: Ungültige Eingabedaten.</h1>';
    }
} else {
    echo '<h1>Fehler: Ungültige Anfragemethode.</h1>';
}
?>
<a href="index.php"><button class="sbutton">Zurück zur Startseite</button></a>
</html>
