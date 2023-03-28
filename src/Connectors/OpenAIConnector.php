<?php

namespace VigStudio\LaravelAI\Connectors;

use Exception;
use Illuminate\Support\Collection;
use OpenAI\Client;
use VigStudio\LaravelAI\Bridges\ModelBridge;
use VigStudio\LaravelAI\Contracts\Connector;
use VigStudio\LaravelAI\Enums\Provider;
use VigStudio\LaravelAI\Responses\ImageResponse;
use VigStudio\LaravelAI\Responses\MessageResponse;
use VigStudio\LaravelAI\Responses\TextResponse;

/**
 * The Connector for the OpenAI provider
 */
class OpenAIConnector implements Connector
{
    /**
     * {@inheritDoc}
     */
    public const NAME = 'openai';

    /**
     * @var int - The default max tokens for the OpenAI API
     */
    private int $defaultMaxTokens = 5;

    /**
     * @var float - The default temperature for the OpenAI API
     */
    private float $defaultTemperature = 0;

    /**
     * @param  Client  $client - The OpenAI client
     */
    public function __construct(protected Client $client)
    {
    }

    /**
     * Setter for the default max tokens
     */
    public function withDefaultMaxTokens(int $maxTokens): self
    {
        $this->defaultMaxTokens = $maxTokens;

        return $this;
    }

    /**
     * Setter for the default temperature
     */
    public function withDefaultTemperature(float $temperature): self
    {
        $this->defaultTemperature = $temperature;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function listModels(): Collection
    {
        return Collection::make($this->client->models()->list()->data)->map(function ($model) {
            return ModelBridge::new()->withProvider(Provider::OpenAI)
                ->withName($model->id ?? '')
                ->withExternalId($model->id ?? '');
        });
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function complete(string $model, string $prompt, int $maxTokens = null, float $temperature = null): TextResponse
    {
        $response = $this->client->completions()->create([
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => $maxTokens ?? $this->defaultMaxTokens,
            'temperature' => $temperature ?? $this->defaultTemperature,
        ]);

        $contents = [];

        foreach ($response->choices as $result) {
            $contents[] = $result->text;
        }

        return TextResponse::new()->withExternalId($response->id)->withMessage(
            MessageResponse::new()->withContent(implode("\n--\n", $contents))->withRole('assistant')
        );
    }

    public function completeStream(string $model, string $prompt, int $maxTokens = null, float $temperature = null): TextResponse
    {
        $stream = $this->client->completions()->createStreamed([
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => 2024,
        ]);

        $contents = '';
        $result = [];
        $id = '';
        foreach ($stream as $response) {
            $id = $response->id;
            echo $response->choices[0]->text;
            $contents .= $response->choices[0]->text;
        }

        $result[] = $contents;

        return TextResponse::new()->withExternalId($id)->withMessage(
            MessageResponse::new()->withContent(implode("\n--\n", $result))->withRole('assistant')
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function chat(string $model, array|string $messages): TextResponse
    {
        $messages = is_array($messages) ? $messages : [
            [
                'role' => 'user',
                'content' => $messages,
            ],
        ];

        $chat = $this->client->chat()->create([
            'model' => $model,
            'messages' => $messages,
        ]);

        $response = TextResponse::new()->withExternalId($chat->id);

        foreach ($chat->choices as $choice) {
            $response->withMessage(
                MessageResponse::new()->withContent($choice->message->content)->withRole($choice->message->role)
            );
        }

        return $response;
    }

    public function chatStream(string $model, array|string $messages): TextResponse
    {
        $messages = is_array($messages) ? $messages : [
            [
                'role' => 'user',
                'content' => $messages,
            ],
        ];

        $stream = $this->client->chat()->createStreamed([
            'model' => $model,
            'messages' => $messages,
        ]);

        $content = [
            'id' => '',
            'role' => '',
            'message' => '',
        ];
        foreach ($stream as $chat) {
            $content['id'] = $chat->id;

            $data = $chat->choices[0]->toArray();
            if (! empty($data['delta']['role'])) {
                $content['role'] = $data['delta']['role'];
            } elseif (! empty($data['delta']['content'])) {
                $content['message'] .= $data['delta']['content'];
                echo $data['delta']['content'];
            }
        }

        $response = TextResponse::new()->withExternalId($content['id']);

        $response->withMessage(
            MessageResponse::new()->withContent($content['message'])->withRole($content['role'])
        );

        return $response;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function imageGenerate(string $prompt, int $width, int $height): ImageResponse
    {
        $response = $this->client->images()->create([
            'prompt' => $prompt,
            'n' => 1,
            'size' => sprintf('%dx%d', $width, $height),
            'response_format' => 'url',
        ]);

        $url = null;

        foreach ($response->data as $data) {
            $url = $data->url;
            // $data->b64_json; // null
        }

        return ImageResponse::new()->withCreatedAt($response->created)->withUrl($url);
    }
}
