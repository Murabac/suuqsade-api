<?php

namespace App\Http\Requests\Api;

use App\Rules\ProductLink;
use App\Support\ProductLinkNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BatchOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('links') && is_array($this->input('links'))) {
            $this->merge([
                'links' => array_map(
                    fn (mixed $link) => ProductLinkNormalizer::normalize((string) $link),
                    $this->input('links'),
                ),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'links' => ['required', 'array', 'min:1', 'max:10'],
            'links.*' => ['required', 'string', 'max:2000', new ProductLink],
            'notes' => ['sometimes', 'array'],
            'notes.*' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->has('notes')) {
                return;
            }

            $links = $this->input('links', []);
            $notes = $this->input('notes', []);

            if (count($links) !== count($notes)) {
                $validator->errors()->add(
                    'notes',
                    'Each item must include a matching variant note entry.',
                );
            }
        });
    }
}
