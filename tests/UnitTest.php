<?php

namespace Test;

use Faker\Factory;
use Jetfuel\Txfpay\BankPayment;
use Jetfuel\Txfpay\Constants\Bank;
use Jetfuel\Txfpay\Constants\Channel;
use Jetfuel\Txfpay\DigitalPayment;
use Jetfuel\Txfpay\TradeQuery;
use Jetfuel\Txfpay\Traits\NotifyWebhook;
use Jetfuel\Txfpay\BalanceQuery;
use PHPUnit\Framework\TestCase;

class UnitTest extends TestCase
{
    private $orgId;
    private $merchantId;
    private $secretKey;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->orgId = getenv('ORG_ID');
        $this->merchantId = getenv('MERCHANT_ID');
        $this->secretKey = getenv('SECRET_KEY');
    }

    public function testDigitalPaymentOrder()
    {
        $faker = Factory::create();
        $tradeNo = date('YmdHis').rand(1000, 9999);
        $channel = Channel::UNIONPAY;
        $amount = 2;
        $notifyUrl = $faker->url;
        $returnUrl = $faker->url;

        $payment = new DigitalPayment($this->orgId, $this->merchantId, $this->secretKey);
        $result = $payment->order($tradeNo, $channel, $amount, $notifyUrl, $returnUrl);
        var_dump($result);
        $this->assertArrayHasKey('result',$result);
        
        return $tradeNo;
    }

    /**
     * @depends testDigitalPaymentOrder
     *
     * @param $tradeNo
     */
    public function testDigitalPaymentOrderFind($tradeNo)
    {
        $tradeQuery = new TradeQuery($this->orgId, $this->merchantId, $this->secretKey);
        $result = $tradeQuery->find($tradeNo);
        var_dump($result);
        $this->assertEquals('00', $result['respCode']);
    }

    /**
     * @depends testDigitalPaymentOrder
     *
     * @param $tradeNo
     */
    public function testDigitalPaymentOrderIsPaid($tradeNo)
    {
        $tradeQuery = new TradeQuery($this->orgId, $this->merchantId, $this->secretKey);
        $result = $tradeQuery->isPaid($tradeNo);

        $this->assertFalse($result);
    }

    public function testBankPaymentOrder()
    {
        $faker = Factory::create();
        $tradeNo = date('YmdHis').rand(1000, 9999);
        $bank = Bank::ICBC;
        $amount = 2;
        $returnUrl = 'http://www.yahoo.com';//$faker->url;
        $notifyUrl = 'http://www.yahoo.com';//'$faker->url;

        $payment = new BankPayment($this->orgId, $this->merchantId, $this->secretKey);
        $result = $payment->order($tradeNo, $bank, $amount, $notifyUrl, $returnUrl);
        var_dump($result);

        $this->assertContains('https', $result, '', true);

        return $tradeNo;
    }

    /**
     * @depends testBankPaymentOrder
     *
     * @param $tradeNo
     */
    public function testBankPaymentOrderFind($tradeNo)
    {
        $tradeQuery = new TradeQuery($this->orgId, $this->merchantId, $this->secretKey);
        $result = $tradeQuery->find($tradeNo);
        
        $this->assertEquals('00', $result['respCode']);
    }

    /**
     * @depends testBankPaymentOrder
     *
     * @param $tradeNo
     */
    public function testBankPaymentOrderIsPaid($tradeNo)
    {
        $tradeQuery = new TradeQuery($this->orgId, $this->merchantId, $this->secretKey);
        $result = $tradeQuery->isPaid($tradeNo);

        $this->assertFalse($result);
    }

    public function testTradeQueryFindOrderNotExist()
    {
        $faker = Factory::create();
        $tradeNo = substr($faker->uuid,0,20);

        $tradeQuery = new TradeQuery($this->orgId, $this->merchantId, $this->secretKey);
        $result = $tradeQuery->find($tradeNo);

        $this->assertNull($result);
    }

    public function testTradeQueryIsPaidOrderNotExist()
    {
        $faker = Factory::create();
        $tradeNo = substr($faker->uuid,0,20);

        $tradeQuery = new TradeQuery($this->orgId, $this->merchantId, $this->secretKey);
        $result = $tradeQuery->isPaid($tradeNo);

        $this->assertFalse($result);
    }

    public function testNotifyWebhookVerifyNotifyPayload()
    {
        $mock = $this->getMockForTrait(NotifyWebhook::class);

        // $payload = [
        //     'orgid'          => '3320183127110317',
        //     'merno'          => '562018352711036625',
        //     'amount'         => '200',
        //     'goods_info'     => 'goods_info',
        //     'trade_date'     => '2018-04-03 10:00:00',
        //     'trade_status'   => '0',
        //     'order_id'       => '201804030605516317',
        //     'plat_order_id'  => '2018040314055371674643',
        //     'sign_data'      => 'C7EE6DA3792A752D836DC92DEE46B8CF',
        //     'timestamp'      => '20180403141901',
        // ];

        $payload = [
            'orgid'          => '3320183127110317',
            'merno'          => '562018352711036625',
            'amount'         => '1000',
            'goods_info'     => 'goods_info',
            'trade_date'     => '2018-04-09 17:18:34',
            'trade_status'   => '0',
            'order_id'       => '201804091717589354',
            'plat_order_id'  => '2018040917175841914692',
            'sign_data'      => '79eba19243492b92d3fbc28478e96221',
            'timestamp'      => '20180409185013',
        ];

        $this->assertTrue($mock->verifyNotifyPayload($payload, $this->secretKey));
    }

    public function testNotifyWebhookParseNotifyPayload()
    {
        $mock = $this->getMockForTrait(NotifyWebhook::class);

        $payload = [
            'orgid'          => '3320183127110317',
            'merno'          => '562018352711036625',
            'amount'         => '1000',
            'goods_info'     => 'goods_info',
            'trade_date'     => '2018-04-09 17:18:34',
            'trade_status'   => '0',
            'order_id'       => '201804091717589354',
            'plat_order_id'  => '2018040917175841914692',
            'sign_data'      => '79eba19243492b92d3fbc28478e96221',
            'timestamp'      => '20180409185013',
        ];

        $this->assertEquals([
            'orgid'          => '3320183127110317',
            'merno'          => '562018352711036625',
            'amount'         => '10',
            'goods_info'     => 'goods_info',
            'trade_date'     => '2018-04-09 17:18:34',
            'trade_status'   => '0',
            'order_id'       => '201804091717589354',
            'plat_order_id'  => '2018040917175841914692',
            'sign_data'      => '79eba19243492b92d3fbc28478e96221',
            'timestamp'      => '20180409185013',
        ], $mock->parseNotifyPayload($payload, $this->secretKey));
    }

    public function testNotifyWebhookSuccessNotifyResponse()
    {
        $mock = $this->getMockForTrait(NotifyWebhook::class);

        $this->assertEquals('0000', $mock->successNotifyResponse());
    }

}
