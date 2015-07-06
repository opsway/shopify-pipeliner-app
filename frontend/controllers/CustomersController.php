<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use sandeepshetty\shopify_api;
use common\models\Usersettings;
use common\models\Appsettings;
use common\models\Customers;
use common\models\Pipeliner;

/**
 * Site controller
 */
class CustomersController extends Controller
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
		$customer = new Customers();
        $customer->address = $result->default_address->address1;
        $customer->city =  $result->default_address->city;
        $customer->comments = $result->note;
        $customer->shopify_id = $result->id;
        $customer->store_name = $shop;
        $customer->email = $result->email;
        $customer->first_name = $result->first_name;
        $customer->last_name = $result->last_name;
        $customer->title = $result->default_address->name;
        $customer->phone = $result->default_address->phone;
        $customer->region = $result->default_address->province;
        $customer->country = $result->default_address->country_name;
        $customer->zip = $result->default_address->zip;
        $customer->company = $result->default_address->company;
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		/*$clients = $pipeliner->clients->create();
		$clients->setDefaultSalesUnitId(0);
		$clients->setEmail($result->email);
		$clients->setFirstname($result->first_name);
		$clients->setLastname($result->last_name);
		$clients->setMasterRightId('DV-STANDARD_USER');
		//$clients->setusername($result->email);
		$pipeliner->clients->save($clients);*/
		
		$accounts = $pipeliner->accounts->create();
		$accounts->setAccountClass(1);
		$accounts->setAccountTypeId('DV-1');
		$accounts->setAddress($result->default_address->address1);
		$accounts->setCity($result->default_address->city);
		$accounts->setComments($result->note);
		$accounts->setCountry($result->default_address->country_name);
		$accounts->setEmail1($result->email);
		$accounts->setIndustriesId('PY-7FFFFFFF-33AB019E-EC46-4151-A4F1-72714CC08DAF');
		$accounts->setOwnerId(27612);
		$accounts->setPhone1($result->default_address->phone);
		$accounts->setSalesUnitId(0);
		$accounts->setOrganization($result->default_address->company);
		$accounts->setStateProvince($result->default_address->province);
		$accounts->setZipCode($result->default_address->zip);
		$pipeliner->accounts->save($accounts);
		$customer->pipeliner_id = $accounts->getId();
		$customer->save();
	}
	
	public function actionUpdate()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		$customer = Customers::getByParams(['shopify_id' => $result->id]);
		$customer->address = $result->default_address->address1;
        $customer->city =  $result->default_address->city;
        $customer->comments = $result->note;
        $customer->shopify_id = $result->id;
        $customer->store_name = $shop;
        $customer->email = $result->email;
        $customer->first_name = $result->first_name;
        $customer->last_name = $result->last_name;
        $customer->title = $result->default_address->name;
        $customer->phone = $result->default_address->phone;
        $customer->region = $result->default_address->province;
        $customer->country = $result->default_address->country_name;
        $customer->zip = $result->default_address->zip;
        $customer->company = $result->default_address->company;
		$customer->save();
		$pipeliner = Pipeliner::getInstance()->setStore($shop)->getPipeliner();
		$accounts = $pipeliner->accounts->getById($customer->pipeliner_id);
		$accounts->setAccountClass(1);
		$accounts->setAccountTypeId('DV-1');
		$accounts->setAddress($result->default_address->address1);
		$accounts->setCity($result->default_address->city);
		$accounts->setComments($result->note);
		$accounts->setCountry($result->default_address->country_name);
		$accounts->setEmail1($result->email);
		$accounts->setIndustriesId('PY-7FFFFFFF-33AB019E-EC46-4151-A4F1-72714CC08DAF');
		$accounts->setOwnerId(27612);
		$accounts->setPhone1($result->default_address->phone);
		$accounts->setSalesUnitId(0);
		$accounts->setOrganization($result->default_address->company);
		$accounts->setStateProvince($result->default_address->province);
		$pipeliner->accounts->save($accounts);
	}
	
	public function actionDelete()
	{
		$shop = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
		$post = Yii::$app->request->getRawBody();
		$result = json_decode($post);
		$customer = Customers::getByParams(['shopify_id' => $result->id]);
		if(is_null($customer))
			return false;
		$accounts = $pipeliner->accounts->getById($customer->pipeliner_id);
		$accounts->setIsDeleted(1);
		$pipeliner->accounts->save($accounts);
		$customer->delete();
	}
}