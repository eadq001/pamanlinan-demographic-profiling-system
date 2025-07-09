
<?php
//page can't be accessed when not logged in
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "pamanlinan_db");

// Extract values from URL query parameters
$last_name = isset($_GET['last_name']) ? $_GET['last_name'] : '';
$first_name = isset($_GET['first_name']) ? $_GET['first_name'] : '';
$middle_name = isset($_GET['middle_name']) ? $_GET['middle_name'] : '';

$person = null;
if ($last_name && $first_name && $middle_name) {
    $stmt = $conn->prepare("SELECT * FROM people WHERE last_name=? AND first_name=? AND middle_name=? LIMIT 1");
    $stmt->bind_param("sss", $last_name, $first_name, $middle_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $person = $result->fetch_assoc();
    $stmt->close();
}

// Display the extracted values
echo "Last Name: " . htmlspecialchars($last_name) . "<br>";
echo "First Name: " . htmlspecialchars($first_name) . "<br>";
echo "Middle Name: " . htmlspecialchars($middle_name) . "<br>";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="add.css" />
    <link rel="stylesheet" href="add2.css" />
    <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
</head>
<body>
    
<header>
    <nav class="navbar">
      <div class="logo">Demographic Profiling System form</div>
      <ul class="nav-links">
        <li><a href="list.php">LIST</a></li>
        <li><a href="logout.php">LOGOUT</a></li>
      </ul>
    </nav>
  </header>

<form method="post" action="update.php" autocomplete="off">
    <h1>EDIT DETAILS</h1>

    <input type="hidden" name="orig_last_name" value="<?php echo htmlspecialchars($person['last_name'] ?? ''); ?>">
    <input type="hidden" name="orig_first_name" value="<?php echo htmlspecialchars($person['first_name'] ?? ''); ?>">
    <input type="hidden" name="orig_middle_name" value="<?php echo htmlspecialchars($person['middle_name'] ?? ''); ?>">

    <h2>Personal Information</h2>
    <div class="grid">
      <div>
        <label>Last Name</label>
        <input type="text" name="last_name" required value="<?php echo htmlspecialchars($person['last_name'] ?? ''); ?>" />
      </div>
      <div>
        <label>First Name</label>
        <input type="text" name="first_name" required value="<?php echo htmlspecialchars($person['first_name'] ?? ''); ?>" />
      </div>
      <div>
        <label>Middle Name</label>
        <input type="text" name="middle_name" value="<?php echo htmlspecialchars($person['middle_name'] ?? ''); ?>" />
      </div>
      <div>
        <label>Extension Name</label>
        <input
          type="text"
          name="ext_name"
          list="ext-options"
          placeholder="Select or type extension"
          value="<?php echo htmlspecialchars($person['ext_name'] ?? ''); ?>"
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
          <option value="Male" <?php if(strtolower(trim($person['sex_name'] ?? ''))=='male') echo 'selected'; ?>>Male</option>
          <option value="Female" <?php if(strtolower(trim($person['sex_name'] ?? ''))=='female') echo 'selected'; ?>>Female</option>
        </select>
      </div>
      <div>
        <label>Birthdate</label>
        <input type="text" name="date_of_birth" required placeholder="MM/DD/YYYY" value="<?php echo htmlspecialchars($person['date_of_birth'] ?? ''); ?>"><br>
      </div>
      <div>
        <label>Age</label>
        <input type="text" name="age" value="<?php echo htmlspecialchars($person['age'] ?? ''); ?>" readonly />
      </div>
      <div>
        <label>Civil Status</label>
        <select name="civil_status" required>
          <option value="">-- Select --</option>
          <?php
          $statuses = ['N/A','Single','Married','Widowed','Separated','Divorced','Live-in','Other'];
          foreach($statuses as $status) {
            $sel = (strtolower(trim($person['civil_status'] ?? '')) == strtolower($status)) ? 'selected' : '';
            echo "<option value=\"$status\" $sel>$status</option>";
          }
          ?>
        </select>
      </div>
      <div>
        <label>Place of Birth</label>
        <input type="text" name="place_of_birth" required value="<?php echo htmlspecialchars($person['place_of_birth'] ?? ''); ?>" />
      </div>
    </div>

    <h2>Address & Contact</h2>
    <div class="grid">
      <div>
        <label>Street Name</label>
        <input type="text" name="street_name"  value="<?php echo htmlspecialchars($person['street_name'] ?? ''); ?>" />
      </div>
      <div>
        <!-- purok -->
        <label>Purok Name</label>
        <select name="purok_name" required>
          <option value="">-- Select --</option>
          <?php
          $opts = ['Purok 1','Purok 2A','Purok 2B','Purok 3','Purok 4','Purok 5','Purok 6'];
          foreach($opts as $opt) {
            $sel = (strtolower(trim($person['purok_name'] ?? '')) == strtolower($opt)) ? 'selected' : '';
            echo "<option value=\"$opt\" $sel>$opt</option>";
          }
          ?>
        </select>
      </div>
      <div>
        <label>Contact No.</label>
        <input type="tel" name="cellphone_no"  value="<?php echo htmlspecialchars($person['cellphone_no'] ?? ''); ?>" />
      </div>
      <div>
        <label>Facebook Account</label>
        <input type="text" name="facebook" value="<?php echo htmlspecialchars($person['facebook'] ?? ''); ?>" />
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
          value="<?php echo htmlspecialchars($person['employed_unemployed'] ?? ''); ?>"
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
        <input type="text" name="occupation" required value="<?php echo htmlspecialchars($person['occupation'] ?? ''); ?>" />
      </div>
      <div>
        <label>Solo Parent</label>
        <select name="solo_parent" >
          <option value="">-- Select --</option>
          <?php
          $opts = ['N/A','Yes','No','Other'];
          foreach($opts as $opt) {
            $sel = (strtolower(trim($person['solo_parent'] ?? '')) == strtolower($opt)) ? 'selected' : '';
            echo "<option value=\"$opt\" $sel>$opt</option>";
          }
          ?>
        </select>
      </div>
      <div>
        <label>OFW</label>
        <input
          type="text"
          name="ofw"
          list="ofw-options"
          placeholder="Select or type if yes (Please Specify)"
          
          value="<?php echo htmlspecialchars($person['ofw'] ?? ''); ?>"
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
        <select name="school_youth" >
          <option value="">-- Select --</option>
          <?php
          foreach($opts as $opt) {
            $sel = (strtolower(trim($person['school_youth'] ?? '')) == strtolower($opt)) ? 'selected' : '';
            echo "<option value=\"$opt\" $sel>$opt</option>";
          }
          ?>
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
          value="<?php echo htmlspecialchars($person['pwd'] ?? ''); ?>"
        />
      </div>
      <datalist id="pwd-options">
       <option>N/A</option>
        <option value="NO"></option>
        <option value="DEAF"></option>
        <option value="MUTE"></option>
        <option value="INTELLECTUAL DISABILITY"></option>
        <option value="AUTISM"></option>
        <option value="PHYSICAL DISABILITY"></option>
        <option value="DISABILITY WALKING OR MOVEMENT"></option>
        <option value="HEALTH-RELATED DISABILITY (ILLNESS)"></option>
      </datalist>
      <div>
        <label>Indigenous People</label>
        <input
          type="text"
          name="indigenous"
          list="indigenous-options"
          placeholder="Select or type if applicable"
          required
          value="<?php echo htmlspecialchars($person['indigenous'] ?? ''); ?>"
        />
      </div>
      <datalist id="indigenous-options">
     <option>N/A</option>
         <option value="MIGRANT"></option>
        <option value="MANDAYA"></option>
        <option value="NO"></option>
      </datalist>
      <div>
        <label>Citizenship</label>
        <input type="text" name="citizenship" required value="<?php echo htmlspecialchars($person['citizenship'] ?? ''); ?>" />
      </div>
      <div>
        <label>Toilet</label>
        <select name="toilet" required>
          <option value="">-- Select --</option>
          <option value="N/A" <?php if(strtolower(trim($person['toilet'] ?? ''))=='n/a') echo 'selected'; ?>>N/A</option>
          <option value="Yes" <?php if(strtolower(trim($person['toilet'] ?? ''))=='yes') echo 'selected'; ?>>Yes</option>
          <option value="No" <?php if(strtolower(trim($person['toilet'] ?? ''))=='no') echo 'selected'; ?>>No</option>
        </select>
      </div>
      <div>
        <label>Women's Association</label>
        <select name="womens_association">
          <option value="">-- Select --</option>
          <option value="N/A" <?php if(strtolower(trim($person['womens_association'] ?? ''))=='n/a') echo 'selected'; ?>>N/A</option>
          <option value="Yes" <?php if(strtolower(trim($person['womens_association'] ?? ''))=='yes') echo 'selected'; ?>>Yes</option>
          <option value="No" <?php if(strtolower(trim($person['womens_association'] ?? ''))=='no') echo 'selected'; ?>>No</option>
        </select>
      </div>
    </div>

    <h2>Identification</h2>
    <div class="grid">
      <div>
        <label>Valid ID</label>
        <input type="text" name="valid_id" required value="<?php echo htmlspecialchars($person['valid_id'] ?? ''); ?>" />
      </div>
      <div>
        <label>Type of ID</label>
        <input type="text" name="type_id" required value="<?php echo htmlspecialchars($person['type_id'] ?? ''); ?>" />
      </div>
      <div>
        <label>Household ID</label>
          <input type="text" name="household_id" required value="<?php echo htmlspecialchars($person['household_id'] ?? ''); ?>" />
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