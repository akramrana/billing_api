<?php

require 'AES.php';

set_exception_handler('myException');

$searchArr = [];

$replaceArr = [];

function debugPrint($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function bn2en($number) {
    $bn = ["১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০"];
    $en = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
    return str_replace($bn, $en, $number);
}

function en2bn($number) {
    $bn = ["১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০"];
    $en = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
    return str_replace($en, $bn, $number);
}

function str_replace_first($search, $replace, $subject) {
    $search = '/' . preg_quote($search, '/') . '/';
    return preg_replace($search, $replace, $subject, 1);
}

function myException($exception) {
    echo "<b>Exception:</b> " . $exception->getMessage();
}

function fnDecrypt($ciphertext) {
    $aes = new AES($ciphertext, 'vz178pldcutk2ez4dzo3askdfbak32rz', 256);
    $decrypted_cipher = $aes->decrypt();
    $incoming_data = explode("###", $decrypted_cipher);
    return $incoming_data;
}

function checkToken($header) {
    if (empty($header['secret'])) {
        throw new Exception("forbidden 403");
    } else {
        $decrypt = fnDecrypt($header['secret']);
        if (!empty($decrypt)) {
            if ($decrypt[1] == 'cms') {
                $gmt_time = gmdate("Y-m-d\TH:i:s\Z");
                $date = new DateTime($gmt_time);
                $date->modify("-1 minutes");
                $request_time = new DateTime($decrypt[0]);
                if ($request_time < $date) {
                    throw new Exception("forbidden 403");
                }
            } else {
                throw new Exception("forbidden 403");
            }
        } else {
            throw new Exception("forbidden 403");
        }
    }
}

function replace_space($name) {
    $name = trim($name);
    $str = str_replace(' ', '-', $name);
    $str = str_replace("'", '', $str);
    $str = str_replace("(", '', $str);
    $str = str_replace(")", '', $str);
    $str = strtolower($str);

    return $str;
}

function enToFa($string) {
    return strtr($string, array('0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'));
}