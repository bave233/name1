<?php
include("connectiondb.php");
/////////////เฉพาะ M700/////////////
$strSQL = "SELECT ACCOUNT_NUM FROM ACCOUNTATTRIBUTES WHERE HOME_LOCATION_CODE = 'M700'";
$objParse = oci_parse ($objConnect, $strSQL);
oci_execute ($objParse,OCI_DEFAULT);
/////////////เฉพาะ M700/////////////

while($objResult = oci_fetch_array($objParse,OCI_BOTH))
{
  $ACCOUNT_NUM = $objResult["ACCOUNT_NUM"];
              /////////////เดือนล่าสุด/////////////
              $strSQL2 = "SELECT * FROM
                                    (
                                    SELECT B.ACCOUNT_NUM, B.INVOICE_NUM, B.BILL_DTM,
                                    ROUND(SUM( (B.INVOICE_NET_MNY+B.INVOICE_TAX_MNY)+
                                      (CASE  WHEN B.ADJUSTMENTS_MNY > 0 THEN  B.ADJUSTMENTS_MNY *1.07
                                      ELSE  0
                                    END ))/10000000,2) AS TOTAL ,
                                    AA.HOME_LOCATION_CODE, AA.BILL_CYCLE
                                    FROM BILLSUMMARY B ,ACCOUNTATTRIBUTES AA
                                    WHERE AA.ACCOUNT_NUM = B.ACCOUNT_NUM
                                    AND B.ACCOUNT_NUM = '$ACCOUNT_NUM'
                                    AND B.BILL_STATUS IN (1,7,8)
                                    AND AA.HOME_LOCATION_CODE = 'M700'
                                    AND B.INVOICE_NUM <> 'XXXXX'
                                    GROUP BY B.ACCOUNT_NUM,B.INVOICE_NUM,B.BILL_DTM ,
                                    B.ACTUAL_BILL_DTM  ,B.PAYMENT_DUE_DAT,B.BILL_SETTLED_DTM ,B.INVOICE_NET_MNY, B.ADJUSTMENTS_MNY,
                                    B.INVOICE_TAX_MNY,AA.HOME_LOCATION_CODE, AA.BILL_CYCLE ,B.BILL_SEQ ,B.BALANCE_OUT_MNY
                                    ORDER BY B.BILL_SEQ DESC )
                                    WHERE ROWNUM = 1";
              $objParse2 = oci_parse($objConnect , $strSQL2);
              oci_execute($objParse2, OCI_DEFAULT);
              /////////////เดือนล่าสุด/////////////
              while($objResult2 = oci_fetch_array($objParse2,OCI_BOTH))
              {

                     $ACCOUNT_NUM2 = $objResult2["ACCOUNT_NUM"];
                     $INVOICE_NUM2 = $objResult2["INVOICE_NUM"];
                     $BILL_DTM2 = $objResult2["BILL_DTM"];
                     $TOTAL2 = $objResult2["TOTAL"];
                     $HOME_LOCATION_CODE2 = $objResult2["HOME_LOCATION_CODE"];
                     $BILL_CYCLE2 = $objResult2["BILL_CYCLE"];
                     $V_AVG_MONEY = 0;
                     $V_AVG_MONTH = 0;


                     ///////////////Select ข้อมูล 1 เดือนของ Table ตัวเอง///////////////
                     $strSQL4 = "SELECT INVOICE,BA FROM  (
                                SELECT  ROW_NUMBER() OVER (PARTITION BY A.BA ORDER BY A.DATE_TIME DESC ) RANK, A.*
                                FROM ABS_NEW A WHERE BA = '$ACCOUNT_NUM2'
                              ) WHERE RANK <=1 ";
                     $objParse4 = oci_parse($objConnect , $strSQL4);
                     oci_execute($objParse4, OCI_DEFAULT);
                     ///////////////Select ข้อมูล 1 เดือนของ Table ตัวเอง///////////////

                     while($objResult4 = oci_fetch_array($objParse4,OCI_BOTH))
                     {
                       $INVOICE_NUM4 = $objResult4["INVOICE"];
                       $ACCOUNT_NUM4 = $objResult4["BA"];

                       if ($INVOICE_NUM2 != $INVOICE_NUM4 AND $ACCOUNT_NUM2 == $ACCOUNT_NUM4)
                       {

                         ///////////////Select ข้อมูล 3 เดือน้ยอนหลังมาหา avg และ %///////////////
                         $strSQL3 = "SELECT t1.* FROM
                                   (
                                        SELECT round(SUM(TOTAL)/COUNT(BA)*50/100,2) AS A,COUNT(BA) AS B FROM (
                                      SELECT  ROW_NUMBER() OVER (PARTITION BY A.BA ORDER BY A.DATE_TIME DESC ) RANK, A.*
                                      FROM ABS_NEW A WHERE BA = '$ACCOUNT_NUM2'
                                  ) WHERE RANK <=3  ) T1
                                  GROUP BY A,B
                                  having B = 3";
                         $objParse3 = oci_parse($objConnect , $strSQL3);
                         oci_execute($objParse3, OCI_DEFAULT);
                         ///////////////Select ข้อมูล 3 เดือน้ยอนหลังมาหา avg และ %///////////////

                         while($objResult3 = oci_fetch_array($objParse3,OCI_BOTH))
                         {

                            $AVG_MONTH_TOTAL = $objResult3["A"];
                           if ($TOTAL2 < $AVG_MONTH_TOTAL)
                           {
                             ///////////////Insert ข้อมูลที่มีปัญหา///////////////
                             $strSQL6 = "INSERT INTO RBMALERTDB.STATUS_WARNING (INVOICE,BA,LOCATION_CODE,DATE_TIME,BILL_CYCLE,STATUS_WORKING,AVG_MONTH_TOTAL,TOTAL,WARNING_DT,LAST_DT,STATUS) VALUES ('$INVOICE_NUM2','$ACCOUNT_NUM','$HOME_LOCATION_CODE2','$BILL_DTM2','$BILL_CYCLE2','FAILED','$AVG_MONTH_TOTAL','$TOTAL2',SYSDATE,SYSDATE,'OPEN')";
                             $objParse6 = oci_parse($objConnect , $strSQL6);
                             $objExecute6 = oci_execute($objParse6, OCI_DEFAULT);
                             oci_commit($objConnect);
                             ///////////////Insert ข้อมูลที่มีปัญหา///////////////

                             ///////////////Insert ข้อมูลเดือนล่าสุดจาก Server หลักมาเก็บ///////////////
                             $strSQL99 = "INSERT INTO RBMALERTDB.ABS_NEW (INVOICE,LOCATION_CODE,BA,DATE_TIME,BILL_CYCLE,TOTAL) VALUES ('$INVOICE_NUM2','$HOME_LOCATION_CODE2','$ACCOUNT_NUM2','$BILL_DTM2','$BILL_CYCLE2','$TOTAL2') ";
                             $objParse99 = oci_parse($objConnect , $strSQL99);
                             $objExecute99 = oci_execute($objParse99, OCI_DEFAULT);
                             oci_commit($objConnect);
                             ///////////////Insert ข้อมูลเดือนล่าสุดจาก Server หลักมาเก็บ///////////////

                             echo "$ACCOUNT_NUM2";
                             echo "<br>";
                             echo "$INVOICE_NUM2";
                             echo "<br>";
                             echo "less";
                             echo "<br>";
                           }
                           else {
                             ///////////////Insert ข้อมูลเดือนล่าสุดจาก Server หลักมาเก็บ///////////////
                             $strSQL88 = "INSERT INTO RBMALERTDB.ABS_NEW (INVOICE,LOCATION_CODE,BA,DATE_TIME,BILL_CYCLE,TOTAL) VALUES ('$INVOICE_NUM2','$HOME_LOCATION_CODE2','$ACCOUNT_NUM2','$BILL_DTM2','$BILL_CYCLE2','$TOTAL2') ";
                             $objParse88 = oci_parse($objConnect , $strSQL88);
                             $objExecute88 = oci_execute($objParse88, OCI_DEFAULT);
                             oci_commit($objConnect);
                             ///////////////Insert ข้อมูลเดือนล่าสุดจาก Server หลักมาเก็บ///////////////

                             echo "$ACCOUNT_NUM2";
                             echo "<br>";
                             echo "$INVOICE_NUM2";
                             echo "<br>";
                             echo "over";
                             echo "<br>";
                           }
                         }
                       }
                     }
              }
}
echo "SUCCESS";
echo "<br>";
oci_close($objConnect);
include("oracle.php"); //แจ้งเตือนไลน์///
?>
