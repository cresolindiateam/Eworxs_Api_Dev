<?php
require "vendor/autoload.php";
require "Eworxsmysql.php";
require "fpdf.php";
/*require("phpToPDF.php");*/

require __DIR__ . "/html2pdf/vendor/autoload.php";
use Spipu\Html2Pdf\Html2Pdf;

use mikehaertl\wkhtmlto\Pdf;
$app = new \Slim\App(["settings" => ["displayErrorDetails" => true]]);
//slim application routes

$app->post("/TestPdf", function ($request, $response, $args) {
    sendInvoiceAddVisit(810);
});

$app->post("/LoginUser", function ($request, $response, $args) {
    login_user($request->getParsedBody());
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

/*
$app->post('/sendVoice', function($request, $response, $args) {
    ($request->getParsedBody());
});*/

$app->run();

function ImageUpload($FILES)
{
    $upload_folder = "Image/";
    $max_size = 100000000;
    $allowed_extensions = ["png", "jpg", "jpeg", "gif", "mp4", "gp3", "webm"];
    $filename = $FILES->getClientFilename();
    $extension = strtolower(
        pathinfo($FILES->getClientFilename(), PATHINFO_EXTENSION)
    );

    $new_path = $upload_folder . $filename;
    move_uploaded_file($FILES->file, $new_path);
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
                    $t = $filename;
                    //log code start
                    $ip_address = get_client_ip();
                    $platform = funcDeviceType();
                    $input_array = [];
                    $input_array["fileNamedata"] = $filename;
                    $inputarrayJSON = json_encode($input_array);
                    $input = $inputarrayJSON;
                    //log code end
                    $sqlInsert ="update client_visites set IMAGE='".$t."' where id=".$data1."";
                    $exeInsert = $db->query($sqlInsert);
                    if ($exeInsert) 
                    {
                      $invoiceSql1 ="SELECT client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id,client_visites.pdf_file FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN company_clients ON(client_visites.company_id=company_clients.id) 
                          JOIN companies ON(companies.id=workers.company_id) WHERE client_visites.id=" . $data1;

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
                            sendEmail1($email1, $filename, $image);
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
                            sendEmail1($email1, $filename, $image);
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
                    logs($api_name, $input, $output, $platform, $ip_address);
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
                    logs($api_name, $input, $output, $platform, $ip_address);
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
        $data = ["status" => false, "message" => "Employee Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "add visit images";
        logs($api_name, $input, $output, $platform, $ip_address);
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
                        "SELECT client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visites.company_id=company_clients.id) WHERE client_visites.id=" .
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
                    if ($dataResult1[0]["company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["company_name"]
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

     }
     else{


        if ($data->getSize() > 0) 
        {

           
            if ($data->getSize() > 1048576) 
            {
                $input_array = [];
                $input_array["fileNamedata"] = $filename;
                $inputarrayJSON = json_encode($input_array);
                $input = $inputarrayJSON;
                //log code end
                $data1 = [
                    "status" => false,
                    "message" =>
                        "File size error.file should be less than 1 MB",
                ];
                //log code start
                echo $output = json_encode($data1);
                $api_name = "add visit images";
                logs($api_name, $input, $output, $platform, $ip_address);
                exit();
                return false;
            } 
            else 
            {
                if ($data->getError() === UPLOAD_ERR_OK)
                 {
                    $filename = ImageUpload($data);
                    if ($filename) 
                    {
                        $t = "";
                        $t = $filename;
                        //log code start
                        $ip_address = get_client_ip();
                        $platform = funcDeviceType();
                        $input_array = [];
                        $input_array["fileNamedata"] = $filename;
                        $inputarrayJSON = json_encode($input_array);
                        $input = $inputarrayJSON;
                        //log code end
                        $sqlInsert =
                            "update client_visites set IMAGE='" .
                            $t .
                            "' where id=" .
                            $data1 .
                            "";
                        $exeInsert = $db->query($sqlInsert);
                        if ($exeInsert) 
                        {
                            $invoiceSql1 =
                                "SELECT client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
					              JOIN company_clients ON(client_visites.company_id=company_clients.id) WHERE client_visites.id=" .
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
                                $filename =
                                    $string .
                                    "_invoice_" .
                                    $currentDateTime .
                                    ".pdf";
                                if ($dataResult1[0]["company_name"] != "")
                                 {
                                    $string = str_replace(
                                        " ",
                                        "",
                                        $dataResult1[0]["company_name"]
                                    );
                                    $currentDateTime = date("Ymdhis");
                                    $filename =
                                        $string .
                                        "_invoice_" .
                                        $currentDateTime .
                                        ".pdf";
                                }

                                $image = $t;

                                /* echo $email1;
                                echo $filename;
                                 echo $image;die;*/
                                // echo "hello";die;
                                //                          echo $image;die;
                                // if($image!=''){
                                sendEmail1($email1, $filename, $image);
                                // }
                                // else{

                                // 	sendEmail2($email1,$filename);
                                //              }
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
                    /*echo "jai mata di";die;*/

                    $invoiceSql1 =
                        "SELECT client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
					              JOIN company_clients ON(client_visites.company_id=company_clients.id) WHERE client_visites.id=" .
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
                    if ($dataResult1[0]["company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["company_name"]
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
                }
            }
        }

      elseif($data==null)
      {
         


              $invoiceSql1 =
                        "SELECT client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visites.company_id=company_clients.id) WHERE client_visites.id=" .
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
                    if ($dataResult1[0]["company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["company_name"]
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


        
      }


        else
        {


              $invoiceSql1 =
                        "SELECT client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,workers.company_id FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN companies ON(companies.id=workers.company_id)
                                  JOIN company_clients ON(client_visites.company_id=company_clients.id) WHERE client_visites.id=" .
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
                    if ($dataResult1[0]["company_name"] != "") {
                        $string = str_replace(
                            " ",
                            "",
                            $dataResult1[0]["company_name"]
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

        $data = ["Status" => false, "Message" => "Employee Token is misssing"];
        //log code start
        $output = json_encode($data);
        $api_name = "add visit images";
        logs($api_name, $input, $output, $platform, $ip_address);
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
    //	echo json_encode($data);*/
    return $dis;

    //log code start
    $output = json_encode($d);
    $api_name = "get distance matrix";
    logs($api_name, $input, $output, $platform, $ip_address);
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
    // echo "hi";
    //echo $emp_id;

    $headers = apache_request_headers();
    $headerToken = "";
    if (isset($headers["authorization"])) {
        $headerToken = $headers["authorization"];
    } elseif (isset($headers["Authorization"])) {
        $headerToken = $headers["Authorization"];
    }

    //echo $headerToken;
    //echo "bye";

    if (isset($headers["authorization"]) || isset($headers["Authorization"])) {
        $db = connect_db();
        $sqlUnique =
            "select token  from workers where id='$emp_id' and token='" .
            $headerToken .
            "'";
        /*	echo $sqlUnique; die;*/
        $result = $db->query($sqlUnique);

        if ($result->num_rows != 0) {
            //echo "ftrue ";
            return true;
        } else {
            //echo "ffalse";
            $response["Message"] = "User Token is misssing";
            return false;
        }
    } else {
        //$response["Message"] = "User Token is misssing";
        //echo "false";
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
        logs($api_name, $input, $output, $platform, $ip_address);
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
        logs($api_name, $input, $output, $platform, $ip_address);
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
                    logs($api_name, $input, $output, $platform, $ip_address);
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
                    logs($api_name, $input, $output, $platform, $ip_address);
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
                logs($api_name, $input, $output, $platform, $ip_address);
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
            logs($api_name, $input, $output, $platform, $ip_address);
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
        logs($api_name, $input, $output, $platform, $ip_address);
        //log code end
    } 
    else
    {  
    $db = connect_db();
    $sql = "SELECT company_clients.id,company_clients.company_name as companyClientName,company_clients.clock_setting as clockSetting FROM company_clients JOIN companies ON(company_clients.company_id=companies.id) JOIN workers ON(workers.company_id=companies.id) where company_clients.status=1 && workers.id=$worker_id  ORDER BY company_clients.id";

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
    logs($api_name, $input, $output, $platform, $ip_address);
    //log code end
  }
}

function add_visit($data, $data1)
{
    $db = connect_db();
    $emp_id = $data["workerId"];
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
            logs($api_name, $input, $output, $platform, $ip_address);
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
            $visit_count = company_visit_count($emp_id, $cclientid);
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
                logs($api_name, $input, $output, $platform, $ip_address);
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
                logs($api_name, $input, $output, $platform, $ip_address);
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
                logs($api_name, $input, $output, $platform, $ip_address);
                //log code end
                return;
            }

            $time_start_d = microtime(true);
            $distance = distance_calculator(
                $data["workerId"],
                  $data["visitorLat"],
                $data["visitorLang"]
            );

            $time_end_d = microtime(true);
            $execution_time_d = ($time_end_d - $time_start_d) / 60;
            addvisittimelogs("distance_calc", $execution_time_d);
            /*$time_start_query=microtime(true);
             $sqlInsert = "insert into client_visites(employee_id,company_id,visit_date,client_name,visit_address,departure_time,arrival_time,duration,distance,created_at,IMAGE)"
		   . " VALUES('".$data['employee_id']."','".$data['company_id']."','".$visit_date."','".$data['client_name']."','".$data['visit_address']."', '".$departure_time."','".$arrival_time."','".$duree."','".$distance."','".$datetime."','".$data['image']."')";
		      $exeInsert = $db->query($sqlInsert);
              $time_end_query = microtime(true);
             $execution_time_pro = ($time_end_query - $time_start_query)/60;
             addvisittimelogs('query',$execution_time_pro);*/

            $time_start_pro = microtime(true);
            $lid = "";
            $procedureName = "insertclientvisit";
            $procedure ="CALL insertclientvisit('".$data["workerId"]."','".$data["companyClientId"]."','".$data["clientName"]."','".$visit_date."','".$data["visitorAddress"]."','".$departure_time."','".$arrival_time."','".$duree."','".$distance."','".$datetime."',@id)";
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

            $time_end_pro = microtime(true);
            $execution_time_pro = ($time_end_pro - $time_start_pro) / 60;
            addvisittimelogs("procedure", $execution_time_pro);
            $last_id = $lid;
            $time_start_send_invoice = microtime(true);
            sendInvoiceAddVisit($last_id);
            $time_end_send_invoice = microtime(true);
            $execution_time_send_invoice =
                ($time_end_send_invoice - $time_start_send_invoice) / 60;
            addvisittimelogs(
                "sendinvoiceaddvisit",
                $execution_time_send_invoice
            );
            if ($last_id != "") 
            {
                $time_start_add_visit_image = microtime(true);
                  if($data1==null){
                    add_visit_images('', $last_id);
                  }
                  else
                  {
                    add_visit_images($data1, $last_id);
                  }
                $time_end_add_visit_image = microtime(true);
                $execution_time_add_visit_image =
                    ($time_end_add_visit_image - $time_start_add_visit_image) /
                    60;
                addvisittimelogs(
                    "addvisitimage",
                    $execution_time_add_visit_image
                );
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
            logs($api_name, $input, $output, $platform, $ip_address);
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
        logs($api_name, $input, $output, $platform, $ip_address);
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
            logs($api_name, $input, $output, $platform, $ip_address);
            //log code end
        }
        else 
        {
            $datetime = date("Y-m-d H:i:s");
            $departure_time = date("H:i:s", strtotime($data["departure_time"]));
            //    $sqlInsert = "update clientvisites set  departure_time='".$departure_time."' where id ='".$data['visit_id']."' ";
            // $exeInsert = $db->query($sqlInsert);

            $invoiceSql ="SELECT client_visites.duration,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.postal_code as company_postal_code,companies.email as company_email,company_clients.company_id,company_clients.office_address as client_address,company_clients.postal_code as client_postal_code,companies.address as admin_company_address,companies.company_name as admin_company_name,companies.postal_code as admin_postal_code,client_visites.IMAGE,workers.company_id FROM `client_visites`  JOIN workers ON(client_visites.employee_id=workers.id) JOIN company_clients ON(client_visites.company_id=company_clients.id) JOIN companies ON(companies.id=workers.company_id) WHERE client_visites.id=" . $data["visitId"];
            $resultinvoice = $db->query($invoiceSql);
            if ($resultinvoice->num_rows > 0) 
            {
                $dataResult = [];
                while ($row = $resultinvoice->fetch_assoc()) 
                {
                    $dataResult[] = $row; // assign each value to array
                }
            }
            // create pdf file
            $email = $dataResult[0]["email"];
            $distance = (float) $dataResult[0]["distance"] * 2;
            $duration = (float) $dataResult[0]["duration"];
            $amount = $distance * (float) $dataResult[0]["mileage_rate"];
            $due_bal = (float) $amount + (float) $dataResult[0]["work_rate"];
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
$requestUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;

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
                '<html>
                  <body> 
                 <div style="width:100%;">
                  <div style="width: 530pt;margin-top: 10px;margin-bottom: 10px;margin-left:70px; font-size:16px;color:#4c4b49;" >
                   <b style="font-size:22px;color:4c4b49">'.$dataResult[0]["admin_company_name"]."</b><br>" .
                $dataResult[0]["admin_company_address"]."," . ["admin_postal_code"] .
                ' </div>
                  <table style="width: 595pt;"  CELLSPACING="0" cellpadding="2">
            		<tr>
            			<th style=""></th>
            			<th style=""></th>
            		</tr>
            		<tr>
            			<td ><b>BILL TO</b></td>
            			<td><b>INVOICE#</b></td>
            			<td style="color:#4c4b49;">'.str_pad($data["visit_id"], 6, "0", STR_PAD_LEFT).'</td>
            		</tr>
            		<tr>
            		<td style="color:#4c4b49;">'.$dataResult[0]["company_name"].'</td>
				    <td style="text-align: right;margin-right:200px;"><b>DATE</b></td>
			       <td style="color:#4c4b49;">&nbsp;'.date("Y-m-d").'</td>
            		</tr>
            		<tr>
            			<td style="color:#4c4b49;">' .$dataResult[0]["client_address"] ."," .
                            $dataResult[0]["client_postal_code"] .
                            '</td>
            			<td style="text-align: right;"><b>DUE DATE</b></td>
            			<td style="color:#4c4b49;">&nbsp;'.$due_date.'</td>
            		</tr>
            		<tr>
            			<td></td>
            			<td style="text-align: right;"><b>TERMS</b></td>
            			<td style="color:#4c4b49;">&nbsp;'.$terms.'</td>
            		</tr>
            		
            	</table>
            	<hr>

                <table style="width: 595pt;border: 1px solid #000; margin-top: 60px;"  CELLSPACING="0" cellpadding="2">
        		<thead>
        		<tr style="text-align: left; background: #ccccd2; padding:20px; ">
        			<th style="padding: 10px; padding-left: 20px; color:#4c4b49;">DESCRIPTION</th>
        			<th style="text-align: center; color:#4c4b49;">QTY</th>
        			<th style="text-align: center; color:#4c4b49;">UNIT PRICE</th>
        			<th style="text-align: center; color:#4c4b49;">AMOUNT</th>
        		</tr>
        		</thead>
        	    <tbody>
        		<tr>
        			<td style="padding-left: 20px; padding: 10px;"><b >Field Service</b> <div style="color:#4c4b49;">' .
                        $dataResult[0]["visit_date"] .
                        " - " .
                        $dataResult[0]["client_name"] .
                        ' </div></td>
        			<td style="text-align: center; color:#4c4b49;">'.$duration.'</td>
        			<td style="text-align: center; color:#4c4b49;">' .
                        $dataResult[0]["work_rate"] .
                        '</td>
        			<td style="text-align: center; color:#4c4b49;">' .
                        $dataResult[0]["work_rate"]*$duration .
                        '</td>
        		</tr>

        		<tr>
        			<td style="padding-left: 20px; padding: 10px;"><b>Travel time</b> <div style="color:#4c4b49;">' .$dataResult[0]["ic_postal"] ." TO " .$postalCode.'</div></td> 
        			<td style="text-align: center; color:#4c4b49;">' .$distance .'</td>
        			<td style="text-align: center; color:#4c4b49;">'.$dataResult[0]["mileage_rate"] .
                        '</td>
        			<td style="text-align: center; color:#4c4b49;">'.$amount.'</td>
        		</tr>

        	   </tbody>
        	   <tfoot>
        	   	<tr >
        	   		<td style="border-top:2px dotted #a6a6a4;"></td>
        	   		<td style="border-top:2px dotted #a6a6a4;"></td>
        	   		<td style="text-align: right; border-top:2px dotted #a6a6a4; color:#4c4b49;">
        	   			BALANCE DUE
        	   		</td>
        	   		<td style="text-align: center; border-top:2px dotted #a6a6a4;">
        	   			<h5><b>$'.$due_bal .'</b></h5> 
        	   		</td>
        	   	</tr>
        	   </tfoot> 
        	   </table> 
        	  </div>  
            </body>
          </html>';

            //$pdf->addPage($pdfHtml);

            //print_r($dataResult);
            $currentDateTime = date("Ymdhis");
            $string = "";
            $filename = "";
            $filename = $string . "_invoice_" . $currentDateTime . ".pdf";
            if ($dataResult[0]["company_name"] != "") {
                $string = str_replace(" ", "", $dataResult[0]["company_name"]);
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
            $html2pdf = new Html2Pdf("L", "A4", "fr", true, "UTF-8", 0);
            /*$html2pdf->writeHTML($pdfHtml2);*/
            $html2pdf->writeHTML($pdfHtml);
            $html2pdf->output(__DIR__ . "/pdfs/" . $filename, F);
            $sqlupdate =
                "update client_visites set  pdf_file='$filename' where id ='" .
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
                $attach = $dataResult[0]["IMAGE"];
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
                logs($api_name, $input, $output, $platform, $ip_address);
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

        $data = ["status" => false, "message" => "Employee Token is misssing"];
        echo json_encode($data);

        //log code start
        $output = json_encode($data);
        $api_name = "entry visit";
        logs($api_name, $input, $output, $platform, $ip_address);
        //log code end
    }
}

function sendInvoiceAddVisit($visit_id)
{
    $db = connect_db();

    $invoiceSql =
        "SELECT client_visites.duration,client_visites.id as visit_id,client_visites.client_name,client_visites.distance,client_visites.visit_date,company_clients.company_name,company_clients.work_rate,company_clients.mileage_rate,client_visites.visit_address AS ca_postal, workers.postal_code AS ic_postal,company_clients.due_date_range,company_clients.email,companies.send_invoice_status_client,companies.email as company_email,company_clients.company_id,company_clients.office_address as client_address,company_clients.postal_code as client_postal_code,companies.company_name as admin_company_name,companies.postal_code as admin_postal_code,companies.address as admin_company_address,client_visites.IMAGE  FROM `client_visites` JOIN workers ON(client_visites.employee_id=workers.id) JOIN company_clients ON(client_visites.company_id=company_clients.id) 
             JOIN companies ON(companies.id=company_clients.company_id)
	 WHERE client_visites.id=" . $visit_id;

    $resultinvoice = $db->query($invoiceSql);
    $dataResult = [];
    if ($resultinvoice->num_rows > 0) {
        while ($row = $resultinvoice->fetch_assoc()) {
            $dataResult[] = $row;
        }
    }

    /*$email=$dataResult[0]['email'];*/
    $distance = (float) $dataResult[0]["distance"] * 2;
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
$requestUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;

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
        str_pad($dataResult[0]["visit_id"], 6, "0", STR_PAD_LEFT) .
        '</td>



		</tr>
		<tr>
			<td style="color:#4c4b49;">' .
        $dataResult[0]["company_name"] .
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
		</tr>

		<tr>
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
		</tr>

	   </tbody>

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

    if ($dataResult[0]["company_name"] != "") {
        $string = str_replace(" ", "", $dataResult[0]["company_name"]);
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
    $html2pdf = new Html2Pdf("L", "A4", "fr", true, "UTF-8", 0);
    /*$html2pdf->writeHTML($pdfHtml2);*/

    $html2pdf->writeHTML($pdfHtml);

    $html2pdf->output(__DIR__ . "/pdfs/" . $filename, F);

    $sqlupdate =
        "update client_visites set  pdf_file='$filename' where id ='" .
        $visit_id .
        "' ";
    $db->query($sqlupdate);

    $email = "";
    if ($dataResult[0]["send_invoice_status_client"] == 0) {
        $email = $dataResult[0]["company_email"];
    } else {
        $email = $dataResult[0]["email"];
    }

    $attach = $dataResult[0]["IMAGE"];

    // echo "ddd";
    //  echo $attach;
    // echo $email;
    // echo $filename;

    //  die;
    //
    // if($attach==''){
    //              	sendEmail2($email,$filename);

    //             }
    // else
    // {
    //   sendEmail1($email1,$filename,$image);
    // }
}

function sendInvoiceEmail($email, $fileName, $body)
{
    $file = "pdf_invoice.pdf";
    $htmlContent = "<h1>Invoice has been sent.</h1>";
    $subject = "Eworxs Invoice";
    $headers .= "From: <support@eworxs.app>" . "\r\n";
    $headers .= "Cc: deepak@cresol.in" . "\r\n";
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    $headers .=
        "\nMIME-Version: 1.0\n" .
        "Content-Type: multipart/mixed;\n" .
        " boundary=\"{$mime_boundary}\"";
    $message =
        "--{$mime_boundary}\n" .
        "Content-Type: text/html; charset=\"UTF-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" .
        $htmlContent .
        "\n\n";
    if (!empty($file) > 0) {
        if (is_file($file)) {
            $message .= "--{$mime_boundary}\n";
            $fp = @fopen($file, "rb");
            $data = @fread($fp, filesize($file));

            @fclose($fp);
            $data = chunk_split(base64_encode($data));
            $message .=
                "Content-Type: application/octet-stream; name=\"" .
                basename($file) .
                "\"\n" .
                "Content-Description: " .
                basename($file) .
                "\n" .
                "Content-Disposition: attachment;\n" .
                " filename=\"" .
                basename($file) .
                "\"; size=" .
                filesize($file) .
                ";\n" .
                "Content-Transfer-Encoding: base64\n\n" .
                $data .
                "\n\n";
        }
    }
    $message .= "--{$mime_boundary}--";
    $returnpath = "-f" . $from;

    //send email
    $mail = @mail($email, $subject, $message, $headers, $returnpath);

    //email sending status
    //echo $mail?"Sent":"Mail sending failed.";
}

function sendEmail2($email, $fileName)
{
    $to = $email;
    $from = "support@eworxs.app";
    $fromName = "Eworxs Support";
    $subject = "Eworxs Invoice";
    $file = "pdfs/" . $fileName;

    /*echo $to;

echo $file;die;
*/
    $htmlContent = "<h1>Invoice has been sent.</h1>";
    $headers = "From: $fromName" . " <" . $from . ">";

    //boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    //headers for attachment
    $headers .=
        "\nMIME-Version: 1.0\n" .
        "Content-Type: multipart/mixed;\n" .
        " boundary=\"{$mime_boundary}\"";

    //multipart boundary
    $message =
        "--{$mime_boundary}\n" .
        "Content-Type: text/html; charset=\"UTF-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" .
        $htmlContent .
        "\n\n";

    //preparing attachment
    if (!empty($file) > 0) {
        if (is_file($file)) {
            $message .= "--{$mime_boundary}\n";
            $fp = @fopen($file, "rb");
            $data = @fread($fp, filesize($file));
            @fclose($fp);
            $data = chunk_split(base64_encode($data));
            $message .=
                "Content-Type: application/octet-stream; name=\"" .
                basename($file) .
                "\"\n" .
                "Content-Description: " .
                basename($file) .
                "\n" .
                "Content-Disposition: attachment;\n" .
                " filename=\"" .
                basename($file) .
                "\"; size=" .
                filesize($file) .
                ";\n" .
                "Content-Transfer-Encoding: base64\n\n" .
                $data .
                "\n\n";
        }
    }
    $message .= "--{$mime_boundary}--";
    $returnpath = "-f" . $from;

    //send email
    $mail = @mail($to, $subject, $message, $headers, $returnpath);
    //email sending status
    /*echo $mail?"<h1>Mail sent.</h1>":"<h1>Mail sending failed.</h1>";*/
}

function sendEmail1($email, $fileName = "", $fileName1 = "")
{
    $loop1 = true;

    if ($fileName != "" && $fileName1 != "" && $loop1 == true) {
        /*echo $email;die;*/
        $loop1 = false;

        $to = $email;
        $from = "support@eworxs.app";
        $fromName = "Eworxs Support";
        $subject = "Eworxs Invoice";
        $file = "pdfs/" . $fileName;

        $file2 = "Image/" . $fileName1;
        $htmlContent = "<h1>Invoice has been sent.</h1>";
        $headers = "From: $fromName" . " <" . $from . ">";

        //boundary
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

        //headers for attachment
        $headers .=
            "\nMIME-Version: 1.0\n" .
            "Content-Type: multipart/mixed;\n" .
            " boundary=\"{$mime_boundary}\"";

        //multipart boundary
        $message =
            "--{$mime_boundary}\n" .
            "Content-Type: text/html; charset=\"UTF-8\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" .
            $htmlContent .
            "\n\n";

        //preparing attachment
        if (!empty($file) > 0) {
            if (is_file($file)) {
                $message .= "--{$mime_boundary}\n";
                $fp = @fopen($file, "rb");
                $data = @fread($fp, filesize($file));
                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .=
                    "Content-Type: application/octet-stream; name=\"" .
                    basename($file) .
                    "\"\n" .
                    "Content-Description: " .
                    basename($file) .
                    "\n" .
                    "Content-Disposition: attachment;\n" .
                    " filename=\"" .
                    basename($file) .
                    "\"; size=" .
                    filesize($file) .
                    ";\n" .
                    "Content-Transfer-Encoding: base64\n\n" .
                    $data .
                    "\n\n";
            }
        }

        //preparing attachment2
        if (!empty($file2) > 0) {
            if (is_file($file2)) {
                $message .= "--{$mime_boundary}\n";
                $fp = @fopen($file2, "rb");
                $data = @fread($fp, filesize($file2));
                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .=
                    "Content-Type: application/octet-stream; name=\"" .
                    basename($file2) .
                    "\"\n" .
                    "Content-Description: " .
                    basename($file2) .
                    "\n" .
                    "Content-Disposition: attachment;\n" .
                    " filename=\"" .
                    basename($file2) .
                    "\"; size=" .
                    filesize($file2) .
                    ";\n" .
                    "Content-Transfer-Encoding: base64\n\n" .
                    $data .
                    "\n\n";
            }
        }

        $message .= "--{$mime_boundary}--";
        $returnpath = "-f" . $from;

        //send email
        $mail = @mail($to, $subject, $message, $headers, $returnpath);
        //email sending status
        /*echo $mail?"<h1>Mail sent.</h1>":"<h1>Mail sending failed.</h1>";
         die;*/
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
        logs($api_name, $input, $output, $platform, $ip_address);
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
        logs($api_name, $input, $output, $platform, $ip_address);
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
        logs($api_name, $input, $output, $platform, $ip_address);
        //log code end
    } 

   else
   {
        if (userauthorization()) 
        {
            $db = connect_db();

            $sql =
                "SELECT client_visites.id as id,client_visites.company_id as companyClientId,companies.company_name as clientCompanyName,client_visites.client_name as clientName,client_visites.visit_date as visitDate,CONCAT(client_visites.duration, ' h')  AS duration,client_visites.distance,client_visites.pdf_file as pdfFile FROM client_visites left join company_clients on company_clients.id=client_visites.company_id left join companies on companies.id = company_clients.company_id where client_visites.employee_id='".$worker_id."' ORDER BY client_visites.id desc LIMIT ".$offset.", ".$limit."";


            $data = []; // array variable
            if ($result = $db->query($sql)) 
            {
                while ($row = $result->fetch_assoc()) 
                {
                    $data[] = $row;
                }
            }
            $db = null;
            //log code start
            $ip_address = get_client_ip();
            $platform = funcDeviceType();
            $input_array = [];
            $input_array["employee_id"] = $worker_id;
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
            logs($api_name, $input, $output, $platform, $ip_address);
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

            $data = ["Status" => false, "Message" => "Employee Token is misssing"];
            echo json_encode($data);

            //log code start
            $output = json_encode($data);
            $api_name = "get visit by worker id";
            logs($api_name, $input, $output, $platform, $ip_address);
            //log code end
        }

    }

}

function get_visit_details($visit_id)
{
    if (userauthorization()) {
        $db = connect_db();
        $sql = "SELECT * FROM client_visites where id='" . $visit_id . "'";
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
        logs($api_name, $input, $output, $platform, $ip_address);
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

        $data = ["Status" => false, "Message" => "Employee Token is misssing"];
        echo json_encode($data);
        //log code start
        $output = json_encode($data);
        $api_name = "get visit details";
        logs($api_name, $input, $output, $platform, $ip_address);
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

function company_visit_count($employee_id, $company_client_id)
{
    $db = connect_db();
    $sql =
        "SELECT count(id) as visit_count from client_visites where employee_id=" .
        $employee_id .
        " and company_id =" .
        $company_client_id;
    /*	echo $sql;*/
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

    //AIzaSyCImd8GOXALwOh6N-Z6LbS7mY27NDAKSno

    // $url ="https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyArmhvQFhccQQ6MQeo2CpxGatou__6R_pg&address=" .urlencode($visit_address);

    // $geocode = file_get_contents($url);
    // $json = json_decode($geocode);
    // $vist_address_lat = $json->results[0]->geometry->location->lat;
    // $vist_address_lng = $json->results[0]->geometry->location->lng;

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

// function distance_calculator($emp_id, $visit_address)
// {

//     $visit_address = $visit_address;
//     $db = connect_db();
//     $sql = "SELECT latitude,longitude from workers where id=" . $emp_id;
//     $worker_origin_lat = "";
//     $worker_origin_lng = "";
//     $data = [];
//     if ($result = $db->query($sql)) {
//         while ($row = $result->fetch_assoc()) {
//             // use fetch_assoc here
//             $worker_origin_lat = $row["latitude"]; // assign each value to array
//             $worker_origin_lng = $row["longitude"];
//         }
//     }
//     $vist_address_lat = "";
//     $vist_address_lng = "";

//     //AIzaSyCImd8GOXALwOh6N-Z6LbS7mY27NDAKSno

//     $url ="https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyArmhvQFhccQQ6MQeo2CpxGatou__6R_pg&address=" .urlencode($visit_address);

//     $geocode = file_get_contents($url);
//     $json = json_decode($geocode);
//     $vist_address_lat = $json->results[0]->geometry->location->lat;
//     $vist_address_lng = $json->results[0]->geometry->location->lng;
//     $res12 = GetDistanceMatrix(
//         $worker_origin_lat,
//         $worker_origin_lng,
//         $vist_address_lat,
//         $vist_address_lng
//     );
//     return $res12;
// }

function logs($api_name, $input, $output, $platform, $ip_address)
{
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
    $sqlInsert =
        "insert into logs(api_name,input,token,output,platform,ip_address_val)" .
        " VALUES('" .
        $api_name_val .
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
