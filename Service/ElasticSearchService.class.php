<?php

namespace ElasticSearch\Service;


use Elasticsearch\ClientBuilder;
use System\Service\BaseService;

class ElasticSearchService extends BaseService {

    public $esClient;
    public $defaultHost = '127.0.0.1';
    public $defaultPort = '9200';

    /**
     * 初始化
     * ElasticSearchService constructor.
     * @param null $host
     * @param null $port
     */
    public function __construct($host = null, $port = null){
        $host = $host ?: $this->defaultHost;
        $port = $port ?: $this->defaultPort;
        $this->esClient = ClientBuilder::create()->setHosts([
            'host' => $host,
            'port' => $port,
        ])->build();
    }

    /**
     * 删除
     * @param string $table 表名
     * @return array
     */
    static function delTable($table){
        $client = (new self)->esClient;
        $exists = $client->indices()->exists(['index' => $table]);
        if($exists){
            $res = $client->indices()->delete(['index' => $table]);
            if($res['acknowledged']){
                return self::createReturn(true, null, '删除成功');
            }else{
                return self::createReturn(false, null, '删除失败');
            }
        }else{
            return self::createReturn(true, null, '删除成功');
        }
    }

    /**
     * 同步表数据
     * @param string $table 表名
     * @param array $table_properties 定义字段类型
     */
    static function syncTable($table, $table_properties = []){
        $client = (new self)->esClient;
        $exists = $client->indices()->exists(['index' => $table]);
        if(!$exists){
            $client->indices()->create([
                'index' => $table,
                'body' => [
                    'mappings' => [
                        'properties' => $table_properties
                    ]
                ]
            ]);
        }

        $page = 1;
        $data = M($table)->page($page, 100)->select();
        while($data){
            $params = [];
            foreach($data as $v){
                $params['body'][] = [
                    'index' => [
                        '_index' => $table,
                        '_id' => $table.'_'.$v['id']
                    ]
                ];
                $params['body'][] = $v;
            }
            $client->bulk($params); //批量
            $page++;
            $data = M($table)->page($page, 100)->select();
        }
    }

    /**
     * 同步表的一行数据
     * @param string $table 表名
     * @param int $id id
     * @param array $table_properties 定义字段类型
     * @return array
     */
    static function syncTableId($table, $id, $table_properties = []){
        $data = M($table)->where(['id' => $id])->find();
        if($data){
            $client = (new self)->esClient;
            $exists = $client->indices()->exists(['index' => $table]);
            if(!$exists){
                $client->indices()->create([
                    'index' => $table,
                    'body' => [
                        'mappings' => [
                            'properties' => $table_properties
                        ]
                    ]
                ]);
            }
            $results = $client->index([
                'index' => $table,
                'id' => $table.'_'.$data['id'],
                'body' => $data
            ]);
            if($results['_shards']['successful']){
                return self::createReturn(true, null, '同步成功');
            }else{
                return self::createReturn(false, null, '同步失败');
            }
        }
        return self::createReturn(false, null, '获取数据失败');
    }

    /**
     * 删除
     * @param string $table 表名
     * @param int $id id
     * @return array
     */
    static function delTableId($table, $id){
        $client = (new self)->esClient;
        $exists = $client->exists([
            'index' => $table,
            'id' => $table.'_'.$id
        ]);
        if($exists){
            $results = $client->delete([
                'index' => $table,
                'id' => $table.'_'.$id
            ]);
            if($results['_shards']['successful']){
                return self::createReturn(true, null, '删除成功');
            }else{
                return self::createReturn(false, null, '删除失败');
            }
        }else{
            return self::createReturn(true, null, '删除成功');
        }
    }

    /**
     * 搜索
     * @param array $params 搜索参数，需要定义字段类型
     * @param int $page
     * @param int $limit
     * @param array $sort 排序，需要定义字段类型
     * @return array
     */
    static function search($params, $page = 1, $limit = 10, $sort = []){
        $client = (new self)->esClient;
        $params['body']['size'] = $limit;
        $params['body']['from'] = ($page-1)*$limit;
        if($sort){
            $params['body']['sort'] = $sort;
        }
        $results = $client->search($params);
        $hits = $results['hits']['hits'];
        $items = [];
        foreach($hits as $v){
            $items[] = $v['_source'];
        }
        $total_items = $results['hits']['total']['value'];
        $data = [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'items' => $items,
            'total_items' => (int)$total_items,
            'total_pages' => ceil($total_items/$limit)
        ];
        return self::createReturn(true, $data);
    }
}