<?php

namespace HitPay;

class HitPay 
{
    const version               = 'v1';

    protected $curl;
    protected $endpoint         = 'https://api.hit-pay.com/';
    protected $endpointSandbox  = 'https://api.sandbox.hit-pay.com/';
    protected $apiKey           = null;
    protected $authToken        = null;
    protected $isSandBox        = false;

    /**
    * @param string $apiKey
    */
    public function __construct($apiKey, $isSandBox = false) 
    {
        $this->apiKey       = (string) $apiKey;
        $this->isSandBox    = (string) $isSandBox;
    }

    public function __destruct() 
    {
        if (!is_null($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
    * @return array headers with Authentication tokens added 
    */
    private function build_curl_headers() 
    {
        $headers    = array("X-BUSINESS-API-KEY: $this->apiKey");
        $headers[]  = "X-Requested-With: XMLHttpRequest";

        return $headers;        
    }

    /**
    * @param string $path
    * @return string adds the path to endpoint with.
    */
    private function build_api_call_url($path)
    {
        if (!$this->isSandBox and strpos($path, '/?') === false and strpos($path, '?') === false) {
            return $this->endpoint . self::version . '/' . $path . '/';
        } else if ($this->isSandBox and strpos($path, '/?') === false and strpos($path, '?') === false) {
            return $this->endpointSandbox . self::version . '/' . $path . '/';
        }

        return $this->isSandBox? $this->endpointSandbox . '/' . $path: $this->endpoint . $path;

    }

    /**
    * @param string $method ('GET', 'POST', 'DELETE', 'PATCH')
    * @param string $path whichever API path you want to target.
    * @param array $data contains the POST data to be sent to the API.
    * @return array decoded json returned by API.
    */
    private function api_call($method, $path, array $data=null) 
    {
        $path           = (string) $path;
        $method         = (string) $method;
        $data           = (array) $data;
        $headers        = $this->build_curl_headers();
        $requestUrl     = $this-> build_api_call_url($path);

        $options        = array();
        $options[CURLOPT_HTTPHEADER]        = $headers;
        $options[CURLOPT_RETURNTRANSFER]    = true;
        
        if($method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else if($method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        } else if($method == 'PATCH') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);         
            $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        } else if ($method == 'GET' or $method == 'HEAD') {
            if (!empty($data)) {
                /* Update URL to container Query String of Paramaters */
                $requestUrl .= '?' . http_build_query($data);
            }
        }
        // $options[CURLOPT_VERBOSE] = true;

        $options[CURLOPT_URL]               = $requestUrl;
        $options[CURLOPT_SSL_VERIFYPEER]    = false;

        $this->curl     = curl_init();
        $setopt         = curl_setopt_array($this->curl, $options);
        $response       = curl_exec($this->curl);
        $headers        = curl_getinfo($this->curl);

        $errorNumber   = curl_errno($this->curl);
        $errorMessage  = curl_error($this->curl);
        $responseObj   = json_decode($response, true);

        if ($errorNumber != 0){
            if($errorNumber == 60){
                throw new \Exception("Something went wrong. cURL raised an error with number: $errorNumber and message: $errorMessage. " .
                                    "Please check http://stackoverflow.com/a/21114601/846892 for a fix." . PHP_EOL);
            }
            else{
                throw new \Exception("Something went wrong. cURL raised an error with number: $errorNumber and message: $errorMessage." . PHP_EOL);
            }
        }

        if ($responseObj['success'] == false) {
            $message = json_encode($responseObj['message']);
            throw new \Exception($message . PHP_EOL);
        }

        return $responseObj;
    }

    /////    HMAC   /////

    public function generateSignatureArray($secret, array $args) 
    {   
        $hmacSource = [];        

        foreach ($args as $key => $val) {
            $hmacSource[$key] = "{$key}{$val}";
        }    

        ksort($hmacSource);

        $sig            = implode("", array_values($hmacSource));
        $calculatedHmac = hash_hmac('sha256', $sig, $secret); 

        return $calculatedHmac;
    }


    /////   Request a Payment  /////

    /**
    * @param array single PaymentRequest object.
    * @return array single PaymentRequest object.
    */
    public function paymentRequestCreate(array $data) 
    {
        $response = $this->api_call('POST', 'payment-requests', $data); 

        return $response;
    }

    /**
    * @param string id as provided by paymentRequestCreate, paymentRequestsList, webhook or redirect.
    * @return array single PaymentRequest object.
    */
    public function paymentRequestStatus($id) 
    {
        $response = $this->api_call('GET', 'payment-requests/' . $id, array()); 

        return $response;
    }

    /**
    * @return array containing list of PaymentRequest objects.
    */
    public function paymentRequestsList() 
    {
        $response = $this->api_call('GET', 'payment-requests', array());

        return $response;
    }
}
