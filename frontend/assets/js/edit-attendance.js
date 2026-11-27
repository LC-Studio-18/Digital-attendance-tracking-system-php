
document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('editTimeForm');

    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 

            const recordId = document.getElementById('attendanceId').value;
            const newClockIn = document.getElementById('clockInInput').value;
            const newClockOut = document.getElementById('clockOutInput').value;
            const reason = document.getElementById('edit-time-reason').value.trim();

            const payload = {
                record_id: recordId,
                new_clock_in: newClockIn,
                new_clock_out: newClockOut,
                reason: reason
            };

            try {
                const response = await fetch('../../backend/api/correct_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.status === 'success') {
                    
                    // CHAD'S FUNCTION: Trigger the UI update
                    if (window.EditTime && typeof window.EditTime.applySuccessfulCorrection === 'function') {
                        window.EditTime.applySuccessfulCorrection(recordId, newClockIn, newClockOut, reason);
                    } else {
                        console.warn("Chad's UI update function not found.");
                        alert("Time updated in database, but UI failed to refresh.");
                    }
                    
                } else {
                    alert('Error: ' + result.message);
                }

            } catch (error) {
                console.error('Fetch error:', error);
                alert('A network error occurred. Check your connection.');
            }
        });
    }
});
