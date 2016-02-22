<?php

namespace SimpleHelpers;

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
    const ERROR_CURL_RESPONSE = 101;

    /**
     * string error of http return error
     */
    const ERROR_CURL_HTTP_CODE = 102;

    /**
     * @param string $url
     * @param string $parameterList
     * @param string $method
     * @param array $headerList
     * @param boolean $assoc
     *
     * @return \stdClass
     */
    static public function json(
        $url,
        $parameterList = '',
        $method = self::HTTP_METHOD_GET,
        array $headerList = [],
        $assoc = false
    ) {
        $curl = curl_init();

        $headerList[] = 'Content-Type: application/json;charset=UTF-8';

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerList);

        switch ($method) {
            case 'GET':
                $parameterList = (array) $parameterList;
                $url .= (count($parameterList) > 0) ? '?' . http_build_query($parameterList) : '';
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $parameterList);
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $result = curl_exec($curl);
        $information = curl_getinfo($curl);
        $error = curl_error($curl);

        if ($error) {
            self::getException(
                [
                    $parameterList,
                    $result,
                    $information,
                    $error
                ],
                self::ERROR_CURL_RESPONSE
            );
        }

        if (!empty($information['http_code']) && $information['http_code'] >= 400) {
            self::getException(
                [
                    $parameterList,
                    $result,
                    $information
                ],
                self::ERROR_CURL_HTTP_CODE
            );
        }

        return json_decode($result, $assoc);
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
    static protected function getException(array $exception, $code = 500)
    {
        throw new \Exception(
            'Curl exception: ' . var_export($exception, true),
            $code
        );
    }
}