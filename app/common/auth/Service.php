<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/7/16
 * Time: 11:32
 */

namespace app\common\auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class Service
{
    /**
     * 配置秘钥加密
     * @return Configuration
     */
    public static function getConfig()
    {
        $configuration = Configuration::forSymmetricSigner(
        // You may use any HMAC variations (256, 384, and 512)
            new Sha256(),
            // replace the value below with a key of your own!
            InMemory::base64Encoded('YWFhc0pOU0RLSkJITktKU0RiamhrMTJiM2Joa2ox')
        // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );
        return $configuration;
    }

    /**
     * 签发令牌
     */
    public static function createToken()
    {
        $config = self::getConfig();
        assert($config instanceof Configuration);

        $now = new DateTimeImmutable();

        $token = $config->builder()
            // 签发人
            ->issuedBy('http://example.com')
            // 受众
            ->permittedFor('http://example.org')
            // JWT ID 编号 唯一标识
            ->identifiedBy('123')
            // 签发时间
            ->issuedAt($now)
            // 在1分钟后才可使用
//            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            // 过期时间1小时
            ->expiresAt($now->modify('+1 hour'))
            // 自定义uid 额外参数
            ->withClaim('uid', '大手大脚奥斯卡的话')
            // 自定义header 参数
            ->withHeader('foo', 'bar')
            // 生成token
            ->getToken($config->signer(), $config->signingKey());

        //result:
        //eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImZvbyI6ImJhciJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLmNvbSIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUub3JnIiwianRpIjoiNGYxZzIzYTEyYWEiLCJpYXQiOjE2MDk0Mjk3MjMsIm5iZiI6MTYwOTQyOTc4MywiZXhwIjoxNjA5NDMzMzIzLCJ1aWQiOjF9.o4uLWzZjk-GJgrxgirypHhXKkMMUEeL7z7rmvmW9Mnw
        //base64 decode:
        //{"typ":"JWT","alg":"HS256","foo":"bar"}{"iss":"http:\/\/example.com","aud":"http:\/\/example.org","jti":"4f1g23a12aa","iat":1609429723,"nbf":1609429783,"exp":1609433323,"uid":1}[6cb`"*Gr0ńxoL

        return $token->toString();
    }

    /**
     * 解析令牌
     */
    public static function parseToken(string $token)
    {
        $config = self::getConfig();
        assert($config instanceof Configuration);

        $token = $config->parser()->parse($token);

        assert($token instanceof Plain);
        return $token->claims();
//        dump($token->headers()); // Retrieves the token headers
//        dump($token->claims()); // Retrieves the token claims
    }

    /**
     * 验证令牌
     */
    public static function validationToken(string $token)
    {
        $config = self::getConfig();

        assert($config instanceof Configuration);

        $token = $config->parser()->parse($token);


        assert($token instanceof Plain);

//Lcobucci\JWT\Validation\Constraint\IdentifiedBy: 验证jwt id是否匹配
//Lcobucci\JWT\Validation\Constraint\IssuedBy: 验证签发人参数是否匹配
//Lcobucci\JWT\Validation\Constraint\PermittedFor: 验证受众人参数是否匹配
//Lcobucci\JWT\Validation\Constraint\RelatedTo: 验证自定义cliam参数是否匹配
//Lcobucci\JWT\Validation\Constraint\SignedWith: 验证令牌是否已使用预期的签名者和密钥签名
//Lcobucci\JWT\Validation\Constraint\ValidAt: 验证要求iat，nbf和exp（支持余地配置）

        //验证jwt id是否匹配
        $validate_jwt_id = new \Lcobucci\JWT\Validation\Constraint\IdentifiedBy('11111');
        $config->setValidationConstraints($validate_jwt_id);

        dump($validate_jwt_id);
        //验证签发人url是否正确
        $validate_issued = new \Lcobucci\JWT\Validation\Constraint\IssuedBy('http://example.com');
        $config->setValidationConstraints($validate_issued);
        //验证客户端url是否匹配
        $validate_aud = new \Lcobucci\JWT\Validation\Constraint\PermittedFor('http://example.org');
        $config->setValidationConstraints($validate_aud);

        //验证是否过期
        $timezone = new \DateTimeZone('Asia/Shanghai');
        $now = new \Lcobucci\Clock\SystemClock($timezone);
        $validate_jwt_at = new \Lcobucci\JWT\Validation\Constraint\ValidAt($now);
        $config->setValidationConstraints($validate_jwt_at);

        $constraints = $config->validationConstraints();

        try {
         return   $config->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            // list of constraints violation exceptions:
            return $e->violations();
        }
    }

}