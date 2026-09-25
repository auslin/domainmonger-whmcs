<?php

require 'init.php';

$customers = [
3034                           
];

foreach($customers as $customer){
    try{
    $command = 'GetPayMethods';
    $postData = array(
        'clientid' => $customer,
    );
    $results = localAPI($command, $postData);
    $payMethods = $results['paymethods'];
    $payMethodsDb = \WHMCS\Database\Capsule::table('tblpaymethods')->where('userid',$customer)->get();

    deleteExpired($payMethods);
    //deleteDuplicated($payMethods);
    fixDefault($customer, $payMethodsDb);
    }catch(Exception $e){
        echo $e->getMessage();
    }

}

echo "</br>Task completed";


function deleteDuplicated($payMethods){

}

// Set the last payment method as default
function fixDefault($customerId, $payMethods){
    $defaultCards = 0;
    $lastCard = 0;
    $hasCardNotExpired = false;
    $payMethodToDelete = [];
    foreach($payMethods as $k => $payMethod){
        if($payMethod->order_preference == 0){
            $defaultCards++;
            $lastCard = $payMethod->id;
        }
    }

    if($defaultCards > 1){
        \WHMCS\Database\Capsule::table('tblpaymethods')->where('userid',$customerId)->update(
            [
                'order_preference' => 1
            ]
        );
        \WHMCS\Database\Capsule::table('tblpaymethods')->where('id',$lastCard)->update(
            [
                'order_preference' => 0
            ]
        );
    }
}

function deleteExpired($payMethods){
    $hasCardNotExpired = false;
    $payMethodToDelete = [];
    foreach($payMethods as $k => $payMethod){
        $expireDate = explode('/',$payMethod['expiry_date']);

        if( ( $expireDate[1] < date('y')  )  || ( ($expireDate[0] < date('m') ) && ( $expireDate[1] < date('y') ) ) ){
            $payMethodToDelete[] = $payMethod;
        }else{
            $hasCardNotExpired = true;
        }
    }
    if( $hasCardNotExpired ){
        foreach( $payMethodToDelete  as $pm ){
            \WHMCS\Database\Capsule::table('tblcreditcards')->where('pay_method_id',$pm['id'])->delete();
            \WHMCS\Database\Capsule::table('tblpaymethods')->where('id',$pm['id'])->delete();
            echo "</br>Removed EXPIRED pay method {$pm['id'] } from customer {$pm['contact_id']}</br>";
        }
    }else{
        foreach( $payMethodToDelete  as $pm ){
            $expireDate = explode('/',$payMethod['expiry_date']);
            if($expireDate[1] < "17"){
                \WHMCS\Database\Capsule::table('tblcreditcards')->where('pay_method_id',$pm['id'])->delete();
                \WHMCS\Database\Capsule::table('tblpaymethods')->where('id',$pm['id'])->delete();
                echo "</br>Removed EXPIRED pay method {$pm['id'] } from customer {$pm['contact_id']}</br>";
            }
        }
    }

}
