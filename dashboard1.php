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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Families per Purok</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="new">
    <h2 style="text-align:center;">Families per Purok</h2>
    <canvas id="familyChart" width="300" height="350"></canvas>
    <div id="totalFamilies" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
        <?php
            $totalFamilies = array_sum($familyCounts);
            echo "Total Families: " . $totalFamilies;
        ?>
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
                    legend: { display: false },
                    title: { display: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Purok Name' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Number of Families' } }
                }
            }
        });
    </script>
    </div>
</body>
</html>
