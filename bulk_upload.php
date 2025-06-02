<!DOCTYPE html>
<html>
<head>
    <title>Bulk Upload</title>
</head>
<body>
    <form action="bulk_upload.php" method="post" enctype="multipart/form-data">
        <label>Select CSV file to upload:</label>
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit" name="upload">Upload</button>
    </form>
</body>
</html>

<?php
if (isset($_POST['upload'])) {
    $conn = new mysqli("localhost", "root", "", "pamanlinan_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    function calculate_age($dob) {
        $birthDate = new DateTime($dob);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    }

    if ($_FILES['csv_file']['error'] == 0) {
        $filename = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($filename, "r")) !== false) {
            $header = fgetcsv($handle); // Skip header row

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Assuming column order in CSV matches your DB structure
                list($first_name, $last_name, $middle_name, $ext_name, $sex_name,
                     $date_of_birth, $civil_status, $place_of_birth, $street_name, $purok_name,
                     $cellphone_no, $facebook, $employed_unemployed, $occupation,
                     $solo_parent, $ofw, $school_youth, $pwd, $indigenous, $citizenship,
                     $toilet, $valid_id, $type_id) = $data;

                $age = calculate_age($date_of_birth);

                // Optional: check for duplicate here using a SELECT query
                $stmt = $conn->prepare("INSERT INTO people (
                    first_name, last_name, middle_name, ext_name, sex_name, date_of_birth, age, civil_status,
                    place_of_birth, street_name, purok_name, cellphone_no, facebook, employed_unemployed, occupation,
                    solo_parent, ofw, school_youth, pwd, indigenous, citizenship, toilet, valid_id, type_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("ssssssisssssssssssssssss",
                    $first_name, $last_name, $middle_name, $ext_name, $sex_name, $date_of_birth, $age, $civil_status,
                    $place_of_birth, $street_name, $purok_name, $cellphone_no, $facebook, $employed_unemployed, $occupation,
                    $solo_parent, $ofw, $school_youth, $pwd, $indigenous, $citizenship, $toilet, $valid_id, $type_id
                );

                $stmt->execute();
                $stmt->close();
            }
            fclose($handle);
            echo "<script>alert('CSV uploaded successfully.'); window.location.href='list.php';</script>";
        } else {
            echo "<script>alert('Error opening the file.');</script>";
        }
    } else {
        echo "<script>alert('Upload error.');</script>";
    }

    $conn->close();
}
?>
