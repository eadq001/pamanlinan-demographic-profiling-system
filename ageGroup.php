<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$ageGroups = [
    '0-11 months' => ["age LIKE '%months%'"] ,
    '1-2 years old' => ["(age = 1 OR age = 2) AND age NOT LIKE '%months%'"] ,
    '3-5 years old' => ["(age >= 3 AND age <= 5) AND age NOT LIKE '%months%'"] ,
    '6-12 years old' => ["(age >= 6 AND age <= 12) AND age NOT LIKE '%months%'"] ,
    '13-17 years old' => ["(age >= 13 AND age <= 17) AND age NOT LIKE '%months%'"] ,
    '18-59 years old' => ["(age >= 18 AND age <= 59) AND age NOT LIKE '%months%'"] ,
    '60 above years old' => ["(age >= 60) AND age NOT LIKE '%months%'"]
];

$results = [];
$totalFemales = 0;
$totalMales = 0;
$totalAll = 0;

foreach ($ageGroups as $label => $whereArr) {
    $where = $whereArr[0];
    // Count females
    $stmtF = $pdo->prepare("SELECT COUNT(*) FROM people WHERE $where AND sex_name = 'Female'");
    $stmtF->execute();
    $females = $stmtF->fetchColumn();
    // Count males
    $stmtM = $pdo->prepare("SELECT COUNT(*) FROM people WHERE $where AND sex_name = 'Male'");
    $stmtM->execute();
    $males = $stmtM->fetchColumn();
    $total = $females + $males;
    $results[] = [
        'age_group' => $label,
        'females' => $females,
        'males' => $males,
        'total' => $total
    ];
    $totalFemales += $females;
    $totalMales += $males;
    $totalAll += $total;
}

// Add grand total row
$results[] = [
    'age_group' => 'Total',
    'females' => $totalFemales,
    'males' => $totalMales,
    'total' => $totalAll
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
  <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
    <link rel="stylesheet" href="add.css" />
    <link rel="stylesheet" href="add2.css" />
    <title>Age Group Categories</title>
    <style>
        table { border-collapse: collapse; width: 60%; margin: 40px auto; background: #fff; }
        th, td { border: 1px solid #000; padding: 10px 18px; text-align: center; color: #000; }
        th { background:rgba(79, 223, 239, 0.4); color: #000; font-size: 17px; border: 1px solid #000; }
        tr:last-child { font-weight: bold; background: #f0f0f0; }
        td, th { font-size: 16px; }
        caption { font-size: 22px; margin-bottom: 12px; font-weight: bold; color: #000; }
        body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        #ageGroupTable {
            margin-top: 100px;
        }
       
        .btn:hover {
            background-color:rgb(24, 174, 182) !important;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }




    </style>
</head>
<body>
    <header>
  <nav>
    <div class="logo">BARANGAY PAMANLINAN DEMOGRAPHIC RECORDS</div>
    <ul class="nav-links" id="navLinks">
     <li><a href="dashboard.php">DASHBOARD</a></li>
     <li><a href="disabilitiesGroup.php">DISABILITIES</a></li>
     <li><a href="deceased.php">DECEASED</a></li>
     <li><a href="list.php">LISTS</a></li>
      <li><a href="add.php">ADD</a></li>
      <li><a href="logout.php">LOGOUT</a></li>
    </ul>
  </nav>
</header>
    <table id="ageGroupTable">
        <caption>DILG AGE GROUP CATEGORIES</caption>
        <thead>
            <tr>
                <th>Age Groups</th>
                <th>No. of Females</th>
                <th>No. of Males</th>
                <th>Total Numbers</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['age_group']) ?></td>
                    <td><?= $row['females'] ?></td>
                    <td><?= $row['males'] ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    // Define new age brackets
    $newAgeGroups = [
        'Children 0-5 years old' => ["(age LIKE '%months%' OR (age >= 0 AND age <= 5 AND age NOT LIKE '%months%'))"],
        'Children 6-12 years old' => ["(age >= 6 AND age <= 12) AND age NOT LIKE '%months%'"],
        'Children 13-17 years old' => ["(age >= 13 AND age <= 17) AND age NOT LIKE '%months%'"],
        'Adult 18-35 years old' => ["(age >= 18 AND age <= 35) AND age NOT LIKE '%months%'"],
        'Adult 36-50 years old' => ["(age >= 36 AND age <= 50) AND age NOT LIKE '%months%'"],
        'Adult 51-65 years old' => ["(age >= 51 AND age <= 65) AND age NOT LIKE '%months%'"],
        'Adult 66 years old and above' => ["(age >= 66) AND age NOT LIKE '%months%'"]
    ];

    $newResults = [];
    $newTotalFemales = 0;
    $newTotalMales = 0;
    $newTotalAll = 0;

    foreach ($newAgeGroups as $label => $whereArr) {
        $where = $whereArr[0];
        // Count females
        $stmtF = $pdo->prepare("SELECT COUNT(*) FROM people WHERE $where AND sex_name = 'Female'");
        $stmtF->execute();
        $females = $stmtF->fetchColumn();
        // Count males
        $stmtM = $pdo->prepare("SELECT COUNT(*) FROM people WHERE $where AND sex_name = 'Male'");
        $stmtM->execute();
        $males = $stmtM->fetchColumn();
        $total = $females + $males;
        $newResults[] = [
            'age_group' => $label,
            'females' => $females,
            'males' => $males,
            'total' => $total
        ];
        $newTotalFemales += $females;
        $newTotalMales += $males;
        $newTotalAll += $total;
    }

    // Add grand total row
    $newResults[] = [
        'age_group' => 'Total',
        'females' => $newTotalFemales,
        'males' => $newTotalMales,
        'total' => $newTotalAll
    ];
    ?>

    <table id="customAgeGroupTable">
        <caption>DISASTER AGE GROUP CATEGORIES</caption>
        <thead>
            <tr>
                <th>Age Groups</th>
                <th>No. of Females</th>
                <th>No. of Males</th>
                <th>Total Numbers</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($newResults as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['age_group']) ?></td>
                    <td><?= $row['females'] ?></td>
                    <td><?= $row['males'] ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="text-align:center;margin-top:-18px;margin-bottom:12px;">
        <button onclick="printTables()" class="btn" style="padding:10px 22px;font-size:16px;background:#6ca0a3;color:#fff;border:none;border-radius:5px;cursor:pointer;">Print</button>
    </div>
    <script>
    function printTables() {
        var printContents = '';
        var style = `<style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; }
            table { border-collapse: collapse; width: 90%; margin: 30px auto; background: #fff; }
            th, td { border: 0.5px solid #000; padding: 10px 18px; text-align: center; color: #000; }
            th { background: #fff; color: #000; font-size: 17px; border: 0.5px solid #000; }
            tr:last-child { font-weight: bold; background: #f0f0f0; }
            td, th { font-size: 16px; }
            caption { font-size: 22px; margin-bottom: 12px; font-weight: bold; color: #000; }
        </style>`;
        printContents += style;
        printContents += document.getElementById('ageGroupTable').outerHTML;
        printContents += document.getElementById('customAgeGroupTable').outerHTML;
        var win = window.open('', '', 'height=900,width=1200');
        win.document.write('<html><head><title>Print Tables</title></head><body>' + printContents + '</body></html>');
        win.document.close();
        win.focus();
        setTimeout(function() { win.print(); win.close(); }, 500);
    }
    </script>
</body>
</html>
