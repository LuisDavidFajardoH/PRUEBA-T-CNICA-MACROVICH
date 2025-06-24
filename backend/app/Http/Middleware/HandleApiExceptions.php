<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class HandleApiExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Handle the given exception.
     */
    protected function handleException(Request $request, Throwable $e): JsonResponse
    {
        // Only handle API requests
        if (!$request->is('api/*')) {
            throw $e;
        }

        $response = $this->prepareJsonResponse($e);

        return response()->json($response['data'], $response['status']);
    }

    /**
     * Prepare the JSON response for the given exception.
     */
    protected function prepareJsonResponse(Throwable $e): array
    {
        return match (true) {
            $e instanceof ValidationException => [
                'data' => [
                    'message' => 'Los datos proporcionados no son válidos.',
                    'error' => 'validation_failed',
                    'errors' => $e->errors(),
                ],
                'status' => 422
            ],
            
            $e instanceof AuthenticationException => [
                'data' => [
                    'message' => 'No autenticado.',
                    'error' => 'unauthenticated',
                ],
                'status' => 401
            ],
            
            $e instanceof ModelNotFoundException => [
                'data' => [
                    'message' => 'Recurso no encontrado.',
                    'error' => 'resource_not_found',
                ],
                'status' => 404
            ],
            
            $e instanceof NotFoundHttpException => [
                'data' => [
                    'message' => 'Endpoint no encontrado.',
                    'error' => 'endpoint_not_found',
                ],
                'status' => 404
            ],
            
            $e instanceof MethodNotAllowedHttpException => [
                'data' => [
                    'message' => 'Método HTTP no permitido.',
                    'error' => 'method_not_allowed',
                ],
                'status' => 405
            ],
            
            default => [
                'data' => [
                    'message' => 'Error interno del servidor.',
                    'error' => 'internal_server_error',
                    'details' => app()->environment('local') ? [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ],
                'status' => 500
            ]
        };
    }
}
