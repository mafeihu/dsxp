<?php
namespace lib;
use \think\File;
use \think\request;
use \think\Validate;
class Upload extends Validate{
    /**
     * 多文件上传和单文件上传
     */
    public function upload(){
        $files = request()->file('img');
        $images = array();
        if(empty($files)){
            error("上传文件不能空");
        }
        foreach($files as $file){
//            //宽高验证
//            $imageInfo = $file->getInfo();
//            $imagesize = getimagesize($imageInfo['tmp_name']);
//            if($imagesize[0] > 1002){
//                error('请选择宽度不超过<b>1002px</b>的JPG图片...');
//            }
//            if($imagesize[1] > 2500){
//                error('请选择高度不超过<b>2000px</b>的JPG图片...');
//            }
            //移动到框架应用根目录/public/uploads/ 目录下f
            $info = $file->validate(
                        ['size'=>2000000,'ext'=>'png,jpg,jpeg，gif','mine'=>"image"]
                        )->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                array_push($images,config('domain').'/uploads/'.$info->getSaveName());
            }else{
                error( $file->getError());
            }
        }
        if(count($images)==1){
            success($images[0]);
        }else{
            success($images);
        }

    }
}
