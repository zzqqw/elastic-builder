

## 使用

 ~~~
 use Elasticsearch\ClientBuilder;
 use Xqiang\ElasticBuilder\Elastic;
 $client = ClientBuilder::create()->setHosts([
     "127.0.0.1:9200",
 ])->setSSLVerification(false)->build();
 $elastic=Elastic::getInstance($client);
 
 
 $bulider = $elastic->index('test')
     ->where('id',1)
     ->whereIsNotNull('name')
     ->whereLike('name','zhiqiang')
     ->whereBetween('age',1,20)
     ->whereQueryString('name=zhiqiang OR Xqiang',['name']);
     
     
  //常见搜索   
 $bulider->search();
     
 //聚合查询
 $bulider->count();
 $bulider->sum();
 $bulider->avg();
 $bulider->max();
 $bulider->min();
 ~~~