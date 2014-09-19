<?php

    /*
     *  Class to integrate with Untappd's API.
     *    Authenticated calls are done using OAuth and require access tokens for a user.
     *    API calls which do not require authentication do not require tokens
     *
     *  Full documentation available on github
     *    http://wiki.github.com/jmathai/Untappd-async
     *
     *  @author Jaisen Mathai <jaisen@jmathai.com>
     */

    class EpiUntappd
    {
        protected $clientId, $clientSecret, $accessToken;
        protected $accessTokenUrl = 'https://Untappd.com/oauth2/access_token';
        protected $authorizeUrl = 'https://Untappd.com/oauth2/authorize';
        protected $apiUrl = 'http://api.untappd.com/v4';
        protected $userAgent = 'EpiUntappd (http://github.com/jmathai/Untappd-async/tree/)';
        protected $apiVersion = 'v4';
        protected $isAsynchronous = false;
        protected $followLocation = false;
        protected $connectionTimeout = 5;
        protected $requestTimeout = 30;
        protected $debug = true;

        public function getAccessToken($code, $redirectUri)
        {
            $params = array('client_id' => $this->clientId, 'client_secret' => $this->clientSecret, 'grant_type' => 'authorization_code', 'redirect_uri' => $redirectUri, 'code' => $code);
            //$qs     = http_build_query($params);

            $web_client = new \Idno\Core\Webservice();
            $content = $web_client->send('get', "{$this->accessTokenUrl}", $params);
            error_log(json_encode($content));
            return json_decode($content['content'],0);

            //return $this->request('GET', "{$this->accessTokenUrl}", $params);
        }

        public function getAuthorizeUrl($redirectUri)
        {
            $params = array('client_id' => $this->clientId, 'response_type' => 'code', 'redirect_uri' => $redirectUri);
            $qs     = http_build_query($params);

            return "{$this->requestTokenUrl}?{$qs}";
        }

        public function setAccessToken($accessToken)
        {
            $this->accessToken = $accessToken;
        }

        public function setTimeout($requestTimeout = null, $connectionTimeout = null)
        {
            if ($requestTimeout !== null)
                $this->requestTimeout = floatval($requestTimeout);
            if ($connectionTimeout !== null)
                $this->connectionTimeout = floatval($connectionTimeout);
        }

        public function setUserAgent($agent)
        {
            $this->userAgent = $agent;
        }

        public function useApiVersion($version = null)
        {
            $this->apiVersion = $version;
        }

        public function useAsynchronous($async = true)
        {
            $this->isAsynchronous = (bool)$async;
        }

        // Public api interface for most calls GET/POST/DELETE
        public function delete($endpoint, $params = null)
        {
            return $this->request('DELETE', $endpoint, $params);
        }

        public function get($endpoint, $params = null)
        {
            return $this->request('GET', $endpoint, $params);
        }

        public function post($endpoint, $params = null)
        {
            return $this->request('POST', $endpoint, $params);
        }

        public function __construct($clientId = null, $clientSecret = null, $accessToken = null)
        {
            $this->clientId     = $clientId;
            $this->clientSecret = $clientSecret;
            $this->accessToken  = $accessToken;
        }

        private function getApiUrl($endpoint)
        {
            if (!empty($this->apiVersion))
                return "{$this->apiUrl}/{$this->apiVersion}{$endpoint}";
            else
                return "{$this->apiUrl}{$endpoint}";
        }

        private function request($method, $endpoint, $params = null)
        {

            error_log("Requestiing {$method} {$endpoint} " . json_encode($params));

            if (preg_match('#^https?://#', $endpoint))
                $url = $endpoint;
            else
                $url = $this->getApiUrl($endpoint);

            if ($this->accessToken) {
                $params['oauth_token'] = $this->accessToken;
            } else {
                $params['client_id']     = $this->clientId;
                $params['client_secret'] = $this->clientSecret;
            }

            $web_client = new \Idno\Core\Webservice();
            if ($content = $web_client->send($method, $url, $params)) {
                error_log(var_export($content,true));
                $object = json_decode($content['content']);
                $object->responseText = $content['content'];
                return $object;
            }

            return false;
/*
            if ($method === 'GET')
                $url .= is_null($params) ? '' : '?' . http_build_query($params, '', '&');

            error_log($url);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            if ($method === 'POST' && $params !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }

            $resp = new \EpiUntappdJson(\EpiCurl::getInstance()->addCurl($ch), $this->debug);
            $resp->responseText;

            error_log(var_export($resp,true));

            return $resp;*/
        }
    }

    class EpiUntappdJson implements ArrayAccess, Countable, IteratorAggregate
    {
        private $debug;
        private $__resp;

        public function __construct($response, $debug = false)
        {
            $this->__resp = $response;
            $this->debug  = $debug;
        }

        // ensure that calls complete by blocking for results, NOOP if already returned
        public function __destruct()
        {
            $this->responseText;
        }

        // Implementation of the IteratorAggregate::getIterator() to support foreach ($this as $...)
        public function getIterator()
        {
            if ($this->__obj) {
                return new ArrayIterator($this->__obj);
            } else {
                return new ArrayIterator($this->response);
            }
        }

        // Implementation of Countable::count() to support count($this)
        public function count()
        {
            return count($this->response);
        }

        // Next four functions are to support ArrayAccess interface
        // 1
        public function offsetSet($offset, $value)
        {
            $this->response[$offset] = $value;
        }

        // 2
        public function offsetExists($offset)
        {
            return isset($this->response[$offset]);
        }

        // 3
        public function offsetUnset($offset)
        {
            unset($this->response[$offset]);
        }

        // 4
        public function offsetGet($offset)
        {
            return isset($this->response[$offset]) ? $this->response[$offset] : null;
        }

        public function __get($name)
        {
            $accessible         = array('responseText' => 1, 'headers' => 1, 'code' => 1);
            $this->responseText = $this->__resp->data;
            $this->headers      = $this->__resp->headers;
            $this->code         = $this->__resp->code;
            if (isset($accessible[$name]) && $accessible[$name])
                return $this->$name;
            elseif (($this->code < 200 || $this->code >= 400) && !isset($accessible[$name]))
                EpiUntappdException::raise($this->__resp, $this->debug);

            // Call appears ok so we can fill in the response
            $this->response = json_decode($this->responseText, 1);
            $this->__obj    = json_decode($this->responseText);

            if (gettype($this->__obj) === 'object') {
                foreach ($this->__obj as $k => $v) {
                    $this->$k = $v;
                }
            }

            if (property_exists($this, $name)) {
                return $this->$name;
            }

            return null;
        }

        public function __isset($name)
        {
            $value = self::__get($name);

            return !empty($name);
        }
    }

    class EpiUntappdException extends Exception
    {
        public static function raise($response, $debug)
        {
            $message = $response->data;

            switch ($response->code) {
                case 400:
                    throw new EpiUntappdBadRequestException($message, $response->code);
                case 401:
                    throw new EpiUntappdNotAuthorizedException($message, $response->code);
                case 403:
                    throw new EpiUntappdForbiddenException($message, $response->code);
                case 404:
                    throw new EpiUntappdNotFoundException($message, $response->code);
                default:
                    throw new EpiUntappdException($message, $response->code);
            }
        }
    }

    class EpiUntappdBadRequestException extends EpiUntappdException
    {
    }

    class EpiUntappdNotAuthorizedException extends EpiUntappdException
    {
    }

    class EpiUntappdForbiddenException extends EpiUntappdException
    {
    }

    class EpiUntappdNotFoundException extends EpiUntappdException
    {
    }
