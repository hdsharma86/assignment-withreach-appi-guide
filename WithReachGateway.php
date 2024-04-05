<?php
/**
 * @class WithReachGateway
 */
class WithReachGateway {

    protected $authToken;
    protected $createSessionAPIUrl = "https://api.withreach.com/v1/session";
    protected $createOrderAPIUrl = "https://api.sandbox.withreach.com/v1/orders";
    protected $stashAPIUrl = "https://stash.rch.how";
    protected $refundAPIUrl = "https://api.sandbox.withreach.com/v1/orders/";
        
    /**
     * __construct
     *
     * @param  mixed $authUserName
     * @param  mixed $authPassword
     * @return void
     */
    public function __construct($authUserName = '', $authPassword = ''){
        $this->authToken = base64_encode($authUserName.':'.$authPassword);
    }
    
    /**
     * createSession
     *
     * @param  mixed $sessionData
     * @return void
     */
    public function createSession($sessionData = []){
        return $this->post($this->createSessionAPIUrl, $sessionData);
    }
    
    /**
     * createBillingCardProfile
     *
     * @param  mixed $merchantId
     * @param  mixed $stashId
     * @param  mixed $cardProfile
     * @return void
     */
    public function createBillingCardProfile( $merchantId = null, $stashId = null, $cardProfile = [] ){
        $url = $this->stashAPIUrl.'/'.$merchantId.'/'.$stashId;
        return $this->post($url, $cardProfile);        
    }

        
    /**
     * createOrder
     *
     * @param  mixed $orderData
     * @return void
     */
    public function createOrder( $orderData = [] ){
        return $this->post($this->createOrderAPIUrl, $orderData);  
    }
    
    /**
     * raiseRefund
     *
     * @param  mixed $orderID
     * @param  mixed $refundData
     * @return void
     */
    public function raiseRefund($orderID = null, $refundData = []){
        $url = $this->refundAPIUrl.$orderID .'/refunds';
        return $this->post($url, $refundData); 
    }

        
    /**
     * cancelOrder
     *
     * @param  mixed $orderId
     * @return void
     */
    public function cancelOrder($orderId = null){
        $apiURL = "https://api.sandbox.withreach.com/v1/orders/".$orderId."/cancel";
        return $this->delete($apiURL);
    }
    
    /**
     * cancelSession
     *
     * @param  mixed $sessionId
     * @return void
     */
    public function cancelSession($sessionId = null){
        $apiURL = "https://api.sandbox.withreach.com/v1/session/{$$sessionId}/cancel";
        return $this->delete($apiURL);
    }
    
    /**
     * captureTransaction
     *
     * @param  mixed $orderId
     * @return void
     */
    public function captureTransaction( $orderId = null ){
        $apiURL = "https://api.sandbox.withreach.com/v1/orders/{$orderId}/capture";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '.$this->authToken,
            'content-type: application/json'
        ));
        curl_setopt($ch, CURLOPT_URL, $apiURL);

        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }

        
    /**
     * signature
     *
     * @param  mixed $signature
     * @param  mixed $secret
     * @return void
     */
    public function signature($signature = null, $secret = ''){
        return $signature = base64_encode(hash_hmac('sha256', $signature, $secret, TRUE));
    }
    
    /**
     * post
     *
     * @param  mixed $apiURL
     * @param  mixed $payload
     * @return void
     */
    public function post($apiURL = '', $payload = []){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '.$this->authToken,
            'content-type: application/json'
        ));
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }
    
    /**
     * delete
     *
     * @param  mixed $apiURL
     * @return void
     */
    public function delete($apiURL = ''){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic '.$this->authToken,
            'content-type: application/json'
        ));
        curl_setopt($ch, CURLOPT_URL, $apiURL);

        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }
        
    /**
     * __toString
     *
     * @return void
     */
    public function __toString()
    {
        return $this->authToken;
    }    
}
?>