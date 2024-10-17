<?php


/*
 * getPageOffers
 * Get the n-th page of offers (12 offers per page)
 */
function getPageOffers(int $n, array $filters): ?array {
    $filteredOffers = Offer::getFilteredOffers($filters);

    if (!$filteredOffers) {
        return null;
    }

    $startIndex = ($n - 1) * 12;
    $endIndex = $startIndex + 12;

    return array(
        "offers" => array_slice($filteredOffers, $startIndex, $endIndex) ?? array(),
        "totalPages" => ceil(count($filteredOffers) / 12) ?? 1
    );
}
?>
