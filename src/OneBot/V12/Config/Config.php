<?php

declare(strict_types=1);

namespace OneBot\V12\Config;

use OneBot\V12\Exception\OneBotException;
use OneBot\V12\Utils;

class Config extends \Noodlehaus\Config implements ConfigInterface
{
    public function __construct($configPath)
    {
        parent::__construct($configPath);

        $this->validateConfig();
    }

    public function getEnabledCommunications(): array
    {
        $enabled = [];
        foreach ($this->get('communications', []) as $type => $config) {
            if (isset($config['enable']) && $config['enable'] === false) {
                continue;
            }
            if ($type === 'http' && ($config['enable'] ?? false) === true) {
                $enabled[] = [
                    'type' => $type,
                    'host' => $config['host'] ?? '127.0.0.1',
                    'port' => $config['port'] ?? 9600,
                    'access_token' => $config['access_token'] ?? null,
                ];
            } elseif ($type === 'http_webhook' && Utils::isAssocArray($config) && ($config['enable'] ?? false) === true) {
                $enabled[] = [
                    'type' => $type,
                    'url' => $config['url'],
                    'access_token' => $config['access_token'] ?? null,
                ];
            } elseif ($type === 'http_webhooks' && !Utils::isAssocArray($config)) {
                foreach ($config as $vs) {
                    if (($vs['enable'] ?? false) === true) {
                        $enabled[] = [
                            'type' => 'http_webhook',
                            'url' => $vs['url'],
                            'access_token' => $vs['access_token'] ?? null,
                        ];
                    }
                }
            } elseif ($type === 'ws_reverse' && Utils::isAssocArray($config) && ($config['enable'] ?? false) === true) {
                $enabled[] = [
                    'type' => $type,
                    'url' => $config['url'],
                    'access_token' => $config['access_token'] ?? null,
                ];
            } elseif ($type === 'ws_reverses' && !Utils::isAssocArray($config)) {
                foreach ($config as $ks => $vs) {
                    if (($vs['enable'] ?? false) === true) {
                        $enabled[] = [
                            'type' => 'ws_reverse',
                            'url' => $vs['url'],
                            'access_token' => $vs['access_token'] ?? null,
                        ];
                    }
                }
            } elseif ($type === 'ws' && ($config['enable'] ?? false) === true) {
                $enabled[] = [
                    'type' => $type,
                    'host' => $config['host'] ?? '127.0.0.1',
                    'port' => $config['port'] ?? 9600,
                    'access_token' => $config['access_token'] ?? null,
                ];
            } else {
                throw new OneBotException(sprintf('Unsupported communication [%s].', $type));
            }
        }
        return $enabled;
    }

    protected function validateConfig()
    {
        $pattern = [
            'communications' => [
                'http?' => [
                    'enable' => ':bool',
                    'host' => ":string :regexp('#((2(5[0-5]|[0-4]\\d))|[0-1]?\\d{1,2})(\\.((2(5[0-5]|[0-4]\\d))|[0-1]?\\d{1,2})){3}#')",
                    'port' => ':number :int',
                    'access_token?' => ':string',
                    'event_enabled' => ':bool',
                    'event_buffer_size' => ':number :int',
                ],
                'http_webhook?' => [
                    'enable' => ':bool',
                    'url' => ':string :url',
                    'access_token?' => ':string',
                    'timeout' => ':number :int',
                ],
                'http_webhooks?' => [
                    '*' => [
                        'enable' => ':bool',
                        'url' => ':string :url',
                        'access_token?' => ':string',
                        'timeout' => ':number :int',
                    ],
                ],
                'ws_reverse?' => [
                    'enable' => ':bool',
                    'url' => ':string :url',
                    'access_token?' => ':string',
                    'reconnected_interval' => ':number :int',
                ],
                'ws_reverses?' => [
                    '*' => [
                        'enable' => ':bool',
                        'url' => ':string :url',
                        'access_token?' => ':string',
                        'reconnected_interval' => ':number :int',
                    ],
                ],
                'ws?' => [
                    'enable' => ':bool',
                    'host' => ":string :regexp('#((2(5[0-5]|[0-4]\\d))|[0-1]?\\d{1,2})(\\.((2(5[0-5]|[0-4]\\d))|[0-1]?\\d{1,2})){3}#')",
                    'port' => ':number :int',
                    'access_token?' => ':string',
                ],
            ],
        ];

        Utils::validateArray($pattern, $this->all());
    }
}
