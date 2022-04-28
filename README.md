<p align="center"><a href="#" target="_blank"><img src="https://github.com/Homeeat/Pocoapoco/blob/main/src/Image/Pocoapoco_black.jpg" alt="Pocoapoco" width="500"></a></p>
<br>
<p align="center">
<a href="#"><img src="https://img.shields.io/badge/php-%3E%3D7.0-blue" alt="PHP Version"></a>
<a href="#"><img src="https://img.shields.io/badge/license-MIT%20%2B%20file%20LICENSE-blue" alt="License"></a>
</p>
<br>

## Pocoapoco 框架
---------- 
Pocoapoco框架是由國家兩廳院資訊組，針對自家開發需求撰寫而成，透過簡單路由引擎加上 MVC 架構概念，做到程式碼的輕易控管。
<br><br>

## 框架安裝及部署
---------- 
### **- 安裝**
composer：https://packagist.org/packages/ntch/pocoapoco
```bash
composer create-project ntch/pocoapoco
```
<br>

### **- 伺服器**
伺服器使用 Nginx，其設定檔（nginx.conf）範例如下：

```bash
# 需自行調整參數
# path -> 執行 composer 的地方
# web_basic -> 專案名稱

server {
        listen 60000;
        root   /{path}/pocoapoco/web/{web_basic}/public/;
        index  index.html index.htm index.php;
        location /NTCH/ {
            alias /{path}/pocoapoco/src/;
            location ~ \.php$ {
                fastcgi_pass 127.0.0.1:30001;
                fastcgi_index index.php;
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $request_filename;
                fastcgi_param DOCUMENT_ROOT $document_root;
                fastcgi_param PROJECT_ROOT /{path}/pocoapoco/web/{web_basic}/;
            }
        }
        location / {
            try_files $uri /NTCH/Bootstrap.php$is_args$args;
        }
    }
```
<br><br>


## 目錄結構
---------- 
```
pocoapoco
│
└─── src（framework）
│
└─── tests（PHPUnit）
│
└─── vendor（require package）
│
└─── web（your projects）
│    │
│    └─── web_basic 
│         │
│         │─── controllers
│         │
│         │─── libraries
│         │
│         │─── log
│         │
│         │─── models
│         │
│         │─── public
│         │
│         │─── routes
│         │
│         │─── settings
│         │
│         └─── view
│ 
│  composer.json
│  composer.lock

```
<br><br>


## 學習
---------- 
> ### **router**

資料夾：routes<br>
檔案：router.php<br>

< routes 起手式 >
```php
use Ntch\Pocoapoco\WebRestful\Routing\Router;

$router = new Router();
```
< 可用路由的方法 >
```php
$router->controller($uri, $path, $file, $method);

$router->view($uri, $path, $file, $data);

$router->public($path, $file);

$router->mvc($uri,
    [
        'controller' => [$path, $file],
        'libraries' => [$libraryName],
        'mail' => ['$mailName'],
        'aws' => ['$awsName'],
        'models' => [$modelName]
    ]);

// example
$router->controller('/uri/:parameter', '/path', 'class', 'method');

$router->view('/uri/:parameter', '/path', 'class', ["pocoapoco" => "framework"]);

$router->public('/path', 'index.html');

$router->mvc('/uri/:parameter',
    [
        'controller' => ['/path', 'class', 'method'],
        'libraries' => ['name'],
        'mail' => ['name'],
        'aws' => ['name'],
        'oracle' => ['server_name', 'tb_name'],
        'mysql' => ['server_name', 'tb_name'],
        'mssql' => ['server_name', 'tb_name'],
        'postgre' => ['server_name', 'tb_name'],
    ]);
```

| 方法         | 說明  |
| :--------   | :---- |
| controller  | 轉向 controller 方法 |
| view        | 轉向 view 方法 |
| public      | 轉向 public 方法 |
| mvc         | 需要引入 model 和 library 等物件並轉向 controller 方法 |

＊model 提供的類型再請參考 model 的文件

<br>

| 參數    | 型態   | 說明  |
| :----  | :----  | :---- |
| uri    | string | 請求的網址，加 '：' 及代表此為參數 |
| path   | string | 請求導向執行檔案的路徑 |
| file   | string | 執行檔案名稱 |
| method | string | 執行檔案中的方法名稱，預設 index |
| data   | array | 要傳入的參數 |

＊注意：重複符合的 uri ，以上面的為主。<br>
＊建議：常用功能方法往上放，能加速執行速度。

<br><br>

> ### **controller**

資料夾：controllers<br>
檔案：xxx.php<br>

< controllers 起手式 >
```php
use Ntch\Pocoapoco\WebRestful\Controllers\Controller;
# 如要導向 view 請把 Router 導入
use Ntch\Pocoapoco\WebRestful\Routing\Router;

class xxx extends Controller
{
    public function index()
    {
        $router = new Router();
        $router->view(null, '/path', 'class', array("name" => "Pocoapoco"));
    }
}
```

< request 提供的物件 >
```text
# $this->request

[request] => stdClass Object
        (
            [uuid] => 10AEF2B9-D783-2CAA-8E07-6BDC2EF83117
            [method] => GET
            [uri] => Array
                (
                    [a] => 1
                    [b] => 2
                    [c] => 3
                )

            [query] => Array
                (
                    [name] => Pocoapoco
                )

            [input] => 
            [attribute] => Array
                (
                )

            [authorization] => Array
                (
                )

            [cookies] => Array
                (
                    [php] => omfb5668s9i952d05gdbckp1ej
                )

            [files] => Array
                (
                )

            [client] => Array
                (
                    [ip] => 172.0.0.0
                    [port] => 60505
                )

            [time] => Array
                (
                    [unix] => 1643257668.4144
                    [date] => 2022-01-01
                    [time] => 12:00:00
                )

            [headers] => Array
                (
                )

        )
```
| 參數           | 說明  |
| :----         | :---- |
| uuid          | 每次請求的獨立唯一編號 |
| method        | 請求方法 |
| uri           | router 設定的參數 |
| query         | GET 方法代入的參數 |
| attribute     | POST 方法代入的參數 |
| authorization | herder 中的 token |
| cookies       | 請求帶入的 cookie |
| files         | input 上傳的 file |
| client        | 請求來源 |
| time          | 請求時間 |
| headers       | 請求 header |

<br><br>

> ### **setting**

資料夾：settings<br>
檔案：xxx.ini<br>

  - library.ini：libraries 命名與載入層級設定，實際引入由 router 載入
  - mail.ini：郵件參數設定
  - aws.ini：aws iam 參數設定
  - error.ini：開發與正式上線參數設定
  - log.ini：log 參數設定
  - project.ini：專案共用參數設定
  - model 檔名：oracle.ini、mysql.ini、mssql.ini、postgre.ini

< settings 起手式 >
```ini
# 檔名：libraries.ini
# 別名 = 路徑（要載入至哪個路徑下的所有檔案）
name = /path

# 檔名：mail.ini
[name]
Host        = pocoapoco.com
Port        = 25
Username    = pocoapoco
Password    = xxxxxxxxx
SMTPAuth    = true|false
SMTPSecure  = tls|ssl
CharSet     = utf-8
SMTPDebug   = 1|0
Timeout     = 5

# 檔名：aws.ini
[name]
version  = latest
region   = ap-northeast-1
key      = pocoapoco
secret   = xxxxxxxxx

# 檔名：error.ini
[MAIN]
debug                = 1|0
page_4xx             = 4xx.html
page_5xx             = 5xx.html
mail_from            = server@pocoapoco.com
mail_to              = royhylee@mail.npac-ntch.org:ROY
mail_server          = mail.ini-name

[PAGE]
E_EXCEPTION          = 1
E_ERROR              = 1
E_WARNING            = 0
E_PARSE              = 1
E_NOTICE             = 0
E_CORE_ERROR         = 1
E_CORE_WARNING       = 1
E_COMPILE_ERROR      = 1
E_COMPILE_WARNING    = 1
E_USER_ERROR         = 1
E_STRICT             = 1
E_RECOVERABLE_ERROR  = 1
E_DEPRECATED         = 1
E_USER_DEPRECATED    = 1
E_ALL                = 1

[MAIL]
E_EXCEPTION          = 1
E_ERROR              = 1
E_WARNING            = 1
E_PARSE              = 1
E_NOTICE             = 1
E_CORE_ERROR         = 1
E_CORE_WARNING       = 1
E_COMPILE_ERROR      = 1
E_COMPILE_WARNING    = 1
E_USER_ERROR         = 1
E_STRICT             = 1
E_RECOVERABLE_ERROR  = 1
E_DEPRECATED         = 1
E_USER_DEPRECATED    = 1
E_ALL                = 1

# 檔名：log.ini
cycle  = daily|weekly|monthly|yearly

# 檔名：project.ini
[name]
key = value

# 檔名：oracle.ini
[server_name]
type      = server
ip        = xx.xx.xx.xx
port      = 1521
sid       = oracle
user      = pocoapoco
password  = xxxxxxxxx
path      = /path
class     = class

[tb_name]
type    = table
server  = server_name
table   = tb
path    = /path
class   = class

# 檔名：mysql.ini
[server_name]
type      = server
ip        = xx.xx.xx.xx
port      = 3306
database  = pocoapoco
user      = pocoapoco
password  = xxxxxxxxx
path      = /path
class     = class

[tb_name]
type    = table
server  = server_name
table   = tb
path    = /path
class   = class

# 檔名：mssql.ini
[server_name]
type      = server
ip        = xx.xx.xx.xx
port      = 1433
database  = pocoapoco
user      = pocoapoco
password  = xxxxxxxxx
path      = /path
class     = class

[tb_name]
type    = table
server  = server_name
table   = tb
path    = /path
class   = class

# 檔名：postgre.ini
[server_name]
type      = server
ip        = xx.xx.xx.xx
port      = 5432
database  = pocoapoco
schema    = public
user      = pocoapoco
password  = xxxxxxxxx
path      = /path
class     = class

[tb_name]
type    = table
server  = server_name
table   = tb
path    = /path
class   = class
```

<br><br>

> ### **library**

資料夾：libraries<br>
檔案：xxx.php<br>

< library 起手式 >
```php
# 依據 PSR-4 命名規則，給予路徑
namespace la\lb\lc;

class xxx
{
    public function index()
    {
        echo 'librarys import success！' . PHP_EOL;
    }
}
```

< setting 定義別名 >
```ini
lib = /la
```

< router 檔案引入至 controller 使用 >
```php
$router->mvc('/uri',
    [
        'controller' => ['/path', 'class'],
        'libraries' => ['lib']
    ]);
```

< controller 使用 >
```php
use Ntch\Pocoapoco\WebRestful\Controllers\Controller;
use la\lb\lc\class;

class test extends Controller
{
    public function index()
    {
        $lib = new class();
        $lib->index();
    }
}
```

<br><br>

> ### **model**

資料夾：models<br>
檔案：xxx.php<br>

＊提供類型：Oracle、Mysql、Mssql、Postgre

< model 起手式 >
```php
# 依據需求引入相對應的 model

# Oracle
use Ntch\Pocoapoco\WebRestful\Models\OracleModel;

# Mysql
use Ntch\Pocoapoco\WebRestful\Models\MysqlModel;

# Mssql
use Ntch\Pocoapoco\WebRestful\Models\MssqlModel;

# Postgre
use Ntch\Pocoapoco\WebRestful\Models\PostgreModel;


class model_demo extends OracleModel # 依據使用的類型繼承
{

    # modelType = server or table
    public string $modelType = 'table';
    # setting 給的匿名
    public string $modelName = 'tb_name';

    public function schema()
    {
        $schema['COLUMN_NAME'] = [
            'DATA_TYPE' => '', 
            'DATA_SIZE' => '',
            'NULLABLE' => '',
            'DATA_DEFAULT' => '',
            'KEY_TYPE' => '',
            'COMMENT' => '',
            'SYSTEM_SET' => ''
        ];

        return $schema;
    }
}
```

| 參數           | 說明  | 必填  | 內容  |  
| :----         | :---- | :---- | :---- |
| DATA_TYPE     | 欄位型別 | 是 | 各別資料庫如下說明 |
| DATA_SIZE     | 欄位大小 | 否 | 時間參數依各資料庫 format 格式，提供：年、月、日、時、分、秒 |
| NULLABLE      | 是否為空值 | 否 | 預設為否 |
| DATA_DEFAULT  | 預設值 | 否 | 預設為 null |
| KEY_TYPE      | 鍵值 | 否 | P：主鍵 |
| COMMENT       | 敘述 | 否 |  |
| SYSTEM_SET    | 系統設定，做 CRUD 時會自動代入 | 否 | PRIMARY_KEY：主鍵 , UPDATE_DATE：更新時間 |

<br>

 - Oracle 提供的 DATA_TYPE
   - CHAR
   - NCHAR
   - VARCHAR2
   - NVARCHAR2
   - NCLOB
   - FLOAT
   - NUMBER
   - DATE
  
 - Mysql 提供的 DATA_TYPE
   - char
   - varchar
   - tinyint
   - smallint
   - mediumint
   - bigint
   - int
   - float
   - decimal
   - timestamp
   - datetime
   - date
   - time
   - year

 - Mssql 提供的 DATA_TYPE
   - char
   - varchar
   - nchar
   - nvarchar
   - tinyint
   - smallint
   - bigint
   - int
   - float
   - decimal
   - datetime
   - date

 - Postgre 提供的 DATA_TYPE
   - char
   - varchar
   - uuid
   - bool
   - date
   - inet
   - json
   - float
   - decimal
   - integer
   - bigint
   - smallint
   - timestamp
   - timestamptz
   - xml

< setting 設定檔定義別名 >
```ini
[server_name]
type      = server
ip        = xx.xx.xx.xx
port      = 1521
sid       = oracle
user      = pocoapoco
password  = xxxxxxxxx
path      = /path
class     = class

[tb_name]
type    = table
server  = server_name
table   = tb
path    = /path
class   = class
```

< router 檔案引入至 controller 使用 >
```php
$router->mvc('/uri',
    [
        'controller' => ['/path', 'class'],
        'oracle' => ['server_name', 'tb_name']
    ]);
```

＊server 為撈出該 database 所有 table，非必要引入

< controller 使用 >
```php
use Ntch\Pocoapoco\WebRestful\Controllers\Controller;

class test extends Controller
{
    public function index()
    {
        # server 導入
        $server_name = $this->models['oracle']->server['server_name']->tb_name;

        # table 導入
        $tb_name = $this->models['oracle']->table['tb_name'];

        # ORM 架構 
        # createTable 範例
        $sql = $tb_name->createTable();

        # commentTable 範例
        $sql = $tb_name->commentTable();

        # select 範例
        $data = $server_name->select(['a', 'b', 'SUM(c)'])->
        where(['b' => 1])->groupby(['a', 'b'])->
        orderby(['a'])->query();

        # insert 範例
        $data = $tb_name->insert()->value(['a' => 1])->query();

        # update 範例
        $data = $tb_name->update()->set(['a' => 1])->where(['b' => 2])->query();

        # delete 範例
        $data = $tb_name->delete()->where(['a' => 1])->query();

        # merge 範例 - 僅提供 Oracle 使用
        $data = $tb_name->merge()->using('user', 'table')->on("a", "b")->
                    matched()->update()->set(['c' => 'c', 'd' => 'd'])->
                    not()->insert()->value()->query();

        # commit 範例
        $tb_name->commit();

        # rollback 範例
        $tb_name->rollback();

        # 筆數限制 - 第 5 筆開始顯示 8 筆
        # Oracle 範例 - 若兩個方法皆要使用 offset() limit() 順序可顛倒
        $data = $server_name->select()->offset(5)->limit(8)->query();

        # Mysql 範例 - 若兩個方法皆要使用 limit() offset() 順序可顛倒
        $data = $mysql->select()->limit(8)->offset(5)->query();

        # Mssql 範例 - 兩種方法
        $data = $mssql->select()->top(8)->query();
        $data = $mssql->select()->groupby()->offset(5)->fetch(8)->query();
        
        # Postgre 範例 - 若兩個方法皆要使用 limit() offset() 順序可顛倒
        $data = $postgre->select()->limit(8)->offset(5)->query();
    }
}
```

 - 通用方法
   - createTable()
   - commentTable()  // Mysql 不適用
   - insert()
   - value($value)
   - delete()
   - update()
   - set($set)
   - select($select)
   - where($where)
   - orderby($orderby)
   - groupby($groupby)
   - query()
   - commit()
   - rollback()
   - keyName($keyName) (指定 key 欄位)

 - Oracle 方法
   - merge()
   - using($userName, $tableName)
   - on($target, $source)
   - matched()
   - not()

| 參數       | 型態    | 說明  |
| :----     | :----   | :---- |
| value     | array   | \[ '欄位名稱' => 值 \] |
| set       | array   | \[ '欄位名稱' => 值 \] |
| select    | array   | 一維陣列填入欄位名稱 |
| where     | array   | \[ '欄位名稱' => 值 \] |
| orderby   | array   | 一維陣列填入欄位名稱 |
| groupby   | array   | 一維陣列填入欄位名稱 |
| keyName   | string  | 指定返回的值的 key |
| userName  | string  | 使用者名稱 |
| tableName | string  | 表名稱 |
| target    | string  | 目標比對欄位 |
| source    | string  | 來源比對欄位 |

<br><br>

> ### **log**

資料夾：log<br>
檔案：依據 setting 中的 log.ini 設定週期產生<br>

< log 使用 >
```php
# 依據 PSR-3 規則，給予層級

$this->log($level, $message, $info);

// example
$this->log('INFO', 'message', ['name' => 'pocoapoco']);
```

| 參數     | 型態   | 說明  |
| :----   | :----  | :---- |
| level   | string | 依據 PSR-3 規則，給予層級 |
| message | string | 主要訊息標題 |
| info    | array  | 要記錄的資訊 |

<br><br>

> ### **mail**
< mail 使用 >
```php

$res = $mailer->header($header)->
                from($from)->
                to($to)->
                subject($subject)->
                content($source, $type, $content, $data)->
                send();

// example
$mailer = $this->mail['mailer'];

$header = ['ID' => 'xxxxxxxx'];
$from = ['server@pocoapoco.com' => 'POCOAPOCO'];
$to = ['royhylee@mail.npac-ntch.org' => 'ROY LEE'];
        
$res = $res = $mailer->header($header)->
                from($from)->
                to($to)->
                subject('test')->
                content('local', 'html', '/path/error.php', ['error' => 'message'])->
                send();
```
 - 方法
   - header($header)
   - from($from)
   - to($to) 
   - subject($subject)
   - content($source, $type, $content, $data)
   - attachment($source, $attachment)
   - image($path, $cid, $name)
   - send()
 
| 參數        | 型態    | 說明  |
| :----      | :----   | :---- |
| header     | array   | 信件表頭 |
| from       | array   | 寄件人 \[ '信箱' => '寄件人名稱' \] |
| to         | array   | 收件人 \[ '信箱' => '收件人名稱' \] |
| subject    | string  | 信件標題 |
| source     | string  | 資料來源 content => user\|url\|local ,  attachment => url\|local |
| type       | array   | text\|html |
| content    | string  | 信件內容，依 $content 帶入的值分別給予 text\|uri\|path |
| data       | array   | 使用 view 中的檔案，要帶入的參數 |
| attachment | string  | 信件內容，依 $content 帶入的值分別給予 uri\|path |
| path       | string  | 圖片位置 uri\|path |
| cid        | string  | 圖片別名 |
| name       | string  | 圖片名稱 |


<br><br>

> ### **aws**
< aws 使用 >
```php

$aws = $this->aws['name'];

// example
$aws->s3_upload('bucket', '/aws_path', 'aws_file.txt', '/local_path', 'local_file.txt', 2)

$aws->s3_read('bucket', '/aws_path', 'aws_file.txt', ['key' => 'xxxxxxxxxxxx', 'md5' => 'oooooooooooo'])

$aws->s3_download('bucket', '/aws_path', 'aws_file.txt', '/local_path', 'local_file.txt', ['key' => 'xxxxxxxxxxxx', 'md5' => 'oooooooooooo'])

$aws->s3_copy('source_bucket', '/source_path', 'source_file.txt', 'target_bucket', '/target_path', 'target_file.txt')

```
 - 方法
   - s3_exist($bucket, $awsPath, $awsFile, $sseKey)
   - s3_list($bucket)
   - s3_upload($bucket, $awsPath, $awsFile, $localPath, $localFile, $security, $download)
   - s3_read($bucket, $awsPath, $awsFile, $sseKey)
   - s3_download($bucket, $awsPath, $awsFile, $localPath, $localFile, sseKey)
   - s3_copy($sourceBucket, $sourcePath, $sourceFile, $targetBucket, $targetPath, $targetFile)
   - s3_delete($bucket, $awsPath, $awsFile, $sseKey)
   - s3_get($bucket, $awsPath, $awsFile, $effectTime, $sseKey)
 
| 參數           | 型態           | 預設      | 說明  |
| :----         | :----         | :----     | :---- |
| bucket        | string        |           | aws s3 桶子名稱 | 
| awsPath       | string        |           | aws s3 桶子路徑 |
| awsFile       | string        |           | aws s3 桶子文件名稱 |
| localPath     | string        |           | 地端路徑 |
| localFile     | string        |           | 地端檔案名稱 |
| security      | int           |           | 1：public 2：private 3：sse encryption |
| sseKey        | array         | []        | \[ 'key' => '值', 'md5' => '值' \] |
| download      | int           | 0         | 0：show on web 1：download file |
| sourceBucket  | string        |           | aws s3 來源桶子名稱 |
| sourcePath    | string        |           | aws s3 來源桶子路徑 |
| sourceFile    | string        |           | aws s3 來源桶子文件名稱 |
| targetBucket  | string        |           | aws s3 目標桶子名稱 |
| targetPath    | string        |           | aws s3 目標桶子路徑 |
| targetFile    | string        |           | aws s3 目標桶子文件名稱 |
| effectTime    | int           |           | 網址有效時間（分鐘）-1：永久公開 |
