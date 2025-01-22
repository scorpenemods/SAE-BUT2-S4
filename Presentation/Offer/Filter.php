<?php
// File: Filter.php
// Filter offers by criteria

/**
 * get_page_offers
 * Get offers filtered by $filters, and paginated by 12 at page $n
 * @param int $n
 * @param array $filters
 * @return array|null
 */
function get_page_offers(int $n, array $filters): ?array {
    $filteredOffers = Offer::get_filtered_offers($n, $filters);

    if (!$filteredOffers) {
        return null;
    }

    return array(
        "offers" => $filteredOffers[0],
        "totalPages" => $filteredOffers[1]
    );
}
