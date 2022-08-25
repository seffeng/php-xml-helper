## PHP Helpers

### 安装

```shell
# 安装
$ composer require seffeng/xml-helper
```

### 目录说明

```
|---src
|   |   Xml.php
|---tests
|       XmlTest.php
```

### 示例

```php
/**
 * TestController.php
 * 示例
 */
namespace App\Http\Controllers;

use Seffeng\XmlHelper\Xml;

class TestController extends Controller
{
    public function index()
    {
        $value = ['a' => ['id' => 1,'name' => 'aaa']];
        var_dump(Xml::toXml($value));
    }
}
```

### 备注

1、更多示例请参考 tests 目录下测试文件。