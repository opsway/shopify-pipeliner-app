<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use sandeepshetty\shopify_api;
use common\models\Usersettings;
use common\models\Appsettings;
use common\models\Customers;
use common\models\Pipeliner;
use common\models\Shopify;
use common\models\Collections;
use common\models\Products;

/**
 * Site controller
 */
class PipelinerController extends Controller
{

	public $enableCsrfValidation = false;
	private $userSettings = null;
	private $appSettings = null;
	private $pipeliner = null;
	private $shopify = null;
	private $store = null;
	
	
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
	
	public function actionGetdata()
	{
		$shop = Yii::$app->request->post()['store'];
		$userSettings = Usersettings::getByParams(['store_name' => $shop]);
		$appSettings = Yii::$app->db->createCommand('SELECT * FROM app_settings')->queryOne();
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$shopify = Shopify::getInstance()->setStore($shop)->getShopify();
		
		$this->setUserSettings($userSettings)
			 ->setAppSettings($appSettings)
			 ->setPipeliner($pipeliner)
			 ->setShopify($shopify)
			 ->setStore($shop)
			 ->syncCollections()
			 ->syncDeletedCollections()
			 ->syncProducts()
			 ->syncDeletedProducts()
			 ->syncCustomers()
			 ->syncDeletedCustomers();
		
		json_encode(array(
			'success'	=>	true
		));
	}
	
	private function syncCollections()
	{
		$pipeliner = $this->getPipeliner();
		$shopify = $this->getShopify();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('IS_DELETED',0);
		$collections = $pipeliner->productCategories->get($filter);
		foreach($collections as $collection)
		{
			$shopifyCollection = Collections::getByParams(['pipeliner_id' => $collection->getId()]);
			if(is_null($shopifyCollection))
			{
				$shopifyCollection = new Collections();
				$result = $shopify('POST','/admin/custom_collections.json',array(
					'custom_collection' => array(
						'title'	=>	$collection->getName()
					)
				));
				$shopifyCollection->shopify_id = $result['id'];
			}
			$shopifyCollection->title = $collection->getName();
			$shopifyCollection->store_name = $this->getStore();
			$shopifyCollection->pipeliner_id = $collection->getId();
			$shopifyCollection->save();

			$shopify('PUT','/admin/custom_collections/' . $shopifyCollection->shopify_id . '.json',array(
				'custom_collection' => array(
					'title'	=>	$collection->getName(),
					'id'	=>	$shopifyCollection->shopify_id
				)
			));

		}
		
		return $this;
	}
	
	private function syncDeletedCollections()
	{
		$pipeliner = $this->getPipeliner();
		$shopify = $this->getShopify();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('IS_DELETED',1);
		$collections = $pipeliner->productCategories->get($filter);
		foreach($collections as $collection)
		{
			$shopifyCollection = Collections::getByParams(['pipeliner_id' => $collection->getId()]);	
			if(is_null($shopifyCollection))
				continue;
			$shopify('DELETE','/admin/custom_collections/' . $shopifyCollection->shopify_id . '.json');			
			$shopifyCollection->delete();
		}
		return $this;
	}
	
	private function syncProducts()
	{
		$pipeliner = $this->getPipeliner();
		$shopify = $this->getShopify();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('IS_DELETED',0);
		$products = $pipeliner->products->get($filter);
		foreach($products as $product)
		{
			$shopifyProduct = Products::getByParams(['pipeliner_id' => $product->getId()]);
			if(is_null($shopifyProduct))
			{
				$shopifyProduct = new Products();
				$result = $shopify('POST','/admin/products.json',array(
					'product'	=>	array(
						'title'	=>	$product->getName(),
						'body_html' =>	$product->getDescription(),
						'variants'	=>	array(
							'sku'	=>	$product->getSku(),
							'price' =>	$this->getProductPrice($product->getId()),
							'option1'	=>	'First'
						)
					)
				));
				$shopifyProduct->shopify_id = $result['id'];
			}
			$collection = Collections::getByParams(['pipeliner_id' => $product->getProductCategoryId()]);
			$collectionId = (is_null($collection)) ? 0 : $collection->shopify_id;
			$shopifyProduct->title = $product->getName();
			$shopifyProduct->pipeliner_id = $product->getId();
			$shopifyProduct->description = $product->getDescription();
			$shopifyProduct->sku = $product->getSku();
			$shopifyProduct->category_id = $collectionId;
			$shopifyProduct->price = $this->getProductPrice($product->getId());
			$shopifyProduct->store_name = $this->getStore();
			$shopifyProduct->save();
			$shopify('PUT','/admin/products/' . $shopifyProduct->shopify_id . '.json',array(
				'product' => array(
					'title'	=>	$product->getName(),
					'body_html' =>	$product->getDescription(),
					'variants'	=>	array(
						'sku'	=>	$product->getSku(),
						'price' =>	$this->getProductPrice($product->getId()),
						'option1'	=>	'First'
					)
				)
			));
		}
		
		return $this;
	}
	
	private function syncDeletedProducts()
	{
		$pipeliner = $this->getPipeliner();
		$shopify = $this->getShopify();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('IS_DELETED',1);
		$products = $pipeliner->products->get($filter);
		foreach($products as $product)
		{
			$shopifyProduct = Products::getByParams(['pipeliner_id' => $product->getId()]);
			if(is_null($shopifyProduct))
				continue;
			else
			{			
				$product = $shopify('GET','/admin/products.json',array(
					'ids' => $shopifyProduct['shopify_id']
					)
				);

				if(!empty($product))
					$shopify('DELETE','/admin/products/' . $shopifyProduct->shopify_id . '.json');
			}

			$shopifyProduct->delete();
		}		
		
		return $this;
	}
	
	private function syncCustomers()
	{
		$pipeliner = $this->getPipeliner();
		$shopify = $this->getShopify();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('IS_DELETED',0);
		$accounts = $pipeliner->accounts->get($filter);
		foreach($accounts as $account)
		{
			$customer = Customers::getByParams(['pipeliner_id' => $account->getId()]);
			if(is_null($customer))
			{
				$customer = new Customers();
				$password = $this->getPassword();
				$result = $shopify('POST','/admin/customers.json',array(
					'customer'	=>	array(
						'first_name'	=>	$account->getFirstname(),
						'last_name'	=>	$account->getLastname(),
						'email'	=>	$account->getEmail1(),
						'verified_email'	=>	false,
						'default_address'	=>	array(
							'address1'	=>	$account->getAddress(),
							'province'	=>	$account->getStateProvince(),
							'phone'	=>	$account->getPhone1(),
							'zip'	=>	$account->getZipCode(),
							'last_name'	=>	$account->getLastname(),
							'first_name'	=>	$account->getFirstname(),
							'country'	=>	$account->getCountry(),
							'city'	=>	$account->getCity(),
							'company'	=>	$account->getOrganization()
						),
						'password'	=>	$password,
						'password_confirmation'	=>	$password,
						'send_email_welcome'	=>	true,
						'note'	=>	$account->getComments()
					)
				));
				$customer->shopify_id = $result['id'];
				$customer->password = $password;
			}
			
            $customer->address		=	$account->getAddress();
            $customer->city			=	$account->getCity();
            $customer->comments		=	$account->getComments();
            $customer->store_name	=	$this->getStore();
            $customer->email		=	$account->getEmail1();
            $customer->first_name	=	$account->getFirstname();
            $customer->last_name	=	$account->getLastname();
            $customer->title		=	$account->getFirstname() . ' ' . $account->getLastname();
            $customer->phone		=	$account->getPhone1();
            $customer->region		=	$account->getStateProvince();
            $customer->country		=	$account->getCountry();
            $customer->zip			=	$account->getZipCode();
            $customer->company		=	$account->getOrganization();
			$customer->pipeliner_id =	$account->getId();
			$customer->save();
			$shopify('PUT','/admin/customers/' . $customer->shopify_id . '.json',array(
					'customer'	=>	array(
						'first_name'	=>	$account->getFirstname(),
						'last_name'	=>	$account->getLastname(),
						'email'	=>	$account->getEmail1(),
						'verified_email'	=>	false,
						'default_address'	=>	array(
							'address1'	=>	$account->getAddress(),
							'province'	=>	$account->getStateProvince(),
							'phone'	=>	$account->getPhone1(),
							'zip'	=>	$account->getZipCode(),
							'last_name'	=>	$account->getLastname(),
							'first_name'	=>	$account->getFirstname(),
							'country'	=>	$account->getCountry(),
							'city'	=>	$account->getCity(),
							'company'	=>	$account->getOrganization()
						),
						'password'	=>	$password,
						'password_confirmation'	=>	$password,
						'send_email_welcome'	=>	true,
						'note'	=>	$account->getComments()
					)
			));			
		}
		
		return $this;
		
	}
	
	private function syncDeletedCustomers()
	{
		$pipeliner = $this->getPipeliner();
		$shopify = $this->getShopify();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('IS_DELETED',0);
		$accounts = $pipeliner->accounts->get($filter);
		foreach($accounts as $account)
		{
			$customer = Customers::getByParams(['pipeliner_id' => $account->getId()]);
			if(is_null($customer))
				continue;
			$shopify('DELETE','/admin/customers/' . $customer->shopify_id . '.json');
			$customer->delete();
		}
		return $this;
	}
	
	/* Private getters and setters */
	private function getPassword($length = 6)
	{
		$symbols = 'abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$password = '';
		for($i = 0;$i != $length;$i++)
			$password .= $symbols[rand(0,61)];
		return $password;
	}
	
	private function getProductPrice($id)
	{
		$pipeliner = $this->getPipeliner();
		$filter = Pipeliner::getQueryFilter();
		$filter->equals('PRODUCT_ID',$id);
		$price = null;
		$prices = $pipeliner->productPriceListPrices->get($filter);
		foreach($prices as $item)
			$price = $item->getPrice();
		return (empty($price)) ? 0 : $price;
	}
	
	private function setStore($store)
	{
		if(is_null($this->store))
			$this->store = $store;
		return $this;		
	}
	
	private function getStore()
	{
		return $this->store;
	}
	
	private function setUserSettings($userSettings)
	{
		if(is_null($this->userSettings))
			$this->userSettings = $userSettings;
		return $this;
	}
	
	private function getUserSettings()
	{
		return $this->userSettings;
	}
	
	private function setAppSettings($appSettings)
	{
		if(is_null($this->appSettings))
			$this->appSettings = $appSettings;
		return $this;		
	}
	
	private function getAppSettings()
	{
		return $this->appSettings;
	}
	
	private function setPipeliner($pipeliner)
	{
		if(is_null($this->pipeliner))
			$this->pipeliner = $pipeliner;
		return $this;		
	}
	
	private function getPipeliner()
	{
		return $this->pipeliner;
	}
	
	private function setShopify($shopify)
	{
		if(is_null($this->shopify))
			$this->shopify = $shopify;
		return $this;		
	}
	
	private function getShopify()
	{
		return $this->shopify;
	}
	
}