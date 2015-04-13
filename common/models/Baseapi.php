<?php
namespace common\models;

class Baseapi
{
    
    protected $storeName;
    
    public function setStore($storeName)
    {
        $this->storeName = $storeName;
        
        return $this;
    }
    
    public function getStoreName()
    {
        return $this->storeName;
    }
    
}
