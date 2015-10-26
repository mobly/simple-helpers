<?php

namespace SimpleHelpers;

class Rest
{
    /**
     * @param string $method
     * @param string $url
     * @param string $parameterList
     * @param array $headerList
     *
     * @return \stdClass
     */
    static public function json($method, $url, $parameterList = '', array $headerList = [])
    {
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
                $url .= '?' . http_build_query((array) $parameterList);
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
            var_dump(
                $parameterList,
                $result,
                $information,
                $error
            );
        }

        if (!empty($information['http_code']) && $information['http_code'] >= 400) {
            var_dump(
                $parameterList,
                $result,
                $information
            );
        }

        return json_decode($result);
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
            'GET',
            $url,
            $parameterList,
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
                throw new \Exception('Error: ' . var_export($json, true));
            }

            foreach ($json->issues as $issue) {
                $issueList[] = $issue;
            }
        }

        return $issueList;
    }
}