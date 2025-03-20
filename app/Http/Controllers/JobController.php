<?php

namespace App\Http\Controllers;

use App\Services\JobFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\RateLimiter;

class JobController extends BaseController
{
    /**
     * Get filtered jobs with pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Rate limiting
            if (!$this->checkRateLimit($request)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Too many requests',
                    'error' => 'Please try again later'
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }

            // Validate the filter parameter
            $validator = Validator::make($request->all(), [
                'filter' => 'nullable|string|max:1000',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid parameters',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            // Parse filters
            try {
                $filters = $this->parseFilters($request->get('filter', ''));
            } catch (Exception $e) {
                Log::error('Filter parsing error: ' . $e->getMessage());
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid filter format',
                    'error' => $e->getMessage()
                ], Response::HTTP_BAD_REQUEST);
            }

            // Apply filters and get paginated results
            try {
                $perPage = $request->get('per_page', 20);
                $jobs = (new JobFilterService($filters))->apply()->paginate($perPage);

                return new JsonResponse([
                    'success' => true,
                    'data' => $jobs->items(),
                    'meta' => [
                        'current_page' => $jobs->currentPage(),
                        'last_page' => $jobs->lastPage(),
                        'per_page' => $jobs->perPage(),
                        'total' => $jobs->total()
                    ]
                ], Response::HTTP_OK);

            } catch (QueryException $e) {
                Log::error('Database query error: ' . $e->getMessage());
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Error executing database query',
                    'error' => 'Invalid filter criteria or database error'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch (Exception $e) {
            Log::error('Unexpected error in JobController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return new JsonResponse([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check rate limit for the request
     *
     * @param Request $request
     * @return bool
     */
    protected function checkRateLimit(Request $request): bool
    {
        $key = $request->ip();
        $maxAttempts = 60; // 60 requests
        $decayMinutes = 1; // per minute

        return !RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Parse filter string into array structure
     *
     * @param string $filterString
     * @return array
     * @throws Exception
     */
    protected function parseFilters(string $filterString): array
    {
        if (empty($filterString)) {
            return [];
        }

        try {
            $filters = [];
            $parts = explode(' AND ', $filterString);

            foreach ($parts as $part) {
                $part = trim($part, '()');

                if (empty($part)) {
                    continue;
                }

                // Handle OR conditions
                if (strpos($part, ' OR ') !== false) {
                    $orParts = explode(' OR ', $part);
                    $orFilters = [];

                    foreach ($orParts as $orPart) {
                        $orPart = trim($orPart);
                        if (strpos($orPart, 'attribute:') === 0) {
                            $this->parseAttributeFilter($orPart, $orFilters);
                        } else {
                            $this->parseRegularFilter($orPart, $orFilters);
                        }
                    }

                    // Add OR filters to the main filters array
                    foreach ($orFilters as $field => $filter) {
                        if (!isset($filters[$field])) {
                            $filters[$field] = [
                                'operator' => 'OR',
                                'value' => []
                            ];
                        }
                        $filters[$field]['value'][] = $filter['value'];
                    }
                } else {
                    // Handle regular AND conditions
                    if (strpos($part, 'attribute:') === 0) {
                        $this->parseAttributeFilter($part, $filters);
                    } else {
                        $this->parseRegularFilter($part, $filters);
                    }
                }
            }

            return $filters;

        } catch (Exception $e) {
            Log::error('Filter parsing error: ' . $e->getMessage());
            throw new Exception('Error parsing filter: ' . $e->getMessage());
        }
    }

    /**
     * Parse attribute filter
     *
     * @param string $filter
     * @param array &$filters
     * @throws Exception
     */
    protected function parseAttributeFilter(string $filter, array &$filters): void
    {
        try {
            $filter = substr($filter, 10); // Remove 'attribute:'
            if (!preg_match('/^(\w+)(>=|<=|=|!=|>|<|LIKE)(.+)$/', $filter, $matches)) {
                throw new Exception("Invalid attribute filter format: $filter");
            }

            $attributeName = $matches[1];
            $operator = $matches[2];
            $value = trim($matches[3]);

            // Validate operator
            $validOperators = ['>=', '<=', '=', '!=', '>', '<', 'LIKE'];
            if (!in_array($operator, $validOperators)) {
                throw new Exception("Invalid operator '$operator' in attribute filter");
            }

            // Handle boolean values
            if (strtolower($value) === 'true') {
                $value = '1';
            } elseif (strtolower($value) === 'false') {
                $value = '0';
            }
            // Handle JSON array values
            elseif (strpos($value, '[') === 0 && strpos($value, ']') === strlen($value) - 1) {
                // Remove quotes from the JSON string if present
                $value = str_replace('"', '', $value);
                $value = str_replace("'", '', $value);
                $value = explode(',', trim($value, '[]'));
                $value = array_map('trim', $value);
            }

            $filters['attributes'][$attributeName] = [
                'operator' => $operator,
                'value' => $value
            ];

        } catch (Exception $e) {
            throw new Exception("Error in attribute filter '$filter': " . $e->getMessage());
        }
    }

    /**
     * Parse regular filter
     *
     * @param string $filter
     * @param array &$filters
     * @throws Exception
     */
    protected function parseRegularFilter(string $filter, array &$filters): void
    {
        try {
            if (!preg_match('/^(\w+)\s*(>=|<=|=|!=|>|<|LIKE|HAS_ANY|IS_ANY|EXISTS)\s*\(?(.*?)\)?$/', $filter, $matches)) {
                throw new Exception("Invalid filter format: $filter");
            }

            $field = $matches[1];
            $operator = $matches[2];
            $value = trim($matches[3], '()');

            // Validate operator
            $validOperators = ['>=', '<=', '=', '!=', '>', '<', 'LIKE', 'HAS_ANY', 'IS_ANY', 'EXISTS'];
            if (!in_array($operator, $validOperators)) {
                throw new Exception("Invalid operator '$operator' in filter");
            }

            // Handle array values for specific operators
            if (in_array($operator, ['HAS_ANY', 'IS_ANY'])) {
                if (empty($value)) {
                    throw new Exception("Empty value not allowed for operator '$operator'");
                }
                $value = explode(',', $value);
            }

            $filters[$field] = [
                'operator' => $operator,
                'value' => $value
            ];

        } catch (Exception $e) {
            throw new Exception("Error in filter '$filter': " . $e->getMessage());
        }
    }
}
