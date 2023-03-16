<?php

namespace Illegal\LaravelAI\Bridges;

use Illegal\LaravelAI\Contracts\Bridge;
use Illegal\LaravelAI\Contracts\HasConnector;
use Illegal\LaravelAI\Enums\Connectors;
use Illegal\LaravelAI\Models\Model;

final class ModelBridge implements Bridge
{
    use HasConnector;

    public string $externalId;
    public string $name;

    public function withExternalId(string $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'name'        => $this->name,
        ];
    }

    public function import(): Model
    {
        return Model::updateOrCreate([
            'external_id' => $this->externalId,
            'connector'   => $this->connector
        ], array_merge(
            $this->toArray(),
            [
                'connector' => $this->connector,
                'is_active' => true,
            ]
        ));
    }
}
