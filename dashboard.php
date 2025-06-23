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
    <link rel="stylesheet" href="dashboard/dashboard.css">
    <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">

</head>

<body>
    <header style="margin-bottom: 50px;position:relative;">
  <nav style="display: flex;align-items:center;justify-content:space-between;">
    <div class="logo">BARANGAY PAMANLINAN DEMOGRAPHIC RECORDS DASHBOARD</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="add.php">ADD</a></li>
      <li><a href="list.php">LISTS</a></li>
      <li><a href="index.php">LOGOUT</a></li>
    </ul>
  </nav>
</header>
<main style="margin-left:50px;">


    <!-- PUROK POPULATION -->
    <div class="container" >
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
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Purok Name'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of People'
                        }
                    }
                }
            }
        });

        // Sidebar collapse logic
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    </script>
    <?php
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=pamanlinan_db', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Query to get families per purok (unique household_id count per purok)
    $stmtFamilies = $pdo->query("
    SELECT purok_name, COUNT(DISTINCT household_id) as family_count
    FROM people
    WHERE household_id IS NOT NULL AND household_id != ''
    GROUP BY purok_name
    ORDER BY purok_name
");
    $familyData = $stmtFamilies->fetchAll(PDO::FETCH_ASSOC);
    $familyPurokNames = array_column($familyData, 'purok_name');
    $familyCounts = array_column($familyData, 'family_count');
    ?>


    <!-- FAMILY PER PUROK -->
    <div class="container">

        <h2 style="text-align:center;">Families per Purok</h2>
        <canvas id="familyChart" width="600" height="350"></canvas>
        <div id="totalFamilies" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
            <?php
            $totalFamilies = array_sum($familyCounts);
            echo "Total Families: " . $totalFamilies;
            ?>
        </div>
    </div>

    <style>
        #familyChart {
            max-width: 700px;
            max-height: 650px;
            margin: 0 auto;
            display: block;
        }
    </style>
    <script>
        const familyPurokNames = <?php echo json_encode($familyPurokNames); ?>;
        const familyCounts = <?php echo json_encode($familyCounts); ?>;
        const familyCtx = document.getElementById('familyChart').getContext('2d');
        new Chart(familyCtx, {
            type: 'bar',
            data: {
                labels: familyPurokNames,
                datasets: [{
                    label: 'Number of Families',
                    data: familyCounts,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Purok Name'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Families'
                        }
                    }
                }
            }
        });
    </script>
</main>
</body>

</html>