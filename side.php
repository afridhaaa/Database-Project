<div class="sidebar">
                  
                        <!-- Sidebar -->
                    
                           <ul class="sidebar-nav">
                           <li class="active">
                    <a class="nav-link" href="index.php">Home</a>
                              </li>
                           <li class="active">
                    <a class="nav-link" href="admin_login.php">Admin</a>
                              </li>
                             
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Circuits
</a>
<ul class="dropdown-menu">
  <li><a href="mycircuit.php" class="dropdown-item">Circuit Details</a></li>
<li>
                            <form action="process.php" method="post">
                                <button type="submit" name="cwithmost" class="dropdown-item">Circuits with most races</button>  </form>
                          </li>
                          <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="avgn" class="dropdown-item">Average Number </button> </form>
                          </li>
                          <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="rtl" class="dropdown-item">Retrieve Total Points </button> </form>
                          </li>
</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Constructors
</a>
<ul class="dropdown-menu">
<li><a href="myconstruct.php" class="dropdown-item">Constructor Details</a></li>
<li>
<li>
                            <form action="process.php" method="post">
                                <button type="submit" name="clist" class="dropdown-item">Constructors List</button></form>
                          </li>
                          <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="cona" class="dropdown-item">most races constructors</button> </form>
                          </li>

</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Drivers
</a>
<ul class="dropdown-menu">
<li><a href="mydriver.php" class="dropdown-item">Driver Details</a></li>
<li>
<li class="active">
<form action="process.php" method="post">
<button type="submit" name="avgPoint" class="dropdown-item">Average Points</button>
</form>                             
</li>
<li>
<form action="process.php" method="post">
         <button type="submit" name="topdriver" class="dropdown-item">Top Driver</button>
              </form>
         </li>
         <li>
  <form action="process.php" method="post">
   <button type="submit" name="alldrivers" class="dropdown-item">All Drivers</button> </form>
      </li>
      <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="retrievedrivers" class="dropdown-item">Retrieve Drivers</button> </form>
                          </li>
</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Driver Standings
</a>
<ul class="dropdown-menu">
<li><a href="mystand.php" class="dropdown-item">Standings Details</a></li>
<li>
<li>
                                <form action="process.php" method="post">
                                    <button type="submit" name="totalpoints" class="dropdown-item">Total Points</button> </form>
                              </li>
                              <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="dwithavgspeed" class="dropdown-item"> highest avg speed drivers </button>  </form>
                          </li>
                          
</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Lap Times
</a>
<ul class="dropdown-menu">
<li><a href="mylap.php" class="dropdown-item">Lap Time Details</a></li>
<li>
<li>
                                <form action="process.php" method="post">
                                    <button type="submit" name="laptime" class="dropdown-item">Fastest Lap Time</button> </form>
                              </li>
                              <li>
                                <form action="process.php" method="post">
                                    <button type="submit" name="dwithfast" class="dropdown-item">fastest lap speed drivers</button>
                                      </form>
                              </li>

</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Races
</a>
<ul class="dropdown-menu">
<li><a href="myrace.php" class="dropdown-item">Races Details</a></li>
<li>
                              <li>
                                <form action="process.php" method="post">
                                    <button type="submit" name="retrieveraces" class="dropdown-item">Retrieve Races</button>
                                   </form>
                              </li>
                              <li>
                                <form action="process.php" method="post">
                                    <button type="submit" name="raceslist" class="dropdown-item">Races List</button> </form>
                              </li>
                              <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="raceswithmdrivers" class="dropdown-item">Races with multiple drivers  </button> </form>
                          </li> 
                            
                          <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="allraces" class="dropdown-item">All Races</button> </form>
                          </li>   
                          <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="nofraces" class="dropdown-item">Number of races</button> </form>
                          </li>     
                          <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="araces" class="dropdown-item">Races Achieved</button> </form>
                          </li>                
</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Results
</a>
<ul class="dropdown-menu">
<li><a href="myresult.php" class="dropdown-item">Result Details</a></li>
<li>
<li>
                            <form action="process.php" method="post">
                                <button type="submit" name="mostracewins" class="dropdown-item">Most race wins</button>  </form>
                          </li>
                          <li>
                                <form action="process.php" method="post">
                                    <button type="submit" name="raceswon" class="dropdown-item">Races Won</button>
                                   </form>
                              </li>
                              <li>
                            <form action="process.php" method="post">
                                <button type="submit" name="top5" class="dropdown-item">Top 5 Drivers</button>
                               </form>
                          </li>
</ul>
</li>

                   
                           </ul>
                      
                        <!-- /#sidebar-wrapper -->
                
                </div>