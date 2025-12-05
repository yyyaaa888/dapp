<?php
/**
 * 自动加载，把需要使用的相关类自动加载到php环境中
 */

spl_autoload_register('autoload');

function autoload(){
    require "EthereumAbi.php";
    require "InputDataDecoder.php";
}