(() => {
    // The clock-in endpoint and response fields should match the backend contract.
    const CLOCK_IN_ENDPOINT = '../backend/controllers/clock-in.php';

    const button = document.getElementById('clock-in-button');
    const feedbackElement = document.getElementById('clock-feedback');
    const greetingElement = document.getElementById('greeting');
    const dateElement = document.getElementById('date-line');

    const state = {
        isClockedIn: false,
        isSubmitting: false,
        clockedInAt: null,
        feedback: '',
        feedbackType: 'status'
    };

    function render() {
        button.disabled = state.isSubmitting || state.isClockedIn;
        button.querySelector('span:last-child').textContent = state.isSubmitting
            ? 'Clocking In…'
            : state.isClockedIn ? 'Clocked In' : 'Clock In';

        feedbackElement.textContent = state.feedback;
        feedbackElement.setAttribute('role', state.feedbackType === 'error' ? 'alert' : 'status');
    }

    function renderDateAndGreeting() {
        const now = new Date();
        const hour = now.getHours();
        const greeting = hour < 12 ? 'Good Morning!' : hour < 18 ? 'Good Afternoon!' : 'Good Evening!';
        const date = new Intl.DateTimeFormat('en-ZA', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(now);
        const time = new Intl.DateTimeFormat('en-ZA', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        }).format(now).toUpperCase();

        greetingElement.textContent = greeting;
        dateElement.textContent = `${date} • ${time}`;
    }

    async function clockIn() {
        if (state.isSubmitting || state.isClockedIn) {
            return;
        }

        state.isSubmitting = true;
        state.feedback = '';
        state.feedbackType = 'status';
        render();

        try {
            const response = await fetch(CLOCK_IN_ENDPOINT, {
                method: 'POST',
                headers: { Accept: 'application/json' }
            });

            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error('We could not read the server response. Please try again.');
            }

            if (!response.ok || result.success === false) {
                throw new Error(result.message || 'Unable to clock in. Please try again.');
            }

            state.isClockedIn = true;
            state.clockedInAt = result.clockedInAt || result.clocked_in_at || null;
            state.feedback = state.clockedInAt
                ? `Clock-in successful. Recorded at ${new Date(state.clockedInAt).toLocaleString()}.`
                : 'Your clock-in was recorded.';
        } catch (error) {
            state.feedback = error instanceof TypeError
                ? 'Connection problem. Check your network and try again.'
                : error.message || 'Unable to clock in. Please try again.';
            state.feedbackType = 'error';
        } finally {
            state.isSubmitting = false;
            render();
        }
    }

    button.addEventListener('click', clockIn);
    renderDateAndGreeting();
    render();
})();
