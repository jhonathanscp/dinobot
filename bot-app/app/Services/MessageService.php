<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MessageService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    protected $commands = [
        'help' => HelpService::class,
        'fig'  => StickerService::class,
    ];

    public function processMessage($messageData)
    {
        $prefix = config('app.prefix');

        $messageText = data_get($messageData, 'data.message.conversation')
            ?? data_get($messageData, 'data.message.extendedTextMessage.text')
            ?? data_get($messageData, 'data.message.imageMessage.caption')
            ?? data_get($messageData, 'data.message.videoMessage.caption')
            ?? '';

        $messageAfterSplit = preg_split("/(" . preg_quote($prefix) . ")/", $messageText, 2, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (str_contains(' ', $messageAfterSplit[1])) {
            $arrayOfCmds = explode(" ", $messageAfterSplit[1]);

            array_pop($messageAfterSplit);

            $messageAfterSplit = [...$messageAfterSplit, ...$arrayOfCmds];
        }

        $commandName = $messageAfterSplit[1];

        if (!isset($this->commands[$commandName])) {
            return app(HelpService::class)->execute($messageAfterSplit, $messageData);
        }

        return app($this->commands[$commandName])->execute($messageAfterSplit, $messageData);
    }
}
