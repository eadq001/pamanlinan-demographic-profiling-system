<?php
// Connect to the database
$pdo = new PDO('mysql:host=localhost;dbname=profiling_system_db', 'root', '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $last = $_POST['last_name'];
    $first = $_POST['first_name'];
    $middle = $_POST['middle_name'];
    $ext = $_POST['ext_name'];
    $sex = $_POST['sex_name'];
    $street = $_POST['street_name'];
    $purok = $_POST['purok_name'];
    $place = $_POST['place_of_birth'];
    $date = $_POST['date_of_birth'];
    $age = $_POST['age'];
    $civil = $_POST['civil_status'];
    $citizenship = $_POST['citizenship'];
    $employed = $_POST['employed_unemployed'];
    $solo = $_POST['solo_parent'];
    $ofw = $_POST['ofw'];
    $occupation = $_POST['occupation'];
    $toilet = $_POST['toilet'];
    $school = $_POST['school_youth'];
    $pwd = $_POST['pwd'];
    $cellphone = $_POST['cellphone_no'];
    $facebook = $_POST['facebook'];
    $valid = $_POST['valid_id'];
    $type = $_POST['type_id'];

    $stmt = $pdo->prepare("INSERT INTO people (last_name, first_name, middle_name, ext_name, sex_name, street_name, purok_name, place_of_birth, date_of_birth, age, civil_status, citizenship, employed_unemployed, solo_parent, ofw, occupation, toilet, school_youth, pwd, cellphone_no, facebook, valid_id, type_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$last, $first, $middle, $ext, $sex, $street, $purok,  $place, $date, $age, $civil, $citizenship, $employed, $solo, $ofw, $occupation, $toilet, $school, $pwd, $cellphone, $facebook, $valid, $type]);

    header('Location: list.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Person</title>
  <link rel="stylesheet" href="add.css">
  <link rel="stylesheet" href="font.css">
  <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
  <style>
    * {
      box-sizing: border-box;
    }

    
    h2 {
      text-align: center;
      margin-top: 20px;
    }

    form {
      max-width: 1000px;
      margin: 20px auto;
      padding: 20px;
    }

    #residentForm {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 15px;
    }

    input, select {
      padding: 10px;
      font-size: 16px;
      width: 100%;
    }

    .submit_button {
      grid-column: span 3;
      padding: 12px 20px;
      font-size: 16px;
      background-color: #4285F4;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: fit-content;
    }

    .submit_button:hover {
      background-color: #357ae8;
    }

    @media (max-width: 768px) {
      .submit_button {
        grid-column: span 1;
        justify-self: center;
      }
    }
  </style>
</head>
<body>
<header>
  <nav class="navbar">
    <div class="logo">Brgy. Pamanlinan Demographic Profiling System</div>
    <div class="hamburger" id="hamburger">&#9776;</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="index.php">LOGOUT</a></li>
    </ul>
  </nav>
</header>

  <h2>Resident Registration Form</h2>

  <form method="post" id="residentForm">
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="middle_name" placeholder="Middle Name" required>
    <input type="text" name="ext_name" placeholder="Extension Name (e.g. Jr., Sr.)" >
    <select name="sex_name" required>
      <option value="">Gender</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
    </select>
    <input type="text" name="street_name" placeholder="Street Name" required>
    <input type="text" name="purok_name" placeholder="Purok Name" required>
    <input type="text" name="place_of_birth" placeholder="Place of Birth" required>
    <input type="text" name="date_of_birth" placeholder="MM/DD/YYYY" required>
    <input type="text" name="age" placeholder="Age" required>
    <input type="text" name="civil_status" placeholder="Civil Status" required>
    <input type="text" name="citizenship" placeholder="Citizenship" required>
    <select name="employed_unemployed" required>
      <option value="">Employed/Unemployed</option>
      <option value="Employed">Employed</option>
      <option value="Unemployed">Unemployed</option>
    </select>
    <select name="solo_parent" required>
      <option value="">Solo Parent? Y/N</option>
      <option value="Yes">Yes</option>
      <option value="No">No</option>
    </select>
    <select name="ofw" required>
      <option value="">OFW? Y/N</option>
      <option value="Yes">Yes</option>
      <option value="No">No</option>
    </select>
    <input type="text" name="occupation" placeholder="Occupation" required>
    <input type="text" name="toilet" placeholder="Toilet (Yes/No)" required>
    <input type="text" name="school_youth" placeholder="Out-of-School Youth (15–24)? Y/N" required>
    <input type="text" name="pwd" placeholder="PWD? Y/N (If Yes, specify)" required>
    <input type="text" name="cellphone_no" placeholder="Cellphone No." required>
    <input type="text" name="facebook" placeholder="facebook." required>
    <input type="text" name="valid_id" placeholder="valid id no." required>
    <input type="text" name="type_id" placeholder="type of id" required>

    <button type="submit" class="submit_button">Submit</button>
  </form>

</body>
<script>
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');

  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });
</script>
</html>
