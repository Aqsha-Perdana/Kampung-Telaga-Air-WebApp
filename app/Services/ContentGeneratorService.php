<?php

namespace App\Services;

use App\Models\Boat;
use App\Models\Homestay;
use App\Models\Destinasi;
use App\Models\PaketCulinary;
use App\Models\Kiosk;

class ContentGeneratorService
{
    /**
     * Generate a compelling description based on selected resources.
     */
    public function generateDescription(array $data)
    {
        $destinations = $this->getNames(Destinasi::class, $data['destinasi_ids'] ?? [], 'nama');
        $homestays = $this->getNames(Homestay::class, $data['homestay_ids'] ?? [], 'nama');
        $boats = $this->getNames(Boat::class, $data['boat_ids'] ?? [], 'nama');
        $culinaries = $this->getNames(PaketCulinary::class, $data['culinary_paket_ids'] ?? [], 'nama_paket');
        
        $parts = [];
        
        // Intro
        $intros = [
            "Experience the ultimate getaway in Telaga Air with this exclusive package.",
            "Discover the hidden gems of Telaga Air through this carefully curated adventure.",
            "Escape the ordinary and immerse yourself in the beauty of Telaga Air.",
            "Create unforgettable memories with your loved ones in this comprehensive tour package."
        ];
        $parts[] = $intros[array_rand($intros)];

        // Destinations
        if (!empty($destinations)) {
            $destList = $this->formatList($destinations);
            $parts[] = "Explore breathtaking locations including {$destList}.";
        }

        // Homestay
        if (!empty($homestays)) {
            $homeList = $this->formatList($homestays);
            $parts[] = "Enjoy a comfortable and relaxing stay at {$homeList}, offering authentic local hospitality.";
        }

        // Boat
        if (!empty($boats)) {
            $boatList = $this->formatList($boats);
            $parts[] = "Embark on a thrilling water adventure with our premium boat service, {$boatList}.";
        }

        // Culinary
        if (!empty($culinaries)) {
            $foodList = $this->formatList($culinaries);
            $parts[] = "Indulge your taste buds with delicious local delicacies like {$foodList}.";
        }

        // Closing
        $closings = [
            "Book now for an experience you'll cherish forever!",
            "Perfect for families, couples, and solo travelers alike.",
            "Don't miss out on this perfect blend of adventure and relaxation.",
            "Your dream vacation in Telaga Air starts here."
        ];
        $parts[] = $closings[array_rand($closings)];

        return implode(" ", $parts);
    }

    /**
     * Generate a day-by-day itinerary based on selected resources and duration.
     */
    public function generateItinerary(array $data)
    {
        $duration = (int) ($data['durasi_hari'] ?? 1);
        $itinerary = [];

        // Fetch resource details with their assigned days
        $activities = $this->mapActivities($data);

        for ($day = 1; $day <= $duration; $day++) {
            $dayActivities = $activities[$day] ?? [];
            $title = "Day {$day}: Exploration & Leisure";
            $descParts = [];

            if ($day === 1) {
                $title = "Arrival & Check-in";
                $descParts[] = "Arrive at meeting point and warm welcome.";
            }

            if (!empty($dayActivities)) {
                foreach ($dayActivities as $act) {
                    $descParts[] = $act;
                }
            } else {
                $descParts[] = "Free and easy time to explore the surroundings or relax.";
            }

            if ($day === $duration) {
                $descParts[] = "Check-out and departure. Safe travels home!";
                if ($duration > 1) {
                    $title = "Departure";
                }
            }

            $itinerary[] = [
                'day' => $day,
                'title' => $title,
                'description' => implode(" ", $descParts)
            ];
        }

        return $itinerary;
    }

    private function getNames($model, $ids, $column)
    {
        if (empty($ids)) return [];
        return $model::whereIn($model === Destinasi::class ? 'id_destinasi' : ($model === Homestay::class ? 'id_homestay' : ($model === Kiosk::class ? 'id_kiosk' : 'id')), $ids)
            ->pluck($column)
            ->toArray();
    }

    private function formatList(array $items)
    {
        if (count($items) <= 1) return array_shift($items);
        $last = array_pop($items);
        return implode(', ', $items) . ' and ' . $last;
    }

    private function mapActivities($data)
    {
        $mapped = [];

        // Map Destinations
        if (isset($data['destinasi_ids'])) {
            foreach ($data['destinasi_ids'] as $idx => $id) {
                $day = $data['destinasi_hari'][$idx] ?? 1;
                $name = Destinasi::where('id_destinasi', $id)->value('nama');
                if ($name) $mapped[$day][] = "Visit the stunning {$name}.";
            }
        }

        // Map Boats
        if (isset($data['boat_ids'])) {
            foreach ($data['boat_ids'] as $idx => $id) {
                $day = $data['boat_hari'][$idx] ?? 1;
                $name = Boat::find($id)->nama ?? 'Boat';
                $mapped[$day][] = "Enjoy a scenic boat tour with {$name}.";
            }
        }

        // Map Culinary
        if (isset($data['culinary_paket_ids'])) {
            foreach ($data['culinary_paket_ids'] as $idx => $id) {
                $day = $data['culinary_hari'][$idx] ?? 1;
                $name = PaketCulinary::find($id)->nama_paket ?? 'Culinary';
                $mapped[$day][] = "Authentication local dining experience: {$name}.";
            }
        }
        
        // Map Kiosk
        if (isset($data['kiosk_ids'])) {
            foreach ($data['kiosk_ids'] as $idx => $id) {
                $day = $data['kiosk_hari'][$idx] ?? 1;
                $name = Kiosk::where('id_kiosk', $id)->value('nama');
                if ($name) $mapped[$day][] = "Shopping for souvenirs at {$name}.";
            }
        }

        // Map Homestay (usually Check-in on Day 1)
        if (isset($data['homestay_ids'])) {
            foreach ($data['homestay_ids'] as $idx => $id) {
                $name = Homestay::where('id_homestay', $id)->value('nama');
                if ($name) {
                    // Assuming check-in is day 1
                    $mapped[1][] = "Check-in at {$name}.";
                }
            }
        }

        return $mapped;
    }
}
