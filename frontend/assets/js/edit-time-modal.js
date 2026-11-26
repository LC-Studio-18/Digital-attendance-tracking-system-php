// Connects attendance-row edit buttons to the shared correction modal.
(() => {
    const editTimeDialog = document.querySelector("[data-edit-time-dialog]");
    const editTimeNotice = document.querySelector("[data-edit-time-notice]");
    const editTimeClock = document.querySelector("[data-edit-time-now]");
    const editTimeStaffName = document.querySelector("[data-edit-time-staff-name]");
    const editTimeStaffId = document.querySelector("[data-edit-time-staff-id]");
    const editTimeInitials = document.querySelector("[data-edit-time-initials]");
    const editTimeAttendanceId = document.querySelector("#attendanceId");
    const editTimeClockIn = document.querySelector("#clockInInput");
    const editTimeClockOut = document.querySelector("#clockOutInput");
    let activeEditTrigger = null;

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
        editTimeAttendanceId.value = trigger.dataset.recordId || "";
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

    // After a successful POST, call with the submitted values:
    // window.EditTime.applySuccessfulCorrection(payload.record_id, payload.new_clock_in, payload.new_clock_out).
    window.EditTime = window.EditTime || {};
    window.EditTime.applySuccessfulCorrection = (recordId, newClockIn, newClockOut) => {
        if (!recordId || typeof window.EditTime.updateAttendanceRow !== "function") {
            throw new Error("The attendance UI updater is unavailable or the record ID is missing.");
        }

        window.EditTime.updateAttendanceRow(recordId, newClockIn, newClockOut);
        editTimeNotice.hidden = true;
        editTimeDialog.close();
        const updatedTrigger = Array.from(document.querySelectorAll("[data-edit-time-open][data-record-id]"))
            .find((button) => button.dataset.recordId === String(recordId));
        if (updatedTrigger) {
            updatedTrigger.focus();
        }
        activeEditTrigger = null;
    };

    document.addEventListener("click", (event) => {
        const trigger = event.target instanceof Element
            ? event.target.closest("[data-edit-time-open]")
            : null;

        if (!trigger) {
            return;
        }

        activeEditTrigger = trigger;
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

    updateEditTimeClock();
})();
