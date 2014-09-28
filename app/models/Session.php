<?php

class Session extends ParentModel {

    public static $instance = null;

    public $_id = null;
    public $cart = ['total'=>0];
    public $disallowNotf = [];
//    private function __construct(){
//    }


    public function pushVal($key, $val){
        if(!is_array($this->{$key})){
            $this->{$key} = [];
        }
        $this->{$key}[] = $val;
        $this->save();
    }

    public function increment($key){
        $Mongo = new Mongo();
        $db = $Mongo->selectDB('home24');
        $db->session->update(['_id' => $this->_id], ['$inc' => ['cart.'.$key => 1]]);
    }

    public function delete($key){
        $Mongo = new Mongo();
        $db = $Mongo->selectDB('home24');
        $db->session->update(['_id' => $this->_id], ['$unset' => ['cart.'.$key => '']]);
    }

    public function getCart(){
        $cart = self::FindFirst([['_id' => $this->_id]])->cart;
        unset($cart['total']);
        return $cart;
    }

    public static function getInstance(){

        if(static::$instance === null){
            $di = Phalcon\DI::getDefault();
            if(!$di->getSession()->has('session_id')){
                $session_id = substr(md5(self::getRandomString(32, 4, 'lun')), 0, 24);
                $di->getSession()->set('session_id', $session_id);
            } else{
                $session_id = $di->getSession()->get('session_id');
            }
            static::$instance = self::FindFirst([['_id' => new MongoId($session_id)]]);
            if(!static::$instance){
                static::$instance = new Session();
                static::$instance->setId($session_id);
                static::$instance->save();
            }
        }
        return static::$instance;
    }

} 