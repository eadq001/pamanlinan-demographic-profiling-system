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
    <div class="logo">BPDR DASHBOARD</div>
    <ul class="nav-links" id="navLinks">
      <li><a href="ageGroup.php">AGE GROUP</a></li>
      <li><a href="disabilitiesGroup.php">DISABILITIES</a></li>
      <li><a href="deceased.php">DECEASED</a></li>
      <li><a href="list.php">LISTS</a></li>
      <li><a href="add.php">ADD</a></li>
      <li><a href="logout.php">LOGOUT</a></li>
    </ul>
  </nav>
</header>
<main style="margin-left:30px;">


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

    // Query to get household per purok (unique household_id count per purok)
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


    <!-- HOUSEHOLD PER PUROK -->
    <div class="container">

        <h2 style="text-align:center;">Household per Purok</h2>
        <canvas id="familyChart" width="600" height="350"></canvas>
        <div id="totalFamilies" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
            <?php
            $totalFamilies = array_sum($familyCounts);
            echo "Total Household: " . $totalFamilies;
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
                    label: 'Number of Household',
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

        <?php
        // Query to get families per purok, counting unique family_id per purok
        $stmtFamilyID = $pdo->query("
            SELECT purok_name, COUNT(DISTINCT family_id) as family_id_count
            FROM people
            WHERE family_id IS NOT NULL AND family_id != ''
            GROUP BY purok_name
            ORDER BY purok_name
        ");
        $familyIDData = $stmtFamilyID->fetchAll(PDO::FETCH_ASSOC);
        $familyIDPurokNames = array_column($familyIDData, 'purok_name');
        $familyIDCounts = array_column($familyIDData, 'family_id_count');
        ?>

        <!-- FAMILIES PER PUROK (by family_id) -->
        <div class="container">
            <h2 style="text-align:center;">Families per Purok</h2>
            <canvas id="familyIDChart" width="600" height="350"></canvas>
            <div id="totalFamilyID" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
                <?php
                $totalFamilyID = array_sum($familyIDCounts);
                echo "Total Families: $totalFamilyID";
                ?>
            </div>
        </div>

        <script>
            const familyIDPurokNames = <?php echo json_encode($familyIDPurokNames); ?>;
            const familyIDCounts = <?php echo json_encode($familyIDCounts); ?>;
            const familyIDCtx = document.getElementById('familyIDChart').getContext('2d');
            new Chart(familyIDCtx, {
                type: 'bar',
                data: {
                    labels: familyIDPurokNames,
                    datasets: [{
                        label: 'Number of Families',
                        data: familyIDCounts,
                        backgroundColor: 'rgba(255, 87, 34, 0.7)',
                        borderColor: 'rgba(255, 87, 34, 1)',
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


    <?php
    // Query to get toilets per purok, counting only unique household_id with toilet = 'Yes'
    $stmtToilets = $pdo->query("
        SELECT purok_name, COUNT(DISTINCT household_id) AS yes_count
        FROM people
        WHERE toilet = 'Yes' AND household_id IS NOT NULL AND household_id != ''
        GROUP BY purok_name
        ORDER BY purok_name
    ");
    $toiletData = $stmtToilets->fetchAll(PDO::FETCH_ASSOC);
    $toiletPurokNames = array_column($toiletData, 'purok_name');
    $toiletYesCounts = array_column($toiletData, 'yes_count');
    ?>

    <!-- TOILETS PER PUROK -->
    <div class="container">
        <h2 style="text-align:center;">Households with Toilet per Purok</h2>
        <canvas id="toiletChart" width="600" height="350"></canvas>
        <div id="totalToilets" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
            <?php
            $totalYes = array_sum($toiletYesCounts);
            echo "Total Households with Toilet: $totalYes";
            ?>
        </div>
    </div>

    <script>
        const toiletPurokNames = <?php echo json_encode($toiletPurokNames); ?>;
        const toiletYesCounts = <?php echo json_encode($toiletYesCounts); ?>;
        const toiletCtx = document.getElementById('toiletChart').getContext('2d');
        new Chart(toiletCtx, {
            type: 'bar',
            data: {
                labels: toiletPurokNames,
                datasets: [
                    {
                        label: 'Households with Toilet',
                        data: toiletYesCounts,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                    }
                ]
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
                            text: 'Number of Households'
                        }
                    }
                }
            }
        });
    </script>



    <?php
    // Query to get gender count per purok
    $stmtGenders = $pdo->query("
        SELECT purok_name,
            SUM(CASE WHEN sex_name = 'Male' THEN 1 ELSE 0 END) AS male_count,
            SUM(CASE WHEN sex_name = 'Female' THEN 1 ELSE 0 END) AS female_count
        FROM people
        GROUP BY purok_name
        ORDER BY purok_name
    ");
    $genderData = $stmtGenders->fetchAll(PDO::FETCH_ASSOC);
    $genderPurokNames = array_column($genderData, 'purok_name');
    $maleCounts = array_column($genderData, 'male_count');
    $femaleCounts = array_column($genderData, 'female_count');
    ?>

    <!-- GENDERS PER PUROK -->
    <div class="container">
        <h2 style="text-align:center;">Genders</h2>
        <canvas id="genderChart" width="600" height="350"></canvas>
        <div id="totalGenders" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
            <?php
            $totalMales = array_sum($maleCounts);
            $totalFemales = array_sum($femaleCounts);
            echo "Total Males: $totalMales | Total Females: $totalFemales";
            ?>
        </div>
    </div>

    <script>
        const genderPurokNames = <?php echo json_encode($genderPurokNames); ?>;
        const maleCounts = <?php echo json_encode($maleCounts); ?>;
        const femaleCounts = <?php echo json_encode($femaleCounts); ?>;
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'bar',
            data: {
                labels: genderPurokNames,
                datasets: [
                    {
                        label: 'Male',
                        data: maleCounts,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                    },
                    {
                        label: 'Female',
                        data: femaleCounts,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
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
    </script>

    <?php
    // Query to get out-of-school youth count per purok
    $stmtOSY = $pdo->query("
        SELECT purok_name, COUNT(*) as osy_count
        FROM people
        WHERE school_youth = 'Yes'
        GROUP BY purok_name
        ORDER BY purok_name
    ");
    $osyData = $stmtOSY->fetchAll(PDO::FETCH_ASSOC);
    $osyPurokNames = array_column($osyData, 'purok_name');
    $osyCounts = array_column($osyData, 'osy_count');
    ?>

    <!-- OUT-OF-SCHOOL YOUTH PER PUROK -->
    <div class="container">
        <h2 style="text-align:center;">Out-of-School Youth per Purok</h2>
        <canvas id="osyChart" width="600" height="350"></canvas>
        <div id="totalOSY" style="text-align:center;margin-top:18px;font-size:1.2em;color:#057570;font-weight:bold;">
            <?php
            $totalOSY = array_sum($osyCounts);
            echo "Total Out-of-School Youth: $totalOSY";
            ?>
        </div>
    </div>

    <script>
        const osyPurokNames = <?php echo json_encode($osyPurokNames); ?>;
        const osyCounts = <?php echo json_encode($osyCounts); ?>;
        const osyCtx = document.getElementById('osyChart').getContext('2d');
        new Chart(osyCtx, {
            type: 'bar',
            data: {
                labels: osyPurokNames,
                datasets: [{
                    label: 'Out-of-School Youth',
                    data: osyCounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.7)',
                    borderColor: 'rgba(153, 102, 255, 1)',
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
                            text: 'Number of Out-of-School Youth'
                        }
                    }
                }
            }
        });
    </script>
</main>

<!-- Export Charts Section -->
<div style="margin:40px 0;text-align:center;">
    <h3>Export Chart Data to Excel</h3>
    <form id="exportForm" style="display:inline-block;margin-top:0;">
        <select id="chartSelect" name="chartSelect" style="padding:6px 12px;font-size:1em;">
            <option value="all">All Charts</option>
            <option value="purok">Population per Purok</option>
            <option value="household">Household per Purok</option>
            <option value="families">Families per Purok</option>
            <option value="toilet">Households with Toilet per Purok</option>
            <option value="gender">Genders per Purok</option>
            <option value="osy">Out-of-School Youth per Purok</option>
        </select>
        <button type="button" id="exportBtn" style="margin-top:10px;padding:6px 18px;font-size:1em;background:#057570;color:#fff;border:none;border-radius:4px;cursor:pointer;">Export to Excel</button>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function exportToExcel(sheetData, sheetName, fileName) {
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(sheetData);

        // Autofit columns
        const colWidths = sheetData[0].map((_, colIdx) => {
            let maxLen = 0;
            sheetData.forEach(row => {
                const cell = row[colIdx];
                const len = cell ? cell.toString().length : 0;
                if (len > maxLen) maxLen = len;
            });
            return { wch: Math.max(maxLen + 2, 12) };
        });
        ws['!cols'] = colWidths;

        // Center all cells and format header
        for (let R = 0; R < sheetData.length; ++R) {
            for (let C = 0; C < sheetData[0].length; ++C) {
                const cellRef = XLSX.utils.encode_cell({r:R, c:C});
                if (!ws[cellRef]) continue;
                ws[cellRef].s = ws[cellRef].s || {};
                ws[cellRef].s.alignment = { horizontal: "center", vertical: "center" };
                if (R === 0) {
                    ws[cellRef].s.font = { bold: true, color: { rgb: "FFFFFF" } };
                    ws[cellRef].s.fill = { fgColor: { rgb: "057570" } };
                }
            }
        }

        wb.SheetNames.push(sheetName);
        wb.Sheets[sheetName] = ws;
        XLSX.writeFile(wb, fileName);
    }

    function getChartData(chartKey) {
        switch(chartKey) {
            case 'purok':
                return [
                    ['Purok Name', 'Population'],
                    ...purokNames.map((name, i) => [name, purokCounts[i]])
                ];
            case 'household':
                return [
                    ['Purok Name', 'Household Count'],
                    ...familyPurokNames.map((name, i) => [name, familyCounts[i]])
                ];
            case 'families':
                return [
                    ['Purok Name', 'Families Count'],
                    ...familyIDPurokNames.map((name, i) => [name, familyIDCounts[i]])
                ];
            case 'toilet':
                return [
                    ['Purok Name', 'Households with Toilet'],
                    ...toiletPurokNames.map((name, i) => [name, toiletYesCounts[i]])
                ];
            case 'gender':
                return [
                    ['Purok Name', 'Male', 'Female'],
                    ...genderPurokNames.map((name, i) => [name, maleCounts[i], femaleCounts[i]])
                ];
            case 'osy':
                return [
                    ['Purok Name', 'Out-of-School Youth'],
                    ...osyPurokNames.map((name, i) => [name, osyCounts[i]])
                ];
            default:
                return null;
        }
    }

    document.getElementById('exportBtn').addEventListener('click', function() {
        const selected = document.getElementById('chartSelect').value;
        if (selected === 'all') {
            const wb = XLSX.utils.book_new();
            const charts = [
                {key:'purok', name:'Population'},
                {key:'household', name:'Household'},
                {key:'families', name:'Families'},
                {key:'toilet', name:'Toilet'},
                {key:'gender', name:'Gender'},
                {key:'osy', name:'OSY'}
            ];
            charts.forEach(chart => {
                const data = getChartData(chart.key);
                if (data) {
                    const ws = XLSX.utils.aoa_to_sheet(data);

                    // Autofit columns
                    const colWidths = data[0].map((_, colIdx) => {
                        let maxLen = 0;
                        data.forEach(row => {
                            const cell = row[colIdx];
                            const len = cell ? cell.toString().length : 0;
                            if (len > maxLen) maxLen = len;
                        });
                        return { wch: Math.max(maxLen + 2, 12) };
                    });
                    ws['!cols'] = colWidths;

                    // Center all cells and format header
                    for (let R = 0; R < data.length; ++R) {
                        for (let C = 0; C < data[0].length; ++C) {
                            const cellRef = XLSX.utils.encode_cell({r:R, c:C});
                            if (!ws[cellRef]) continue;
                            ws[cellRef].s = ws[cellRef].s || {};
                            ws[cellRef].s.alignment = { horizontal: "center", vertical: "center" };
                            if (R === 0) {
                                ws[cellRef].s.font = { bold: true, color: { rgb: "FFFFFF" } };
                                ws[cellRef].s.fill = { fgColor: { rgb: "057570" } };
                            }
                        }
                    }

                    wb.SheetNames.push(chart.name);
                    wb.Sheets[chart.name] = ws;
                }
            });
            XLSX.writeFile(wb, 'dashboard_charts.xlsx');
        } else {
            const data = getChartData(selected);
            let sheetName = '';
            let fileName = '';
            switch(selected) {
                case 'purok': sheetName='Population'; fileName='population_per_purok.xlsx'; break;
                case 'household': sheetName='Household'; fileName='household_per_purok.xlsx'; break;
                case 'families': sheetName='Families'; fileName='families_per_purok.xlsx'; break;
                case 'toilet': sheetName='Toilet'; fileName='toilet_per_purok.xlsx'; break;
                case 'gender': sheetName='Gender'; fileName='gender_per_purok.xlsx'; break;
                case 'osy': sheetName='OSY'; fileName='osy_per_purok.xlsx'; break;
            }
            exportToExcel(data, sheetName, fileName);
        }
    });
</script>
</script>
</body>

</html>