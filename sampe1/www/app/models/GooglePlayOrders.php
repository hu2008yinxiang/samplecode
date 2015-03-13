<?php

class GooglePlayOrders extends Phalcon\Mvc\Model
{

    /**
     * 订单号长度大约64位
     *
     * @var string
     */
    public $order_id;

    /**
     * 包名长度大约 128位
     *
     * @var string
     */
    public $package_name;

    /**
     * 即IAPID 大约 64位
     *
     * @var string
     */
    public $product_id;

    /**
     * 账号 大约 12位
     *
     * @var string
     */
    public $account_id;

    /**
     * 支付时间 从data解析得到
     * 
     * @var string
     */
    public $purchase_time;

    /**
     * 支付日期 从purchase_time生成
     * 
     * @var string
     */
    public $purchase_date;

    /**
     * 客户端传过来的签名
     * 
     * @var string
     */
    public $sign;

    /**
     * 客户端传过来的订单信息
     * 
     * @var string
     */
    public $data;
}