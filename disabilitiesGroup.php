<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$disabilities = [
    'DEAF',
    'MUTE',
    'BLIND',
    'INTELLECTUAL DISABILITY',
    'AUTISM',
    'PHYSICAL DISABILITY',
    'DISABILITY WALKING OR MOVEMENT',
    'HEALTH-RELATED DISABILITY (ILLNESS)'
];

// Fetch all people with a disability
$stmt = $pdo->query("SELECT age, sex_name, pwd FROM people WHERE pwd IS NOT NULL AND pwd != ''");
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to get age group
function get_age_group($age) {
    if (stripos($age, 'months') !== false) return 'child';
    $ageInt = intval($age);
    if ($ageInt <= 17) return 'child';
    if ($ageInt >= 18 && $ageInt <= 59) return 'adult';
    if ($ageInt >= 60) return 'elderly';
    return null;
}

// Initialize counts
$table = [];
foreach ($disabilities as $dis) {
    $table[$dis] = [
        'child' => ['male' => 0, 'female' => 0],
        'adult' => ['male' => 0, 'female' => 0],
        'elderly' => ['male' => 0, 'female' => 0],
        'total' => ['male' => 0, 'female' => 0]
    ];
}
$table['OTHER DISABILITIES'] = [
    'child' => ['male' => 0, 'female' => 0],
    'adult' => ['male' => 0, 'female' => 0],
    'elderly' => ['male' => 0, 'female' => 0],
    'total' => ['male' => 0, 'female' => 0]
];

foreach ($people as $person) {
    $pwd = strtoupper(trim($person['pwd']));
    $sex = strtolower(trim($person['sex_name']));
    $ageGroup = get_age_group($person['age']);
    if (!$ageGroup || ($sex !== 'male' && $sex !== 'female')) continue;
    $matched = false;
    foreach ($disabilities as $dis) {
        if (strpos($pwd, $dis) !== false) {
            $table[$dis][$ageGroup][$sex]++;
            $table[$dis]['total'][$sex]++;
            $matched = true;
            break;
        }
    }
    // Only count as 'OTHER DISABILITIES' if not matched and pwd is not 'NO' or empty
    if (!$matched && $pwd !== '' && $pwd !== 'NO') {
        $table['OTHER DISABILITIES'][$ageGroup][$sex]++;
        $table['OTHER DISABILITIES']['total'][$sex]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="nav.css">
      <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">

    <title>Disabilities Group Table</title>
    <style>
        body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
       h2 {text-align:center;}
        table { border-collapse: collapse; width: 70%; margin:0 auto;background:rgb(253, 253, 253);}
        th, td { border: 1px solid #888; padding: 6px 10px; text-align: center; }
        th {
    background: rgba(79, 223, 239, 0.4); }
        .kinds { text-align: left; font-weight: bold; }
        .kinds_of_disability {width:300px;}
    </style>
</head>
<header style="margin-bottom: 50px;position:relative;">
  <nav >
    <div class="logo">BARANGAY PAMANLINAN DEMOGRAPHIC RECORDS</div>
    <ul class="nav-links" id="navLinks">
        <li><a href="dashboard.php">DASHBOARD</a></li>
      <li><a href="ageGroup.php">AGE GROUP</a></li>
      <li><a href="deceased.php">DECEASED</a></li>
      <li><a href="list.php">LISTS</a></li>
      <li><a href="add.php">ADD</a></li>
      <li><a href="logout.php">LOGOUT</a></li>
    </ul>
  </nav>
</header>
<body>
    <h2>Disabilities Group Table</h2>
    <table>
    <tr>
        <th rowspan="2" class="kinds_of_disability">TYPES OF DISABILITY</th>
        <th colspan="2">CHILD<br>(17 YRS OLD AND BELOW)</th>
        <th colspan="2">ADULT<br>(18-59 YRS OLD)</th>
        <th colspan="2">ELDERLY<br>(60 AND ABOVE)</th>
        <th colspan="2">TOTAL NUMBER</th>
    </tr>
    <tr>
        <th>MALE</th><th>FEMALE</th>
        <th>MALE</th><th>FEMALE</th>
        <th>MALE</th><th>FEMALE</th>
        <th>MALE</th><th>FEMALE</th>
    </tr>
    <?php foreach (array_merge($disabilities, ['OTHER DISABILITIES']) as $dis): ?>
    <tr>
        <td class="kinds"><?= htmlspecialchars($dis) ?></td>
        <td><?= $table[$dis]['child']['male'] ?></td>
        <td><?= $table[$dis]['child']['female'] ?></td>
        <td><?= $table[$dis]['adult']['male'] ?></td>
        <td><?= $table[$dis]['adult']['female'] ?></td>
        <td><?= $table[$dis]['elderly']['male'] ?></td>
        <td><?= $table[$dis]['elderly']['female'] ?></td>
        <td><?= $table[$dis]['total']['male'] ?></td>
        <td><?= $table[$dis]['total']['female'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<br>
<div style="text-align:center;">
    <button onclick="window.print()" style="padding:8px 18px;font-size:16px;cursor:pointer;">Print Table</button>
</div>
<style>
@media print {
    body, table, th, td {
        color: #000 !important;
        background: #fff !important;
    }
    th, td {
        border: 0.5px solid #000 !important;
    }
    .logo, .nav-links, header, button {
        display: none !important;
    }
    table {
        width: 100% !important;
        margin: 0 !important;
    }
}
</style>
</body>
</html>
