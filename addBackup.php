<?php
// Connect to the database
$pdo = new PDO('mysql:host=localhost;dbname=profiling_system_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'last'        => $_POST['last_name'],
        'first'       => $_POST['first_name'],
        'middle'      => $_POST['middle_name'],
        'ext'         => $_POST['ext_name'],
        'sex'         => $_POST['sex_name'],
        'street'      => $_POST['street_name'],
        'purok'       => $_POST['purok_name'],
        'place'       => $_POST['place_of_birth'],
        'date'        => $_POST['date_of_birth'],
        'age'         => $_POST['age'],
        'civil'       => $_POST['civil_status'],
        'citizenship' => $_POST['citizenship'],
        'employed'    => $_POST['employed_unemployed'],
        'solo'        => $_POST['solo_parent'],
        'ofw'         => $_POST['ofw'],
        'occupation'  => $_POST['occupation'],
        'toilet'      => $_POST['toilet'],
        'school'      => $_POST['school_youth'],
        'pwd'         => $_POST['pwd'],
        'indigenous'  => $_POST['indigenous'],
        'cellphone'   => $_POST['cellphone_no'],
        'facebook'    => $_POST['facebook'],
        'valid'       => $_POST['valid_id'],
        'type'        => $_POST['type_id'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO people (
            last_name, first_name, middle_name, ext_name, sex_name,
            street_name, purok_name, place_of_birth, date_of_birth, age,
            civil_status, citizenship, employed_unemployed, solo_parent, ofw,
            occupation, toilet, school_youth, pwd, indigenous, cellphone_no,
            facebook, valid_id, type_id
        ) VALUES (
            :last, :first, :middle, :ext, :sex,
            :street, :purok, :place, :date, :age,
            :civil, :citizenship, :employed, :solo, :ofw,
            :occupation, :toilet, :school, :pwd, :indigenous, :cellphone,
            :facebook, :valid, :type
        )
    ");
    $stmt->execute($data);

    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Brgy. Pamanlinan | Add Person</title>
  <link rel="stylesheet" href="font.css">
  <link rel="stylesheet" href="add.css">
  <style>
   body {
    background: linear-gradient(to right, #6ca0a3, #ffffff);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0;
  }

     header {
    position: fixed; /* keep it fixed on scroll */
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background-color:#055c61;
    padding: 0rem;
    flex-wrap: wrap;
    justify-content: space-between;
  }
    .navbar {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .logo {
      font-size: 1.3rem;
      font-weight: bold;
    }
    .nav-links {
      list-style: none;
      display: flex;
      gap: 1rem;
    }
    .nav-links a {
      color: white;
      text-decoration: none;
      font-weight: 500;
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
    h2 {
      color: #055c61;
      margin: 2rem 0 1rem;
      font-size: 1.5rem;
      border-bottom: 2px solid #00408033;
      padding-bottom: 0.5rem;
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
    input, select {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid lightslategray;
      border-radius: 6px;
      font-size: 0.95rem;
    }
    input:focus, select:focus {
      border-color:rgba(3, 19, 34, 0.35);
      outline: none;
    }
    button[type="submit"] {
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
    button[type="submit"]:hover {
      background-color: #003060;
    }
  </style>
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="logo">Brgy. Pamanlinan Demographic Profiling System</div>
      <ul class="nav-links">
        <li><a href="index.php">LOGOUT</a></li>
      </ul>
    </nav>
  </header>
  <form method="post">
    <h2>Personal Information</h2>
    <div class="grid">
      <div><label>First Name</label><input type="text" name="first_name" required></div>
      <div><label>Last Name</label><input type="text" name="last_name" required></div>
      <div><label>Middle Name</label><input type="text" name="middle_name"></div>
      <div><label>Extension Name</label><input type="text" name="ext_name" list="ext-options" placeholder="Select or type extension"></div>
      <datalist id="ext-options">
        <option value="N/A">
        <option value="Jr.">
        <option value="Sr.">
        <option value="II">
        <option value="III">
        <option value="IV">
        <option value="Other">
      </datalist>
      <div><label>Gender</label>
        <select name="sex_name" required>
          <option value="">-- Select --</option>
          <option>N/A</option>
          <option>Male</option>
          <option>Female</option>
          <option>Other</option>
        </select>
      </div>
      <div><label>Birthdate</label><input type="date" name="date_of_birth" required></div>
      <div><label>Age</label><input type="number" name="age" required></div>
      <div><label>Civil Status</label>
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
      <div><label>Place of Birth</label><input type="text" name="place_of_birth" required></div>
    </div>

    <h2>Address & Contact</h2>
    <div class="grid">
      <div><label>Street Name</label><input type="text" name="street_name" required></div>
      <div><label>Purok Name</label><input type="text" name="purok_name" required></div>
      <div><label>Cellphone No.</label><input type="tel" name="cellphone_no" required></div>
      <div><label>Facebook</label><input type="text" name="facebook"></div>
    </div>

    <h2>Employment & Other Info</h2>
    <div class="grid">
      <div><label>Employed/Unemployed</label><input type="text" name="employed_unemployed" list="employed_unemployed-options" placeholder="Select or type occupation" required></div>
      <datalist id="employed_unemployed-options">
        <option>N/A</option>
        <option value="Self-employed">
        <option value="Unemployed">
        <option value="Other">
      </datalist>
      <div><label>Occupation</label><input type="occupation" name="occupation" required></div>
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
      <div><label>OFW</label><input type="text" name="ofw" list="ofw-options" placeholder="Select or type if yes (Please Specify)" required></div>
      <datalist id="ofw-options">
        <option>N/A</option>
        <option value="Yes">
        <option value="No">
        <option value="Other">
      </datalist>
      <div><label>Out-of-school Youth</label>
        <select name="school_youth" required>
          <option value="">-- Select --</option>
          <option>N/A</option>
          <option>Yes</option>
          <option>No</option>
          <option>Other</option>
        </select>
      </div>
      <div><label>PWD</label><input type="text" name="pwd" list="pwd-options" placeholder="Select or type if applicable" required></div>
      <datalist id="pwd-options">
        <option>N/A</option>
        <option value="Yes">
        <option value="No">
        <option value="Other">
      </datalist>
      <div><label>Indigenous People</label><input type="text" name="indigenous" list="indigenous-options" placeholder="Select or type if applicable" required></div>
      <datalist id="indigenous-options">
        <option>N/A</option>
        <option value="Yes">
        <option value="No">
        <option value="Other">
      </datalist>
      <div><label>Citizenship</label><input type="text" name="citizenship" required></div>
      <div><label>Toilet</label>
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
      <div><label>Valid ID No.</label><input type="text" name="valid_id"></div>
      <div><label>ID Type</label><input type="text" name="type_id"></div>
    </div>

    <button type="submit">Submit</button>

  </form>
  <script>
  document.querySelectorAll('input[type="text"], input[type="tel"]').forEach(input => {
    input.addEventListener('input', () => {
      input.value = input.value.toUpperCase();
    });
  });
</script>
</body>
</html>
