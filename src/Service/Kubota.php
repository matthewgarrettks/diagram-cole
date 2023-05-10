<?php

namespace App\Service;

use App\Entity\FetchResponse;
use App\Service\Log;

class Kubota
{

    private ?string $authValue = null;

    private int $roleId = 23;

    private string $lang = 'en';

    private string $origin = 'https://kpad.kubota.com';

    private int $consecutiveErrorCount = 0;

    private \DateTimeImmutable $lastAuth;

    private string $contentType = 'application/json';

    private string $consumerCode = '100faiXie8u';

    private string $endpoint_books = 'api/books';

    private array $errors;

    /**
     * @var \App\Service\Log
     */
    private \App\Service\Log $log;

    public function __construct(\App\Service\Log $log)
    {
        $this->log = $log;
    }

    /**
     * Get JSON from Kubota's API
     * @param  string  $url
     * @param  bool  $allowReAuthentication  No need to set. Prevents recursion.
     * @return Exception|ScrapeResponse
     */
    public function fetch(string $url, bool $allowReAuthentication = true): FetchResponse|\Exception
    {
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-type: application/json"]);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
              'Authorization: ' . $this->authValue,
              'kubotapad-role-id: ' . $this->roleId,
              'KubotaPAD-Language-Code: ' . $this->lang,
            ]);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if (200 !== $status) {
                $this->consecutiveErrorCount++;
            } else {
                $this->consecutiveErrorCount = 0;
            }
            if ($this->consecutiveErrorCount > 9) {
                $this->log->log('Too many bad responses. Stopping.');
                $this->log->end();
            }
            if ((true === $allowReAuthentication) && (200 !== $status) && (true === $this->authenticate())) {
                return $this->fetch($url, false);
            }
            return new FetchResponse($status, $json_response);
        } catch (\Exception $e) {
            $this->log->log("FAIL fetching $url");
            return $e;
        }
    }

    /**
     * Authenticates against the Kubota server
     * @return bool
     */
    private function authenticate(): bool
    {
        if (isset($this->lastAuth) && time() - $this->lastAuth->getTimestamp() < 300) {
            return true;
        }
        $result = $this->authRaw();
        if (true === $result) {
            return true;
        }
        $this->log->log('!!! Auth error, trying again !!!');
        sleep(5);
        $result = $this->authRaw();
        if (true === $result) {
            return true;
        }
        $this->log('!!! Second attempt auth error: ' . $result->getMessage() . ' !!!');
        $this->log->end();
        return false;
    }

    /**
     * Uses curl to perform the authentication
     * @return bool|\Exception
     */
    private function authRaw(): bool|\Exception
    {
        try {
            $curl = curl_init('https://kpad.kubota.com/api/token/consumer');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
              'Content-type: ' . $this->contentType,
              'Origin: ' . $this->origin,
              'KubotaPAD-Language-Code: ' . $this->lang,
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, '{"consumer_code":"' . $this->consumerCode . '"}');
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if (200 !== $status) {
                throw new \Exception('Response code other than 200: ' . var_export($status, true));
            }
            if (empty($json_response)) {
                throw new \Exception('Empty response body');
            }
            $data = json_decode($json_response);
            if (!property_exists($data, 'access_token')) {
                throw new \Exception('No access token');
            }
            $this->authValue = 'Bearer ' . $data->access_token;
            $this->lastAuth = new \DateTimeImmutable();
            $this->log->log('*** Authorized ***');
            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getAllBooksBeginningWith($begin, $offset = 0): ?\stdClass
    {
        $url = $this->origin . '/' . $this->endpoint_books . '?model_name=' . $begin . '&match_condition=prefix&offset=' . $offset . '&sort=model_name&order=ASC';
        $this->log->log("Querying $url\n");
        $data = $this->fetch($url);
        if (empty($data->getData()) || 200 !== $data->getCode()) {
            $this->log->log('!!! Boop !!! Failed getting models starting with ' . $begin . ', offset ' . $offset, [], true);
            return null;
        }
        return json_decode($data->getData());
    }

    public function getBookIdsFor($text): array
    {
        $data = $this->getAllBooksBeginningWith($text);
        $return = [];
        foreach ($data as $books) {
            if (is_array($books)) {
                foreach ($books as $book) {
                    $return[] = $book->book_id;
                }
            }
        }
        return $return;
    }

}
