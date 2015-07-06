<?php
namespace common\models;

use Yii;
use common\models\Appsettings;
use common\models\Usersettings;
use sandeepshetty\shopify_api;

class Shopify extends Baseapi
{
    
    private static $instance = null;
    private $shopify = null;
   
    public static function getInstance()
    {
        if(is_null(self::$instance))
            self::$instance = new Shopify();
        return self::$instance;
    }
    
    public function __construct() 
    {
        
    }

    public function getShopify()
    {
        if(is_null($this->shopify))
        {
            $app_settings = Appsettings::find()->one();
            $user_settings = Usersettings::getByParams(['store_name' => $this->getStoreName()]);
            $this->shopify = shopify_api\client(
                $this->storeName, $user_settings['access_token'], $app_settings['api_key'], $app_settings['shared_secret']
            );
        }
        return $this->shopify;
    }
    
}
