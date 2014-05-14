<?php

    /**
     * Utility methods for handling external web services
     *
     * @package known
     * @subpackage core
     */

    namespace known\Core {

        class Webservice extends \known\Common\Component
        {

            /**
             * Send a web services request to a specified endpoint
             * @param string $verb The verb to send the request with; one of POST, GET, DELETE, PUT
             * @param string $endpoint The URI to send the request to
             * @param array $params Optionally, an array of parameters to send (keys are the parameter names)
             * @param array $headers Optionally, an array of headers to send with the request (keys are the header names)
             * @return array
             */
            static function send($verb, $endpoint, array $params = null, array $headers = null)
            {
                $req = "";
                if ($params) {
                    $req = http_build_query($params);
                }

                $curl_handle = curl_init();

                switch (strtolower($verb)) {
                    case 'post':
                        curl_setopt($curl_handle, CURLOPT_POST, 1);
                        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $req);
                        break;
                    case 'delete':
                        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'DELETE'); // Override request type
                        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $req);
                        break;
                    case 'put':
                        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT'); // Override request type
                        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $req);
                        break;
                    case 'get':
                    default:
                        curl_setopt($curl_handle, CURLOPT_HTTPGET, true);
                        if (strpos($endpoint, '?') !== false) {
                            $endpoint .= '&' . $req;
                        } else {
                            $endpoint .= '?' . $req;
                        }
                        break;
                }

                curl_setopt($curl_handle, CURLOPT_URL, $endpoint);
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_handle, CURLOPT_USERAGENT, "known http://known.co");
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);

                // Allow plugins and other services to extend headers, allowing for plugable authentication methods on calls
                $new_headers = \known\Core\site()->triggerEvent('webservice:headers', ['headers' => $headers, 'verb' => $verb]);
                if (!empty($new_headers) && (is_array($new_headers))) {
                    if (empty($headers)) $headers = [];
                    $headers = array_merge($headers, $new_headers);
                }

                if (!empty($headers) && is_array($headers)) {
                    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
                }

                $buffer      = curl_exec($curl_handle);
                $http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

                if ($error = curl_error($curl_handle)) {
                    error_log($error);
                }

                curl_close($curl_handle);

                return ['content' => $buffer, 'response' => $http_status, 'error' => $error];
            }

            /**
             * Send a web services GET request to a specified URI endpoint
             * @param string $endpoint The URI to send the GET request to
             * @param array $params Optionally, an array of parameters to send (keys are the parameter names)
             * @param array $headers Optionally, an array of headers to send with the request (keys are the header names)
             * @return array
             */
            static function get($endpoint, array $params = null, array $headers = null)
            {
                return self::send('get', $endpoint, $params, $headers);
            }

            /**
             * Send a web services POST request to a specified URI endpoint
             * @param string $endpoint The URI to send the POST request to
             * @param array $params Optionally, an array of parameters to send (keys are the parameter names)
             * @param array $headers Optionally, an array of headers to send with the request (keys are the header names)
             * @return array
             */
            static function post($endpoint, array $params = null, array $headers = null)
            {
                return self::send('post', $endpoint, $params, $headers);
            }

            /**
             * Send a web services PUT request to a specified URI endpoint
             * @param string $endpoint The URI to send the PUT request to
             * @param array $params Optionally, an array of parameters to send (keys are the parameter names)
             * @param array $headers Optionally, an array of headers to send with the request (keys are the header names)
             * @return array
             */
            static function put($endpoint, array $params = null, array $headers = null)
            {
                return self::send('put', $endpoint, $params, $headers);
            }

            /**
             * Send a web services DELETE request to a specified URI endpoint
             * @param string $endpoint The URI to send the DELETE request to
             * @param array $params Optionally, an array of parameters to send (keys are the parameter names)
             * @param array $headers Optionally, an array of headers to send with the request (keys are the header names)
             * @return array
             */
            static function delete($endpoint, array $params = null, array $headers = null)
            {
                return self::send('delete', $endpoint, $params, $headers);
            }

        }

    }