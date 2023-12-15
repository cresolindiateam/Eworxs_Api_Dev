<?php
include "header.php";
require_once "../stripe-php-master/init.php";



$get_current_plan = "";
$company_id = $_SESSION["companyid"];
$db = db_connect();
$plan_name="";
$status="";
$sub_start="";
$sub_end="";
if ($company_id != "") 
{
$plan_name="";
$status="";
$sub_start="";
$sub_end="";
    $checkoutsession_id = "";
    $plan_sql ="SELECT checkout_session_id FROM companies WHERE id = " . $company_id;
    $plan_exe = $db->query($plan_sql);
    if ($plan_exe->num_rows > 0) 
    {
        $dataResult = $plan_exe->fetch_all(MYSQLI_ASSOC);
        $checkoutsession_id = $dataResult[0]["checkout_session_id"];
    }
    if ($checkoutsession_id != "") 
    {

        // echo "hello";die;
        $url = "";
        $result = "";
        $json = "";
        $sub_id = "";
        $url ="https://api.stripe.com/v1/checkout/sessions/" .$checkoutsession_id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $headers = [];
        $headers[] = "Accept: application/json";
        $headers[] ="Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) 
        {
            echo "Error:" . curl_error($ch);
        }
        curl_close($ch);
        $json = json_decode($result, true);
// echo "<pre>";
//         print_r($json);die;
        $sub_id = $json["subscription"];
        if ($sub_id != "")
         {
            $url1 = "https://api.stripe.com/v1/subscriptions/" . $sub_id;
            // echo $url1;die;
            $ch1 = curl_init();
            curl_setopt($ch1, CURLOPT_URL, $url1);
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "GET");
            $headers1 = [];
            $headers1[] = "Accept: application/json";
            $headers1[] ="Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
            curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers1);
            $result1 = curl_exec($ch1);
            if (curl_errno($ch1)) 
            {
                echo "Error:" . curl_error($ch1);
            }
            curl_close($ch1);
            $get_current_plan = "";
            $json1 = json_decode($result1, true);

            $plan_id_value = $json1["items"]["data"][0]["plan"]["id"];
            $plan_sql1 ="SELECT plan_name,plan_id,id FROM plans WHERE plan_id = '" .
                            $plan_id_value ."'";
            
// echo $plan_id_value;die;
            $plan_exe1 = $db->query($plan_sql1);
              if($plan_exe1->num_rows > 0) 
                {
                            $dataResult1 = $plan_exe1->fetch_all(MYSQLI_ASSOC);
                            $plan_name = $dataResult1[0]["plan_name"];
                            $plan_id = $dataResult1[0]["plan_id"];
                            $get_current_plan = $dataResult1[0]["id"];
                            $status = $json1["status"];
                             $month =$json1["items"]["data"][0]["price"]["recurring"]["interval"];
                             $interval =$json1["items"]["data"][0]["price"]["recurring"]["interval_count"];
                         $duration = "+" . $interval . " " . $month;
                         $sub_start = date("d M Y", $json1["created"]);
                         $startDate = new DateTime($sub_start);
                          $startDate->modify($duration);
                         $sub_end = $startDate->format('d M Y');


                }
            }
        } 
    else 
    {

      $plan_sql ="SELECT plan_id,subscription_id,created_at FROM `company_subscriptions` where company_id=".$company_id." and status=1 ORDER BY `id` DESC limit 1";
      
        $plan_exe = $db->query($plan_sql);
        if ($plan_exe->num_rows > 0) 
        {
           
            $dataResult = $plan_exe->fetch_all(MYSQLI_ASSOC);

             $get_current_plan  = $dataResult[0]["plan_id"];


                // if($get_current_plan== 41){
               $plan_sql11 ="SELECT plan_name,plan_id,id FROM plans WHERE id = '" .
                            $get_current_plan ."'";
                           
            $plan_exe11 = $db->query($plan_sql11);
              if($plan_exe11->num_rows > 0) 
                {
                            $dataResult11 = $plan_exe11->fetch_all(MYSQLI_ASSOC);
                            $plan_name = $dataResult11[0]["plan_name"];
                            $status = 'active';
                          
                }


                    $sub_id=$dataResult[0]['subscription_id'];
                    if($sub_id!="")
                    {
                    $url1 = "https://api.stripe.com/v1/subscriptions/" . $sub_id;
                    
                $ch1 = curl_init();
                curl_setopt($ch1, CURLOPT_URL, $url1);
                curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "GET");
                $headers1 = [];
                $headers1[] = "Accept: application/json";
                $headers1[] = "Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
                curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers1);
                $result1 = curl_exec($ch1);
                if (curl_errno($ch1))
                {
                    echo "Error:" . curl_error($ch1);
                }
                curl_close($ch1);
                $json1 = json_decode($result1, true);
                 
                $sub_id_value = $json1["items"]["data"][0]["id"];
                $status = $json1["status"];
                $plan_id_value = $json1["items"]["data"][0]["plan"]["id"];
                $month =$json1["items"]["data"][0]["price"]["recurring"]["interval"];
                $interval =$json1["items"]["data"][0]["price"]["recurring"]["interval_count"];
                $duration = "+" . $interval . " " . $month;
                $sub_start = date("d M Y", $json1["created"]);
                $startDate = new DateTime($sub_start);
                          $startDate->modify($duration);
                         $sub_end = $startDate->format('d M Y');


               }
               else
               {
                 $get_current_plan = 41;
              $plan_sql1 ="SELECT plan_name,plan_id,id FROM plans WHERE id = '" .
                            $get_current_plan ."'";
            $plan_exe1 = $db->query($plan_sql1);
              if($plan_exe1->num_rows > 0) 
                {

                    //print_r($dataResult[0]);die; 
                            $dataResult1 = $plan_exe1->fetch_all(MYSQLI_ASSOC);
                            $plan_name = $dataResult1[0]["plan_name"];
                            $plan_id = $dataResult1[0]["plan_id"];
                            $get_current_plan = $dataResult1[0]["id"];
                            //$sub_start = date("d M Y",$dataResult[0]["created_at"]);


                             $startDate = new DateTime($dataResult[0]["created_at"]);
                          $startDate->modify($startDate);
                         $sub_start = $startDate->format('d M Y');
                           
                         

                            $sub_end = '-';
                            $status ='active';


                }
               }

        }
        else
        {
              $get_current_plan = 41;
              $plan_sql1 ="SELECT plan_name,plan_id,id FROM plans WHERE id = '" .
                            $get_current_plan ."'";
            $plan_exe1 = $db->query($plan_sql1);
              if($plan_exe1->num_rows > 0) 
                {
                            $dataResult1 = $plan_exe1->fetch_all(MYSQLI_ASSOC);
                            $plan_name = $dataResult1[0]["plan_name"];
                            $plan_id = $dataResult1[0]["plan_id"];
                            $get_current_plan = $dataResult1[0]["id"];
                }
        }
    }
}

if (isset($_POST["updateplan"])) 
{
    $radioVal = $_POST["fav_language"];
    $quantity=1;
    if(isset($_POST["noofworkers"]) && $_POST["noofworkers"]!="")
    {
      $quantity = $_POST["noofworkers"];
    }


    $company_id = $_SESSION["companyid"];
    $db = db_connect();
    if ($company_id != "") 
    {
        $checkoutsession_id = "";
        $plan_sql ="SELECT checkout_session_id FROM companies WHERE id = " .$company_id;
        $plan_exe = $db->query($plan_sql);
        if ($plan_exe->num_rows > 0) 
        {
            $dataResult = $plan_exe->fetch_all(MYSQLI_ASSOC);
            $checkoutsession_id = $dataResult[0]["checkout_session_id"];
        }
        if ($checkoutsession_id != "") 
        {
            $url = "";
            $result = "";
            $json = "";
            $sub_id = "";
            $url ="https://api.stripe.com/v1/checkout/sessions/" .$checkoutsession_id;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $headers = [];
            $headers[] = "Accept: application/json";
            $headers[] = "Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) 
            {
                echo "Error:" . curl_error($ch);
            }
            curl_close($ch);
            $json = json_decode($result, true);
            $sub_id = $json["subscription"];
            if ($sub_id != "") 
            {
                $url1 = "";
                $result1 = "";
                $json1 = "";
                $status = "";
                $plan_id_value = "";
                $sub_start = "";
                $currency = "";
                $interval = "";
                $month = "";
                $plan_name = "";
                $plan_id = "";
                $sub_end = "";
                $duration = "";
                $sub_id_value = "";
                $plan_db_id = "";
                $url1 = "https://api.stripe.com/v1/subscriptions/" . $sub_id;
                $ch1 = curl_init();
                curl_setopt($ch1, CURLOPT_URL, $url1);
                curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "GET");
                $headers1 = [];
                $headers1[] = "Accept: application/json";
                $headers1[] = "Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
                curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers1);
                $result1 = curl_exec($ch1);
                if (curl_errno($ch1))
                {
                    echo "Error:" . curl_error($ch1);
                }
                curl_close($ch1);
                $json1 = json_decode($result1, true);
                $sub_id_value = $json1["items"]["data"][0]["id"];
                $status = $json1["status"];
                $plan_id_value = $json1["items"]["data"][0]["plan"]["id"];
                $month =$json1["items"]["data"][0]["price"]["recurring"]["interval"];
                $interval =$json1["items"]["data"][0]["price"]["recurring"]["interval_count"];
                $duration = "+" . $interval . " " . $month;
                $sub_start = date("d M Y", $json1["created"]);
                $sub_startstrtotime = strtotime($sub_start);
                $sub_end = date("d M Y",strtotime("+1 month", $sub_startstrtotime)
                );
                $plan_sql1 =
                    "SELECT plan_name,plan_id,id FROM plans WHERE plan_id = '" .
                    $plan_id_value .
                    "'";
                $plan_exe1 = $db->query($plan_sql1);
                if ($plan_exe1->num_rows > 0) {
                    $dataResult1 = $plan_exe1->fetch_all(MYSQLI_ASSOC);
                    $plan_name = $dataResult1[0]["plan_name"];
                    $plan_id = $dataResult1[0]["plan_id"];
                    $plan_db_id = $dataResult1[0]["id"];
                }
                if ($sub_id_value != "" && $radioVal != "") {
                    $data12 = "quantity=".$quantity."&price=" . $radioVal;
                    $url =
                        "https://api.stripe.com/v1/subscription_items/" .
                        $sub_id_value;
                    $ch1734 = curl_init();
                    curl_setopt($ch1734, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1734, CURLOPT_URL, $url);
                    curl_setopt($ch1734, CURLOPT_POST, 1);
                    curl_setopt($ch1734, CURLOPT_POSTFIELDS, $data12);
                    $headers = [];
                    $headers[] = "Accept: application/json";
                    $headers[] =
                        "Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
                    curl_setopt($ch1734, CURLOPT_HTTPHEADER, $headers);

                    $result1734 = curl_exec($ch1734);
                    if (curl_errno($ch1734)) {
                        echo "Error:" . curl_error($ch1734);
                    }
                    $current_plan_id = "";
                    $current_plan_id = $_POST["currentplanid"];

                    if ($result1734 != "") {
                         $sql = "update company_subscriptions set status =0 WHERE company_id='$company_id' and plan_id='$current_plan_id'";
                        $db->query($sql);


                    
                       /* echo $sql;
                        die();*/
                

                        $sql = "update companies  set checkoutsession_id ='' WHERE id='$company_id' ";
                       /* echo $sql;
                        die();*/
                        $db->query($sql);

                         $sql = "update companies  set no_of_workers =".$quantity." WHERE id='$company_id' ";
                       /* echo $sql;
                        die();*/
                        $db->query($sql);

                        

                        $update_active_plan_id = "";
                        $plan_sql1234 =
                            "SELECT id FROM plans WHERE plan_id = '" .
                            $radioVal .
                            "'";
                        $plan_exe1234 = $db->query($plan_sql1234);
                        if ($plan_exe1234->num_rows > 0) {
                            $dataResult1234 = $plan_exe1234->fetch_all(
                                MYSQLI_ASSOC
                            );
                            $update_active_plan_id = $dataResult1234[0]["id"];
                        }
                        $sql =
                            "insert into company_subscriptions(plan_id,company_id,subscription_id,no_of_workers,status,created_at) value(" .
                            $update_active_plan_id .
                            "," .
                            $company_id .
                            ",'" .
                            $result1734->subscription .
                            "'," .
                            $quantity .
                            ",1,now())";
                        $db->query($sql);

                           
                    }
                }
            }
        } else {

            $url = "https://api.stripe.com/v1/customers";
            $ch1769 = curl_init();
            curl_setopt($ch1769, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch1769, CURLOPT_URL, $url);
            curl_setopt($ch1769, CURLOPT_POST, 1);
            $headers = [];
            $headers[] = "Accept: application/json";
            $headers[] ="Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
            curl_setopt($ch1769, CURLOPT_HTTPHEADER, $headers);
            $result1769 = curl_exec($ch1769);
            $json1769 = json_decode($result1769, true);
            if ($json1769 != "") {
                $url = "https://api.stripe.com/v1/payment_methods";
                $ch17961 = curl_init();
                curl_setopt($ch17961, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch17961, CURLOPT_URL, $url);
                curl_setopt($ch17961, CURLOPT_POST, 1);
                curl_setopt($ch17961,CURLOPT_POSTFIELDS,"type=card&card[number]=" .$_POST["card_number"] ."&card[exp_month]=" .$_POST["expiry_month"] ."&card[exp_year]=" .$_POST["expiry_year"] ."&card[cvc]=" .$_POST["cvc"] ."");
                $headers = [];
                $headers[] = "Accept: application/json";
                $headers[] ="Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
                curl_setopt($ch17961, CURLOPT_HTTPHEADER, $headers);
                $res17961 = curl_exec($ch17961);
                $data17961 = json_decode($res17961);

                if ($data17961 != "") 
                {
                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL,"https://api.stripe.com/v1/payment_methods/" .$data17961->id ."/attach");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, "customer=" . $json1769["id"] . "");
                    curl_setopt($ch,CURLOPT_USERPWD,"sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm" . ":" . "");

                    $headers = [];
                    $headers[] ="Content-Type: application/x-www-form-urlencoded";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo "Error:" . curl_error($ch);
                    }
                    curl_close($ch);
                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL,"https://api.stripe.com/v1/customers/" .$json1769["id"] ."");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch,CURLOPT_POSTFIELDS,"name=" .$company_id .
                            "&invoice_settings[default_payment_method]=" .$data17961->id
                    );
                    curl_setopt($ch,CURLOPT_USERPWD,"sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm" . ":" . "");
                    $headers = [];
                    $headers[] ="Content-Type: application/x-www-form-urlencoded";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result7777 = curl_exec($ch);
                    $data7777 = json_decode($result7777);
                    if (curl_errno($ch)) {
                        echo "Error:" . curl_error($ch);
                    }
                    curl_close($ch);

                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL,"https://api.stripe.com/v1/invoices");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch,CURLOPT_POSTFIELDS,"customer=" . $json1769["id"] . "");
                    curl_setopt($ch,CURLOPT_USERPWD,"sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm" . ":" . "");
                    $headers = [];
                    $headers[] ="Content-Type: application/x-www-form-urlencoded";
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo "Error:" . curl_error($ch);
                    }
                    curl_close($ch);
                }

                if ($data7777 != "") 
                {
                    $data1796 ="items[0][quantity]=".$quantity."&items[0][price]=" .$radioVal ."&customer=" .$json1769["id"];
                    $url = "https://api.stripe.com/v1/subscriptions";
                    $ch1796 = curl_init();
                    curl_setopt($ch1796, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1796, CURLOPT_URL, $url);
                    curl_setopt($ch1796, CURLOPT_POST, 1);
                    curl_setopt($ch1796, CURLOPT_POSTFIELDS, $data1796);
                    $headers = [];
                    $headers[] = "Accept: application/json";
                    $headers[] ="Authorization: Bearer sk_test_k11gZlDKwJ3iUInZzkVlcivP00eWZkWKVm";
                    curl_setopt($ch1796, CURLOPT_HTTPHEADER, $headers);
                    $result1796 = curl_exec($ch1796);
                    if ($result1796 != "") 
                    {
                        $sub_id_value = "";
                        $status = "";
                        $plan_id_value = "";
                        $month = "";
                        $interval = "";
                        $duration = "";
                        $sub_start = "";
                        $sub_end = "";
                        $plan_db_id = "";
                        $json1 = json_decode($result1796, true);
                        $sub_id_value = $json1["items"]["data"][0]["id"];
                        $status = $json1["status"];
                        $plan_id_value =
                            $json1["items"]["data"][0]["plan"]["id"];
                        $month =
                            $json1["items"]["data"][0]["price"]["recurring"][
                                "interval"
                            ]; 
                        $interval =
                            $json1["items"]["data"][0]["price"]["recurring"][
                                "interval_count"
                            ];
                        $duration = "+" . $interval . " " . $month;
                        $sub_start = date("d M Y", $json1["created"]);
                        $sub_startstrtotime = strtotime($sub_start);
                        $sub_end = date(
                            "d M Y",
                            strtotime("+1 month", $sub_startstrtotime)
                        );
                        $plan_sql1 ="SELECT plan_name,plan_id,id FROM plans WHERE plan_id = '" .
                            $plan_id_value ."'";
                          
                        $plan_exe1 = $db->query($plan_sql1);
                        if ($plan_exe1->num_rows > 0) 
                        {
                            $dataResult1 = $plan_exe1->fetch_all(MYSQLI_ASSOC);
                            $plan_name = $dataResult1[0]["plan_name"];
                            $plan_id = $dataResult1[0]["plan_id"];
                            $plan_db_id = $dataResult1[0]["id"];
                        }

                        $current_plan_id = "";
                        $current_plan_id = $_POST["currentplanid"];
                        $sql = "update company_subscriptions set status =0 WHERE company_id='$company_id' and plan_id='$current_plan_id'";


                        $db->query($sql);

       

                          $sql = "update companies  set checkoutsession_id ='' WHERE id='$company_id' ";
                        $db->query($sql);

                        
                            $sql = "update companies  set no_of_workers =".$quantity." WHERE id='$company_id' ";
                       /* echo $sql;
                        die();*/
                        $db->query($sql);

                        
                         

                        $update_active_plan_id = "";
                        $plan_sql1234 = "SELECT id FROM plans WHERE plan_id = '" .$radioVal ."'";
                        $plan_exe1234 = $db->query($plan_sql1234);
                        if ($plan_exe1234->num_rows > 0) 
                        {
                            $dataResult1234 = $plan_exe1234->fetch_all(
                                MYSQLI_ASSOC
                            );
                            $update_active_plan_id = $dataResult1234[0]["id"];
                        }

                        $sql ="insert into company_subscriptions(plan_id,company_id,subscription_id,no_of_workers,status,created_at) value(" .$update_active_plan_id ."," .$company_id .",'" .$json1["id"] ."'," .$quantity .",1,now())";
                        // echo $sql;die;
                        $db->query($sql);
                    }
                }
            }
        }
    }
}


$sql11 ="SELECT id FROM company_clients WHERE company_id=" .$company_id ." ORDER BY id";
$exe11 = $db->query($sql11);
$client_id = [];
if ($exe11->num_rows > 0) 
{
    $dataResult11 = $exe11->fetch_all(MYSQLI_ASSOC);
    foreach ($dataResult11 as $row11) 
    {
        $client_id[] = $row11["id"];
    }
}

$sql12 ="SELECT id FROM workers WHERE company_id=" . $company_id . " ORDER BY id";
$exe12 = $db->query($sql12);
$emp_id = [];
if ($exe12->num_rows > 0) 
{
    $dataResult12 = $exe12->fetch_all(MYSQLI_ASSOC);
    foreach ($dataResult12 as $row12) 
    {
        $emp_id[] = $row12["id"];
    }
}
$clienttext = implode(",", $client_id);
$emptext = implode(",", $emp_id);
$visit_count = 0;
$sql ="SELECT count(id) as visit_count from client_visites where employee_id in (" .$emptext .
    ") and company_id in (" .$clienttext .")";

$data = [];
if ($result = $db->query($sql)) 
{
    while ($row = $result->fetch_assoc()) 
    {
        $data[] = $row; // assign each value to array
    }
}
$visit_count = $data[0]["visit_count"];
if ($_SESSION["role"] == 1)
 {
    echo "<script> window.location = 'admin_login.php'</script>";
}

$sql = "SELECT company_subscriptions.id,company_subscriptions.subscription_id,company_subscriptions.status,company_subscriptions.created_at,plans.plan_name,plans.amount,plans.duration_type,company_subscriptions.no_of_workers as num_emp FROM company_subscriptions INNER JOIN plans ON (company_subscriptions.plan_id=plans.id)
left JOIN companies ON (companies.id=company_subscriptions.company_id)


 WHERE company_subscriptions.company_id=$company_id ORDER BY company_subscriptions.id";
$exe = $db->query($sql);
$data = $exe->fetch_all(MYSQLI_ASSOC);
foreach ($data as $key => $value) {
    $data[$key]["id"] = $value["id"];
    $data[$key]["subscription_id"] = $value["subscription_id"];
    $data[$key]["status"] = $value["status"];
    $data[$key]["plan_name"] = $value["plan_name"];
    $data[$key]["amount"] = $value["amount"];
    $data[$key]["type"] = $value["duration_type"];
    $data[$key]["num_emp"] = $value["num_emp"];
    $data[$key]["created_at"] = $value["created_at"];
}

$sql = "SELECT * FROM plans WHERE status=1 ORDER BY id";
$exe = $db->query($sql);
$planData = $exe->fetch_all(MYSQLI_ASSOC);
?>




<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>EWORXS | Automatically Track Your Mileage</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
   <script src="https://js.stripe.com/v3/"></script>
</head>

<style>
  body{
    padding: 0px !important;
  }

  .StripeElement {
  box-sizing: border-box;

  height: 40px;

  padding: 10px 12px;

  border: 1px solid transparent;
  border-radius: 4px;
  background-color: white;

  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}

.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
  border-color: #fa755a;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}
</style>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
 
  <?php include "left_side_bar.php"; ?>

 
 
  <!-- Trigger the modal with a button -->
 



  <!-- Modal -->
  <div class="add_plan_modal">
  <div class="modal fade" id="add_plan_modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Buy New Plan</h4>
        </div>
        <div class="modal-body">
          <div class="user-info-area">
            <div class="row ">


              <div class="box-body" style="overflow:scroll">
              <table id="" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Plan Name</th>
                  <th>Amount</th>
                  <th>Type</th>
                  <th>Num Emp</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($planData as $key => $item) {
                    $count = $key + 1;
                    $id = $item["id"];
                    echo "<tr>";
                    echo "<td>" . $count . "</td>";
                    echo "<td>" . $item["plan_name"] . "</td>";
                    echo "<td>" . $item["amount"] . "</td>";
                    if ($item["type"] == 1) {
                        echo "<td>Monthly</td>";
                    } elseif ($item["type"] == 2) {
                        echo "<td>Yearly</td>";
                    } else {
                        echo "<td>Other</td>";
                    }
                    echo "<td>" . $item["num_emp"] . "</td>";
                    echo '<td><button type="button" class="btn theme-btn" id="add-employee-list-btn" onclick="buyNewPlan(' .
                        $item["id"] .
                        "," .
                        $company_id .
                        ')">Buy</button></td>';
                } ?>

              </tbody>
               
              </table>
            </div>

            </div>
          </div>
        </div>
        <div class="modal-footer">

          <!-- <button type="button" class="btn theme-btn" id="add-company-btn" onClick="addNewPlan();">Create</button> -->
          <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancel</button>
        </div>
      </div>
      
    </div>
  </div>
  </div>






  <div class="card_detail_modal">
  <div class="modal fade" id="card_detail_modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Buy New Plan</h4>
        </div>
        <div class="modal-body">
          
            <div class="form-row">
            <input type="text" name="text"/>
              <label for="card-element">
                Credit or debit card
              </label>
              <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
              </div>

              <!-- Used to display form errors. -->
              <div id="card-errors" role="alert"></div>
            </div>

            <button onClick="cardForm();">Submit Payment</button>
         

        </div>
        <div class="modal-footer">

          <!-- <button type="button" class="btn theme-btn" id="add-company-btn" onClick="addNewPlan();">Create</button> -->
          <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancel</button>
        </div>
      </div>
      
    </div>
  </div>
  </div>








  <div class="edit_user_list_modal">
  <div class="modal fade" id="edit_employee_list_modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <p class="test" value="" ></p>
          <h4 class="modal-title">Edit Company </h4>
          <input type="text" class="form-control" id="editUserId" style="display: none;" />
        </div>






         <div class="modal-body">
          <div class="user-info-area">
            <div class="row ">
              <div class="col-md-3">
                <div class="personal-info-label">Company Name</div>
                <input type="text" class="form-control" id="editcompanyName"/>
              </div>

              <div class="col-md-3">
                <div class="personal-info-label">First Name</div>
                <input type="text" class="form-control" id="editfirstName"/>
              </div>

              <div class="col-md-3">
                <div class="personal-info-label">Last Name</div>
                <input type="text" class="form-control" id="editlastName"/>
              </div>

              <div class="col-md-3">
                <div class="personal-info-label">Email</div>
                <input type="text" class="form-control" id="editemail"/>
              </div>
            </div>

            <hr/>

            <div class="row ">
              <div class="col-md-3">
                <div class="personal-info-label">Phone</div>
                <input type="text" class="form-control" id="editphone"/>
              </div>

             
              <div class="col-md-3">
                <div class="personal-info-label">Password</div>
                <input type="text" class="form-control" id="editpassword"/>
              </div>

              <div class="col-md-3">
                <div class="personal-info-label">Postal Code</div>
                <input type="text" class="form-control" id="editpostalCode"/>
              </div>
                 <div class="col-md-3">
                <div class="personal-info-label">Status</div>
                <select class="form-control" id="editstatus">
                  <option value="1">1</option>
                    <option value="2">2</option>
                </select>
              </div>
            </div>
          

            <hr/>

       <!--      <div class="row ">
              <div class="col-md-3">
                <div class="personal-info-label">Work Rate</div>
                <input type="text" class="form-control" id="editworkRate"/>
              </div>

              <div class="col-md-3">
                <div class="personal-info-label">Mileage Rate</div>
                <input type="text" class="form-control" id="editmileageRate"/>
              </div>

              <div class="col-md-3">
                <div class="personal-info-label">Due Date Range</div>
                <input type="text" class="form-control" id="editdateRange"/>
              </div>

            </div> -->

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn theme-btn" id="add-employee-list-btn" onClick="editComapny();">Update</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal" >Cancel</button>
        </div>
      </div>
      
    </div>
  </div>
  </div>




  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        
      <h1 class="make-inline">
        Subscriptions List
      </h1>

<?php 
$data_worker_count=0;
$noofworkerallowedinteambasic=0;
if($_SESSION['role']==2)
{
 $sql18 = "SELECT plan_id FROM  company_subscriptions where company_id =".$_SESSION['companyid']."  and company_subscriptions.status=1 order by id desc limit 1"; 
  $exe181 = $db->query($sql18);
  $data181 = $exe181->fetch_all(MYSQLI_ASSOC);
       if($data181[0]['plan_id']==40)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id
         where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 40 and company_subscriptions.status=1"; 
           $sql1 = "SELECT no_of_workers as worker_count_limit FROM companies where id =".$_SESSION['companyid']; 

                       $exe1711 = $db->query($sql1);
              $data1711 = $exe1711->fetch_all(MYSQLI_ASSOC);

            if(!empty($data1711)){
            foreach ($data1711 as $key => $value1711){
                  $noofworkerallowedinteambasic=$value1711['worker_count_limit']; 
              }}
 
        }

        if($data181[0]['plan_id']==41)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 41 and company_subscriptions.status=1"; 
        }

       if($data181[0]['plan_id']==42)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 42 and company_subscriptions.status=1"; 
        }


        if($data181[0]['plan_id']==43)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 43 and company_subscriptions.status=1"; 
        }

         if($data181[0]['plan_id']==44)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 44 and company_subscriptions.status=1"; 
        }

           if($data181[0]['plan_id']==71)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 71 and company_subscriptions.status=1"; 
        }

          if($data181[0]['plan_id']==72)
        {
         $sql = "SELECT count(workers.id) as worker_count FROM workers left join company_subscriptions on company_subscriptions.company_id=workers.company_id where workers.company_id =".$_SESSION['companyid']." and  company_subscriptions.plan_id= 72 and company_subscriptions.status=1"; 
        }
  $exe171 = $db->query($sql);
  $data171 = $exe171->fetch_all(MYSQLI_ASSOC);
          if(!empty($data171)){
          foreach ($data171 as $key => $value171){
              $data_worker_count=$value171['worker_count']; 
          }
        }

}

if($data181[0]['plan_id']==41 && $data_worker_count<1)
{?>
        <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New <i class="fa fa-plus-circle"></i></button>
<?php } 

else if($data181[0]['plan_id']==42 && $data_worker_count<1)
{?>
        <!-- <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New <i class="fa fa-plus-circle"></i></button> -->
<?php } 

else if($data181[0]['plan_id']==43 && $data_worker_count<1)
{?>
        <!-- <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New <i class="fa fa-plus-circle"></i></button> -->
<?php }


else if($data181[0]['plan_id']==40 && $data_worker_count<$noofworkerallowedinteambasic)
{?>
        <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New  <i class="fa fa-plus-circle"></i></button>
<?php }

else if($data181[0]['plan_id']==44 && $data_worker_count<$noofworkerallowedinteambasic)
{?>
        <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New  <i class="fa fa-plus-circle"></i></button>
<?php }

else if($data181[0]['plan_id']==71)
{?>
        <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New  <i class="fa fa-plus-circle"></i></button>
<?php }

else if($data181[0]['plan_id']==72)
{?>
        <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_employee_list_modal" style="margin-right: 10px;">Create New  <i class="fa fa-plus-circle"></i></button>
<?php }?>


<button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#upgrad" style="margin-right: 10px;">Upgrade Plan <i class="fa fa-plus-circle"></i></button>


<?php include("upgrademodal.php");?>
      <!-- <a href="emp_list_csv.php"><button class="btn btn-warning pull-right">Export&nbsp;&nbsp;<i class="fa fa-file-excel-o"></i></button></a> -->
      
    <!--    <button type="button" class="btn theme-btn pull-right " data-toggle="modal" data-target="#add_plan_modal" style="margin-right: 10px;">Buy New <i class="fa fa-plus-circle"></i></button>
      -->
    </section>


<section class="content" style="padding-bottom: 0px;min-height: unset;">
  <section class="content" style="padding-bottom: 0px;min-height: unset;">
  <div class="row">
        <div class="">

          <div class="box">
            
            <!-- /.box-header -->
            <div class="box-body">

<div class="plan_details_box" style="margin-top: 20px;margin-left: 10px;">
<div class="col-md-3" style="font-size:  16x;  font-weight: 500;">
   <span style="
    text-transform: capitalize;
    font-size: 16px;
    font-weight: 600;
">current plan</span>:-&nbsp;<?php echo $plan_name; ?>
</div>

<div class="col-md-3" style="font-size: 16px; font-weight: 500;">
  <span style="
    text-transform: capitalize;
    font-size: 16px;
    font-weight: 600;
">status</span>:-&nbsp;<?php if ($status == "active") {
    echo '<span style="color:green;text-transform:capitalize">' .
        $status .
        "</span>";
} ?>
</div>

<div class="col-md-3" style="font-size: 16px; font-weight: 500;">
  <span style="
    text-transform: capitalize;
    font-size: 16px;
    font-weight: 600;
">start date</span>:<?php echo $sub_start; ?> 
</div>
<div class="col-md-3" style="font-size: 16px; font-weight: 500;">
  <span style="
    text-transform: capitalize;
    font-size: 16px;
    font-weight: 600;
">next billing date</span>:-&nbsp;<?php echo $sub_end; ?> 
</div>
<div class="col-md-3" style="font-size: 16px; font-weight: 500;">
  <span style="
    text-transform: capitalize;
    font-size: 16px;
    font-weight: 600;
">subscription duration</span>:-&nbsp;<?php echo $interval . " " . $month; ?>
</div>

<div class="col-md-3" style="font-size: 16px; font-weight: 500;">
<?php 
/*echo $plan_id;
echo $visit_count;
echo $plan_name;*/
if (
    intval($plan_id) == 41 &&
    $plan_name == "Self Free" &&
    intval($visit_count) > 9
) { ?>
           <button class="btn btn-primary" onclick="upgradenow()">upgrade now</button>
       <?php }


        ?> 
       </div>
</div>
</div>
</div>
</div>
</div>


</section>
</section>



    <!-- Main content -->
    <section class="content">




<section class="content">
      <div class="row">
        <div class="">

          <div class="box">
            
            <!-- /.box-header -->
            <div class="box-body" style="overflow:scroll">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th style="width: 10px">#</th>
                 <!--  <th>Subscription Id</th> -->
                  <th>Amount</th>
                  <th>Type</th>
                  <th>Num Emp</th>
                  <th>Status</th>
                  <th>Created At</th>
                  <!-- <th>Action</th> -->
                </tr>
                </thead>
                <tbody>

<?php foreach ($data as $key => $item) {
    $count = $key + 1;
    $id = $item["id"];

    echo "<tr>";
    echo "<td>" . $count . "</td>";
    //echo'<td>'.$item['subscription_id'].'</td>';
    echo "<td>" . $item["amount"] . "</td>";
    if ($item["type"] == 1) {
        echo "<td>Monthly</td>";
    } elseif ($item["type"] == 2) {
        echo "<td>Yearly</td>";
    } else {
        echo "<td>Other</td>";
    }
    echo "<td>" . $item["num_emp"] . "</td>";
    if ($item["status"] == 1) {
        echo "<td>Active</td>";
    } else {
        echo "<td>Deactive</td>";
    }
    echo "<td>" . $item["created_at"] . "</td>";

    // if($item['status']==1){
    //   echo'<td><button type="button" class="btn btn-danger" id="add-employee-list-btn" onclick="deactivatePlan('.$item['id'].',0)">Deactivate</button></td>';
    // }else{
    //   echo'<td><button type="button" class="btn theme-btn" id="add-employee-list-btn" onclick="deactivatePlan('.$item['id'].',1)">Activate</button></td>';
    // }
} ?>

              </tbody>
               
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>





     
    </section>

  </div>
  
 

</div>
<!-- ./wrapper -->

<!-- jQuery 2.2.3 -->
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jQuery/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.6 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- Morris.js charts -->
<script src="plugins/jQuery/raphael-min.js"></script>

<!-- daterangepicker -->
<script src="plugins/jQuery/moment.min.js"></script>

<!-- Bootstrap WYSIHTML5 -->
<script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>

<!-- page script -->

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>

<script type="text/javascript">

function cardForm(){
  var d = $("input[name=cardnumber]").val();
  alert(d);
}


function buyNewPlan(id,company_id){
  $.ajax({
      url:"AjaxBuyPlan.php",
      data:{id:id},
      type:'post',
      dataType: 'json',
      async: true,
      success:function(response){
        var status=response['Status'];
        if(status==1){
          createCheckoutSession(response['Message'],id,company_id);
        }else{
          alert(response['Message']);
        }
      },
      error:function(xhr,status,error){
        // var err = eval("(" + xhr.responseText + ")");
        // alert(err.Message);
        console.log(error);
      }
  });
}


function createCheckoutSession(cust_id,plan_id,company_id){
  $.ajax({
      url:"AjaxCheckoutSession.php",
      data:{cust_id:cust_id,plan_id:plan_id,company_id:company_id},
      type:'post',
      dataType: 'json',
      async: true,
      success:function(response){
        var status=response['Status'];
        if(status==1){
          gotoCheckout(response['Message']);
        }else{
          alert(response['Message']);
        }
      },
      error:function(xhr,status,error){
        // var err = eval("(" + xhr.responseText + ")");
        // alert(err.Message);
        console.log(error);
      }
  });
}

function gotoCheckout(session_id){
  var stripe = Stripe('pk_test_yLjBRjPFNtGx6w4D32faGafM');
  stripe.redirectToCheckout({
    // Make the id field from the Checkout Session creation API response
    // available to this file, so you can provide it as parameter here
    // instead of the {{CHECKOUT_SESSION_ID}} placeholder.
    sessionId: session_id
  }).then(function (result) {
    alert(result);
    // If `redirectToCheckout` fails due to a browser or network
    // error, display the localized error message to your customer
    // using `result.error.message`.
  });
}


</script>


<script type="text/javascript">
// Create a Stripe client.
var stripe = Stripe('pk_test_yLjBRjPFNtGx6w4D32faGafM');

// Create an instance of Elements.
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    color: '#32325d',
    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: '#aab7c4'
    }
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
};

// Create an instance of the card Element.
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>.
card.mount('#card-element'); 

</script>

<script>
function upgradenow()
{

  $('#upgrad').modal('show');

}

</script>

<script src="creditcard.js"></script>
<script>
  function cardFormValidate(){
    var cardValid = 0;
    //card number validation
    $('#card_number').validateCreditCard(function(result){
        if(result.valid){
            $("#card_number").removeClass('required');
            cardValid = 1;
        }else{
            $("#card_number").addClass('required');
            cardValid = 0;
        }
    });
    //card details validation
   /* var cardName = $("#name_on_card").val();*/
    var expMonth = $("#expiry_month").val();
    var expYear = $("#expiry_year").val();
    var cvv = $("#cvv").val();
    var regName = /^[a-z ,.'-]+$/i;
    var regMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
    var regYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
    var regCVV = /^[0-9]{3,3}$/;
    if (cardValid == 0) {
        $("#card_number").addClass('required');
        $("#card_number").focus();
        return false;
    }else if (!regMonth.test(expMonth)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").addClass('required');
        $("#expiry_month").focus();
        return false;
    }else if (!regYear.test(expYear)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").addClass('required');
        $("#expiry_year").focus();
        return false;
    }else if (!regCVV.test(cvv)) {
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").removeClass('required');
        $("#cvv").addClass('required');
        $("#cvv").focus();
        return false;
    }else{
        $("#card_number").removeClass('required');
        $("#expiry_month").removeClass('required');
        $("#expiry_year").removeClass('required');
        $("#cvv").removeClass('required');
      /*  $("#name_on_card").removeClass('required');*/
        return true;
    }
}
$(document).ready(function() {
    //card validation on input fields
    $('#paymentForm input[type=text]').on('keyup',function(){
        cardFormValidate();
    });
    $('#updateplan').on('click',function(){
      if(cardFormValidate()){
    $('#paymentForm').submit();
    }
    });
});
</script>

<script>
$(document).ready(function() {
  var inputBox = $("#worker_section");
  var radioButton3 = $("#selfpaidyear");
  var radioButton2 = $("#businessyearly");
  var radioButton = $("#businessmonthly");
   var radioButton5 = $("#businessaceyearly");
  var radioButton4 = $("#businessacemonthly");

var newValue121 = '';
 $('#no_of_workers').on('keyup', function() {
         newValue121 = $.trim($('#no_of_workers').val());
           
        alert(newValue121);
        
        if(newValue121 === '')
        {
            newValue121=1;
        }
        var defaultValue = $("#total_charge_default").val();
        var totalchargeValue = $("#total_charge").text();
        // alert(totalchargeValue);
        // Do something with the new value
        if(newValue121 === '' )
        {  
            console.log("hello");
        console.log(defaultValue);
         $('#total_charge').text(defaultValue*newValue121);
        }
        else
        {
            console.log("hello1");
          $('#total_charge').text(defaultValue*newValue121); 
        } 

    });
  inputBox.hide(); // Initially hide the input box

  // Add a click event handler to the radio button
  radioButton3.click(function() {
    if (radioButton3.is(":checked")) {
      // Show the input box
       var selectedValue = $(this).val();
        
        // Make an AJAX call based on the selected value
        $.ajax({
            url: 'getplanprice.php', // Replace with your actual AJAX endpoint URL
            type: 'POST', // or 'GET' depending on your server-side implementation
            data: { option: selectedValue }, // Pass data to the server
            success: function(response) {
                // alert(response);
                // alert("hello");
                // Update the result div with the AJAX response
                $('#total_charge').text(response);
                 $('#total_charge_default').val(response);
            },
            error: function(xhr, status, error) {
                // Handle errors if the AJAX call fails
                console.error(xhr.responseText);
            }
        });
      
    } else {
      // Hide the input box
      
    }
  });


   
    
   radioButton2.click(function() {
    
    if (radioButton2.is(":checked")) {

 $("#no_of_workers").val(''); 
        // Get the selected radio button's value
        var selectedValue = $(this).val();
        
        // Make an AJAX call based on the selected value
        $.ajax({
            url: 'getplanprice.php', // Replace with your actual AJAX endpoint URL
            type: 'POST', // or 'GET' depending on your server-side implementation
            data: { option: selectedValue }, // Pass data to the server
            success: function(response) {
                // alert(response);
                // alert("hello");
                // Update the result div with the AJAX response
                $('#total_charge').text(response);
                $('#total_charge_default').val(response);
            },
            error: function(xhr, status, error) {
                // Handle errors if the AJAX call fails
                console.error(xhr.responseText);
            }
        });

     inputBox.show();
    } else {
      // Hide the input box
      inputBox.hide();
    }
});

          radioButton4.click(function() {
    
    if (radioButton4.is(":checked")) {

 $("#no_of_workers").val(''); 
        // Get the selected radio button's value
        var selectedValue = $(this).val();
        
        // Make an AJAX call based on the selected value
        $.ajax({
            url: 'getplanprice.php', // Replace with your actual AJAX endpoint URL
            type: 'POST', // or 'GET' depending on your server-side implementation
            data: { option: selectedValue }, // Pass data to the server
            success: function(response) {
                // alert(response);
                // alert("hello");
                // Update the result div with the AJAX response
                $('#total_charge').text(response);
                    $('#total_charge_default').val(response);
            },
            error: function(xhr, status, error) {
                // Handle errors if the AJAX call fails
                console.error(xhr.responseText);
            }
        });

  inputBox.show();
    } else {
      // Hide the input box
      inputBox.hide();
    }

    });

         radioButton5.click(function() {
    
    if (radioButton5.is(":checked")) {

 $("#no_of_workers").val(''); 
        // Get the selected radio button's value
        var selectedValue = $(this).val();
        
        // Make an AJAX call based on the selected value
        $.ajax({
            url: 'getplanprice.php', // Replace with your actual AJAX endpoint URL
            type: 'POST', // or 'GET' depending on your server-side implementation
            data: { option: selectedValue }, // Pass data to the server
            success: function(response) {
                // alert(response);
                // alert("hello");
                // Update the result div with the AJAX response
                $('#total_charge').text(response);
                    $('#total_charge_default').val(response);
            },
            error: function(xhr, status, error) {
                // Handle errors if the AJAX call fails
                console.error(xhr.responseText);
            }
        });

      // Show the input box
      inputBox.show();
    } else {
      // Hide the input box
      inputBox.hide();
    }
  });

     radioButton.click(function() {
    if (radioButton.is(":checked")) {
      // Show the input box
       // alert("hello");
        // Get the selected radio button's value
        $("#no_of_workers").val('');
        var selectedValue = $(this).val();
        
        // Make an AJAX call based on the selected value
        $.ajax({
            url: 'getplanprice.php', // Replace with your actual AJAX endpoint URL
            type: 'POST', // or 'GET' depending on your server-side implementation
            data: { option: selectedValue }, // Pass data to the server
            success: function(response) {
                // Update the result div with the AJAX response
                $('#total_charge').text(response);
                    $('#total_charge_default').val(response);
            },
            error: function(xhr, status, error) {
                // Handle errors if the AJAX call fails
                console.error(xhr.responseText);
            }
        });
      inputBox.show();
    } else {
      // Hide the input box
      inputBox.hide();
    }
  });
});
</script>

<script type="text/javascript">
    $(document).ready(function() {
    // Attach a change event listener to the radio buttons
    // $('input[type=radio][name=fav_language]').click(function() {
    //     alert("hello");
    //     // Get the selected radio button's value
    //     var selectedValue = $(this).val();
        
    //     // Make an AJAX call based on the selected value
    //     $.ajax({
    //         url: 'getplanprice.php', // Replace with your actual AJAX endpoint URL
    //         type: 'POST', // or 'GET' depending on your server-side implementation
    //         data: { option: selectedValue }, // Pass data to the server
    //         success: function(response) {
    //             // Update the result div with the AJAX response
    //             $('#total_charge').text(response);
    //         },
    //         error: function(xhr, status, error) {
    //             // Handle errors if the AJAX call fails
    //             console.error(xhr.responseText);
    //         }
    //     });
    // });

    
    // Attach an input event listener to the input field
    // $('#no_of_workers').on('input', function() {
    //     var newValue = $(this).val();
    //     var totalchargeValue = $(#total_charge).text();
    //     // Do something with the new value
    //     $('#total_charge').text(totalchargeValue*newValue);
    // });


});

</script>

</body>
</html>
