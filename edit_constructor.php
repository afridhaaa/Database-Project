<?php
include 'db/db.php';

$constructor_id = $_GET['id'];
$sql = "SELECT * FROM constructors WHERE constructor_id = $constructor_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
?>
    <form action="update_constructor.php" method="POST">
        <input type="hidden" name="constructor_id" value="<?php echo $constructor_id; ?>">
        <input type="text" name="constructor_name" value="<?php echo $row['constructor_name']; ?>" required>
        <input type="number" name="no_of_pole_positions" value="<?php echo $row['no_of_pole_positions']; ?>" required>
        <input type="number" name="no_of_titles" value="<?php echo $row['no_of_titles']; ?>" required>
        <input type="number" name="constructor_points" value="<?php echo $row['constructor_points']; ?>" required>
        <input type="text" name="nationality" value="<?php echo $row['nationality']; ?>" required>
        <input type="text" name="url" value="<?php echo $row['url']; ?>" required>
        <button type="submit">Update Constructor</button>
    </form>
<?php
}
$conn->close();
?>
