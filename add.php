<?php
//page can't be accessed when not logged in
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connection to database
$conn = new mysqli("localhost", "root", "", "pamanlinan_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentYear = date('Y'); // Current year for display

// Function to calculate age from date of birth
function calculate_age($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

// Handle POST submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $ext_name = trim($_POST['ext_name']);
    $sex_name = $_POST['sex_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $age = calculate_age($date_of_birth); // Calculate age dynamically
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
    $household_id = trim($_POST['household_id']);

    // Check for duplicates
    $stmt = $conn->prepare("SELECT id FROM people WHERE first_name = ? AND last_name = ? AND middle_name = ?");
    $stmt->bind_param("sss", $first_name, $last_name, $middle_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Duplicate entry: This name already exists.');</script>";
    } else {
        // Proceed to insert
        $insert = $conn->prepare("INSERT INTO people (
            first_name, last_name, middle_name, ext_name, sex_name, date_of_birth, age, civil_status,
            place_of_birth, street_name, purok_name, cellphone_no, facebook, employed_unemployed, occupation,
            solo_parent, ofw, school_youth, pwd, indigenous, citizenship, toilet, valid_id, type_id, household_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insert->bind_param("ssssssissssssssssssssssss",
            $first_name, $last_name, $middle_name, $ext_name, $sex_name, $date_of_birth, $age, $civil_status,
            $place_of_birth, $street_name, $purok_name, $cellphone_no, $facebook, $employed_unemployed, $occupation,
            $solo_parent, $ofw, $school_youth, $pwd, $indigenous, $citizenship, $toilet, $valid_id, $type_id, $household_id
        );

        if ($insert->execute()) {
            echo "<script>alert('Data saved successfully.'); window.location.href='list.php';</script>";
        } else {
            echo "<script>alert('Error saving data: " . $insert->error . "');</script>";
        }

        $insert->close();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Brgy. Pamanlinan | Add Person</title>
  <link rel="stylesheet" href="font.css" />
  <link rel="stylesheet" href="add.css" />
  <style>
    body {
      background: linear-gradient(to right, #6ca0a3, #ffffff);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
    }
    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      background-color: #055c61;
      padding: 1.5rem;
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
      color: white;
    }
    .nav-links {
      list-style: none;
      display: flex;
      gap: 1rem;
    }
    .nav-links a {
      color: white;
      text-decoration: none;
      font-weight: 700;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }
    form {
      background-color: #fff;
      max-width: 960px;
      margin: 2rem auto;
      margin-top: 100px;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    h1 {
      color: rgb(1, 180, 13);
      margin-left: 32%;
      font-size: 2rem;
      padding-bottom: 0.5rem;
      text-shadow: 2px 1px 2px rgba(0, 0, 0, 0.89);
    }
    h2 {
      color: #055c61;
      margin: 2rem 0 1rem;
      font-size: 1.5rem;
      border-bottom: 2px solid #00408033;
      padding-bottom: 0.5rem;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.39);
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    label {
      font-weight: 600;
      display: block;
      margin-bottom: 0.4rem;
      color: #333;
    }
    input,
    select {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid lightslategray;
      border-radius: 6px;
      font-size: 0.95rem;
    }
    input:focus,
    select:focus {
      border-color: rgba(3, 19, 34, 0.35);
      outline: none;
    }
    button[type='submit'] {
      margin-top: 2rem;
      background-color: #055c61;
      color: white;
      padding: 0.75rem 2rem;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
    }
    button[type='submit']:hover {
      background-color: #003060;
    }
    .current-year {
      text-align: center;
      margin: 10px;
      font-weight: bold;
      color: #055c61;
    }
  </style>
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="logo">Demographic Profiling System form</div>
      <ul class="nav-links">
        <li><a href="list.php">LIST</a></li>
        <li><a href="index.php">LOGOUT</a></li>
      </ul>
    </nav>
  </header>
  
   

  <div class="current-year">Current Year: <?= $currentYear ?></div>

  <form method="post" autocomplete="off">
    <h1>REGISTRATION FORM</h1>

    <h2>Personal Information</h2>
    <div class="grid">
      <div>
        <label>Last Name</label>
        <input type="text" name="last_name" required />
      </div>
      <div>
        <label>First Name</label>
        <input type="text" name="first_name" required />
      </div>
      <div>
        <label>Middle Name</label>
        <input type="text" name="middle_name" />
      </div>
      <div>
        <label>Extension Name</label>
        <input
          type="text"
          name="ext_name"
          list="ext-options"
          placeholder="Select or type extension"
        />
      </div>
      <datalist id="ext-options">
        <option value="N/A"></option>
        <option value="Jr."></option>
        <option value="Sr."></option>
        <option value="II"></option>
        <option value="III"></option>
        <option value="IV"></option>
        <option value="Other"></option>
      </datalist>
      <div>
        <label>Sex</label>
        <select name="sex_name" required>
          <option value="">-- Select --</option>
          <option>Male</option>
          <option>Female</option>
        </select>
      </div>
      <div>
        <label>Birthdate</label>
        <input type="text" name="date_of_birth" required placeholder="MM/DD/YYYY"><br>
      </div>
      <!-- Age input removed because it will be calculated automatically -->
      <div>
        <label>Civil Status</label>
        <select name="civil_status" required>
          <option value="">-- Select --</option>
          <option>N/A</option>
          <option>Single</option>
          <option>Married</option>
          <option>Widowed</option>
          <option>Separated</option>
          <option>Divorced</option>
          <option>Live-in</option>
          <option>Other</option>
        </select>
      </div>
      <div>
        <label>Place of Birth</label>
        <input type="text" name="place_of_birth" required />
      </div>
    </div>

    <h2>Address & Contact</h2>
    <div class="grid">
      <div>
        <label>Street Name</label>
        <input type="text" name="street_name" required />
      </div>
      <div>
        <label>Purok Name</label>
        <input type="text" name="purok_name" required />
      </div>
      <div>
        <label>Contact No.</label>
        <input type="tel" name="cellphone_no" required />
      </div>
      <div>
        <label>Facebook Account</label>
        <input type="text" name="facebook" />
      </div>
    </div>

    <h2>Employment & Other Info</h2>
    <div class="grid">
      <div>
        <label>Employed/Unemployed</label>
        <input
          type="text"
          name="employed_unemployed"
          list="employed_unemployed-options"
          placeholder="Select or type occupation"
          required
        />
      </div>
      <datalist id="employed_unemployed-options">
        <option>N/A</option>
        <option value="Self-employed"></option>
        <option value="Unemployed"></option>
        <option value="Other"></option>
      </datalist>
      <div>
        <label>Occupation</label>
        <input type="text" name="occupation" required />
      </div>
      <div>
        <label>Solo Parent</label>
        <select name="solo_parent" required>
          <option value="">-- Select --</option>
          <option>N/A</option>
          <option>Yes</option>
          <option>No</option>
          <option>Other</option>
        </select>
      </div>
      <div>
        <label>OFW</label>
        <input
          type="text"
          name="ofw"
          list="ofw-options"
          placeholder="Select or type if yes (Please Specify)"
          required
        />
      </div>
      <datalist id="ofw-options">
        <option>N/A</option>
        <option value="Yes"></option>
        <option value="No"></option>
        <option value="Other"></option>
      </datalist>
      <div>
        <label>Out-of-school Youth</label>
        <select name="school_youth" required>
          <option value="">-- Select --</option>
          <option>N/A</option>
          <option>Yes</option>
          <option>No</option>
          <option>Other</option>
        </select>
      </div>
      <div>
        <label>PWD</label>
        <input
          type="text"
          name="pwd"
          list="pwd-options"
          placeholder="Select or type if yes (please specify)"
          required
        />
      </div>
      <datalist id="pwd-options">
        <option>N/A</option>
        <option value="Yes"></option>
        <option value="No"></option>
        <option value="Other"></option>
      </datalist>
      <div>
        <label>Indigenous People</label>
        <input
          type="text"
          name="indigenous"
          list="indigenous-options"
          placeholder="Select or type if applicable"
          required
        />
      </div>
      <datalist id="indigenous-options">
        <option>N/A</option>
        <option value="Yes"></option>
        <option value="No"></option>
        <option value="Other"></option>
      </datalist>
      <div>
        <label>Citizenship</label>
        <input type="text" name="citizenship" required />
      </div>
      <div>
        <label>Toilet</label>
        <select name="toilet" required>
          <option value="">-- Select --</option>
          <option>N/A</option>
          <option>Yes</option>
          <option>No</option>
        </select>
      </div>
    </div>

    <h2>Identification</h2>
    <div class="grid">
      <div>
        <label>Valid ID</label>
        <input type="text" name="valid_id" required />
      </div>
      <div>
        <label>Type of ID</label>
        <input type="text" name="type_id" required />
      </div>
      <div>
        <label>Household ID</label>
          <input type="text" name="household_id" required />
        </div>
    </div>

    </div>

    <button type="submit">Save</button>
  </form>

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
