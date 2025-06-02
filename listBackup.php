<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=login_sample_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Import CSV data
if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
    $filename = $_FILES['csv_file']['tmp_name'];
    if ($_FILES['csv_file']['size'] > 0) {
        $file = fopen($filename, 'r');
        while (($row = fgetcsv($file)) !== false) {
            // Skip if row has incorrect column count
            if (count($row) !== 24) continue;

            $stmt = $pdo->prepare("
                INSERT INTO people (
                    last_name, first_name, middle_name, ext_name, sex_name,
                    street_name, purok_name, place_of_birth, date_of_birth, age,
                    civil_status, citizenship, employed_unemployed, solo_parent, ofw,
                    occupation, toilet, school_youth, pwd, indigenous,
                    cellphone_no, facebook, valid_id, type_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute($row);
        }
        fclose($file);
        echo "<script>alert('Data imported successfully.');</script>";
    }
}

// Fetch all people for display
$stmt = $pdo->query("SELECT * FROM people");
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database setup
$host = 'localhost';
$db = 'login_sample_db';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Define the expected columns in order
$columns = [
    'last_name', 'first_name', 'middle_name', 'ext_name', 'sex_name',
    'street_name', 'purok_name', 'place_of_birth', 'date_of_birth', 'age',
    'civil_status', 'citizenship', 'employed_unemployed', 'solo_parent', 'ofw',
    'occupation', 'toilet', 'school_youth', 'pwd', 'indigenous',
    'cellphone_no', 'facebook', 'valid_id', 'type_id'
];

// File upload handling
if (isset($_FILES['excel_file']['tmp_name'])) {
    $filePath = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header row

            // Ensure row has the expected number of columns
            if (count($row) < count($columns)) continue;

            // Escape and assign each column value
            $values = [];
            foreach ($columns as $i => $col) {
                $values[] = "'" . $conn->real_escape_string($row[$i]) . "'";
            }

            $sql = "INSERT INTO people (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
            $conn->query($sql);
        }

        //echo "<h3>Import successful!</h3>";
    } catch (Exception $e) {
        echo "Error loading file: " . $e->getMessage();
        header("Refresh:0");

    }
} else {
    //echo "No file uploaded.";
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>People List</title>
  <link rel="stylesheet" href="list.css"/>
  <link rel="stylesheet" href="import.css">
  <style>
    body {
      background: linear-gradient(to right, #6ca0a3, #ffffff);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
    }
    .table-container {
      overflow-x: auto;
      padding: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 8px 12px;
      border: 1px solid #ccc;
    }
    th {
      background-color: #f0f0f0;
    }
  </style>
</head>
<body>

<header>
  <nav>
    <div class="logo">RECORD OF BARANGAY INHABITANTS BY HOUSEHOLD</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="add.php">ADD</a></li>
      <li><a href="index.php">LOGOUT</a></li>
    </ul>
    <div class="burger" id="burger">&#9776;</div>
  </nav>
</header>

<div style="margin: 20px;display: flex;align-items:center;" class="search-upload-box">



  <!-- Search and Filter -->
  <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search..." style="padding: 10px; width: 300px;">
  <select id="columnSelect" onchange="filterTable()" style="padding: 10px;">
    <option value="all">All Columns</option>
    <?php
    $columns = [
        "Last Name", "First Name", "Middle Name", "Ext", "Sex", "Street", "Purok Name", "Place of Birth", "Birth Date",
        "Age", "Civil Status", "Citizenship", "Employed/Unemployed", "Solo Parent", "OFW",
        "Occupation", "Toilet", "School Youth", "PWD", "Indigenous", "Cellphone", "Facebook", "Valid ID", "Type ID"
    ];
    foreach ($columns as $index => $col) {
        echo "<option value=\"$index\">$col</option>";
    }
    ?>
  </select>
  <button onclick="exportToCSV()" style="padding: 10px;">Export CSV</button>

    <!-- CSV Import Form -->
    

<div class="upload-files-box">
  <form action="list.php" method="post" enctype="multipart/form-data" class="upload_files">
    <p>Import Excel File to Database</p>
    <input type="file" name="excel_file" accept=".xlsx, .xls" required>
    <input type="submit" value="Upload & Import">
  </form>
</div>
</div>

<!-- Table -->
<div class="table-container">
  <table>
    <thead>
      <tr>
        <?php foreach ($columns as $col): ?>
          <th><?= htmlspecialchars($col) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody id="dataTable">
      <?php foreach ($people as $person): ?>
        <tr>
          <td><?= htmlspecialchars($person['last_name']) ?></td>
          <td><?= htmlspecialchars($person['first_name']) ?></td>
          <td><?= htmlspecialchars($person['middle_name']) ?></td>
          <td><?= htmlspecialchars($person['ext_name']) ?></td>
          <td><?= htmlspecialchars($person['sex_name']) ?></td>
          <td><?= htmlspecialchars($person['street_name']) ?></td>
          <td><?= htmlspecialchars($person['purok_name']) ?></td>
          <td><?= htmlspecialchars($person['place_of_birth']) ?></td>
          <td><?= htmlspecialchars($person['date_of_birth']) ?></td>
          <td><?= htmlspecialchars($person['age']) ?></td>
          <td><?= htmlspecialchars($person['civil_status']) ?></td>
          <td><?= htmlspecialchars($person['citizenship']) ?></td>
          <td><?= htmlspecialchars($person['employed_unemployed']) ?></td>
          <td><?= htmlspecialchars($person['solo_parent']) ?></td>
          <td><?= htmlspecialchars($person['ofw']) ?></td>
          <td><?= htmlspecialchars($person['occupation']) ?></td>
          <td><?= htmlspecialchars($person['toilet']) ?></td>
          <td><?= htmlspecialchars($person['school_youth']) ?></td>
          <td><?= htmlspecialchars($person['pwd']) ?></td>
          <td><?= htmlspecialchars($person['indigenous']) ?></td>
          <td><?= htmlspecialchars($person['cellphone_no']) ?></td>
          <td><?= htmlspecialchars($person['facebook']) ?></td>
          <td><?= htmlspecialchars($person['valid_id']) ?></td>
          <td><?= htmlspecialchars($person['type_id']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Scripts -->
<script>
  function filterTable() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const column = document.getElementById("columnSelect").value;
    const rows = document.querySelectorAll("#dataTable tr");

    rows.forEach(row => {
      const cells = row.querySelectorAll("td");
      let match = false;

      if (column === "all") {
        cells.forEach(cell => {
          if (cell.textContent.toLowerCase().includes(input)) match = true;
        });
      } else {
        const cell = cells[parseInt(column)];
        if (cell && cell.textContent.toLowerCase().includes(input)) match = true;
      }

      row.style.display = match ? "" : "none";
    });
  }

  function exportToCSV() {
    const table = document.querySelector("table");
    const rows = Array.from(table.querySelectorAll("tr"));
    let csv = [];

    rows.forEach(row => {
      if (row.style.display === "none") return;

      const cells = Array.from(row.querySelectorAll("th, td"));
      const rowData = cells.map(cell => `"${cell.innerText.replace(/"/g, '""')}"`);
      csv.push(rowData.join(","));
    });

    const blob = new Blob([csv.join("\n")], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "people_list.csv";
    link.click();
    URL.revokeObjectURL(url);
  }
</script>

</body>
</html>
