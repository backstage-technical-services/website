<?php
declare(strict_types=1);

namespace App\Helpers;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;
use function is_bool;
use function is_scalar;
use function var_export;

/**
 * Formats records into a logfmt string.
 *
 * @see https://brandur.org/logfmt
 * @see https://godoc.org/github.com/kr/logfmt
 *
 * @author Peter Thompson <peter.thompson@dunelm.org.uk>
 */
class MonologLogFmtFormatter extends NormalizerFormatter
{
    private string $timeKey = 'ts';
    private string $lvlKey = 'level';
    private string $msgKey = 'msg';

    public function __construct()
    {
        parent::__construct('Y-m-d\TH:i:s.vp');
    }

    /**
     * {@inheritdoc}
     */
    public function format(LogRecord $record)
    {
        $vars = parent::format($record);

        $pairs = [
            'service' => 'php',
            $this->timeKey => $vars['datetime'],
            $this->lvlKey => strtolower($vars['level_name']),
            $this->msgKey => $vars['message']
        ];

        foreach ($vars['context'] as $ctxKey => $ctxVal) {
            if (array_key_exists($ctxKey, $pairs)) {
                continue;
            }
            if (!$this->isValidIdent($ctxKey)) {
                continue;
            }
            $pairs[$ctxKey] = $ctxVal;
        }

        foreach ($vars['extra'] as $extraKey => $extraVal) {
            if (array_key_exists($extraKey, $pairs)) {
                continue;
            }
            if (!$this->isValidIdent($extraKey)) {
                continue;
            }
            $pairs[$extraKey] = $extraVal;
        }

        return implode(' ', array_map(fn($k, $v) => $k . '=' . $this->stringifyVal($v), array_keys($pairs), array_values($pairs))) . "\n";
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    protected function stringifyVal($val): string
    {
        if ($this->isValidIdent($val)) {
            return (string)$val;
        }

        return $this->convertToString($val);
    }

    protected function isValidIdent($val): bool
    {
        if (is_string($val)) {
            // Control chars, DEL, ", =, space
            if (preg_match('/[\x00-\x1F\x7F\"\=\s]/', $val)) {
                return false;
            }

            return $val !== '';
        }

        if (is_bool($val)) {
            return false;
        }

        return is_scalar($val);
    }

    protected function convertToString($data): string
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        return $this->toJson($data, true);
    }
}
