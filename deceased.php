<?php
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
if (isset($_POST['add_deceased_id'])) {
    $personId = intval($_POST['add_deceased_id']);
    // Get person data
    $stmt = $pdo->prepare("SELECT * FROM people WHERE id = ?");
    $stmt->execute([$personId]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($person) {
        // Insert into deceased table (assumes same columns as people)
        $columns = array_keys($person);
        $colList = implode(',', $columns);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $values = array_values($person);
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
    <title>Deceased Management</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f8f8; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px 30px 20px 30px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
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
<div class="container">
    <h2>Deceased Management</h2>
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
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="add_deceased_id" value="<?= $person['id'] ?>" />
                            <input type="submit" value="Add to Deceased" onclick="return confirm('Are you sure you want to move this person to deceased?');" />
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($searchResults)): ?>
            <div style="text-align:center;color:#a00;margin-top:18px;">No results found.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($deceasedPeople)): ?>
        <h3 style="margin-top:40px;text-align:center;">List of Deceased People</h3>
        <table>
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Sex</th>
                    <th>Birth Date</th>
                    <th>Purok</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($deceasedPeople as $person): ?>
                <tr>
                    <td><?= htmlspecialchars($person['last_name']) ?></td>
                    <td><?= htmlspecialchars($person['first_name']) ?></td>
                    <td><?= htmlspecialchars($person['middle_name']) ?></td>
                    <td><?= htmlspecialchars($person['sex_name']) ?></td>
                    <td><?= htmlspecialchars($person['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($person['purok_name']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
