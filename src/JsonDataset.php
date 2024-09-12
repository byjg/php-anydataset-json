<?php

namespace ByJG\AnyDataset\Json;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Exception\DatasetException;

class JsonDataset
{

    /**
     * @var array|null
     */
    private ?array $jsonObject;

    /**
     * JsonDataset constructor.
     * @param array|string $json
     * @throws DatasetException
     */
    public function __construct(array|string $json)
    {
        if (is_array($json)) {
            $this->jsonObject = $json;
            return;
        }

        $this->jsonObject = json_decode($json, true);

        $lastError = json_last_error();
        $lastErrorDesc = match ($lastError) {
            JSON_ERROR_NONE => 'No errors',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            default => 'Unknown error',
        };

        if ($lastError != JSON_ERROR_NONE) {
            throw new DatasetException("Invalid JSON string: " . $lastErrorDesc);
        }
    }

    /**
     * @access public
     * @param string $path
     * @param bool $throwErr
     * @return JsonIterator
     * @throws IteratorException
     */
    public function getIterator(string $path = "", bool $throwErr = false): JsonIterator
    {
        return new JsonIterator($this->jsonObject, $path, $throwErr);
    }
}
