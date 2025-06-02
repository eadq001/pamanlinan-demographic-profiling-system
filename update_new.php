<?php
$conn = new mysqli("localhost", "root", "", "pamanlinan_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) {
        echo "<script>alert('Invalid ID.'); window.location.href='list.php';</script>";
        exit;
    }

    // Collect and sanitize form inputs
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $ext_name = trim($_POST['ext_name']);
    $sex_name = $_POST['sex_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $civil_status = $_POST['civil_status'];
    $place_of_birth = trim($_POST['place_of_birth']);
    $street_name = trim($_POST['street_name']);
    $purok_name = trim($_POST['purok_name']);
    $cellphone_no = trim($_POST['cellphone_no']);
    $facebook = trim($_POST['facebook']);
    $employed_unemployed = trim($_POST['employed_unemployed']);
    $occupation = trim($_POST['occupation']);
    $solo_parent = $_POST['solo_parent'];
    $ofw = trim($_POST['ofw']);
    $school_youth = $_POST['school_youth'];
    $pwd = trim($_POST['pwd']);
    $indigenous = trim($_POST['indigenous']);
    $citizenship = trim($_POST['citizenship']);
    $toilet = $_POST['toilet'];
    $valid_id = trim($_POST['valid_id']);
    $type_id = trim($_POST['type_id']);

    $stmt = $conn->prepare("UPDATE people SET 
        last_name = ?, first_name = ?, middle_name = ?, ext_name = ?, sex_name = ?, date_of_birth = ?, civil_status = ?, 
        place_of_birth = ?, street_name = ?, purok_name = ?, cellphone_no = ?, facebook = ?, employed_unemployed = ?, 
        occupation = ?, solo_parent = ?, ofw = ?, school_youth = ?, pwd = ?, indigenous = ?, citizenship = ?, 
        toilet = ?, valid_id = ?, type_id = ? WHERE id = ?");
    $stmt->bind_param("ssssssssssssssssssssssi",
        $last_name, $first_name, $middle_name, $ext_name, $sex_name, $date_of_birth, $civil_status,
        $place_of_birth, $street_name, $purok_name, $cellphone_no, $facebook, $employed_unemployed,
        $occupation, $solo_parent, $ofw, $school_youth, $pwd, $indigenous, $citizenship, $toilet,
        $valid_id, $type_id, $id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Record updated successfully.'); window.location.href='list.php';</script>";
    } else {
        echo "<script>alert('Error updating record: " . $stmt->error . "'); window.location.href='edit.php?id=$id';</script>";
    }
    $stmt->close();
} else {
    header("Location: list.php");
    exit;
}
$conn->close();
?>