<html>
<head>
<title>Alert Line System</title>
</head>
<body>
<?php
include("index.php");
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
?>
</body>
</html>
