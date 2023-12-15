<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

require 'dbconfig.php';
session_start();
$company_id = $_SESSION["companyid"];
$emp_id= $_REQUEST['emp_id'];
$tempName = "quick_book.csv";
$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $tempName);
$fileName = mb_ereg_replace("([\.]{2,})", '', $file);
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=".$fileName);
$db=db_connect();
$sql = "SELECT client_visites.id as clientvisitesid,client_visites.client_name,company_clients.due_date_range,client_visites.duration,client_visites.distance,workers.work_rate AS emp_work_rate,workers.mileage_rate AS emp_mileage_rate,company_clients.email AS client_email,company_clients.work_rate AS client_work_rate,company_clients.mileage_rate AS client_mileage_rate,client_visites.visit_date,company_clients.postal_code AS ca_postal, workers.postal_code AS ic_postal FROM client_visites JOIN workers ON(client_visites.employee_id=workers.id) JOIN company_clients ON(client_visites.company_id=company_clients.id) where company_clients.company_id = $company_id ORDER BY client_visites.id DESC";


$exe = $db->query($sql);
$resultData = $exe->fetch_all(MYSQLI_ASSOC);
$db = null;
$data=array();
$count=1000;
    foreach ($resultData as $key => $value)
    {
    	 $count=$count+1;
    	 $due_days = $value['due_date_range'];
         $due_date = $value['visit_date'];
		  if($due_days>0)
		  {
			$due_days = "+".$due_days." day";
			$due_date = strtotime($due_days, strtotime($due_date));
			$due_date = date("Y-m-d", $due_date);
		  }
         $distance=(float)$value['distance']*2;
         $amount=$distance*(float)$value['client_mileage_rate'];
         $client_due_bal = (float)$amount+(float)$value['client_work_rate']*(float)$value['duration'];
	     $data[$key]['id']= $count;	 
	     $data[$key]['client_name']= $value['client_name'];
	     $data[$key]['visit_date']= $value['visit_date'];

       
         $data[$key]['ca_postal']= $value['ca_postal'];
         $data[$key]['client_email']= $value['client_email'];
          $data[$key]['ic_postal']= $value['ic_postal'];
	     $data[$key]['due_date']= $due_date;


	      $data[$key]['terms']= '';
	     

            if($value['due_date_range']>0)
                   {
                   $data[$key]['terms']='Net '.$value['due_date_range'];
                   }
                   else
                   {
                    $data[$key]['terms']='Due on receipt';
                    
                   }
	       $data[$key]['location']= '-';
         $data[$key]['memo']= '-';
         $data[$key]['item']= array('Field Service','TravelTime');
          
	     $data[$key]['item_desc']= 'Field Service '.$value['visit_date'].'-'.$value['client_name'];

	       $data[$key]['item_quantity']= array($value['duration'],$value['distance']);
	        $data[$key]['item_rate']= array($value['client_work_rate'],$value['client_mileage_rate']);
	       $data[$key]['item_amount']= $client_due_bal;
	       $data[$key]['service_date']= $value['visit_date'];

   }

function outputCSV($data, $csvHeader) {
	$output = fopen("php://output", "w");  
	foreach ($csvHeader as $rowheader)
	fputcsv($output, $rowheader);  
	foreach ($data as $row)

if(count($row['item'])>1)
{
   foreach ($row['item'] as $key12=>$row1)

{
      if($key12==0){
		if($row['id'] || $row['client_name']|| $row['visit_date']|| $row['due_date']|| $row['terms']|| $row['location']|| $row['memo']|| $row['item']|| $row['item_desc']|| $row['item_quantity']|| $row['item_rate']|| $row['item_amount']){
			fputcsv($output, array($row['id'],$row['client_name'],$row['visit_date'],$row['due_date'],$row['terms'],$row['location'],$row['memo'],$row1,$row['item_desc'],$row['item_quantity'][$key12],$row['item_rate'][$key12],$row['item_quantity'][$key12]*$row['item_rate'][$key12],$row['service_date'],$row['client_email']))."<br/>";
		}
	}
	else
	{
		if($row['id'] || $row['client_name']|| $row['visit_date']|| $row['due_date']|| $row['terms']|| $row['location']|| $row['memo']|| $row['item']|| $row['item_desc']|| $row['item_quantity']|| $row['item_rate']|| $row['item_amount']){

			$mil_desc='Travel time '.$row['ic_postal'].'  To  '.$row['ca_postal'];
			fputcsv($output, array($row['id'],'','','','','','',$row1,$mil_desc,$row['item_quantity'][$key12]*2,$row['item_rate'][$key12],$row['item_quantity'][$key12]*2*$row['item_rate'][$key12],'',''))."<br/>";
		}	
	}
	
}




}
else
{
	fputcsv($output, $row)."<br/>";
}
	
// fputcsv($output, array('All data in this file is for sample purposes only.','','','','','','','','','','','',''))."<br/>";
// fputcsv($output, array('* Required column','','','','','','','','','','','',''))."<br/>";

// fputcsv($output, array('NOTE: You must turn on "Custom transaction numbers" in Accounts and Settings or your invoice numbers will be replaced by standard QuickBooks invoice numbers.','','','','','','','','','','','',''))."<br/>";
fclose($output);



}
$csvHeader = array(			
 array('*InvoiceNO','*Customer','*InvoiceDate','*DueDate','Terms','Location','Memo','Item(Product/Service)','ItemDescription','ItemQuantity','ItemRate','*ItemAmount','Service Date','Client\'s email address')
 );
outputCSV($data, $csvHeader);

?>