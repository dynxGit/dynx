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

## Avatar
Avatar's is located in `@webroot/images/avatar`folder. Create folders if not exists, and chmod/chown properly.

If you want to generate letter based avatar (when no image is uploaded) copy *index.php* from `@dynx/assets/avatar` to images/avatar folder.
