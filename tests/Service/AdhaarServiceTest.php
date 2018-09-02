<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace tests\Phadhaar\Service;

use PHPUnit\Framework\TestCase;
use Phadhaar\Service\AdhaarAuthService;
use GuzzleHttp\Client;
use Phadhaar\Model\User;
use Phadhaar\Service\AdhaarAuthRequest;


/**
 * Description of User
 *
 * @author gourav sarkar
 */
class AdhaarServiceTest extends TestCase
{

    /**
     *
     * @dataprovider userProvider
     */
    public function userProvider()
    {}

    public function testAdhaarServiceAuth()
    {
        $user = new User();
        $user->setEmail('sschoudhury@dummyemail.com');
        //$user->setGender(User::GENDER_MALE);
        //$user->setDob('13-05-1968');
        //$user->setName('Shivshankar Choudhury');
         $user->setAdhaarNumber('999941057058');
        
        $httpService=new Client();
        $adhaarRequest=new AdhaarAuthRequest($user);
        $adhaarService=new AdhaarAuthService($httpService);
        $response=$adhaarService->auth($adhaarRequest);
       // file_put_contents('outout.xml',$response);
      

        /**
         * <Auth uid="" rc="" tid="" ac="" sa="" ver="" txn="" lk="">
         * <Uses pi="" pa="" pfa="" bio="" bt="" pin="" otp=""/>
         * <Meta udc="" rdsId="" rdsVer="" dpId="" dc="" mi="" mc="" />
         * <Skey ci="">encrypted and encoded session key</Skey>
         * <Hmac>SHA-256 Hash of Pid block, encrypted and then encoded</Hmac>
         * <Data type="X|P">encrypted PID block</Data>
         * <Signature>Digital signature of AUA</Signature>
         * </Auth
         */
       
    }
}
