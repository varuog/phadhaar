# phadhaar

##Description
Adhaar Authentication for API 2.5 using PHP

## Installation

Add the Laravel Facebook SDK package to your `composer.json` file.

    composer require varuog/phadhaar


## Sample Code
```PHP
$adhaarNumber='999941057058';
$user = new User($adhaarNumber);
$user->setEmail('sschoudhury@dummyemail.com');
$user->setGender(User::GENDER_MALE);
$user->setDob('13-05-1968');
$user->setName('Shivshankar Choudhury');
        
$httpService=new Client();
$adhaarRequest=new AdhaarAuthRequest($user);
$adhaarService=new AdhaarAuthService($httpService);
$response=$adhaarService->auth($adhaarRequest);
```