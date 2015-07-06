<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use sandeepshetty\shopify_api;
use common\models\Usersettings;
use common\models\Appsettings;

/**
 * Site controller
 */
class OrdersController extends Controller
{

	public $enableCsrfValidation = false;
	
    public function behaviors()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
			'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
	
	public function actionCreate()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		Yii::info($post,'callbackInfo');
	}
	
	public function actionUpdate()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		Yii::info($post,'callbackInfo');
	}
	
	public function actionDelete()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		Yii::info($post,'callbackInfo');
	}
}