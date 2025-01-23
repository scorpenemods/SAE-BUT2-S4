function toggleDetails(meetingId) {
    let detailsDiv = document.getElementById('meeting-details-' + meetingId);
    if (detailsDiv.classList.contains('hidden')) {
        detailsDiv.classList.remove('hidden');
    } else {
        detailsDiv.classList.add('hidden');
    }
}