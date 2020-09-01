<?php
/**
 * Created by PhpStorm.
 * User: yezhilie
 * Date: 2020/9/1
 * Time: 14:33
 */

namespace ElasticSearch\Controller;

use Common\Controller\Base;
use ElasticSearch\Service\ElasticSearchService;

class TestController extends Base
{

    public function syncTable(){
        $res = ElasticSearchService::syncTable('user', [
            'nickname' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ],
            'create_time' => [
                'type' => 'long'
            ]
        ]);
        echo "<pre>";
            print_r($res);
        echo "</pre>";
    }

    public function syncTableId(){
        $res = ElasticSearchService::syncTableId('user', 3, [
            'nickname' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ],
            'create_time' => [
                'type' => 'long'
            ]
        ]);
        echo "<pre>";
            print_r($res);
        echo "</pre>";
    }

    public function delTable(){
        $res = ElasticSearchService::delTable('user');
        echo "<pre>";
            print_r($res);
        echo "</pre>";
    }

    public function delTableId(){
        $res = ElasticSearchService::delTableId('user', 2);
        echo "<pre>";
            print_r($res);
        echo "</pre>";
    }

    public function search(){
        $params = [
            'index' => 'user',
            'body' => [
//                'query' => [
//                    'match' => [
//                        'nickname' => '管理员'
//                    ]
//                ]
            ]
        ];
        $sort = [
            'create_time' => ['order' => 'desc'],
        ];
        $res = ElasticSearchService::search($params, 1, 20, $sort);
        echo "<pre>";
            print_r($res);
        echo "</pre>";
    }
}