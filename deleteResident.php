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

// Store deleted residents in session
if (!isset($_SESSION['deleted_residents'])) {
    $_SESSION['deleted_residents'] = [];
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchResults = [];
$message = '';

// Handle deletion
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    // Get resident details before deletion
    $stmt = $pdo->prepare("SELECT last_name, first_name, middle_name, sex_name, date_of_birth, purok_name FROM people WHERE id = ?");
    $stmt->execute([$delete_id]);
    $deletedResident = $stmt->fetch(PDO::FETCH_ASSOC);
    // Delete resident
    $del = $pdo->prepare("DELETE FROM people WHERE id = ?");
    if ($del->execute([$delete_id]) && $deletedResident) {
        $deletedResident['deleted_time'] = date('Y-m-d H:i:s');
        $_SESSION['deleted_residents'][] = $deletedResident;
        $message = "Resident deleted successfully.";
    } else {
        $message = "Error deleting resident.";
    }
}

// Handle search
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT id, last_name, first_name, middle_name, sex_name, date_of_birth, purok_name FROM people WHERE CONCAT(last_name, ' ', first_name, ' ', middle_name) LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
      <link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">

    <title>Delete Resident</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(to right, #6ca0a3, #ffffff); }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px 30px 20px 30px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        h2 { text-align: center; margin-bottom: 24px; }
        form { display: flex; justify-content: center; margin-bottom: 24px; }
        input[type="text"] { padding: 8px 14px; border: 1px solid #bbb; border-radius: 4px; min-width: 260px; font-size: 16px; }
        button, input[type="submit"] { padding: 7px 18px; background: #c00; color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; margin-left: 8px; }
        button:hover, input[type="submit"]:hover { background: #a00; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #bbb; padding: 8px 12px; text-align: left; }
        th { background: #e0e0e0; }
        tr:nth-child(even) { background: #f7f7f7; }
        .message { text-align: center; margin-bottom: 10px; color: #c00; }
        .success { color: #057570; }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this resident?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Delete Resident</h2>
    <?php if ($message): ?>
        <div class="message<?= $message === "Resident deleted successfully." ? ' success' : '' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <form method="get" autocomplete="off">
        <input type="text" name="search" placeholder="Search by Last, First, or Middle Name" value="<?= htmlspecialchars($search) ?>" autofocus />
        <button type="submit">Search</button>
    </form>
    <?php if ($search !== ''): ?>
        <form method="post" id="deleteForm">
            <input type="hidden" name="delete_id" id="delete_id" value="">
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
                            <button type="button" onclick="confirmDelete(<?= $person['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <?php if (empty($searchResults)): ?>
            <div style="text-align:center;color:#a00;margin-top:18px;">No results found.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['deleted_residents'])): ?>
        <h3 style="margin-top:40px;text-align:center;">Deleted Residents</h3>
        <table>
            <thead>
                <tr>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Sex</th>
                    <th>Birth Date</th>
                    <th>Purok</th>
                    <th>Time of Removal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['deleted_residents'] as $person): ?>
                <tr>
                    <td><?= htmlspecialchars($person['last_name']) ?></td>
                    <td><?= htmlspecialchars($person['first_name']) ?></td>
                    <td><?= htmlspecialchars($person['middle_name']) ?></td>
                    <td><?= htmlspecialchars($person['sex_name']) ?></td>
                    <td><?= htmlspecialchars($person['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($person['purok_name']) ?></td>
                    <td><?= htmlspecialchars($person['deleted_time']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
