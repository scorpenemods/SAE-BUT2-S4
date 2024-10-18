<?php


/*
 * getPageOffers
 * Get the n-th page of offers (12 offers per page)
 */
function getPageOffers(int $n, array $filters): ?array {
    $filteredOffers = Offer::getFilteredOffers($n, $filters);

    if (!$filteredOffers) {
        return null;
    }

    return array(
        "offers" => $filteredOffers,
        "totalPages" => 10
    );
}
?>
