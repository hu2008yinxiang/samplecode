<?php

/**
 * @author 继续
 *
 */
class ApiController extends Phalcon\Mvc\Controller
{

    /**
     *
     * @return \Phalcon\HTTP\ResponseInterface @Post('/register')
     */
    public function registerAction()
    {
        $response = $this->response;
        $empty = array(
            'package' => '',
            'android_id' => '',
            'adid' => '',
            'imei' => '',
            'apps' => array()
        );
        $rate = rand(0, 8);
        $params = array_merge($empty, json_decode(Misc::gunzip($this->request->getRawBody()), true));
        // $params['apps'][] = $rate > 0 ? $params['package'] : 'com.test';
        $params['apps'][] = $params['package'];
        $sh = array();
        // TODO 此处处理客户端上传的数据
        foreach ($params['apps'] as $k => $app) {
            $sh[] = ':' . $k . ':';
        }
        $adItem = \AdItems::findFirst(array(
            'package NOT IN ( ' . implode(', ', $sh) . ' )',
            'bind' => $params['apps'],
            'order' => 'rand()'
        ));
        if ($adItem) {
            // $adItem = $adItem->getFirst();
        } else {
            $response->setContentType('application/json');
            $response->setJsonContent(array());
            return $response;
        }
        $info = array(
            'sequence' => array(
                'admob',
                'mm',
                'inmob'
            ),
            'url' => 'http://' . $this->request->getHttpHost() . $adItem->getRedirectUrl($params['package'], $params['imei'], $params['android_id']),
            'image' => 'http://' . $this->request->getHttpHost() . $adItem->getImageUrl(),
            'interval' => $adItem->interval,
            'package' => $adItem->package,
            'type' => $adItem->type
        );
        $response->setContentType('application/json');
        $response->setJsonContent($info);
        return $response;
    }

    /**
     *
     * @return \Phalcon\HTTP\ResponseInterface @Post('/show')
     */
    public function showAction()
    {
        $response = $this->response;
        $params = json_decode(Misc::gunzip($this->request->getRawBody()), true);
        $empty = array(
            'package' => '',
            'android_id' => '',
            'from' => '',
            'type' => 'featured',
            'imei' => '',
            'adid' => ''
        );
        $params = array_merge($empty, $params);
        // TODO 此处处理客户端上传的数据
        $response->setContentType('application/json');
        return $response;
    }
}