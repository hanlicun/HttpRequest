<?php
class Http
{
    /**
     * Initiate a HTTP/HTTPS request
     * @param $url interface url
     * @param array $params  array('content'=>'test', 'format'=>'json');
     * @param string $method request type    GET|POST
     * @param bool $multi image info
     * @param array $extheaders 
     * @return mixed
     */
    public static function request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array(),$is_restful=false)
    {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, 'Jianli develop');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        $timeout = $multi?300:30;
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        if($is_restful){
            curl_setopt ( $ci, CURLOPT_CUSTOMREQUEST, $method );
        }
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if(isset($headers[0])&&($headers[0]=='Content-type: text/xml' || $headers[0]=='Content-Type:application/json;charset=utf-8')){
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                    }elseif($multi and is_array($multi))
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                if(is_string($multi))
                {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $multi);
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if(isset($headers[0])&&($headers[0]=='Content-type: text/xml' || $headers[0]=='Content-Type:application/json;charset=utf-8')){
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                }elseif(!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }
}
