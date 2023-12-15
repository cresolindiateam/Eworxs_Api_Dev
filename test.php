<?php 
echo "hello";
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


$app->run();


echo "dkhbhdbhd";
?>