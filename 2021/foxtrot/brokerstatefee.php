<?php
// print_r($_GET);

require_once("include/config.php");
require_once(DIR_FS."islogin.php");

$instance = new transaction();
$get_brokers=$instance->select_broker();
$client_maintenance_instance = new client_maintenance();
$get_states = $client_maintenance_instance->select_state();
    $broker_instance = new broker_master();
    if(isset($_POST['submit']) && $_POST['submit']=='Save') {
     
       $res= $broker_instance->save_broker_state_fee($_POST);
         header("location:/CloudFox/brokerstatefee.php?msg=success&broker_id=".$_POST['broker']);exit;
    }
    $broker_id=isset($_GET['broker_id']) ? $_GET['broker_id']: 0;
    if($broker_id){
         $feeData= (array) $broker_instance->load_broker_state_fee($broker_id);
    }
$content = "brokerstatefee";
require_once(DIR_WS_TEMPLATES."main_page.tpl.php");