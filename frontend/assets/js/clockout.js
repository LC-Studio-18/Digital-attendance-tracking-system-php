(function () {
    "use strict";

    var clockOutView = document.getElementById("clockOutView");
    var successView = document.getElementById("successView");
    var confirmModal = document.getElementById("confirmModal");

    function pad(number) {
        return String(number).padStart(2, "0");
    }

    function updateDateTime() {
        var now = new Date();
        var date = now.toLocaleDateString("en-ZA", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        });
        var time = pad(now.getHours()) + ":" + pad(now.getMinutes());

        document.getElementById("dateTime").textContent =
            date.replace(/,/g, "") + "  ·  " + time;

        var hour = now.getHours();
        var greeting = "Good Day";
        if (hour < 12) greeting = "Good Morning";
        if (hour >= 12 && hour < 17) greeting = "Good Afternoon";
        if (hour >= 17) greeting = "Good Evening";

        document.getElementById("dayGreeting").textContent = greeting;
        document.getElementById("successGreeting").textContent = greeting + "!";
    }

    function showModal(modal) {
        modal.hidden = false;
    }

    function hideModal(modal) {
        modal.hidden = true;
    }

    document.getElementById("openConfirm").addEventListener("click", function () {
        showModal(confirmModal);
        document.getElementById("cancelClockOut").focus();
    });

    document.getElementById("cancelClockOut").addEventListener("click", function () {
        hideModal(confirmModal);
        document.getElementById("openConfirm").focus();
    });

    document.getElementById("confirmClockOut").addEventListener("click", function () {
        hideModal(confirmModal);
        clockOutView.parentElement.hidden = true;

        var now = new Date();
        document.getElementById("clockoutTime").textContent =
            pad(now.getHours()) + ":" + pad(now.getMinutes()) +
            (now.getHours() >= 12 ? " PM" : " AM");

        successView.hidden = false;
    });

    document.getElementById("backToDashboard").addEventListener("click", function () {
        successView.hidden = true;
        clockOutView.parentElement.hidden = false;
        clockOutView.hidden = false;
        updateDateTime();
    });

    confirmModal.addEventListener("click", function (event) {
        if (event.target === confirmModal) {
            hideModal(confirmModal);
            document.getElementById("openConfirm").focus();
        }
    });

    updateDateTime();
    window.setInterval(updateDateTime, 30000);
})();
