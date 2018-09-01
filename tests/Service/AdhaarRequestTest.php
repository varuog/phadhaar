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
class AdhaarRequestTest extends TestCase
{

    /**
     *
     * @dataprovider userProvider
     */
    public function userProvider()
    {}

    /**
     * 
     */
    public function testSessionKey()
    {
        $user = new User();
        $user->setEmail('sschoudhury@dummyemail.com');
        $user->setGender(User::GENDER_MALE);
        $user->setDob('13-05-1968');
        $user->setName('Shivshankar Choudhury');
        $user->setAdhaarNumber('999941057058');
        
        $adhaar=new AdhaarAuthRequest($user);
        $payload=$adhaar->sign();
        //var_dump(serialize($adhaar), 1);
        //for debug
        file_put_contents('test.xml',$payload); 
        //$this->assertEquals($payload, 1);
        
        
    }
}
