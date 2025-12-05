<?php

namespace App\Service\Web3p;

use App\Service\BaseService;
use Illuminate\Support\Facades\Log;
use Web3\Contract;
use Web3\Web3;
use Web3p\EthereumTx\Transaction;

class Base extends BaseService {

    public $is_test = 0;
    public $url = 'https://bsc-mainnet.core.chainstack.com/fea316526729e408be2e14d196ee34fc';

    public $apiUrl = 'https://api.bscscan.com/api';
    //测试网
    public $testUrl = 'https://bsc-testnet.publicnode.com';
    public $testApiUrl = 'https://api-testnet.bscscan.com/api';
    public $web3;
    public $contract;
    public $chainId;
    private $binaryCode = '';

    public $pledgeAbi = '[ { "inputs": [], "stateMutability": "nonpayable", "type": "constructor" }, { "inputs": [ { "internalType": "address", "name": "target", "type": "address" } ], "name": "AddressEmptyCode", "type": "error" }, { "inputs": [], "name": "AddressFailedCall", "type": "error" }, { "inputs": [ { "internalType": "address", "name": "account", "type": "address" } ], "name": "AddressInsufficientBalance", "type": "error" }, { "inputs": [ { "internalType": "uint256", "name": "amount", "type": "uint256" } ], "name": "buyCRAB", "outputs": [ { "internalType": "bool", "name": "", "type": "bool" } ], "stateMutability": "nonpayable", "type": "function" }, { "inputs": [], "name": "developerCRAB", "outputs": [ { "internalType": "bool", "name": "", "type": "bool" } ], "stateMutability": "nonpayable", "type": "function" }, { "inputs": [ { "internalType": "uint256", "name": "CRABAmount", "type": "uint256" } ], "name": "pledge", "outputs": [ { "internalType": "bool", "name": "", "type": "bool" } ], "stateMutability": "nonpayable", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "user", "type": "address" }, { "internalType": "uint256", "name": "CRABAmount", "type": "uint256" } ], "name": "PledgeInvalid", "type": "error" }, { "inputs": [], "name": "SafeERC20FailedCall", "type": "error" }, { "inputs": [ { "internalType": "address", "name": "token", "type": "address" } ], "name": "SafeERC20FailedOperation", "type": "error" }, { "inputs": [], "name": "unPledge", "outputs": [ { "internalType": "bool", "name": "", "type": "bool" } ], "stateMutability": "nonpayable", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "user", "type": "address" }, { "internalType": "uint256", "name": "CRABAmount", "type": "uint256" }, { "internalType": "uint256", "name": "tolCRABAmount", "type": "uint256" } ], "name": "UnPledgeInvalid", "type": "error" }, { "inputs": [ { "internalType": "uint256", "name": "StartDay", "type": "uint256" }, { "internalType": "uint256", "name": "updataRewardCount", "type": "uint256" }, { "internalType": "bool", "name": "isReward", "type": "bool" } ], "name": "UpdataRewardError", "type": "error" }, { "inputs": [ { "internalType": "address", "name": "user", "type": "address" }, { "internalType": "uint256", "name": "amount", "type": "uint256" }, { "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "WithdrawInvalid", "type": "error" }, { "anonymous": false, "inputs": [ { "indexed": true, "internalType": "address", "name": "account", "type": "address" }, { "indexed": false, "internalType": "uint256", "name": "amount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "costUSDT", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "BuyCRAB", "type": "event" }, { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "uint256", "name": "amount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "DeveloperCRAB", "type": "event" }, { "anonymous": false, "inputs": [ { "indexed": true, "internalType": "address", "name": "account", "type": "address" }, { "indexed": false, "internalType": "uint256", "name": "CRABAmount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "Pledge", "type": "event" }, { "inputs": [ { "internalType": "address", "name": "addr", "type": "address" } ], "name": "setISuperWinner", "outputs": [], "stateMutability": "nonpayable", "type": "function" }, { "anonymous": false, "inputs": [ { "indexed": true, "internalType": "address", "name": "user", "type": "address" }, { "indexed": false, "internalType": "uint256", "name": "amount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "UnPledge", "type": "event" }, { "inputs": [], "name": "updataBigPrizeReward", "outputs": [], "stateMutability": "nonpayable", "type": "function" }, { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "uint256", "name": "CAKEAmount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "CRABPledgeTotal", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "shareNum", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "UpdataBigPrizeReward", "type": "event" }, { "inputs": [], "name": "updataReward", "outputs": [], "stateMutability": "nonpayable", "type": "function" }, { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "uint256", "name": "CAKEAmount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "CRABPledgeTotal", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "shareNum", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "UpdataReward", "type": "event" }, { "inputs": [], "name": "withdraw", "outputs": [ { "internalType": "bool", "name": "", "type": "bool" } ], "stateMutability": "nonpayable", "type": "function" }, { "anonymous": false, "inputs": [ { "indexed": true, "internalType": "address", "name": "user", "type": "address" }, { "indexed": false, "internalType": "uint256", "name": "amount", "type": "uint256" }, { "indexed": false, "internalType": "uint256", "name": "time", "type": "uint256" } ], "name": "Withdraw", "type": "event" }, { "inputs": [], "name": "CAKEToken", "outputs": [ { "internalType": "contract IERC20", "name": "", "type": "address" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "CRAB_APPRECIATION_BASE_QUANTITY", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "CRAB_BASE_PRICE", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "", "type": "address" } ], "name": "CRABPledgeBalance", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "CRABPledgeTotal", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "CRABToken", "outputs": [ { "internalType": "contract IERC20", "name": "", "type": "address" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "DEV_CRAB_AMOUNT", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "getCRABPrice", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "user", "type": "address" } ], "name": "getUserReward", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "name": "isReward", "outputs": [ { "internalType": "bool", "name": "", "type": "bool" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "MINSTARUSDT", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "noRewardAmount", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "roundId", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "shareNum", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "SuperWinner", "outputs": [ { "internalType": "contract ISuperWinner", "name": "", "type": "address" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "tolDEVCount", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "tolSellCRAB", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "UPDATA_TIME", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "updataRewardCount", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [], "name": "USDTToken", "outputs": [ { "internalType": "contract IERC20", "name": "", "type": "address" } ], "stateMutability": "view", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "", "type": "address" } ], "name": "userLockWithdraw", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "", "type": "address" } ], "name": "userTolWithdraw", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" }, { "inputs": [ { "internalType": "address", "name": "", "type": "address" } ], "name": "userWithdraw", "outputs": [ { "internalType": "uint256", "name": "", "type": "uint256" } ], "stateMutability": "view", "type": "function" } ]';
    public $pledgeContractAddress;//质押合约地址
    public $zoneAbi = '[{"inputs":[{"internalType":"address[]","name":"addrs","type":"address[]"},{"internalType":"uint256[]","name":"rewards","type":"uint256[]"}],"name":"achievementU","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address[]","name":"addrs","type":"address[]"},{"internalType":"uint256[]","name":"superWinnerTeamCostUset","type":"uint256[]"},{"internalType":"uint256","name":"tolTeamCostUse","type":"uint256"}],"name":"achievementUpdata","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"token","type":"address"},{"internalType":"address","name":"addr","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"address","name":"target","type":"address"}],"name":"AddressEmptyCode","type":"error"},{"inputs":[],"name":"AddressFailedCall","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"AddressInsufficientBalance","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"BossBuyInvalid","type":"error"},{"inputs":[{"internalType":"address[]","name":"addrs","type":"address[]"},{"internalType":"address[]","name":"recommendedAddrs","type":"address[]"}],"name":"initUserRegister","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"recommendedAddr","type":"address"}],"name":"register","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"address","name":"recommended","type":"address"}],"name":"RegisterInvalid","type":"error"},{"inputs":[],"name":"SafeERC20FailedCall","type":"error"},{"inputs":[{"internalType":"address","name":"token","type":"address"}],"name":"SafeERC20FailedOperation","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":true,"internalType":"address","name":"referRecommender","type":"address"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Register","type":"event"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"residueCRAB","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setISuperWinner","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"superBossBuyCRAB","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"cakePrice","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"crabPrice","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"crabAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"SuperBossBuyCRAB","type":"event"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"updataBigPrizeReward","outputs":[],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"CAKEAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"CRABPledgeTotal","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"shareNum","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"UpdataBigPrizeReward","type":"event"},{"inputs":[],"name":"updataReward","outputs":[],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"CAKEAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"superBossNum","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"updataRewardCount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"UpdataReward","type":"event"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"with","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Withdraw","type":"event"},{"inputs":[],"name":"bigPrizeRewardCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"bigShareNum","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"bossAchievementRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CONTRACT_SuperBossV1","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CONTRACT_WinnerV1","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CRABToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CRABTokenInfo","outputs":[{"internalType":"contractICRABToken","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getAllSuperBoss","outputs":[{"internalType":"address[]","name":"addrs","type":"address[]"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCostTokenAmount","outputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"internalType":"uint256","name":"crabAmount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserDirectThrust","outputs":[{"internalType":"address[]","name":"addrs","type":"address[]"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserReward","outputs":[{"internalType":"uint256","name":"shareA","type":"uint256"},{"internalType":"uint256","name":"recommendA","type":"uint256"},{"internalType":"uint256","name":"bigShareA","type":"uint256"},{"internalType":"uint256","name":"bossRewards","type":"uint256"},{"internalType":"uint256","name":"withdrawA","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"isBossAchievementReward","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"isReward","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"isSuperBoss","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"noBigPrizeRewardAmount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"noRewardAmount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"recommendReward","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"shareNum","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"SuperBoss","outputs":[{"internalType":"contractISuperBoss","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"SuperWinner","outputs":[{"internalType":"contractISuperWinner","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"updataBossAchievementCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"updataRewardCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"USDT_BASE_COST","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userLockBigPrizeRewardWithdraw","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userLockWithdraw","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userRecommended","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userWithdraw","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"}]';
    public $zoneContractAddress;//节点合约地址
    public $mainAbi = '[{"inputs":[{"internalType":"address","name":"initialOwner","type":"address"},{"internalType":"address[]","name":"users","type":"address[]"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"address","name":"target","type":"address"}],"name":"AddressEmptyCode","type":"error"},{"inputs":[],"name":"AddressFailedCall","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"AddressInsufficientBalance","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"teamID","type":"uint256"},{"internalType":"uint256","name":"STARAmount","type":"uint256"},{"internalType":"uint256","name":"endTime","type":"uint256"}],"name":"BuySTARInvalid","type":"error"},{"inputs":[],"name":"OnlyBuyTime","type":"error"},{"inputs":[],"name":"OnlyEOA","type":"error"},{"inputs":[{"internalType":"address","name":"owner","type":"address"}],"name":"OwnableInvalidOwner","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"OwnableUnauthorizedAccount","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"address","name":"recommended","type":"address"}],"name":"RegisterInvalid","type":"error"},{"inputs":[],"name":"RoundEnd","type":"error"},{"inputs":[],"name":"SafeERC20FailedCall","type":"error"},{"inputs":[{"internalType":"address","name":"token","type":"address"}],"name":"SafeERC20FailedOperation","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"teamId","type":"uint256"}],"name":"TeamInvalid","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"UnRegister","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"STARAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakePrice","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"BuySTAR","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"GrantBigPrize","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"teamType","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"JoinTeam","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":true,"internalType":"address","name":"referRecommender","type":"address"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Register","type":"event"},{"anonymous":false,"inputs":[{"indexed":false,"internalType":"uint256","name":"rId","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"endTime","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"RunNextRound","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Withdraw","type":"event"},{"inputs":[],"name":"AMPLIFIED_BASE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"AMPLIFIED_DECIMALS","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKEPOOL","outputs":[{"internalType":"contractICakePool","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"_STARAmount","type":"uint256"}],"name":"CAKESwapSTAR","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKEToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CONTRACT_SuperBossV1","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"CRABPool","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"INVITE_MAX_USDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"MARKERS_TIME","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"MIN_TIME","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"PANCAKEROUTER","outputs":[{"internalType":"contractIPancakeRouter","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"ROUND_MAX_TIME","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"_CAKEAmount","type":"uint256"}],"name":"STARSwapCAKE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"STAR_FREE_AMOUNT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"STAR_FREE_MAX_AMOUNT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"SuperWinnerV1","outputs":[{"internalType":"contractISuperWinnerV1","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"USDTToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"address","name":"","type":"address"}],"name":"V1_rIdBuySTARUserUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"WIN_SMALL_PRIZE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"achievementPool","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"averOperationalNodePool","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"bigPrizeCRABPool","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"bigPrizeOperationalNodePool","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"STARAmount","type":"uint256"}],"name":"buySTAR","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"developerRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"},{"internalType":"uint256","name":"","type":"uint256"}],"name":"directThrust","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"donate","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"endTime","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"firstday_rIdHoldSTARRealTimeByNum","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"freeSTARAmount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getActualEndTime","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"teamId","type":"uint256"}],"name":"getAllocationScale","outputs":[{"components":[{"internalType":"uint256","name":"appreciationPool","type":"uint256"},{"internalType":"uint256","name":"bigPrize","type":"uint256"},{"internalType":"uint256","name":"holdSTARRealTime","type":"uint256"},{"internalType":"uint256","name":"holdCRAB","type":"uint256"},{"internalType":"uint256","name":"smallPrize","type":"uint256"},{"internalType":"uint256","name":"averOperationalNode","type":"uint256"},{"internalType":"uint256","name":"weightingOperationalNode","type":"uint256"},{"internalType":"uint256","name":"achievement","type":"uint256"},{"internalType":"uint256","name":"invite","type":"uint256"},{"internalType":"uint256","name":"developer","type":"uint256"},{"internalType":"uint256","name":"passNext","type":"uint256"}],"internalType":"structSuperWinnerInitialData.AllocationScale","name":"","type":"tuple"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"teamId","type":"uint256"}],"name":"getBigPrizeScale","outputs":[{"components":[{"internalType":"uint256","name":"lastAddr","type":"uint256"},{"internalType":"uint256","name":"nextRound","type":"uint256"},{"internalType":"uint256","name":"holdCRAB","type":"uint256"},{"internalType":"uint256","name":"vipUser","type":"uint256"},{"internalType":"uint256","name":"operationalNode","type":"uint256"}],"internalType":"structSuperWinnerInitialData.BigPrizeScale","name":"","type":"tuple"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getBuySTRAAddrs","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKEPrice","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"time","type":"uint256"}],"name":"getNewStartDays","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getRIdBigPrizeCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getRIdBuySTARUserUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"rId","type":"uint256"},{"internalType":"address","name":"user","type":"address"}],"name":"getRIdBuySTARUserUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"rId","type":"uint256"}],"name":"getRIdSTARBuyCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getRIdSTARTotalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"rId","type":"uint256"},{"internalType":"address","name":"user","type":"address"}],"name":"getRIdSTARUserBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getRIdSTARUserBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"rId","type":"uint256"}],"name":"getRoundInfo","outputs":[{"internalType":"uint256","name":"buyCount","type":"uint256"},{"internalType":"uint256","name":"totalSupply","type":"uint256"},{"internalType":"uint256[7]","name":"cakeInfo","type":"uint256[7]"},{"internalType":"address","name":"lastAddr","type":"address"},{"internalType":"uint256","name":"winAmount","type":"uint256"},{"internalType":"uint256[4]","name":"teamInfo","type":"uint256[4]"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getSTARUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getSmallPrizeRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"time","type":"uint256"}],"name":"getStartDays","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserAllRIdVIPReward","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserDirectThrust","outputs":[{"internalType":"address[]","name":"addrs","type":"address[]"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"rId","type":"uint256"}],"name":"getUserInfo","outputs":[{"internalType":"uint256","name":"userSTARBalance","type":"uint256"},{"internalType":"uint256","name":"buySTARUserUSDT","type":"uint256"},{"internalType":"uint256","name":"HSTARTReward","type":"uint256"},{"internalType":"uint256","name":"smallPrizeReward","type":"uint256"},{"internalType":"uint256","name":"invitePrizeReward","type":"uint256"},{"internalType":"uint256","name":"passNextRewards","type":"uint256"},{"internalType":"uint256","name":"withdrawA","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserRecommended","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserTeamId","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"user","type":"address"}],"name":"getUserWinRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getWinBigPrizeInfos","outputs":[{"components":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"internalType":"structSuperWinnerInitialData.WinBigPrizeInfo[]","name":"","type":"tuple[]"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getWinSmallPrizeInfos","outputs":[{"components":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"internalType":"structSuperWinnerInitialData.WinSmallPrizeInfo[]","name":"","type":"tuple[]"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"grantBigPrize","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"teamId","type":"uint256"}],"name":"joinTeam","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"lastBuyTime","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"poolAddrs","outputs":[{"internalType":"address","name":"appreciationPool","type":"address"},{"internalType":"address","name":"bigPrize","type":"address"},{"internalType":"address","name":"averOperationalNode","type":"address"},{"internalType":"address","name":"weightingOperationalNode","type":"address"},{"internalType":"address","name":"achievement","type":"address"},{"internalType":"address","name":"inviteBurn","type":"address"},{"internalType":"address","name":"passNextBurn","type":"address"},{"internalType":"address","name":"developer","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdAchievementCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdAppreciationPoolCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdAverOperationalNodeCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdBigPrizeCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdBurnCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"address","name":"","type":"address"}],"name":"rIdBuySTARISVIPUser","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdBuySTARTolUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"address","name":"","type":"address"}],"name":"rIdBuySTARUserUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdCakeTotalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdCurPassNextCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdGrantBigPrize","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdHoldCRABCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdHoldSTARRealTimeByNum","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"address","name":"","type":"address"}],"name":"rIdHoldSTARRealTimeByUserDeductNum","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdHoldSTARRealTimeCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdInviteCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdLastAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdPassNextCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdSTARBuyCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdSTARTotalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"address","name":"","type":"address"}],"name":"rIdSTARUserBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdSmallPrizeCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdTeamIdCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"},{"internalType":"address","name":"","type":"address"}],"name":"rIdUserIsBuy","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdVIPBuySTARTolUSDT","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdVIPByNum","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"rIdWeightingOperationalNodeCakeBalance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"recommendedAddr","type":"address"}],"name":"register","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"roundId","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"time","type":"uint256"}],"name":"runNextRound","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"setIsStar","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setOwnableOperation","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"teamId","type":"uint256"},{"components":[{"internalType":"uint256","name":"appreciationPool","type":"uint256"},{"internalType":"uint256","name":"bigPrize","type":"uint256"},{"internalType":"uint256","name":"holdSTARRealTime","type":"uint256"},{"internalType":"uint256","name":"holdCRAB","type":"uint256"},{"internalType":"uint256","name":"smallPrize","type":"uint256"},{"internalType":"uint256","name":"averOperationalNode","type":"uint256"},{"internalType":"uint256","name":"weightingOperationalNode","type":"uint256"},{"internalType":"uint256","name":"achievement","type":"uint256"},{"internalType":"uint256","name":"invite","type":"uint256"},{"internalType":"uint256","name":"developer","type":"uint256"},{"internalType":"uint256","name":"passNext","type":"uint256"}],"internalType":"structSuperWinnerInitialData.AllocationScale","name":"AScale","type":"tuple"}],"name":"setTeamIdByAllocation","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"teamId","type":"uint256"},{"components":[{"internalType":"uint256","name":"lastAddr","type":"uint256"},{"internalType":"uint256","name":"nextRound","type":"uint256"},{"internalType":"uint256","name":"holdCRAB","type":"uint256"},{"internalType":"uint256","name":"vipUser","type":"uint256"},{"internalType":"uint256","name":"operationalNode","type":"uint256"}],"internalType":"structSuperWinnerInitialData.BigPrizeScale","name":"bPrizeScale","type":"tuple"}],"name":"setTeamIdByBigPrize","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address[]","name":"users","type":"address[]"}],"name":"setUser","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"startTime","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"teamIdByAllocation","outputs":[{"internalType":"uint256","name":"appreciationPool","type":"uint256"},{"internalType":"uint256","name":"bigPrize","type":"uint256"},{"internalType":"uint256","name":"holdSTARRealTime","type":"uint256"},{"internalType":"uint256","name":"holdCRAB","type":"uint256"},{"internalType":"uint256","name":"smallPrize","type":"uint256"},{"internalType":"uint256","name":"averOperationalNode","type":"uint256"},{"internalType":"uint256","name":"weightingOperationalNode","type":"uint256"},{"internalType":"uint256","name":"achievement","type":"uint256"},{"internalType":"uint256","name":"invite","type":"uint256"},{"internalType":"uint256","name":"developer","type":"uint256"},{"internalType":"uint256","name":"passNext","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"teamIdByBigPrize","outputs":[{"internalType":"uint256","name":"lastAddr","type":"uint256"},{"internalType":"uint256","name":"nextRound","type":"uint256"},{"internalType":"uint256","name":"holdCRAB","type":"uint256"},{"internalType":"uint256","name":"vipUser","type":"uint256"},{"internalType":"uint256","name":"operationalNode","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userInviteRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userIsRecommend","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userPassNextRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userRecommended","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userSmallPrizeRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userTeamId","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userTolWithdrawRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"userWinRewards","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"weightingOperationalNodePool","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"winBigPrizeInfos","outputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"","type":"uint256"}],"name":"winSmallPrizeInfos","outputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"}]';
    public $mainContractAddress;//主合约地址

    //接口调用地址
    public $userAddress;
    public $privateKey;//提现钱包地址私钥

    public $permissionAddress;
    public $permissionPrivateKey;


    public $FTW_PledgeAddress;
    public $FTW_PledgePrivateKey;

    public $FTW_ZoneRewardAddress;
    public $FTW_ZoneRewardPrivateKey;

    public $FTW_BigPriceAddress;
    public $FTW_BigPricePrivateKey;

    public $FTW_DWithdrawAddress;
    public $FTW_DWithdrawPrivateKey;

    public $FTW_BigPrizeRewardAddress;
    public $FTW_BigPrizeRewardPrivateKey;


    public function __construct()
    {
        if($this->is_test){
            // Log::channel('web3p')->info('当前为测试环境,地址:'.$this->testUrl);
            #测试
            $this->chainId = '97';
            $this->web3 = new Web3($this->testUrl);
        }else{
            // Log::channel('web3p')->info('当前为正式环境,地址:'.$this->url);
            #正式
            $this->chainId = '56';
            $this->web3 = new Web3($this->url);
        }

        //项目合约
        $this->pledgeContractAddress = env('PLEDGE_FORMAL_ADDRESS');
        $this->zoneContractAddress = env('BOSS_FORMAL_ADDRESS');
        $this->mainContractAddress = env('WINNER_FORMAL_ADDRESS');


        $this->userAddress = env('userAddress');
        $this->privateKey = env('privateKey');

        $this->permissionAddress = env('permissionAddress');
        $this->permissionPrivateKey = env('permissionPrivateKey');

        $this->FTW_PledgeAddress = env('FTW_PledgeAddress');
        $this->FTW_PledgePrivateKey = env('FTW_PledgePrivateKey');

        $this->FTW_ZoneRewardAddress = env('FTW_ZoneRewardAddress');
        $this->FTW_ZoneRewardPrivateKey = env('FTW_ZoneRewardPrivateKey');

        $this->FTW_BigPriceAddress = env('FTW_BigPriceAddress');
        $this->FTW_BigPricePrivateKey = env('FTW_BigPricePrivateKey');

        $this->FTW_DWithdrawAddress = env('FTW_DWithdrawAddress');
        $this->FTW_DWithdrawPrivateKey = env('FTW_DWithdrawPrivateKey');

        $this->FTW_BigPrizeRewardAddress = env('FTW_BigPrizeRewardAddress');
        $this->FTW_BigPrizeRewardPrivateKey = env('FTW_BigPrizeRewardPrivateKey');





        $this->pledgeContract = new Contract($this->web3->provider, $this->pledgeAbi);
        $this->zoneContract = new Contract($this->web3->provider, $this->zoneAbi);
        $this->mainContract = new Contract($this->web3->provider, $this->mainAbi);

    }

    public function getPrivateKey($method){

        if(in_array($method,['runNextRound','achievementUpdata'])){
            $privateKey = $this->permissionPrivateKey;

        }else if(in_array($method,['updataPledgeReward','updataZoneReward','grantBigPrize','withdrawDeveloper','updataBigPrizeReward'])){
            switch ($method){
                case 'updataPledgeReward':
                    $privateKey = $this->FTW_PledgePrivateKey;
                    break;
                case 'updataZoneReward':
                    $privateKey = $this->FTW_ZoneRewardPrivateKey;
                    break;
                case 'grantBigPrize':
                    $privateKey = $this->FTW_BigPricePrivateKey;
                    break;
                case 'withdrawDeveloper':
                    $privateKey = $this->FTW_DWithdrawPrivateKey;
                    break;
                case 'updataBigPrizeReward':
                    $privateKey = $this->FTW_BigPrizeRewardPrivateKey;
                    break;
                default:
                    $this->errorMsg('参数错误,方法执行类型不存在');
                    break;
            }
        }
        return $privateKey;
    }
    public function getUserAddress($method){

        if(in_array($method,['runNextRound','achievementUpdata'])){
            $userAddress = $this->permissionAddress;

        }else if(in_array($method,['updataPledgeReward','updataZoneReward','grantBigPrize','withdrawDeveloper','updataBigPrizeReward'])){
            switch ($method){
                case 'updataPledgeReward':
                    $userAddress = $this->FTW_PledgeAddress;
                    break;
                case 'updataZoneReward':
                    $userAddress = $this->FTW_ZoneRewardAddress;
                    break;
                case 'grantBigPrize':
                    $userAddress = $this->FTW_BigPriceAddress;
                    break;
                case 'withdrawDeveloper':
                    $userAddress = $this->FTW_DWithdrawAddress;
                    break;
                case 'updataBigPrizeReward':
                    $userAddress = $this->FTW_BigPrizeRewardAddress;
                    break;
                default:
                    $this->errorMsg('参数错误,方法执行类型不存在');
                    break;
            }
        }
        return $userAddress;
    }

    public function getEthGas(){
//        $url='https://api-testnet.bscscan.com/api';
//        $param = [
//
//        ];
//
//        $respon = json_decode(curl($url,$param),true);
//        if($respon['status'] !== 1 || $respon['message'] !== 'OK'){
//            return ;
//        }

        return '0x'.dechex(1000000);
    }

    public function getEthGasPrice(){
        $url=$this->is_test?$this->testApiUrl:$this->apiUrl;
        $param = [
            'module'=>'proxy',
            'action'=>'eth_gasPrice',
            'apikey' => env('BSC_API_KEY')
        ];

        $respon = json_decode(curlGet($url,$param),true);
//        return $respon['result'];
        $hexnum = substr((string)$respon['result'],2);
        // Log::channel('web3p')->info('$hexnum:'.$respon['result']);
        // Log::channel('web3p')->info('$hexnum:'.$hexnum);
        $nums = (hexdec($hexnum))*1.1;
        if($nums<=5000000000){
            $nums = 5000000000;
        }
        // Log::channel('web3p')->info('$nums:'.$nums);
        return '0x'.dechex($nums);
    }

    //获取nonce文件路径
    public function getNoncePathByMethod($method){
        $projectName = env('PROJECT_NAME');
        $dirPath = '/www/wwwroot/'.$projectName.'/public/nonce/';

        if(in_array($method,['runNextRound','achievementUpdata'])){
            $fileName= 'nonce_permission.txt';

        }else if(in_array($method,['updataPledgeReward','updataZoneReward','grantBigPrize','withdrawDeveloper'])){
            switch ($method){
                case 'updataPledgeReward':
                    $fileName= 'nonce_pledge.txt';
                    break;
                case 'updataZoneReward':
                    $fileName= 'nonce_zone_reward.txt';
                    break;
                case 'grantBigPrize':
                    $fileName= 'nonce_big_price.txt';
                    break;
                case 'withdrawDeveloper':
                    $fileName= 'nonce_developer_withdraw.txt';
                    break;
                default:
                    $this->errorMsg('参数错误,调用方法不存在');
                    break;
            }
        }
        $filePath = $dirPath.$fileName;

        return $filePath;
    }

    public function getNonceByMethod($method){
        $filePath = $this->getNoncePathByMethod($method);

        $dir_name = dirname($filePath);
        if(!file_exists($dir_name))
        {
            //iconv防止中文名乱码
            $res = mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
        }
        $fp = fopen($filePath,"c+");//打开文件资源通道 不存在则自动创建
        $nonce = file_get_contents($filePath);
        if($nonce < 0){
            $nonce = -1;
        }
        $nonce++;

        fwrite($fp,$nonce);//写入文件


        fclose($fp);//关闭资源通道
//        $nonce = file_get_contents($filePath);
//        if($nonce < 0){
//            $nonce = -1;
//        }
//        $nonce++;
//        file_put_contents($filePath, $nonce);
        return  $nonce;
    }

    //回退nonce
    public function revertNonceByMethod($method){

        $filePath = $this->getNoncePathByMethod($method);

//        $nonce = file_get_contents($filePath);
//        if($nonce < 0){
//            $nonce = -1;
//        }
//        $nonce--;
//        file_put_contents($filePath, $nonce);

        $dir_name = dirname($filePath);
        if(!file_exists($dir_name))
        {
            //iconv防止中文名乱码
            $res = mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
        }
        $fp = fopen($filePath,"c+");//打开文件资源通道 不存在则自动创建
        $nonce = file_get_contents($filePath);
        if($nonce <= 0){
            $nonce = 0;
        }
        $nonce--;
        fwrite($fp,$nonce);//写入文件


        fclose($fp);//关闭资源通道
        return  $nonce;
    }

    public function excuteMethod($contractType='',$method,$params=[]){
        #调用者地址;

        $method2 = $method;
        if($method=='updataReward'){
            if($contractType == 'pledge'){
                $method2='updataPledgeReward';
            }else{
                $method2='updataZoneReward';
            }
        }
        $userAddress = $this->getUserAddress($method2);
        $skey = $this->getPrivateKey($method2);
//        $nonce = $this->getNonceByMethod($method2);
        switch ($contractType){
            case 'pledge':
                $contract = $this->pledgeContract;
                $contractAddress = $this->pledgeContractAddress;
                break;
            case 'zone':
                $contract = $this->zoneContract;
                $contractAddress = $this->zoneContractAddress;
                break;
            case 'main':
                $contract = $this->mainContract;
                $contractAddress = $this->mainContractAddress;
                break;
            default:
                $this->errorMsg('参数错误,调用合约不正确');
                break;
        }

        $nonce = $this->getNonceByAddress($userAddress);
        if(!empty($params)){
            $data = '0x' . $contract->at($contractAddress)->getData($method,$params);
        }else{
            $data = '0x' . $contract->at($contractAddress)->getData($method);
        }

        $contract_transfer_gas = $this->getEthGas();
        $gas_price = $this->getEthGasPrice();

        $eth = $this->web3->eth;

        $txParams = [
            'from' => $userAddress,
            'to' => $contractAddress,
            'value' => '0x0',//合约交易固定0,用户之间钱包互转才有对应的值
            'gas' => $contract_transfer_gas,
            'gasPrice' => $gas_price,
            'data' => $data,
            'chainId' => $this->chainId,
//            'nonce' => '0x'.dechex($nonce),
            'nonce' => $nonce,
        ];
        // write_log($method2.'Log/'.date('Y-m-d').'_param.txt',$txParams);
       // dump($txParams);
        $transaction = new Transaction($txParams);
        $signedTransaction = $transaction->sign($skey);
//      dump($signedTransaction);
//      Send the transaction
//        $callback = new Callback();
//        $result = $eth->sendRawTransaction('0x'.$signedTransaction, $callback);

        try{
            $result = $this->proxyWithdraw($signedTransaction);
            // write_log($method2.'Log/'.date('Y-m-d').'.txt',$result);
//        dump($result);
            if(isset($result['error'])){
                $this->revertNonceByMethod($method2);
            }
        }catch (\Exception $e) {
            $this->revertNonceByMethod($method2);
        }

        return $result;
    }
    public function queryMethod($contractType='',$method,$params){
        switch ($contractType){
            case 'pledge':
                $contract = $this->pledgeContract;
                $contractAddress = $this->pledgeContractAddress;
                break;
            case 'zone':
                $contract = $this->zoneContract;
                $contractAddress = $this->zoneContractAddress;
                break;
            case 'main':
                $contract = $this->mainContract;
                $contractAddress = $this->mainContractAddress;
                break;
            default:
                $this->errorMsg('参数错误,调用合约不正确');
                break;
        }

        $cb = new Callback();
        try {
            $contract->at($contractAddress)->call($method,json_encode($params),$cb);
        }catch (\Exception $e){
            info($e);
        }

//

        $result = $cb->result;
//        dd($result);
        // write_log('query_'.$method.'Log/'.date('Y-m-d').'.txt',$result);

        return $result;
    }

    // public  function proxyWithdraw($hex){
    //     $url=$this->is_test?$this->testApiUrl:$this->apiUrl;
    //     $param = [
    //         'module'=>'proxy',
    //         'action'=>'eth_sendRawTransaction',
    //         'hex'=>$hex,
    //         'apikey' => env('BSC_API_KEY')
    //     ];
    //     $respon = json_decode(curlGet($url,$param),true);
    //     return $respon;
    // }

    public  function proxyWithdraw($hex){
        $url = 'https://bsc-mainnet.nodereal.io/v1/b856adf4dc6d435c8de0988c232a67d8';
        $params['id'] = 1;
        $params['jsonrpc'] = '2.0';
        $params['method'] = 'eth_sendRawTransaction';
        $params['params'] = [
            '0x'.$hex
        ];
        $result = curlPost($url,$params);
        $result = json_decode($result,true);
        if(isset($result['error'])){
            $this->errorMsg($result['error']['message']);
        }
        return $result['result'];
    }


    public function getNonceByAddress($address){
        $url = 'https://bsc-mainnet.nodereal.io/v1/b856adf4dc6d435c8de0988c232a67d8';
        $params['id'] = 1;
        $params['jsonrpc'] = '2.0';
        $params['method'] = 'eth_getTransactionCount';
        $params['params'] = [
            $address,
            'pending'
        ];
        $result = curlPost($url,$params);
        $result = json_decode($result,true);
        if(isset($result['error'])){
            $this->errorMsg($result['error']['message']);
        }
        return $result['result'];
        // $url=$this->is_test?$this->testApiUrl:$this->apiUrl;
        // $param = [
        //     'module'=>'proxy',
        //     'action'=>'eth_getTransactionCount',
        //     'address' => strtolower($address),
        //     'tag'=>'latest',
        //     'apikey' => env('BSC_API_KEY')
        // ];
        // $respon = json_decode(curlGet($url,$param),true);
        // return $respon['result'];
    }
}
