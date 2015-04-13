<?php
namespace console\controllers;

use Yii;
use yii\web\Controller;
use PipelinerSales\ApiClient\PipelinerClient;
use sandeepshetty\shopify_api;
use common\models\Collections;
use common\models\Products;
use common\models\Customers;
use common\models\Pipeliner;
use common\models\Shopify;

class PipelinerController extends \yii\console\Controller
{
    
    private $pipelinerServiceUrl = 'https://eu.pipelinersales.com';
    private $pipelinerTeamId = 'us_OpsWay1';
    private $pipelinerApiPassword = 'FaQvkjJ8qniXrFhu';
    private $pipelinerApiToken = 'us_OpsWay1_GPOTUQ1T1GAHYM9A';
    
    public function actionIndex() 
    {
        $pipeliner = $this->getPipeliner();    
        $shopify = $this->getShopify();
        $filter = Pipeliner::getQueryFilter();
        $categories = $pipeliner->productCategories->get($filter::equals('IS_DELETED',0));
        $accountIterator = $pipeliner->productCategories->getEntireRangeIterator($categories);
        foreach($accountIterator as $account)
        {
            $account->getId()."\r\n";
            $collections = Collections::getByParams(['pipeliner_id' => $account->getId()]);
            if(is_null($collections))
            {
                $collections = new Collections();
                $collections->title = $account->getName();
                $collections->store_name = Pipeliner::getInstance()->getStoreName();
                $collections->created_at = date('Y-m-d H:i:s');
                $collections->pipeliner_id = $account->getId();
                $collections->save();
            } else {
                $collections->title = $account->getName();
                $collections->save();               
            }
            
            $method = ($collections->shopify_id == 0) ? 'POST' : 'PUT';
            $url = ($collections->shopify_id == 0) ? '/admin/custom_collections.json' : '/admin/custom_collections/' . $collections->shopify_id . '.json';
            $response = $shopify($method,$url,[
                'custom_collection' => [
                    'title' => $account->getName()
                ]
            ]);
            if($collections->shopify_id == 0)
            {
                $collections->shopify_id = $response['id'];
                $collections->save();         
            }
        }
        
    }
    
    public function actionSynccategories()
    {

    }
    
    
    private function getPipeliner()
    {
        /*$post = Yii::$app->request->post();*/
        $post['store'] = 'stder.myshopify.com';
        return Pipeliner::getInstance()->setStore($post['store'])->getPipeliner();
    }
    
    private function getShopify()
    {
        /*$post = Yii::$app->request->post();*/
        $post['store'] = 'stder.myshopify.com';
        return Shopify::getInstance()->setStore($post['store'])->getShopify();
    }
}