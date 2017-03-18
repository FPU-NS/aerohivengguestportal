<html>
<head>
<title>Guest Wifi Registration</title>
<style>
body {
    background-color: #000000;
}
</style>  
<link href="https://fonts.googleapis.com/css?family=Cutive" rel="stylesheet">  
</head>

<body text="#FFFFFF">
<font face='Cutive'>
<img src="logo.jpg">
<p>  

<?php
# In my local environment I had to set the following to false due to self-signed certs and trust issues
# I'm not entirely sure this is needed where there are trusted certificates
# One note however: you must have set curl.cainfo=/path/to/cacert.pem in your php.ini
curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);
# Anyhing in <> must be replaced with your Hivemanager NG or webserver settings.
# The following header will redirect back to the guest registration form after 60 seconds.
header('Refresh: 60; URL=<ORGINALFORMURL>');
# Start of variables that are passed on from the HTML form POST
$firstName = $_POST["firstName"];
$lastName = $_POST["lastName"];
$email = $_POST["email"];
$phone = $_POST["phone"];
#Preset variables that fill in the CURLOPT_POSTFIELDS below.
$policy = "GUEST";
# groupId can be found by using GET against https://cloud-va.aerohive.com/xapi/v1/identity/credentials?ownerId=<OWNERID>
# I recommend creating an account manually for the user group and using GET with postman to find the groupId of the newly created user
$groupId = "<GROUPID>";
# $randomnumber allows the creation of visitor credentials without worrying about those credentials already existing.
# At some point I would like to build logic to detect already existing credentials and simply renew, and resend.
$randomnumber = rand(1, 100000);
$userName = "$firstName.$lastName.$randomnumber";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://cloud-va.aerohive.com/xapi/v1/identity/credentials?ownerId=<OWNERID>",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
# phone entry includes a '1' in front of the $phone variable. 
# This is because Hivermanager NG requires the country code for all user creations
  CURLOPT_POSTFIELDS => "{\r\n  \"deliverMethod\": \"EMAIL_AND_SMS\",\r\n  \"policy\": \"$policy\",\r\n  \"email\": \"$email\",\r\n  \"firstName\": \"$firstName\",\r\n  \"groupId\": \"$groupId\",\r\n  \"lastName\": \"$lastName\",\r\n \"phone\": \"1$phone\",\r\n  \"userName\": \"$userName\"\r\n}",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer <APITOKEN-FROMOAUTH2ORHMNGPORTAL>",
    "cache-control: no-cache",
    "content-type: application/json",
    "x-ah-api-client-id: <AH-DEV-AP-CLIENTID>",
    "x-ah-api-client-redirect-uri: <AH-DEV-AP-REDIRECTURI>",
    "x-ah-api-client-secret: AH-DEV-AP-CLIENTSECRET"
  ),
));

$response = curl_exec($curl);
$json = json_decode($response);
$err = curl_error($curl);

curl_close($curl);
#response page that pulls specific data from the decoded $response
if ($err) {
  echo "cURL Error #:" . $err;
} else { 
    echo "Connect to " .$json->data->ssid[0];
    echo " with the password: " .$json->data->password; 
}
?>

<p>
This screen will close in one minute
<p>
<a href="<ORGINALFORMURL>">Reset</a>
</font>
</body>
</html>