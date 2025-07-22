<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'first-page-url' => $this->url(1),
                'last-page-url' => $this->url($this->lastPage()),

                'prev-page-url' => $this->previousPageUrl(),
                'next-page-url' => $this->nextPageUrl(),

            ],
            'meta' => [
                'current-page-no' => $this->currentPage(),
                'last-page-no' => $this->lastPage(),
                'total' => $this->total(),
                'per-page' => $this->perPage(),
            ]
        ];
    }
}
