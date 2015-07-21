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
class AppController extends Controller
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
	
	public function actionCallback()
	{
		$command = Yii::$app->db->createCommand('SELECT * FROM app_settings');
		$settings = $command->queryOne();
		$get = Yii::$app->request->get();
        if(isset($get['code']))
        {
            $shop = $get['shop'];
            $access_token = shopify_api\oauth_access_token(
                            $shop, $settings['api_key'], $settings['shared_secret'], $get['code']
            );
            $shopify = shopify_api\client(
                            $shop, $access_token, $settings['api_key'], $settings['shared_secret']
            );

            $hooks = array(
                'customers/create', 
				'customers/update', 
				'customers/delete', 
				'products/create', 
				'products/update', 
				'products/delete', 
				'collections/create', 
				'collections/update', 
				'collections/delete', 
				'orders/create', 
				'orders/delete', 
				'orders/updated',
				'app/uninstalled',
            );

            foreach($hooks as $hook)
            {
                    $arguments = array(
                            'webhook' => array(
                                    'topic' => $hook,
                                    'address' => 'https://apps.opsway.com/shopify/pipeliner/frontend/web/index.php?r=' . $hook,
                                    'format' => "json"
                            )
                    );
                    $shopify('POST', '/admin/webhooks.json', $arguments);
            }

            $userSettings = new Usersettings();
            $userSettings->access_token = $access_token;
            $userSettings->store_name = $shop;
            $userSettings->save();

            //Yii::$app->db->createCommand('INSERT INTO user_settings(`access_token`,`store_name`,`old_cart`) VALUES("' . $access_token . '", "' . $shop . '","' . \Yii::$app->db->quoteValue() . '")')->execute();

            $this->redirect('https://' . $shop . '/admin/apps',302);
		} else {
			$userSettings = Usersettings::getByParams(['store_name' => $get['shop']]);
			echo \Yii::$app->view->renderFile('@app/views/site/shopify.php',['store_settings' => $settings,'user_settings' => $userSettings]);
		}
	}
        
	public function actionUninstalled()
	{
		$userSettings = Usersettings::getByParams(['store_name' => $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN']]);
                
		if(is_null($userSettings))
			return false;
		$userSettings->delete();
	}

}
?>