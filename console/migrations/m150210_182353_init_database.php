<?php

use yii\db\Schema;
use yii\db\Migration;

class m150210_182353_init_database extends Migration
{
    public function up()
    {
        $this->createTable('app_settings', array(
            'id' => Schema::TYPE_PK,
            'api_key' => Schema::TYPE_STRING . '(300) DEFAULT NULL',
            'redirect_url' => Schema::TYPE_STRING . '(300) DEFAULT NULL',
            'permissions' => Schema::TYPE_STRING,
            'shared_secret' => Schema::TYPE_STRING . '(300) NOT NULL'
        ),NULL,true);
        
        $this->insert('app_settings',array(
            'api_key'   => '3902f8de5a842727568f5d0642c61ca9',
            'redirect_url'  =>  'http://view-source.ru/frontend/web/index.php?r=site/callback',
            'permissions'   =>  '["read_content","write_content","read_products","write_products","read_customers","write_customers","read_orders","read_shipping","write_shipping","write_orders"]',
            'shared_secret' =>  '6db68208c44e1b073417abcfbe89a6c9'
        ));
        
        $this->createTable('user_settings', array(
            'id' => Schema::TYPE_PK,
            'access_token' => Schema::TYPE_STRING . ' NOT NULL',
            'store_name' => Schema::TYPE_STRING . '(300) NOT NULL',
            'service_url' => Schema::TYPE_STRING . '(300) NOT NULL',
            'team_id' => Schema::TYPE_STRING . '(300) NOT NULL',
            'api_password' => Schema::TYPE_STRING . '(300) NOT NULL',
            'api_token' => Schema::TYPE_STRING . '(300) NOT NULL'
        ),NULL,true);
        
        $this->createTable('collections', array(
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP,
            'updated_at' => Schema::TYPE_TIMESTAMP,
            'store_name' => Schema::TYPE_STRING . '(300) NOT NULL',
            'shopify_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pipeliner_id' => Schema::TYPE_STRING . ' NOT NULL',
            
        ),NULL,true);
        
        $this->createTable('products', array(
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP,
            'updated_at' => Schema::TYPE_TIMESTAMP,
            'store_name' => Schema::TYPE_STRING . '(300) NOT NULL',
            'shopify_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pipeliner_id' => Schema::TYPE_STRING . ' NOT NULL',
            'description'  => Schema::TYPE_STRING,
            'category_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
            'sku'          => Schema::TYPE_STRING . ' NOT NULL',
            'price'          => Schema::TYPE_STRING . ' NOT NULL'
            
        ),NULL,true);
        
        $this->createTable('products_categories', array(
            'id' => Schema::TYPE_PK,
            'product_id' => Schema::TYPE_STRING . ' NOT NULL',
            'collection_id' => Schema::TYPE_TIMESTAMP . ' NOT NULL',
            'store_name' => Schema::TYPE_TIMESTAMP . ' NOT NULL'
        ),NULL,true);
        
        $this->createTable('customers', array(
            'id' => Schema::TYPE_PK,
            'address' => Schema::TYPE_STRING,
            'city' => Schema::TYPE_STRING,
            'comments' => Schema::TYPE_STRING,
            'created_at' => Schema::TYPE_TIMESTAMP,
            'updated_at' => Schema::TYPE_TIMESTAMP,
            'pipeliner_id' => Schema::TYPE_STRING . ' NOT NULL',
            'shopify_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'store_name' => Schema::TYPE_STRING . '(300) NOT NULL',
            'email'  => Schema::TYPE_STRING,
            'first_name'  => Schema::TYPE_STRING,
            'last_name'  => Schema::TYPE_STRING,
            'title'  => Schema::TYPE_STRING,
            'phone'  => Schema::TYPE_STRING,
            'region'  => Schema::TYPE_STRING,
            'country'  => Schema::TYPE_STRING,
            'zip'          => Schema::TYPE_STRING,
            'company'          => Schema::TYPE_STRING,
            'password'          => Schema::TYPE_STRING
            
        ),NULL,true);
    }

    public function down()
    {
        $this->dropTable('app_settings');
        $this->dropTable('user_settings');
        $this->dropTable('collections');
        $this->dropTable('products');
        $this->dropTable('customers');

        return true;
    }
}
