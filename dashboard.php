<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
// Query to get count per purok_name
$stmt = $pdo->query("SELECT purok_name, COUNT(*) as count FROM people GROUP BY purok_name ORDER BY purok_name");
$purokData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$purokNames = array_column($purokData, 'purok_name');
$purokCounts = array_column($purokData, 'count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="add.css" />
    <link rel="stylesheet" href="add2.css" />
    <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f8fb; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); padding: 32px; }
        h2 { text-align: center; color: #057570; }
        canvas { margin: 0 auto; display: block; }
    </style>
</head>
<body>
    <header style="position: relative;">
    <nav class="navbar">
      <div class="logo">Demographic Profiling System form</div>
      <ul class="nav-links">
        <li><a href="add.php">ADD</a></li>
        <li><a href="list.php">LIST</a></li>
        <li><a href="logout.php">LOGOUT</a></li>
      </ul>
    </nav>
  </header>
    <div class="container">
        <h2>Population per Purok</h2>
        <canvas id="purokChart" width="600" height="350"></canvas>
        <div id="totalPopulation" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
            <?php
                $totalPopulation = array_sum($purokCounts);
                echo "Total Population: " . $totalPopulation;
            ?>
        </div>
    </div>
    <script>
        const purokNames = <?php echo json_encode($purokNames); ?>;
        const purokCounts = <?php echo json_encode($purokCounts); ?>;
        const ctx = document.getElementById('purokChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: purokNames,
                datasets: [{
                    label: 'Number of People',
                    data: purokCounts,
                    backgroundColor: 'rgba(6, 182, 212, 0.7)',
                    borderColor: 'rgba(6, 182, 212, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Purok Name' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Number of People' } }
                }
            }
        });
    </script>
</body>
</html>
