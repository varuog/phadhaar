<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phadhaar\Service;

use Phadhaar\Exception\AdhaarNotSupportedException;
use phpDocumentor\Reflection\Types\String_;
use Phadhaar\Model\User;
use src\Service\AdhaarAuthResponse;
use GuzzleHttp;

/**
 * Description of AdhaarAuthService
 *
 * @author gourav sarkar
 */
class AdhaarAuthService
{

    const API_VERSION_2 = '2.0';

    const TEST_ADHAAR_ENDPOINT = 'http://auth.uidai.gov.in';

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
    // put your code here
    protected $httpService;

    protected $config;

    /**
     *
     * @param \GuzzleHttp\Client $httpService
     * @param Object $config
     */
    public function __construct(\GuzzleHttp\Client $httpService, $config = [])
    {
        $this->httpService = $httpService;
    }

    /**
     *
     * @param \Phadhaar\Model\User $user
     */
    public function auth(AdhaarAuthRequest $adhaarRequest)
    {
        $endpoint = $adhaarRequest->generateEndpoint();
        var_dump($endpoint);

        $options = [];
        try {
            $response = $this->httpService->post($endpoint, [
                'body' => serialize($adhaarRequest),
                'headers' => [
                    'Content-Type' => 'applciation/xml'
                ]
            ]);
        } catch (\Exception $exp) {
            //var_dump($exp);
        }
        var_dump($response);
        die();
        return new AdhaarAuthResponse($response);
    }
}
