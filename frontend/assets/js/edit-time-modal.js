// Connects row-level edit buttons to the shared modal; saving remains a UI-only preview.
(() => {
    const editTimeDialog = document.querySelector("[data-edit-time-dialog]");
    const editTimeForm = document.querySelector("[data-edit-time-form]");
    const editTimeNotice = document.querySelector("[data-edit-time-notice]");
    const editTimeClock = document.querySelector("[data-edit-time-now]");
    const editTimeStaffName = document.querySelector("[data-edit-time-staff-name]");
    const editTimeStaffId = document.querySelector("[data-edit-time-staff-id]");
    const editTimeInitials = document.querySelector("[data-edit-time-initials]");
    const editTimeClockIn = document.querySelector("#edit-time-clock-in");
    const editTimeClockOut = document.querySelector("#edit-time-clock-out");

    function updateEditTimeClock() {
        const now = new Date();
        editTimeClock.dateTime = now.toISOString();
        editTimeClock.textContent = new Intl.DateTimeFormat(undefined, {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        }).format(now);
    }

    function setEditTimeValues(trigger) {
        if (trigger.hasAttribute("data-edit-time-staff-name")) {
            const name = trigger.dataset.editTimeStaffName;
            const staffId = trigger.dataset.editTimeStaffId;
            const nameParts = name.trim().split(/\s+/);

            editTimeStaffName.textContent = name;
            editTimeStaffId.textContent = staffId;
            editTimeInitials.textContent = nameParts
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part[0].toUpperCase())
                .join("");
            editTimeClockIn.value = trigger.dataset.editTimeClockIn || "";
            editTimeClockOut.value = trigger.dataset.editTimeClockOut || "";
        }
    }

    document.addEventListener("click", (event) => {
        const trigger = event.target instanceof Element
            ? event.target.closest("[data-edit-time-open]")
            : null;

        if (!trigger) {
            return;
        }

        setEditTimeValues(trigger);
        editTimeNotice.hidden = true;
        editTimeDialog.showModal();
        updateEditTimeClock();
    });

    document.querySelectorAll("[data-edit-time-close]").forEach((button) => {
        button.addEventListener("click", () => editTimeDialog.close());
    });

    editTimeDialog.addEventListener("click", (event) => {
        if (event.target === editTimeDialog) {
            editTimeDialog.close();
        }
    });

    editTimeDialog.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            event.preventDefault();
            editTimeDialog.close();
        }
    });

    editTimeForm.addEventListener("submit", (event) => {
        event.preventDefault();
        editTimeNotice.textContent = "Saved";
        editTimeNotice.hidden = false;
    });

    updateEditTimeClock();
})();
