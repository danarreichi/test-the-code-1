<?php
include 'action.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>

<body>
    <form method="post">
        <input type="text" name="name" id="name" required>
        <input type="number" name="qty" id="qty" required>
        <input type="submit" name="addQueue" value="Tambahkan Antrian">
    </form>
    <hr>
    <form method="post">
        <input type="submit" name="callNext" value="Tempatkan">
    </form>
    <br>
    <form method="post">
        <input type="submit" name="checkQueue" value="Check Antrian">
    </form>

    <?php
    $restaurantQueue = new RestaurantQueue();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['addQueue'])) {
        $restaurantQueue->addToQueue($_REQUEST["name"], $_REQUEST["qty"]);
        $tables = $restaurantQueue->viewQueue();
        showQueue($tables);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['checkQueue'])) {
        $tables = $restaurantQueue->viewQueue();
        showQueue($tables);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['callNext'])) {
        $tables = $restaurantQueue->callNext();
        showQueue($tables);
    }

    function showQueue($data)
    {
        echo "<div style=\"margin-top:16px;\">";
        foreach ($data as $item) {
            echo "<div style=\"margin-bottom:8px; padding:8px; border: 1px solid #ddd; border-radius: 4px;\">";
            echo "<strong>Name:</strong> " . htmlspecialchars($item['name']) . "<br>";
            echo "<strong>Quantity:</strong> " . htmlspecialchars($item['qty']) . "<br>";
            echo "<strong>Time In:</strong> " . htmlspecialchars($item['time_created']) . "<br>";
            if (isset($item["assigned_to"])) {
                foreach ($item["assigned_to"] as $assignedTo) {
                    echo "<div style=\"margin-top:4px; padding:8px; border: 1px solid #ddd; border-radius: 4px;\">";
                    echo "<strong>Table:</strong> " . htmlspecialchars($assignedTo['table']) . "<br>";
                    echo "<strong>Quantity:</strong> " . htmlspecialchars($assignedTo['qty']) . "<br>";
                    echo "<strong>Time In:</strong> " . htmlspecialchars($assignedTo['time_created']) . "<br>";
                    echo "</div>";
                }
            } else {
                echo "<div style=\"margin-top:4px; padding:8px; border: 1px solid #ddd; border-radius: 4px;\">";
                echo "<strong>Status:</strong> Belum Ditempatkan <br>";
                echo "</div>";
            }
            echo "</div>";
        }
        echo "</div>";
    }

    ?>
</body>

</html>