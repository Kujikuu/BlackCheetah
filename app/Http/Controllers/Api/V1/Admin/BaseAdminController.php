<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;

abstract class BaseAdminController extends Controller
{
    /**
     * Ensure user has admin role
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Admin access required');
        }
    }

    /**
     * Get theme color for primary
     */
    protected function getPrimaryColor(): string
    {
        return '#696CFF';
    }

    /**
     * Get theme color for success
     */
    protected function getSuccessColor(): string
    {
        return '#71DD37';
    }

    /**
     * Get theme color for info
     */
    protected function getInfoColor(): string
    {
        return '#03C3EC';
    }

    /**
     * Get theme color for warning
     */
    protected function getWarningColor(): string
    {
        return '#FFAB00';
    }

    /**
     * Parse sorting parameters from request
     * Handles both string and JSON object formats
     */
    protected function parseSortParams(Request $request, string $defaultColumn = 'created_at', string $defaultOrder = 'desc'): array
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
}
