<?php

namespace App\Admin\Actions\Grid\RowAction;

use App\Service\Api\Factory;
use Dcat\Admin\Grid\RowAction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Web3\Web3;
use Web3\Contract;
use Web3p\EthereumTx\Transaction;
use App\Service\Web3p\Base as WebBase;

class LaunchWithdraw extends RowAction {
	/**
	 * 标题
	 *
	 * @return string
	 */
	public function title() {
		return admin_trans_label("<i class='feather icon-x' title='发起提现'></i> 发起提现");
	}

	/**
	 * 设置确认弹窗信息，如果返回空值，则不会弹出弹窗
	 *
	 * 允许返回字符串或数组类型
	 *
	 * @return array|string|void
	 */
	public function confirm() {
		return ["您确定要发起提现吗？"];
	}

	/**
	 * 处理请求
	 *
	 * @param Request $request
	 *
	 * @return \Dcat\Admin\Actions\Response
	 */
	public function handle() {
		\DB::beginTransaction();
		try {
			// 获取当前行ID
			$id = $this->getKey();
			$withdraw = App::make('userWithdraw')->details($id);
			if (empty($withdraw)) {
				throw new Exception('未找到订单');
			}
			if($withdraw['status'] == 0 && $withdraw['is_launch'] == 0){
	            $abi = '[{"inputs":[{"internalType":"address","name":"initialOwner","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"address","name":"target","type":"address"}],"name":"AddressEmptyCode","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"AddressInsufficientBalance","type":"error"},{"inputs":[],"name":"FailedInnerCall","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"name":"InvestInvalid","type":"error"},{"inputs":[{"internalType":"address","name":"owner","type":"address"}],"name":"OwnableInvalidOwner","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"OwnableUnauthorizedAccount","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"address","name":"recommended","type":"address"}],"name":"RegisterInvalid","type":"error"},{"inputs":[{"internalType":"address","name":"token","type":"address"}],"name":"SafeERC20FailedOperation","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"cakwAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"BuyTicket","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"cakwAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Invest","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":true,"internalType":"address","name":"referRecommender","type":"address"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Register","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousCakwAddr","type":"address"},{"indexed":true,"internalType":"address","name":"newCakwAddr","type":"address"}],"name":"SetCakwAddr","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousTicketAddr","type":"address"},{"indexed":true,"internalType":"address","name":"newTicketAddr","type":"address"}],"name":"SetTicketAddr","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousWithdrawAddr","type":"address"},{"indexed":true,"internalType":"address","name":"newWithdrawAddr","type":"address"}],"name":"SetWithdrawAddr","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"user","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Withdraw","type":"event"},{"inputs":[],"name":"BASE_SCALE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKEPOOL","outputs":[{"internalType":"contractICakePool","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKEToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKWToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKW_APPRECIATION_BASE_QUANTITY","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKW_BASE_PRICE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"DESTRUCTION_SCALE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"MIN_INVEST_CAKE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"USDTToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"burnCAKW","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakwAmount","type":"uint256"}],"name":"buyCakwByCalcCakeAmount","outputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakwAmount","type":"uint256"}],"name":"buyTicket","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"cakwAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKEPOOLData","outputs":[{"internalType":"int128","name":"amount","type":"int128"},{"internalType":"uint256","name":"end","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKEPrice","outputs":[{"internalType":"uint256","name":"amountOut","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKWPrice","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"name":"invest","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"name":"investCakeByBurnCAKWAmount","outputs":[{"internalType":"uint256","name":"cakwAmount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"isInvest","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"recommended","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"recommendedAddr","type":"address"}],"name":"register","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"sellCAKW","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setCakwAddr","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setTicketAddr","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setWithdrawAddr","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"ticketAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"withdrawAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"}]';
	            $web3Url = env('PRC_URL');
	            $contract = new Contract($web3Url, $abi);
	            $this->chainId = 56;
	            $this->withdrawAddress = env('FORMAL_ADDRESS');
	            $this->privateKey = env('WITHDRAW_PRIVATE_KEY');
	            $this->walletAddress = env('WITHDRAW_ADDRESS');
	            $nonce = $this->getNonceByAddress($this->walletAddress);
	            $method = 'withdraw';
	            $to_addr = $withdraw['address'];
	            $number = bcmul($withdraw['amount'],bcpow(10,18));
	            $data = '0x' . $contract->at($this->withdrawAddress)->getData($method, $to_addr, $number);
	            $contract_transfer_gas = getEthGas();
	            $apiUrl = env('API_URL');
	            $gas_price = getEthGasPrice($apiUrl);
	            $web3 = new Web3($web3Url);
	            $eth = $web3->eth;

	            $txParams = [
	                'from' => $this->walletAddress,
	                'to' => $this->withdrawAddress,
	                'value' => '0x0',//合约交易固定0,用户之间钱包互转才有对应的值
	                'gas' => $contract_transfer_gas,
	                'gasPrice' => $gas_price,
	                'data' => $data,
	                'chainId' => $this->chainId,
	                'nonce' => $nonce,
	            ];
	            $transaction = new Transaction($txParams);
	            $signedTransaction = $transaction->sign($this->privateKey);
	            $result = $this->proxyWithdraw($signedTransaction, $apiUrl);
	            if(isset($result['error']) && isset($result['error']['message'])){
	            	throw new Exception($result['error']['message']);
	            }
	            $tradeHash = $result['result'];
	            $withdraw->trade_hash = $tradeHash;
	            $withdraw->is_launch = 1;
	            $withdraw->save();
	        }
			\DB::commit();
			return $this->response()->success('发起成功')->refresh();
		} catch (\Exception $e) {
			\DB::rollBack();
			return $this->response()->error($e->getMessage());
		}
	}

    public function getNonceByAddress($address){

        $url = env('API_URL');
        $param = [
            'module'=>'proxy',
            'action'=>'eth_getTransactionCount',
            'address' => strtolower($address),
            'tag'=>'latest',
            'apikey' => env('BSC_API_KEY')
        ];
        $respon = json_decode(curlGet($url,$param),true);
        return $respon['result'];
    }

    public  function proxyWithdraw($hex,$url){
        $param = [
            'module'=>'proxy',
            'action'=>'eth_sendRawTransaction',
            'hex'=>$hex,
            'apikey' => 'W7Q141BZ6BDEJW9X59FH1BJS1UJB44I1N4'
        ];
        
        $respon = json_decode(curlGet($url,$param),true);
        return $respon;
    }
}
