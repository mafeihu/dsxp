<?php
//namespace app\api\controller;
//use lib\Easemob;
//use lib\Upload;
//use think\Controller;
//use think\View;
//use think\Db;
//use opensearch;
//use \think\Session;
//use \think\Request;
//
//class Login extends Common
//{
//    private $cdkey = "8SDK-EMY-6699-RHRPN";
//    private $password = "647039";
//    public function _initialize()
//    {
//        parent::_initialize(); // TODO: Change the autogenerated stub
//    }
//    /**
//     * 发送验证码
//     */
//    public function sendSMS($mobile="18222169593"){
//        $mobile = Request::instance()->request('mobile');
//        if (empty($mobile) || !preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}|^17[0-9]\d{8}$#', $mobile)) {
//            error("手机格式不正确");
//        }else{
//            //一分钟只能发送一条
//            $intime = DB::name("Mobile_sms")->field(["intime"])->where(["mobile"=>$mobile])->order('intime desc')->find();;
//            $mistiming = time()-intval($intime["intime"]);
//            if($mistiming<60){
//                error("一分钟只能发送一条短信");
//            }
//            //每天只能发送10条
//            $send_count = DB::name("mobile_sms")->where(["mobile"=>$mobile,"date"=>date("Y-m-d")])->count();
//            if($send_count>10) {
//                error("今天短信发送数量已达上限");
//            }
//            $system = DB::name('system')->field('tengxun_appid,tengxun_appkey,code_volidity')->where(['id'=>1])->find();
//            //$date = DB::name("member")->where(array('phone'=>$mobile))->find();
//            $mobile_code = random(6, 1);
//            $content = "【龙脉直播】您的验证码为".$mobile_code.",如非本人操作请忽略此消息。";
//        }
//        $url='http://hprpt2.eucp.b2m.cn:8080/sdkproxy/sendsms.action?cdkey='.$this->cdkey.'&password='.$this->password.'&phone='.$mobile.'&message='.$content;
//        file_get_contents($url);
//        DB::name('Mobile_sms')->insert(['mobile'=>$mobile,'code'=>$mobile_code,'state'=>1,'date'=>date('Y-m-d',time()),'intime'=>time()]);
//        success('发送成功!');
//        }
//    /**
//     * 注册和登录
//     */
//    public function message_login(){
//        $param = Request::instance()->request();
//        $log = empty($param["log"]) ? '116.42669': $param["log"];
//        $lag = empty($param["lag"]) ? '39.917149': $param["lag"];
//        if(empty($param["mobile"]) || !(preg_match("/^1[34578]{1}\d{9}$/",$param["mobile"])) || empty($param["yzm"])){
//            error("验证不正确");
//        }
//        $mobile = $param["mobile"];
//        //判断验证码是否有效期
//        $result = DB::name("Mobile_sms")->where(["mobile"=>$mobile,"code"=>$param["yzm"]])->order("intime desc")->find();
//        if(!$result){
//            error("验证码不正确");
//        }
//        $state = $result["state"];
//        $valid_time =time()-intval($result["intime"]);
//        if($valid_time>600 || $state==2){
//            error("验证码已失效,请重新发送");
//        }
//        /**
//         * 用户定位
//         */
//        if($log && $lag){
//            $gwd = $lag.','.$log;
//            $baidu_apikey =DB::name('system')->where("")->value('baidu_apikey');
//            $file_contents = file_get_contents('http://api.map.baidu.com/geocoder/v2/?ak='.$baidu_apikey.'&location='.$gwd.'&output=json');
//            $rs = json_decode($file_contents,true);
//            $sheng = $rs['result']['addressComponent']['province'];
//            $shi = $rs['result']['addressComponent']['city'];
//            $qu = $rs['result']['addressComponent']['district'];
//            $address = $rs['result']['formatted_address'];
//        }
//        $user = DB::name("member")->where(["phone"=>$mobile])->find();
//        if($user){
//            //用户存在的时候
//            if($user['is_del']==2){
//                error('账号被限制,请联系平台!');
//            }else{
//                $member_token = uniqid();
//                $user["app_token"] = $member_token;
//                $user["header_img"] = $user["header_img"];
//                DB::name("member")->where(["member_id"=>$user["member_id"]])->update(["app_token"=>$member_token,'log'=>$log,'lag'=>$lag]);
//                DB::name("mobile_sms")->where(["mobile_sms_id"=>$result['mobile_sms_id']])->update(["state"=>2]);
//                success($user);
//            }
//        }else{
//            //用户不存在的时候
//            $photo =  $photo = "/Public/admin/touxiang.png";
//            $chars = "abcdefghijklmnopqrstuvwxyz123456789";
//            mt_srand(10000000*(double)microtime());
//            for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < 12; $i++){
//                $str .= $chars[mt_rand(0, $lc)];
//            }
//            $photo = "/Public/admin/touxiang.png";
//            $hx_password="123456";
//            $data = [
//                'app_token'=>uniqid(),
//                'phone'=>$mobile,
//                'uuid'=>get_guid(),
//                'header_img'=>$photo,
//                'username'=>"游荡者",
//                'ID'=>get_number(),
//                'intime'=>time(),
//                'alias'=>$str,
//                'sex'=>1,
//                'signature'=>"这个人很懒什么都没有留下！！",
//                'hx_username'=>$str,
//                'hx_password'=>$hx_password,
//                'province'=>$sheng,
//                'city'=>$shi,
//                'area'=>$qu,
//                'address'=>$address,
//            ];
//            if ($member_id=DB::name('member')->insertGetId($data)){
//                //验证成功进行状态修改
//                DB::name("mobile_sms")->where(["mobile_sms_id"=>$result['mobile_sms_id']])->update(["state"=>2]);
//                $hx = new Easemob();
//                $result = $hx->huanxin_zhuce($str,$hx_password); //环信注册
//                if(!$result){
//                    error("授权注册失败");
//                }
//                $user = DB::name('member')->where(['member_id'=>$member_id])->find();
//                $user['img'] = $user["header_img"];
//                success($user);
//            }else {
//                error('失败!');
//            }
//        }
//    }
//    /**
//     * @第三方登陆（微信，qq）
//     * @state 1:微信  2：qq    3:微博
//     */
////    public function third_login(){
////        $user = $this->checklogin();
////        $params = Request::instance()->request();
////        $log = empty($params["log"]) ? '116.42669': $params["log"];
////        $lag = empty($params["lag"]) ? '39.917149': $params["lag"];
////        $openid = empty($params['openid']) ? error("无法获取用户信息") : $params["openid"];
////        $state =empty($params['state']) ? error("参数错误") : $params["state"];
////        if(!in_array($state,array(1,2,3))){
////            error("参数不符合要求");
////        }
////        switch ($state){
////            case 1:$data['openid'] = $openid;break;
////            case 2:$data['qq_openid'] = $openid;break;
////            case 3:$data['weibo'] = $openid;break;
////        }
////        /**
////         * 用户定位
////         */
////        if($log && $lag){
////            $gwd = $lag.','.$log;
////            $baidu_apikey =DB::name('system')->where(["id"=>1])->value('baidu_apikey');
////            $file_contents = file_get_contents('http://api.map.baidu.com/geocoder/v2/?ak='.$baidu_apikey.'&location='.$gwd.'&output=json');
////            $rs = json_decode($file_contents,true);
////            $sheng = $rs['result']['addressComponent']['province'];
////            $shi = $rs['result']['addressComponent']['city'];
////            $qu = $rs['result']['addressComponent']['district'];
////            $address = $rs['result']['formatted_address'];
////        }
////        /**
////         * 用户判断
////         */
////        $user = DB::name('member')->where($data)->find();
////        if ($user){
////            //用户存在的时候
////            if($user['is_del']==2){
////                error('账号被限制,请联系平台!');
////            }else{
////                $member_token = uniqid();
////                $user["app_token"] = $member_token;
////                $user["header_img"] = $user["header_img"];
////                DB::name("member")->where(["member_id"=>$user["member_id"]])->update(["app_token"=>$member_token,'log'=>$log,'lag'=>$lag]);
////                success($user);
////            }
////        }else{
////            //用户不存在的时候
////            switch ($state){
////                case 1:$data['openid'] = $openid;break;
////                case 2:$data['qq_openid'] = $openid;break;
////                case 3:$data['weibo'] = $openid;break;
////            }
////            //第三方登录的相关信息
////            $header_img =  empty($params["header_img"]) ?  "/Public/admin/touxiang.png" : $params["header_img"];
////            $sex = empty($params["sex"]) ? 0 : $params["sex"];
////            $username = empty($params["username"]) ? "游荡者" : $params["username"];
////            $chars = "abcdefghijklmnopqrstuvwxyz123456789";
////            mt_srand(10000000*(double)microtime());
////            for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < 12; $i++){
////                $str .= $chars[mt_rand(0, $lc)];
////            }
////            $hx_password="123456";
////            $data = [
////                'app_token'=>uniqid(),
////                'uuid'=>get_guid(),
////                'header_img'=>$header_img,
////                'username'=>$username,
////                'ID'=>get_number(),
////                'intime'=>time(),
////                'alias'=>$str,
////                'sex'=>$sex,
////                'signature'=>"这个人很懒什么都没有留下！！",
////                'hx_username'=>$str,
////                'hx_password'=>$hx_password,
////                'province'=>$sheng,
////                'city'=>$shi,
////                'area'=>$qu,
////                'address'=>$address,
////            ];
////            if ($member_id=DB::name('member')->insertGetId($data)){
////                $hx = new Easemob();
////                $result = $hx->huanxin_zhuce($str,$hx_password); //环信注册
////                if(!$result){
////                    error("授权注册失败");
////                }
////                $user = DB::name('member')->where(['member_id'=>$member_id])->find();
////                $user['img'] = $user["header_img"];
////                success($user);
////            }else {
////                error('失败!');
////            }
////        }
////    }
//    /**
//     *图片上传
//     */
//    public function upload(){
//        $up = new Upload();
//        $up->upload();
//    }
//}
