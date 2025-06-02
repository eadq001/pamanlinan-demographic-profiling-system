<?php
//page can't be accessed when not logged in
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "pamanlinan_db");

// Get all POST values
$fields = [
    'last_name', 'first_name', 'middle_name', 'ext_name', 'sex_name', 'date_of_birth', 'age', 'civil_status',
    'place_of_birth', 'street_name', 'purok_name', 'cellphone_no', 'facebook', 'employed_unemployed', 'occupation',
    'solo_parent', 'ofw', 'school_youth', 'pwd', 'indigenous', 'citizenship', 'toilet', 'valid_id', 'type_id', 'household_id'
];

$data = [];
foreach ($fields as $field) {
    $data[$field] = isset($_POST[$field]) ? $_POST[$field] : '';
}

// Get original names
$orig_last_name = isset($_POST['orig_last_name']) ? $_POST['orig_last_name'] : '';
$orig_first_name = isset($_POST['orig_first_name']) ? $_POST['orig_first_name'] : '';
$orig_middle_name = isset($_POST['orig_middle_name']) ? $_POST['orig_middle_name'] : '';

// Find the person's id using the original names
$stmt = $conn->prepare("SELECT id FROM people WHERE last_name=? AND first_name=? AND middle_name=? LIMIT 1");
$stmt->bind_param("sss", $orig_last_name, $orig_first_name, $orig_middle_name);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row) {
    $id = $row['id'];
    // Prepare update statement (24 fields + id = 25)
    $sql = "UPDATE people SET 
        last_name=?, first_name=?, middle_name=?, ext_name=?, sex_name=?, date_of_birth=?, age=?, civil_status=?, place_of_birth=?, 
        street_name=?, purok_name=?, cellphone_no=?, facebook=?, employed_unemployed=?, occupation=?, solo_parent=?, ofw=?, 
        school_youth=?, pwd=?, indigenous=?, citizenship=?, toilet=?, valid_id=?, type_id=?, household_id = ? 
        WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssssssssssssssssi",
        $data['last_name'], $data['first_name'], $data['middle_name'], $data['ext_name'], $data['sex_name'], $data['date_of_birth'], $data['age'], $data['civil_status'],
        $data['place_of_birth'], $data['street_name'], $data['purok_name'], $data['cellphone_no'], $data['facebook'], $data['employed_unemployed'], $data['occupation'],
        $data['solo_parent'], $data['ofw'], $data['school_youth'], $data['pwd'], $data['indigenous'], $data['citizenship'], $data['toilet'], $data['valid_id'], $data['type_id'],$data['household_id'],
        $id
    );
    if ($stmt->execute()) {
        // Redirect to edit2.php with updated names after successful update
        echo "<script>
            window.onload = function() {
                var alertBox = document.createElement('div');
                alertBox.textContent = 'Record successfully updated!';
                alertBox.style.position = 'fixed';
                alertBox.style.top = '20px';
                alertBox.style.left = '50%';
                alertBox.style.transform = 'translateX(-50%)';
                alertBox.style.background = '#4caf50';
                alertBox.style.color = '#fff';
                alertBox.style.padding = '12px 24px';
                alertBox.style.borderRadius = '4px';
                alertBox.style.zIndex = '9999';
                document.body.appendChild(alertBox);
                setTimeout(function() {
                    alertBox.remove();
                }, 1000);
            };
        </script>";
        
        echo "<script>
            var urlParams = new URLSearchParams(window.location.search);
            var listState = urlParams.toString();
            window.opener.location.href = 'list.php?' + listState;
            window.opener.location.reload();
        </script>";
        
        echo "<script>
            setTimeout(function() {
                window.close();
            }, 1000);
        </script>";
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Person not found.";
}

$conn->close();
?>