<?php
/**
 * 老王
 *
 **/

namespace app\common\auth;
use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\Signer\Hmac\Sha256;
use \Lcobucci\JWT\Parser;
use \Lcobucci\JWT\ValidationData;
/**
 * 单例模式
 */
class JWTAuth
{
    private static $instance;
    /**
     * JWT TOKEN
     * @var [type]
     */
    private $token;
    /**
     * 颁发
     * @var string
     */
    private $iss = 'tudin.test';
    /**
     * 接收
     * @var string
     */
    private $aud = 'tudin.test';

    private $uid;

    private $secrect="#$%#$%*&^(*(*(";

    private $decodeToken;

    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __contruct(){

    }

    private function __clone(){

    }

    public function encode(){
        $time = time();
        $this->token = (new builder())->setHeader('alg','HS256')
            ->setIssuer($this->iss)
            ->setAudience($this->aud)
            ->setIssuedAt($time) //生效时间
            ->setExpiration($time + 20)//过期时间
            ->set('uid',$this->uid)
            ->sign(new Sha256(), $this->secrect)//加密算法
            ->getToken();

        return $this;
    }

    public function getToken(){
        return (string)$this->token;
    }

    public function setToken($token){
        $this->token = $token;
        return $this;
    }
    /**
     * 用户信息uid
     * @param [type] $uid [description]
     */
    public function setUid($uid){
        $this->uid = $uid;
        return $this;
    }

    public function jsonDecode(){

        $token = $this->token;
        $this->decodeToken = (new Parser())->parse((string) $token);

        // echo $this->decodeToken->getClaim('uid');
        return $this->decodeToken;
    }
    /**
     * 验证令牌是否有效
     * @return [type] [description]
     */
    public function validate(){

        $data = new ValidationData();
        $data->setIssuer($this->iss);
        $data->setAudience($this->aud);
        return $this->jsonDecode()->validate($data);

    }
    /**
     * 签名来验证令牌在生成后是否未被修改
     * @return [type] [description]
     */
    public function verify(){
        $result = $this->jsonDecode()->verify(new Sha256(), $this->secrect);
        return $result;
    }

}