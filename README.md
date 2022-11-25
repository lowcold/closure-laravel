<br>开发者：lowcold
<br>邮箱：79240950@qq.com
<br>ps:如果无法安装，请不要使用国内镜像，国内镜像同步时间未知

# 安装

```
composer require lowcold/closure-table
```

# 修改关联模型

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use lowcold\ClosureTable\ClosureTable;

class Member
{
    use ClosureTable, SoftDeletes, HasFactory;

    // 定义上级字段
    const parent = 'parent';
    // 定义关联表
    const closure = 'member_closure';
}

```

# 获取数据的方法示例

```php
<?php
$member = Member::find(3);
  
// 获取所有后代
$member->getDescendants()->get()->toArray();
  
// 获取所有后代，包括自己
$member->getDescendantsAndOwn()->get()->toArray();
 
 // 获取所有祖先
$member->getAncestor()->get()->toArray();
  
// 获取所有祖先，包括自己
$member->getAncestorAndOwn()->get()->toArray();

// 使用排序
$member->getDescendants()->orderBy('member_closure.distance','desc')->get()->toArray();
```

# 移动

```php
// 把10移动到5
Member::find(10)->move(Member::find(5));
```

```
# 设置为顶级
```php
Member::find(10)->setRoot();
```

## 注意，此处为严重的个人习惯

如果使用了表前缀，请在`.env`中添加`DB_PREFIX=xxxx_`

以及把`config/database.php`中的数组mysql的`'prefix' => ''`改为`'prefix' => env('DB_PREFIX')`
