<?php

$_POST = json_decode(file_get_contents("php://input"), true);
class ApiController extends ControllerBase {

    public function indexAction() {

    }

    public function listingAction() {
        $args = func_get_args();
        $on_page = 10;
        if(preg_match('#^[0-9]+$#', end($args))){
            $current_page = array_pop($args);
        } else{
            $current_page = 1;
        }
        try{
            $condition = [];
            $skip = $on_page * ($current_page - 1);
            $count = Product::count($condition);
            $max_page = ceil($count / $on_page);
            if($current_page > $max_page){
                throw new Exception('Wrong page num', 400);
            }
            $Products = Product::find(['conditions' => $condition,'limit' => $on_page, 'skip' => $skip]);
            $r = [];
            $keys = ['lnk', 'title', 'img', 'price', 'category'];
            foreach($Products as $product){
                $p = [];
                foreach($keys as $k){
                    if(isset($product->$k)){
                        $p[$k] = $product->$k;
                    }
                }
                $r[] = $p;
            }
            print_r(json_encode(['data' => $r, 'page' => $current_page, 'max_page' => $max_page]));
        } catch(Exception $e){
            http_response_code($e->getCode());
            die($e->getMessage());
        }



        die();
    }

    public function addAction(){
        $Product = new Product();
        $Product->title = $_POST['title'];
        $Product->lnk = preg_replace('#[^a-z0-9]#', '_', mb_strtolower($_POST['title'], 'utf-8'));
        $Product->category = $_POST['category'];
        $Product->price = (float) $_POST['price'];

        if($_POST['img']){
            $Image = new Image();
            $Image->base64 = base64_encode(file_get_contents($_POST['img']));
            $Image->rel_prod = $Product->lnk;
            $Image->lnk = $Product->lnk . '.' . preg_replace('#^\S+\.([^.]+)$#', '$1', $_POST['img']);
            $Image->save();
            $Product->img =  $Image->lnk;
        }

        $Product->save();
        die;
    }
    private function convertProduct($Product){
        $prod = [];
        $keys = ['lnk', 'title', 'img', 'price', 'category'];
        foreach($keys as $k){
            if(isset($Product->$k)){
                $prod[$k] = $Product->$k;
            }
        }
        return $prod;
    }

    public function productAction($key){
        try{
            if(!$key && $_POST['ids']){
                $products = Product::Find([['lnk' => ['$in' => $_POST['ids']]]]);
                $response = [];
                foreach($products as $Product){
                    $response[] = $this->convertProduct($Product);
                }
                die(json_encode(['data' => $response]));
            } else{
                $Product = Product::FindFirst([['lnk' => $key]]);
                if(!$Product){
                    throw new Exception('Wring product id', 400);
                }
                die(json_encode($this->convertProduct($Product)));
            }
        } catch(Exception $e){
            http_response_code($e->getCode());
            die($e->getMessage());
        }

    }

    public function cartAction($key){
        $Session = Session::getInstance();
        $request = new Phalcon\Http\Request();
        if($request->isPut() == true){
            $Session->increment($key);
        } elseif($request->isDelete() == true){
            $Session->delete($key);
        } elseif($request->isPost() == true && $request->getPost("clear")== true){
            $Session->cart = ['total' => 0];
            $Session->save();
        }
        $cart = $Session->getCart();
        $products = Product::Find([['lnk' => ['$in' => array_keys($cart)]]]);
        foreach($products as $k => $Prod){
            $products[$Prod->lnk] = $this->convertProduct($Prod);
            unset($products[$k]);
        }
        $total = 0;
        foreach($cart as $lnk => $count){
            $cart[$lnk] = [
                'title' => $products[$lnk]['title'],
                'count' => $count,
                'price' => $products[$lnk]['price']
            ];
            $total += $cart[$lnk]['price'] * $cart[$lnk]['count'];
        }
        die(json_encode(['cart' => $cart, 'total' => $total]));
    }

} 