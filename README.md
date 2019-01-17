**Laravel Api Starter With Voyager and Dingo**

laravel version : 5.7

This is the api **starter pack** I use for development. 
This app already has API authentication routes created and I can also use the voyager part to manage the app. 



**Available End points:**
  - post /api/auth/login
  - post /api/auth/sign-up
  - post /api/auth/send-password-reset-link
  - post /api/auth/profile-update
  - get /api/auth/user
  - delete /api/auth/invalidate
  - post /api/auth/update-password
  - patch /api/auth/refresh
  - get /api/auth/user-types
    
_**How to Use**_

- pull the repo to your local machine
- run `composer install` to install dependencies
-  copy the .env.example to .env and change the variables you want to change
- run `php artisan key:generate` to generate your app key 
- run `php artisan jwt:secret` to generate your jwt key 
-  run `  php artisan migrate` to add the voyager, api_logs and the users tables
- run `composer run-script swagger-generate` to generate the apidocs.json file for the swagger documentation 
- go to `public/swagger/index.html` and update the url to point to the apidocs.json
- visit `http://{base_web_address}/swagger` to view and test your endpoints
- visit `http://{base_web_address}/admin` to access voyager
- The End.

**Configuration**

- copy the .env.example to .env
- update the database credentials in .env
- The setup sends email out when there's a server error with status 500 on the `problemResponse()` method used for sending error responses to users.
      
     To enable the email sending, update SEND_EMAIL_ON_500_ERR to true in your .env file
 
_if email sending is enabled_

     update the 500_ERR_EMAIL_RECIPIENT to your desired reciepient

**Added Packages**
- Dingo https://github.com/dingo/api/wiki 
- Swagger  https://swagger.io/
- Migrations Generator   https://github.com/Xethron/migrations-generator
- Models Generator     https://packagist.org/packages/reliese/laravel
- Voyager     https://docs.laravelvoyager.com/getting-started/installation

**Model directory**
 - app/Models
 
**Model Namespace**
 - App\Models
 
  
**Voyager User Model Namespace**
 - App\Models
 
 
 
**Auto Loaded files**

- app/Helpers/app_functions.php
- app/Transformers/*
- app/Classes/*

 

**Swagger documentation assets** 
- public/swagger

**Swagger app documentation URL**
- {base_address}/swagger



 _**Every other thing is as is.**_
 
 
 Licence: MIT