# dynx

## Configuration
In config/web.php
```
 "aliases" => [
   ...
   "@dynx"   => "@vendor/dynx/dynx",
  
 ]
...
"modules" => [
  ...
     "dynx" => [
        "class" => "dynx\Module",
     ],
],
```
