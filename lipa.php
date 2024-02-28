<?php
  date_default_timezone_set('Africa/Nairobi');

  # access token
  $consumerKey = 'ExhBy92Gx9EaBkVSM3nW8xJLCzeEChGbiELA4O5zp9p5UxYQ'; //Fill with your app Consumer Key
  $consumerSecret = '2G6O6ws3vd5meWoPE3wwtxoMAmMK2KZWNwXevlIEuRYhsMVb4bH1SMiuM5Y08R4L'; // Fill with your app Secret

  # define the variables
  # provide the following details, this part is found on your test credentials on the developer account
  $BusinessShortCode = '174379';
  $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';  
  
  /*
    This are your info, for
    $PartyA should be the ACTUAL clients phone number or your phone number, format 2547********
    $AccountRefference, it maybe invoice number, account number etc on production systems, but for test just put anything
    TransactionDesc can be anything, probably a better description of or the transaction
    $Amount this is the total invoiced amount, Any amount here will be 
    actually deducted from a client's side/your test phone number once the PIN has been entered to authorize the transaction. 
    for developer/test accounts, this money will be reversed automatically by midnight.
  */
  
  $PartyA = isset($_GET['phone']) ? $_GET['phone'] : '';
  $AccountReference = 'ESMS';
  $TransactionDesc = 'Test Payment';
  $Amount = isset($_GET['amount']) ? $_GET['amount'] : '';
 
  if ($PartyA && $AccountReference) {
    # Get the timestamp, format YYYYmmddhms -> 20181004151020
    $Timestamp = date('YmdHis');    
    
    # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
    $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

    # header for access token
    $headers = ['Content-Type:application/json; charset=utf8'];

    # M-PESA endpoint urls
    $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    # callback url
    $CallBackURL = 'https://callback.kenova.co';  

    $curl = curl_init($access_token_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;  
    curl_close($curl);

    # header for stk push
    $stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

    # initiating the transaction
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $initiate_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

    $curl_post_data = array(
      //Fill in the request parameters with valid values
      'BusinessShortCode' => $BusinessShortCode,
      'Password' => $Password,
      'Timestamp' => $Timestamp,
      'TransactionType' => 'CustomerPayBillOnline',
      'Amount' => $Amount,
      'PartyA' => $PartyA,
      'PartyB' => $BusinessShortCode,
      'PhoneNumber' => $PartyA,
      'CallBackURL' => $CallBackURL,
      'AccountReference' => $AccountReference,
      'TransactionDesc' => $TransactionDesc
    );

    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    $curl_response = curl_exec($curl);
    print_r($curl_response);

    echo $curl_response;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>M-PESA Payment Form</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
    body {
      padding: 20px;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Enter Mpesa Details</h2>
    <form id="mpesaForm">
      <div class="form-group">
        <label for="phone">Phone Number:</label>
        <input type="tel" class="form-control" id="phone" name="phone" placeholder="2547********" required>
      </div>
      <div class="form-group">
        <label for="amount">Amount:</label>
        <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter amount" required>
      </div>
      <button type="button" class="btn btn-primary" onclick="submitForm()">Lipa</button>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

  <script>
    function submitForm() {
      var phone = document.getElementById('phone').value;
      var amount = document.getElementById('amount').value;

      // Redirect to the PHP script with the parameters
      window.location.href = "?phone=" + phone + "&amount=" + amount;
    }
  </script>

</body>
</html>