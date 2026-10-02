<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Captcha;

use Reyhan\Core\Rules\ValidPoWChallenge;
use Illuminate\Foundation\Http\FormRequest;

final class SolveCaptchaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'nonce' => ['required', 'string', new ValidPoWChallenge],
            'elapsed_ms' => ['required', 'integer', 'min:0'],
        ];
    }
}
