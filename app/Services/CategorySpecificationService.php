<?php

namespace App\Services;

class CategorySpecificationService
{
    /**
     * Get specifications for a given category.
     * 
     * @param string|int $categoryIdentifier Slug or ID
     * @return array
     */
    public function getSpecifications($categoryIdentifier)
    {
        // If it's an ID, we'd ideally look up the slug, but for now let's use a mapping logic
        // In a real app, this might be stored in the database or a config file.
        
        $specifications = [
            'vintage-cars' => [
                [
                    'name' => 'year',
                    'label' => 'Year',
                    'type' => 'number',
                    'placeholder' => 'e.g. 1965',
                    'required' => true,
                ],
                [
                    'name' => 'mileage',
                    'label' => 'Mileage (km)',
                    'type' => 'number',
                    'placeholder' => 'e.g. 50000',
                    'required' => false,
                ],
                [
                    'name' => 'fuel_type',
                    'label' => 'Fuel Type',
                    'type' => 'select',
                    'options' => ['Petrol', 'Diesel', 'Electric'],
                    'required' => false,
                ],
            ],
            'jewelry' => [
                [
                    'name' => 'metal',
                    'label' => 'Metal Type',
                    'type' => 'text',
                    'placeholder' => 'e.g. 24K Gold',
                    'required' => true,
                ],
                [
                    'name' => 'stone',
                    'label' => 'Stone Type',
                    'type' => 'text',
                    'placeholder' => 'e.g. Diamond',
                    'required' => false,
                ],
            ],
            'art' => [
                [
                    'name' => 'artist',
                    'label' => 'Artist Name',
                    'type' => 'text',
                    'placeholder' => 'e.g. Vincent van Gogh',
                    'required' => true,
                ],
                [
                    'name' => 'medium',
                    'label' => 'Medium',
                    'type' => 'text',
                    'placeholder' => 'e.g. Oil on Canvas',
                    'required' => false,
                ],
            ],
        ];

        return $specifications[$categoryIdentifier] ?? [];
    }

    /**
     * Get all categories that have specifications.
     *
     * @return array
     */
    public function getCategoriesWithSpecifications()
    {
        return ['vintage-cars', 'jewelry', 'art'];
    }
}
