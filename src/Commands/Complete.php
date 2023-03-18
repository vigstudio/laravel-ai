<?php

namespace Illegal\LaravelAI\Commands;

use Illegal\LaravelAI\Bridges\CompletionBridge;
use Illegal\LaravelAI\Contracts\ConsoleProviderDependent;
use Illuminate\Console\Command;

class Complete extends Command
{
    use ConsoleProviderDependent;

    protected $signature = 'ai:complete';

    protected $description = 'Use the AI to complete your prompt';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $provider = $this->askForProvider();

        while(1) {
            $message = $this->ask('You');
            if ($message === 'exit') {
                break;
            }
            $this->info(
                'AI: ' .
                CompletionBridge::new()
                    ->withProvider($provider)
                    ->withModel('text-davinci-003')
                    ->complete($message)
            );
        }
    }
}
