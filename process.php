<?php include 'db/db.php';
if(isset($_POST['avgPoint'])){
    $sql="SELECT d.forename, c.constructor_name, AVG(rs.points) AS avg_points FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id GROUP BY d.forename, c.constructor_name ORDER BY avg_points DESC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:avgpoint.php?msg=avg_point");
  }
  else{
      header("Location:index.php?msg=avgpoint_error");
  }
  }
  if(isset($_POST['raceswon'])){
    $sql="SELECT d.forename, r.name AS race_name, c.circuit_name FROM results rs INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits c ON r.circuit_id = c.circuit_id INNER JOIN drivers d ON rs.driverId = d.driverId WHERE rs.position = 1 ORDER BY d.forename, r.date";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:raceswon.php?msg=races_won");
  }
  else{
      header("Location:index.php?msg=raceswon_error");
  }
  }
  if(isset($_POST['topdriver'])){
    $sql="SELECT d.forename, COUNT(rs.position) AS total_wins, c.circuit_country FROM results rs INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits c ON r.circuit_id = c.circuit_id INNER JOIN drivers d ON rs.driverId = d.driverId WHERE rs.position = 1 GROUP BY d.forename, c.circuit_country ORDER BY total_wins DESC LIMIT 3";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:topdriver.php?msg=top_driver");
  }
  else{
      header("Location:index.php?msg=topdriver_error");
  }
  }
  if(isset($_POST['retrieveraces'])){
    $sql="SELECT d.forename, r.name AS race_name, rs.grid AS starting_position, rs.position AS final_position, c.constructor_name FROM results rs INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN drivers d ON rs.driverId = d.driverId WHERE rs.position < rs.grid ORDER BY rs.position ASC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:retrieveraces.php?msg=retrieve_races");
  }
  else{
      header("Location:index.php?msg=retrieveraces_error");
  }
  }
  if(isset($_POST['totalpoints'])){
    $sql="SELECT d.forename, c.constructor_name, ci.circuit_name, SUM(rs.points) AS total_points FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id GROUP BY d.forename, c.constructor_name, ci.circuit_name ORDER BY total_points DESC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:totalpoints.php?msg=total_points");
  }
  else{
      header("Location:index.php?msg=totalpoints_error");
  }
  }
  if(isset($_POST['laptime'])){
    $sql="SELECT d.forename, r.name AS race_name, c.circuit_name, MIN(rs.fastestLapTime) AS fastest_lap_time FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits c ON r.circuit_id = c.circuit_id GROUP BY d.forename, r.name, c.circuit_name ORDER BY fastest_lap_time ASC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:laptime.php?msg=lap_time");
  }
  else{
      header("Location:index.php?msg=laptime_error");
  }
  }
  if(isset($_POST['alldrivers'])){
    $sql="SELECT d.forename, c.constructor_name, r.name AS race_name, ci.circuit_name FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id WHERE rs.position = 1 AND c.constructor_id IN ( SELECT constructor_id FROM constructors WHERE no_of_titles > 0 ) ORDER BY c.constructor_name, r.date";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:alldrivers.php?msg=all_drivers");
  }
  else{
      header("Location:index.php?msg=alldrivers_error");
  }
  }
  if(isset($_POST['dwithfast'])){
    $sql="SELECT d.forename, c.constructor_name, r.name AS race_name, rs.fastestLapSpeed FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId WHERE c.constructor_id IN ( SELECT constructorId FROM results WHERE position <= 3 ) ORDER BY rs.fastestLapSpeed DESC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:dwithfast.php?msg=dwith_fast");
  }
  else{
      header("Location:index.php?msg=dwithfast_error");
  }
  }
  if(isset($_POST['raceslist'])){
    $sql="SELECT r.name AS race_name, c.circuit_name, d.forename AS winner FROM results rs INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits c ON r.circuit_id = c.circuit_id INNER JOIN drivers d ON rs.driverId = d.driverId WHERE r.year = 2015 AND rs.position = 1 ORDER BY r.date";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:raceslist.php?msg=races_list");
  }
  else{
      header("Location:index.php?msg=raceslist_error");
  }
  }
  if(isset($_POST['retrievedrivers'])){
    $sql="SELECT d.forename, c.constructor_name, ci.circuit_name, SUM(rs.points) AS total_points FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id GROUP BY d.forename, c.constructor_name, ci.circuit_name HAVING total_points > 50 ORDER BY total_points DESC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:retrievedrivers.php?msg=retrieve_drivers");
  }
  else{
      header("Location:index.php?msg=retrievedrivers_error");
  }
  }
  if(isset($_POST['dwithavgspeed'])){
    $sql="SELECT ci.circuit_name, d.forename, AVG(lap.milliseconds) AS avg_speed FROM lap_times lap INNER JOIN drivers d ON lap.driverId = d.driverId INNER JOIN races r ON lap.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id GROUP BY ci.circuit_name, d.forename ORDER BY avg_speed ASC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:dwithavgspeed.php?msg=dwith_avgspeed");
  }
  else{
      header("Location:index.php?msg=dwithavgspeed_error");
  }
  }
  if(isset($_POST['raceswithmdrivers'])){
    $sql="SELECT r.name AS race_name, c.constructor_name, COUNT(rs.driverId) AS drivers_in_top_5 FROM results rs INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN constructors c ON rs.constructorId = c.constructor_id WHERE rs.position <= 5 GROUP BY r.name, c.constructor_name HAVING drivers_in_top_5 > 1 ORDER BY drivers_in_top_5 DESC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:raceswithmdrivers.php?msg=raceswith_mdrivers");
  }
  else{
      header("Location:index.php?msg=raceswithmdrivers_error");
  }
  }
  if(isset($_POST['mostracewins'])){
    $sql="SELECT d.forename, ci.circuit_name, COUNT(rs.position) AS total_wins FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id WHERE rs.position = 1 GROUP BY d.forename, ci.circuit_name ORDER BY total_wins DESC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:mostracewins.php?msg=mostrace_wins");
  }
  else{
      header("Location:index.php?msg=mostracewins_error");
  }
  }
  if(isset($_POST['allraces'])){
    $sql="SELECT r.name AS race_name, d.forename, rs.fastestLapTime, rs.position FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN races r ON rs.raceId = r.raceId WHERE rs.fastestLapTime IS NOT NULL AND rs.position <> 1 ORDER BY rs.fastestLapTime ASC";
  $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:allraces.php?msg=all_races");
  }
  else{
      header("Location:index.php?msg=allraces_error");
  }
  }
  if(isset($_POST['top5'])){
   $sql="SELECT d.forename, c.constructor_name, COUNT(rs.position) AS podium_finishes FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id WHERE rs.position <= 3 GROUP BY d.forename, c.constructor_name ORDER BY podium_finishes DESC LIMIT 5";
    $query=mysqli_query($conn, $sql);
  if($query){
      $row=mysqli_fetch_array($query);
    
      header("Location:top5.php?msg=top_5");
  }
  else{
      header("Location:index.php?msg=top_5_error");
  }
  }
  if(isset($_POST['cwithmost'])){
   $sql="SELECT c.constructor_name, ci.circuit_name, COUNT(rs.position) AS total_wins FROM results rs INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id WHERE rs.position = 1 GROUP BY c.constructor_name, ci.circuit_name ORDER BY total_wins DESC";
    $query=mysqli_query($conn, $sql);
   if($query){
       $row=mysqli_fetch_array($query);
     
       header("Location:cwithmost.php?msg=cwith_most");
   }
   else{
       header("Location:index.php?msg=cwithmost_error");
   }
   }
   if(isset($_POST['nofraces'])){
   $sql="SELECT d.forename, r.year, COUNT(rs.raceId) AS total_races, AVG(rs.points) AS avg_points FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN races r ON rs.raceId = r.raceId WHERE r.year = 2020 GROUP BY d.forename, r.year ORDER BY avg_points DESC";
    $query=mysqli_query($conn, $sql);
    if($query){
        $row=mysqli_fetch_array($query);
      
        header("Location:nofraces.php?msg=nof_races");
    }
    else{
        header("Location:index.php?msg=nofraces_error");
    }
    }
    if(isset($_POST['clist'])){
       $sql="SELECT c.constructor_name, ci.circuit_name, COUNT(rs.fastestLap) AS total_fastest_laps FROM results rs INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id WHERE rs.fastestLap IS NOT NULL GROUP BY c.constructor_name, ci.circuit_name HAVING total_fastest_laps >= 5 ORDER BY total_fastest_laps DESC";
        $query=mysqli_query($conn, $sql);
         if($query){
             $row=mysqli_fetch_array($query);
           
             header("Location:clist.php?msg=c_list");
         }
         else{
             header("Location:index.php?msg=clist_error");
         }
         }
         if(isset($_POST['araces'])){
           $sql="SELECT r.name AS race_name, c.constructor_name, COUNT(rs.position) AS drivers_in_top_2 FROM results rs INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN constructors c ON rs.constructorId = c.constructor_id WHERE rs.position IN (1, 2) GROUP BY r.name, c.constructor_name HAVING drivers_in_top_2 = 2 ORDER BY r.date";
            $query=mysqli_query($conn, $sql);
              if($query){
                  $row=mysqli_fetch_array($query);
                
                  header("Location:araces.php?msg=a_races");
              }
              else{
                  header("Location:index.php?msg=araces_error");
              }
              }
 if(isset($_POST['avgn'])){
$sql="SELECT ci.circuit_name, d.forename, AVG(rs.laps) AS avg_laps FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id GROUP BY ci.circuit_name, d.forename ORDER BY avg_laps DESC";
    $query=mysqli_query($conn, $sql);
if($query){
     $row=mysqli_fetch_array($query);
                     
  header("Location:avgn.php?msg=avg_n");
  }
    else{
   header("Location:index.php?msg=avgn_error");
        }
   }
   if(isset($_POST['cona'])){
    $sql="SELECT c.constructor_name, r.year, COUNT(rs.position) AS total_wins FROM results rs INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id WHERE rs.position = 1 AND ci.circuit_country = 'Australia' GROUP BY c.constructor_name, r.year ORDER BY total_wins DESC";
    $query=mysqli_query($conn, $sql);
    if($query){
         $row=mysqli_fetch_array($query);
                         
      header("Location:cona.php?msg=cona");
      }
        else{
       header("Location:index.php?msg=cona_error");
            }
       }
       if(isset($_POST['rtl'])){
      $sql="SELECT d.forename, c.constructor_name, ci.circuit_name, SUM(rs.points) AS total_points FROM results rs INNER JOIN drivers d ON rs.driverId = d.driverId INNER JOIN constructors c ON rs.constructorId = c.constructor_id INNER JOIN races r ON rs.raceId = r.raceId INNER JOIN circuits ci ON r.circuit_id = ci.circuit_id GROUP BY d.forename, c.constructor_name, ci.circuit_name ORDER BY total_points DESC";
        $query=mysqli_query($conn, $sql);
        if($query){
             $row=mysqli_fetch_array($query);
                             
          header("Location:rtl.php?msg=rtl");
          }
            else{
           header("Location:index.php?msg=rtl_error");
                }
           }
?>