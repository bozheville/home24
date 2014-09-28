<?php

class ParentModel extends \Phalcon\Mvc\Collection {
    public static function getRandomString($base_len = 20, $rounds = 3, $alphatype = "luns") {
        $true_rounds = $rounds;
        $alphabet = array();
        if (strstr($alphatype, "l")) {
            $alphabet["lower"] = "qwertyuiopasdfghjklzxcvbnm";
        }
        if (strstr($alphatype, "u")) {
            $alphabet["upper"] = "QWERTYUIOPASDFGHJKLZXCVBNM";
        }
        if (strstr($alphatype, "n")) {
            $alphabet["numeric"] = "1234567890";
        }
        if (strstr($alphatype, "s")) {
            $alphabet["specials"] = ",.?;:[]{}!@#$%^&*()_+-=";
        }
        $crypt = "";
        while (--$rounds > 0) {
            $substr = str_split(implode("", $alphabet));
            shuffle($substr);
            $substr = implode("", $substr);
            $substr = substr($substr, rand(0, strlen($substr) - $base_len), $base_len);
            $crypt .= $substr;
        }
        $crypt = str_split($crypt);
        shuffle($crypt);
        $crypt = implode("", $crypt);
        $crypt = substr($crypt, rand(0, strlen($crypt) - $base_len), $base_len);
        if ($base_len >= count(array_keys($alphabet))) {
            $candidate = str_split($crypt);
            foreach ($alphabet as $key => $value) {
                $has[$key] = false;
            }
            foreach ($candidate as $value) {
                foreach ($has as $key => $val) {
                    if (strstr($alphabet[$key], $value)) {
                        $has[$key] = true;
                        break;
                    }
                }
            }
            foreach ($has as $val) {
                if (empty($val)) {
                    $crypt = self::getRandomString($base_len, $true_rounds, $alphatype);
                    break;
                }
            }
        }
        return $crypt;
    }
} 