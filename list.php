<?php
// Move this block to the very top of the file, before any HTML, whitespace, or echo output
if (isset($_GET['export']) && $_GET['export'] == '1') {
    require 'vendor/autoload.php';
    $pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $columns = [
        'last_name', 'first_name', 'middle_name', 'ext_name', 'sex_name',
        'street_name', 'purok_name', 'place_of_birth', 'date_of_birth', 'age',
        'civil_status', 'citizenship', 'employed_unemployed', 'solo_parent', 'ofw',
        'occupation', 'toilet', 'school_youth', 'pwd', 'indigenous',
        'cellphone_no', 'facebook', 'valid_id', 'type_id', 'household_id', 'family_id','womens_association', 'senior_citizen'
    ];
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
      "Family ID" => "family_id",
      "Age Group DILG" => "age_group_dilg",
      "Age Group DISASTER" => "age_group_disaster",
      "Womens Association" => "womens_association",
      "Senior Citizen" => "senior_citizen_filter"
    ];
    // Fetch all people for export if not filtered
    $stmt = $pdo->query("SELECT * FROM people");
    $people = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Re-run the filter logic to get $filteredPeople for export
    $searchValue = isset($_GET['search_value']) ? trim($_GET['search_value']) : '';
    $searchColumn = isset($_GET['search_column']) ? $_GET['search_column'] : '';
    $ageGroupDilg = isset($_GET['age_group_dilg_value']) ? $_GET['age_group_dilg_value'] : '';
    $ageGroupDisaster = isset($_GET['age_group_disaster_value']) ? $_GET['age_group_disaster_value'] : '';
    $filteredPeople = $people;
    if (isset($filterOptions[$searchColumn])) {
      $filter = $filterOptions[$searchColumn];
      if ($filter === 'womens_association') {
        $stmt = $pdo->prepare("SELECT * FROM people WHERE womens_association = 'yes'");
        $stmt->execute();
        $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } else if ($filter === 'senior_citizen_filter') {
        $stmt = $pdo->prepare("SELECT * FROM people WHERE (age >= 60 AND age NOT LIKE '%months%')");
        $stmt->execute();
        $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } else if ($filter === 'age_group_dilg' && $ageGroupDilg !== '') {
        $ageWhere = '';
        switch ($ageGroupDilg) {
          case '0-11 months': $ageWhere = "age LIKE '%months%'"; break;
          case '1-2 years old': $ageWhere = "(age = 1 OR age = 2) AND age NOT LIKE '%months%'"; break;
          case '3-5': $ageWhere = "(age >= 3 AND age <= 5) AND age NOT LIKE '%months%'"; break;
          case '6-12': $ageWhere = "(age >= 6 AND age <= 12) AND age NOT LIKE '%months%'"; break;
          case '13-17': $ageWhere = "(age >= 13 AND age <= 17) AND age NOT LIKE '%months%'"; break;
          case '18-59': $ageWhere = "(age >= 18 AND age <= 59) AND age NOT LIKE '%months%'"; break;
          case '60 and up': $ageWhere = "(age >= 60) AND age NOT LIKE '%months%'"; break;
        }
        if ($ageWhere) {
          $stmt = $pdo->prepare("SELECT * FROM people WHERE $ageWhere");
          $stmt->execute();
          $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
      } else if ($filter === 'age_group_disaster' && $ageGroupDisaster !== '') {
        $ageWhere = '';
        switch ($ageGroupDisaster) {
          case 'Children 0-5 years old': $ageWhere = "(age LIKE '%months%' OR (age >= 0 AND age <= 5) AND age NOT LIKE '%months%')"; break;
          case 'Children 6-12 years old': $ageWhere = "(age >= 6 AND age <= 12) AND age NOT LIKE '%months%'"; break;
          case 'Children 13-17 years old': $ageWhere = "(age >= 13 AND age <= 17) AND age NOT LIKE '%months%'"; break;
          case 'Adult 18-35 years old': $ageWhere = "(age >= 18 AND age <= 35) AND age NOT LIKE '%months%'"; break;
          case 'Adult 36-50 years old': $ageWhere = "(age >= 36 AND age <= 50) AND age NOT LIKE '%months%'"; break;
          case 'Adult 51-65 years old': $ageWhere = "(age >= 51 AND age <= 65) AND age NOT LIKE '%months%'"; break;
          case 'Adult 66 years old and above': $ageWhere = "(age >= 66) AND age NOT LIKE '%months%'"; break;
        }
        if ($ageWhere) {
          $stmt = $pdo->prepare("SELECT * FROM people WHERE $ageWhere");
          $stmt->execute();
          $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
      } else if ($filter === 'pwd') {
        // Show only people with PWD value not 'NO' and not blank
        $stmt = $pdo->prepare("SELECT * FROM people WHERE pwd IS NOT NULL AND TRIM(pwd) != '' AND UPPER(TRIM(pwd)) != 'NO'");
        $stmt->execute();
        $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultCount = count($filteredPeople);
      }
       else if ($filter === 'ofw') {
        // Show only people with OFW value not 'NO' and not blank
        $stmt = $pdo->prepare("SELECT * FROM people WHERE ofw IS NOT NULL AND TRIM(ofw) != '' AND UPPER(TRIM(ofw)) != 'NO'");
        $stmt->execute();
        $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultCount = count($filteredPeople);
      }
      else if (strpos($filter, ':') !== false) {
        list($col, $val) = explode(':', $filter, 2);
        $stmt = $pdo->prepare("SELECT * FROM people WHERE $col = ?");
        $stmt->execute([$val]);
        $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } else if ($searchValue !== '') {
        $col = $filter;
        if ($col === 'age') {
          $stmt = $pdo->prepare("SELECT * FROM people WHERE age = ? AND age NOT LIKE '%months%'");
          $stmt->execute([$searchValue]);
        } else {
          $stmt = $pdo->prepare("SELECT * FROM people WHERE $col LIKE ?");
          $stmt->execute(['%' . $searchValue . '%']);
        }
        $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
    } elseif ($searchValue !== '' && $searchColumn === 'All') {
      $where = [];
      $params = [];
      foreach ($filterOptions as $label => $col) {
        if (strpos($col, ':') !== false) {
          $col = explode(':', $col, 2)[0];
        }
        if ($col === 'age_group_dilg' || $col === 'age_group_disaster' || $col === 'senior_citizen_filter') continue;
        $where[] = "$col LIKE ?";
        $params[] = '%' . $searchValue . '%';
      }
      $stmt = $pdo->prepare("SELECT * FROM people WHERE " . implode(' OR ', $where));
      $stmt->execute($params);
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Determine export filename based on filter
    $filename = 'people_filtered.xlsx';
    if (isset($filterOptions[$searchColumn]) && $searchColumn !== '') {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $searchColumn);
        $filename = strtolower($safeName) . '_filtered.xlsx';
    } elseif ($searchColumn === 'All' && $searchValue !== '') {
        $filename = 'all_columns_filtered.xlsx';
    }
    // Output
    while (ob_get_level() > 0) ob_end_clean();
    if (function_exists('ini_set')) {
        ini_set('zlib.output_compression', 'Off');
    }
    // Prevent any accidental whitespace or output before headers
    if (!headers_sent()) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');
    }
    flush();
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set font size 12px and center alignment for all cells (use correct object)
    $spreadsheet->getActiveSheet()->getStyle(
        $sheet->calculateWorksheetDimension()
    )->getFont()->setSize(12);
    $spreadsheet->getActiveSheet()->getStyle(
        $sheet->calculateWorksheetDimension()
    )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // Set headers (all uppercase)
    $colIndex = 1;
    foreach ($columns as $col) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $headerText = strtoupper(str_replace('_', ' ', $col));
        $sheet->setCellValue($colLetter . '1', $headerText);
        // Style header: bold, 12px, center
        $sheet->getStyle($colLetter . '1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle($colLetter . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $colIndex++;
    }
    // Set data (all center, 12px, and uppercase)
    $rowIndex = 2;
    foreach ($filteredPeople as $person) {
        $colIndex = 1;
        foreach ($columns as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $cellValue = isset($person[$col]) ? strtoupper($person[$col]) : '';
            $sheet->setCellValue($colLetter . $rowIndex, $cellValue);
            $sheet->getStyle($colLetter . $rowIndex)->getFont()->setSize(12);
            $sheet->getStyle($colLetter . $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $colIndex++;
        }
        $rowIndex++;
    }
    // Auto-size columns
    foreach (range(1, count($columns)) as $colIndex) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }
    // Clean output buffer again just before output
    if (ob_get_length()) ob_end_clean();
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>

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
                    cellphone_no, facebook, valid_id, type_id, family_id, household_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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



$columns = [
    'last_name', 'first_name', 'middle_name', 'ext_name', 'sex_name',
    'street_name', 'purok_name', 'place_of_birth', 'date_of_birth', 'age',
    'civil_status', 'citizenship', 'employed_unemployed', 'solo_parent', 'ofw',
    'occupation', 'toilet', 'school_youth', 'pwd', 'indigenous',
    'cellphone_no', 'facebook', 'valid_id', 'type_id', 'household_id','family_id', 'womens_association', 'senior_citizen'
];




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

    #exportBtn, #printBtn {
      transition: all 0.4s;
    }

    #exportBtn:hover, #printBtn:hover {
      background-color:rgb(25, 117, 202) !important;
    }
  </style>
</head>
<body>

<header>
  <nav>
    <div class="logo">BARANGAY PAMANLINAN DEMOGRAPHIC RECORDS</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="dashboard.php">DASHBOARD</a></li>
      <li><a href="ageGroup.php">AGE GROUP</a></li>
      <li><a href="disabilitiesGroup.php">DISABILITIES</a></li>
      <li><a href="deceased.php">DECEASED</a></li>
      <li><a href="add.php">ADD</a></li>
      <li><a href="logout.php">LOGOUT</a></li>
    </ul>
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
   "Family ID" => "family_id",

  // Add Age Group DILG filter
  "Age Group DILG" => "age_group_dilg",
  // Add Age Group DISASTER filter
  "Age Group DISASTER" => "age_group_disaster",
  "Womens Association" => "womens_association",
  "Senior Citizen" => "senior_citizen_filter"
];

$searchValue = isset($_GET['search_value']) ? trim($_GET['search_value']) : '';
$searchColumn = isset($_GET['search_column']) ? $_GET['search_column'] : '';
// Add: get age group value if set
$ageGroupDilg = isset($_GET['age_group_dilg_value']) ? $_GET['age_group_dilg_value'] : '';
$ageGroupDisaster = isset($_GET['age_group_disaster_value']) ? $_GET['age_group_disaster_value'] : '';
$totalCount = $pdo->query("SELECT COUNT(*) FROM people")->fetchColumn();

$filteredPeople = $people;
$resultCount = count($people);

if (isset($filterOptions[$searchColumn])) {
  $filter = $filterOptions[$searchColumn];
  if ($filter === 'womens_association') {
    // Show only rows where womens_association is 'yes'
    $stmt = $pdo->prepare("SELECT * FROM people WHERE womens_association = 'yes'");
    $stmt->execute();
    $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $resultCount = count($filteredPeople);
  } else if ($filter === 'senior_citizen_filter') {
    // Show only rows where age is 60 or above (exclude months)
    $stmt = $pdo->prepare("SELECT * FROM people WHERE (age >= 60 AND age NOT LIKE '%months%')");
    $stmt->execute();
    $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $resultCount = count($filteredPeople);
  } else if ($filter === 'age_group_dilg' && $ageGroupDilg !== '') {
    // Age group filter logic
    $ageWhere = '';
    switch ($ageGroupDilg) {
      case '0-11 months':
        // Only select rows where age contains 'months'
        $ageWhere = "age LIKE '%months%'";
        break;
      case '1-2 years old':
        $ageWhere = "(age = 1 OR age = 2) AND age NOT LIKE '%months%'";
        break;
      case '3-5':
        $ageWhere = "(age >= 3 AND age <= 5) AND age NOT LIKE '%months%'";
        break;
      case '6-12':
        $ageWhere = "(age >= 6 AND age <= 12) AND age NOT LIKE '%months%'";
        break;
      case '13-17':
        $ageWhere = "(age >= 13 AND age <= 17) AND age NOT LIKE '%months%'";
        break;
      case '18-59':
        $ageWhere = "(age >= 18 AND age <= 59) AND age NOT LIKE '%months%'";
        break;
      case '60 and up':
        $ageWhere = "(age >= 60) AND age NOT LIKE '%months%'";
        break;
    }
    if ($ageWhere) {
      $stmt = $pdo->prepare("SELECT * FROM people WHERE $ageWhere");
      $stmt->execute();
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultCount = count($filteredPeople);
    }
  } else if ($filter === 'age_group_disaster' && $ageGroupDisaster !== '') {
    // Age group DISASTER filter logic
    $ageWhere = '';
    switch ($ageGroupDisaster) {
      case 'Children 0-5 years old':
        $ageWhere = "(age LIKE '%months%' OR (age >= 0 AND age <= 5) AND age NOT LIKE '%months%')";
        break;
      case 'Children 6-12 years old':
        $ageWhere = "(age >= 6 AND age <= 12) AND age NOT LIKE '%months%'";
        break;
      case 'Children 13-17 years old':
        $ageWhere = "(age >= 13 AND age <= 17) AND age NOT LIKE '%months%'";
        break;
      case 'Adult 18-35 years old':
        $ageWhere = "(age >= 18 AND age <= 35) AND age NOT LIKE '%months%'";
        break;
      case 'Adult 36-50 years old':
        $ageWhere = "(age >= 36 AND age <= 50) AND age NOT LIKE '%months%'";
        break;
      case 'Adult 51-65 years old':
        $ageWhere = "(age >= 51 AND age <= 65) AND age NOT LIKE '%months%'";
        break;
      case 'Adult 66 years old and above':
        $ageWhere = "(age >= 66) AND age NOT LIKE '%months%'";
        break;
    }
    if ($ageWhere) {
      $stmt = $pdo->prepare("SELECT * FROM people WHERE $ageWhere");
      $stmt->execute();
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultCount = count($filteredPeople);
    }
  } else if ($filter === 'pwd') {
    // Show only people with PWD value not 'NO' and not blank
    $stmt = $pdo->prepare("SELECT * FROM people WHERE pwd IS NOT NULL AND TRIM(pwd) != '' AND UPPER(TRIM(pwd)) != 'NO'");
    $stmt->execute();
    $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $resultCount = count($filteredPeople);
  } else if ($filter === 'ofw') {
    // Show only people with OFW value not 'NO' and not blank
    $stmt = $pdo->prepare("SELECT * FROM people WHERE ofw IS NOT NULL AND TRIM(ofw) != '' AND UPPER(TRIM(ofw)) != 'NO'");
    $stmt->execute();
    $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $resultCount = count($filteredPeople);
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
    if ($col === 'age') {
      // Exact match for age (exclude 'months' values)
      $stmt = $pdo->prepare("SELECT * FROM people WHERE age = ? AND age NOT LIKE '%months%'");
      $stmt->execute([$searchValue]);
    } else {
      $stmt = $pdo->prepare("SELECT * FROM people WHERE $col LIKE ?");
      $stmt->execute(['%' . $searchValue . '%']);
    }
    if (isset($stmt)) {
      $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $resultCount = count($filteredPeople);
    }
  }
} elseif ($searchValue !== '' && $searchColumn === 'All') {
  // Search all columns
  $where = [];
  $params = [];
  foreach ($filterOptions as $label => $col) {
    if (strpos($col, ':') !== false) {
      $col = explode(':', $col, 2)[0];
    }
    // Skip pseudo-columns like Age Group DILG and Age Group DISASTER, and senior citizen
    if ($col === 'age_group_dilg' || $col === 'age_group_disaster' || $col === 'senior_citizen_filter') continue;
    $where[] = "$col LIKE ?";
    $params[] = '%' . $searchValue . '%';
  }
  $stmt = $pdo->prepare("SELECT * FROM people WHERE " . implode(' OR ', $where));
  $stmt->execute($params);
  $filteredPeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $resultCount = count($filteredPeople);
}
?>


<div class="import" style="margin: 24px 0 18px 20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
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
    <!-- Age Group DISASTER secondary filter -->
    <select id="ageGroupDisasterSelect" name="age_group_disaster_value" style="padding:7px 10px;border:1px solid #bbb;border-radius:4px;display:none;">
      <option value="">Select Age Group</option>
      <option value="Children 0-5 years old" <?= $ageGroupDisaster === 'Children 0-5 years old' ? 'selected' : '' ?>>Children 0-5 years old</option>
      <option value="Children 6-12 years old" <?= $ageGroupDisaster === 'Children 6-12 years old' ? 'selected' : '' ?>>Children 6-12 years old</option>
      <option value="Children 13-17 years old" <?= $ageGroupDisaster === 'Children 13-17 years old' ? 'selected' : '' ?>>Children 13-17 years old</option>
      <option value="Adult 18-35 years old" <?= $ageGroupDisaster === 'Adult 18-35 years old' ? 'selected' : '' ?>>Adult 18-35 years old</option>
      <option value="Adult 36-50 years old" <?= $ageGroupDisaster === 'Adult 36-50 years old' ? 'selected' : '' ?>>Adult 36-50 years old</option>
      <option value="Adult 51-65 years old" <?= $ageGroupDisaster === 'Adult 51-65 years old' ? 'selected' : '' ?>>Adult 51-65 years old</option>
      <option value="Adult 66 years old and above" <?= $ageGroupDisaster === 'Adult 66 years old and above' ? 'selected' : '' ?>>Adult 66 years old and above</option>
    </select>
    <!-- Search button removed -->
  </form>
  <span id="resultCount" style="font-size:15px;color:#444;display:inline;margin-left:-30px"></span>
    Showing <?= $resultCount ?> out of <?= $totalCount ?> result<?= $resultCount === 1 ? '' : 's' ?>
  </span>
  <button id="exportBtn" style="padding:7px 18px;background:#6ca0a3;color:#fff;border:none;border-radius:4px;font-size:15px;cursor:pointer;">Export to Excel</button>
  <button id="printBtn" style="padding:7px 18px;background:#6ca0a3;color:#fff;border:none;border-radius:4px;font-size:15px;cursor:pointer;">Print</button>
   
  <!-- Excel Import Form -->
  <div class="upload-files-box">
    <form action="list.php" method="post" enctype="multipart/form-data" class="upload_files">
      <p style="margin-right:-12px;">Import Excel File to Database</p>
      <input type="file" name="excel_file" accept=".xlsx, .xls" required>
      <input type="submit" value="Upload & Import">
    </form>
    <?php
    // Handle Excel import (excluding age, womens_association, senior_citizen)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']['tmp_name']) && is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
        require_once 'vendor/autoload.php';
        $filePath = $_FILES['excel_file']['tmp_name'];
        $pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $duplicates = 0;
        $imported = 0;
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            if (count($rows) < 2) {
                echo "<script>alert('No data rows found in the Excel file.');</script>";
            } else {
                // Get columns from the people table and which are NOT NULL
                $tableColsInfo = $pdo->query("DESCRIBE people")->fetchAll(PDO::FETCH_ASSOC);
                $tableCols = [];
                $notNullCols = [];
                foreach ($tableColsInfo as $colInfo) {
                    if ($colInfo['Field'] === 'id') continue;
                    $tableCols[] = $colInfo['Field'];
                    if ($colInfo['Null'] === 'NO' && $colInfo['Default'] === null) {
                        $notNullCols[] = $colInfo['Field'];
                    }
                }
                $header = array_map(function($h) { return strtolower(str_replace([' ', '-'], '_', trim($h))); }, $rows[0]);
                // Map header to table columns
                $colMap = [];
                foreach ($header as $i => $col) {
                    if (in_array($col, $tableCols)) {
                        $colMap[$i] = $col;
                    }
                }
                if (empty($colMap)) {
                    echo "<script>alert('Excel header does not match any database columns. Please check your file.');</script>";
                    return;
                }
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $data = [];
                    foreach ($colMap as $idx => $colName) {
                        $cellVal = isset($row[$idx]) ? trim($row[$idx]) : null;
                        // If required column and value is empty/null, use empty string
                        if (in_array($colName, $notNullCols) && ($cellVal === null || $cellVal === '')) {
                            $cellVal = '';
                        }
                        $data[$colName] = $cellVal;
                    }
                    // If all values are empty, skip
                    if (count(array_filter($data, function($v) { return $v !== null && $v !== ''; })) === 0) continue;
                    // Check for duplicate (by last_name, first_name, middle_name)
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM people WHERE last_name = ? AND first_name = ? AND middle_name = ?");
                    $stmt->execute([
                        $data['last_name'] ?? '',
                        $data['first_name'] ?? '',
                        $data['middle_name'] ?? ''
                    ]);
                    if ($stmt->fetchColumn() > 0) {
                        $duplicates++;
                        continue;
                    }
                    $insertCols = array_keys($data);
                    $insertVals = array_values($data);
                    $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
                    $insertSql = "INSERT INTO people (" . implode(',', $insertCols) . ") VALUES ($placeholders)";
                    $insertStmt = $pdo->prepare($insertSql);
                    if (!$insertStmt->execute($insertVals)) {
                        $error = $insertStmt->errorInfo();
                        echo '<script>alert("Insert failed: ' . addslashes(json_encode($error)) . ' Row: ' . json_encode($data) . '");</script>';
                    } else {
                        $lastId = $pdo->lastInsertId();
                        if (!$lastId) {
                            echo '<script>alert("Insert statement executed but no row inserted. Row: ' . json_encode($data) . '");</script>';
                        }
                        $imported++;
                    }
                }
                if ($imported === 0 && $duplicates === 0) {
                    echo "<script>alert('No records imported. Please check your Excel file format and columns.');</script>";
                } else {
                    echo "<script>alert('Import complete. $imported record(s) imported. $duplicates duplicate(s) skipped.');window.location='list.php';</script>";
                }
            }
        } catch (Exception $e) {
            echo "<script>alert('Error importing file: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
    ?>
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
  const ageGroupDisasterSelect = document.getElementById('ageGroupDisasterSelect');
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
    if (columnSelect.value === 'Age Group DILG') {
      searchInput.value = '';
      ageGroupDilgSelect.style.display = '';
      ageGroupDisasterSelect.style.display = 'none';
    } else if (columnSelect.value === 'Age Group DISASTER') {
      searchInput.value = '';
      ageGroupDilgSelect.style.display = 'none';
      ageGroupDisasterSelect.style.display = '';
    } else {
      ageGroupDilgSelect.style.display = 'none';
      ageGroupDisasterSelect.style.display = 'none';
      searchInput.style.display = '';
    }
    toggleAgeGroupDilg();
    submitSearch();
  });
  ageGroupDilgSelect.addEventListener('change', function() {
    submitSearch();
  });
  ageGroupDisasterSelect.addEventListener('change', function() {
    submitSearch();
  });
  function toggleAgeGroupDilg() {
    if (columnSelect.value === 'Age Group DILG') {
      ageGroupDilgSelect.style.display = '';
      ageGroupDisasterSelect.style.display = 'none';
      searchInput.style.display = 'none';
    } else if (columnSelect.value === 'Age Group DISASTER') {
      ageGroupDilgSelect.style.display = 'none';
      ageGroupDisasterSelect.style.display = '';
      searchInput.style.display = 'none';
    } else {
      ageGroupDilgSelect.style.display = 'none';
      ageGroupDisasterSelect.style.display = 'none';
      searchInput.style.display = '';
    }
  }
  // On page load
  toggleAgeGroupDilg();
  toggleAgeGroupDisaster();
</script>

 

</div>


<!-- Table -->
<div class="table-container">
  <table>
    <thead>
      <tr>
        <?php foreach ($columns as $col): ?>
            <?php if ($col == 'employed_unemployed'): ?>
                <th><?= htmlspecialchars(str_replace('_','/', $col)) ?></th>
            <?php elseif ($col == 'sex_name'): ?>
                <th>SEX</th>
            <?php elseif ($col == 'purok_name'): ?>
                <th>PUROK</th>
            <?php elseif ($col == 'street_name'): ?>
                <th>STREET</th>
            <?php else: ?>
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
      
      <?php
      // Function to calculate age based on date_of_birth
      if (!function_exists('calculateAge')) {
        function calculateAge($dob) {
          if (!$dob || $dob == '0000-00-00') return '';
          $birthDate = new DateTime($dob);
          $today = new DateTime();
          $interval = $today->diff($birthDate);
          $years = $interval->y;
          $months = $interval->m + ($interval->y * 12);
          // If less than 1 year old, show in months
          if ($years < 1) {
            if ($interval->m < 1 && $interval->d < 1) $months = 0;
            return $months . ' months';
          }
          return $years;
        }
      }
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
          <?php
          // Always recalculate age and update DB if needed
          $calculatedAge = calculateAge($person['date_of_birth']);
          if ($person['age'] !== $calculatedAge && $calculatedAge !== '') {
            $updateStmt = $pdo->prepare("UPDATE people SET age = ? WHERE last_name = ? AND first_name = ? AND middle_name = ?");
            $updateStmt->execute([
              $calculatedAge,
              $person['last_name'],
              $person['first_name'],
              $person['middle_name']
            ]);
            $person['age'] = $calculatedAge;
          }
          ?>
          <td><?= htmlspecialchars($person['age']) ?></td>
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
          <td><?= htmlspecialchars($person['family_id']) ?></td>
          <td><?= htmlspecialchars($person['womens_association']) ?></td>
            <?php
            // Determine if person is a senior citizen (age 60 and above)
            $isSenior = false;
            if (is_numeric($person['age']) && intval($person['age']) >= 60) {
              $isSenior = true;
            } elseif (is_string($person['age']) && preg_match('/^\d+$/', $person['age']) && intval($person['age']) >= 60) {
              $isSenior = true;
            }

            $seniorValue = $isSenior ? 'Yes' : 'No';

            // Update the database if value is different
            if ($person['senior_citizen'] !== $seniorValue) {
              $updateSenior = $pdo->prepare("UPDATE people SET senior_citizen = ? WHERE last_name = ? AND first_name = ? AND middle_name = ?");
              $updateSenior->execute([
                $seniorValue,
                $person['last_name'],
                $person['first_name'],
                $person['middle_name']
              ]);
              // Update local array for display
              $person['senior_citizen'] = $seniorValue;
            }
            ?>
            <td><?= htmlspecialchars($person['senior_citizen']) ?></td>
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



<!-- print and export button  -->
<script>
document.getElementById('exportBtn').onclick = function(e) {
  e.preventDefault();
  // Build query string from current filters
  const params = new URLSearchParams(new FormData(document.getElementById('searchForm'))).toString();
  window.location.href = 'list.php?export=1&' + params;
};

document.getElementById('printBtn').onclick = function() {
  var printContents = document.querySelector('.table-container').outerHTML;
  var style = `<style>body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}table{border-collapse:collapse;width:100%;background:#fff;}th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}th{background:#f0f0f0;text-transform:uppercase;}</style>`;
    link.download = "people_list.csv";
    link.click();
    URL.revokeObjectURL(url);
  }
</script>



<!-- print and export button  -->
<script>
document.getElementById('exportBtn').onclick = function(e) {
  e.preventDefault();
  // Build query string from current filters
  const params = new URLSearchParams(new FormData(document.getElementById('searchForm'))).toString();
  window.location.href = 'list.php?export=1&' + params;
};

document.getElementById('printBtn').onclick = function() {
  var printContents = document.querySelector('.table-container').outerHTML;
  var style = `<style>body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}table{border-collapse:collapse;width:100%;background:#fff;}th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}th{background:#f0f0f0;text-transform:uppercase;}</style>`;
  var win = window.open('', '', 'height=900,width=1200');
  win.document.write('<html><head><title>Print Results</title></head><body>' + style + printContents + '</body></html>');
  win.document.close();
  win.focus();
  setTimeout(function() { win.print(); win.close(); }, 500);
};
</script>



</body>
</html>
