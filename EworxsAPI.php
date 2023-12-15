<?php
require "vendor/autoload.php";
require "Eworxsmysql.php";
require "fpdf.php";
require_once "googlecloudvendor/autoload.php";
use Google\Cloud\Storage\StorageClient;
require __DIR__.'/smtp/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . "/html2pdf/vendor/autoload.php";
use Spipu\Html2Pdf\Html2Pdf;
use mikehaertl\wkhtmlto\Pdf;
$app = new \Slim\App(["settings" => ["displayErrorDetails" => true]]);

//slim application routes
$app->get("/testemail", function ($request, $response, $args) {
testemail();
});  

$app->post("/AddArrivalTime", function ($request, $response, $args) {
add_arrival_time($request->getParsedBody());
});

$app->get("/ImageRenderByVisitId", function ($request, $response, $args) {
image_render_by_visit_id($_REQUEST["visitId"]);
});

$app->post("/PendingVisitStatus", function ($request, $response, $args) {
pending_visits($request->getParsedBody());
});

$app->post("/DeleteVisit", function ($request, $response, $args) {
delete_visit($request->getParsedBody());
});


$app->post("/AddDepartureTime", function ($request, $response, $args) {
    add_departure_time(
        $request->getParsedBody(),
        $request->getUploadedFiles()["attachment"]
    );
});

$app->post("/TestPdf", function ($request, $response, $args) {
    sendInvoiceAddVisit(810);
});

$app->post("/LoginUser", function ($request, $response, $args) {
    login_user($request->getParsedBody());
});


$app->post("/FindLatest", function ($request, $response, $args) {
    find_latest($request->getParsedBody());
});


$app->get("/GetCompanies", function ($request, $response, $args) {
    get_companies($_REQUEST["workerId"]);
});

$app->post("/AddVisit", function ($request, $response, $args) {
    add_visit(
        $request->getParsedBody(),
        $request->getUploadedFiles()["attachment"]
    );
});

$app->post("/SendInvoice", function ($request, $response, $args) {
    entry_visit($request->getParsedBody());
});

$app->get("/GetAllVisits", function ($request, $response, $args) {
    get_visits($_REQUEST["workerId"],$_REQUEST["offset"],$_REQUEST["limit"]);
});

$app->get("/GetVisitDetail", function ($request, $response, $args) {
    get_visit_details($_REQUEST["visit_id"]);
});
$app->post("/StoreVisitImage", function ($request, $response, $args) {
    add_visit_images2(
        $request->getUploadedFiles()["attachment"],
        (int) $request->getParsedBody()["visitId"]
    );
});

$app->get("/GetDistanceMatrix", function ($request, $response, $args) {
    GetDistanceMatrix(
        $_REQUEST["originslat"],
        $_REQUEST["originslng"],
        $_REQUEST["destinationslat"],
        $_REQUEST["destinationslng"]
    );
});

$app->get("/userauthorizationbyempid", function ($request, $response, $args) {
    userauthorizationbyempid($_REQUEST["emp_id"]);
});

$app->post("/ImageUpload", function ($request, $response, $args) {
    // print_r($_FILES);
    ImageUpload($_FILES);
});



$app->run();



function image_render_by_visit_id($data)
{
    $db = connect_db();
    $visitid=$data;
     if($visitid!= "") 
    {
       $sqlInsert1= "select image from client_visits where id=".$visitid;
       $exeInsert1 = $db->query($sqlInsert1);
       $dataResult11=array();

                                    if ($exeInsert1->num_rows > 0)
                                    {
                                        while ($row = $exeInsert1->fetch_assoc()) 
                                        {
                                            
                                            $dataResult11[] = $row['image'];
                                        }
                                    }

        if (!empty($dataResult11[0])) 
        {
              $image_url = "https://storage.googleapis.com/eworxsmobileattachment/651134a10b4e4Capturea.PNG";

 $image_date= file_get_contents($image_url);
header("Content-Type: image/png");
echo $image_data;

   }
    else{
        $data = ["status" => false, "message" => "Worker id is misssing"];
        echo json_encode($data);
    }
}
}

function delete_visit($data){
       if(userauthorization())
 {
    $db = connect_db();
    $name=$data["visitId"];
     if($data["visitId"] != "") 
    {
       $visit_id= $data["visitId"] ;
      $sqlInsert1= "delete from client_visits where id=".$visit_id." ";
       $exeInsert1 = $db->query($sqlInsert1);
        $data = ["status" => true, "message" => "Visit deleted"];
        echo json_encode($data);
    }
    else{
        $data = ["status" => false, "message" => "Worker id is misssing"];
        echo json_encode($data);
    }
  }
  else 
    {
        
        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);
       
    }
}


function pending_visits($data)
{


   if(userauthorization())
 {
    $db = connect_db();
  
      

 if($data["workerId"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["workerId"] = $data["workerId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "worker id is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "pending visits api";
        logs($api_name,$data["workerId"],0,$input, $output, $platform, $ip_address);
        //log code end
    }

  
  
 else
 {
        $worker_id = $data["workerId"];
        $PendingVisitStatus = 0;
  
            $sqlInsert1= "select client_visits.id,companies.id as company_id,company_clients.client_company_name,client_visits.arrival_time,client_visits.visit_date,client_visits.client_name,client_visits.visit_status from client_visits left join company_clients on company_clients.id=client_visits.company_client_id left join companies on companies.id=company_clients.company_id where client_visits.worker_id=".$worker_id." and client_visits.visit_status=".$PendingVisitStatus." order by client_visits.id desc limit 1";
                
                  
                    $exeInsert1 = $db->query($sqlInsert1);
              $dataResult11 = [];
              $result=0;
              $rows=0;
              
                                    if ($exeInsert1->num_rows > 0)
                                    {
                                        while ($row = $exeInsert1->fetch_assoc()) 
                                        {
                                            
                                            $dataResult11[] = $row;
                                             $rows=1;
                                            
                                            $result=$row['visit_status'];
                                            
                                        }
                                    }
                                   

             if($result==0 &&  $rows==1){
                    $data = [
            "status" => true,
            "data" => $dataResult11,
             "clockSetting" =>1
      
        ];
        echo json_encode($data);
             }
             else{
                    $data = [
            "status" => false,
            "data" => $dataResult11,
             "clockSetting" => 1
      
        ];
        echo json_encode($data);
             }
                                   

          }
 
}
else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $data["visitId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "entry visit";
        logs($api_name,$worker_id,0,$input, $output, $platform, $ip_address);
        //log code end
    }
}




function add_departure_time($data,$data1)
{


   if(userauthorization())
 {
    $db = connect_db();
    if ($data["departureTime"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["departureTime"] = $data["departureTime"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "departure time is required."
            
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
       elseif($data["departureLocation"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["departureLocation"] = $data["departureLocation"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "departure location is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }


       elseif($data["departureLat"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["departureLat"] = $data["departureLat"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "departure latitude is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }

    elseif($data["departureLang"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["departureLang"] = $data["departureLang"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "departure longitude is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }

 elseif($data["visitId"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visitId"] = $data["visitId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "visit id is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }

     elseif($data["clientName"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["clientName"] = $data["clientName"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "client Name is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
  
 else
 {
        $departure_time = $data["departureTime"];
        $departure_lat = $data["departureLat"];
        $departure_lang = $data["departureLang"];
          $visit_id = $data["visitId"];
          $client_name = $data["clientName"];
            $sqlInsert1= "select arrival_time, visit_address,visit_lat,visit_lang,worker_id from client_visits where id=".$visit_id."";
                    $exeInsert1 = $db->query($sqlInsert1);
              $dataResult11 = [];
                                    if ($exeInsert1->num_rows > 0)
                                    {
                                        while ($row = $exeInsert1->fetch_assoc()) 
                                        {
                                            $dataResult11[] = $row;
                                        }
                                    }
$lat1=$dataResult11[0]['visit_lat'];
$lon1=$dataResult11[0]['visit_lang'];
$lat2=$data["departureLat"];
$lon2=$data["departureLang"];


$distancevalue = GetDrivingDistance($lat1,$lat2,$lon1,$lon2);
$limit_distance = 200;

// echo $distancevalue*1609.34;
//  die;
if ($distancevalue*1609.34 > $limit_distance) {
    $data = [
        "status" => false,
        "message" => "Error: Distance exceeds 200 meters. You cannot add a visit."
    ];
    echo json_encode($data);
  //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["distance_meter"] = $distancevalue*1609.34;
        $input_array["limit_distance"] = $limit_distance;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end

}
    



        else
        {

                $departure_time = date("H:i:s", strtotime($data["departureTime"]));
                $arrival_time = date("H:i:s", strtotime($data[0]["arrival_time"]));
                 $duree = 0;
                $minDiff = 0;
            if($departure_time > $arrival_time) 
            {
                $departure = new DateTime($data["departureTime"]);
                $arrival = new DateTime($data["arrivalTime"]);
                $interval = $arrival->diff($departure);
                $duration_hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600) + ($interval->days * 24);
                $formatted_duration = number_format($duration_hours, 2);
                $duree = $formatted_duration;
                $minDiff = round(abs(strtotime($departure_time) - strtotime($arrival_time)) /60,2);
            } 
            else 
            {
             $data = [
                    "status" => false,
                    "message" =>
                        "Departure time should be greater than arrival time.",
                ];
                echo json_encode($data);

                  //log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
                $input_array["departureLocation"] = $data["departureLocation"];
                $input_array["visit_address"] = $data["visit_address"];
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                $output = json_encode($data);
                $api_name = "add departure time api";
                logs($api_name,'',0,$input, $output, $platform, $ip_address);
                //log code end
                        return;
            }





                $distance = distance_calculator(
                                $dataResult11[0]['worker_id'],
                                $data["departureLat"],
                                $data["departureLang"]
                            );
        

                        $sqlInsert= "update client_visits set dep_lat=".$departure_lat.",dep_lang=".$departure_lang.", duration=".$duree.", departure_time='".$data["departureTime"]."' where id=".$visit_id."";
                      
                        $exeInsert = $db->query($sqlInsert);
                        $sqlInsert1= "update client_visits set visit_status='1' where id=".$visit_id."";
                        $exeInsert1 = $db->query($sqlInsert1);
                         $sqlInsert= "update client_visits set distance='".$distance."' where id=".$visit_id."";
                        $exeInsert = $db->query($sqlInsert);

                        if ($exeInsert) 
                        {
                                 $last_id = $visit_id;
         
           $sendinvoicedone=false;  
           $sendinvoicedone= sendInvoiceAddVisit($last_id);
          if($sendinvoicedone)
            {
         
                          $lastInsertedId = $visit_id;

                                if ($lastInsertedId != "") 
                            {
                                  if($data1==null){
                                   
                                    add_visit_images('', $lastInsertedId);
                                  }
                                  else
                                  {
                                   
                                    add_visit_images($data1, $lastInsertedId);
                                  }
                            }
                        }
                              $data171 = [
                                           "status" => true,
                                           "message" => "Departure Time Added Successfully",
                                           "visit_id"=>$lastInsertedId
                                        ];
                             echo json_encode($data171);
                               //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["distance"] = $distance;
        $input_array["duration"] = $duree;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);
        //log code end
                        } 
                        else 
                        {
                          // Handle the query error here
                             $data171 = [
                                           "status" => false,
                                           "message" => "Departure Time Not Added Successfully",
                                           "error" =>$db->error
                                        ];
                             echo json_encode($data171);
                               //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
                         }
          }
 }
}
else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $data["visitId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "entry visit";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
}



function add_arrival_time($data)
{


   if(userauthorization())
 {
    $db = connect_db();
    if ($data["arrivalTime"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["arrivalTime"] = $data["arrivalTime"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "arrival time is required."
            
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
       elseif($data["workerId"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["arrivalTime"] = $data["workerId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "worker id is required."
      
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,$data["workerId"],0,$input, $output, $platform, $ip_address);
        //log code end
    }

      elseif($data["companyClientId"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
      
        $input_array["arrivalTime"] = $data["companyClientId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "client id is required."
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 

elseif($data["clientName"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
      
        $input_array["clientName"] = $data["clientName"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "client name is required."
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 

    elseif($data["visitDate"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
      
        $input_array["visitDate"] = $data["visitDate"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "date is required."
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 


      elseif($data["visitorAddress"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
      
        $input_array["visitorAddress"] = $data["visitorAddress"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "visitor address is required."
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 

    //       elseif($data["duration"] == "") 
    // {
    //     //log code start
    //     $ip_address = get_client_ip();
    //     $platform = funcDeviceType();
    //     $input_array = [];
      
    //     $input_array["duartion"] = $data["duration"];
    //     $inputarrayJSON = json_encode($input_array);
    //     $input = $inputarrayJSON;
    //     //log code end
    //     $data = [
    //         "status" => false,
    //         "message" => "duration is required."
    //     ];
    //     echo json_encode($data);
    //     //log code start
    //     $output = json_encode($data);
    //     $api_name = "add arrival time api";
    //     logs($api_name, $input, $output, $platform, $ip_address);
    //     //log code end
    // } 

    //         elseif($data["duration"] == "") 
    // {
    //     //log code start
    //     $ip_address = get_client_ip();
    //     $platform = funcDeviceType();
    //     $input_array = [];
      
    //     $input_array["duartion"] = $data["duration"];
    //     $inputarrayJSON = json_encode($input_array);
    //     $input = $inputarrayJSON;
    //     //log code end
    //     $data = [
    //         "status" => false,
    //         "message" => "duration is required."
    //     ];
    //     echo json_encode($data);
    //     //log code start
    //     $output = json_encode($data);
    //     $api_name = "add arrival time api";
    //     logs($api_name, $input, $output, $platform, $ip_address);
    //     //log code end
    // } 

            elseif($data["visitorLang"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
      
        $input_array["visitorLang"] = $data["visitorLang"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "visitor lang is required."
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 


          elseif($data["visitorLat"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
      
        $input_array["visitorLat"] = $data["visitorLat"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = [
            "status" => false,
            "message" => "visitor lat is required."
        ];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add arrival time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 

 else
 {
        $arrival_time = $data["arrivalTime"];
        $worker_id = $data["workerId"];
        $company_client_id = $data["companyClientId"];
        $client_name = $data["clientName"];
        $date = $data["visitDate"];
        $location = $data["visitorAddress"];
        $visitor_lat = $data["visitorLat"];
        $visitor_lang = $data["visitorLang"];
        // $duration = $data["duration"];

        $mil_sta=0;
        $sqlSelect ="select mileage_status from companies left join company_clients on (company_clients.company_id=companies.id) where  company_clients.id =".$company_client_id; 
// echo $sqlSelect;die;

            $exeSelectMileageStatus = $db->query($sqlSelect);
            if ($exeSelectMileageStatus->num_rows > 0)
                        {
                            while ($row = $exeSelectMileageStatus->fetch_assoc()) 
                            {
                              $mil_sta=$row['mileage_status'];
                            }
                        }

if($mil_sta==1){
         $distance = distance_calculator(
                $data["workerId"],
                $data["visitorLat"],
                $data["visitorLang"]
            );
}
else
{
    $distance=0;
}



$companyclientworkrate='';
$companymileagerate='';
$companyclientworkRateSql1  ="SELECT work_rate,mileage_rate FROM `company_clients`  WHERE `id` = " . $company_client_id;

                          // echo $invoiceSql1;

                        $resultcclientworkrate1 = $db->query($companyclientworkRateSql1);
$dataResultwork1=array();

  if ($resultcclientworkrate1->num_rows > 0)
                        {
                            while ($row = $resultcclientworkrate1->fetch_assoc()) 
                            {
                                $dataResultwork1[] = $row;
                            }
                        }


$companyclientworkrate=$dataResultwork1[0]['work_rate'];
$companymileagerate=$dataResultwork1[0]['mileage_rate'];


$workerworkrate='';
$workermileagerate='';
$workerworkRateSql1 ="SELECT work_rate,mileage_rate FROM `workers`  WHERE `id` = " . $worker_id;

                          // echo $invoiceSql1;

                        $resultworkerworkrate1 = $db->query($workerworkRateSql1);
$dataResultwork2=array();

  if ($resultworkerworkrate1->num_rows > 0)
                        {
                            while ($row = $resultworkerworkrate1->fetch_assoc()) 
                            {
                                $dataResultwork2[] = $row;
                            }
                        }


$workerworkrate=$dataResultwork2[0]['work_rate'];
$workermileagerate=$dataResultwork2[0]['mileage_rate'];


$milstatus='';
$returnmileageStatusSql ="SELECT return_mileage FROM `company_clients`  WHERE `id` = " . $company_client_id;

                          // echo $invoiceSql1;

                        $returnmileageStatus = $db->query($returnmileageStatusSql);
$dataResultmileage=array();

  if ($returnmileageStatus->num_rows > 0)
                        {
                            while ($row = $returnmileageStatus->fetch_assoc()) 
                            {
                                $dataResultmileage[] = $row;
                            }
                        }


$milstatus=$dataResultmileage[0]['return_mileage'];


$milstatusdata='';
$mileageStatusSql ="SELECT mileage_status FROM `companies`  
left join company_clients on company_clients.company_id = companies.id

WHERE company_clients.id = " . $company_client_id;

                          // echo $invoiceSql1;

                        $mileageStatus = $db->query($mileageStatusSql);
$dataResultmileagedata=array();

  if ($mileageStatus->num_rows > 0)
                        {
                            while ($row = $mileageStatus->fetch_assoc()) 
                            {
                                $dataResultmileagedata[] = $row;
                            }
                        }


$milstatusdata=$dataResultmileagedata[0]['mileage_status'];


// echo $distance;die;
 $visitinsertdate=date('Y-m-d H:i:s');
        

        $sqlInsert =
            "insert into client_visits(arrival_time,worker_id,company_client_id,client_name,visit_date,visit_address,visit_lat,visit_lang,distance,client_work_rate,client_mileage_rate,worker_work_rate,worker_mileage_rate,return_mileage_status,mileage_status)" .
            " VALUES('".$arrival_time."','".$worker_id."','".$company_client_id."','".$client_name."','" .$date."','".$location."','".$visitor_lat."','" .$visitor_lang."','" .$distance .
            "',".$companyclientworkrate.",".$companymileagerate.",".$workerworkrate.",".$workermileagerate.",".$milstatus.",".$milstatusdata.")";
         
           // echo $sqlInsert;die;
        $exeInsert = $db->query($sqlInsert);
        if ($exeInsert) 
        {
          $lastInsertedId = $db->insert_id;

              $data171 = [
                           "status" => true,
                           "message" => "Arrival Time Added Successfully",
                           "visit_id"=>$lastInsertedId
                        ];
             echo json_encode($data171);

  $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $data;
        $inputarrayJSON = json_encode($input_array);
$input = $inputarrayJSON;
     $output = json_encode($data);
        $api_name = "arrival api logs";
logs($api_name,$worker_id,1, $input, $output, $platform, $ip_address);


        } 
        else 
        {
          // Handle the query error here
             $data171 = [
                           "status" => false,
                           "message" => "Arrival Time Not Added Successfully",
                           "error" =>$db->error
                        ];
             echo json_encode($data171);
         }
  }
}
else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $data["visitId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "entry visit";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
}

 function ImageUpload($FILES)
 {
    $max_size = 100000000;
    $allowed_extensions = ["png", "jpg", "jpeg", "gif", "mp4", "gp3", "webm"];
    $filename = uniqid().$FILES->getClientFilename();
     $extension = strtolower(
        pathinfo($FILES->getClientFilename(), PATHINFO_EXTENSION)
     );
       $storage = new StorageClient([
        'keyFilePath' => 'eworxs-161e114e0e08.json',
    ]);

    $bucketName = 'eworxsmobileattachment';
    
    $bucket = $storage->bucket($bucketName);
   

$file = fopen($FILES->file, 'r');
    $bucket->upload($file, [
        'name' => $filename
    ]);


//$filename = $bucket->object($filename)->signedUrl(time() + 60000000000000000);



return $filename;

    
}




function authorization()
{
    $headers = apache_request_headers();
    if ($headers["Authorization"] != "Cresol123!@#") {
        $response["message"] = "Api key is misssing";
        echo $response->getStatusCode();
        return false;
    } else {
        return true;
    }
}

function add_visit_images2($data, $data1)
{
    if (userauthorization())
     {
        $db = connect_db();
        if ($data == "") 
        {
             $data171 = [
                                "status" => true,
                                "message" => "image should not be empty",
                            ];
                            echo json_encode($data171);
            
           
        }
           if ($data1 == "") 
        {
             $data171 = [
                                "status" => true,
                                "message" => "visit id should not be empty",
                            ];
                            echo json_encode($data171);
            
           
        }
          else
          {
             if ($data->getError() === UPLOAD_ERR_OK) 
            {

                $filename = ImageUpload($data);
                if ($filename) 
                {
                    $t = "";

                    $t = 'https://storage.googleapis.com/eworxsmobileattachment/'.$filename;
                   // $t = $filename;
                    // $t = $filename;
                    //log code start
                    $ip_address = get_client_ip();
                    $platform = funcDeviceType();
                    $input_array = [];
                    $input_array["fileNamedata"] = $filename;
                    $inputarrayJSON = json_encode($input_array);
                    $input = $inputarrayJSON;
                    //log code end
                    $sqlInsert ="update client_visits set image='".$t."' where id=".$data1."";
                    $exeInsert = $db->query($sqlInsert);
                    if ($exeInsert) 
                    {
                      $invoiceSql1 ="SELECT client_visits.id as visit_id,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id,client_visits.pdf_file FROM `client_visits`  JOIN workers ON(client_visits.worker_id=workers.id) JOIN company_clients ON(client_visits.company_client_id=company_clients.id) 
                          JOIN companies ON(companies.id=workers.company_id) WHERE client_visits.id=" . $data1;

                          // echo $invoiceSql1;

                        $resultinvoice1 = $db->query($invoiceSql1);
                        $dataResult1 = [];
                        if ($resultinvoice1->num_rows > 0)
                        {
                            while ($row = $resultinvoice1->fetch_assoc()) 
                            {
                                $dataResult1[] = $row;
                            }
                        }

                        $email1 = "";
                        if($dataResult1[0]["send_invoice_status_client"] == 0)
                        {
                            $email1 = $dataResult1[0]["company_email"];
                        } 
                        else 
                        {
                            $email1 = $dataResult1[0]["email"];
                        }

                        if ($data1) 
                        {
                            $filename = $dataResult1[0]["pdf_file"];
                            $image = $t;
                            sendEmail1($email1, $filename, $image, $data1);
                            $data171 = [
                                "status" => true,
                                "message" => "image added Successfully",
                            ];
                            echo json_encode($data171);
                        }
                        else
                        {
                              $filename = $dataResult1[0]["pdf_file"];
                            $image = $t;
                            sendEmail1($email1, $filename, $image, $data1);
                            $data171 = [
                                "status" => true,
                                "message" => "image not added Successfully",
                            ];
                            echo json_encode($data171);

                        }
                    }

                    //log code start
                    $output = json_encode($data1);
                    $api_name = "add visit images";
                    logs($api_name,'',1, $input, $output, $platform, $ip_address);
                    //log code end
                } 
                else 
                {
                    //log code start
                    $ip_address = get_client_ip();
                    $platform = funcDeviceType();
                    $input_array = [];
                    $input_array["fileNamedata"] = $filename;
                    $inputarrayJSON = json_encode($input_array);
                    $input = $inputarrayJSON;
                    //log code end
                    $data1 = ["status" => true, "message" => "File name error"];
                    echo json_encode($data1);

                    //log code start
                    $output = json_encode($data1);
                    $api_name = "add visit images";
                    logs($api_name,'',0, $input, $output, $platform, $ip_address);
                    //log code end
                }
            }
          }

    } 
    else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["fileNamedata"] = $filename;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add visit images";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
}

function add_visit_images($data, $data1)
{

    if (userauthorization())
     {
        $db = connect_db();

   
     if ($data=='')
     {
        

         $invoiceSql1 =
                        "SELECT client_visits.id as visit_id,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visits`  JOIN workers ON(client_visits.worker_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visits.company_client_id=company_clients.id) WHERE client_visits.id=" .
                        $data1;



                    $resultinvoice1 = $db->query($invoiceSql1);
                    $dataResult1 = [];
                    if ($resultinvoice1->num_rows > 0) {
                        while ($row = $resultinvoice1->fetch_assoc()) {
                            $dataResult1[] = $row;
                        }
                    }

                    /*$email1=$dataResult1[0]['email'];*/
                    $email1 = "";
                    if ($dataResult1[0]["send_invoice_status_client"] == 0) {
                        $email1 = $dataResult1[0]["company_email"];
                    } else {
                        $email1 = $dataResult1[0]["email"];
                    }
                    $currentDateTime = date("Ymdhis");
                    $string = "";
                    $filename = "";
                    $filename =
                        $string . "_invoice_" . $currentDateTime . ".pdf";
                    if ($dataResult1[0]["client_company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["client_company_name"]
                        );
                        $currentDateTime = date("Ymdhis");
                        $filename =
                            $string . "_invoice_" . $currentDateTime . ".pdf";
                    }
                 
                    sendEmail2($email1, $filename);
            $data = [
                            "Status" => true,
                            "Message" => "image added Successfully",
                        ];

 $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $email1;
        $input_array["filename"] = $filename;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);
        //log code end


     }
      elseif($data==null)
      {
        

         


              $invoiceSql1 =
                        "SELECT client_visits.id as visit_id,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visits`  JOIN workers ON(client_visits.worker_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visits.company_client_id=company_clients.id) WHERE client_visits.id=" .
                        $data1;

                        // echo $invoiceSql1;die;

                    $resultinvoice1 = $db->query($invoiceSql1);
                    $dataResult1 = [];
                    if ($resultinvoice1->num_rows > 0) {
                        while ($row = $resultinvoice1->fetch_assoc()) {
                            $dataResult1[] = $row;
                        }
                    }

                    /*$email1=$dataResult1[0]['email'];*/
                    $email1 = "";
                    if ($dataResult1[0]["send_invoice_status_client"] == 0) {
                        $email1 = $dataResult1[0]["company_email"];
                    } else {
                        $email1 = $dataResult1[0]["email"];
                    }
                    $currentDateTime = date("Ymdhis");
                    $string = "";
                    $filename = "";
                    $filename =
                        $string . "_invoice_" . $currentDateTime . ".pdf";
                    if ($dataResult1[0]["client_company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["client_company_name"]
                        );
                        $currentDateTime = date("Ymdhis");
                        $filename =
                            $string . "_invoice_" . $currentDateTime . ".pdf";
                    }
                 
                    sendEmail2($email1, $filename);
            $data = [
                            "Status" => true,
                            "Message" => "image added Successfully",
                        ];

$ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $email1;
        $input_array["filename"] = $filename;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);

       }
     else
     {
       
           
            if ($data->getSize() > 1048576*10) 
            {
               

                $input_array = [];
                $input_array["fileNamedata"] = $filename;
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end
                $data1 = [
                    "status" => false,
                    "message" =>
                        "File size error.file should be less than 10 MB",
                ];
                //log code start
                echo $output = json_encode($data1);
                $api_name = "add visit images";
                logs($api_name,'',0, $input, $output, $platform, $ip_address);
                exit();
                return false;
            } 
            else 
            {
               

                if ($data->getError() === UPLOAD_ERR_OK)
                 {
                     
                    $filename = ImageUpload($data);
                    if ($filename!='') 
                    {
                         
                        $t = "";
                          // $t = 'https://storage.cloud.google.com/eworxsmobileattachment/'.$filename;
                         $t = 'https://storage.googleapis.com/eworxsmobileattachment/'.$filename;
                    // $t = $filename;
                    
                        $sqlInsert =
                            "update client_visits set image='" .
                            $t .
                            "' where id=" .
                            $data1 .
                            "";
                        $exeInsert = $db->query($sqlInsert);
                        if ($exeInsert) 
                        {
 

                            $invoiceSql1 =
                                "SELECT client_visits.pdf_file,client_visits.id as visit_id,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visits`  JOIN workers ON(client_visits.worker_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visits.company_client_id=company_clients.id) WHERE client_visits.id=" .
                                $data1;



                            $resultinvoice1 = $db->query($invoiceSql1);
                            $dataResult1 = [];
                            if ($resultinvoice1->num_rows > 0) 
                            {
                                while ($row = $resultinvoice1->fetch_assoc()) 
                                {
                                    $dataResult1[] = $row;
                                }
                            }

                            /*$email1=$dataResult1[0]['email'];*/
                            $email1 = "";
                            if (
                                $dataResult1[0]["send_invoice_status_client"] ==
                                0
                            ) {
                                $email1 = $dataResult1[0]["company_email"];
                            } else {
                                $email1 = $dataResult1[0]["email"];
                            }

                            if ($data1) 
                            {
                                $currentDateTime = date("Ymdhis");
                                $string = "";
                                $filename = "";
                                $filename =$dataResult1[0]["pdf_file"];
                                    // $string .
                                    // "_invoice_" .
                                    // $currentDateTime .
                                    // ".pdf";
                                if ($dataResult1[0]["client_company_name"] != "")
                                 {
                                    $string = str_replace(
                                        " ",
                                        "",
                                        $dataResult1[0]["client_company_name"]
                                    );

                                    $currentDateTime = date("Ymdhis");
                                     $filename =$dataResult1[0]["pdf_file"];
                                    // $filename =
                                    //     $string .
                                    //     "_invoice_" .
                                    //     $currentDateTime .
                                    //     ".pdf";
                                }

                                $image = $t;
                           
                                //  echo $email1;
                                // echo $filename;
                                //  echo $image;
                               
                                sendEmail1($email1, $filename, $image, $data1);
                               
                                                           }
                        }

                        $data1 = [
                            "Status" => true,
                            "Message" => "image added Successfully",
                        ];
                        //log code start
                        $output = json_encode($data1);
                        $api_name = "add visit images";
                        logs(
                            $api_name,
                            '',1,
                            $input,
                            $output,
                            $platform,
                            $ip_address
                        );
                        //log code end
                    } 
                    else
                     {
                        //log code start
                        $ip_address = get_client_ip();
                        $platform = funcDeviceType();
                        $input_array = [];
                        $input_array["fileNamedata"] = $filename;
                        $inputarrayJSON = json_encode($input_array);
                        $input = $inputarrayJSON;
                        //log code end
                        $data1 = [
                            "Status" => true,
                            "Message" => "File name error",
                        ];
                        //log code start
                        $output = json_encode($data1);
                        $api_name = "add visit images";
                        logs(
                            $api_name,
                            '',0,
                            $input,
                            $output,
                            $platform,
                            $ip_address
                        );
                        //log code end
                    }
                } 

                else 
                {
                    $invoiceSql1 =
                        "SELECT client_visits.id as visit_id,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visits`  JOIN workers ON(client_visits.worker_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visits.company_client_id=company_clients.id) WHERE client_visits.id=" .
                        $data1;

                    $resultinvoice1 = $db->query($invoiceSql1);
                    $dataResult1 = [];
                    if ($resultinvoice1->num_rows > 0) {
                        while ($row = $resultinvoice1->fetch_assoc()) {
                            $dataResult1[] = $row;
                        }
                    }

                    /*$email1=$dataResult1[0]['email'];*/
                    $email1 = "";
                    if ($dataResult1[0]["send_invoice_status_client"] == 0) {
                        $email1 = $dataResult1[0]["company_email"];
                    } else {
                        $email1 = $dataResult1[0]["email"];
                    }
                    $currentDateTime = date("Ymdhis");
                    $string = "";
                    $filename = "";
                    $filename =
                        $string . "_invoice_" . $currentDateTime . ".pdf";
                    if ($dataResult1[0]["client_company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["client_company_name"]
                        );
                        $currentDateTime = date("Ymdhis");
                        $filename =
                            $string . "_invoice_" . $currentDateTime . ".pdf";
                    }

                    sendEmail2($email1, $filename);
                     $data1 = [
                            "Status" => true,
                            "Message" => "image insert succesfully",
                        ];

$ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $email1;
        $input_array["filename"] = $filename;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode($data);
        $api_name = "add departure time api";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);






                }
            }
        }    
     

    } 
    else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["fileNamedata"] = $filename;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = ["Status" => false, "Message" => "Worker Token is misssing"];
        //log code start
        $output = json_encode($data);
        $api_name = "add visit images";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    }
}

function GetDistanceMatrix(
    $originslat,
    $originslng,
    $destinationslat,
    $destinationslng
) {
    //log code start
    $ip_address = get_client_ip();
    $platform = funcDeviceType();
    $input_array = [];
    $input_array["destinationslat"] = $destinationslat;
    $input_array["originslat"] = $originslat;
    $input_array["originslng"] = $originslng;
    $input_array["destinationslng"] = $destinationslng;
    $inputarrayJSON = json_encode($input_array);
    $input = $inputarrayJSON;
    //log code end

// echo $originslat.'--';
//    echo      $destinationslat.'--';
//      echo    $originslng.'--';
//      echo   $destinationslng.'--';

    $dis = GetDrivingDistance(
        $originslat,
        $destinationslat,
        $originslng,
        $destinationslng
    );




    $data = [
        "Status" => true,
        "Message" => "Distance measured",
        "Distance" => $dis,
    ];
    //  echo json_encode($data);*/
    return $dis;

    //log code start
    $output = json_encode($d);
    $api_name = "get distance matrix";
    logs($api_name,'',1, $input, $output, $platform, $ip_address);
    //log code end
}

function GetDrivingDistance($lat1, $lat2, $long1, $long2)
{
    $url =
        "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" .
        $lat1 .
        "," .
        $long1 .
        "&destinations=" .
        $lat2 .
        "," .
        $long2 .
        "&mode=driving&key=AIzaSyArmhvQFhccQQ6MQeo2CpxGatou__6R_pg";
  // echo $url;die;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response, true);

    $dist = $response_a["rows"][0]["elements"][0]["distance"]["value"];
    $dist = $dist / 1000;
    $dist = round($dist, 1);
   
    return $dist;
}

// User Authentication then API Athentication : If User Authentication missing then call API Authentication
function userauthorization()
{
    $headers = apache_request_headers();
    $headerToken = "";
    if (isset($headers["authorization"])) {
        $headerToken = $headers["authorization"];
    } elseif (isset($headers["Authorization"])) {
        $headerToken = $headers["Authorization"];
    }

    if (isset($headers["authorization"]) || isset($headers["Authorization"])) {
        $db = connect_db();
        $sqlUnique =
            "select token  from workers where token='" . $headerToken . "'";
        /* echo $sqlUnique;die;*/
        $result = $db->query($sqlUnique);

        if ($result->num_rows != 0) {
            return true;
        } else {
            $response["Message"] = "User Token is misssing";
            return false;
        }
    } else {
        //$response["Message"] = "User Token is misssing";
        return false;
    }
}

function userauthorizationbyempid($emp_id)
{
    
    $headers = apache_request_headers();
    $headerToken = "";
    if (isset($headers["authorization"])) {
        $headerToken = $headers["authorization"];
    } elseif (isset($headers["Authorization"])) {
        $headerToken = $headers["Authorization"];
    }

  

    if (isset($headers["authorization"]) || isset($headers["Authorization"])) {
        $db = connect_db();
        $sqlUnique =
            "select token  from workers where id='$emp_id' and token='" .
            $headerToken .
            "'";
        /*  echo $sqlUnique; die;*/
        $result = $db->query($sqlUnique);

        if ($result->num_rows != 0) {
            
            return true;
        } else {
            
            $response["Message"] = "User Token is misssing";
            return false;
        }
    } else {
       
        return false;
    }
}

function login_user($data)
{
    $db = connect_db();
    if ($data["email"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $data["email"];
        $input_array["password"] = $data["password"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "email is required.",
            "token" => null,
            "userId" => null,
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "login api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 
    elseif($data["password"] == "")
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $data["email"];
        $input_array["password"] = $data["password"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "Password required.",
            "token" => null,
            "userId" => null,
        ];
        echo json_encode($data);
        
        //log code start
        $output = json_encode($data);
        $api_name = "login api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 
    else 
    {
        $sqlUnique ="select * from workers where email='".$data["email"]."' && status=1";
        $resultUnique = $db->query($sqlUnique);
        $row_cnt = $resultUnique->num_rows;
        if ($row_cnt > 0) 
        {
            $dataResult = [];
            while ($row = $resultUnique->fetch_assoc()) 
            {
                $dataResult[] = $row; // assign each value to array
            }
            $password = $data["password"];
            if ($dataResult[0]["password"] == md5($password)) 
            {
                $companyid = 0;
                if ($dataResult[0]["company_id"]) 
                {
                    $companyid = $dataResult[0]["company_id"];
                }
                $sqlsubscription ="select id from company_subscriptions where company_id='".$companyid."' ";
                $resultsubscription = $db->query($sqlsubscription);
                if ($resultsubscription->num_rows <= 0) 
                {
                    //log code start
                    $ip_address = get_client_ip();
                    $platform = funcDeviceType();
                    $input_array = [];
                    $input_array["email"] = $data["email"];
                    $input_array["password"] = $data["password"];
                    $inputarrayJSON = json_encode($input_array);
                    $input = $inputarrayJSON;
                    //log code end
                
                    $data = [
                        "status" => false,
                        "message" => "Don't have any plan.",
                        "token" => null,
                        "userId" => null,
                    ];
                    echo json_encode($data);

                    //log code start
                    $output = json_encode($data);
                    $api_name = "login api";
                    logs($api_name,'',0, $input, $output, $platform, $ip_address);
                    //log code end
                } 
                else 
                {
                    $Usertoken = md5(uniqid($dataResult[0]["id"], true));
                    $sql ="update workers SET token ='".$Usertoken."' WHERE id = '".$dataResult[0]["id"]."'";
                    $db->query($sql);
                    $db = null;
                    //log code start
                    $ip_address = get_client_ip();
                    $platform = funcDeviceType();
                    $input_array = [];
                    $input_array["email"] = $data["email"];
                    $input_array["password"] = $data["password"];
                    $inputarrayJSON = json_encode($input_array);
                    $input = $inputarrayJSON;
                    //log code end

                    $data = [
                        "status" => true,
                        "token" => $Usertoken,
                        "message" => "Successfully login",
                        "workerId" => $dataResult[0]["id"],
                        "firstName" => $dataResult[0]["first_name"],
                        "lastName" => $dataResult[0]["last_name"],
                        "email" => $dataResult[0]["email"],
                        "workerCompanyId" => $dataResult[0]["company_id"],
                        "address" => $dataResult[0]["local_address"],
                        "attachmentText" =>"Image should be 1 mb",
                        "attachmentImageSize"=>5
                    ];
                    echo json_encode($data);
                    //log code start
                    $output = json_encode($data);
                    $api_name = "login api";
                    logs($api_name,$dataResult[0]["id"],1, $input, $output, $platform, $ip_address);
                    //log code end
                }
            } 
            else
            {
                // log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
                $input_array["email"] = $data["email"];
                $input_array["password"] = $data["password"];
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end

                $data = [
                    "status" => false,
                    "message" => "password wrong",
                    "token" => null,
                    "userId" => null,
                ];
                echo json_encode($data);

                //log code start
                $output = json_encode($data);
                $api_name = "login api";
                logs($api_name,'',0, $input, $output, $platform, $ip_address);
                //log code end
            }
        } 
        else 
        {
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["email"] = $data["email"];
            $input_array["password"] = $data["password"];
            $inputarrayJSON = json_encode($input_array);
            $input = $inputarrayJSON;
            //log code end

            $data = [
                "status" => false,
                "message" => "email not found",
                "token" => null,
                "userId" => null,
            ];
            echo json_encode($data);

            //log code start
            $output = json_encode($data);
            $api_name = "login api";
            logs($api_name,'',0, $input, $output, $platform, $ip_address);
            //log code end
        }
    }
}




function find_latest($data)
{
    $db = connect_db();
    if ($data["deviceType"] == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["deviceType"] = $data["deviceType"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "device type is required.",
           
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "find latest api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 
    elseif($data["version"] == "")
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["version"] = $data["version"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "version required.",
           
        ];
        echo json_encode($data);
        
        //log code start
        $output = json_encode($data);
        $api_name = "find latest api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 
    else 
    {
        $deviceversion=$data["version"];
        // $devicetype=$data["deviceType"];
        if($deviceversion>=1.0 )
        {
                    //log code start
                    $ip_address = get_client_ip();
                    $platform = funcDeviceType();
                    $input_array = [];
                    $input_array["version"] = $data["version"];
                    $inputarrayJSON = json_encode($input_array);
                    $input = $inputarrayJSON;
                    //log code end
                    $data = [
                        "status" => true,
                        "message" => "latest",
                       
                    ];
                    echo json_encode($data);

                    //log code start
                    $output = json_encode($data);
                    $api_name = "find latest api";
                    logs($api_name,'',1, $input, $output, $platform, $ip_address);
                    //log code end
              
            } 
        else
        {
                // log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
            
                $input_array["version"] = $data["version"];
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end

                $data = [
                    "status" => false,
                    "message" => "not latest",
                  
                ];
                echo json_encode($data);

                //log code start
                $output = json_encode($data);
                $api_name = "find latest api"; 
                logs($api_name,'',0, $input, $output, $platform, $ip_address);
                //log code end
       
    }
    }
}



// For Category
function get_companies($worker_id)
{
     if ($worker_id == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $data["email"];
        $input_array["password"] = $data["password"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "Worker Id is required.",
            "token" => null,
            "userId" => null,
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "login api";
        logs($api_name,$worker_id,0, $input, $output, $platform, $ip_address);
        //log code end
    } 
    else
    {  
    $db = connect_db();
    $sql = "SELECT company_clients.id,company_clients.client_company_name as companyClientName, 1 as clockSetting  FROM company_clients JOIN companies ON(company_clients.company_id=companies.id) JOIN workers ON(workers.company_id=companies.id) where company_clients.status=1 && workers.id=".$worker_id."  ORDER BY company_clients.id";

    $exe = $db->query($sql);
    $data = []; // array variable
    if ($result = $db->query($sql)) 
    {
        while ($row = $result->fetch_assoc())
         {
            $data[] = $row; // assign each value to array
        }
    }
    //log code start
    $ip_address = get_client_ip();
    $platform = funcDeviceType();
    $input_array = [];
    $input_array["worker_id"] = $worker_id;
    $inputarrayJSON = json_encode($input_array);
    $input = $inputarrayJSON;
    //log code end

    $db = null;
    $data1 = ["status" => true, "message" => "data retrieved", "data" => $data];
    echo json_encode($data1);

    //log code start
    $output = json_encode($data1);
    $api_name = "get compnaies client by worker id";
    logs($api_name,$worker_id,1, $input, $output, $platform, $ip_address);
    //log code end
  }
}

function add_visit($data, $data1)
{
    $db = connect_db();
    $worker_id = $data["workerId"];
    $visitor_lat = $data["visitorLat"];
    $visitor_lang = $data["visitorLang"];
    $current_plan = "";
    $time_start = microtime(true);
    if (userauthorizationbyempid($emp_id)) 
    {
        if ($data["companyClientId"] == "") 
        {
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["worker_id"] = $data["workerId"];
            $input_array["company_client_id"] = $data["companyClientId"];
            $input_array["departure_time"] = $data["departureTime"];
            $input_array["visit_date"] = $data["visitDate"];
            $input_array["arrival_time"] = $data["arrivalTime"];
            $input_array["visit_date"] = $data["visitDate"];
            $input_array["visitor_address"] = $data["visitorAddress"];
            $input_array["client_name"] = $data["clientName"];
            $input_array["duration"] = $data["duration"];
            $input_array["distance"] = distance_calculator(
                $data["workerId"],
                $data["visitorLat"],
                $data["visitorLang"]

            );
            $inputarrayJSON = json_encode($input_array);
            $input = $inputarrayJSON;
            //log code end
            
            $data = ["status" => false, "message" => "Comapany is required."];
            echo json_encode($data);
            //log code start
            $output = json_encode($data);
            $api_name = "add visit";
            logs($api_name,$data["workerId"],0, $input, $output, $platform, $ip_address);
            //log code end
        } 

        elseif($visitor_lat="")
        {
               $data = ["status" => false, "message" => "Visitor Latitude is required."];
                echo json_encode($data);
        }

        elseif($visitor_lang="")
        {
            
            $data = ["status" => false, "message" => "Visitor Langitude is required."];
                    echo json_encode($data);
        }

        else
        {
            $cclientid = $data["companyClientId"];
            $current_plan = company_current_plan($cclientid);
            $visit_count = company_visit_count($worker_id, $cclientid);
            //condition for self free
            if (intval($current_plan["Data"][0]["id"]) == 41 && $current_plan["Data"][0]["plan_name"] == "Self Free" && intval($current_plan["Data"][0]["plan_type"]) == 1 &&intval($visit_count["Data"][0]["visit_count"]) > 9) 
            {
                //log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
                $input_array["worker_id"] = $data["workerId"];
                $input_array["company_client_id"] = $data["companyClientId"];
                $input_array["departure_time"] = $data["departureTime"];
                $input_array["visit_date"] = $data["visitDate"];
                $input_array["arrival_time"] = $data["arrivalTime"];
                $input_array["visit_date"] = $data["visitDate"];
                $input_array["visitor_address"] = $data["visitorAddress"];
                $input_array["client_name"] = $data["clientName"];
                $input_array["duration"] = $data["duration"];
                $input_array["distance"] = distance_calculator(
                    $data["workerId"],
                     $data["visitorLat"],
                     $data["visitorLang"]
                );
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end

                $data = [
                    "status" => false,
                    "message" =>
                        "unable to add more than 10 visits on current plan",
                    "planName" => $current_plan["Data"][0]["plan_name"],
                ];
                echo json_encode($data);

                //log code start
                $output = json_encode($data);
                $api_name = "add visit";
                logs($api_name,'',0, $input, $output, $platform, $ip_address);
                //log code end
                return;
            }
               //condition for self free
                /*if(intval($current_plan['Data'][0]['id'])==42 && $current_plan['Data'][0]['plan_name']=="Self Paid" && intval($current_plan['Data'][0]['plan_type'])==1 &&intval($visit_count['Data'][0]['visit_count'])>9)   
                    { 
                     $data= array('Status'=>false ,"Message"=>"unable to add more than 10 visits on current plan","Plan_Name"=>$current_plan['Data'][0]['plan_name'],);
                     echo json_encode($data);
                     return;
                    } */

                // else insert new items here
                $datetime = date("Y-m-d H:i:s");
                $visit_date = date("Y-m-d", strtotime($data["visitDate"]));
                $departure_time = date("H:i:s", strtotime($data["departureTime"]));
                $arrival_time = date("H:i:s", strtotime($data["arrivalTime"]));
                $duree = 0;
                $minDiff = 0;
            if($departure_time > $arrival_time) 
            {
                $departure = new DateTime($data["departureTime"]);
                $arrival = new DateTime($data["arrivalTime"]);
                $interval = $arrival->diff($departure);
                $duration_hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600) + ($interval->days * 24);
                $formatted_duration = number_format($duration_hours, 2);
                $duree = $formatted_duration;
                $minDiff = round(abs(strtotime($departure_time) - strtotime($arrival_time)) /60,2);
            } 
            else
            {
                //log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
                $input_array["worker_id"] = $data["workerId"];
                $input_array["company_client_id"] = $data["companyClientId"];
                $input_array["departure_time"] = $data["departureTime"];
                $input_array["visit_date"] = $data["visitDate"];
                $input_array["arrival_time"] = $data["arrivalTime"];
                $input_array["visit_date"] = $data["visitDate"];
                $input_array["visitor_address"] = $data["visitorAddress"];
                $input_array["client_name"] = $data["clientName"];
                $input_array["duration"] = $data["duration"];
                $input_array["distance"] = distance_calculator(
                    $data["workerId"],
                      $data["visitorLat"],
                      $data["visitorLang"]
                );
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end

                $data = [
                    "status" => false,
                    "message" =>
                        "Departure time should be greater than arrival time.",
                ];
                echo json_encode($data);
                return;

                //log code start
                $output = json_encode($data);
                $api_name = "add visit";
                logs($api_name,$data["workerId"],0, $input, $output, $platform, $ip_address);
                //log code end
            }

              $cidd=$data["companyClientId"];
              $ciddd= get_clock_setting($cidd,$db);
            if($minDiff % $ciddd != 0)
            {
                
                //log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
                 $input_array["worker_id"] = $data["workerId"];
                $input_array["company_client_id"] = $data["companyClientId"];
                $input_array["departure_time"] = $data["departureTime"];
                $input_array["visit_date"] = $data["visitDate"];
                $input_array["arrival_time"] = $data["arrivalTime"];
                $input_array["visit_date"] = $data["visitDate"];
                $input_array["visitor_address"] = $data["visitorAddress"];
                $input_array["client_name"] = $data["clientName"];
                $input_array["duration"] = $data["duration"];
                $input_array["distance"] = distance_calculator(
                    $data["workerId"],
                      $data["visitorLat"],
                    $data["visitorLang"]
                );
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end

                $data = [
                    "status" => false,
                    "message" => "There should be atleast ".$ciddd." min difference.",
                ];
                echo json_encode($data);

                //log code start
                $output = json_encode($data);
                $api_name = "add visit";
                logs($api_name,$data["workerId"],0,$input, $output, $platform, $ip_address);
                //log code end
                return;
            }

           
            $distance = distance_calculator(
                $data["workerId"],
                  $data["visitorLat"],
                $data["visitorLang"]
            );

          
       
            $lid = "";
            $procedureName = "insertclientvisit";
            $procedure ="CALL insertclientvisit('".$data["visitorLat"]."','".$data["visitorLang"]."','".$data["workerId"]."','".$data["companyClientId"]."','".$data["clientName"]."','".$visit_date."','".$data["visitorAddress"]."','".$departure_time."','".$arrival_time."','".$duree."','".$distance."','".$datetime."',@id)";

            // echo $procedure;die;
            $results1 = $db->query($procedure);
            $results2 = $db->query("SELECT @id");
            $num_rows = $results2->num_rows;
            if ($num_rows > 0) 
            {
                while ($row = $results2->fetch_object()) 
                {
                    $lid = $row->{"@id"};
                }
            }

            $last_id = $lid;
           $sendinvoicedone=false;  
           $sendinvoicedone= sendInvoiceAddVisit($last_id);
          if($sendinvoicedone)
            {
            if ($last_id != "") 
            {
                  if($data1==null){
                    add_visit_images('', $last_id);
                  }
                  else
                  {
                    add_visit_images($data1, $last_id);
                  }
            }
        }
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["my_data"] = $data1;
             $input_array["worker_id"] = $data["workerId"];
            $input_array["company_client_id"] = $data["companyClientId"];
            $input_array["departure_time"] = $data["departureTime"];
            $input_array["visit_date"] = $data["visitDate"];
            $input_array["arrival_time"] = $data["arrivalTime"];
            $input_array["visit_date"] = $data["visitDate"];
            $input_array["visitor_address"] = $data["visitorAddress"];
            $input_array["client_name"] = $data["clientName"];
            $input_array["duration"] = $data["duration"];
            $input_array["distance"] = distance_calculator(
                $data["workerId"],
                  $data["visitorLat"],
                $data["visitorLang"]
            );
            $inputarrayJSON = json_encode($input_array);
            $input = $inputarrayJSON;
            //log code end
            $data1 = [
                "status" => true,
                "message" => "visits Successfully",
                "visitId" => $last_id,
            ];
            echo json_encode($data1);
            //log code start
            $output = json_encode($data1);
            $api_name = "add visit";
            logs($api_name,$data["workerId"],1, $input, $output, $platform, $ip_address);
            //log code end
        }
    }
    else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
         $input_array["worker_id"] = $data["workerId"];
            $input_array["company_client_id"] = $data["companyClientId"];
            $input_array["departure_time"] = $data["departureTime"];
            $input_array["visit_date"] = $data["visitDate"];
            $input_array["arrival_time"] = $data["arrivalTime"];
            $input_array["visit_date"] = $data["visitDate"];
            $input_array["visitor_address"] = $data["visitorAddress"];
            $input_array["client_name"] = $data["clientName"];
            $input_array["duration"] = $data["duration"];
            $input_array["distance"] = distance_calculator(
                $data["workerId"],
                  $data["visitorLat"],
                $data["visitorLang"]
            );
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end
        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add visit";
        logs($api_name,$data["workerId"],0, $input, $output, $platform, $ip_address);
        //log code end
    }

    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start) / 60;
    addvisittimelogs("userauth", $execution_time);
}

function entry_visit($data)
{
    $db = connect_db();
    if (userauthorization())
     {
        if ($data["visitId"] == "") 
        {
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["visit_id"] = $data["visitId"];
            $inputarrayJSON = json_encode($input_array);
            $input = $inputarrayJSON;
            //log code end

            $data = ["status" => false, "message" => "visit id is required."];
            echo json_encode($data);

            //log code start
            $output = json_encode($data);
            $api_name = "entry visit";
            logs($api_name,'',0, $input, $output, $platform, $ip_address);
            //log code end
        }
        else 
        {
            $datetime = date("Y-m-d H:i:s");
            $departure_time = date("H:i:s", strtotime($data["departure_time"]));
            //    $sqlInsert = "update clientvisites set  departure_time='".$departure_time."' where id ='".$data['visit_id']."' ";
            // $exeInsert = $db->query($sqlInsert);

            $invoiceSql ="SELECT client_visits.visit_lat,client_visits.visit_lang,client_visits.duration,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.postal_code as company_postal_code,companies.email as company_email,company_clients.company_id,company_clients.office_address as client_address,company_clients.postal_code as client_postal_code,client_visits.return_mileage_status as return_mileage,companies.address as admin_company_address,companies.company_name as admin_company_name,companies.postal_code as admin_postal_code,client_visits.image,workers.company_id,companies.mileage_status,client_visits.id as visit_id FROM `client_visits`  JOIN workers ON(client_visits.worker_id=workers.id) JOIN company_clients ON(client_visits.company_client_id=company_clients.id) JOIN companies ON(companies.id=workers.company_id) WHERE client_visits.id=" . $data["visitId"];
            $resultinvoice = $db->query($invoiceSql);
            if ($resultinvoice->num_rows > 0) 
            {
                $dataResult = [];
                while ($row = $resultinvoice->fetch_assoc()) 
                {
                    $dataResult[] = $row; // assign each value to array
                }
            }


            $email = $dataResult[0]["email"];

            if($dataResult[0]["mileage_status"] == 1)
            {
                if($dataResult[0]["return_mileage"]==0)
                {
                $distance = (float) $dataResult[0]["distance"];
                }
                else
                {
                  $distance = (float) $dataResult[0]["distance"] * 2;  
                }

} 

else
{
     if($dataResult[0]["return_mileage"]==0)
            {
            $distance = 0;
            }
            else
            {
              $distance = 0;  
            }
}

            $duration = (float) $dataResult[0]["duration"];
            $amount = $distance * (float) $dataResult[0]["mileage_rate"];
            $due_bal = (float) $amount + (float) $dataResult[0]["work_rate"]*$duration;
            $due_days = $dataResult[0]["due_date_range"];

           $terms='Due on receipt';
            $due_date = $dataResult[0]["visit_date"];
            if ($due_days > 0) {
                $terms ='Net '.$due_days;
                $due_days = "+" . $due_days . " day";
                $due_date = strtotime($due_days, strtotime($due_date));
                $due_date = date("Y-m-d", $due_date);
            }

$apiKey = 'AIzaSyArmhvQFhccQQ6MQeo2CpxGatou__6R_pg';
$address = $dataResult[0]["ca_postal"];
// Prepare the request URL
// $requestUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
$requestUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$dataResult[0]["visit_lat"].",".$dataResult[0]["visit_lang"]."&key=".$apiKey;


// Make the API request
$response = file_get_contents($requestUrl);
$data = json_decode($response, true);
$postalCode='';
// Check if the response was successful
if ($data['status'] === 'OK') {
    foreach ($data['results'][0]['address_components'] as $component) {
        if (in_array('postal_code', $component['types'])) {
            $postalCode = $component['long_name'];
                       break;
        }
    }
} else {
    $postalCode='';
}


// echo $visit_id;

// echo $dataResult[0]["visit_id"];

// die;

 $pdfHtml =
        '<html><body > 
    <div style="width:100%;">

    <div style="width: 530pt;margin-top: 10px;margin-bottom: 10px;margin-left:70px; font-size:16px;color:#4c4b49;">
<b style="font-size:22px;color:4c4b49">' .
        $dataResult[0]["admin_company_name"] .
        "</b><br>" .
        $dataResult[0]["admin_company_address"] .
        "," .
        $dataResult[0]["admin_postal_code"] .
        '
</div>

    
    <table style="width: 530pt;margin-top: 10px;margin-left:70px; font-size:16px;"  CELLSPACING="0" cellpadding="2">
    
        <tr>
            <td style="text-align: right;padding-right:540pt;"><b>BILL TO</b></td>
            <td><b>INVOICE#</b></td>
            <td style="color:#4c4b49;">' .
        str_pad($dataResult[0]["visit_id"], 6, "0", STR_PAD_LEFT) .
        '</td>



        </tr>
        <tr>
            <td style="color:#4c4b49;">' .
        $dataResult[0]["client_company_name"] .
        '</td>
            
            <td style="text-align: right;"><b>DATE</b></td>
            <td style="color:#4c4b49;">&nbsp;' .
        date("Y-m-d") .
        '</td>
        </tr>
        <tr>
            <td style="color:#4c4b49;">' .
        $dataResult[0]["client_address"] .
        "," .
        $dataResult[0]["client_postal_code"] .
        '</td>
            <td style="text-align: right;"><b>DUE DATE</b></td>
            <td style="color:#4c4b49;">&nbsp;' .
        $due_date .
        '</td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: right;"><b>TERMS</b></td>
            <td style="color:#4c4b49;">&nbsp;'.$terms.'</td>
        </tr>
        
    </table>
    <hr>
    <table style="width: 530pt;border: 1px solid #000; margin-top: 60px; margin-left:50px;"  CELLSPACING="0" cellpadding="2">
        <thead>
        <tr style="text-align: left; background: #ccccd2; padding:20px; ">
            <th style="padding: 10px; padding-left: 20px; color:#4c4b49;">DESCRIPTION</th>
            <th style="padding: 10px;text-align: center; color:#4c4b49;">QTY</th>
            <th style="padding: 10px;text-align: center; color:#4c4b49;">UNIT PRICE</th>
            <th style="padding: 10px;text-align: center; color:#4c4b49;">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="padding-left: 20px; padding: 10px;"><b >Field Service</b>&nbsp;&nbsp;&nbsp;<div style="color:#4c4b49;">' .
        $dataResult[0]["visit_date"] .
        " - " .
        $dataResult[0]["client_name"] .
        ' </div></td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">'.$duration
    .'</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $dataResult[0]["work_rate"] .
        '</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $dataResult[0]["work_rate"]*$duration .
        '</td>
        </tr>';

        if($dataResult[0]["mileage_status"] == 1)
{

      $pdfHtml .=   '<tr>
            <td style="padding-left: 20px; padding: 10px;"><b>Travel time</b>&nbsp;&nbsp;&nbsp;<div style="color:#4c4b49;">' .
        $dataResult[0]["ic_postal"] .
        " TO " .
        $postalCode .



        '</div></td> 
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .$distance.
        '</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $dataResult[0]["mileage_rate"] .
        '</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $amount .
        '</td>
        </tr>';
    }



      $pdfHtml .= '</tbody>

       <tfoot>
        <tr >
            <td style="border-top:2px dotted #a6a6a4;"></td>
            <td style="border-top:2px dotted #a6a6a4;"></td>
            <td style="padding-top:16px;margin-top:10px;text-align: right; border-top:2px dotted #a6a6a4; color:#4c4b49;">
                BALANCE DUE
            </td>
            <td style="text-align: center; border-top:2px dotted #a6a6a4;">
                <h5><b>$' .
        $due_bal .
        '</b></h5> 
            </td>
        </tr>
       </tfoot> 
    </table> 
    </div>  
</body></html>';



            $currentDateTime = date("Ymdhis");
            $string = "";
            $filename = "";
            $filename = $string . "_invoice_" . $currentDateTime . ".pdf";
            if ($dataResult[0]["client_company_name"] != "") {
                $string = str_replace(" ", "", $dataResult[0]["client_company_name"]);
                $currentDateTime = date("Ymdhis");
                $filename = $string . "_invoice_" . $currentDateTime . ".pdf";
            }

            $pdf_options = [
                "source_type" => "html",
                "source" => $pdfHtml,
                "action" => "save",
                "save_directory" => "pdfs",
                "file_name" => $filename,
            ];
            $pdfHtml2 = "<h1><b>$" . $due_bal . "</b></h1>";
            // $html2pdf = new Html2Pdf("L", "A4", "fr", true, "UTF-8", 0);
            $html2pdf = new Html2Pdf("L", "Letter", "fr", true, "UTF-8", 0);
            
            /*$html2pdf->writeHTML($pdfHtml2);*/
            $html2pdf->writeHTML($pdfHtml);
            $html2pdf->output(__DIR__ . "/pdfs/" . $filename, F);
            $sqlupdate =
                "update client_visits set  pdf_file='$filename' where id ='" .
                $data["visit_id"] .
                "' ";

            $st = $db->query($sqlupdate);
            $filename;
            if ($st) {
                $email = "";
                if ($dataResult[0]["send_invoice_status_client"] == 0) {
                    $email = $dataResult[0]["company_email"];
                } else {
                    $email = $dataResult[0]["email"];
                }
                $attach = $dataResult[0]["image"];
               sendEmail2($email, $filename);
                //log code start
                $ip_address = get_client_ip();
                $platform = funcDeviceType();
                $input_array = [];
                $input_array["visit_id"] = $data["visit_id"];
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end

                $data1 = ["status" => true, "message" => "voice sent"];
                echo json_encode($data1);

                //log code start
                $output = json_encode($data1);
                $api_name = "entry visit";
                logs($api_name,'',1, $input, $output, $platform, $ip_address);
                //log code end
            }
        }
    } 
    else 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $data["visitId"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = ["status" => false, "message" => "Worker Token is misssing"];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "entry visit";
        logs($api_name,'',0,$input, $output, $platform, $ip_address);
        //log code end
    }
}

function sendInvoiceAddVisit($visit_id)
{
    $db = connect_db();

    $invoiceSql =
        "SELECT client_visits.visit_lat,client_visits.visit_lang,client_visits.duration,client_visits.id as visit_id,client_visits.client_name,client_visits.distance,client_visits.visit_date,company_clients.client_company_name,client_visits.return_mileage_status as return_mileage,company_clients.work_rate,company_clients.mileage_rate,client_visits.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,company_clients.company_id,company_clients.office_address as client_address,company_clients.postal_code as client_postal_code,companies.company_name as admin_company_name,companies.postal_code as admin_postal_code,companies.address as admin_company_address,client_visits.image,companies.mileage_status  FROM `client_visits` JOIN workers ON(client_visits.worker_id=workers.id) JOIN company_clients ON(client_visits.company_client_id=company_clients.id) 
             JOIN companies ON(companies.id=company_clients.company_id)
     WHERE client_visits.id=" . $visit_id;


    $resultinvoice = $db->query($invoiceSql);
    $dataResult = [];
    if ($resultinvoice->num_rows > 0) {
        while ($row = $resultinvoice->fetch_assoc()) {
            $dataResult[] = $row;
        }
    }

    /*$email=$dataResult[0]['email'];*/


      if($dataResult[0]["mileage_status"] == 1){
        



    if($dataResult[0]["return_mileage"]==0)
    {
     $distance = (float) $dataResult[0]["distance"];
    }
    else
    {
     $distance = (float) $dataResult[0]["distance"] * 2; 
    }

}

else
{  
     if($dataResult[0]["return_mileage"]== 0)
    {
     $distance = 0;
    }
    else
    {
     $distance = 0; 
    }
 
}

   // $d = $distance;
    $duration = (float) $dataResult[0]["duration"];
    
    $amount = $distance * (float) $dataResult[0]["mileage_rate"];
    $due_bal = (float) $amount + (float) $dataResult[0]["work_rate"]*(float) $duration;
    $due_days = $dataResult[0]["due_date_range"];
    $due_date = $dataResult[0]["visit_date"];
      $terms='Due on receipt';
    if ($due_days > 0) {
        $terms="Net ".$due_days;
        $due_days = "+" . $due_days . " day";
        $due_date = strtotime($due_days, strtotime($due_date));
        $due_date = date("Y-m-d", $due_date);
    }
    

$apiKey = 'AIzaSyArmhvQFhccQQ6MQeo2CpxGatou__6R_pg';
$address = $dataResult[0]["ca_postal"];
// Prepare the request URL
//$requestUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
 $requestUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$dataResult[0]["visit_lat"].",".$dataResult[0]["visit_lang"]."&key=".$apiKey;

// Make the API request
$response = file_get_contents($requestUrl);
$data = json_decode($response, true);
$postalCode='';
// Check if the response was successful
if ($data['status'] === 'OK') {
    foreach ($data['results'][0]['address_components'] as $component) {
        if (in_array('postal_code', $component['types'])) {
            $postalCode = $component['long_name'];
                       break;
        }
    }
} else {
    $postalCode='';
}



    $pdfHtml =
        '<html><body > 
    <div style="width:100%;">

    <div style="width: 530pt;margin-top: 10px;margin-bottom: 10px;margin-left:70px; font-size:16px;color:#4c4b49;">
<b style="font-size:22px;color:4c4b49">' .
        $dataResult[0]["admin_company_name"] .
        "</b><br>" .
        $dataResult[0]["admin_company_address"] .
        "," .
        $dataResult[0]["admin_postal_code"] .
        '
</div>

    
    <table style="width: 530pt;margin-top: 10px;margin-left:70px; font-size:16px;"  CELLSPACING="0" cellpadding="2">
    
        <tr>
            <td style="text-align: right;padding-right:540pt;"><b>BILL TO</b></td>
            <td><b>INVOICE#</b></td>
            <td style="color:#4c4b49;">' .
        str_pad($visit_id, 6, "0", STR_PAD_LEFT) .
        '</td>



        </tr>
        <tr>

            <td style="color:#4c4b49;">' .
        $dataResult[0]["client_company_name"] .
        '</td>
            
            <td style="text-align: right;"><b>DATE</b></td>
            <td style="color:#4c4b49;">&nbsp;' .
        date("Y-m-d") .
        '</td>
        </tr>
        <tr>
            <td style="color:#4c4b49;">' .
        $dataResult[0]["client_address"] .
        "," .
        $dataResult[0]["client_postal_code"] .
        '</td>
            <td style="text-align: right;"><b>DUE DATE</b></td>
            <td style="color:#4c4b49;">&nbsp;' .
        $due_date .
        '</td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: right;"><b>TERMS</b></td>
            <td style="color:#4c4b49;">&nbsp;'.$terms.'</td>
        </tr>
        
    </table>
    <hr>
    <table style="width: 530pt;border: 1px solid #000; margin-top: 60px; margin-left:50px;"  CELLSPACING="0" cellpadding="2">
        <thead>
        <tr style="text-align: left; background: #ccccd2; padding:20px; ">
            <th style="padding: 10px; padding-left: 20px; color:#4c4b49;">DESCRIPTION</th>
            <th style="padding: 10px;text-align: center; color:#4c4b49;">QTY</th>
            <th style="padding: 10px;text-align: center; color:#4c4b49;">UNIT PRICE</th>
            <th style="padding: 10px;text-align: center; color:#4c4b49;">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="padding-left: 20px; padding: 10px;"><b >Field Service</b>&nbsp;&nbsp;&nbsp;<div style="color:#4c4b49;">' .
        $dataResult[0]["visit_date"] .
        " - " .
        $dataResult[0]["client_name"] .
        ' </div></td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">'.$duration
    .'</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $dataResult[0]["work_rate"] .
        '</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $dataResult[0]["work_rate"]*$duration .
        '</td>
        </tr>';

if($dataResult[0]["mileage_status"]==1)
{
        $pdfHtml .='<tr>
            <td style="padding-left: 20px; padding: 10px;"><b>Travel time</b>&nbsp;&nbsp;&nbsp;<div style="color:#4c4b49;">' .
        $dataResult[0]["ic_postal"] .
        " TO " .
        $postalCode .


 
        '</div></td> 
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $distance .
        '</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $dataResult[0]["mileage_rate"] .
        '</td>
            <td style="padding: 10px;text-align: center; color:#4c4b49;">' .
        $amount .
        '</td>
        </tr>';
    }


     $pdfHtml .='</tbody>

       <tfoot>
        <tr >
            <td style="border-top:2px dotted #a6a6a4;"></td>
            <td style="border-top:2px dotted #a6a6a4;"></td>
            <td style="padding-top:16px;margin-top:10px;text-align: right; border-top:2px dotted #a6a6a4; color:#4c4b49;">
                BALANCE DUE
            </td>
            <td style="text-align: center; border-top:2px dotted #a6a6a4;">
                <h5><b>$' .
        $due_bal .
        '</b></h5> 
            </td>
        </tr>
       </tfoot> 
    </table> 
    </div>  
</body></html>';


// echo $distance;die;
    $currentDateTime = date("Ymdhis");
    $string = "";
    $filename = "";
    $filename = $string . "_invoice_" . $currentDateTime . ".pdf";

    if ($dataResult[0]["client_company_name"] != "") {
        $string = str_replace(" ", "", $dataResult[0]["client_company_name"]);
        $currentDateTime = date("Ymdhis");
        $filename = $string . "_invoice_" . $currentDateTime . ".pdf";
    }

    $pdf_options = [
        "source_type" => "html",
        "source" => $pdfHtml,
        "action" => "save",
        "save_directory" => "pdfs",
        "file_name" => $filename,
    ];
    /*phptopdf($pdf_options);*/

    $pdfHtml2 = "<h1><b>$" . $due_bal . "</b></h1>";
    // $html2pdf = new Html2Pdf("L", "A4", "fr", true, "UTF-8", 0);
       $html2pdf = new Html2Pdf("L", "Letter", "fr", true, "UTF-8", 0);
    /*$html2pdf->writeHTML($pdfHtml2);*/

    $html2pdf->writeHTML($pdfHtml);

    $html2pdf->output(__DIR__ . "/pdfs/" . $filename, F);

    $sqlupdate =
        "update client_visits set  pdf_file='$filename' where id ='" .
        $visit_id .
        "' ";
         
    $db->query($sqlupdate);

    $email = "";
    if ($dataResult[0]["send_invoice_status_client"] == 0) {
        $email = $dataResult[0]["company_email"];
    } else {
        $email = $dataResult[0]["email"];
    }

    $attach = $dataResult[0]["image"];

    return true;

    
}

function sendInvoiceEmail($email, $fileName, $body)
{


$apiKey = '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';
$toEmail = $email;
$subject = 'Eworxs Invoice';
$htmlContent ='<h1>Invoice has been sent.</h1>';
$imageUrl = $fileName1; // Replace with your image URL
$pdfPath = "pdfs/" . $fileName; // Replace with the path to your PDF file

// Build the email payload
$emailData = [
    'personalizations' => [
        [
            'to' => [
                ['email' => $toEmail],
            ],
            'subject' => $subject,
        ],
    ],
    'from' => [
        'email' => 'info@eworxs.app', // Replace with your email address
    ],
    'content' => [
        [
            'type' => 'text/html',
            'value' => $htmlContent,
        ],
    ],
    'attachments' => [],
];



// Attach PDF
$pdfContent = file_get_contents($pdfPath);
$emailData['attachments'][] = [
    'content' => base64_encode($pdfContent),
    'filename' => basename($pdfPath),
    'type' => 'application/pdf',
    'disposition' => 'attachment',
];

// Convert the payload to JSON
$emailJson = json_encode($emailData);

// Make the API request
$ch = curl_init('https://api.sendgrid.com/v3/mail/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $emailJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
]);





 $response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close the cURL session
curl_close($ch);

// if ($httpCode == 202) {



    // $file = "pdf_invoice.pdf";
    // $iurl=$fileName;
    // $htmlContent = "<h1>Invoice has been sent.</h1> ";
    // $subject = "Eworxs Invoice";
    // $headers .= "From: <support@eworxs.app>" . "\r\n";
    // $headers .= "Cc: deepak@cresol.in" . "\r\n";
    // $semi_rand = md5(time());
    // $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    // $headers .=
    //     "\nMIME-Version: 1.0\n" .
    //     "Content-Type: multipart/mixed;\n" .
    //     " boundary=\"{$mime_boundary}\"";
    // $message =
    //     "--{$mime_boundary}\n" .
    //     "Content-Type: text/html; charset=\"UTF-8\"\n" .
    //     "Content-Transfer-Encoding: 7bit\n\n" .
    //     $htmlContent .
    //     "\n\n";
    // if (!empty($file) > 0) {
    //     if (is_file($file)) {
    //         $message .= "--{$mime_boundary}\n";
    //         $fp = @fopen($file, "rb");
    //         $data = @fread($fp, filesize($file));

    //         @fclose($fp);
    //         $data = chunk_split(base64_encode($data));
    //         $message .=
    //             "Content-Type: application/octet-stream; name=\"" .
    //             basename($file) .
    //             "\"\n" .
    //             "Content-Description: " .
    //             basename($file) .
    //             "\n" .
    //             "Content-Disposition: attachment;\n" .
    //             " filename=\"" .
    //             basename($file) .
    //             "\"; size=" .
    //             filesize($file) .
    //             ";\n" .
    //             "Content-Transfer-Encoding: base64\n\n" .
    //             $data .
    //             "\n\n";
    //     }
    // }
    // $message .= "--{$mime_boundary}--";
    // $returnpath = "-f" . $from;

    //send email
    // $mail = @mail($email, $subject, $message, $headers, $returnpath);

    //email sending status
    //echo $mail?"Sent":"Mail sending failed.";
}

function sendEmail2($email, $fileName)
{
  $apiKey = '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';
$toEmail = $email;
$subject = 'Eworxs Invoice';
$htmlContent = '<pre>Dear Client,

We sincerely appreciate your valuable time during our visit. Enclosed, you will find the attached invoice.

Warm regards,

Eworxs Team </pre>';
//$imageUrl = $fileName1; // Replace with your image URL
$pdfPath = "pdfs/" . $fileName; // Replace with the path to your PDF file

// Build the email payload
$emailData = [
    'personalizations' => [
        [
            'to' => [
                ['email' => $toEmail],
            ],
            'subject' => $subject,
        ],
    ],
    'from' => [
        'email' => 'info@eworxs.app', // Replace with your email address
    ],
    'content' => [
        [
            'type' => 'text/html',
            'value' => $htmlContent,
        ],
    ],
    'attachments' => [],
];

// Attach Image from URL
// $imageContent = file_get_contents($imageUrl);
// $emailData['attachments'][] = [
//     'content' => base64_encode($imageContent),
//     'filename' => 'image.png',
//     'type' => 'image/png', // Adjust the content type as needed
//     'disposition' => 'inline',
//     'content_id' => 'image_id'
// ];

// Attach PDF
$pdfContent = file_get_contents($pdfPath);
$emailData['attachments'][] = [
    'content' => base64_encode($pdfContent),
    'filename' => basename($pdfPath),
    'type' => 'application/pdf',
    'disposition' => 'attachment',
];

// Convert the payload to JSON
$emailJson = json_encode($emailData);

// Make the API request
$ch = curl_init('https://api.sendgrid.com/v3/mail/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $emailJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
]);





 $response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close the cURL session
curl_close($ch);

if ($httpCode == 202) {



         $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["to"] = $to;
          $output_array = [];
        $output_array["message"] = "email sent";
        
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode();
        $api_name = "add departure time api";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);
    }
    //email sending status
    /*echo $mail?"<h1>Mail sent.</h1>":"<h1>Mail sending failed.</h1>";*/
}

function sendEmail1($email, $fileName = "", $fileName1 = "", $visitid = "")
{
      $loop1 = true;



      if ($fileName != "" && $fileName1 != "" && $loop1 == true) 
      {
         $loop1 = false;

$apiKey = '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';
$toEmail = $email;
$subject = 'Eworxs Invoice';
$htmlContent = '<pre>Dear Client,

We sincerely appreciate your valuable time during our visit. Enclosed, you will find the attached invoice.

Warm regards,

Eworxs Team </pre>';
$imageUrl = $fileName1; // Replace with your image URL
$pdfPath = "pdfs/" . $fileName; // Replace with the path to your PDF file

// Build the email payload
$emailData = [
    'personalizations' => [
        [
            'to' => [
                ['email' => $toEmail],
            ],
            'subject' => $subject,
        ],
    ],
    'from' => [
        'email' => 'info@eworxs.app', // Replace with your email address
    ],
    'content' => [
        [
            'type' => 'text/html',
            'value' => $htmlContent,
        ],
    ],
    'attachments' => [],
];

// Attach Image from URL
$imageContent = file_get_contents($imageUrl);
$emailData['attachments'][] = [
    'content' => base64_encode($imageContent),
    'filename' => 'image.png',
    'type' => 'image/png', // Adjust the content type as needed
    'disposition' => 'inline',
    'content_id' => 'image_id'
];

// Attach PDF
$pdfContent = file_get_contents($pdfPath);
$emailData['attachments'][] = [
    'content' => base64_encode($pdfContent),
    'filename' => basename($pdfPath),
    'type' => 'application/pdf',
    'disposition' => 'attachment',
];

// Convert the payload to JSON
$emailJson = json_encode($emailData);

// Make the API request
$ch = curl_init('https://api.sendgrid.com/v3/mail/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $emailJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
]);

 $response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close the cURL session
curl_close($ch);

if ($httpCode == 202) {

    $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["to"] = $to;
          $output_array = [];
        $output_array["message"] = "email sent";
        
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode();
        $api_name = "add departure time api";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);
} else {
   
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["to"] = $to;
          $output_array = [];
        $output_array["message"] = "email not sent";
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        $output = json_encode();
        $api_name = "add departure time api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
}
}
}

// For Visits

function get_visits($worker_id,$offset,$limit)
{
  if($worker_id == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["email"] = $data["email"];
        $input_array["password"] = $data["password"];
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "Worker Id is required.",
            "token" => null,
            "userId" => null,
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "login api";
        logs($api_name,$worker_id,0, $input, $output, $platform, $ip_address);
        //log code end
    } 

    else if($offset == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["offset"] = $data["offset"];
    
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "offset is required.",
            "token" => null,
            "userId" => null,
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "login api";
        logs($api_name,'',0, $input, $output, $platform, $ip_address);
        //log code end
    } 

    else if($limit == "") 
    {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["limit"] = $data["limit"];
     
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "status" => false,
            "message" => "limit is required.",
            "token" => null,
            "userId" => null,
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "login api";
        logs($api_name,'',0,$input, $output, $platform, $ip_address);
        //log code end
    } 

   else
   {
        if (userauthorization()) 
        {
            $db = connect_db();

            $sql =
                "SELECT client_visits.id as id,client_visits.mileage_status,client_visits.return_mileage_status,client_visits.company_client_id as companyClientId,
                  company_clients.client_company_name as clientCompanyName,
                client_visits.client_name as clientName,client_visits.visit_date as visitDate,CONCAT(client_visits.duration, ' h')  AS duration,client_visits.distance,client_visits.pdf_file as pdfFile FROM client_visits left join company_clients on company_clients.id=client_visits.company_client_id left join companies on companies.id = company_clients.company_id where client_visits.departure_time NOT LIKE '00:00:00' and client_visits.visit_status = 1 and client_visits.worker_id='".$worker_id."' ORDER BY client_visits.id desc LIMIT ".$offset.", ".$limit."";


            $data = []; // array variable
            if ($result = $db->query($sql)) 
            {
                while ($row = $result->fetch_assoc()) 
                {

                    if ($row['mileage_status'] == 1) 
                    {
                        if($row['return_mileage_status'] == 1)
                        {
                         $row['distance'] = $row['distance']*2;
                        } 
                        else
                        {
                           $row['distance'] = $row['distance'];
                        }
                   }
                    else
                     {
                     $row['distance'] = "0";
                     }


                    $data[] = $row;
                }
            }
            $db = null;
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["worker_id"] = $worker_id;
            $inputarrayJSON = json_encode($input_array);
            $input = $inputarrayJSON;
            //log code end

            $data = [
                "status" => true,
                "message" => "data retrieved",
                "data" => $data,
            ];
            echo json_encode($data);

            //log code start
            $output = json_encode($data);
            $api_name = "get visit by worker id";
            logs($api_name,$worker_id,1, $input, $output, $platform, $ip_address);
            //log code end
        } else {
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["worker_id"] = $worker_id;
            $inputarrayJSON = json_encode($input_array);
            $input = $inputarrayJSON;
            //log code end

            $data = ["Status" => false, "Message" => "Worker Token is misssing"];
            echo json_encode($data);

            //log code start
            $output = json_encode($data);
            $api_name = "get visit by worker id";
            logs($api_name,$worker_id,0, $input, $output, $platform, $ip_address);
            //log code end
        }

    }

}

function get_visit_details($visit_id)
{
    if (userauthorization()) {
        $db = connect_db();
        $sql = "SELECT * FROM client_visits where id='" . $visit_id . "'";
        $data = [];
        if ($result = $db->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                // use fetch_assoc here
                $data[] = $row; // assign each value to array
            }
        }
        $db = null;

        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $visit_id;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = [
            "Status" => true,
            "Message" => "data retrieved",
            "Data" => $data,
        ];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "get visit details";
        logs($api_name,'',1, $input, $output, $platform, $ip_address);
        //log code end
    } else {
        //log code start
        $ip_address = get_client_ip();
        $platform = funcDeviceType();
        $input_array = [];
        $input_array["visit_id"] = $visit_id;
        $inputarrayJSON = json_encode($input_array);
        $input = $inputarrayJSON;
        //log code end

        $data = ["Status" => false, "Message" => "Worker Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "get visit details";
        logs($api_name,'',0,$input, $output, $platform, $ip_address);
        //log code end
    }
}

function company_current_plan($company_client_id)
{
    $db = connect_db();
    $sql = "SELECT plans.plan_name,plans.id,plans.plan_type FROM company_clients  left join company_subscriptions on company_subscriptions.company_id=company_clients.company_id left join plans on company_subscriptions.plan_id=plans.id where company_subscriptions.status=1 and company_clients.id=$company_client_id";
    $data = [];
    if ($result = $db->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            // use fetch_assoc here
            $data[] = $row; // assign each value to array
        }
    }
    $db = null;
    $data = ["Data" => $data];
    return $data;
}

function company_visit_count($worker_id, $company_client_id)
{
    $db = connect_db();
    $sql =
        "SELECT count(id) as visit_count from client_visits where worker_id=" .
        $worker_id .
        " and company_id =" .
        $company_client_id;
    /*  echo $sql;*/
    $data = [];
    if ($result = $db->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            // use fetch_assoc here
            $data[] = $row; // assign each value to array
        }
    }
    $db = null;
    $data = ["Data" => $data];
    return $data;
}

function testemail()
{

$apiKey = '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';
$toEmail = 'cresoluser@gmail.com';
$subject = 'Eworxs Invoice';
$htmlContent ='<h1>Invoice has been sent.</h1>';
// Build the email payload
$emailData = [
    'personalizations' => [
        [
            'to' => [
                ['email' => $toEmail],
            ],
            'subject' => $subject,
        ],
    ],
    'from' => [
        'email' => 'info@eworxs.app', // Replace with your email address
    ],
    'content' => [
        [
            'type' => 'text/html',
            'value' => $htmlContent,
        ],
    ],
   
];
// Convert the payload to JSON
$emailJson = json_encode($emailData);

// Make the API request
$ch = curl_init('https://api.sendgrid.com/v3/mail/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $emailJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
]);
 $response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


}
function distance_calculator($emp_id, $visitor_lat,$visitor_lang)
{

    $visit_address = $visit_address;
    $db = connect_db();
    $sql = "SELECT latitude,longitude from workers where id=" . $emp_id;
    $worker_origin_lat = "";
    $worker_origin_lng = "";
    $data = [];
    if ($result = $db->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            // use fetch_assoc here
            $worker_origin_lat = $row["latitude"]; // assign each value to array
            $worker_origin_lng = $row["longitude"];
        }
    }
    $vist_address_lat = "";
    $vist_address_lng = "";
    $vist_address_lat=$visitor_lat;
    $vist_address_lng=$visitor_lang;
    $res12 = GetDistanceMatrix(
        $worker_origin_lat,
        $worker_origin_lng,
        $vist_address_lat,
        $vist_address_lng
    );
    return $res12;
}



function logs($api_name,$worker_id,$status,$input, $output, $platform, $ip_address)
{

    $worker_id = ($worker_id == '')? null : $worker_id;
    $headers = apache_request_headers();
    $headerToken = "empty";
    if (isset($headers["authorization"])) {
        $headerToken = $headers["authorization"];
    } elseif (isset($headers["Authorization"])) {
        $headerToken = $headers["Authorization"];
    }
    $db = connect_db();
    $api_name_val = "";
    $input_val = "";
    $output_val = "";
    $platform_val = "";
    $ip_address_val = "";
    $api_name_val = $api_name;
    $input_val = $input;
    $output_val = $output;
    $platform_val = $platform;
    $ip_address_val = $ip_address;

    if($worker_id == null)
    {
$sqlInsert =
        "insert into logs(api_name,worker_id,status,input,token,output,platform,ip_address_val)" .
        " VALUES('" .
        $api_name_val .
        "',null,'" .
        $status .
        "','" .
        $input_val .
        "','" .
        $headerToken .
        "','" .
        $output_val .
        "','" .
        $platform_val .
        "','" .
        $ip_address_val .
        "')";
    }
    else{
    $sqlInsert =
        "insert into logs(api_name,worker_id,status,input,token,output,platform,ip_address_val)" .
        " VALUES('" .
        $api_name_val .
        "','" .
        $worker_id .
        "','" .
        $status .
        "','" .
        $input_val .
        "','" .
        $headerToken .
        "','" .
        $output_val .
        "','" .
        $platform_val .
        "','" .
        $ip_address_val .
        "')";
}
         // echo $sqlInsert;die;
    $exeInsert = $db->query($sqlInsert);
}

function addvisittimelogs($function_name, $time_taken)
{
    $headers = apache_request_headers();
    $headerToken = "empty";
    if (isset($headers["authorization"])) {
        $headerToken = $headers["authorization"];
    } elseif (isset($headers["Authorization"])) {
        $headerToken = $headers["Authorization"];
    }
    $db = connect_db();

    $time_taken = $time_taken;
    $function_name = $function_name;

    $sqlInsert =
        "insert into addvisittimelog(function_name,time_taken)" .
        " VALUES('" .
        $function_name .
        "','" .
        $time_taken .
        "')";

    $exeInsert = $db->query($sqlInsert);
}

function funcDeviceType()
{
    $useragent = $_SERVER["HTTP_USER_AGENT"];
    if (stripos($useragent, "mobile") !== false) {
        return "mobile device";
    } else {
        return "desktop or laptop computer";
    }
}


function get_clock_setting($data,$db)
{
  if($data!='')
 {
    $sqlInsert ="select clock_setting from company_clients where id = ".$data;
    $exeInsert = $db->query($sqlInsert);
    if($exeInsert->num_rows)
    {
      $dataResult = [];
        while ($row = $exeInsert->fetch_assoc())
        {
          $dataResult[] = $row;
        }
      return $dataResult[0]['clock_setting']; 
    }
  }
}

function get_client_ip()
{
    $ipaddress = "";
    if (getenv("HTTP_CLIENT_IP")) {
        $ipaddress = getenv("HTTP_CLIENT_IP");
    } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
        $ipaddress = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("HTTP_X_FORWARDED")) {
        $ipaddress = getenv("HTTP_X_FORWARDED");
    } elseif (getenv("HTTP_FORWARDED_FOR")) {
        $ipaddress = getenv("HTTP_FORWARDED_FOR");
    } elseif (getenv("HTTP_FORWARDED")) {
        $ipaddress = getenv("HTTP_FORWARDED");
    } elseif (getenv("REMOTE_ADDR")) {
        $ipaddress = getenv("REMOTE_ADDR");
    } else {
        $ipaddress = "UNKNOWN";
    }
    return $ipaddress;
}

?>
