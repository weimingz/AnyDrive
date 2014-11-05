<?php 
  require('cookie.php');

 ?>
        
<!DOCTYPE html>
<!-- saved from url=(0029)http://bootswatch.com/united/ -->
<html lang="en">
<?php include 'head.php'; ?>
<?php
// set deafult varible name

$con = mysqli_connect('127.0.0.1:3306','root');
// Check connection
if (mysqli_connect_errno()){
  die('Could not connect: ' . mysqli_connect_error());
} 
if(!mysqli_select_db($con, 'AnyDrive')) {
  if (mysqli_query($con, "CREATE DATABASE AnyDrive")){

  } else {
    echo "Error creating database: " . mysqli_error();
  }
}

function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}

$feedback = '';
$action_str = 'action';
$getFieldEdit_str = 'edit';
$getFieldDelete_str = 'delete';


$isUpdate_str = 'isUpdate';
$carID_str = "carID";
$allCarID_query = "SELECT carID FROM car";
$allCarID_result = mysqli_query($con, $allCarID_query);

$carID_set = array();
while(($row =  mysqli_fetch_assoc($allCarID_result))) {
    $carID_set[] = $row[$carID_str];
}

$copyNum_str = "copyNum";
$userEmail_str = "userEmail";
$bookingTime_str = "bookingTime";
$collectDate_str = "collectDate";
$returnDate_str = "returnDate";
$cost_str = "cost";

$carID_data = $copyNum_data = $userEmail_data = $bookingTime_data = $collectDate_data = $returnDate_data = $cost_data = '';
$carID_Err = $copyNum_Err = $userEmail_Err = $bookingTime_Err = $collectDate_err = $returnDate_err = $cost_err = '';

$isInsert = False;
$isUpdate = False;
$isGet = False;
$isOverwrite = false;

$Err_Required_Field = 'Required field!';

if ($_SERVER["REQUEST_METHOD"] == "GET") {

  if($_GET[$isUpdate_str] == "true"){
    $isUpdate = True;
  }

  if($_GET[$action_str] == $getFieldEdit_str){
      $carID_data = $_GET[$carID_str];
      $copyNum_data = $_GET[$copyNum_str];
      $bookingQuery = "SELECT * FROM booking ".
      " WHERE bookingID = '$carID_data' AND copyNum = '$copyNum_data' AND userEmail = '$userEmail_data' AND bookingTime='$bookingTime_data'
      ";
      $bookingResult = mysqli_query($con, $bookingQuery);

      $row = mysqli_fetch_assoc($bookingResult);

      $carID_data = $row[$carID_str];
      $copyNum_data = $row[$copyNum_str];
      $bookingTime_data = $row[$bookingTime_str];
      $userEmail_data = $row[$userEmail_str];
      $collectDate_data = $row[$collectDate_str];
      $returnDate_data = $row[$returnDate_str];
      $cost_data = $row[$cost_str];


  } else if($_GET[$action_str] == $getFieldDelete_str){
      $carID_data = $_GET[$carID_str];
      $copyNum_data = $_GET[$copyNum_str];
      $bookingTime_data = $_GET[$bookingTime_str];
      $userEmail_data = $_GET[$userEmail_str];

      $deleteBooking = "DELETE FROM copy WHERE carID = '$carID_data' AND copyNum = '$copyNum_data' ";
      $carResult = mysqli_query($con, $deleteBooking);
      $feedback = "copy deleted successfully";
  }


} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    if($_POST[$isUpdate_str] == 'true'){
      $isOverwrite  = true;
      $isUpdate = true;
    }
    $isInsert = TRUE;
    if (empty($_POST[$carID_str])) {
      $carID_Err = $Err_Required_Field;
      $isInsert = False;
    } else {
      $carID_data = test_input($_POST[$carID_str]);
    }

    if (empty($_POST[$copyNum_str])) {
      $copyNum_Err = $Err_Required_Field;
      $isInsert = False;
    } else {
      $copyNum_data = test_input($_POST[$copyNum_str]);
    }   

    if (empty($_POST[$bookingTime_str])) {
      $bookingTime_Err = $Err_Required_Field;
      $isInsert = False;
    } else {
      if(test_input($_POST[$bookingTime_str]) == "yes"){
        $bookingTime_data = 1;
      } else {
        $bookingTime_data = 0;
      }
      
    }       

    if (empty($_POST[$userEmail_str])) {
      $userEmail_Err = $Err_Required_Field;
      $isInsert = False;
    } else if(!is_int(intval($_POST[$userEmail_str])) or intval($_POST[$userEmail_str]) < 0){
      $userEmail_Err = "userEmail must be positive!";
      $isInsert = False;
    } else {
      $userEmail_data = test_input($_POST[$userEmail_str]);
    }

   
   
}

if($isInsert){
  $sql = "SELECT carID FROM copy where carID='$carID_data' AND copyNum ='$copyNum_data'";
  $result = mysqli_query($con, $sql);

  if(!$isOverwrite) {
      
    $insertCar = "INSERT INTO copy (carID, copyNum, bookingTime, userEmail)
      VALUES ('$carID_data','$copyNum_data', '$bookingTime_data', '$userEmail_data')";

    if (mysqli_num_rows($result) > 0) {
      $carID_Err = "your car copy has been registered";
    } else {
      if ( $con->query($insertCar) === TRUE) {
        $feedback = "New record created successfully";
        $carID_data = $copyNum_data = $bookingTime_data = $userEmail_data ='';
      } else {  
        $feedback = "Error: " . $insertCar . "<br>" . $con->error;
      }
    } 
  } else {
    $updateCar = "UPDATE copy SET bookingTime='$bookingTime_data', userEmail='$userEmail_data'" .
     "WHERE carID='$carID_data' AND copyNum ='$copyNum_data'";
     if ( $con->query($updateCar) === TRUE) {
        $feedback = "New record updated successfully";
      } else {  
        $feedback = "Error: " . $updateCar . "<br>" . $con->error;
      }
  }


}


// query 

$query = "SELECT * FROM copy";
$carListResult = mysqli_query($con, $query);


?>
<body>
<?php include 'navigation.php'; ?>

 <div class="container mainBody">
     <div class="page-header" id="banner">
      <h1>Car Copy Info<small>&nbsp<?php echo $feedback;?></small></h1>
        <form method="post" class="form-horizontal">
          <br>
          <div class="form-group">
            <label for="carID" class="col-lg-2 control-label">Car ID*</label>
            <div class="col-lg-6 ">
            <select 
            <?php 
                if($isUpdate){
                  echo "disabled";
                }
            ?>
            type="text" name="carID" class="form-control" id="carID" >
                <?php
                  for($x=0;$x<count($carID_set);$x++) {

                  echo "<option value='$carID_set[$x]' ";
                  if($carID_set[$x] == $carID_data) {
                    echo "selected='selected'";
                  }
                  echo ">" . $carID_set[$x] . "</option>"; 
                }
                ?>
            </select>
            <?php 
                if($isUpdate){
                  echo "<input type='hidden' name='carID' value=". $carID_data  . ">";
                }
            ?>

            </div>
            <div class = "col-lg-4">
                <span class="text-danger"><?php echo $carID_Err;?></span>
            </div>
          </div>

          <div class="form-group">
            <label for="copyNum" class="col-lg-2 control-label">Copy Number*</label>
            <div class="col-lg-6 ">
              <input type="text" name="copyNum"
              <?php 
                if($isUpdate){
                  echo "readonly";
                }
              ?>
              class="form-control" id="copyNum" value="<?php echo $copyNum_data;?>" >
              
            </div>
            <div class = "col-lg-4">
                <span class="text-danger"><?php echo $copyNum_Err;?></span>
            </div>
          </div>


          <div class="form-group">
            <label for="bookingTime" class="col-lg-2 control-label">availability*</label>
            <div class=" col-lg-6">
              <label class="radio-inline">
                <input class='btn btn-deafult radio-inline'type="radio" name="bookingTime" value="yes" 
                  <?php 
                    if($bookingTime_data) {
                      echo "checked";
                    }
                   ;?>
                > 
                <span>Yes</span>
              </label>
              <label class="radio-inline">
                <input class='btn btn-deafult radio-inline'type="radio" name="bookingTime" value="no" 
                <?php 
                    if(!$bookingTime_data) {
                      echo "checked";
                    }
                   ;?>
                > 
                <span>No</span>
              </label>
            </div>
            <div class = "col-lg-4">
                <span class="text-danger"><?php echo $bookingTime_Err;?></span>
            </div>
          </div>


          <div class="form-group">
            <label for="userEmail" class="col-lg-2 control-label">Start Date of Service</label>
            <div class=" col-lg-6">
              <input name="userEmail"  value="<?php echo $userEmail_data;?>" placeholder="Select Date" class="form-control" data-date-format="YYYY-MM-DD" id='userEmail'>
            </div>
            <div class = "col-lg-4">
                <span class="text-danger"><?php echo $userEmail_Err;?></span>
            </div>
          </div>


          <!-- hide class for submit isUpdate -->
          <input type="hidden" name='isUpdate' value= 
          <?php
          if($isUpdate){
            echo 'true';
          } else {
            echo 'false';
          }
          ?>
          >
          
          <div class="form-group">
            <div class="col-lg-10 col-lg-offset-2">
              <button type="submit" class="btn btn-primary">
                <?php
                if($isUpdate){
                  echo "update";
                } else {
                  echo "submit";
                }
                ?>
              </button>
            </div>
          </div>

        </form>
     </div>


<div class="page-header" id="banner">
      
  <div class="tab-pane fade in active" id="Open">
    <!-- Open Gig -->  
    <div class="panel  panel-default panel-primary">
      <!-- Default panel contents -->
      <div class="panel-heading"><h2>Current Car Copy List</h2></div>
      <div class="panel-body">
      </div>

      <!-- Table -->
      <!-- open gigs -->
      <div class="table-responsive">
        <table class='table table-hover' border="0">
          <tr>
            <th>Car ID</th>
            <th>Copy Number</th>
            <th>Availability</th>
            <th>Start Date Of Service</th>
            <th>Action</th>
          </tr>
         
          
            <?php
            if (mysqli_num_rows($carListResult) > 0) {
            // output data of each row
              while($row = mysqli_fetch_assoc($carListResult)) {
                echo "<tr>";
                echo "<td>".$row[$carID_str]."</td>";
                echo "<td>".$row[$copyNum_str]."</td>";
                echo "<td>";
                if($row[$bookingTime_str] == 1){
                  echo "Yes";
                } else {
                  echo "No";
                }
                
                echo "</td>";
                echo "<td>".$row[$userEmail_str]."</td>";

                echo "<td><a href='?isUpdate=true&action=edit&carID=". $row[$carID_str] . "&copyNum=". $row[$copyNum_str].
                "'><button class='btn btn-primary btn-sm col-xs-offset-1 col-sm-4'>edit</button></a>";
                echo "<a href='?action=delete&carID=". $row[$carID_str] . "&copyNum=". $row[$copyNum_str].
                "'><button class='btn btn-primary btn-sm col-xs-offset-1 col-sm-4'>delete</button></a></td>";

                echo "</tr>";
                
              }
            } else {
              echo "<tr><td colspan='7'><h4> No Car Copy bookingTime</h4></td></tr>";
            }
          ?>
        
          </table>
        </div>
      </div>
    </div>    
  </div>   
 

</div><!--body part-->



<?php include 'footer.php'; ?>  
<script>
  $(function () {
      $('#userEmail').datetimepicker({
        pickTime: false
      });

      $('#userEmail').datetimepicker();
    

  });
</script>

</body>


</html>

