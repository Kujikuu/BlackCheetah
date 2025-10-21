<?php

namespace App\Http\Controllers\Api\V1;

abstract class BaseResourceController extends Controller
{
    /**
     * Parse sorting parameters from request
     * Handles both string and JSON object formats
     */
    protected function parseSortParams(\Illuminate\Http\Request $request, string $defaultColumn = 'created_at', string $defaultOrder = 'desc'): array
    {
        $sortBy = $request->get('sortBy', $request->get('sort_by', $defaultColumn));

        // Handle if sortBy is a JSON string
        if (is_string($sortBy) && (str_starts_with($sortBy, '{') || str_starts_with($sortBy, '['))) {
            $sortByDecoded = json_decode($sortBy, true);
            if ($sortByDecoded && isset($sortByDecoded['key'])) {
                return [
                    'column' => $sortByDecoded['key'],
                    'order' => $sortByDecoded['order'] ?? $defaultOrder,
                ];
            }

            return ['column' => $defaultColumn, 'order' => $defaultOrder];
        }

        return [
            'column' => $sortBy,
            'order' => $request->get('sortOrder', $request->get('sort_order', $defaultOrder)),
        ];
    }

    /**
     * Apply common filters to a query
     */
    protected function applyFilters($query, \Illuminate\Http\Request $request, array $filterableFields = []): void
    {
        foreach ($filterableFields as $field => $config) {
            $value = $request->get($field);
            
            if ($value !== null && $value !== '') {
                if (is_array($config)) {
                    $operator = $config['operator'] ?? '=';
                    $column = $config['column'] ?? $field;
                    
                    match ($operator) {
                        'like' => $query->where($column, 'like', "%{$value}%"),
                        'in' => $query->whereIn($column, is_array($value) ? $value : [$value]),
                        default => $query->where($column, $operator, $value)
                    };
                } else {
                    $query->where($field, $value);
                }
            }
        }
    }

    /**
     * Apply common search functionality
     */
    protected function applySearch($query, \Illuminate\Http\Request $request, array $searchableFields = []): void
    {
        $search = $request->get('search');
        
        if ($search && !empty($searchableFields)) {
            $query->where(function ($q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field) {
                    if (is_array($field)) {
                        $relation = $field['relation'];
                        $columns = $field['columns'];
                        
                        $q->orWhereHas($relation, function ($rq) use ($search, $columns) {
                            foreach ($columns as $column) {
                                $rq->where($column, 'like', "%{$search}%");
                            }
                        });
                    } else {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(\Illuminate\Http\Request $request): int
    {
        return (int) $request->get('per_page', 15);
    }
}
