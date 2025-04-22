# dynx

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
