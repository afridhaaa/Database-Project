<?php
include 'db/db.php';

$driverId = $_GET['id'];
$sql = "SELECT drivers.forename, drivers.nationality, drivers.url, driver_standings.points, driver_standings.position, driver_standings.wins 
        FROM drivers 
        INNER JOIN driver_standings ON drivers.driverId = driver_standings.driverId 
        WHERE drivers.driverId = $driverId";
        
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
?>
    <form action="update_driver.php" method="POST">
        <input type="hidden" name="driverId" value="<?php echo $driverId; ?>">
        <input type="text" name="forename" value="<?php echo $row['forename']; ?>" required>
        <input type="number" name="points" value="<?php echo $row['points']; ?>" required>
        <input type="number" name="position" value="<?php echo $row['position']; ?>" required>
        <input type="number" name="wins" value="<?php echo $row['wins']; ?>" required>
        <input type="text" name="nationality" value="<?php echo $row['nationality']; ?>" required>
        <input type="text" name="url" value="<?php echo $row['url']; ?>" required>
        <button type="submit">Update Driver Standing</button>
    </form>
<?php
} else {
    echo "No driver found";
}

$conn->close();
?>
