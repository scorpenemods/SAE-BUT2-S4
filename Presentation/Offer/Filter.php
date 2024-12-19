<?php


/**
 * getPageOffers
 * Get offers filtered by $filters, and paginated by 12 at page $n
 * @param int $n
 * @param array $filters
 * @return array|null
 */
function getPageOffers(int $n, array $filters): ?array {
    $filteredOffers = Offer::getFilteredOffer($n, $filters);

    if (!$filteredOffers) {
        return null;
    }

    return array(
        "offers" => $filteredOffers[0],
        "totalPages" => $filteredOffers[1]
    );
}