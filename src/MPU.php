<?php
namespace KenNebula\DingerPaymentIntegration;

use ErrorException;
use Illuminate\Support\Facades\Http;

class Dinger {
    /**
     * MPU Pay payment with card number and expiredAt (month/year)
     */
    public static function sendPayment($data)
    {
        $app_type = config('mpu.APP_TYPE');
        $url = config('mpu.'.$app_type);
        $merchantID = config('mpu.merchantID');
        $invoiceNo = $data['externalTransactionId'];
        $productDesc = 'test';
        $amount=round($data['amount'],2)*100;
        $amount=substr(str_repeat(0, 12).$amount, - 12);
        $currencyCode = config('mpu.currencyCode');
        $userDefined1 = 'userDefined1';
        $userDefined2 = 'userDefined1';
        $userDefined3 = 'userDefined1';
        $FrontendURL = config('mpu.frontendURL');
        $BackendURL = config('mpu.backendURL');
        $data = [
            "merchantID" => $merchantID,
            "invoiceNo" => $invoiceNo,
            "productDesc" => $productDesc,
            "amount" => $amount,
            "currencyCode" => $currencyCode,
            "userDefined1" => $userDefined1,
            "userDefined2" => $userDefined2,
            "userDefined3" => $userDefined3,
            "FrontendURL" => $FrontendURL,
            "BackendURL" => $BackendURL
        ];
        $hashValue = self::getMPUPaymentHash($data);
        Helper::save_logs('Payload data for payment provider '. self::$provider. '.', $data);
        Helper::save_logs('Payload hash value for payment provider '. self::$provider. '.', $hashValue);
        $send_form="
        <html>
        <header></header>
        <body>
        <form id='mpu_payment' action=$url method='post'>";
        $send_form.=csrf_field();
        $send_form.="
                <input type='hidden' name='merchantID' id='merchantID' value='$merchantID'>
                <input type='hidden' name='invoiceNo' id='invoiceNo' value='$invoiceNo'>
                <input type='hidden' name='productDesc' id='productDesc' value='$productDesc'>
                <input type='hidden' name='amount' id='amount' value='$amount'>
                <input type='hidden' name='currencyCode' id='currencyCode' value='$currencyCode'>
                <input type='hidden' name='userDefined1' id='userDefined1' value='$userDefined1'>
                <input type='hidden' name='userDefined2' id='userDefined2' value='$userDefined2'>
                <input type='hidden' name='userDefined3' id='userDefined3' value='$userDefined3'>
                <input type='hidden' name='FrontendURL' id='FrontendURL' value='$FrontendURL'>
                <input type='hidden' name='BackendURL' id='BackendURL' value='$BackendURL'>
                <input type='hidden' name='hashValue' id='hashValue' value='$hashValue'>";
            
        $send_form.="</form>
            <script language='JavaScript'>";
        $send_form.="
                document.getElementById('mpu_payment').submit();";

        $send_form.="</script>
            </body>
            </html>";
        echo $send_form;
    } 

    /**
     * to get MPU payment Hash code (HMACSHA1)
     */
    public static function getMPUPaymentHash($data)
    {
        asort($data, SORT_STRING); // sorting values by ASCII
        $str = implode('', $data);
        $signData = hash_hmac('sha1', $str, config('mpu.secretKey'), false);
        $signData = strtoupper($signData);
        return urlencode($signData);
    }
    
    /**
     * to encrypt or decrypt card information
     */
    public static function cardEncryptDecrypt($cardNumber, $expiredAt, $type = "encrypt") 
    {
        $strToEnc = $cardNumber.";".$expiredAt;

        // Encryption
        $encrypted = self::encrypt($strToEnc);

        // Decryption
        $decrypted = self::decrypt($encrypted);

        return $type == "encrypt" ? $encrypted : $decrypted;
    }

    protected static function encrypt($clearText)
    {
        $encryptionKey = config('mpu.secretKey');
        // Convert text to binary data
        $clearBytes = mb_convert_encoding($clearText, 'UTF-8');
        
        // Derive the key and IV using a PBKDF2 function
        $salt = hex2bin('4976616e204d65647665646576'); // Same salt as in the C# example
        $keyAndIV = self::deriveKeyAndIV($encryptionKey, $salt, 32, 16);

        $key = $keyAndIV['key'];
        $iv = $keyAndIV['iv'];

        // Encrypt the data using AES-256-CBC
        $encrypted = openssl_encrypt($clearBytes, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // Return base64-encoded encrypted data
        return base64_encode($encrypted);
    }

    protected static function decrypt($cipherText)
    {
        $encryptionKey = config('mpu.secretKey');
        // Convert base64 encoded ciphertext back to binary data
        $cipherBytes = base64_decode($cipherText);

        // Derive the key and IV using a PBKDF2 function
        $salt = hex2bin('4976616e204d65647665646576'); // Same salt as in the C# example
        $keyAndIV = self::deriveKeyAndIV($encryptionKey, $salt, 32, 16);

        $key = $keyAndIV['key'];
        $iv = $keyAndIV['iv'];

        // Decrypt the data using AES-256-CBC
        $decrypted = openssl_decrypt($cipherBytes, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // Return the original decrypted string
        return mb_convert_encoding($decrypted, 'UTF-8');
    }

    private static function deriveKeyAndIV($password, $salt, $keyLength, $ivLength)
    {
        $encryptionKey = config('mpu.secretKey');
        // Derive key and IV using OpenSSL's PBKDF2
        $iterations = 1000; // As per PBKDF2 standard, 1000 iterations
        $key = hash_pbkdf2('sha1', $password, $salt, $iterations, $keyLength * 2, true);
        $iv = substr($key, $keyLength, $ivLength);

        return [
            'key' => substr($key, 0, $keyLength),
            'iv' => $iv
        ];
    }

}