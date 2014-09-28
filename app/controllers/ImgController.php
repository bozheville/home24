<?php


class ImgController extends ControllerBase {

    public function lnkAction($lnk){
        try{
            $img = Image::findFirst([['lnk' => $lnk]]);
            if(!$img){
                throw new Exception('File not found', 400);
            }
            $img = base64_decode($img->base64);
            header('Content-Type: image/jpeg');
            echo $img;
            die();
        } catch(Exception $e){
            http_response_code($e->getCode());
            die($e->getMessage());
        }
    }
} 