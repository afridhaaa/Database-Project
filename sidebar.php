<?php
// Get the current file name to compare for active class
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'admin_dashboard.php') { echo 'active'; } ?>" href="admin_dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_circuits.php') { echo 'active'; } ?>" href="crud_circuits.php">Manage Circuits</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_constructors.php') { echo 'active'; } ?>" href="crud_constructors.php">Manage Constructors</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_drivers.php') { echo 'active'; } ?>" href="crud_drivers.php">Manage Drivers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_driver_standings.php') { echo 'active'; } ?>" href="crud_driver_standings.php">Manage Driver Standings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_lap_times.php') { echo 'active'; } ?>" href="crud_lap_times.php">Manage Lap Times</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_races.php') { echo 'active'; } ?>" href="crud_races.php">Manage Races</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if ($currentPage == 'crud_results.php') { echo 'active'; } ?>" href="crud_results.php">Manage Results</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger <?php if ($currentPage == 'logout.php') { echo 'active'; } ?>" href="logout.php">Logout</a>
        </li>
    </ul>
</div>
