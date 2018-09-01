# phadhaar


# Sample Code
```PHP
$user = new User();
$user->setEmail('sschoudhury@dummyemail.com');
$user->setGender(User::GENDER_MALE);
$user->setDob('13-05-1968');
$user->setName('Shivshankar Choudhury');
$user->setAdhaarNumber('999941057058');
        
$httpService=new Client();
$adhaarRequest=new AdhaarAuthRequest($user);
$adhaarService=new AdhaarAuthService($httpService);
$response=$adhaarService->auth($adhaarRequest);
```