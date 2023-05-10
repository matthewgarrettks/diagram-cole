<?php

namespace App\Service;

class Log
{

    /**
     * Echoes the message out to the screen, and if its an error, stores it in the error array
     * @param  string  $message
     * @param  array  $scrapePath
     * @param  bool  $isError
     * @return void
     */
    public function log(string $message, array $scrapePath = [], bool $isError = false): void
    {
        $prefix = implode(' -> ', $scrapePath);
        echo $prefix . ' -> ' . $message . PHP_EOL;
        if (true === false) {
            $this->errors[] = ['path' => $scrapePath, 'message' => $message];
        }
    }


    /**
     * Echoes out the end results and dies
     * @return void
     */
    public function end(): void
    {
        echo PHP_EOL;
        echo sprintf('%d books, %d figures, %d illustrations added', $this->books_added, $this->figures_added, $this->illustrations_added) . PHP_EOL;
        echo PHP_EOL;
        echo '!!!!!!!!!!!!!!!!!!!!!!' . PHP_EOL;
        echo '!!! ' . count($this->errors) . ' missed data !!!' . PHP_EOL;
        echo '!!!!!!!!!!!!!!!!!!!!!!' . PHP_EOL;
        foreach ($this->errors as $msg) {
            echo implode(' -> ', $msg['path']) . ' -> ' . $msg['message'] . PHP_EOL;
        }
        die();
    }

}
