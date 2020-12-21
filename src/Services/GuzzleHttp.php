<?php


namespace Cranux\Larakeaimao\Services;


use GuzzleHttp\Client;

class GuzzleHttp
{
    /**
     * @param string $baseUri 基URI
     * @param string $method 请求方法
     * @param string $sRequUrl 请求路由
     * @param array $data 请求数据
     * @param int $timeout 超时时间
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest(string $baseUri, $method = 'GET', $sRequUrl = null, $data = [], $timeout=3)
    {
        //区分请求方式
        $aParams =[];
        if($method == 'GET' ){
            $aParams = ['query'=>$data];
        }else if($method == 'POST' ){
            $aParams = ['form_params'=>$data];
        }


        $httpClient = $httpClient = new Client([
            'base_uri' => $baseUri,
            'timeout' => $timeout
        ]);
        $sHttpRes = $httpClient->request($method, $sRequUrl, $aParams)->getBody()->getContents();
        return json_decode($sHttpRes, true);

    }

    /**
     * @param $baseUri
     * @param string $method
     * @param $sRequUrl
     * @param array $data
     * @param $timeout
     */
    public function sendAsyncRequest($baseUri, $method = 'GET', $sRequUrl = null, $data = [], $timeout=3)
    {
        $httpClient = new Client([
            'base_uri' => $baseUri,
            'timeout' => $timeout
        ]);
        $httpClient->requestAsync($method, $sRequUrl, ['query' => $data])
            ->then(
                function (ResponseInterface $res) {
                    return json_decode($res->getBody()->getContents(), true);
                },
                function (RequestException $e) {
                    Log::write('接口调用失败==='.$e->getMessage());
                }
            )->wait();
    }
}