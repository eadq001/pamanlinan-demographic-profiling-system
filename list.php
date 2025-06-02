<?php
//page can't be accessed when not logged in
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Import CSV data (if any)
if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
    $filename = $_FILES['csv_file']['tmp_name'];
    if ($_FILES['csv_file']['size'] > 0) {
        $file = fopen($filename, 'r');
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== 24) continue;
            $stmt = $pdo->prepare("
                INSERT INTO people (
                    last_name, first_name, middle_name, ext_name, sex_name,
                    street_name, purok_name, place_of_birth, date_of_birth, age,
                    civil_status, citizenship, employed_unemployed, solo_parent, ofw,
                    occupation, toilet, school_youth, pwd, indigenous,
                    cellphone_no, facebook, valid_id, type_id, household_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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

// Current Year Label
$currentYear = date('Y');
?>

<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database setup for Excel import
$host = 'localhost';
$db = 'pamanlinan_db';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$columns = [
    'last_name', 'first_name', 'middle_name', 'ext_name', 'sex_name',
    'street_name', 'purok_name', 'place_of_birth', 'date_of_birth', 'age',
    'civil_status', 'citizenship', 'employed_unemployed', 'solo_parent', 'ofw',
    'occupation', 'toilet', 'school_youth', 'pwd', 'indigenous',
    'cellphone_no', 'facebook', 'valid_id', 'type_id', 'household_id'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']['tmp_name'])) {
    $filePath = $_FILES['excel_file']['tmp_name'];
    $duplicates = 0;

    try {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header
            if (count($row) < count($columns)) continue;

            $last_name = $conn->real_escape_string(trim($row[0]));
            $first_name = $conn->real_escape_string(trim($row[1]));
            $middle_name = $conn->real_escape_string(trim($row[2]));

            $checkSql = "SELECT COUNT(*) AS count FROM people 
                         WHERE last_name='$last_name' AND first_name='$first_name' AND middle_name='$middle_name'";
            $result = $conn->query($checkSql);
            $exists = $result->fetch_assoc();

            if ($exists['count'] > 0) {
                $duplicates++;
                continue;
            }

            $values = [];
            foreach ($columns as $i => $col) {
                $values[] = "'" . $conn->real_escape_string(trim($row[$i])) . "'";
            }

            $insertSql = "INSERT INTO people (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
            $conn->query($insertSql);
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?import=success&duplicates=$duplicates");
        exit;

    } catch (Exception $e) {
        echo "<script>alert('Error loading file: " . addslashes($e->getMessage()) . "');</script>";
    }
}

$conn->close();
?>

<?php if (isset($_GET['import']) && $_GET['import'] === 'success'): ?>
    <script>
        <?php if (!empty($_GET['duplicates']) && intval($_GET['duplicates']) > 0): ?>
            alert("Import successful. <?= intval($_GET['duplicates']) ?> duplicate(s) found and skipped.");
        <?php else: ?>
            alert("Import successful. No duplicates found.");
        <?php endif; ?>
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.search = "";
            window.history.replaceState({}, document.title, url);
        }
    </script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>People List</title>
  <link rel="stylesheet" href="list.css"/>
  <link rel="stylesheet" href="import.css">
  <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
  <style>
    body {
      background: linear-gradient(to right, #6ca0a3, #ffffff);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
    }
    .table-container {
      overflow-x: auto;
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
      text-transform: uppercase;
    }
  </style>
</head>
<body>

<header>
  <nav>
    <div class="logo">BARANGAY PAMANLINAN DEMOGRAPHIC RECORDS</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="add.php">ADD</a></li>
      <li><a href="logout.php">LOGOUT</a></li>
    </ul>
    <div class="burger" id="burger">&#9776;</div>
  </nav>
</header>

<div style="margin: 20px; display: flex; align-items:center;" class="search-upload-box">

  <!-- Search and Filter -->
  <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search..." style="padding: 10px; width: 300px;">
  <select id="columnSelect" onchange="filterTable()" style="padding: 10px;">
    <option value="all">All Columns</option>
    <?php
    $columns = [
        "Last Name", "First Name", "Middle Name", "Ext", "Sex", "Street", "Purok Name", "Place of Birth", "Birth Date",
        "Age", "Civil Status", "Citizenship", "Employed/Unemployed", "Solo Parent", "OFW",
        "Occupation", "Toilet", "School Youth", "PWD", "Indigenous", "Contact no.", "Facebook", "Valid ID", "ID Type", "Household ID"
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
      <?php
      // Sort people array by last name alphabetically
      usort($people, function ($a, $b) {
        return strcmp($a['last_name'], $b['last_name']);
      });
      ?>
      
      <?php foreach ($people as $person): 
        // Calculate age based on date_of_birth and current date
        $dob = $person['date_of_birth'];
        $calculatedAge = '';
        if ($dob && strtotime($dob)) {
          $birthDate = new DateTime($dob);
          $today = new DateTime();
          $calculatedAge = $today->diff($birthDate)->y;
          // Update DB if calculated age is different from stored age
          if (isset($person['age']) && $calculatedAge != $person['age']) {
            $updateStmt = $pdo->prepare("UPDATE people SET age = ? WHERE last_name = ? AND first_name = ? AND middle_name = ?");
            $updateStmt->execute([$calculatedAge, $person['last_name'], $person['first_name'], $person['middle_name']]);
          }
        } else {
          $calculatedAge = htmlspecialchars($person['age']); // fallback
        }
      ?>
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
          <td><?= $calculatedAge ?></td>
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
          <td><?= htmlspecialchars($person['household_id']) ?></td>
        </tr>
      <?php endforeach; ?>

      <script>
        document.addEventListener('keydown', function(e) {
          const tableContainer = document.querySelector('.table-container');
          const scrollSpeed = 140; // Adjust scroll speed as needed
          const scrollDuration = 300; // Duration for smooth scrolling in milliseconds

          function smoothScrollBy(x, y) {
        const startTime = performance.now();

        function step(currentTime) {
          const elapsedTime = currentTime - startTime;
          const progress = Math.min(elapsedTime / scrollDuration, 1);

          tableContainer.scrollBy(x * progress, y * progress);

          if (progress < 1) {
            requestAnimationFrame(step);
          }
        }

        requestAnimationFrame(step);
          }

          if (e.key === 'ArrowDown') {
        e.preventDefault();
        smoothScrollBy(0, scrollSpeed);
          } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        smoothScrollBy(0, -scrollSpeed);
          } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        smoothScrollBy(scrollSpeed, 0);
          } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        smoothScrollBy(-scrollSpeed, 0);
          }
        });
      </script>

      <script>
        // Assign person['id'] to const id when a row is clicked
        document.querySelectorAll('.clickable-row').forEach(row => {
          row.addEventListener('click', function(e) {
        const id = this.getAttribute('data-id');
        // You can use the id variable here as needed
        // Example: console.log(id);
          });
        });
      </script>
      
    </tbody>
  </table>

  <div id="popupOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;">
    <div id="popupBox" style="background:#fff;max-width:500px;width:90vw;max-height:80vh;overflow:auto;position:relative;margin:60px auto;padding:12px 12px 16px 12px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.2);font-size:10px;">
      <button id="popupCloseBtn" style="position:absolute;top:8px;right:12px;background:transparent;border:none;font-size:20px;cursor:pointer;">&times;</button>
      <div id="popupContent"></div>
      <div id="popupEditBtnContainer" style="margin-top:18px;text-align:right;">
        <!-- Edit button will be injected here -->
      </div>
    </div>
  </div>
  <script>
    // Get headers from the table
    const tableHeaders = Array.from(document.querySelectorAll("table thead th")).map(th => th.innerText);
    const dataTable = document.getElementById("dataTable");
    const popupOverlay = document.getElementById("popupOverlay");
    const popupContent = document.getElementById("popupContent");
    const popupCloseBtn = document.getElementById("popupCloseBtn");
    const popupEditBtnContainer = document.getElementById("popupEditBtnContainer");
    let popupPermanent = false;

    // Helper to show popup with row data
    function showPopup(row) {
      const cells = Array.from(row.children);
      let html = '<table style="width:100%;border-collapse:collapse;font-size:8px !important;">';
      for (let i = 0; i < cells.length; i++) {
        html += `<tr>
          <td style="font-weight:bold;padding:5px 7px;border-bottom:1px solid #eee;">${tableHeaders[i]}</td>
          <td style="padding:5px 7px;border-bottom:1px solid #eee;">${cells[i].innerText}</td>
        </tr>`;
      }
      html += '</table>';
      popupContent.innerHTML = html;
      popupOverlay.style.display = "block";
      popupPermanent = true;
      // Disable all row clicks
      Array.from(dataTable.querySelectorAll("tr")).forEach(tr => {
        tr.classList.add("popup-disabled");
      });

     
      // You may want to use a unique ID if available in your data

      
      // Build edit link (adjust as needed for your edit.php)
      // Build edit link using last_name, first_name, and middle_name from the row
      const lastName = encodeURIComponent(cells[0]?.innerText || '');
      const firstName = encodeURIComponent(cells[1]?.innerText || '');
      const middleName = encodeURIComponent(cells[2]?.innerText || '');
      const editUrl = `edit.php?last_name=${lastName}&first_name=${firstName}&middle_name=${middleName}`;
      if (editUrl) {
        popupEditBtnContainer.innerHTML = `<a href="${editUrl}" target="_blank" style="font-size:13px;text-align:left;padding:7px 16px;background:#6ca0a3;color:#fff;text-decoration:none;border-radius:4px;transition:background 0.2s;">Edit</a>`;
      } else {
        popupEditBtnContainer.innerHTML = `<button style="font-size:13px;text-align:left;padding:7px 16px;background:#ccc;color:#fff;border:none;border-radius:4px;cursor:default;">Edit</button>`;
      }
    }

    // Helper to close popup
    function closePopup() {
      popupOverlay.style.display = "none";
      popupPermanent = false;
      popupEditBtnContainer.innerHTML = "";
      // Enable all row clicks
      Array.from(dataTable.querySelectorAll("tr")).forEach(tr => {
        tr.classList.remove("popup-disabled");
      });
    }

    // Add click event to each row
    Array.from(dataTable.querySelectorAll("tr")).forEach(tr => {
      tr.addEventListener("click", function(e) {
        if (popupPermanent) return;
        showPopup(this);
      });
      tr.style.cursor = "pointer";
    });

    // Close button
    popupCloseBtn.addEventListener("click", function() {
      closePopup();
    });

    // ESC key closes popup
    document.addEventListener("keydown", function(e) {
      if (popupPermanent && (e.key === "Escape" || e.key === "Esc")) {
        closePopup();
      }
    });

    // Close popup when clicking outside the popupBox
    popupOverlay.addEventListener("click", function(e) {
      if (e.target === popupOverlay) {
        closePopup();
      }
    });

    // Prevent click-through on popupBox
    document.getElementById("popupBox").addEventListener("click", function(e) {
      e.stopPropagation();
    });
  </script>






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
