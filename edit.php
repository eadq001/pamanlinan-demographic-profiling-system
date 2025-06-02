<?php
// filepath: c:\xampp\htdocs\pamanlinan\edit.php
// Connection to database
$conn = new mysqli("localhost", "root", "", "pamanlinan_db");

// Get the ID from the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;


// Fetch the record based on the ID
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM people WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "<script>alert('No record found for the given ID.'); window.location.href='list.php';</script>";
        exit;
    }

    $stmt->close();
} else {
    echo "<script>alert('Invalid ID.'); window.location.href='list.php';</script>";
    exit;
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Details</title>
  <link rel="stylesheet" href="font.css" />
  <link rel="stylesheet" href="add.css" />
  <link rel="stylesheet" href="add2.css" />
</head>

<header>
    <nav class="navbar">
      <div class="logo">Demographic Profiling System form</div>
      <ul class="nav-links">
        <li><a href="list.php">LIST</a></li>
        <li><a href="index.php">LOGOUT</a></li>
      </ul>
    </nav>
  </header>


<body>
  <form method="post" action="update.php" autocomplete="off">
    <h1>Edit Details</h1>

    <input type="hidden" name="id" value="<?= $row['id'] ?>" />

    <h2>Personal Information</h2>
    <div class="grid">
      <div>
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" required />
      </div>
      <div>
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>" required />
      </div>
      <div>
        <label>Middle Name</label>
        <input type="text" name="middle_name" value="<?= htmlspecialchars($row['middle_name']) ?>" />
      </div>
      <div>
        <label>Extension Name</label>
        <input type="text" name="ext_name" value="<?= htmlspecialchars($row['ext_name']) ?>" />
      </div>
      <div>
        <label>Sex</label>
        <select name="sex_name" required>
          <option value="Male" <?= $row['sex_name'] == 'Male' ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= $row['sex_name'] == 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
      </div>
      <div>
        <label>Birthdate</label>
        <input type="text" name="date_of_birth" value="<?= htmlspecialchars($row['date_of_birth']) ?>" required />
      </div>
      <div>
        <label>Civil Status</label>
        <select name="civil_status" required>
          <option value="Single" <?= $row['civil_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
          <option value="Married" <?= $row['civil_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
          <option value="Widowed" <?= $row['civil_status'] == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
          <option value="Separated" <?= $row['civil_status'] == 'Separated' ? 'selected' : '' ?>>Separated</option>
        </select>
      </div>
      <div>
        <label>Place of Birth</label>
        <input type="text" name="place_of_birth" value="<?= htmlspecialchars($row['place_of_birth']) ?>" required />
      </div>
    </div>

    <h2>Address & Contact</h2>
    <div class="grid">
      <div>
        <label>Street Name</label>
        <input type="text" name="street_name" value="<?= htmlspecialchars($row['street_name']) ?>" required />
      </div>
      <div>
        <label>Purok Name</label>
        <input type="text" name="purok_name" value="<?= htmlspecialchars($row['purok_name']) ?>" required />
      </div>
      <div>
        <label>Contact No.</label>
        <input type="tel" name="cellphone_no" value="<?= htmlspecialchars($row['cellphone_no']) ?>" required />
      </div>
      <div>
        <label>Facebook Account</label>
        <input type="text" name="facebook" value="<?= htmlspecialchars($row['facebook']) ?>" />
      </div>
    </div>

    <h2>Employment & Other Info</h2>
    <div class="grid">
      <div>
        <label>Employed/Unemployed</label>
        <input type="text" name="employed_unemployed" value="<?= htmlspecialchars($row['employed_unemployed']) ?>" required />
      </div>
      <div>
        <label>Occupation</label>
        <input type="text" name="occupation" value="<?= htmlspecialchars($row['occupation']) ?>" required />
      </div>
      <div>
        <label>Solo Parent</label>
        <select name="solo_parent" required>
          <option value="Yes" <?= $row['solo_parent'] == 'Yes' ? 'selected' : '' ?>>Yes</option>
          <option value="No" <?= $row['solo_parent'] == 'No' ? 'selected' : '' ?>>No</option>
        </select>
      </div>
      <div>
        <label>OFW</label>
        <input type="text" name="ofw" value="<?= htmlspecialchars($row['ofw']) ?>" required />
      </div>
      <div>
        <label>Out-of-school Youth</label>
        <select name="school_youth" required>
          <option value="Yes" <?= $row['school_youth'] == 'Yes' ? 'selected' : '' ?>>Yes</option>
          <option value="No" <?= $row['school_youth'] == 'No' ? 'selected' : '' ?>>No</option>
        </select>
      </div>
      <div>
        <label>PWD</label>
        <input type="text" name="pwd" value="<?= htmlspecialchars($row['pwd']) ?>" required />
      </div>
      <div>
        <label>Indigenous People</label>
        <input type="text" name="indigenous" value="<?= htmlspecialchars($row['indigenous']) ?>" required />
      </div>
      <div>
        <label>Citizenship</label>
        <input type="text" name="citizenship" value="<?= htmlspecialchars($row['citizenship']) ?>" required />
      </div>
      <div>
        <label>Toilet</label>
        <select name="toilet" required>
          <option value="Yes" <?= $row['toilet'] == 'Yes' ? 'selected' : '' ?>>Yes</option>
          <option value="No" <?= $row['toilet'] == 'No' ? 'selected' : '' ?>>No</option>
        </select>
      </div>
    </div>

    <h2>Identification</h2>
    <div class="grid">
      <div>
        <label>Valid ID</label>
        <input type="text" name="valid_id" value="<?= htmlspecialchars($row['valid_id']) ?>" required />
      </div>
      <div>
        <label>Type of ID</label>
        <input type="text" name="type_id" value="<?= htmlspecialchars($row['type_id']) ?>" required />
      </div>
    </div>

    <button type="submit">Update</button>
  </form>

    <!-- Add this script before the closing </body> tag -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle all input fields except facebook
  const inputs = document.querySelectorAll('input:not([name="facebook"])');
  inputs.forEach(function(input) {
    // Convert existing value to uppercase on page load
    if (input.type === 'text' || input.type === 'tel') {
      input.value = input.value.toUpperCase();
    }
    // Convert to uppercase as user types
    input.addEventListener('input', function() {
      if (input.type === 'text' || input.type === 'tel') {
        input.value = input.value.toUpperCase();
      }
    });
  });

  // Handle all select fields (convert option text to uppercase, keep value unchanged)
  const selects = document.querySelectorAll('select');
  selects.forEach(function(select) {
    Array.from(select.options).forEach(function(option) {
      option.text = option.text.toUpperCase();
      // Do not change option.value to avoid clearing the value
    });
  });
});
</script>
</body>
</html>