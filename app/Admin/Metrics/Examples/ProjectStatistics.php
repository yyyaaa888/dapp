<?php

namespace App\Admin\Metrics\Examples;

use Dcat\Admin\Widgets\Metrics\RadialBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Web3p\EthereumTx\Transaction;
use Web3p\RLP\RLP;
use Web3\Web3;
use Web3\Contract;
use App\Service\Eth\Callback;

class ProjectStatistics extends RadialBar
{
    /**
     * 初始化卡片内容
     */
    protected function init()
    {
        parent::init();

        $this->title('');
        $this->height(300);
        $this->chartHeight(300);
        $this->chartLabels('');
        $this->dropdown([
            // '7' => 'Last 7 Days',
            // '28' => 'Last 28 Days',
            // '30' => 'Last Month',
            // '365' => 'Last Year',
        ]);
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        // 卡片底部
        $this->withFooter([]);
    }

    /**
     * 卡片底部内容.
     *
     * @param string $new
     * @param string $open
     * @param string $response
     *
     * @return $this
     */
    public function withFooter($data)
    {
        return $this->footer(
            <<<HTML

HTML
        );
    }
}
