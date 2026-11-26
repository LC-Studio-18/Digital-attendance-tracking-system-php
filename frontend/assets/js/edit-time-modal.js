// Connects row-level edit buttons to the shared modal and applies mock saves to the visible UI.
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
    const editTimeReason = document.querySelector("#edit-time-reason");
    const editTimeSaveButton = editTimeForm.querySelector('[type="submit"]');
    let activeEditTrigger = null;

    async function sendTimeCorrectionMock(payload) {
        if (!payload.staff_id) {
            return {
                success: false,
                message: "The staff ID is missing. Refresh the page and try again.",
            };
        }

        return { success: true };
    }

    const sendTimeCorrection = sendTimeCorrectionMock;

    /*
    When the endpoint is ready and rows provide data-edit-time-attendance-id,
    comment out the mock binding above and uncomment this request:

    const sendTimeCorrection = async function (payload) {
        const response = await fetch("/api/correct_attendance.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const result = await response.json();

        if (!response.ok) {
            return {
                success: false,
                message: result.message || "The time correction could not be saved.",
            };
        }

        return result;
    };
    */

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

    function updateTimeCell(cell, value) {
        const label = cell.querySelector(".lbl");
        const lateValue = cell.querySelector(".late");

        if (lateValue && value) {
            lateValue.textContent = value;
            return;
        }
        if (lateValue) {
            lateValue.remove();
        }

        Array.from(cell.childNodes).forEach((node) => {
            if (node !== label) {
                node.remove();
            }
        });
        cell.append(document.createTextNode(value || "\u2014"));
    }

    function updateRecentActivity(name, activityList) {
        const now = new Date();
        const time = new Intl.DateTimeFormat(undefined, {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        }).format(now);
        const item = document.createElement("li");
        const timeLabel = document.createElement("span");
        const activity = document.createElement("span");
        const action = document.createElement("em");

        timeLabel.textContent = time;
        activity.append(document.createTextNode(name));
        action.textContent = "\u2022Time Corrected";
        activity.append(action);
        item.append(timeLabel, activity);
        activityList.prepend(item);
    }

    document.addEventListener("click", (event) => {
        const trigger = event.target instanceof Element
            ? event.target.closest("[data-edit-time-open]")
            : null;

        if (!trigger) {
            return;
        }

        activeEditTrigger = trigger;
        setEditTimeValues(trigger);
        editTimeReason.value = "";
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

    editTimeForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!activeEditTrigger) {
            editTimeNotice.textContent = "Choose an attendance row before saving.";
            editTimeNotice.hidden = false;
            return;
        }

        const correction = {
            attendance_id: activeEditTrigger.dataset.editTimeAttendanceId || "",
            staff_id: activeEditTrigger.dataset.editTimeStaffId || "",
            clock_in: editTimeClockIn.value,
            clock_out: editTimeClockOut.value,
            reason: editTimeReason.value.trim(),
        };

        editTimeSaveButton.disabled = true;
        editTimeSaveButton.textContent = "Saving...";
        editTimeNotice.hidden = true;

        try {
            const result = await sendTimeCorrection(correction);
            if (!result || result.success !== true) {
                editTimeNotice.textContent = result && result.message
                    ? result.message
                    : "The time correction could not be saved. Please try again.";
                editTimeNotice.hidden = false;
                return;
            }

            const row = activeEditTrigger.closest(".rec");
            const timeCells = row ? row.querySelectorAll(".c") : [];
            const activityList = document.querySelector("#activity");
            if (!row || timeCells.length < 2 || !activityList) {
                editTimeNotice.textContent = "The attendance row or Recent Activity feed could not be updated. Refresh the page and try again.";
                editTimeNotice.hidden = false;
                return;
            }

            updateTimeCell(timeCells[0], correction.clock_in);
            updateTimeCell(timeCells[1], correction.clock_out);
            activeEditTrigger.dataset.editTimeClockIn = correction.clock_in;
            activeEditTrigger.dataset.editTimeClockOut = correction.clock_out;
            updateRecentActivity(activeEditTrigger.dataset.editTimeStaffName || "", activityList);
            editTimeDialog.close();

            activeEditTrigger.focus();
            activeEditTrigger = null;
        } catch (error) {
            console.error("Could not save the attendance time correction.", error);
            editTimeNotice.textContent = error instanceof Error
                ? error.message
                : "The time correction could not be saved. Please try again.";
            editTimeNotice.hidden = false;
        } finally {
            editTimeSaveButton.disabled = false;
            editTimeSaveButton.textContent = "Save Changes";
        }
    });

    updateEditTimeClock();
})();
