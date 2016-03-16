<?php

namespace SimpleHelpers;

use SimpleHelpers\Http\StatusCode;

class Rest
{
    /**
     * string method get
     */
    const HTTP_METHOD_GET = 'GET';

    /**
     * string method post
     */
    const HTTP_METHOD_POST = 'POST';

    /**
     * string error of curl response
     */
    const ERROR_CURL_CODE = 101;

    /**
     * string error of http return error
     */
    const ERROR_CURL_HTTP_CODE = 102;

    /**
     * json parse error
     */
    const ERROR_CURL_JSON = 103;

    /**
     * @param string $url
     * @param string|array $parameterList
     * @param string $method
     * @param array $headerList
     * @param boolean $associative
     * @param array $optionList
     * @param integer $retries
     *
     * @return array|\stdClass
     *
     * @throws \Exception
     */
    static public function json(
        $url,
        $parameterList = '',
        $method = self::HTTP_METHOD_GET,
        array $headerList = [],
        $associative = false,
        array $optionList = [],
        $retries = 5
    ) {
        $headerList[] = 'Content-Type: application/json;charset=UTF-8';

        $defaultOptionList = [
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headerList,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_CONNECTTIMEOUT_MS => 30 * 1000,
            CURLOPT_TIMEOUT_MS => 600 * 1000,
        ];

        switch ($method) {
            case self::HTTP_METHOD_GET:
                $parameterList = (array) $parameterList;
                $parameters = '';
                foreach ($parameterList as $key => $value) {
                    if (is_array($value)) {
                        $parameters .= (empty($parameters) ? '' : '&') . self::normalizeArrayValues($key, $value);

                        unset($parameterList[$key]);
                    }
                }

                if (count($parameterList) > 0) {
                    $parameters .= (empty($parameters) ? '' : '&') . http_build_query($parameterList);
                }

                if ($parameters) {
                    $url .= '?' . $parameters;
                }
                break;
            case self::HTTP_METHOD_POST:
                $optionList[CURLOPT_POST] = true;
                $optionList[CURLOPT_POSTFIELDS] = $parameterList;
                break;
        }

        $optionList[CURLOPT_URL] = $url;

        $curl = curl_init();

        curl_setopt_array($curl, $optionList + $defaultOptionList);

        $tries = 0;

        do {
            $timeStart = microtime(true);
            $response = curl_exec($curl);
            $executionTime = microtime(true) - $timeStart;
            $information = curl_getinfo($curl);
            $errorCode = curl_errno($curl);
            $tries++;
        } while ($errorCode === CURLE_OPERATION_TIMEOUTED && $tries < $retries);

        if ($errorCode) {
            self::getException(
                [
                    'errorCode' => $errorCode,
                    'error' => curl_error($curl),
                    'executionTime' => $executionTime,
                    'tries' => $tries,
                    'optionList' => $optionList,
                    'response' => $response,
                    'information' => $information,
                ],
                self::ERROR_CURL_CODE
            );
        }

        if (!empty($information['http_code']) && $information['http_code'] >= StatusCode::BAD_REQUEST) {
            self::getException(
                [
                    'executionTime' => $executionTime,
                    'tries' => $tries,
                    'optionList' => $optionList,
                    'response' => $response,
                    'information' => $information,
                ],
                self::ERROR_CURL_HTTP_CODE
            );
        }

        $data = json_decode($response, $associative);

        if (false === $data) {
            self::getException(
                [
                    'executionTime' => $executionTime,
                    'tries' => $tries,
                    'optionList' => $optionList,
                    'response' => $response,
                    'information' => $information,
                ]
            );
        }

        $data['curl'] = [
            'errorCode' => $errorCode,
            'error' => curl_error($curl),
            'executionTime' => $executionTime,
            'tries' => $tries,
            'optionList' => $optionList,
            'information' => $information,
        ];

        return $data;
    }

    /**
     * @param string $key
     * @param array $valueList
     *
     * @return string
     */
    static protected function normalizeArrayValues($key, array $valueList)
    {
        $normalized = '';
        $equal = $key . '=';
        foreach ($valueList as $value) {
            $normalized .= (empty($normalized) ? '' : '&') . $equal . urlencode($value);
        }

        return $normalized;
    }

    /**
     * @param string $url
     * @param string $user
     * @param array $parameterList
     *
     * @return \stdClass
     */
    static public function jiraGet($url, $user, array $parameterList = [])
    {
        return self::json(
            $url,
            $parameterList,
            self::HTTP_METHOD_GET,
            [
                'Authorization: Basic ' . $user
            ]
        );
    }

    /**
     * @param array $configuration
     * @param array $keyList
     * @param array $parameterList
     *
     * @return array
     *
     * @throws \Exception
     */
    static public function jiraGetIssueList(array $configuration, array $keyList, array $parameterList = [])
    {
        $issueList = [];

        if (empty($keyList)) {
            return $issueList;
        }

        foreach (array_chunk($keyList, 50) as $chunk) {
            $parameterList['jql'] = 'key IN(' . implode(', ', $chunk) . ')';

            $json = self::jiraGet(
                $configuration['host'] . $configuration['endpoint']['search'],
                $configuration['loginHash'],
                $parameterList
            );

            if (!isset($json->issues)) {
                self::getException(
                    var_export($json, true)
                );
            }

            foreach ($json->issues as $issue) {
                $issueList[] = $issue;
            }
        }

        return $issueList;
    }

    /**
     * @param array $configuration
     * @param string $key
     * @param integer $transitionId
     *
     * @return \stdClass
     */
    static public function jiraPostIssueTransition(array $configuration, $key, $transitionId)
    {
        return self::json(
            $configuration['host']
                . sprintf(
                    $configuration['endpoint']['transition'],
                    $key
                ),
            json_encode([
                'transition' => [
                    'id' => $transitionId,
                ],
            ]),
            self::HTTP_METHOD_POST,
            [
                'Authorization: Basic ' . $configuration['loginHash']
            ]
        );
    }

    /**
     * @param array $exception
     * @param int $code
     *
     * @throws \Exception
     */
    static protected function getException(array $exception, $code = StatusCode::INTERNAL_SERVER_ERROR)
    {
        throw new \Exception(
            'Curl exception: ' . var_export($exception, true),
            $code
        );
    }
}