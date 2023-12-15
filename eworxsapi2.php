<?php   
require 'vendor/autoload.php'; 
require 'Eworxsmysql.php';
require 'fpdf.php';
/*require("phpToPDF.php");*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__.'/html2pdf/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;

use mikehaertl\wkhtmlto\Pdf;
$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
 //slim application routes 


 

$app->post('/AddVisit', function($request, $response, $args) {
    add_visit($request->getParsedBody(),$request->getUploadedFiles()['fileName']);
});


$app->run(); 
  



function add_visit($data,$data1){
	$db = connect_db(); 
    $emp_id=$data['employee_id'];
    $current_plan='';

    if($data['company_id']=="")
    { 

    	
			
			echo "hello";die;
            //log code end
	} 
  else 
   { 
	        $datetime = date("Y-m-d H:i:s");   
			$visit_date = date("Y-m-d",strtotime($data['visit_date'])); 
			$departure_time=date("H:i:s",strtotime($data['departure_time'])); 
			$arrival_time = date("H:i:s",strtotime($data['arrival_time'])); 
	 	$duree=2;
		

			$distance=10;
		/*	$sqlInsert = "insert into client_visites(employee_id,company_id,visit_date,client_name,visit_address,departure_time,arrival_time,duration,distance,created_at,IMAGE)"
		. " VALUES('".$data['employee_id']."','".$data['company_id']."','".$visit_date."','".$data['client_name']."','".$data['visit_address']."', '".$departure_time."','".$arrival_time."','".$duree."','".$distance."','".$datetime."','".$data['image']."')";

		echo $sqlInsert;die;
			$exeInsert = $db->query($sqlInsert);
			$last_id = $db->insert_id;*/
			


$lid='';
$procedureName = 'insertclientvisit';
$procedure = "CALL insertclientvisit('".$data['employee_id']."','".$data['company_id']."','".$data['client_name']."','".$visit_date."','".$data['visit_address']."', '".$departure_time."','".$arrival_time."','".$duree."','".$distance."','".$datetime."',@id)";
$results1 = $db->query($procedure);
$results2 = $db->query("SELECT @id");
$num_rows = $results2->num_rows;
if ($num_rows > 0) {

    while($row = $results2->fetch_object())
    {
    $lid= $row->{"@id"};

    }
}

echo $lid;
	} 
  

} 




?>
