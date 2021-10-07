<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PrintDebug
{
    public static function display($message, $must_display){
        if ($must_display){
            echo(date("Y-m-d H:i:s") . " : " . $message . "\n");
        }
    }
}