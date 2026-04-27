<?php

namespace App\Mcp\Prompts;

use App\Models\MapLocation;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Provides guided campus navigation assistance using real map location data. Helps students find buildings, facilities, and plan routes between locations.')]
class MapNavigationPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->get('user');
        $startLocation = trim((string) $this->param($request, 'start_location', ''));
        $destination = trim((string) $this->param($request, 'destination', ''));
        $preference = trim((string) $this->param($request, 'preference', 'fastest'));

        // Get all available locations for context
        $allLocations = MapLocation::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get(['name', 'description', 'category', 'latitude', 'longitude']);

        $navigationPlan = [
            'start' => null,
            'destination' => null,
            'waypoints' => [],
            'estimated_time' => null,
            'distance' => null,
            'instructions' => [],
        ];

        if ($startLocation === '' && $destination === '') {
            // General overview mode
            $categoryGroups = $allLocations->groupBy('category');
            
            $summary = [
                'title' => 'Campus Navigation Guide',
                'total_locations' => $allLocations->count(),
                'categories' => [],
                'popular_destinations' => $allLocations->take(5)->map(fn($loc) => [
                    'name' => $loc->name,
                    'category' => $loc->category,
                    'description' => $loc->description,
                ])->toArray(),
            ];

            foreach ($categoryGroups as $cat => $locations) {
                $summary['categories'][] = [
                    'category' => $cat,
                    'count' => $locations->count(),
                    'locations' => $locations->map(fn($loc) => $loc->name)->toArray(),
                ];
            }

            $summary['assistance_message'] = 'Ask me to navigate from one location to another, or ask about specific buildings or facilities!';

            return Response::json($summary);
        }

        // Find start location
        if ($startLocation !== '') {
            $start = $this->findLocation($allLocations, $startLocation);
            if ($start) {
                $navigationPlan['start'] = [
                    'name' => $start->name,
                    'category' => $start->category,
                    'description' => $start->description,
                    'coordinates' => [
                        'latitude' => $start->latitude,
                        'longitude' => $start->longitude,
                    ],
                ];
            }
        }

        // Find destination
        if ($destination !== '') {
            $dest = $this->findLocation($allLocations, $destination);
            if ($dest) {
                $navigationPlan['destination'] = [
                    'name' => $dest->name,
                    'category' => $dest->category,
                    'description' => $dest->description,
                    'coordinates' => [
                        'latitude' => $dest->latitude,
                        'longitude' => $dest->longitude,
                    ],
                ];
            }
        }

        // Build navigation instructions
        if ($navigationPlan['start'] && $navigationPlan['destination']) {
            $distance = $this->calculateDistance(
                $navigationPlan['start']['coordinates']['latitude'],
                $navigationPlan['start']['coordinates']['longitude'],
                $navigationPlan['destination']['coordinates']['latitude'],
                $navigationPlan['destination']['coordinates']['longitude']
            );

            $navigationPlan['distance'] = round($distance, 2) . ' km';
            $navigationPlan['estimated_time'] = $this->estimateTime($distance, $preference);
            $navigationPlan['instructions'] = $this->buildInstructions(
                $navigationPlan['start'],
                $navigationPlan['destination'],
                $allLocations
            );
        }

        // Provide helpful suggestions if location not found
        if ($startLocation !== '' && !$navigationPlan['start']) {
            $suggestions = $this->suggestSimilarLocations($allLocations, $startLocation);
            $navigationPlan['start_search_error'] = "Location '{$startLocation}' not found.";
            $navigationPlan['start_suggestions'] = $suggestions;
        }

        if ($destination !== '' && !$navigationPlan['destination']) {
            $suggestions = $this->suggestSimilarLocations($allLocations, $destination);
            $navigationPlan['destination_search_error'] = "Location '{$destination}' not found.";
            $navigationPlan['destination_suggestions'] = $suggestions;
        }

        return Response::json($navigationPlan);
    }

    /**
     * Find location by name (case-insensitive, partial match)
     */
    private function findLocation($locations, string $search): ?MapLocation
    {
        $search = strtolower($search);
        
        // Exact match first
        $exact = $locations->first(fn($loc) => strtolower($loc->name) === $search);
        if ($exact) {
            return $exact;
        }

        // Partial match
        $partial = $locations->first(fn($loc) => 
            str_contains(strtolower($loc->name), $search) || 
            str_contains(strtolower($loc->description), $search)
        );
        if ($partial) {
            return $partial;
        }

        return null;
    }

    /**
     * Find similar location names for suggestions
     */
    private function suggestSimilarLocations($locations, string $query): array
    {
        $query = strtolower($query);
        return $locations
            ->filter(fn($loc) => 
                str_contains(strtolower($loc->name), $query) ||
                str_contains(strtolower($loc->category), $query)
            )
            ->take(5)
            ->map(fn($loc) => [
                'name' => $loc->name,
                'category' => $loc->category,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Estimate travel time based on distance and preference
     */
    private function estimateTime(float $distanceKm, string $preference): string
    {
        // Average walking speed: 5 km/h
        $walkingMinutes = round(($distanceKm / 5) * 60);
        
        if ($preference === 'fastest') {
            $bikingMinutes = round(($distanceKm / 15) * 60);
            return "Walk: ~{$walkingMinutes}min | Bike: ~{$bikingMinutes}min";
        }

        return "Approximately {$walkingMinutes} minutes (walking)";
    }

    /**
     * Build step-by-step navigation instructions
     */
    private function buildInstructions(array $start, array $destination, $allLocations): array
    {
        $instructions = [];
        
        $instructions[] = "Start at {$start['name']} ({$start['category']})";
        
        $startCat = $start['category'];
        $destCat = $destination['category'];
        
        if ($startCat !== $destCat) {
            $instructions[] = "Head towards the {$destCat} area";
        }
        
        // Check for nearby landmarks of the same category
        $sameCategory = $allLocations->filter(fn($loc) => 
            $loc->category === $destCat && $loc->name !== $destination['name']
        )->take(2);
        
        if ($sameCategory->isNotEmpty()) {
            $landmarks = $sameCategory->map(fn($loc) => $loc->name)->implode(' and ');
            $instructions[] = "You'll pass by {$landmarks} along the way";
        }
        
        $instructions[] = "Arrive at {$destination['name']} ({$destination['category']})";
        $instructions[] = "Located in: {$destination['description']}";
        
        return $instructions;
    }

    /**
     * Get parameter from request
     */
    private function param(Request $request, string $key, mixed $default = null): mixed
    {
        if (method_exists($request, 'input')) {
            return $request->input($key, $default);
        }

        if (method_exists($request, 'get')) {
            return $request->get($key, $default);
        }

        if ($request instanceof \ArrayAccess && isset($request[$key])) {
            return $request[$key];
        }

        if (isset($request->{$key})) {
            return $request->{$key};
        }

        return $default;
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                'start_location',
                'Starting location (building, facility name) - optional, defaults to current location context',
                false
            ),
            new Argument(
                'destination',
                'Destination location to navigate to',
                true
            ),
            new Argument(
                'preference',
                'Route preference: fastest, shortest, or accessible',
                false
            ),
        ];
    }
}
