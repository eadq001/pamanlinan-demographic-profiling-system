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
      <li><a href="dashboard.php">DASHBOARD</a></li>
      <li><a href="add.php">ADD</a></li>
      <li><a href="index.php">LOGOUT</a></li>
    </ul>
    <div class="burger" id="burger">&#9776;</div>
  </nav>
</header>

<?php
// Map filter labels to database columns
$filterOptions = [
  "Last Name" => "last_name",
  "First Name" => "first_name",
  "Middle Name" => "middle_name",
  "Ext" => "ext_name",
  "Sex" => "sex_name",
  "Street" => "street_name",
  "Purok Name" => "purok_name",
  "Place of Birth" => "place_of_birth",
  "Birth Date" => "date_of_birth",
  "Age" => "age",
  "Civil Status" => "civil_status",
  "Citizenship" => "citizenship",
  // Custom filter for Employed/Unemployed
  "Employed" => "employed_unemployed:Employed",
  "Unemployed" => "employed_unemployed:Unemployed",
  "Solo Parent" => "solo_parent",
  "OFW" => "ofw",
  "Occupation" => "occupation",
  "Toilet" => "toilet",
  "School Youth" => "school_youth",
  "PWD" => "pwd",
  "Indigenous" => "indigenous",
  "Contact no." => "cellphone_no",
  "Facebook" => "facebook",
  "Valid ID" => "valid_id",
  "ID Type" => "type_id",
  "Household ID" => "household_id",
  // Add Age Group DILG filter
  "Age Group DILG" => "age_group_dilg"
];

$searchValue = isset($_GET['search_value']) ? trim($_GET['search_value']) : '';
$searchColumn = isset($_GET['search_column']) ? $_GET['search_column'] : '';
// Add: get age group value if set
$ageGroupDilg = isset($_GET['age_group_dilg_value']) ? $_GET['age_group_dilg_value'] : '';
$totalCount = $pdo->query("SELECT COUNT(*) FROM people")->fetchColumn();

$filteredPeople = $people;
$resultCount = count($people);

if (isset($filterOptions[$searchColumn])) {
  $filter = $filterOptions[$searchColumn];
  if ($filter === 'age_group_dilg' && $ageGroupDilg !== '') {
    // Age group filter logic
    $ageWhere = '';
    switch ($ageGroupDilg) {
      case '0-11 months':
        $ageWhere = "(age = 0 OR age = 1 OR age = 2 OR age = 3 OR age = 4 OR age = 5 OR age = 6 OR age = 7 OR age = 8 OR age = 9 OR age = 10 OR age = 11)";
        break;
      case '1-2 years old':
        $ageWhere = "(age = 1 OR age = 2)";
        break;
      case '3-5':
        $ageWhere = "(age >= 3 AND age <= 5)";
        break;
      case '6-12':
        $ageWhere = "(age >= 6 AND age <= 12)";
        break;
      case '13-17':
        $ageWhere = "(age >= 13 AND age <= 17)";
        break;
      case '18-59':
        $ageWhere = "(age >= 18 AND age <= 59)";
        break;
      case '60 and up':
        $ageWhere = "(age >= 60)";
        break;
    }
    if ($ageWhere) {
      $stmt = $pdo->prepare("SELECT * FROM people WHERE $ageWhere");
      $stmt->execute();
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultCount = count($filteredPeople);
    }
  } else if (strpos($filter, ':') !== false) {
    // For Employed/Unemployed exact match, ignore search value
    list($col, $val) = explode(':', $filter, 2);
    $stmt = $pdo->prepare("SELECT * FROM people WHERE $col = ?");
    $stmt->execute([$val]);
    if (isset($stmt)) {
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultCount = count($filteredPeople);
    }
  } else if ($searchValue !== '') {
    $col = $filter;
    $stmt = $pdo->prepare("SELECT * FROM people WHERE $col LIKE ?");
    $stmt->execute(['%' . $searchValue . '%']);
    if (isset($stmt)) {
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultCount = count($filteredPeople);
    }
  }
} elseif ($searchValue !== '' && $searchColumn === 'All') {
  // Search all columns
  $where = [];
  $params = [];
  foreach ($filterOptions as $col) {
    if (strpos($col, ':') !== false) {
      $col = explode(':', $col, 2)[0];
    }
    // Skip pseudo-columns like Age Group DILG
    if ($col === 'age_group_dilg') continue;
    $where[] = "$col LIKE ?";
    $params[] = '%' . $searchValue . '%';
  }
  $stmt = $pdo->prepare("SELECT * FROM people WHERE " . implode(' OR ', $where));
  $stmt->execute($params);
  $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $resultCount = count($filteredPeople);
}
?>


<div style="margin: 24px 0 18px 20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
  <form id="searchForm" method="get" action="list.php" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
    <input type="text" id="searchInput" name="search_value" placeholder="Search..." value="<?= htmlspecialchars($searchValue) ?>" style="padding:7px 12px;border:1px solid #bbb;border-radius:4px;min-width:180px;">
    <select id="columnSelect" name="search_column" style="padding:7px 10px;border:1px solid #bbb;border-radius:4px;">
      <option value="All" <?= $searchColumn === 'All' ? 'selected' : '' ?>>All</option>
      <?php foreach ($filterOptions as $label => $col): ?>
        <option value="<?= htmlspecialchars($label) ?>" <?= $searchColumn === $label ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
      <?php endforeach; ?>
    </select>
    <!-- Age Group DILG secondary filter -->
    <select id="ageGroupDilgSelect" name="age_group_dilg_value" style="padding:7px 10px;border:1px solid #bbb;border-radius:4px;display:none;">
      <option value="">Select Age Group</option>
      <option value="0-11 months" <?= $ageGroupDilg === '0-11 months' ? 'selected' : '' ?>>0-11 months</option>
      <option value="1-2 years old" <?= $ageGroupDilg === '1-2 years old' ? 'selected' : '' ?>>1-2 years old</option>
      <option value="3-5" <?= $ageGroupDilg === '3-5' ? 'selected' : '' ?>>3-5</option>
      <option value="6-12" <?= $ageGroupDilg === '6-12' ? 'selected' : '' ?>>6-12</option>
      <option value="13-17" <?= $ageGroupDilg === '13-17' ? 'selected' : '' ?>>13-17</option>
      <option value="18-59" <?= $ageGroupDilg === '18-59' ? 'selected' : '' ?>>18-59</option>
      <option value="60 and up" <?= $ageGroupDilg === '60 and up' ? 'selected' : '' ?>>60 and up</option>
    </select>
    <!-- Search button removed -->
  </form>
  <span id="resultCount" style="font-size:15px;color:#444;display:<?= $searchValue !== '' ? 'inline' : 'none' ?>;">
    Showing <?= $resultCount ?> out of <?= $totalCount ?> result<?= $resultCount === 1 ? '' : 's' ?>
  </span>

   <!-- CSV Import Form -->
  <div class="upload-files-box">
    <form action="list.php" method="post" enctype="multipart/form-data" class="upload_files">
      <p>Import Excel File to Database</p>
      <input type="file" name="excel_file" accept=".xlsx, .xls" required>
      <input type="submit" value="Upload & Import">
    </form>
  </div>
</div>

<script>
  // Focus search input and place cursor at end if it has value after reload
  window.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value.trim() !== '') {
      searchInput.focus();
      // Move cursor to end
      var val = searchInput.value;
      searchInput.value = '';
      searchInput.value = val;
    }
  });
</script>

<?php
// Overwrite $people for table rendering
$people = $filteredPeople;
?>

<script>
  // Auto-submit form on input/select change
  const searchInput = document.getElementById('searchInput');
  const columnSelect = document.getElementById('columnSelect');
  const searchForm = document.getElementById('searchForm');
  const ageGroupDilgSelect = document.getElementById('ageGroupDilgSelect');
  let typingTimer;
  const doneTypingInterval = 350; // ms

  function submitSearch() {
    searchForm.submit();
  }

  searchInput.addEventListener('input', function() {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(submitSearch, doneTypingInterval);
  });

  columnSelect.addEventListener('change', function() {
    toggleAgeGroupDilg();
    submitSearch();
  });
  ageGroupDilgSelect.addEventListener('change', function() {
    submitSearch();
  });

  function toggleAgeGroupDilg() {
    if (columnSelect.value === 'Age Group DILG') {
      ageGroupDilgSelect.style.display = '';
      searchInput.style.display = 'none';
    } else {
      ageGroupDilgSelect.style.display = 'none';
      searchInput.style.display = '';
    }
  }
  // On page load
  toggleAgeGroupDilg();
</script>

 

</div>


<!-- Table -->
<div class="table-container">
  <table>
    <thead>
      <tr>
        <?php foreach ($columns as $col): ?>
                    <?php if ($col == 'employed_unemployed') :?>
            <th><?= htmlspecialchars(str_replace('_','/', $col)) ?></th>
            <?php else:?>
              <th><?= htmlspecialchars(str_replace('_',' ', $col)) ?></th>
          <?php endif; ?>
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
      
      <?php foreach ($people as $person): ?>
        <tr>
          <td><?= htmlspecialchars(rtrim($person['last_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['first_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['middle_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['ext_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['sex_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['street_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['purok_name'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['place_of_birth'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['date_of_birth'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['age'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['civil_status'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['citizenship'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['employed_unemployed'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['solo_parent'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['ofw'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['occupation'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['toilet'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['school_youth'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['pwd'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['indigenous'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['cellphone_no'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['facebook'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['valid_id'])) ?></td>
          <td><?= htmlspecialchars(rtrim($person['type_id'])) ?></td>
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
