<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use sandeepshetty\shopify_api;
use common\models\Usersettings;
use common\models\Appsettings;
use common\models\Collections;
use common\models\Pipeliner;
use common\models\Shopify;
use common\models\Products;

/**
 * Site controller
 */
class CollectionsController extends Controller
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
		$collection = Collections::getByParams(['shopify_id' => $result->id]);
		if(is_null($collection))
		{
			$collection = new Collections();
			$collection->title = $result->title;
			$collection->shopify_id = $result->id;
			$collection->store_name = $shop;
			$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
			$categories = $pipeliner->productCategories->create();
			$categories->setName($result->title);
			$pipeliner->productCategories->save($categories);
			$collection->pipeliner_id = $categories->getId();
			$collection->save();
		}
	}
	
	public function actionUpdate()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		$collection = Collections::getByParams(['shopify_id' => $result->id]);
		if(is_null($collection))
			return false;
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$categories = $pipeliner->productCategories->getById($collection->pipeliner_id);
		$categories->setName($result->title);
		$shopify = Shopify::getInstance()->setStore($shop)->getShopify();
		$collectionProducts = $shopify('GET','/admin/collects.json',['collection_id' => $collection->shopify_id]);
		foreach($collectionProducts as $productInCollection)
		{
			$product = Products::getByParams(['shopify_id' => $productInCollection['product_id']]);
			if(is_null($product))
				continue;
			$product->category_id = $productInCollection['collection_id'];
			$products = $pipeliner->products->getById($product->pipeliner_id);
			$products->setProductCategoryId($collection->pipeliner_id);
			Yii::info($productInCollection['product_id'],'callbackInfo');
			$product->save();
			$pipeliner->products->save($products);
		}
		$pipeliner->productCategories->save($categories);
		$collection->save();
	}
	
	public function actionDelete()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		$collection = Collections::getByParams(['shopify_id' => $result->id]);
		if(is_null($collection))
			return false;
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$categories = $pipeliner->productCategories->getById($collection->pipeliner_id);
		$categories->setIsDeleted(1);
		$pipeliner->productCategories->save($categories);
		$collection->delete();
	}
}