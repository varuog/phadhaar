<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Phadhaar\Service;

use Phadhaar\Exception\AdhaarNotSupportedException;

/**
 * Description of AdhaarAuthService
 *
 * @author gourav sarkar
 */
class AdhaarAuthService {

    const API_VERSION_2 = '2.0';
    const TEST_ADHAAR_ENDPOINT = 'http://auth.uidai.gov.in';
    const TEST_ASA_LICENSE_KEY = 'MMxNu7a6589B5x5RahDW-zNP7rhGbZb5HsTRwbi-VVNxkoFmkHGmYKM';
    const TEST_AUA_LICENSE_KEY = 'MBni88mRNM18dKdiVyDYCuddwXEQpl68dZAGBQ2nsOlGMzC9DkOVL5s';
    const TEST_AUA_CODE = 'public';
    const TEST_ASA_CODE = 'public';

    /**
     * <Auth uid="" rc="" tid="" ac="" sa="" ver="" txn="" lk="">
      <Uses pi="" pa="" pfa="" bio="" bt="" pin="" otp=""/>
      <Meta udc="" rdsId="" rdsVer="" dpId="" dc="" mi="" mc="" />
      <Skey ci="">encrypted and encoded session key</Skey>
      <Hmac>SHA-256 Hash of Pid block, encrypted and then encoded</Hmac>
      <Data type="X|P">encrypted PID block</Data>
      <Signature>Digital signature of AUA</Signature>
      </Auth
     */
    //put your code here
    protected $httpService;
    protected $config;
    protected $user;
    protected $enableProtoBuf = false; //X/P

    /**
     *
     * @var type 
     */
    protected $consent = 'Y';
    protected $tid = '';
    protected $auaLicenseKey = self::TEST_AUA_LICENSE_KEY;
    protected $auaCode = self::TEST_AUA_CODE;
    protected $asaCode = self::TEST_ASA_CODE;
    protected $apiVersion = self::API_VERSION_2;
    protected $transactionId;
    protected $certificatePath = "../Storage/uidai_auth_stage.cer";

    /*
     * Skey
     */
    protected $certificateExpiryDate;

    /**
     * Hmac
     */
    protected $hmac;

    /**
     * Session Key
     */
    protected $session;
    protected $sessionKey;

    /**
     * Other meta data is bio dependent
     */
    protected $metaUdc;
    protected $pid;

    /**
     * 
     * @param \GuzzleHttp\Client $httpService
     * @param type $config
     */
    public function __construct(\GuzzleHttp\Client $httpService, $config) {
        $this->httpService = $httpService;
    }

    protected function generateMetaUdc() {
        $bytes = random_bytes(20);
        $this->metaUdc = bin2hex($bytes);
    }

    protected function generateTransactionId() {
        $bytes = random_bytes(50);
        $this->transactionId = bin2hex($bytes);
    }

    protected function generateCertificateExpiry() {
        $certinfo = openssl_x509_parse(file_get_contents($this->certificatePath));
        $this->certificateExpiryDate = \Carbon\Carbon::parse($certinfo['validTo_time_t'])->format('Ymd');
    }

    /**
     * 
     */
    protected function generateSessionKey() {
        /*
         * Generate 256 bit session key
         */
        $this->session = openssl_random_pseudo_bytes(256, true);

        $crypted = '';
        $publicKeyRes = openssl_pkey_get_public(file_get_contents('../Storage/uidai_auth_stage.cer'));
        $publicKeyDetails = openssl_pkey_get_details($publicKeyRes); //openssl_pkey_get_details

        openssl_public_encrypt($this->session, $crypted, $publicKeyDetails['key']);
        $this->sessionKey = base64_encode($crypted);
    }

    protected function generateHmac(\Phadhaar\Model\User $user) {
        if (!$this->enableProtoBuf) {
            $xmlPid = serialize($user);
            $hashXmlPid = hash($xmlPid, 'sha256');
            //encrypt
            $encryptedXmlPid = $hashXmlPid;
            $this->hmac = base64_encode($encryptedXmlPid);
            return;
        }

        throw new AdhaarNotSupportedException('ProtoBuf Not supported yet');
    }

    protected function generatePidBlock(\Phadhaar\Model\User $user) {
        if (!in_array('aes-256-gcm', openssl_get_cipher_methods())) {
            throw new AdhaarNotSupportedException('aes-256-gcm support is needed');
        }

        if (!$this->enableProtoBuf) {
            $xmlPid = serialize($user);
            //encrypt
            $iv=$user->getTs();
            openssl_encrypt($xmlPid, 'aes-256-gcm',  $this->session, OPENSSL_ZERO_PADDING, $iv);
            $encryptedXmlPid = $xmlPid;
            $this->pid = base64_encode($encryptedXmlPid);
            return;
        }

        throw new AdhaarNotSupportedException('ProtoBuf Not supported yet');
    }

    /**
     * 
     * @return type
     */
    protected function generateEndpoint(Phadhaar\Model\User $user) {
        $endpoint = sprintf('%s/%s/%s/%s/%s/%s'
                , self::TEST_ADHAAR_ENDPOINT
                , self::API_VERSION_2
                , 'public'
                , $user->getAdhaarNumber()[0]
                , $user->getAdhaarNumber()[1]
                , urlencode(self::TEST_ASA_LICENSE_KEY)
        );

        return $endpoint;
    }

    /**
     * 
     * @param \Phadhaar\Model\User $user
     */
    public function auth(\Phadhaar\Model\User $user) {
        $this->generateTransactionId();
        $this->generateMetaUdc();
        $this->generateCertificateExpiry();
        $this->generateSessionKey();
        $endpoint = $this->generateEndpoint($user);

        $options = [];
        $this->httpService->post($endpoint, $options);
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }

    public function serailize() {
        
    }

}
