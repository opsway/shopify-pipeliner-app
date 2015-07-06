<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Usersettings;
use sandeepshetty\shopify_api;
use common\models\Pipeliner;
use common\models\Products;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
	 
	public $enableCsrfValidation = false;
	 
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
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
            ]
        ];
    }

    public function actionIndex()
    {
		
		$shop = 'stder.myshopify.com';
		$product = Products::getByParams(['id' => 6]);
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		
		$priceLists = $pipeliner->productPriceLists->get();
		$accountIterator = $pipeliner->productPriceLists->getEntireRangeIterator($priceLists);
		$priceListId = null;
		foreach ($accountIterator as $account) {
			$priceListId = $account->getId();
		}
		
		 if (empty($priceListId)) {
			$priceList = $pipeliner->productPriceLists->create();
			$priceList->setStartDate(date('Y-m-d'));
			$priceList->setEndDate(date('Y-m-d'));
			$pipeliner->productPriceLists->save($priceList);
		} else {
			$priceList = $pipeliner->productPriceLists->getById($priceListId);
		}

		$price = $pipeliner->productPriceListPrices->create();
		$price->setProductId($product->pipeliner_id);
		$price->setPrice($product->price);
		$price->setProductPriceListId($priceList->getId());
		$pipeliner->productPriceListPrices->save($price);
    }
	
	public function actionSaveconfig()
	{
		$settings = Usersettings::getByParams(['store_name' => $_POST['store']]);
		$result = array();
		if(is_null($settings))
		{
			$result = array(
				'success'	=>	false,
				'errors' => array('Store not found')
			);
		} else {
			foreach($_POST['formData'] as $input)
			{
				if($input['name'] == 'service_url')
					$settings->service_url = $input['value'];
				if($input['name'] == 'team_id')
					$settings->team_id = $input['value'];				
				if($input['name'] == 'api_password')
					$settings->api_password = $input['value'];				
				if($input['name'] == 'api_token')
					$settings->api_token = $input['value'];
			}
			$settings->save();
			$result = array(
				'success'	=>	true,
				'errors' => array('OK')
			);
		}
		echo json_encode($result);
	}
}
