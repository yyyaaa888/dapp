<?php
require "../index.php";

use Carbon\Carbon;
use Illuminate\Support\trade;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Service\Api\Factory;

$worker = new Worker();
$worker->count = 1;
$worker->onWorkerStart = function($worker){

    Gateway::$registerAddress = '127.0.0.1:1249';

    // $con = new AsyncTcpConnection('ws://bsc-testnet.nodereal.io/ws/v1/2c17e6c2386a4c9fa3011a1b672a8200');
    $con = new AsyncTcpConnection('ws://bsc-mainnet.nodereal.io/ws/v1/748aa065e4614a7faf5cf05594162516');

    // 设置以ssl加密方式访问，使之成为wss
    $con->transport = 'ssl';

    $con->onConnect = function($con) {
        $msg = [
            'jsonrpc'=>'2.0',
            'id'=>2,
            'method'=>'eth_subscribe',
            'params'=>[
                'logs',
                [
                    'address'=>env('RECHARGE_FORMAL_ADDRESS'),
                    'topics'=>['0x7d6eb2fb50784387320fc1feeab286693c5929bcf3578f5776f5494d0cef1f0b']
                ]
            ],
        ];
        $con->send(json_encode($msg));
    };

    $con->onMessage = function($con, $data) {
        $data =  json_decode($data,true);
        if(isset($data['ping'])){
            $msg = ["pong" => $data['ping']];
            $con->send(json_encode($msg));
        }else{
            if(isset($data['result'])){
                $tid = $data['result'];
                // var_dump($data);
            }elseif (isset($data['method'])) {
                \DB::beginTransaction();
                try {
                    // dump($data);
                    $address = $data['params']['result']['topics'][1];
                    $tradeHash = $data['params']['result']['transactionHash'];
                    $str = $data['params']['result']['data'];
                    $str = str_replace('0x', '', $str);
                    $length = 64;
                    $count = ceil(mb_strlen($str) / $length);
                    $trade = [];
                    for ($i=0; $i<$count; $i++) {
                        $arrVal =  substr($str, $i * $length, $length)." ";
                        $trade[] = hexdec($arrVal);
                    }
                    // dump($trade);
                    $saveData['address'] = '0x'.substr($address, -40);
                    $saveData['amount'] = isset($trade[0]) ? bcdiv(sctonum($trade[0]), bcpow(10,18), 8) : 0;
                    $saveData['type'] = 1;
                    $saveData['status'] = 1;
                    $saveData['created_at'] = isset($trade[1]) ? date('Y-m-d H:i:s',$trade[1]) : date('Y-m-d H:i:s');
                    $saveData['trade_hash'] = $tradeHash;
                    // dump($saveData);
                    $recharge = App::make('wbscRecharge')->details(['trade_hash'=>$saveData['trade_hash']]);
                    if(!$recharge){
                        // 充值订单
                        App::make('wbscRecharge')->create($saveData);
                        // 充值流水
                        Factory::UserWalletLogService()->add($saveData['address'], 'wbsc', 0, 201, $saveData['amount']);
                    }
                    \DB::commit();
                } catch (\Exception $e) {
                    \DB::rollBack();
                    Log::info('WBSC充值异常：'.$e->getMessage());
                }
            }
        }
    };

    $con->onClose = function ($con) {
        //这个是延迟断线重连，当服务端那边出现不确定因素，比如宕机，那么相对应的socket客户端这边也链接不上，那么可以吧1改成适当值，则会在多少秒内重新，我也是1，也就是断线1秒重新链接
        $con->reConnect(1);
    };

    $con->onError = function ($con, $code, $msg) {
        echo "error $code $msg\n";
    };

    $con->connect();
};

Worker::runAll();
