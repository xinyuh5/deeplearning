<?php
session_start();
// echo "Favorite color is " . $_SESSION["userid"] . ".";
// echo "Favorite color is " . $_SESSION["userid"] . ".";
$conn = new mysqli('localhost','xwhz411recipes_admin','cs411xwhzcs411','xwhz411recipes_database');
$sql1 = "SELECT * FROM Accounts WHERE User_id=". $_SESSION["userid"]." ";
$result1 = $conn->query($sql1);
$row1 = $result1->fetch_assoc();
if($row1["Gender"]==1){
    $Gender1="Female";
}
if($row1["Gender"]==2){
    $Gender1="Male";
}

// #Calorie
// if($row1["Gender"]==1){
//     $BMR= (655 + (4.3 * $row1['Weight']) + (4.7 * $row1['Height']) - (4.7 *$row1['Age']));
// }

// if($row1["Gender"]==2){
//     $BMR=(66 + (6.3 * $row1['Weight']) + (12.9 * $row1['Height']) - (6.8 *$row1['Age']));
// }

    $sql_select = "SELECT Gender, Weight, Height, Age, act FROM Accounts WHERE User_id = ". $_SESSION["userid"]."";
    
    
    // SELECT * FROM Recipes as r, Favorites as f WHERE f.Recipe_id=r.Recipe_id and User_id=". $_SESSION["userid"]."";
    $result = $conn->query($sql_select);
    //echo $result;
    
    
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            //echo "Gender: " . $row["Gender"]. " ";
            $Gender = $row["Gender"];
            $Weight = $row["Weight"];
            $Height = $row["Height"];
            $Age = $row["Age"];
            $act = $row["act"];            
        }
    } else {
       echo "0 results";
    }
    
    $expected_nutrition = exec("/home/xwhz411recipes/virtualenv/pythontest/3.7/bin/python3 nutrition_standard.py $Gender $Weight $Height $Age $act", $output, $ret_code);
    // Use preg_split() function
          
    // use of explode 
    $string = $expected_nutrition; 
    $str_arr = explode ("]", $string);  
    $str_arr = explode ("[", $str_arr[0]);  
    $str_arr = explode (",", $str_arr[1]); 
    //print_r($str_arr); 
    //echo $expected_nutrition[0:4];
    //echo $output;
    //echo $ret_code;
    //echo $expected_nutrition;
    // if ($expected_nutrition){
    //     echo "Worked";
    //     echo $expected_nutrition;
    //     }
    //     else{
    //     echo "didnt work";
    //     }
    
    // calculate past 7 days
    $date_now = new DateTime("now", new DateTimeZone('America/Chicago') );
    $date_now = $date_now->format('Y-m-d 00:00:00');
    //echo $date_now;
    
    $date_ago = new DateTime("-7 days", new DateTimeZone('America/Chicago') );
    $date_ago = $date_ago->format('Y-m-d 00:00:00');
    //echo $date_ago;
    
    //$days_ago = date('Y-m-d 00:00:00', strtotime('-7 days', strtotime('2019-04-19')));
    //echo $days_ago;
    //$timestamp = strtotime('today midnight');
    //echo gmdate("Y-m-d\TH:i:s\Z", $timestamp);
    //$date_ago = '2019-04-12 00:00:00';
    
    $sql_past = "SELECT Calorie_Intake, Sodium, Fat, Protein FROM Total_records WHERE User_id = ". $_SESSION["userid"]." AND Time>='$date_ago' AND Time <'$date_now'";
    $calorie_past = array();
    $sodium_past = array();
    $fat_past = array();
    $protein_past = array();

    $result_past = $conn->query($sql_past);
    
    if ($result_past->num_rows > 0) {
        // output data of each row
        while($row = $result_past->fetch_assoc()) {
            array_push($calorie_past, $row["Calorie_Intake"]);
            array_push($sodium_past, $row["Sodium"]);
            array_push($fat_past, $row["Fat"]);
            array_push($protein_past, $row["Protein"]);
        }
    } else {
        echo "0 result";
    }
    

    
    // calculate average
    $sql_avg = "SELECT AVG(Calorie_Intake) AS Calorie_Intake, AVG(Sodium) AS Sodium, AVG(Fat) AS Fat, AVG(Protein) AS Protein FROM Total_records WHERE Time>='$date_ago' AND Time <'$date_now' GROUP BY Time";
    
    
    // SELECT * FROM Recipes as r, Favorites as f WHERE f.Recipe_id=r.Recipe_id and User_id=". $_SESSION["userid"]."";
    $result_avg = $conn->query($sql_avg);
    $calorie_avg = array();
    $sodium_avg = array();
    $fat_avg = array();
    $protein_avg = array();
    
    if ($result_avg->num_rows > 0) {
        // output data of each row
        while($row = $result_avg->fetch_assoc()) {
            //print_r($row);
            array_push($calorie_avg, $row["Calorie_Intake"]);
            array_push($sodium_avg, $row["Sodium"]);
            array_push($fat_avg, $row["Fat"]);
            array_push($protein_avg, $row["Protein"]);
        }
    } else {
        echo "0 result";
    }
    
        
    // text cloud

    
    $sql_text = "SELECT Categories FROM Favorites JOIN Recipes ON Favorites.Recipe_id = Recipes.Recipe_id WHERE User_id = ". $_SESSION["userid"]."";

    $result_text = $conn->query($sql_text);
    $saved_cat = array();
    
    if ($result_text->num_rows > 0) {
        // output data of each row
        while($row = $result_text->fetch_assoc()) {
            
            $cat = $row["Categories"];
            $str_arr_text = explode("##", $cat);
            for($i=0; $i<count($str_arr_text); $i++){
                if(array_key_exists($str_arr_text[$i], $saved_cat)){
                    $saved_cat[$str_arr_text[$i]] = $saved_cat[$str_arr_text[$i]]+1;
                }
                else{
                    $saved_cat[$str_arr_text[$i]] = 1;
                }
            }
        }
    } else {
        echo "0 results";
    }
    // sort the array
    arsort($saved_cat);
    //print_r($saved_cat);
    //print_r(array_keys($saved_cat));
    
    $conn->close();
        

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <link href="img/hamburger-icon.png" rel="icon" type="image/ong" />
	<title>Recipe Explorer</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.css" />
    <link rel="stylesheet" href="css/flaticon.css" />
    <link rel="stylesheet" href="css/themify-icons.css" />
    <link rel="stylesheet" href="vendors/owl-carousel/owl.carousel.min.css" />
    <link rel="stylesheet" href="vendors/nice-select/css/nice-select.css" />
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>   
    
    <!-- main css -->
    <link rel="stylesheet" href="css/style.css" />

  </head>
<!--================ Start Header Menu Area =================-->

  <body>

<header class="header_area">
<div class="main_menu">
<nav class="navbar navbar-expand-lg navbar-light">
<div class="container"><a class="navbar-brand logo_h" href="index.html"><img alt="" src="img/hamburger-icon.png" /></a>
<div class="collapse navbar-collapse offset" id="navbarSupportedContent">
<ul class="nav navbar-nav menu_nav ml-auto" style="margin-left: 640px;">

	<li class="nav-item"><a class="nav-link" href="about-us.html">About</a></li>
	<li class="nav-item"><a class="nav-link" href="qa.html">Q&amp;A</a></li>
	<li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
    <li class="nav-item submenu dropdown">
      <a
        href="person.php"
        class="nav-link dropdown-toggle"
        data-toggle="dropdown"
        role="button"
        aria-haspopup="true"
        aria-expanded="false"
        >Personal Center</a
      >
      <ul class="dropdown-menu">
        <li class="nav-item">
          <a class="nav-link" href="own-recipes.php">Own Recipes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="saved_recipes.php">Saved Recipes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="user_analysis.php">Report</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.html">Sign Out</a>
        </li>
      </ul>
    </li>
	<li class="nav-item"><a class="nav-link" href="search_main.php">Search</a></li>


</ul>
</div>
</div>
</nav>
</div>
</header>

<!--================ End Header Menu Area =================-->



<!--================Personal Area =================-->
<section class="blog_area section_gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <div class="blog_left_sidebar">

                <div class="content_wrapper">
                        <h4 class="title">Personal Information</h4>
                        <div class="content">
                            <p><img src="https://img.icons8.com/office/16/000000/toilet.png" height="20" width="20"><span class=" h1"> Gender:</span> <span><?=$Gender1;?></span></p>
                            <p><img src="https://img.icons8.com/dusk/64/000000/age.png"  height="20" width="20"><span class="h1"> Age: </span><?=$row1['Age'];?></p>
                           <p><img src="https://img.icons8.com/cotton/64/000000/weight-1.png" height="20" width="20"><span class="h1"> Weight: </span><?=$row1['Weight'];?> pounds</p>
                           <p><img src="https://img.icons8.com/ultraviolet/40/000000/height.png" height="20" width="20"><span class="h1"> Height: </span> <?=$row1['Height'];?> inches</p>
                        </div>

                        <!--<h4 class="title"> Estimated Calorie Needs per Day</h4>-->
                        <!--<div class="content">-->
                        <!--<p>If you are sedentary (little or no exercise) :</p>-->
                        <!--<p>Calorie-Calculation = <?=$BMR*1.2;?></p>-->
                        <!--<p>If you are lightly active (light exercise/sports 1-3 days/week) : </p>-->
                        <!--<p>Calorie-Calculation = <?=($BMR*1.375);?></p>-->
                        <!--<p>If you are moderately active (moderate exercise/sports 3-5 days/week) :</p>-->
                        <!--<p>Calorie-Calculation = <?=($BMR*1.55);?></p>-->
                        <!--<p>If you are very active (hard exercise/sports 6-7 days a week) :</p>-->
                        <!--<p>Calorie-Calculation = <?=($BMR*1.725);?></p>-->
                        <!--<p>If you are extra active (very hard exercise/sports & physical job or 2x training) : </p>-->
                        <!--<p>Calorie-Calculation = <?=($BMR*1.9);?></p>-->
                        <!--</div>-->

                        <!--<h4 class="title">Nutrition Facts</h4>-->
                        <!--<div class="content">-->
                        <!--    <ul class="course_list">-->
                        <!--        <li class="justify-content-between d-flex">-->
                        <!--            <p>NA</p>-->
                        <!--            <a class="primary-btn text-uppercase" href="#">View Details</a>-->
                        <!--    </ul>-->
                        <!--</div>-->
                        
                        <h4 class="title">Nutrition Plots</h4>
                        <div class="content">
                           <ul class="nav nav-pills">
                            <li class="active"><a data-toggle="pill" href="#home">Calory</a></li>
                            <li><a id = "fat" data-toggle="pill" href="#menu1">Fat</a></li>
                            <li><a id = "protein" data-toggle="pill" href="#menu2">Protein</a></li>
                            <li><a id = "sodium" data-toggle="pill" href="#menu3">Sodium</a></li>
                          </ul>
                          <div class="tab-content">
                            <div id="home" class="tab-pane fade in active">
                              <script src="js/Chart.min.js"></script>
	                          <script src="js/utils.js"></script>
	                          	<div style="width:100%">
		                        <canvas id="canvas_calory"></canvas>
	                            </div>
                            	<script>
                            		var randomScalingFactor = function() {
                            			return Math.round(Math.random() * 100);
                            		};
                            		
                            
                            		var color = Chart.helpers.color;
                            		var val_calory = "<?php echo $str_arr[0] ?>";
                            		var config_calory = {
                            			type: 'radar',
                            			data: {
                            			    labels: ['Mon', 'Tue', 'Wed', 'Tues', 'Fri', 'Sat', 'Sun'],
                            				//labels: [['Eating', 'Dinner'], ['Drinking', 'Water'], 'Sleeping', ['Designing', 'Graphics'], 'Coding', 'Cycling', 'Running'],
                            				datasets: [{
                            					label: 'Real Performance',
                            					backgroundColor: color(window.chartColors.red).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.red,
                            					pointBackgroundColor: window.chartColors.red,
 
                            					data: [
                                                    "<?php echo $calorie_past[0]?>",
                                                    "<?php echo $calorie_past[1]?>",
                                                    "<?php echo $calorie_past[2]?>",
                                                    "<?php echo $calorie_past[3]?>",
                                                    "<?php echo $calorie_past[4]?>",
                                                    "<?php echo $calorie_past[5]?>",
                                                    "<?php echo $calorie_past[6]?>"
                            					]
                            				}, {
                            					label: 'Standard Performance',
                            					backgroundColor: color(window.chartColors.blue).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.blue,
                            					pointBackgroundColor: window.chartColors.blue,
                            					data: [
                            						val_calory,
                            						val_calory,
                            						val_calory,
                            						val_calory,
                            						val_calory,
                            						val_calory,
                            						val_calory
                            					]
                            				}, {
                            					label: 'Average Performance',
                            					backgroundColor: color(window.chartColors.yellow).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.yellow,
                            					pointBackgroundColor: window.chartColors.yellow,
                            					data: [
                                                    "<?php echo $calorie_avg[0]?>",
                                                    "<?php echo $calorie_avg[1]?>",
                                                    "<?php echo $calorie_avg[2]?>",
                                                    "<?php echo $calorie_avg[3]?>",
                                                    "<?php echo $calorie_avg[4]?>",
                                                    "<?php echo $calorie_avg[5]?>",
                                                    "<?php echo $calorie_avg[6]?>"
                            					]
                            				}
                            				]
                            			},
                            			options: {
                            				legend: {
                            					position: 'top',
                            				},
                            				title: {
                            					display: true,
                            					text: 'Calory Radar Chart'
                            				},
                            				scale: {
                            					ticks: {
                            						beginAtZero: true
                            					}
                            				}
                            			}
                            		};
                                    
                            		window.onload = function() {
                            		    //var val = "<?php echo $str_arr[0] ?>";
                            		    //document.write(val);
                            			window.myRadar_1 = new Chart(document.getElementById('canvas_calory'), config_calory);
                            		};
                            		/*
                            		document.getElementById('calory').addEventListener('click', function() {
                                        window.myRadar_1 = new Chart(document.getElementById('canvas_calory'), config_calory);
                            		});      */                      		
                                   
                            	</script>
                            </div>
                            <div id="menu1" class="tab-pane fade">
                              <script src="js/Chart.min.js"></script>
	                          <script src="js/utils.js"></script>
                              	<div style="width:100%">
		                        <canvas id="canvas_fat"></canvas>
	                            </div>
                            	<script>
                            		var randomScalingFactor = function() {
                            			return Math.round(Math.random() * 100);
                            		};
                            
                            		var color = Chart.helpers.color;
                            		var val_fat = "<?php echo $str_arr[2] ?>";
                            		var config_fat = {
                            			type: 'radar',
                            			data: {
                            			    labels: ['Mon', 'Tue', 'Wed', 'Tues', 'Fri', 'Sat', 'Sun'],
                            				//labels: [['Eating', 'Dinner'], ['Drinking', 'Water'], 'Sleeping', ['Designing', 'Graphics'], 'Coding', 'Cycling', 'Running'],
                            				datasets: [{
                            					label: 'Real Performance',
                            					backgroundColor: color(window.chartColors.red).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.red,
                            					pointBackgroundColor: window.chartColors.red,
                            					data: [
                                                    "<?php echo $fat_past[0]?>",
                                                    "<?php echo $fat_past[1]?>",
                                                    "<?php echo $fat_past[2]?>",
                                                    "<?php echo $fat_past[3]?>",
                                                    "<?php echo $fat_past[4]?>",
                                                    "<?php echo $fat_past[5]?>",
                                                    "<?php echo $fat_past[6]?>"
                            					]
                            				}, {
                            					label: 'Expected Performance',
                            					backgroundColor: color(window.chartColors.blue).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.blue,
                            					pointBackgroundColor: window.chartColors.blue,
                            					data: [
                                                     val_fat,
                                                     val_fat,
                                                     val_fat,
                                                     val_fat,
                                                     val_fat,
                                                     val_fat,
                                                     val_fat
                            					]
                            				}, {
                            					label: 'Average Performance',
                            					backgroundColor: color(window.chartColors.yellow).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.yellow,
                            					pointBackgroundColor: window.chartColors.yellow,
                            					data: [
                                                    "<?php echo $fat_avg[0]?>",
                                                    "<?php echo $fat_avg[1]?>",
                                                    "<?php echo $fat_avg[2]?>",
                                                    "<?php echo $fat_avg[3]?>",
                                                    "<?php echo $fat_avg[4]?>",
                                                    "<?php echo $fat_avg[5]?>",
                                                    "<?php echo $fat_avg[6]?>"
                            					]
                            				}
                            				]
                            			},
                            			options: {
                            				legend: {
                            					position: 'top',
                            				},
                            				title: {
                            					display: true,
                            					text: 'Fat Radar Chart'
                            				},
                            				scale: {
                            					ticks: {
                            						beginAtZero: true
                            					}
                            				}
                            			}
                            		};
                                    /*
                            		window.onload = function() {
                            			window.myRadar_2 = new Chart(document.getElementById('canvas_fat'), config_fat);
                            		};*/
                            		                            		
                            		document.getElementById('fat').addEventListener('click', function() {
                                        window.myRadar_2 = new Chart(document.getElementById('canvas_fat'), config_fat);
                            		});       
                                   
                            	</script>
                            </div>
                            <div id="menu2" class="tab-pane fade">
                              <script src="js/Chart.min.js"></script>
	                          <script src="js/utils.js"></script>
	                          	<div style="width:100%">
		                        <canvas id="canvas_protein"></canvas>
	                            </div>
                            	<script>
                            		var randomScalingFactor = function() {
                            			return Math.round(Math.random() * 100);
                            		};
                            
                            		var color = Chart.helpers.color;
                            		var val_protein = "<?php echo $str_arr[1] ?>";
                            		var config_protein = {
                            			type: 'radar',
                            			data: {
                            			    labels: ['Mon', 'Tue', 'Wed', 'Tues', 'Fri', 'Sat', 'Sun'],
                            				//labels: [['Eating', 'Dinner'], ['Drinking', 'Water'], 'Sleeping', ['Designing', 'Graphics'], 'Coding', 'Cycling', 'Running'],
                            				datasets: [{
                            					label: 'Real Performance',
                            					backgroundColor: color(window.chartColors.red).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.red,
                            					pointBackgroundColor: window.chartColors.red,
                            					data: [
                                                    "<?php echo $protein_past[0]?>",
                                                    "<?php echo $protein_past[1]?>",
                                                    "<?php echo $protein_past[2]?>",
                                                    "<?php echo $protein_past[3]?>",
                                                    "<?php echo $protein_past[4]?>",
                                                    "<?php echo $protein_past[5]?>",
                                                    "<?php echo $protein_past[6]?>"
                            					]
                            				}, {
                            					label: 'Expected Performance',
                            					backgroundColor: color(window.chartColors.blue).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.blue,
                            					pointBackgroundColor: window.chartColors.blue,
                            					data: [
                            					    val_protein,
                            					    val_protein,
                            					    val_protein,
                            					    val_protein,
                            					    val_protein,
                            					    val_protein,
                            					    val_protein
                            				// 		randomScalingFactor(),
                            				// 		randomScalingFactor(),
                            				// 		randomScalingFactor(),
                            				// 		randomScalingFactor(),
                            				// 		randomScalingFactor(),
                            				// 		randomScalingFactor(),
                            				// 		randomScalingFactor()
                            					]
                            				}, {
                            					label: 'Average Performance',
                            					backgroundColor: color(window.chartColors.yellow).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.yellow,
                            					pointBackgroundColor: window.chartColors.yellow,
                            					data: [
                                                    "<?php echo $protein_avg[0]?>",
                                                    "<?php echo $protein_avg[1]?>",
                                                    "<?php echo $protein_avg[2]?>",
                                                    "<?php echo $protein_avg[3]?>",
                                                    "<?php echo $protein_avg[4]?>",
                                                    "<?php echo $protein_avg[5]?>",
                                                    "<?php echo $protein_avg[6]?>"
                            					]
                            				}
                            				]
                            			},
                            			options: {
                            				legend: {
                            					position: 'top',
                            				},
                            				title: {
                            					display: true,
                            					text: 'Protein Radar Chart'
                            				},
                            				scale: {
                            					ticks: {
                            						beginAtZero: true
                            					}
                            				}
                            			}
                            		};
                                    /*
                            		window.onload = function() {
                            			window.myRadar_3 = new Chart(document.getElementById('canvas_protein'), config_protein);
                            		};*/
                            		
                            		document.getElementById('protein').addEventListener('click', function() {
                                        window.myRadar_3 = new Chart(document.getElementById('canvas_protein'), config_protein);
                            		});                                   		
                            		
                                   
                            	</script>                              
                              
                            </div>
                            
                            <div id="menu3" class="tab-pane fade">
                              <script src="js/Chart.min.js"></script>
	                          <script src="js/utils.js"></script>
	                          	<div style="width:100%">
		                        <canvas id="canvas_sodium"></canvas>
	                            </div>
                            	<script>
                            		var randomScalingFactor = function() {
                            			return Math.round(Math.random() * 100);
                            		};
                            
                            		var color = Chart.helpers.color;
                            		var val_sodium = "<?php echo $str_arr[3] ?>";
                            		var config_sodium = {
                            			type: 'radar',
                            			data: {
                            			    labels: ['Mon', 'Tue', 'Wed', 'Tues', 'Fri', 'Sat', 'Sun'],
                            				//labels: [['Eating', 'Dinner'], ['Drinking', 'Water'], 'Sleeping', ['Designing', 'Graphics'], 'Coding', 'Cycling', 'Running'],
                            				datasets: [{
                            					label: 'Real Performance',
                            					backgroundColor: color(window.chartColors.red).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.red,
                            					pointBackgroundColor: window.chartColors.red,
                            					data: [
                                                    "<?php echo $sodium_past[0]?>",
                                                    "<?php echo $sodium_past[1]?>",
                                                    "<?php echo $sodium_past[2]?>",
                                                    "<?php echo $sodium_past[3]?>",
                                                    "<?php echo $sodium_past[4]?>",
                                                    "<?php echo $sodium_past[5]?>",
                                                    "<?php echo $sodium_past[6]?>"
                            					]
                            				}, {
                            					label: 'Expected Performance',
                            					backgroundColor: color(window.chartColors.blue).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.blue,
                            					pointBackgroundColor: window.chartColors.blue,
                            					data: [
                                                      val_sodium,
                                                      val_sodium,
                                                      val_sodium,
                                                      val_sodium,
                                                      val_sodium,
                                                      val_sodium,
                                                      val_sodium
                            					]
                            				}, {
                            					label: 'Average Performance',
                            					backgroundColor: color(window.chartColors.yellow).alpha(0.2).rgbString(),
                            					borderColor: window.chartColors.yellow,
                            					pointBackgroundColor: window.chartColors.yellow,
                            					data: [
                                                    "<?php echo $sodium_avg[0]?>",
                                                    "<?php echo $sodium_avg[1]?>",
                                                    "<?php echo $sodium_avg[2]?>",
                                                    "<?php echo $sodium_avg[3]?>",
                                                    "<?php echo $sodium_avg[4]?>",
                                                    "<?php echo $sodium_avg[5]?>",
                                                    "<?php echo $sodium_avg[6]?>"
                            					]
                            				}
                            				]
                            			},
                            			options: {
                            				legend: {
                            					position: 'top',
                            				},
                            				title: {
                            					display: true,
                            					text: 'Sodium Radar Chart'
                            				},
                            				scale: {
                            					ticks: {
                            						beginAtZero: true
                            					}
                            				}
                            			}
                            		};
                                    /*
                            		window.onload = function() {
                            			window.myRadar_4 = new Chart(document.getElementById('canvas_sodium'), config_sodium);
                            		};*/
                            		
                            		document.getElementById('sodium').addEventListener('click', function() {
                                        window.myRadar_4 = new Chart(document.getElementById('canvas_sodium'), config_sodium);
                            		});                                      
                            	</script>                              
                              
                            </div>
                          </div>                           
                                                    
                            
                        </div>                        
                        
                        
                        
                    </div>
                </div>
            </div>
            <div id = "xinyu"  class="col-lg-5">
                            <style>
                                table,th,td {
                                  border : 1px solid white;
                                  border-collapse: collapse;
                                }
                                th,td {
                                  padding: 5px;
                                }
                            
                                /* The actual popup */
                                .pos{
                                    background-color: #002347;
                                    color: #fff;
                                    text-align: center;
                                    font-weight: bold;
                                }
                                /* Toggle this class - hide and show the popup */
                                .pos {
                                  -webkit-animation: fadeIn 1s;
                                  animation: fadeIn 1s;
                                }
                                
                                /* Add animation (fade in the popup) */
                                @-webkit-keyframes fadeIn {
                                  from {opacity: 0;} 
                                  to {opacity: 1;}
                                }
                                
                                @keyframes fadeIn {
                                  from {opacity: 0;}
                                  to {opacity:1 ;}
                                }

                            </style>
                <div class="blog_right_sidebar">
                    <aside class="single_sidebar_widget search_widget">
                        <div class="calendar">
                           <div class = "pos" id="pos" onclick="myFunction()" style="position:absolute; display:none;">test
                           </div>
                        <div  class="container">
                            <h1>My Calendar</h1>
        
                    			<title>ice-cream</title>
                                <img src = "img/fish.png" alt = "Test Image" width="100" height="100"/>
                                <!--<link href="img/hamburger-icon.png" rel="icon" type="image/ong" />-->
                        		<br></br>
                        		<div id="v-cal">
                        			<div class="vcal-header">
                        				<button class="vcal-btn" data-calendar-toggle="previous">
                        					<svg height="24" version="1.1" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                        						<path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"></path>
                        					</svg>
                        				</button>
                        
                        				<div class="vcal-header__label" data-calendar-label="month">
                        					
                        				</div>
                        				<button class="vcal-btn" data-calendar-toggle="next">
                        					<svg height="24" version="1.1" viewbox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                        						<path d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"></path>
                        					</svg>
                        					
                        				</button>
                        			</div>
                        			<div class="vcal-week">
                        				<span>Mon</span>
                        				<span>Tue</span>
                        				<span>Wed</span>
                        				<span>Thu</span>
                        				<span>Fri</span>
                        				<span>Sat</span>
                        				<span>Sun</span>
                        			</div>
                        			<div class="vcal-body" data-calendar-area="month"></div>
                        		</div>
                                
                        		</div>
                               
                        		<footer>
                        		    <p></p>
                        		    <span class="dot"></span>  <span>Fail To Achieve Your Goal! </span>
                        		    <p></p>
                        		    <span class=" p_dot"></span> <span>Achieve Your Goal! </span>
                        		    <br></br>
                        		
                        		</footer>
                        		
                        
                        	<script src="js/Calendar.js" type="text/javascript"></script>
                        	<script>
                        
                        		window.addEventListener('load', function () {
                        			fishCalendar.init({
                        				disablePastDays: true
                        			});
                        		})
                        	</script>
                        	
                            <script>
                            function myFunction() {
                              var x = document.getElementById("pos");
                              if (x.style.display === "none") {
                                x.style.display = "block";
                              } else {
                                x.style.display = "none";
                              }
                            }
                            </script>
                    </div>
                    </aside>
                    <aside class="single-sidebar-widget tag_cloud_widget">

                        <h1>Tag Clouds</h1>
                        <ul class="list">
                            <li>
                            <?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[0]),">",array_keys($saved_cat)[0],"</a>";?>
                            </li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[1]),">",array_keys($saved_cat)[1],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[2]),">",array_keys($saved_cat)[2],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[3]),">",array_keys($saved_cat)[3],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[4]),">",array_keys($saved_cat)[4],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[5]),">",array_keys($saved_cat)[5],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[6]),">",array_keys($saved_cat)[6],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[7]),">",array_keys($saved_cat)[7],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[8]),">",array_keys($saved_cat)[8],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[9]),">",array_keys($saved_cat)[9],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[10]),">",array_keys($saved_cat)[10],"</a>";?></li>
                            <li><?php echo "<a href=search_results.php?search_key=",urlencode(array_keys($saved_cat)[11]),">",array_keys($saved_cat)[11],"</a>";?></li>
                        </ul>
                        <!--<div  class="container">-->
                        	<footer style = "display: flex;
                                  align-items: center;
                                  justify-content: center;
                                  font-family: serif;
                                font-size: 15px;">
                        		    <br></br>
                                    <a href="#">@XWHZ</a> |
                        			<a href="#">We have made a cool website!</a>
                        	</footer>
                        	<!--</div>-->
                    </aside>
                </div>
            </div>
        </div>
    </div>
    


</section>
<!--================Blog Area =================-->

     <style>
.blue {
  color:	#5F9EA0;
}
.Pink {
  color:	#ffc0cb;
}
.a-xxx { font-size: 10px; }


.h1 { 
    font-family: Georgia, Times, "Times New Roman", serif; font-size: 15px; font-style: normal; font-variant: normal; font-weight: 700; line-height: 26.4px; }
}

.h2 { 
    font-family: Georgia, Times, "Times New Roman", serif; font-size: 10px; }
}

</style>
      <!-- Optional JavaScript -->
      <!-- jQuery first, then Popper.js, then Bootstrap JS -->
      <script src="js/jquery-3.2.1.min.js"></script>
      <script src="js/popper.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="vendors/nice-select/js/jquery.nice-select.min.js"></script>
      <script src="vendors/owl-carousel/owl.carousel.min.js"></script>
      <script src="js/owl-carousel-thumb.min.js"></script>
      <script src="js/jquery.ajaxchimp.min.js"></script>
      <script src="js/mail-script.js"></script>
      <!--gmaps Js-->
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjCGmQ0Uq4exrzdcL6rvxywDDOvfAu6eE"></script>
      <script src="js/gmaps.min.js"></script>
      <script src="js/theme.js"></script>
    </body>
  </html>