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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchResults = [];

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM people WHERE CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle add-to-deceased action
if (isset($_POST['add_deceased_id']) && isset($_POST['cause_of_death']) && isset($_POST['date_of_death'])) {
    $personId = intval($_POST['add_deceased_id']);
    $causeOfDeath = trim($_POST['cause_of_death']);
    // Convert mm/dd/yyyy to Y-m-d
    $dateOfDeath = trim($_POST['date_of_death']);
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateOfDeath)) {
        $parts = explode('/', $dateOfDeath);
        $dateOfDeath = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
    }
    // Get person data
    $stmt = $pdo->prepare("SELECT * FROM people WHERE id = ?");
    $stmt->execute([$personId]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($person) {
        // Insert into deceased table (assumes same columns as people plus cause_of_death and date_of_death)
        $columns = array_keys($person);
        $columns[] = 'cause_of_death';
        $columns[] = 'date_of_death';
        $colList = implode(',', $columns);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $values = array_values($person);
        $values[] = $causeOfDeath;
        $values[] = $dateOfDeath;
        $insert = $pdo->prepare("INSERT INTO deceased ($colList) VALUES ($placeholders)");
        $insert->execute($values);
        // Delete from people table
        $del = $pdo->prepare("DELETE FROM people WHERE id = ?");
        $del->execute([$personId]);
        echo '<script>alert("Person moved to deceased.");window.location.href=window.location.pathname;</script>';
        exit;
    }
}

// Fetch all deceased people for display
$deceasedPeople = $pdo->query("SELECT * FROM deceased ORDER BY last_name, first_name, middle_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <link rel="stylesheet" href="nav.css">
    <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
    <title>Deceased Management</title>
    <style>
        body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(to right, #6ca0a3, #ffffff); }
        .container { margin: 40px 20px; background: #fff; padding: 30px 30px 20px 30px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        h2 { text-align: center; margin-bottom: 24px; }
        form { display: flex; justify-content: center; margin-bottom: 24px; }
        input[type="text"] { padding: 8px 14px; border: 1px solid #bbb; border-radius: 4px; min-width: 260px; font-size: 16px; }
        button, input[type="submit"] { padding: 7px 18px; background: #6ca0a3; color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; margin-left: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #bbb; padding: 8px 12px; text-align: left; }
        th { background: #e0e0e0; }
        tr:nth-child(even) { background: #f7f7f7; }
      
    </style>
</head>
<body>
<header style="margin-bottom: 50px;position:relative;">
  <nav >
    <div class="logo">BARANGAY PAMANLINAN DEMOGRAPHIC RECORDS</div>
    <ul class="nav-links" id="navLinks">
         <li><a href="dashboard.php">DASHBOARD</a></li>
      <li><a href="ageGroup.php">AGE GROUP</a></li>
      <li><a href="disabilitiesGroup.php">DISABILITIES</a></li>
      <li><a href="list.php">LISTS</a></li>
      <li><a href="add.php">ADD</a></li>
      <li><a href="logout.php">LOGOUT</a></li>
    </ul>
  </nav>
</header>
<div class="container">
    <h2 style="text-transform:uppercase;">Deceased Management</h2>
    <form method="get" action="">
        <input type="text" name="search" placeholder="Search by Last, First, or Middle Name" value="<?= htmlspecialchars($search) ?>" autofocus />
        <button type="submit">Search</button>
    </form>
    <?php if ($search !== ''): ?>
        <table>
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Sex</th>
                    <th>Birth Date</th>
                    <th>Purok</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($searchResults as $person): ?>
                <tr>
                    <td><?= htmlspecialchars($person['last_name']) ?></td>
                    <td><?= htmlspecialchars($person['first_name']) ?></td>
                    <td><?= htmlspecialchars($person['middle_name']) ?></td>
                    <td><?= htmlspecialchars($person['sex_name']) ?></td>
                    <td><?= htmlspecialchars($person['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($person['purok_name']) ?></td>
                    <td>
                        <button type="button" class="add-deceased-btn" data-id="<?= $person['id'] ?>">Add to Deceased</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div id="causeOfDeathModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;">
            <div style="background:#fff;max-width:400px;width:90vw;max-height:80vh;overflow:auto;position:relative;margin:60px auto;padding:18px 36px 24px 18px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.2);">
                <form id="causeOfDeathForm" method="post" style="flex-direction: column;">
                    <input type="hidden" name="add_deceased_id" id="deceasedPersonId" value="" />
                    <label for="cause_of_death" style="font-weight:bold;">Cause of Death:</label>
                    <input type="text" name="cause_of_death" id="causeOfDeathInput" required style="width:100%;padding:8px 10px;margin:12px 0 18px 0;border:1px solid #bbb;border-radius:4px;" />
                    <label for="date_of_death" style="font-weight:bold;">Date of Death:</label>
                    <input type="text" name="date_of_death" id="dateOfDeathInput" required placeholder="mm/dd/yyyy" pattern="\d{2}/\d{2}/\d{4}" style="width:100%;padding:8px 10px;margin:12px 0 18px 0;border:1px solid #bbb;border-radius:4px;" />
                    <div>
                        <button type="button" id="cancelModalBtn" style="background:#bbb;color:#fff;margin-right:10px;">Cancel</button>
                        <input type="submit" value="Confirm & Add" style="background:#6ca0a3;color:#fff;" />
                    </div>
                </form>
            </div>
        </div>
        <script>
        document.querySelectorAll('.add-deceased-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('deceasedPersonId').value = this.getAttribute('data-id');
                document.getElementById('causeOfDeathModal').style.display = 'block';
                document.getElementById('causeOfDeathInput').value = '';
                document.getElementById('dateOfDeathInput').value = '';
                document.getElementById('causeOfDeathInput').focus();
            });
        });
        document.getElementById('cancelModalBtn').onclick = function() {
            document.getElementById('causeOfDeathModal').style.display = 'none';
        };
        // Prevent form submission if cause or date is empty
        document.getElementById('causeOfDeathForm').onsubmit = function() {
            const cause = document.getElementById('causeOfDeathInput').value.trim();
            const date = document.getElementById('dateOfDeathInput').value.trim();
            if (cause === '' || date === '') {
                alert('Please enter the cause of death and date of death.');
                return false;
            }
            if (!/^\d{2}\/\d{2}\/\d{4}$/.test(date)) {
                alert('Date of death must be in mm/dd/yyyy format.');
                return false;
            }
        };
        </script>
        <?php if (empty($searchResults)): ?>
            <div style="text-align:center;color:#a00;margin-top:18px;">No results found.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($deceasedPeople)): ?>
        <h3 style="margin-top:40px;text-align:center;">List of Deceased People</h3>
        <table id="deceasedTable">
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Sex</th>
                    <th>Birth Date</th>
                    <th>Purok</th>
                    <th>Cause of Death</th>
                    <th>Date of Death</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($deceasedPeople as $person): ?>
                <tr class="deceased-row" data-person='<?= json_encode($person, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <td><?= htmlspecialchars($person['last_name']) ?></td>
                    <td><?= htmlspecialchars($person['first_name']) ?></td>
                    <td><?= htmlspecialchars($person['middle_name']) ?></td>
                    <td><?= htmlspecialchars($person['sex_name']) ?></td>
                    <td><?= htmlspecialchars($person['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($person['purok_name']) ?></td>
                    <td>
                        <span class="cause-of-death-text"><?= htmlspecialchars($person['cause_of_death']) ?></span>
                        <button type="button" class="edit-cause-btn" style="margin-left:8px;padding:2px 8px;font-size:13px;">Edit</button>
                        <form class="edit-cause-form" method="post" style="display:none;margin:0;">
                            <input type="hidden" name="edit_deceased_id" value="<?= $person['id'] ?>" />
                            <input type="text" name="new_cause_of_death" value="<?= htmlspecialchars($person['cause_of_death']) ?>" style="width:120px;padding:2px 6px;font-size:13px;" required />
                            <button type="submit" style="padding:2px 8px;font-size:13px;">Save</button>
                            <button type="button" class="cancel-edit-btn" style="padding:2px 8px;font-size:13px;">Cancel</button>
                        </form>
                    </td>
                    <td>
                        <span class="date-of-death-text">
        <?= !empty($person['date_of_death']) ? date('m/d/Y', strtotime($person['date_of_death'])) : '' ?>
    </span>
    <button type="button" class="edit-date-btn" style="margin-left:8px;padding:2px 8px;font-size:13px;">Edit</button>
    <form class="edit-date-form" method="post" style="display:none;margin:0;">
        <input type="hidden" name="edit_date_deceased_id" value="<?= $person['id'] ?>" />
        <input type="text" name="new_date_of_death" value="<?= !empty($person['date_of_death']) ? date('m/d/Y', strtotime($person['date_of_death'])) : '' ?>" placeholder="mm/dd/yyyy" pattern="\d{2}/\d{2}/\d{4}" style="width:140px;padding:2px 6px;font-size:13px;" required />
        <button type="submit" style="padding:2px 8px;font-size:13px;">Save</button>
        <button type="button" class="cancel-edit-date-btn" style="padding:2px 8px;font-size:13px;">Cancel</button>
    </form>
                    </td>
                    <?php
                    // Handle edit cause of death
                    if (isset($_POST['edit_deceased_id']) && isset($_POST['new_cause_of_death'])) {
                        $editId = intval($_POST['edit_deceased_id']);
                        $newCause = trim($_POST['new_cause_of_death']);
                        $stmt = $pdo->prepare("UPDATE deceased SET cause_of_death = ? WHERE id = ?");
                        $stmt->execute([$newCause, $editId]);
                        echo '<script>window.location.href=window.location.pathname;</script>';
                        exit;
                    }
                    // Handle edit date of death
                    if (isset($_POST['edit_date_deceased_id']) && isset($_POST['new_date_of_death'])) {
                        $editId = intval($_POST['edit_date_deceased_id']);
                        $newDate = trim($_POST['new_date_of_death']);
                        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $newDate)) {
                            $parts = explode('/', $newDate);
                            $newDate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                        }
                        $stmt = $pdo->prepare("UPDATE deceased SET date_of_death = ? WHERE id = ?");
                        $stmt->execute([$newDate, $editId]);
                        echo '<script>window.location.href=window.location.pathname;</script>';
                        exit;
                    }
                    ?>
                    <script>
                    // Cause of death edit
                    document.querySelectorAll('.edit-cause-btn').forEach(function(btn) {
                        btn.onclick = function(e) {
                            e.stopPropagation();
                            const td = btn.closest('td');
                            td.querySelector('.cause-of-death-text').style.display = 'none';
                            btn.style.display = 'none';
                            td.querySelector('.edit-cause-form').style.display = 'inline-block';
                            td.querySelector('input[name="new_cause_of_death"]').focus();
                        };
                    });
                    document.querySelectorAll('.cancel-edit-btn').forEach(function(btn) {
                        btn.onclick = function(e) {
                            e.stopPropagation();
                            const td = btn.closest('td');
                            td.querySelector('.cause-of-death-text').style.display = '';
                            td.querySelector('.edit-cause-btn').style.display = '';
                            td.querySelector('.edit-cause-form').style.display = 'none';
                        };
                    });
                    document.querySelectorAll('.edit-cause-form').forEach(function(form) {
                        form.onsubmit = function(e) { e.stopPropagation(); };
                        form.addEventListener('click', function(e) { e.stopPropagation(); });
                    });
                    // Date of death edit
                    document.querySelectorAll('.edit-date-btn').forEach(function(btn) {
                        btn.onclick = function(e) {
                            e.stopPropagation();
                            const td = btn.closest('td');
                            td.querySelector('.date-of-death-text').style.display = 'none';
                            btn.style.display = 'none';
                            td.querySelector('.edit-date-form').style.display = 'inline-block';
                            td.querySelector('input[name="new_date_of_death"]').focus();
                        };
                    });
                    document.querySelectorAll('.cancel-edit-date-btn').forEach(function(btn) {
                        btn.onclick = function(e) {
                            e.stopPropagation();
                            const td = btn.closest('td');
                            td.querySelector('.date-of-death-text').style.display = '';
                            td.querySelector('.edit-date-btn').style.display = '';
                            td.querySelector('.edit-date-form').style.display = 'none';
                        };
                    });
                    document.querySelectorAll('.edit-date-form').forEach(function(form) {
                        form.onsubmit = function(e) {
        const dateInput = form.querySelector('input[name="new_date_of_death"]');
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dateInput.value.trim())) {
            alert('Date of death must be in mm/dd/yyyy format.');
            e.preventDefault();
            return false;
        }
        e.stopPropagation();
    };
    form.addEventListener('click', function(e) { e.stopPropagation(); });
});
                    </script>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div id="deceasedPopupOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;">
            <div id="deceasedPopupBox" style="background:#fff;max-width:500px;width:90vw;max-height:80vh;overflow:auto;position:relative;margin:60px auto;padding:12px 12px 16px 12px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.2);font-size:13px;">
                <button id="deceasedPopupCloseBtn" style="position:absolute;top:8px;right:12px;background:transparent;border:none;font-size:20px;cursor:pointer;">&times;</button>
                <div id="deceasedPopupContent"></div>
            </div>
        </div>
        <script>
        const deceasedRows = document.querySelectorAll('.deceased-row');
        const deceasedPopupOverlay = document.getElementById('deceasedPopupOverlay');
        const deceasedPopupContent = document.getElementById('deceasedPopupContent');
        const deceasedPopupCloseBtn = document.getElementById('deceasedPopupCloseBtn');
        deceasedRows.forEach(row => {
            row.addEventListener('click', function() {
                const person = JSON.parse(this.getAttribute('data-person'));
                let html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                for (const key in person) {
                    let value = person[key] ?? '';
                    if (key === 'date_of_death' && value) {
                        // Format date as MM-DD-YYYY
                        const d = new Date(value);
                        if (!isNaN(d)) {
                            value = ('0'+(d.getMonth()+1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2) + '-' + d.getFullYear();
                        }
                    }
                    html += `<tr><td style="font-weight:bold;padding:5px 7px;border-bottom:1px solid #eee;">${key.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase())}</td><td style="padding:5px 7px;border-bottom:1px solid #eee;">${value}</td></tr>`;
                }
                html += '</table>';
                deceasedPopupContent.innerHTML = html;
                deceasedPopupOverlay.style.display = 'block';
            });
            row.style.cursor = 'pointer';
        });
        deceasedPopupCloseBtn.addEventListener('click', function() {
            deceasedPopupOverlay.style.display = 'none';
        });
        deceasedPopupOverlay.addEventListener('click', function(e) {
            if (e.target === deceasedPopupOverlay) {
                deceasedPopupOverlay.style.display = 'none';
            }
        });
        document.getElementById('deceasedPopupBox').addEventListener('click', function(e) {
            e.stopPropagation();
        });
        document.addEventListener('keydown', function(e) {
            if (deceasedPopupOverlay.style.display === 'block' && (e.key === 'Escape' || e.key === 'Esc')) {
                deceasedPopupOverlay.style.display = 'none';
            }
        });

        </script>
    <?php endif; ?>
</div>
</body>
</html>
