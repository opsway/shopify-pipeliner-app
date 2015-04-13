<?php
namespace common\models;

use Yii;
use PipelinerSales\ApiClient\PipelinerClient;
use common\models\Usersettings;
use PipelinerSales\ApiClient\Query\Filter;


class Pipeliner extends Baseapi
{
    
    private static $instance = null;
    private $pipeliner = null;
    
    public static function getInstance()
    {
        if(is_null(self::$instance))
            self::$instance = new Pipeliner();
        return self::$instance;
    }
    
    public function __construct() 
    {
        
    }
    
    public function setStore($storeName)
    {
        $this->storeName = $storeName;
        
        return $this;
    }


    public function getPipeliner()
    {
        if(is_null($this->pipeliner))
        {
            $settings = Usersettings::getByParams(['store_name' => $this->getstoreName()]);

            $this->pipeliner = PipelinerClient::create(
                $settings['service_url'],
                $settings['team_id'],
                $settings['api_token'],
                $settings['api_password']
            );
        }
        return $this->pipeliner;
    }
    
    public static function getQueryFilter()
    {
        return new Filter;
    }
    
}
