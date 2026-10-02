<?php

declare(strict_types=1);

namespace Reyhan\Core\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpThrottledException extends Exception
{
    public function __construct(
        public int $secondsRemaining,
        string $message = ''
    ) {
        $msg = $message ?: __('Please wait :seconds seconds before requesting another code.', ['seconds' => $secondsRemaining]);
        parent::__construct($msg, 429);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => [
                'seconds_remaining' => $this->secondsRemaining,
            ],
        ], 429);
    }
}
