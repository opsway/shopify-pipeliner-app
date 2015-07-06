<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use sandeepshetty\shopify_api;
use common\models\Usersettings;
use common\models\Appsettings;
use common\models\Products;
use common\models\Pipeliner;

/**
 * Site controller
 */
class ProductsController extends Controller
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
		$product = new Products();
		$product->title = $result->title;
		$product->store_name = $shop;
		$product->shopify_id = $result->id;
		$product->description = $result->body_html;
		$product->sku = $result->variants[0]->sku;
		$product->price = $result->variants[0]->price;
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$products = $pipeliner->products->create();
		$products->setDescription($result->body_html);
		$products->setName($result->title);
		$products->setSku($result->variants[0]->sku);
		$products->setUnitSymbol($result->variants[0]->weight_unit);
		$pipeliner->products->save($products);
		$product->pipeliner_id = $products->getId();
		$product->save();
		$this->setProductPrice($product);
	}
	
	public function actionUpdate()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		$product = Products::getByParams(['shopify_id' => $result->id]);
		if(is_null($product))
			return false;
		$product->title = $result->title;
		$product->store_name = $shop;
		$product->description = $result->body_html;
		$product->sku = $result->variants[0]->sku;
		$product->price = $result->variants[0]->price;
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$products = $pipeliner->products->getById($product->pipeliner_id);
		$products->setDescription($result->body_html);
		$products->setName($result->title);
		$products->setSku($result->variants[0]->sku);
		//$products->setProductCategoryId($_cat->getData('pipeliner_api_id'));
		$products->setUnitSymbol($result->variants[0]->weight_unit);
		$pipeliner->products->save($products);
		$product->save();
		$this->setProductPrice($product);
	}
	
	public function actionDelete()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		$product = Products::getByParams(['shopify_id' => $result->id]);
		if(is_null($product))
		{
			Yii::info('NOT FOUND','callbackInfo');
			Yii::info($post,'callbackInfo');
			return false;
		}
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$products = $pipeliner->products->getById($product->pipeliner_id);
		$products->setIsDeleted(1);
		$pipeliner->products->save($products);
		$product->delete();
	}
	
	private function setProductPrice($product)
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
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
}