<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Phadhaar\Exception\AdhaarValidationException;

namespace Phadhaar\Model;

/**
 * Description of User
 *
 * @author gourav sarkar
 */
class User implements \Serializable {
    //put your code here

    /**
     * <Uses pi="" pa="" pfa="" bio="" bt="" pin="" otp=""/> 
     * <Pid ts="" ver="" wadh=””>
      <Demo lang="">
      <Pi ms="E|P" mv="" name="" lname="" lmv="" gender="M|F|T" dob=""
      dobt="V|D|A" age="" phone="" email=""/>
      <Pa ms="E" co="" house="" street="" lm="" loc=""
      vtc="" subdist="" dist="" state="" country="" pc="" po=""/>
      <Pfa ms="E|P" mv="" av="" lav="" lmv=""/>
      </Demo>
      <Bios dih="">
      <Bio type="FMR|FIR|IIR|FID" posh="" bs="">encoded biometric</Bio>
      </Bios>
      <Pv otp="" pin=""/>
     * 
      </Pid>
     * @var type 
     */
    const PID_VERSION_1 = '1.0';
    const PID_VERSION_2 = '2.0';

    /**
     * 
     */
    const SUPPORTED_LANGS = [
        'Assamese' => '01',
        'Bengali' => '02',
        'Gujarati' => '05',
        'Hindi' => '01',
        'Kannada' => '07',
        'Malayalam' => '11',
        'Manipuri' => '12',
        'Marathi' => '13',
        'Oriya' => '15',
        'Punjabi' => '16',
        'Tamil' => '20',
        'Telugu' => '21',
        'Urdu' => '22'];

    /**
     * 
     */
    const ADDRESS_FIELD = [
        'co',
        'house',
        'street',
        'lm',
        'loc',
        'vtc',
        'subdist',
        'dist',
        'state',
        'country',
        'pc',
        'po',
    ];

    protected $usesOtp = false;
    protected $usesPin = false;
    protected $usesBioType = false; //no
    protected $usesBio = false; //no
    protected $usesPersonalInfo = false;
    protected $usesPersonalAddress = false;
    protected $usesPersonalFullAddress = false;

    /**
     * Data
     * @var type 
     */
    protected $name;
    protected $dob;
    protected $dobt;
    protected $age;
    protected $phone;
    protected $email;
    protected $address;
    protected $gender;

    /**
     *
     * @var type 
     */
    protected $nameMatchValue; //default 100
    protected $nameMatchStrategy; //default ''E'
    protected $addressMatchStrategy = 'E'; //default E, E/P for full address
    protected $addressMatchValue; //default E, E/P for full address

    /**
     *
     * @var type 
     */
    protected $langAddressValue;
    protected $langAddressMatchValue;
    protected $langNameValue;
    protected $langNameMatchValue;

    /**
     *
     * @var type 
     */
    protected $lang;

    /**
     * 
     */
    protected $otp;
    protected $pin;
    protected $adhaarNumber;

    /**
     * PID
     */
    protected $ts;
    protected $wadh; //for ekyc
    protected $pidVersion; //for ekyc

    /**
     * 
     */
    protected $xmlWriter;

    public function __construct() {
        /*
         * YYYY-MM-DDThh:mm:ss
         */
        $this->ts = \Carbon\Carbon::now()->toIso8601String();
        $this->pidVersion = self::PID_VERSION_2;

        $this->xmlWriter = new \XMLWriter();
    }

    public function getTs() {
        return $this->ts;
    }

    /**
     * 
     * @param type $lang
     * @throws AdhaarValidationException
     */
    public function setLanguage($lang) {

        if (!in_array($lang, static::SUPPORTED_LANGS)) {

            throw new AdhaarValidationException('Unsupported Languages');
        }
        $this->lang = $lang;
    }

    /**
     * 
     * @param type $strategy E,P
     * @param type $value   1,100
     */
    public function setMatchingStrategy($strategy, $value) {
        $this->nameMatchStrategy = $strategy;
        $this->nameMatchValue = $value;
    }

    /**
     * 
     * @param type $name
     * @param type $matchValue
     * @throws AdhaarValidationException
     */
    public function setLocaleName($name, $matchValue) {
        if (!$this->lang) {
            throw new AdhaarValidationException('Language Must be set');
        }
        $this->personalIdentityField['lname'] = $name;
        $this->langNameValue = $name;
        $this->langNameMatchValue = $matchValue;

        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $gender M,F,T
     */
    public function setName($name) {
        $this->name = $name;
        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $gender M,F,T
     */
    public function setGender($gender) {
        $this->gender = $gender;
        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $dob YYYY, YYYY-MM-DD
     * @param type $dobt V,D,T
     */
    public function setDob($dob, $dobt, $onlyYear = false) {
        $carbondate = \Carbon\Carbon::parse($dob);
        if ($onlyYear) {
            $parsedDate = $carbondate->format('Y');
        } else {
            $parsedDate = $carbondate->format('Y-m-d');
        }

        $this->dob = $parsedDate;
        $this->dobt = $dobt;
        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $age
     */
    public function setAge($age) {
        $this->age = $age;
        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $email
     */
    public function setEmail($email) {
        $this->dob = $email;
        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $phone
     */
    public function setPhone($phone) {
        $this->phone = $phone;
        $this->usesPersonalInfo = true;
    }

    /**
     * 
     * @param type $address
     * @param type $matchValue
     * @throws AdhaarValidationException
     */
    public function setLocaleAddress($address, $matchValue) {
        if (!$this->lang) {
            throw new AdhaarValidationException('Language Must be set');
        }
        if (isset($this->address) && is_array($this->address)) {
            throw new AdhaarValidationException('Partial addres does not have locale address');
        }


        $this->langAddressValue = $address;
        $this->langAddressMatchValue = $matchValue;
        $this->usesPersonalFullAddress = true;
    }

    /**
     * Array is partial address
     * String means full address
     * @todo validation check
     * @param type $address
     */
    public function setAddress($address, $strategy = '', $value = '') {

        /**
         * split address only has exact match
         * full address has p/E and value
         */
        if (is_array($address)) {
            $this->value = $value;
            $this->addressMatchStrategy = 'E';
            $this->usesPersonalAddress = true;
        } else {
            $this->addressMatchStrategy = $strategy;
            $this->value = $value;
            $this->usesPersonalFullAddress = true;
        }

        $this->address = $address;
    }

    /**
     * 
     * @param type $otp
     */
    public function setOtp($otp) {
        $this->otp = $otp;
        $this->usesOtp = true;
    }

    /**
     * 
     * @param type $pin
     */
    public function setPin($pin) {
        $this->pin = $pin;
        $this->usesPin = true;
    }

    public function setAdhaarNumber($adhaarNum) {
        $this->adhaarNumber = $adhaarNum;
    }

    public function getAdhaarNumber() {
        return $this->adhaarNumber;
    }

    public function serialize() {
        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument("1.0");
        /*
         * Pid Block
         */
        $this->xmlWriter->startElement('Pid');
        $this->xmlWriter->startAttribute('ts');
        $this->xmlWriter->text($this->ts);
        $this->xmlWriter->endAttribute();

        $this->xmlWriter->startAttribute('ver');
        $this->xmlWriter->text($this->pidVersion);
        $this->xmlWriter->endAttribute();

        $this->xmlWriter->startAttribute('wadh');
        $this->xmlWriter->text($this->ts);
        $this->xmlWriter->endAttribute();

        /*
         * Demo Block
         */
        $this->xmlWriter->startElement('Demo');
        $this->xmlWriter->startAttribute('lang');
        $this->xmlWriter->text($this->lang);
        $this->xmlWriter->endAttribute();

        /*
         * PI block self colsed
         */
        $this->xmlWriter->startElement('Demo');
        $this->xmlWriter->startAttribute('ms');
        $this->xmlWriter->text($this->nameMatchStrategy);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('mv');
        $this->xmlWriter->text($this->nameMatchValue);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('name');
        $this->xmlWriter->text($this->name);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('lname');
        $this->xmlWriter->text($this->langNameValue);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('lmv');
        $this->xmlWriter->text($this->langNameMatchValue);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('gender');
        $this->xmlWriter->text($this->gender);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('dob');
        $this->xmlWriter->text($this->dob);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('dobt');
        $this->xmlWriter->text($this->dobt);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('age');
        $this->xmlWriter->text($this->age);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('phone');
        $this->xmlWriter->text($this->phone);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('email');
        $this->xmlWriter->text($this->email);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->endElementdElement();

        /*
         * Address block Pa,Pfa
         */
        if (is_array($this->address)) {
            $this->xmlWriter->startElement('Pa');
            $this->xmlWriter->startAttribute('ms');
            $this->xmlWriter->text($this->addressMatchStrategy);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('co');
            $this->xmlWriter->text($this->address['co']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('house');
            $this->xmlWriter->text($this->address['house']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('street');
            $this->xmlWriter->text($this->addres['street']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('lm');
            $this->xmlWriter->text($this->address['lm']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('loc');
            $this->xmlWriter->text($this->address['loc']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('vtc');
            $this->xmlWriter->text($this->address['vtc']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('subdist');
            $this->xmlWriter->text($this->address['subdist']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('dist');
            $this->xmlWriter->text($this->address['dist']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('state');
            $this->xmlWriter->text($this->address['state']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('country');
            $this->xmlWriter->text($this->address['country']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('pc');
            $this->xmlWriter->text($this->address['pc']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('po');
            $this->xmlWriter->text($this->address['po']);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->endElementdElement();
        } else {
            $this->xmlWriter->startElement('Pfa');
            $this->xmlWriter->startAttribute('ms');
            $this->xmlWriter->text($this->addressMatchStrategy);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('mv');
            $this->xmlWriter->text($this->addressMatchValue);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('av');
            $this->xmlWriter->text($this->address);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->startAttribute('lav');
            $this->xmlWriter->text($this->langAddressValue);
            $this->xmlWriter->endAttribute();
            $this->xmlWriter->endElementdElement();
        }


        /*
         * Otp/pin block
         */
        $this->xmlWriter->startElement('Pv');
        $this->xmlWriter->startAttribute('otp');
        $this->xmlWriter->text($this->otp);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->startAttribute('pin');
        $this->xmlWriter->text($this->pin);
        $this->xmlWriter->endAttribute();
        $this->xmlWriter->endElementdElement();


        /*
         * Demo end
         */
        $this->xmlWriter->endElementdElement();
        /*
         * Pid End
         */
        $this->xmlWriter->endElementdElement();


        echo $this->xmlWriter->outputMemory();
    }

    public function unserialize(string $serialized): void {
        throw new Exception('Not supported');
    }

}
