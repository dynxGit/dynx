# dynx
Extended User Manager

  Under constrution
## Planned services & solution

### Frontend elements
* **Registration**
  * Simple registration form (name and email address only)
  * Numerical Token confirmation via email (token is configurable default: 3 letter-4 number DYN-2234)
  * Initial password setup (see: Change/ edit password)
  * Initial profile setup (see: Profile)
* **Login**
  *  Login form with email & password
  *  Login via social (OAUTH etc.)
*  **Recovery** 
    *  Form for e-mail address
    *  Send email with Numerical Token if user exists
    *  Password setup form
*  **Change/edit password**
  * password form vith validation (configurable the neccessary length, letter and characters)
    * Letter Lowercase
    * Letter Uppercase
    * Numbers
    * Special Characters   
  * Client side validation is visually displayed 
* **Public profile**
  * Default profile fields
    * Nickname
    * Phone (default)
    * Avatar
    * Introdution text
  * Extra (Configurable) profile fields
* **Account (logged user)**
  * Account type and comparison
  * Payment status/process
  * Messages /notifcation about account
### Backend 
* User management
* RBAC
* Account type and services
* Billing

* 
## Configuration
In config/web.php
```
'bootstrap' => [
 ...
 'dynx'
],
'aliases' => [
   ...
   '@dynx'   => '@vendor/dynx/dynx',
  
 ]
...
'modules' => [
  ...
     'dynx' => [
        'class' => 'dynx\Module',
     ],
],
'components' =>[
...
 'user' => [
    'class' => 'dynx\components\DyWebUser',
    'enableAutoLogin' => true,
 ],
 'authManager' => [
     'class' => 'yii\rbac\DbManager',
 ],
]
```

## Avatar
Avatar's is located in `@webroot/images/avatar`folder. Create folders if not exists, and chmod/chown properly.

If you want to generate letter based avatar (when no image is uploaded) copy *index.php* from `@dynx/assets/avatar` to images/avatar folder.
