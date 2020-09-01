## ElasticSearch

### 安装java jdk
ElasticSearch运行需要java jdk支持。所以要先安装java环境。
由于ElasticSearch 5.x 往后依赖于JDK 1.8的，所以现在我们下载JDK 1.8或者更高版本。

[java8 下载](https://www.oracle.com/java/technologies/javase/javase-jdk8-downloads.html)

### 安装ElasticSearch
- [官网](https://www.elastic.co/cn/downloads/elasticsearch)
- [华为云镜像](https://mirrors.huaweicloud.com/elasticsearch)

### 使用ElasticSearch

1. 解压缩
2. 启动 `./bin/elasticsearch.bat`
3. 可以访问即启动成功 `http://127.0.0.1:9200`

![image](https://s1.ax1x.com/2020/08/31/dOn0VP.png)
![image](https://s1.ax1x.com/2020/08/31/dOmLAf.png)


## ElasticSearch-PHP

#### composer安装
`composer require elasticsearch/elasticsearch`

#### 实例化
```php
use Elasticsearch\ClientBuilder;
$client = ClientBuilder::create()->setHosts([
    'host' => '127.0.0.1',
    'port' => '9200',
])->build();
```


## 核心概念

#### ==索引(index)==
一个索引可以理解成一个关系型数据库
```php
//判断索引是否存在
$exists = $client->indices()->exists(['index' => $table]);
if(!$exists){
    //新建索引
    $client->indices()->create([
        'index' => $table,
        'body' => [
            'mappings' => [
                'properties' => $table_properties
            ]
        ]
    ]);
}
```

#### 类型(type)
一个type就像一类表，比如user表、order表

注意
- ES 5.X中一个index可以有多种type
- ES 6.X中一个index只能有一种type
- ES 7.X以后已经移除type这个概念

#### ==映射(mapping)==
mapping定义了每个字段的类型等信息。相当于关系型数据库中的表结构
```php
//定义$table_properties
$table_properties = [
    'name' => [
        'type' => 'keyword' //keyword是一个关键字，不会被分词
    ],
    'content' => [
        'type' => 'text', //text会被分词，使用的是全文索引
        'analyzer' => 'standard' //指定分词器
    ]
];
```
##### 数据类型

类型 | 描述
---|---
text | 字符串：用于全文索引，该类型的字段将通过分词器进行分词
keyword | 字符串：不分词，只能搜索该字段的完整的值
long、integer、short、byte、double、float、half_float、scaled_float| 数值型
boolean|布尔型
binary|二进制：该类型的字段把值当做经过base64编码的字符串，默认不存储，且不可搜索
integer_range、float_range、long_range、double_range、date_range|范围类型：范围类型表示值是一个范围，而不是一个具体的值，比如age类型是integer_range，那么值可以是{"gte":20,"lte":40}；搜索"term":{"age":21}可以搜索该值
date|日期：格式如："2022-01-01"、"2022/01/01 12:10:30"、1598929631000(从1970年1月1日0点开始的毫秒数)
array|数据
object|对象
ip|IP类型：IP类型的字段用于存储IPv4和IPv6的地址，本质上是一个长整形字段，例如：192.168.0.0/16





#### ==文档(document)==
一个document相当于关系型数据库中的一行记录
```php
//新建文档
$client->index([
    'index' => $table,
    'id' => $table.'_'.$data['id'], //唯一id，当id已存在时会变成修改操作
    'body' => [
        'id' => '12',
        'name' => '美团',
        'content' => '有用户在使用美团下单时发现无法使用支付宝支付了，对此你怎么看？你觉得这会影响数字化生活发展吗？'
    ]
]);
//批量新建文档
//内容是以下格式，每两个为一条数据
$client->bulk([
    'body' => [
        //第一条数据
        [
            'index' => [
                '_index' => $table,
                '_id' => $table.'_12'
            ]
        ],
        [
            'id' => '12',
            'name' => '美团',
            'content' => '有用户在使用美团下单时发现无法使用支付宝支付了，对此你怎么看？你觉得这会影响数字化生活发展吗？'
        ],
        //第二条数据
        [
            'index' => [
                '_index' => $table,
                '_id' => $table.'_13'
            ]
        ],
        [
            'id' => '13',
            'name' => '美团',
            'content' => '有用户在使用美团下单时发现无法使用支付宝支付了，对此你怎么看？你觉得这会影响数字化生活发展吗？'
        ]
    ]
]);
```


#### 字段(field)
相当于关系型数据库表的字段

#### 集群(cluster)
集群由一个或多个节点组成，一个集群由一个默认名称“elasticsearch”

#### 节点(node)
集群的节点，一台机器或者一个进程

#### 分片和副本(shard)
副本是分片的副本。分片有主分片(primary Shard)和副本分片(replica Shard)之分
一个Index数据在屋里上被分布在多个主分片中，每个主分片只存放部分数据
每个主分片可以有多个副本，叫副本分片，是主分片的复制

## 分词器

#### 内置分词器
1. standard analyzer (默认分词器，可以支持中文分词)
2. simple analyzer
3. whitespace analyzer
4. stop analyzer
5. language analyzer
6. pattern analyzer

#### 安装分词器

##### smartCN
一个简单的中文或中英文混合文本的分词器

安装之后重启

`sh elasticsearch-plugin install analysis-smartcn`

卸载`sh elasticsearch-plugin remove analysis-smartcn`

##### IK分词器
更智能更友好的中文分词器

安装
1. [下载对应的版本](https://github.com/medcl/elasticsearch-analysis-ik/releases)
2. 解压放到`plugins`目录下
3. 重启ES

![image](https://s1.ax1x.com/2020/09/01/djHua6.png)


## 搜索&分页&排序
详细文档：[https://blog.csdn.net/afeiqiang/article/details/83047989](https://blog.csdn.net/afeiqiang/article/details/83047989)
```php
$results = $client->search([
    'index' => 'bbs_article',
    'body' => [
        'size' => 5, //分页：表示获取5条数据
        'form' => 5, //分页：表示从第6条开始获取
        'sort' => [
            'create_time' => [
                'order' => 'desc' //排序：create_time倒序
            ]
        ],
        'query' => [
            'match' => [
                'content' => '新闻发布会' //搜索
            ]
        ]
    ]
]);
```