<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <!-- Logo Section -->
    <div class="logo text-center my-4">
        <a href="index.php">
            <img src="assets/images/FormulaVaultBanner2.png" alt="Formula 1 Logo" style="height: 70px; width:200px;" />
        </a>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="sidebar-nav">
        <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="<?php echo ($current_page == 'admin_login.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="admin_login.php">Admin</a>
        </li>

        <!-- Circuits Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'mycircuit.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Circuits
            </a>
            <ul class="dropdown-menu">
                <li><a href="mycircuit.php" class="dropdown-item <?php echo ($current_page == 'mycircuit.php') ? 'active' : ''; ?>">Circuit Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="cwithmost" class="dropdown-item">Circuits with Most Wins</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="avgn" class="dropdown-item">Average Laps by Circuits</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="rtl" class="dropdown-item">Retrieve Total Points</button>
                    </form>
                </li>
            </ul>
        </li>

        <!-- Constructors Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'myconstruct.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Constructors
            </a>
            <ul class="dropdown-menu">
                <li><a href="myconstruct.php" class="dropdown-item <?php echo ($current_page == 'myconstruct.php') ? 'active' : ''; ?>">Constructor Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="clist" class="dropdown-item">Fastest Laps Achieved</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="cona" class="dropdown-item">Constructor Wins by Year</button>
                    </form>
                </li>
            </ul>
        </li>

        <!-- Drivers Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'mydriver.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Drivers
            </a>
            <ul class="dropdown-menu">
                <li><a href="mydriver.php" class="dropdown-item <?php echo ($current_page == 'mydriver.php') ? 'active' : ''; ?>">Driver Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="avgPoint" class="dropdown-item">Average Points by Constructors</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="topdriver" class="dropdown-item">Top Driver</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="alldrivers" class="dropdown-item">Driver-Constructor Championship Wins</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="retrievedrivers" class="dropdown-item">Achieved Points Above 50</button>
                    </form>
                </li>
            </ul>
        </li>

        <!-- Driver Standings Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'mystand.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Driver Standings
            </a>
            <ul class="dropdown-menu">
                <li><a href="mystand.php" class="dropdown-item <?php echo ($current_page == 'mystand.php') ? 'active' : ''; ?>">Standings Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="totalpoints" class="dropdown-item">Total Driver Points</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="dwithavgspeed" class="dropdown-item">Average Speed of Drivers</button>
                    </form>
                </li>
            </ul>
        </li>

        <!-- Lap Times Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'mylap.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Lap Times
            </a>
            <ul class="dropdown-menu">
                <li><a href="mylap.php" class="dropdown-item <?php echo ($current_page == 'mylap.php') ? 'active' : ''; ?>">Lap Time Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="laptime" class="dropdown-item">Fastest Lap Time</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="dwithfast" class="dropdown-item">Fastest Lap Speed</button>
                    </form>
                </li>
            </ul>
        </li>

        <!-- Races Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'myrace.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Races
            </a>
            <ul class="dropdown-menu">
                <li><a href="myrace.php" class="dropdown-item <?php echo ($current_page == 'myrace.php') ? 'active' : ''; ?>">Races Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="retrieveraces" class="dropdown-item">Starting vs Final Position</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="raceslist" class="dropdown-item">Races List</button>
                    </form>
                </li>
                <!-- <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="allraces" class="dropdown-item">Fastest Lap Times by Race</button>
                    </form>
                </li> -->
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="nofraces" class="dropdown-item">Drivers' Number of Races</button>
                    </form>
                </li>
                <!-- <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="araces" class="dropdown-item">Races Achieved</button>
                    </form>
                </li> -->
            </ul>
        </li>

        <!-- Results Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo ($current_page == 'myresult.php' || $current_page == 'process.php') ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Results
            </a>
            <ul class="dropdown-menu">
                <li><a href="myresult.php" class="dropdown-item <?php echo ($current_page == 'myresult.php') ? 'active' : ''; ?>">Result Details</a></li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="mostracewins" class="dropdown-item">Most Race Wins</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="raceswon" class="dropdown-item">Races Won</button>
                    </form>
                </li>
                <li>
                    <form action="process.php" method="post">
                        <button type="submit" name="top5" class="dropdown-item">Top Drivers with Podiums</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</div>
