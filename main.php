<?php
include('./WithReachGateway.php');

echo "WithReach Integration Guide <br />";

/**
 * Merchant Provided Auth Credentials.
 */
$authUserName = 'TestUser';
$authSecret = 'TestSecret';

/**
 * The StashId can be the same platform's cart id, order id, 
 * session id or any other identifier that they wish for simplicity. 
 * Whatever the StashId is set to
 */
$stashId = "8d12c2db-ed2f-4e89-b378-1"; // System Generated Stash ID
$gatewayStashId = '';

$merchantProvidedSignatureJson = "{}";
$signature = null;

$items = [
    "Name" => "Item 1",
    "Amount" => 10.00,
    "Quantity" => 1
];
$merchantId = "unique_reference_provided_by_merchant";
$currency = "USD";
$autoCapture = true; // It can be true or false
$orderId = null;

$billingProfile = [
    "Name" => "First Last",
    "Email" => "email@example.org",
    "Address" => [
        "Street" => "123 Street",
        "City" => "City",
        "Country" => "ES",
        "Phone" => "4031234567"
    ]
];

$completeUrl = "https://www.send-user-here-after-success-redirect.com";
$cancelUrl = "https://www.send-user-here-after-failed-redirect.com";

$sessionId = "";
$billingProfileId = "";

$sessionData = [
    "MerchantReference" => $merchantId,
    "Currency" => $currency,
    "Items" => [$items],
    "BillingProfile" => $billingProfile,
    "AutoCapture" => $autoCapture,
    "CompleteUrl" => $completeUrl,
    "CancelUrl" => $cancelUrl
];


/**
 * Gateway initilization and auth token generation.
 */
$withReachGateway = new WithReachGateway($authUserName, $authSecret);

/**
 * Get device fingerprint or signature.
 */
$signature = $withReachGateway->signature($merchantProvidedSignatureJson, $authSecret);

/**
 * Create a session here.
 */
$sesssionResponse = $withReachGateway->createSession($sessionData);
if(is_array($sesssionResponse) && count($sesssionResponse) > 0){
    $sessionId = isset($sesssionResponse['SessionId']) ? $sesssionResponse['SessionId'] : '';
    $billingProfileId = (isset($sesssionResponse['BillingProfile']) && isset($sesssionResponse['BillingProfile']['BillingProfileId'])) ? $sesssionResponse['BillingProfile']['BillingProfileId'] : '';
}

/**
 * Create a credit card profile with with-reach using stash request.
 * PCI Complaint
 */
$cardProfileData = [
    "DeviceFingerprint" => $signature,
    "Card" => [
        "Name" => 'Test',
        "Number" => '4111111111111111',
        "VerificationCode" => '0555',
        "Expiry" => [
            "Year" => '2025',
            "Month" => '08',
        ]
    ]
];
$stashResponse = $withReachGateway->createBillingCardProfile($merchantId, $stashId, $cardProfileData);
$gatewayStashId = ($stashResponse && isset($stashResponse['StashId'])) ? $stashResponse['StashId'] : '';

$sesssionResponse = $withReachGateway->createSession($sessionData);
if(is_array($sesssionResponse) && count($sesssionResponse) > 0){
    $sessionId = isset($sesssionResponse['SessionId']) ? $sesssionResponse['SessionId'] : '';
    $billingProfileId = (isset($sesssionResponse['BillingProfile']) && isset($sesssionResponse['BillingProfile']['BillingProfileId'])) ? $sesssionResponse['BillingProfile']['BillingProfileId'] : '';
}

$orderData = [
    "Payment" => [
         "Card" => [
             "StashId" => $gatewayStashId
         ],
         "Type" => "CARD",
         "Method" => "VISA",
         "ReturnUrl" => "http://some.return_url_here"
    ],
   "MerchantReference" => "VisaRequest",
   "DeviceFingerprint" => $signature,
   "Currency" => "CAD",
   "Items" => [$items],
   "BillingProfileId" => $billingProfileId
];
$order = $withReachGateway->createOrder($orderData);
$orderId = isset($order['OrderId']) ? $order['OrderId'] : null;

/**
 * Raise a refund here
 */
$refundReferance = 'TEST';
$refundData = [
    'RefundReference' => $refundReferance,
    'Amount' => 5.00
];
$refund = $withReachGateway->raiseRefund($orderId, $refundData);
$refundId = isset($refund['RefundId']) ? $refund['RefundId'] : '';

/**
 * Cancel any order here.
 */
$cancelOrderResponse = $withReachGateway->cancelOrder($orderId);

/**
 * Cancel any active session.
 */
$cancelSessionResponse = $withReachGateway->cancelSession($sessionId);

/**
 * Capture a non-final transaction.
 */
$captureResponse = $withReachGateway->captureTransaction($orderId);