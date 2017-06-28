<html>
<head>
<title>Alert Line System</title>
</head>
<body>
<?php
include("connectiondb.php");
$strSQLLA = "SELECT * FROM STATUS_WARNING";
$objParseLA = oci_parse ($objConnect, $strSQLLA);

oci_execute ($objParseLA,OCI_DEFAULT);

while($objResultLA = oci_fetch_array($objParseLA,OCI_BOTH))
{
  $tempcheck = array();
  if($objResultLA["STATUS_WORKING"] == "FAILED")
  	{
      $alert1 = $objResultLA["INVOICE"];
      $alert2 = $objResultLA["LOCATION_CODE"];
      $alert3 = $objResultLA["BA"];
      $alert4 = $objResultLA["DATE_TIME"];
      $alert5 = $objResultLA["BILL_CYCLE"];
      $alert6 = $objResultLA["STATUS_WORKING"];
      $alert7 = $objResultLA["AVG_MONTH_TOTAL"];
      $alert8 = $objResultLA["TOTAL"];

      Line($alert1,$alert2,$alert3,$alert4,$alert5,$alert6,$alert7,$alert8);
  	}//if
}//while
echo "SUCCESS without error";




  function Line($alert1,$alert2,$alert3,$alert4,$alert5,$alert6,$alert7,$alert8){
    include("connectiondb.php");
    $chOne = curl_init();
    curl_setopt( $chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    // SSL USE
    curl_setopt( $chOne, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt( $chOne, CURLOPT_SSL_VERIFYPEER, 0);
    //POST
    curl_setopt( $chOne, CURLOPT_POST, 1);
    // Message
    curl_setopt( $chOne, CURLOPT_POSTFIELDS, "message= Invoice : $alert1 BA : $alert2 Date : $alert3 Location Code : $alert4 Bill Cycle : $alert5 ค่าเฉลี่ยน 3 เดือนย้อนหลัง : $alert7 เดือนล่าสุด : $alert8 มีความผิดปกติ");
    //ถ้าต้องการใส่รุป ให้ใส่ 2 parameter imageThumbnail และimageFullsize
    //curl_setopt( $chOne, CURLOPT_POSTFIELDS, "message=hi&imageThumbnail=http://www.wisadev.com/wp-content/uploads/2016/08/cropped-wisadevLogo.png&imageFullsize=http://www.wisadev.com/wp-content/uploads/2016/08/cropped-wisadevLogo.png");
    // follow redirects
    curl_setopt( $chOne, CURLOPT_FOLLOWLOCATION, 1);
    //ADD header array
    $headers = array( 'Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer gMdLewEuyyFZpP0w8itY1KtkMG23SIjNuQm0Ce1RXhg', );
    curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
    //RETURN
    curl_setopt( $chOne, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec( $chOne );
    //Check error
    if(curl_error($chOne)) //error
    {
      Line($alert1,$alert2,$alert3,$alert4,$alert5);
      //echo 'error:' . curl_error($chOne);
    }
    else //true
    {
        $result_ = json_decode($result, true);
        //Array ( [status] => 200 [message] => ok )
        //print_r($result_);die;
        echo "status : ".$result_['status']; echo "message : ". $result_['message'];
        $tempcheck['data'] = array('status_result'  =>  $result_['status'],
                                   'invoice_result' =>  $alert1);
        //Close connect

        curl_close( $chOne );


        foreach ($tempcheck as $value)
        {
          if($value['status_result']=='200')
            {
              $strSQLLB = "UPDATE STATUS_WARNING SET STATUS_WORKING = 'SUCCESS' WHERE STATUS_WORKING = 'FAILED' and INVOICE = '" .$value['invoice_result']. "'";
              $objParseLB = oci_parse($objConnect , $strSQLLB);
              $objExecuteLB = oci_execute($objParseLB, OCI_DEFAULT);
              
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  
			  oci_commit($objConnect);
            }//if
        }//foreach
oci_close($objConnect);
include("comment.php");
    }//else
  }//function
?>
</body>
</html>
