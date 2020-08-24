<?php
/*
  Name: ExpressPay SDK
  Description: Набор функций позволяющий быстро настроить взаимодействие с API сервиса express-pay.by
  Version: 1.0.0
  Author: ООО «ТриИнком»
  Author URI: https://express-pay.by/
 */

define("USE_CURL", true);

/**
 * Class ExpressPay
 */
class ExpressPay
{
    /**
     * @var null
     */
    private $token = null;
    /**
     * @var null
     */
    private $baseUrl = null;
    /**
     * @var null
     */
    private $secretWord = null;
    /**
     * @var bool
     */
    private $useHashCheck = false;

    /**
     * ExpressPay constructor.
     * @param $token
     * @param $url
     * @param bool $useHashCheck
     * @param string $secretWord
     */
    function __construct($token, $url, $useHashCheck = false, $secretWord = "")
    {
        $this->token = $token;
        $this->baseUrl = $url;
        $this->useHashCheck = $useHashCheck;
        $this->secretWord = $secretWord;
    }

    /**
     * @param $requestParams
     * @param $method
     * @return string
     */
    private function computeSignature($requestParams, $method) {
        $normalizedParams = array_change_key_case($requestParams, CASE_LOWER);
        $mapping = array(
            "add-invoice" => array(
                                    "token",
                                    "accountno",
                                    "amount",
                                    "currency",
                                    "expiration",
                                    "info",
                                    "surname",
                                    "firstname",
                                    "patronymic",
                                    "city",
                                    "street",
                                    "house",
                                    "building",
                                    "apartment",
                                    "isnameeditable",
                                    "isaddresseditable",
                                    "isamounteditable"),
            "get-details-invoice" => array(
                                    "token",
                                    "id"),
            "cancel-invoice" => array(
                                    "token",
                                    "id"),
            "status-invoice" => array(
                                    "token",
                                    "id"),
            "get-list-invoices" => array(
                                    "token",
                                    "from",
                                    "to",
                                    "accountno",
                                    "status"),
            "get-list-payments" => array(
                                    "token",
                                    "from",
                                    "to",
                                    "accountno"),
            "get-details-payment" => array(
                                    "token",
                                    "id"),
            "add-card-invoice"  =>  array(
                                    "token",
                                    "accountno",                 
                                    "expiration",             
                                    "amount",                  
                                    "currency",
                                    "info",      
                                    "returnurl",
                                    "failurl",
                                    "language",
                                    "pageview",
                                    "sessiontimeoutsecs",
                                    "expirationdate"),
           "card-invoice-form"  =>  array(
                                    "token",
                                    "cardinvoiceno"),
            "status-card-invoice" => array(
                                    "token",
                                    "cardinvoiceno",
                                    "language"),
            "reverse-card-invoice" => array(
                                    "token",
                                    "cardinvoiceno")
        );
        $apiMethod = $mapping[$method];
        $result = "";
        foreach ($apiMethod as $item){
            $result .= $normalizedParams[$item];
        }
        $hash = strtoupper(hash_hmac('sha1', $result, $this->secretWord));
        return $hash;
    }

    /**
     * @param $numberAccount
     * @param $amount
     * @param $currency
     * @param string $expiration
     * @param string $info
     * @param string $surname
     * @param string $firstName
     * @param string $patronymic
     * @param string $city
     * @param string $street
     * @param string $house
     * @param string $building
     * @param string $apartment
     * @param string $isNameEditable
     * @param string $isAddressEditable
     * @param string $isAmountEditable
     * @param string $emailNotification
     * @return mixed
     */
    public function addInvoice($numberAccount, $amount, $currency, $expiration = "", $info = "", 
                                $surname = "", $firstName = "", $patronymic = "", $city = "", $street = "", $house="", $building = "", 
                                $apartment = "", $isNameEditable = "", $isAddressEditable = "", $isAmountEditable = "", $emailNotification = "") {
        $url = $this->baseUrl . "invoices?token=" . $this->token;
        
        if($this->useHashCheck){
            $requestParams = array(
                "Token" => $this->token,
                "AccountNo" => $numberAccount,
                "Amount" => $amount,
                "Currency" => $currency,
                "Expiration" => $expiration,
                "Info" => $info,
                "Surname" => $surname,
                "FirstName" => $firstName,
                "Patronymic" => $patronymic,
                "City" => $city,
                "Street" => $street,
                "House" => $house,
                "Building" => $building,
                "Apartment" => $apartment,
                "IsNameEditable" => $isNameEditable,
                "IsAddressEditable" => $isAddressEditable,
                "IsAmountEditable" => $isAmountEditable,
                "EmailNotification" => $emailNotification
           );
           $signature = $this->computeSignature($requestParams, "add-invoice");
           $url .= "&signature=" . $signature; 
       }
        $requestParams = array(
                "AccountNo" => $numberAccount,
                "Amount" => $amount,
                "Currency" => $currency,
                "Expiration" => $expiration,
                "Info" => $info,
                "Surname" => $surname,
                "FirstName" => $firstName,
                "Patronymic" => $patronymic,
                "City" => $city,
                "Street" => $street,
                "House" => $house,
                "Building" => $building,
                "Apartment" => $apartment,
                "IsNameEditable" => $isNameEditable,
                "IsAddressEditable" => $isAddressEditable,
                "IsAmountEditable" => $isAmountEditable,
                "EmailNotification" => $emailNotification
        );
                            
        $response = sendRequestPOST($url, $requestParams);  

        return json_decode($response)->InvoiceNo;
    }

    /**
     * @param string $fromDate
     * @param string $toDate
     * @param string $status
     * @param string $accountNo
     * @return mixed
     */
    public function getInvoicesList($fromDate = "", $toDate = "", $status = "", $accountNo = "") {
        $url = $this->baseUrl . "invoices?token=" . $this->token . "&From=" . $fromDate . "&To=" . $toDate . "&AccountNo=" . $accountNo . "&Status=" . $status;

        if($this->useHashCheck) {
            $requestParams = [
                "AccountNo" => $accountNo,
                "From" => $fromDate,
                "To" => $toDate,
                "Status" => $status
            ];

            $signature = $this->computeSignature($requestParams, "get-list-invoices");
            $url .= "&signature=" . $signature;
        }

        return json_decode(sendRequestGET($url));
    }

    /**
     * @param $numberInvoice
     * @return mixed
     */
    public function getInvoiceDetails($numberInvoice) {
        $url = $this->baseUrl . "invoices/" . $numberInvoice . "?token=" . $this->token;

        if($this->useHashCheck)
        {
            $requestParams = [
                "Token" => $this->token,
                "Id" => $numberInvoice
            ];

            $signature = $this->computeSignature($requestParams, "get-details-invoice");
            $url .= "&signature=" . $signature;
        }

        return json_decode(sendRequestGET($url));
    }

    /**
     * @param $numberInvoice
     * @return mixed
     */
    public function invoiceState($numberInvoice) {
        $url = $this->baseUrl . "invoices/" . $numberInvoice . "/status?token=" . $this->token;

        if($this->useHashCheck) {
            $requestParams = [
                "Id" => $numberInvoice
            ];

            $signature = $this->computeSignature($requestParams, "status-invoice");
            $url .= "&signature=" . $signature;
        }

        return json_decode(sendRequestGET($url));
    }

    /**
     * @param $numberInvoice
     * @return mixed
     */
    public function cancelInvoice($numberInvoice) {
        $url = $this->baseUrl . "invoices/" . $numberInvoice . "?token=" . $this->token;

        if($this->useHashCheck) {
            $requestParams = [
                "Id" => $numberInvoice
            ];

            $signature = $this->computeSignature($requestParams, "get-details-invoice");
            $url .= "&signature=" . $signature;
        }

        return json_decode(sendRequestDELETE($url));
    }

    /**
     * @param string $fromDate
     * @param string $toDate
     * @param string $numberPayment
     * @return mixed
     */
    public function getListPayments($fromDate = "", $toDate = "", $numberPayment = "") {
        $url = $this->baseUrl . "payments?token=" . $this->token;

        if($this->useHashCheck) {
            $requestParams = [
                "AccountNo" => $numberPayment,
                "From" => $fromDate,
                "To" => $toDate,
            ];

            $signature = $this->computeSignature($requestParams, "get-list-payments");
            $url .= "&signature=" . $signature;
        }

        return json_decode(sendRequestGET($url));
    }

    /**
     * @param $numberPayment
     * @return mixed
     */
    public function getPaymentDetails($numberPayment) {
        $url = $this->baseUrl . "payments/" . $numberPayment . "?token=" . $this->token;

        if($this->useHashCheck) {
            $requestParams = [
                "PaymentNo" => $numberPayment
            ];

            $signature = $this->computeSignature($requestParams, "get-details-payment");
            $url .= "&signature=" . $signature;
        }

        return json_decode(sendRequestGET($url));
    }
}

/**
 * Class Notification
 */
class Notification {
    /**
     * @var int
     */
    public $cmdtype = 0;
    /**
     * @var int
     */
    public $status = 0;
    /**
     * @var int
     */
    public $paymentNo = 0;
    /**
     * @var string
     */
    public $accountNo = "";
    /**
     * @var int
     */
    public $amount = 0;
    /**
     * @var string
     */
    public $created = "";
    /**
     * @var string
     */
    public $service = "";
    /**
     * @var string
     */
    public $payer = "";
    /**
     * @var string
     */
    public $address = "";

    /**
     * @param $name
     * @param $value
     * @throws Exception
     */
    function __set($name, $value)
    {
        throw new Exception("You can't change fields of this object");
    }

    /**
     * Notification constructor.
     * @param bool $useHashCheck
     * @param string $secretWord
     * @throws Exception
     */
    function __construct($useHashCheck = false, $secretWord = "")
    {
        $signature = isset($_REQUEST['Signature']) ? $_REQUEST['Signature'] : "";
        if($useHashCheck && $signature != $this->computeSignature($_POST["Data"], $secretWord)) {
            throw new Exception('The signature does not match');
        }
        $_data = json_decode($_POST["Data"]);
        $this->cmdtype = $_data->CmdType;
        if($this->cmdtype == 3){
            $this->status = $_data->Status;
        }
        $this->paymentNo = $_data->PaymentNo;
        $this->accountNo = $_data->AccountNo;
        $this->amount = $_data->Amount;
        $this->created = $_data->Created;
        $this->service = $_data->Service;
        $this->payer = $_data->Payer;
        $this->address = $_data->Address;
    }

    /**
     * @param $json
     * @param $secretWord
     * @return null|string
     */
    private function computeSignature($json, $secretWord) {
        $hash = NULL;
        $trimSecretWord = trim($secretWord);
        if (empty($trimSecretWord))
            $hash = strtoupper(hash_hmac('sha1', $json, ""));
        else
            $hash = strtoupper(hash_hmac('sha1', $json, $secretWord));
        return $hash;
    }
}

/**
 * @param $url
 * @return string
 */
function sendRequestGET($url) {
    if(USE_CURL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    else{
        $opts = ['http' =>
            [
                'method'  => 'GET',
                'header'  => 'Content-type: application/x-www-form-urlencoded'
            ]
        ];
        $context  = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
    }
    return $response;
}

/**
 * @param $url
 * @param $params
 * @return string
 */
function sendRequestPOST($url, $params) {
    $post_data = http_build_query($params);

    if(USE_CURL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    else {
        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            ]
        ];
        $context  = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
    }
    return $response;
}

/**
 * @param $url
 * @return string
 */
function sendRequestDELETE($url) {
    if(USE_CURL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    else {
        $opts = ['http' =>
            [
                'method'  => 'DELETE',
                'header'  => 'Content-type: application/x-www-form-urlencoded'
            ]
        ];
        $context  = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
    }
    return $response;
}