<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace tests\Phadhaar\Model;

use PHPUnit\Framework\TestCase;
use Phadhaar\Model\User;

/**
 * Description of User
 *
 * @author gourav sarkar
 */
class UserTest extends TestCase
{

    /**
     *
     * @dataprovider userProvider
     */
    public function userProvider()
    {}

    public function testUserWithDemography()
    {
        $user = new User('999941057058');
        $user->setEmail('sschoudhury@dummyemail.com');
        $user->setGender(User::GENDER_MALE);
        $user->setDob('13-05-1968');
        $user->setName('Shivshankar Choudhury');
        //$user->setAdhaarNumber('999941057058');

       // $this->assertEquals(1, serialize($user));
    }
}
